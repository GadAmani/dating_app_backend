<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function likeUser(Request $request)
    {
        $request->validate([
            'liked_user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        // Prevent duplicate likes
        $like = Like::firstOrCreate([
            'user_id' => $user->id,
            'liked_user_id' => $request->liked_user_id,
        ]);

        return response()->json(['success' => true, 'like' => $like], 200);
    }
}
