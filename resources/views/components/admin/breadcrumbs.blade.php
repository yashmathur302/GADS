@props(['items' => []])

@if (count($items) > 0)
    <nav aria-label="{{ __('Breadcrumb') }}" {{ $attributes->class(['mb-4 text-sm']) }}>
        <ol class="flex flex-wrap items-center gap-1 text-gray-500">
            @foreach ($items as $index => $item)
                <li class="flex items-center gap-1">
                    @if ($index > 0)
                        <span aria-hidden="true">/</span>
                    @endif

                    @if (! empty($item['url']) && $index < count($items) - 1)
                        <a href="{{ $item['url'] }}" class="hover:text-gray-700 hover:underline">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-gray-700 font-medium" @if ($index === count($items) - 1) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
