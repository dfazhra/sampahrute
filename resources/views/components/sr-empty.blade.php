@props(['icon' => '🔍', 'title' => 'Tidak ada data yang cocok', 'text' => 'Ubah kata kunci atau filter untuk melihat data lain.'])
<div {{ $attributes->class(['empty-state']) }}>
    <span class="empty-icon" aria-hidden="true">{{ $icon }}</span>
    <strong>{{ $title }}</strong>
    <p>{{ $text }}</p>
    {{ $slot }}
</div>
