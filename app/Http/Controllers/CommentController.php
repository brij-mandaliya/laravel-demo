<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreReplyRequest;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    /**
     * Store a new top-level comment on the post.
     */
    public function store(StoreCommentRequest $request, Post $post)
    {
        $request->user()->comments()->create([
            'post_id' => $post->id,
            'parent_id' => null,
            'depth' => 0,
            'body' => $request->body,
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Comment added.');
    }

    /**
     * Store a nested reply to an existing comment.
     */
    public function reply(StoreReplyRequest $request, Post $post, Comment $comment)
    {
        $request->user()->comments()->create([
            'post_id' => $post->id,
            'parent_id' => $comment->id,
            'depth' => $comment->depth + 1,
            'body' => $request->body,
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Reply added.');
    }

    /**
     * Remove the specified comment (cascade removes its replies).
     */
    public function destroy(Post $post, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
