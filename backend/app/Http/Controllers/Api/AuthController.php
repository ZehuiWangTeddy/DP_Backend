<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Add DB facade for transaction handling
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends BaseController
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
    public function register(RegisterRequest $request)
    {
        // Validate incoming request
        $validated = $request->validated();

        // Validate received referral code (if provided)
        if (!empty($validated['received_referral_code'])) {
            $validReferralCode = User::where('sent_referral_code', $validated['received_referral_code'])->exists();
            if (!$validReferralCode) {
                return $this->errorResponse(400, 'Invalid referral code');
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
                'received_referral_code' => $validated['received_referral_code'] ?? null,
                'user_role' => '1', // Default normal user
                'has_discount' => $hasDiscount,
            ]);

            // Commit the transaction after successful user creation
            DB::commit();

            // Generate JWT token for the user
            $token = auth()->login($user);

            // Return the response with token and user data
            return $this->dataResponse([
                'user' => $user->only(['user_id', 'name', 'email', 'address', 'user_role']),
                'user_referral_code' => $user->sent_referral_code,
                'received_referral_code' => $user->received_referral_code,
                'has_discount' => $user->has_discount,
                'access_token' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => Auth::factory()->getTTL() * 60, // Token expiration
                ],
            ], "Registration successful");
        } catch (Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            Log::error($e);
            // Return error response in case of failure

            return $this->errorResponse(500, 'Registration failed. Please try again later.');
        }
    }

    /**
     * User Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        // search for email
        $user = User::where('email', $validator->safe(['email']))->first();

        if (!$user) {
            return $this->errorResponse(400, 'Invalid email or password');
        }

        // Check if the user's account is locked
        if ($user->locked_until && $user->locked_until > now()) {
            return $this->errorResponse(400, 'Account locked. Try again later.');
        }

        // Attempt to authenticate using the provided email and password
        $token = Auth::attempt($validator->safe()->all());

        // If authentication fails, return error response
        if (!$token) {
            // If the user failed to log in 4 times, lock the account
            if ($user->failed_login_attempts >= 4) {
                $user->update([
                    // 'active' => false,
                    'trial_available' => false,
                    'locked_until' => now()->addMinutes(10), // Lock account for 10 minutes
                ]);
            }

            $user->update([
                'failed_login_attempts' => ($user->failed_login_attempts ?: 0) + 1,
            ]);

            return $this->errorResponse(400, 'Invalid email or password');
        }

        // Retrieve authenticated user
        $user = Auth::user();

        // Reset failed login attempts on successful login
        $user->update(['failed_login_attempts' => 0, 'active' => true]);

        return $this->StanderResponse(200, 'Login successful', [
            'user' => Auth::user(),
            'access_token' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * User Logout
     */
    public function logout()
    {
        try {
            Auth::logout();

            // Invalidate the token to log the user out
            Auth::invalidate(true);

            return $this->messageResponse('Successfully logged out');
        } catch (Exception $e) {
            // Handle exception if token invalidation fails
            return $this->errorResponse(500, "Failed to logout");
        }
    }

    /**
     * Send Password Reset Link Email
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $user = User::where('email', $validator->safe(['email']))->first();

        // Check if the user's account is active
        if (!$user || !$user->active) {
            return $this->errorResponse(400, "Account is locked or inactive, password reset link cannot be sent.");
        }

        // Send password reset link
        $response = Password::sendResetLink($validator->safe(['email']));

        // Return success or failure response
        if ($response == Password::RESET_LINK_SENT) {
            return $this->messageResponse("Password reset link sent successfully.");
        }

        return $this->errorResponse(400, "Failed to send password reset link. Please try again.");
    }

    /**
     * Reset User Password with reset password email
     */
    public function resetPasswordWithForgotEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->messageResponse("Password reset successfully.")
            : $this->errorResponse(400, "Failed to reset password. Please try again.");
    }

    /**
     * Reset User Password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        // Retrieve user by email
        $user = Auth::user();

        $safe = $validator->safe();
        $password = $safe['password'];

        // Check if the user's account is active
        if (!$user || !$user->active) {
            return $this->errorResponse(400, "Account is locked or inactive, password reset is not allowed.");
        }

        if (Hash::check($password, $user->password)) {
            return $this->errorResponse(400, "Invalid password. Please try again.");
        }

        // Perform password reset
        $user->password = Hash::make($password); // Update password
        if ($user->save()) {
            return $this->messageResponse("Password reset successfully.");
        }

        return $this->errorResponse(400, "Failed to reset password. Please try again.");
    }
}
