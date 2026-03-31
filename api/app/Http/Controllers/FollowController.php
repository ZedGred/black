<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function follow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;
        $authUser = Auth::user();

        if ($authUser->id === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself'
            ], 400);
        }

        $targetUser = User::find($userId);

        if ($authUser->isFollowing($targetUser)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already following this user'
            ], 400);
        }

        $authUser->following()->attach($userId);

        $this->notificationService->notifyUser(
            $targetUser,
            'follow',
            'New Follower',
            $authUser->name . ' started following you',
            User::class,
            $authUser->id
        );

        return response()->json([
            'success' => true,
            'message' => 'You are now following this user',
            'data' => [
                'following_id' => $userId,
                'follower_id' => $authUser->id
            ]
        ]);
    }

    public function unfollow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;
        $authUser = Auth::user();

        $targetUser = User::find($userId);

        if (!$authUser->isFollowing($targetUser)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not following this user'
            ], 400);
        }

        $authUser->following()->detach($userId);

        return response()->json([
            'success' => true,
            'message' => 'You have unfollowed this user'
        ]);
    }

    public function followers(string $username)
    {
        $user = User::where('name', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $followers = $user->followers()
            ->select('users.id', 'users.name', 'users.email')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'followers_count' => $user->followersCount()
                ],
                'followers' => $followers
            ]
        ]);
    }

    public function following(string $username)
    {
        $user = User::where('name', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $following = $user->following()
            ->select('users.id', 'users.name', 'users.email')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'following_count' => $user->followingCount()
                ],
                'following' => $following
            ]
        ]);
    }

    public function checkFollow(string $username)
    {
        $user = User::where('name', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $authUser = Auth::user();
        $isFollowing = $authUser->isFollowing($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'followers_count' => $user->followersCount(),
                    'following_count' => $user->followingCount(),
                ],
                'is_following' => $isFollowing,
                'is_me' => $authUser->id === $user->id
            ]
        ]);
    }
}
