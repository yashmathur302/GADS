@props(['column', 'sort', 'dir', 'form'])

@php
    $isActive = $sort === $column;
    $nextDir = $isActive && $dir === 'desc' ? 'asc' : 'desc';
@endphp

<th {{ $attributes->class(['px-4 py-2']) }}>
    <button
        type="submit"
        form="{{ $form }}"
        name="sort_spec"
        value="{{ $column }}:{{ $nextDir }}"
        class="inline-flex items-center gap-1 font-semibold uppercase tracking-wider text-gray-500 hover:text-gray-700"
    >
        {{ $slot }}
        <x-admin.icon name="{{ $isActive && $dir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3 {{ $isActive ? '' : 'opacity-30' }}" />
    </button>
</th>
