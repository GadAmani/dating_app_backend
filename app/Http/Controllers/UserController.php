<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Like;

class UserController extends Controller
{
    public function likeUser(Request $request, User $user) {
    $authUser = $request->user();

    // Save like to pivot table likes (authUser -> user)
    $authUser->likes()->attach($user->id);

    // Check if the other user also liked authUser → match
    $isMatch = $user->likes()->where('liked_user_id', $authUser->id)->exists();

    if ($isMatch) {
        // Save in matches table
        $authUser->matches()->attach($user->id);
        $user->matches()->attach($authUser->id);

        return response()->json(['message' => 'Match!', 'match' => true]);
    }

    return response()->json(['message' => 'User liked', 'match' => false]);
}

public function dislikeUser(Request $request, User $user) {
    $authUser = $request->user();
    $authUser->dislikes()->attach($user->id);

    return response()->json(['message' => 'User disliked']);
}

public function getMatches(Request $request) {
    $authUser = $request->user();
    return $authUser->matches; // list of matched users
}

    /**
     * Return users of the opposite gender
     * for the logged-in user
     */
    public function oppositeGender()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $oppositeGender = $user->gender === 'male' ? 'female' : 'male';

        $users = User::where('gender', $oppositeGender)
                     ->where('id', '!=', $user->id)
                     ->get(['id', 'name', 'age', 'description', 'profile_image']);

        $users->transform(function ($u) {
            $u->profile_image = $u->profile_image
                ? asset('storage/' . $u->profile_image)
                : null;
            return $u;
        });

        return response()->json($users, 200);
    }

    /**
     * Return profiles that liked the logged-in user
     */
    public function likedMe()
    {
        $user = Auth::user();

        $likedUsers = $user->likesReceived()->with('user')->get()->map(function($like) {
            return [
                'id' => $like->user->id,
                'name' => $like->user->name,
                'age' => $like->user->age,
                'description' => $like->user->description,
                'profile_image' => $like->user->profile_image
                    ? asset('storage/' . $like->user->profile_image)
                    : null,
            ];
        });

        return response()->json($likedUsers);
    }

    /**
     * Return mutual matches (both liked each other)
     */
    public function matchedProfiles()
    {
        $user = Auth::user();

        $matchedUsers = User::whereHas('likesReceived', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereHas('likesGiven', function($q) use ($user) {
            $q->where('liked_user_id', $user->id);
        })->get(['id', 'name', 'age', 'description', 'profile_image']);

        $matchedUsers->transform(function ($u) {
            $u->profile_image = $u->profile_image
                ? asset('storage/' . $u->profile_image)
                : null;
            return $u;
        });

        return response()->json($matchedUsers);
    }
}
