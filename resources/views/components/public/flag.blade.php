{{--
    AJ&K flag as inline SVG (2:3): green field, four white stripes across the lower half,
    gold canton top-left, white crescent and five-point star on the upper fly.
    <x-public.flag class="w-40" />           plain
    <x-public.flag class="w-96" fabric />    with a soft cloth-fold shading overlay
--}}
@props(['fabric' => false, 'title' => null])

@php($id = 'flag'.\Illuminate\Support\Str::random(6))

<svg viewBox="0 0 300 200" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}
     @if ($title) role="img" aria-label="{{ $title }}" @else aria-hidden="true" focusable="false" @endif>
    <defs>
        <clipPath id="{{ $id }}-clip"><rect width="300" height="200" rx="4"/></clipPath>
        <mask id="{{ $id }}-crescent">
            <rect width="300" height="200" fill="black"/>
            <circle cx="196" cy="54" r="30" fill="white"/>
            <circle cx="207" cy="47" r="25" fill="black"/>
        </mask>
        @if ($fabric)
            <linearGradient id="{{ $id }}-folds" x1="0" y1="0" x2="1" y2="0.18">
                <stop offset="0" stop-color="#000" stop-opacity="0.18"/>
                <stop offset="0.18" stop-color="#fff" stop-opacity="0.16"/>
                <stop offset="0.36" stop-color="#000" stop-opacity="0.12"/>
                <stop offset="0.56" stop-color="#fff" stop-opacity="0.14"/>
                <stop offset="0.76" stop-color="#000" stop-opacity="0.14"/>
                <stop offset="1" stop-color="#fff" stop-opacity="0.08"/>
            </linearGradient>
        @endif
    </defs>

    <g clip-path="url(#{{ $id }}-clip)">
        <rect width="300" height="200" fill="#0b6638"/>
        @foreach ([100, 125, 150, 175] as $y)
            <rect y="{{ $y }}" width="300" height="12.5" fill="#fff"/>
        @endforeach
        <rect width="100" height="100" fill="#f2b705"/>
        <rect width="300" height="200" fill="#fff" mask="url(#{{ $id }}-crescent)"/>
        <polygon transform="translate(232 40) scale(1.25)" fill="#fff"
                 points="0,-10 2.25,-3.09 9.51,-3.09 3.63,1.18 5.88,8.09 0,3.82 -5.88,8.09 -3.63,1.18 -9.51,-3.09 -2.25,-3.09"/>
        @if ($fabric)
            <rect width="300" height="200" fill="url(#{{ $id }}-folds)"/>
        @endif
    </g>
</svg>
