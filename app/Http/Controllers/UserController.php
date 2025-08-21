<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /api/users
    public function index()
    {
        $users = User::paginate(10); // pagination best practice
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    // GET /api/users/{id}
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created',
            'data' => $user
        ], 201);
    }

    // PUT /api/users/{id}
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated',
            'data' => $user
        ]);
    }

    // DELETE /api/users/{id}
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted'
        ]);
    }
}
