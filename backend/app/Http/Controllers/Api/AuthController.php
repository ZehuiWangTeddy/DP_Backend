<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Add DB facade for transaction handling
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
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
        try {
            $validated = $request->validated();

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

            // Load the profiles relationship and get the first profile
            $user->load('profiles');
            $profile = $user->profiles->first();

            // Generate JWT token for the user
            $token = JWTAuth::fromUser($user);

            // Return the response with token, user data, and profile
            return $this->dataResponse([
                'user' => $user->only(['user_id', 'name', 'email', 'address', 'user_role']),
                'profiles' => $user->profiles, // Include all profiles in the response
                'user_referral_code' => $user->sent_referral_code,
                'received_referral_code' => $user->received_referral_code,
                'has_discount' => $user->has_discount,
                'access_token' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => Auth::factory()->getTTL() * 60,
                ],
            ], "Registration successful");
        } catch (Exception $e) {
            // If anything goes wrong, roll back the transaction
            Log::error($e);
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
            'password' => 'required|string|min:6',
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
        if ($user->locked_until) {
            $lockedUntil = Carbon::parse($user->locked_until);
            if (!$lockedUntil->isPast()) {
                return $this->errorResponse(400, 'Account locked. Try again later.');
            }
        }

        // Attempt to authenticate using the provided email and password
        $token = Auth::attempt($validator->safe()->all());

        // If authentication fails, return error response
        if (!$token) {
            // If the user failed to log in 4 times, lock the account
            if ($user->failed_login_attempts >= 4) {
                $user->update([
                    'active' => false,
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

        // Debugging: Check if $user is an instance of User
        if (!($user instanceof User)) {
            return $this->errorResponse(500, 'User instance not found');
        }

        // Reset failed login attempts on successful login
        $user->failed_login_attempts = 0;
        $user->active = true;
        $user->trial_available = true;

        // Save the user
        if (!$user->save()) {
            return $this->errorResponse(500, 'Failed to save user data');
        }

        return $this->dataResponse([
            'user' => User::with(['profiles', 'subscriptions'])->where('user_id', $user->user_id)->first(),
            'access_token' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60,
            ],
        ], 'Login successful');
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

            return $this->messageResponse('Successfully logged out', 200);
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
            return $this->messageResponse("Password reset link sent successfully.", 200);
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
            'password' => 'required|min:6|confirmed',
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
            ? $this->messageResponse("Password reset successfully.", 200)
            : $this->errorResponse(400, "Failed to reset password. Please try again.");
    }

    /**
     * Reset User Password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed',
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

        // Retrieve authenticated user
        $user = Auth::user();

        // Debugging: Check if $user is an instance of User
        if (!($user instanceof User)) {
            return $this->errorResponse(500, 'User instance not found');
        }

        // Perform password reset
        $user->password = Hash::make($password); // Update password
        if ($user->save()) {
            return $this->messageResponse("Password reset successfully.", 200);
        }

        return $this->errorResponse(400, "Failed to reset password. Please try again.");
    }

    public function loginFailed()
    {
        return $this->errorResponse(401, 'Unauthenticated');
    }
}
