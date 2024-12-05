<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Add DB facade for transaction handling
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Exception;

class AuthController extends Controller
{
    protected User $user;

    public function __construct(User $user)
    {
        // Inject User model for database operations
        $this->user = $user;
    }

    /**
     * User Registration
     */
    public function register(Request $request)
    {
        // Validate incoming request
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email:rfc,dns|max:100|unique:users', // Ensure unique email in users table
            'password' => 'required|string|min:6|max:100|confirmed',
            'address' => 'nullable|string|max:255',
            'received_referral_code' => 'nullable|string|max:10|exists:users,sent_referral_code',
        ]);

        // Validate received referral code (if provided)
        if (!empty($request['received_referral_code'])) {
            $validReferralCode = User::where('sent_referral_code', $request['received_referral_code'])->exists();
            if (!$validReferralCode) {
                return response()->json(['message' => 'Invalid referral code'], 400);
            }
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            // Generate a referral code for the new user
            $sentReferralCode = strtoupper(Str::random(10));
            $hasDiscount = $request->filled('received_referral_code');

            // Create the new user record in the database
            $user = $this->user::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
                'address' => $request['address'] ?? null,
                'sent_referral_code' => $sentReferralCode,
                'user_role' => '1', // Default normal user
                'has_discount' => $hasDiscount,
            ]);

            // Commit the transaction after successful user creation
            DB::commit();

            // Generate JWT token for the user
            $token = auth()->login($user);

            // Return the response with token and user data
            return response()->json([
                'meta' => [
                    'code' => 201,
                    'status' => 'success',
                    'message' => 'Registration successful',
                ],
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'address', 'user_role']),
                    'user_referral_code' => $user->sent_referral_code,
                    'received_referral_code' => $user->received_referral_code,
                    'has_discount' => $user->has_discount,
                    'access_token' => [
                        'token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL() * 60, // Token expiration
                    ],
                ],
            ]);
        } catch (Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            // Return error response in case of failure
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Registration failed. Please try again later.',
                ],
                'data' => [],
            ]);
        }
    }

    /**
     * User Login
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate using the provided email and password
        $token = auth()->attempt($request->only('email', 'password'));

        // If authentication fails, return error response
        if (!$token) {
            return response()->json(['message' => 'Invalid email or password'], 400);
        }

        // Retrieve authenticated user
        $user = auth()->user();

        // Check if the user's account is locked
        if ($user->locked_until && $user->locked_until > now()) {
            return response()->json(['message' => 'Account locked. Try again later.'], 400);
        }

        // Handle failed login attempts
        if (!Hash::check($request->password, $user->password)) {
            $user->increment('failed_login_attempts');

            // If the user failed to log in 4 times, lock the account
            if ($user->failed_login_attempts >= 4) {
                $user->update([
                    'active' => false,
                    'trial_available' => false,
                    'locked_until' => now()->addMinutes(10), // Lock account for 10 minutes
                ]);
            }

            return response()->json(['message' => 'Invalid email or password'], 400);
        }

        // Reset failed login attempts on successful login
        $user->update(['failed_login_attempts' => 0]);

        // Return the response with token and user data
        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Login successful',
            ],
            'data' => [
                'user' => auth()->user(),
                'access_token' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60,
                ],
            ]
        ]);
    }

    /**
     * User Logout
     */
    public function logout()
    {
        $token = JWTAuth::getToken();

        // If no token is provided, return error response
        if (!$token) {
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No Token provided',
                ],
                'data' => [],
            ]);
        }

        try {
            // Invalidate the token to log the user out
            JWTAuth::invalidate($token);

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Successfully logged out',
                ],
                'data' => [],
            ]);
        } catch (Exception $e) {
            // Handle exception if token invalidation fails
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'Failed to logout',
                ],
                'data' => [],
            ]);
        }
    }

    /**
     * Send Password Reset Link Email
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        // Retrieve user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user's account is active
        if (!$user || !$user->active) {
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Account is locked or inactive, password reset link cannot be sent.',
                ],
            ]);
        }

        // Send password reset link
        $response = Password::sendResetLink($request->only('email'));

        // Return success or failure response
        if ($response == Password::RESET_LINK_SENT) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Password reset link sent successfully.',
                ],
            ]);
        }

        return response()->json([
            'meta' => [
                'code' => 400,
                'status' => 'error',
                'message' => 'Failed to send password reset link. Please try again.',
            ],
        ]);
    }

    /**
     * Reset User Password
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Retrieve user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user's account is active
        if (!$user || !$user->active) {
            return response()->json([
                'meta' => [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Account is locked or inactive, password reset is not allowed.',
                ],
            ]);
        }

        // Perform password reset
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password); // Update password
                $user->save();
            }
        );

        // Return success or failure response
        if ($response == Password::PASSWORD_RESET) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Password reset successfully.',
                ],
            ]);
        }

        return response()->json([
            'meta' => [
                'code' => 400,
                'status' => 'error',
                'message' => 'Failed to reset password. Please try again.',
            ],
        ]);
    }
}
