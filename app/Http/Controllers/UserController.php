<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET /api/users
    public function index()
    {
        $users = User::paginate(10); // pagination best practice
        return response()->json([
            'success' => true,
            "message" => 'Get all users succesfully',
            'data' => $users
        ]);
    }

    // GET /api/users/{id}
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'message' => 'Get user succesfully',
            'data' => $user
        ]);
    }

    // POST /api/users
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'role' => ['required', 'string', Rule::in(['writer', 'admin'])], // role wajib & valid
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', // pastikan ada password_confirmation
            'about' => 'nullable|string',
            'profile_picture' => 'nullable|string' // bisa diubah jadi file jika upload
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $validated = $validator->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create([
            'role' => $validated['role'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'about' => $validated['about'] ?? null,
            'profile_picture' => $validated['profile_picture'] ?? null
        ]);
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|required|string|min:6|confirmed',
            'role' => ['sometimes', 'required', Rule::in(User::validRoles())],
            'about' => 'sometimes|nullable|string',
            'profile_picture' => 'sometimes|nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $validated = $validator->validated();
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'User successfully updated',
            'data' => $user
        ]);
    }

    // DELETE /api/users/{id}
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User succesfully deleted'
        ]);
    }
}
