<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user->assignRole('user');

        $token = Auth::guard('api')->login($user);
        $cookie = cookie('token', $token, 60, null, null, true, true);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ],

            ]
        ])->cookie($cookie);
    }

    public function registerWriter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->hasRole('writer')) {
                return response()->json([
                    'error' => 'This email is already registered as a writer'
                ], 422);
            }
            $user->syncRoles('writer');
        } else {
            return response()->json([
                'error' => 'This email is not registered in the system'
            ], 422);
        }

        $token = Auth::guard('api')->login($user);
        $cookie = cookie('token', $token, 60, null, null, true, true);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
            'token' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]
        ])->cookie($cookie);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');
        $token = Auth::guard('api')->attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $cookie = cookie('token', $token, 60, null, null, true, true);
        return response()->json([
            'status' => 'success',
            'message' => 'User logged in successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ]
        ])->cookie($cookie);
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /*public function me()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 301);
        }

        return response()->json($user);
    }*/
    public function me()
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User data retrieved successfully',
            'data' => [
                'user'        => $user->only('id', 'name', 'email'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles'       => $user->getRoleNames(),
            ],
        ], 200);
    }


    public function refresh()
    {
        try {
            $newToken = Auth::guard('api')->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                "data" => [
                    'user' => Auth::guard('api')->user(),
                    'token' => [
                        'access_token' => $newToken,
                        'token_type'   => 'Bearer',
                        'expires_in'   => auth('api')->factory()->getTTL() * 60,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid or has expired'
            ], 401);
        }
    }

    //======== SHOW ========
    public function showLogin()
    {
        return view('login');
    }
}
