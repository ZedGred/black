<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\JwtHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

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

    //======== GOOGLE OAUTH ========
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $username = str_replace(' ', '_', $googleUser->name);
                $username = $username ?: 'google_user_' . time();

                $user = User::create([
                    'name'     => $username,
                    'email'    => $googleUser->email,
                    'password' => Hash::make(uniqid()),
                    'google_id' => $googleUser->id,
                ]);
            }

            $token = Auth::guard('api')->login($user);
            $cookie = JwtHelper::makeJwtCookie($token);

            $frontendUrl = config('app.frontend_url', env('APP_URL', 'http://localhost:3000'));
            
            return redirect()->to($frontendUrl . '/auth/google/callback?token=' . $token);

        } catch (\Exception $e) {
            $frontendUrl = config('app.frontend_url', env('APP_URL', 'http://localhost:3000'));
            return redirect()->to($frontendUrl . '/login?error=google_auth_failed');
        }
    }
}
