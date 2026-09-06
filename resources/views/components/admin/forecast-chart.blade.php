@props(['points', 'markerBid'])

@php
    $width = 640;
    $height = 220;
    $padLeft = 52;
    $padRight = 10;
    $padTop = 10;
    $padBottom = 28;
    $plotWidth = $width - $padLeft - $padRight;
    $plotHeight = $height - $padTop - $padBottom;

    $minBid = $points->min('bid');
    $maxBid = $points->max('bid');
    $maxClicks = max(1, $points->max('clicks'));

    $toX = fn (float $bid) => $padLeft + ($maxBid > $minBid ? ($bid - $minBid) / ($maxBid - $minBid) : 0) * $plotWidth;
    $toY = fn (int $clicks) => $padTop + $plotHeight - ($clicks / $maxClicks) * $plotHeight;

    $linePoints = $points->map(fn ($point) => round($toX($point->bid), 1).','.round($toY($point->clicks), 1))->implode(' ');
    $areaPoints = $linePoints
        .' '.round($toX($points->last()->bid), 1).','.($padTop + $plotHeight)
        .' '.round($toX($points->first()->bid), 1).','.($padTop + $plotHeight);

    $clampedMarkerBid = max($minBid, min($maxBid, $markerBid));
    $markerX = $toX($clampedMarkerBid);
    $nearestPoint = $points->sortBy(fn ($point) => abs($point->bid - $clampedMarkerBid))->first();
    $markerY = $toY($nearestPoint->clicks);
@endphp

<div class="overflow-x-auto">
    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-auto min-w-[420px]" role="img" aria-label="{{ __('Forecasted clicks across a range of max CPC bids') }}">
        @for ($i = 0; $i <= 4; $i++)
            <line
                x1="{{ $padLeft }}" y1="{{ $padTop + $plotHeight * $i / 4 }}"
                x2="{{ $width - $padRight }}" y2="{{ $padTop + $plotHeight * $i / 4 }}"
                stroke="#e5e7eb" stroke-width="1"
            />
        @endfor

        <polygon points="{{ $areaPoints }}" fill="#4f46e5" fill-opacity="0.08" />
        <polyline points="{{ $linePoints }}" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

        <line
            x1="{{ $markerX }}" y1="{{ $padTop }}" x2="{{ $markerX }}" y2="{{ $padTop + $plotHeight }}"
            stroke="#dc2626" stroke-width="1.5" stroke-dasharray="4 3"
        />
        <circle cx="{{ $markerX }}" cy="{{ $markerY }}" r="4" fill="#dc2626" />

        <text x="{{ $padLeft }}" y="{{ $height - 8 }}" font-size="10" fill="#6b7280">${{ number_format($minBid, 2) }}</text>
        <text x="{{ $width - $padRight }}" y="{{ $height - 8 }}" font-size="10" fill="#6b7280" text-anchor="end">${{ number_format($maxBid, 2) }}</text>
        <text x="{{ $padLeft - 6 }}" y="{{ $padTop + 4 }}" font-size="10" fill="#6b7280" text-anchor="end">{{ number_format($maxClicks) }}</text>
        <text x="{{ $padLeft - 6 }}" y="{{ $padTop + $plotHeight }}" font-size="10" fill="#6b7280" text-anchor="end">0</text>
    </svg>
</div>
<p class="mt-2 text-xs text-gray-500 text-center">
    {{ __('Forecasted clicks across a range of max CPC bids. The dashed line marks your current bid (:bid).', ['bid' => '$'.number_format($markerBid, 2)]) }}
</p>
