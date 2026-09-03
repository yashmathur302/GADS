@php
    $variants = [
        'success' => ['bg-green-50 text-green-800 border-green-200', session('success') ?? session('status')],
        'error' => ['bg-red-50 text-red-800 border-red-200', session('error') ?? ($errors->any() ? __('Please correct the errors below.') : null)],
        'warning' => ['bg-yellow-50 text-yellow-800 border-yellow-200', session('warning')],
        'info' => ['bg-blue-50 text-blue-800 border-blue-200', session('info')],
    ];
@endphp

@foreach ($variants as $variant => [$classes, $message])
    @if ($message)
        <div
            x-data="{ show: true }"
            x-show="show"
            role="{{ $variant === 'error' ? 'alert' : 'status' }}"
            class="mb-4 flex items-start justify-between gap-4 rounded-md border px-4 py-3 {{ $classes }}"
        >
            <p class="text-sm">{{ $message }}</p>

            <button
                type="button"
                @click="show = false"
                class="shrink-0 text-current/70 hover:text-current focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-current rounded"
            >
                <span class="sr-only">{{ __('Dismiss') }}</span>
                &times;
            </button>
        </div>
    @endif
@endforeach
