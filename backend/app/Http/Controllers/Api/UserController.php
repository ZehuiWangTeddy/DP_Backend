<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'email' => [
                'sometimes', 'email', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
        ]);

        $user->update($validated);
        return $this->dataResponse($user);
    }

    /**
     * Remove the specified user.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
