<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $userModel = new User();

        $search = $request->get('search');
        if ($search) {
            $userModel = $userModel->where('name', 'like', '%' . $search . '%');
        }

        return $this->paginationResponse($userModel->paginate(request()->get('per_page', 10)));
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        return $this->dataResponse($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'address' => 'sometimes|string',
        ]);

        $user->update($validated);
        return $this->dataResponse($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        if ($user->user_id == Auth::user()->user_id) {
            return $this->errorResponse('You cannot delete yourself', 403);
        }

        $user->delete();
        return $this->messageResponse("User deleted successfully", 204);
    }
}
