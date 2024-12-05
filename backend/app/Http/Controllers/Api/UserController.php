<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getUserInfo()
    {
        $user = auth()->user(); // Get authenticated user

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User info fetched successfully',
            ],
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'address', 'user_role']),
            ],
        ]);
    }
}
