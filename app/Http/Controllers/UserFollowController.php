<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserFollowController extends Controller
{
    public function follow(Request $request, User $user)
    {
        abort_if($request->user()->is($user), 422);

        $request->user()->following()->syncWithoutDetaching($user->id);

        return back()->with('status', 'user-followed');
    }

    public function unfollow(Request $request, User $user)
    {
        $request->user()->following()->detach($user->id);

        return back()->with('status', 'user-unfollowed');
    }
}
