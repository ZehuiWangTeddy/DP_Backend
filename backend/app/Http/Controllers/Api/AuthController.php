<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisteRequest;
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

class AuthController extends BaseController
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * User Registration
     */
    public function register(RegisteRequest $request)
    {
        $validated = $request->validated();

        if (!empty($validated['received_referral_code'])) {
            $validReferralCode = DB::connection('api_user_pgsql') // 使用 'api_user_pgsql' 连接
            ->table('users_subscriptions_view')
                ->where('user_referral_code', $validated['received_referral_code'])
                ->exists();

            if (!$validReferralCode) {
                return $this->errorResponse(400, 'Invalid referral code');
            }
        }

        try {
            $sentReferralCode = strtoupper(Str::random(10));
            $hasDiscount = !empty($validated['received_referral_code']);

            // Use stored procedure for user creation
            $userId = DB::connection('api_user_pgsql') // 使用 'api_user_pgsql' 连接
            ->selectOne('SELECT create_user_with_profile(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) AS user_id', [
                $validated['email'],
                bcrypt($validated['password']),
                $validated['name'],
                $sentReferralCode,
                $validated['date_of_birth'],
                $validated['address'] ?? null,
                $validated['received_referral_code'] ?? null,
                $hasDiscount,
                true, // Default user_role
                1,    // Default user_role
                'English', // Default language
                false  // Default child_profile
            ])->user_id;

            $user = DB::connection('api_user_pgsql') // 使用 'api_user_pgsql' 连接
            ->table('users_subscriptions_view')
                ->where('user_id', $userId)
                ->first();

            // Use JWTAuth to create a token
            $token = auth()->login($user);

            return $this->dataResponse([
                'user' => [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'access_token' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => Auth::factory()->getTTL() * 60,
                ],
            ], "Registration successful");
        } catch (Exception $e) {
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
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $user = DB::connection('api_user_pgsql') // 使用 'api_user_pgsql' 连接
        ->table('users_subscriptions_view')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return $this->errorResponse(400, 'Invalid email or password');
        }

        if ($user->locked_until && $user->locked_until > now()) {
            return $this->errorResponse(400, 'Account locked. Try again later.');
        }

        // Use Hash to check password and update failed login attempts
        if (!Hash::check($request->password, $user->password)) {
            // Update failed login attempts using a stored procedure or query
            DB::connection('api_user_pgsql') // 使用 'api_user_pgsql' 连接
            ->statement('UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE user_id = ?', [$user->user_id]);
            return $this->errorResponse(400, 'Invalid email or password');
        }

        // Use JWTAuth to create a token
        $token = auth()->login($user);

        return $this->StanderResponse(200, 'Login successful', [
            'user' => $user,
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
            Auth::invalidate(true);

            return $this->messageResponse('Successfully logged out');
        } catch (Exception $e) {
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

        if (!$user || !$user->active) {
            return $this->errorResponse(400, "Account is locked or inactive, password reset link cannot be sent.");
        }

        $response = Password::sendResetLink($validator->safe(['email']));

        if ($response == Password::RESET_LINK_SENT) {
            return $this->messageResponse("Password reset link sent successfully.");
        }

        return $this->errorResponse(400, "Failed to send password reset link. Please try again.");
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

        $user = Auth::user();

        if (!$user || !$user->active) {
            return $this->errorResponse(400, "Account is locked or inactive. Password reset is not allowed.");
        }

        $newPassword = $validator->safe()['password'];

        if (Hash::check($newPassword, $user->password)) {
            return $this->errorResponse(400, "New password cannot be the same as the current password.");
        }

        try {
            $hashedPassword = bcrypt($newPassword);
            DB::connection('api_user_pgsql') // 使用 'api_user_pgsql' 连接
            ->statement('CALL update_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $user->user_id,
                $user->email,
                $hashedPassword,
                $user->name,
                $user->sent_referral_code,
                $user->address,
                $user->received_referral_code,
                $user->has_discount,
                $user->trial_available,
                $user->user_role,
                $user->language,
            ]);

            return $this->messageResponse("Password reset successfully.");
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Failed to reset password. Please try again later.");
        }
    }
}
