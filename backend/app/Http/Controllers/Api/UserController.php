<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $users = User::paginate();
        return $this->paginationResponse($users);
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
            return $this->errorResponse('User not found', 200);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'address' => 'sometimes|string',
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
            return $this->errorResponse('User not found', 200);
        }

        if ($user->id == Auth::user()->id) {
            return $this->errorResponse('You cannot delete yourself', 200);
        }

        $user->delete();
        return $this->messageResponse("User deleted successfully", 201);
    }
}
