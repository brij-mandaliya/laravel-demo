<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Posts') }}
            </h2>

            @auth
                <a href="{{ route('posts.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('New Post') }}
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @forelse ($posts as $post)
                <article class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <a href="{{ route('posts.show', $post) }}" class="block p-6 hover:bg-gray-50">
                        <div class="flex items-center gap-3 mb-2">
                            <x-avatar :name="$post->user->name" class="h-8 w-8 text-sm"/>
                            <span class="text-sm font-medium text-gray-500">{{ $post->user->name }}</span>
                            <span class="text-sm text-gray-400">·</span>
                            <time class="text-sm text-gray-400" datetime="{{ $post->created_at->toIso8601String() }}">
                                {{ $post->created_at->diffForHumans() }}
                            </time>
                            <span class="text-sm text-gray-400 ms-auto">{{ trans_choice(':count comment|:count comments', $post->comments_count) }}</span>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900">{{ $post->title }}</h3>
                        <p class="mt-1 text-gray-600">{{ Str::limit($post->body, 220) }}</p>
                    </a>
                </article>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-500">
                        No posts yet. @auth <a href="{{ route('posts.create') }}" class="text-indigo-600 hover:underline">Write the first one</a>. @endauth
                    </div>
                </div>
            @endforelse

            <div>
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>