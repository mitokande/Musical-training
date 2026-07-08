<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedItem;
use App\Models\FeedLike;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_items' => FeedItem::count(),
            'total_posts' => FeedItem::where('type', 'post')->count(),
            'items_week' => FeedItem::where('created_at', '>=', now()->subDays(7))->count(),
            'total_likes' => FeedLike::count(),
            'total_follows' => Follow::count(),
        ];

        $feedItems = FeedItem::with(['actor:id,name,email', 'subject:id,name'])
            ->withCount('likes')
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->search, fn ($q, $s) => $q->where('body', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $topPosters = FeedItem::select('user_id', DB::raw('count(*) as post_count'))
            ->where('type', 'post')
            ->groupBy('user_id')
            ->orderByDesc('post_count')
            ->with('actor:id,name,email')
            ->take(5)
            ->get();

        $types = FeedItem::select('type')->distinct()->pluck('type');

        return view('admin.community.index', compact('stats', 'feedItems', 'topPosters', 'types'));
    }

    public function destroy(FeedItem $feedItem)
    {
        $feedItem->delete();

        return back()->with('success', 'Feed item deleted.');
    }
}
