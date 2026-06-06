<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\NightComment;
use App\Models\PokerGroup;
use App\Models\PokerNight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NightCommentController extends Controller
{
    public function store(Request $request, PokerGroup $group, PokerNight $night)
    {
        abort_if($night->group_id !== $group->id, 404);
        $this->authorizeMember($group);

        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        NightComment::create([
            'poker_night_id' => $night->id,
            'user_id'        => Auth::id(),
            'message'        => $request->message,
        ]);

        return back()->with('success', 'Comment posted!');
    }

    public function destroy(PokerGroup $group, PokerNight $night, NightComment $comment)
    {
        abort_if($night->group_id !== $group->id, 404);
        abort_if($comment->poker_night_id !== $night->id, 404);

        // Only the comment author or an admin can delete
        if ($comment->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment removed.');
    }

    private function authorizeMember(PokerGroup $group): void
    {
        $isMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if (! $isMember && ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
