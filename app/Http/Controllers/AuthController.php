<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\JwtHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed', 
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $username = str_replace(' ', '_', $validated['name']);

        $user = User::create([
            'name'     => $username,
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $token  = Auth::guard('api')->login($user);
        $cookie = JwtHelper::makeJwtCookie($token);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'token' => [
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                    'expires_in'   => auth('api')->factory()->getTTL() * 60
                ],
            ]
        ])->cookie($cookie);
    }



    public function registerWriter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'This email is not registered in the system'
            ], 422);
        }

        // Cek password sama dengan yang sudah tersimpan
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Incorrect password'
            ], 422);
        }

        // Jika user sudah writer
        if ($user->hasRole('writer')) {
            return response()->json([
                'error' => 'This email is already registered as a writer'
            ], 422);
        }

        // Tambahkan role writer
        $user->syncRoles('writer');

        // Generate token
        $token = Auth::guard('api')->login($user);
        $cookie = JwtHelper::makeJwtCookie($token);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated as writer successfully',
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
        $cookie = JwtHelper::makeJwtCookie($token);

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
