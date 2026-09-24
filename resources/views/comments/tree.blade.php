@if ($tree->isNotEmpty())
    <div class="space-y-4">
        @foreach ($tree as $comment)
            @include('comments.partials.comment', [
                'comment' => $comment,
                'post' => $post,
                'depth' => 0,
            ])
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-400">No comments yet.</p>
@endif