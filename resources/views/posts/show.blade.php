<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $post->title }}
            </h2>

            @can('update', $post)
                <div class="flex items-center gap-3">
                    <a href="{{ route('posts.edit', $post) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Edit') }}
                    </a>

                    <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post? All comments will be removed.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Delete') }}
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <article class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <x-avatar :name="$post->user->name" class="h-8 w-8 text-sm"/>
                        <div>
                            <div class="text-sm font-medium text-gray-700">{{ $post->user->name }}</div>
                            <time class="text-sm text-gray-400" datetime="{{ $post->created_at->toIso8601String() }}">
                                {{ $post->created_at->diffForHumans() }}
                            </time>
                        </div>
                    </div>

                    <div class="prose prose-gray text-gray-800 whitespace-pre-line">
                        {{ $post->body }}
                    </div>
                </div>
            </article>

            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg" aria-labelledby="comments-heading">
                <div class="p-6">
                    <h3 id="comments-heading" class="font-semibold text-lg text-gray-800 mb-4">
                        {{ __('Comments') }}
                    </h3>

                    @auth
                        <form method="POST" action="{{ route('comments.store', $post) }}" class="mb-6">
                            @csrf
                            <label for="comment-body" class="block font-medium text-sm text-gray-700">{{ __('Add a comment') }}</label>
                            <textarea id="comment-body" name="body" rows="3" required
                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      placeholder="Share your thoughts..."></textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                            <div class="mt-3">
                                <x-primary-button>{{ __('Comment') }}</x-primary-button>
                            </div>
                        </form>
                    @else
                        <p class="mb-6 text-sm text-gray-500">
                            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Log in</a> to join the discussion.
                        </p>
                    @endauth

                    @include('comments.tree', ['tree' => $tree, 'post' => $post])
                </div>
            </section>
        </div>
    </div>
</x-app-layout>