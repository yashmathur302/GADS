<x-guest-layout>
    <div class="text-center py-4">
        <p class="text-sm font-semibold text-indigo-600">{{ $code }}</p>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $title }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>

        @auth
            <a href="{{ route('discover.index') }}" class="mt-6 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                {{ __('Back to Discover New Keywords') }}
            </a>
        @else
            <a href="{{ route('login') }}" class="mt-6 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                {{ __('Back to login') }}
            </a>
        @endauth
    </div>
</x-guest-layout>
