@props(['labels', 'values'])

@php
    $width = 640;
    $height = 200;
    $padLeft = 46;
    $padRight = 10;
    $padTop = 10;
    $padBottom = 24;
    $plotWidth = $width - $padLeft - $padRight;
    $plotHeight = $height - $padTop - $padBottom;

    $count = count($values);
    $maxValue = max(1, max($values));
    $barGap = 2;
    $barWidth = $count > 0 ? max(1, ($plotWidth / $count) - $barGap) : 0;

    $toX = fn (int $index) => $padLeft + $index * ($plotWidth / max(1, $count));
    $toHeight = fn (int $value) => ($value / $maxValue) * $plotHeight;

    // Avoid overlapping labels when there are many bars — show at most ~6.
    $labelStep = max(1, (int) ceil($count / 6));
@endphp

<div class="overflow-x-auto">
    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-auto min-w-[420px]" role="img" aria-label="{{ __('Historical average monthly searches') }}">
        @for ($i = 0; $i <= 4; $i++)
            <line
                x1="{{ $padLeft }}" y1="{{ $padTop + $plotHeight * $i / 4 }}"
                x2="{{ $width - $padRight }}" y2="{{ $padTop + $plotHeight * $i / 4 }}"
                stroke="#e5e7eb" stroke-width="1"
            />
        @endfor

        @foreach ($values as $index => $value)
            <rect
                x="{{ round($toX($index), 1) }}"
                y="{{ round($padTop + $plotHeight - $toHeight($value), 1) }}"
                width="{{ round($barWidth, 1) }}"
                height="{{ round($toHeight($value), 1) }}"
                fill="#4f46e5"
                fill-opacity="0.85"
                rx="1"
            >
                <title>{{ $labels[$index] }}: {{ number_format($value) }}</title>
            </rect>

            @if ($index % $labelStep === 0 || $index === $count - 1)
                <text
                    x="{{ round($toX($index) + $barWidth / 2, 1) }}"
                    y="{{ $height - 8 }}"
                    font-size="9"
                    fill="#6b7280"
                    text-anchor="middle"
                >{{ $labels[$index] }}</text>
            @endif
        @endforeach

        <text x="{{ $padLeft - 6 }}" y="{{ $padTop + 4 }}" font-size="10" fill="#6b7280" text-anchor="end">{{ number_format($maxValue) }}</text>
        <text x="{{ $padLeft - 6 }}" y="{{ $padTop + $plotHeight }}" font-size="10" fill="#6b7280" text-anchor="end">0</text>
    </svg>
</div>
<p class="mt-2 text-xs text-gray-500 text-center">
    {{ __('Combined average monthly searches across the keywords below, over the selected date range.') }}
</p>
