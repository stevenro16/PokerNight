<?php

namespace App\Http\Controllers;

use App\Models\GameImage;
use App\Models\GroupMember;
use App\Models\PokerGroup;
use App\Models\PokerNight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GameImageController extends Controller
{
    public function store(Request $request, PokerGroup $group, PokerNight $night)
    {
        abort_if($night->group_id !== $group->id, 404);

        $isMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if (! $isMember && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'images'   => ['required', 'array', 'max:10'],
            'images.*' => ['image', 'max:10240'],
        ]);

        $hasCover = $night->images()->where('is_cover', true)->exists();
        $sortBase = $night->images()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $i => $file) {
            $path = $file->store("poker-nights/{$night->id}", 'public');

            GameImage::create([
                'poker_night_id' => $night->id,
                'uploaded_by'    => Auth::id(),
                'file_path'      => $path,
                'is_cover'       => ! $hasCover && $i === 0,
                'sort_order'     => $sortBase + $i + 1,
            ]);

            if (! $hasCover && $i === 0) {
                $hasCover = true;
            }
        }

        return back()->with('success', 'Images uploaded!');
    }

    public function destroy(GameImage $image)
    {
        $night = $image->pokerNight;
        $isMember = GroupMember::where('group_id', $night->group_id)->where('user_id', Auth::id())->exists();
        if (! $isMember && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        Storage::disk('public')->delete($image->file_path);
        $wasCover = $image->is_cover;
        $image->delete();

        if ($wasCover) {
            $next = GameImage::where('poker_night_id', $night->id)->orderBy('sort_order')->first();
            $next?->update(['is_cover' => true]);
        }

        return back()->with('success', 'Image removed.');
    }
}
