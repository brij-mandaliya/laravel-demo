@php($depth = $depth ?? 0)

<div id="comment-{{ $comment->id }}"
     class="relative {{ $depth > 0 ? 'ms-4 sm:ms-6 border-s-2 border-gray-100 ps-4 sm:ps-6' : '' }}">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center gap-3 mb-2">
            <x-avatar :name="$comment->user->name" class="h-7 w-7 text-xs"/>

            <span class="text-sm font-medium text-gray-600">{{ $comment->user->name }}</span>
            <span class="text-sm text-gray-400">·</span>
            <time class="text-sm text-gray-400" datetime="{{ $comment->created_at->toIso8601String() }}">
                {{ $comment->created_at->diffForHumans() }}
            </time>

            @can('delete', $comment)
                <form method="POST" action="{{ route('comments.destroy', [$post, $comment]) }}"
                      class="ms-auto"
                      onsubmit="return confirm('Delete this comment and its replies?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:underline">
                        {{ __('Delete') }}
                    </button>
                </form>
            @endcan
        </div>

        <p class="text-gray-700 whitespace-pre-line">{{ $comment->body }}</p>
    </div>

    @auth
        @if ($comment->depth < \App\Models\Comment::MAX_NESTING_DEPTH)
            <form method="POST" action="{{ route('comments.reply', [$post, $comment]) }}" class="mt-2">
                @csrf
                <div class="flex items-start gap-2">
                    <textarea name="body" rows="2" required
                              placeholder="Reply to {{ $comment->user->name }}..."
                              class="block flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"></textarea>
                    <button type="submit" class="shrink-0 inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Reply') }}
                    </button>
                </div>
                <x-input-error :messages="$errors->get('parent_id')" class="mt-1" />
                <x-input-error :messages="$errors->get('body')" class="mt-1" />
            </form>
        @endif
    @endauth

    @if ($comment->children->isNotEmpty())
        @foreach ($comment->children as $child)
            @include('comments.partials.comment', [
                'comment' => $child,
                'post' => $post,
                'depth' => $depth + 1,
            ])
        @endforeach
    @endif
</div>