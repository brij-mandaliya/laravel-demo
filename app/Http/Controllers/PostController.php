<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::query()
            ->with('user')
            ->withCount('comments')
            ->latest()
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $post = $request->user()->posts()->create([
            'title' => $request->title,
            'slug' => Post::uniqueSlug($request->title),
            'body' => $request->body,
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post published.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        $comments = $post->comments()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $tree = $this->buildThread($comments);

        return view('posts.show', compact('post', 'tree'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->title = $request->title;
        $post->body = $request->body;

        if ($post->isDirty('title')) {
            $post->slug = Post::uniqueSlug($request->title, $post->id);
        }

        $post->save();

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted.');
    }

    /**
     * Build a nested comment tree preserved in chronological order.
     *
     * @return Collection<int, Comment>
     */
    private function buildThread(Collection $comments): Collection
    {
        $attach = function (Comment $comment) use ($comments, &$attach): Comment {
            $comment->children = $comments
                ->where('parent_id', $comment->id)
                ->values()
                ->map($attach);

            return $comment;
        };

        return $comments
            ->whereNull('parent_id')
            ->values()
            ->map($attach);
    }
}
