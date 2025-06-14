<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
class ContentModerationController extends Controller
{
    public function index(Request $request)
    {
        $contentItems = ContentItem::with(['user', 'comments'])
            ->when($request->input('search'), function ($query, $search) {
    $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('body', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%")
          ->orWhereHas('alum', function ($alumQuery) use ($search) { // Changed from user to alum
              $alumQuery->where('name', 'like', "%{$search}%");
          });
    });
})
            ->when($request->input('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString()
->through(function ($item) {
    return [
        'id' => $item->id,
        'type' => $item->type,
        'title' => $item->title,
        'excerpt' => Str::limit($item->body ?: $item->description, 100),
        'alum' => $item->alum ? [ // Changed from user to alum
            'name' => $item->alum->name,
            'id' => $item->alum->id
        ] : null,
        'status' => $item->status,
        'created_at' => $item->created_at->format('M d, Y H:i'),
        'comments_count' => $item->comments->count(),
        'reports_count' => $item->reports_count ?? 0
    ];
});

        $comments = Comment::with(['user', 'contentItem'])
->when($request->input('comment_search'), function ($query, $search) {
    $query->where(function ($q) use ($search) {
        $q->where('content', 'like', "%{$search}%")
          ->orWhereHas('alum', function ($alumQuery) use ($search) { // Changed from user to alum
              $alumQuery->where('name', 'like', "%{$search}%");
          });
    });
})
            ->when($request->input('comment_status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'comments_page')
            ->withQueryString()
->through(function ($comment) {
    return [
        'id' => $comment->id,
        'content' => $comment->content,
        'alum' => $comment->alum ? [ // Changed from user to alum
            'name' => $comment->alum->name,
            'id' => $comment->alum->id
        ] : null,
        'content_item' => $comment->contentItem ? [
            'title' => $comment->contentItem->title,
            'id' => $comment->contentItem->id
        ] : null,
        'status' => $comment->status,
        'created_at' => $comment->created_at->format('M d, Y H:i')
    ];
});

        return Inertia::render('Admin/ContentModeration/Index', [
            'content_items' => $contentItems,
            'comments' => $comments,
            'filters' => $request->only([
                'search', 'type', 'status', 
                'comment_search', 'comment_status'
            ])
        ]);
    }

    public function updateContentStatus(Request $request, ContentItem $contentItem)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,restricted'
        ]);

        $contentItem->update([
            'status' => $request->status,
            'status_changed_at' => now(),
            'status_changed_by' => auth()->id()
        ]);

        return back()->with('success', 'Content status updated successfully');
    }

    public function updateCommentStatus(Request $request, Comment $comment)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,deleted'
        ]);

        $comment->update([
            'status' => $request->status,
            'status_changed_at' => now(),
            'status_changed_by' => auth()->id()
        ]);

        return back()->with('success', 'Comment status updated successfully');
    }

    public function bulkUpdateContentStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:content_items,id',
            'status' => 'required|in:pending,approved,rejected,restricted'
        ]);

        ContentItem::whereIn('id', $request->ids)->update([
            'status' => $request->status,
            'status_changed_at' => now(),
            'status_changed_by' => auth()->id()
        ]);

        return back()->with('success', 'Bulk content status updated successfully');
    }

    public function bulkUpdateCommentStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:comments,id',
            'status' => 'required|in:pending,approved,rejected,deleted'
        ]);

        Comment::whereIn('id', $request->ids)->update([
            'status' => $request->status,
            'status_changed_at' => now(),
            'status_changed_by' => auth()->id()
        ]);

        return back()->with('success', 'Bulk comment status updated successfully');
    }
}