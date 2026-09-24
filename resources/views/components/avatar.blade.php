@php
    $palette = ['bg-red-500', 'bg-orange-500', 'bg-amber-500', 'bg-emerald-500', 'bg-teal-500', 'bg-sky-500', 'bg-indigo-500', 'bg-fuchsia-500'];
    $color = $palette[abs(crc32($name)) % count($palette)];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full text-white select-none '.$color]) }}>
    {{ strtoupper(mb_substr($name, 0, 1)) }}
</span>