@props(['value' => 0])
@php $level = $value >= 85 ? 'high' : ($value >= 60 ? 'mid' : 'low'); @endphp
<span {{ $attributes->class(['fill-meter', 'fill-'.$level]) }} title="Perkiraan isi {{ $value }}%">
    <span class="fill-track"><span style="width: {{ $value }}%"></span></span>
    <span class="fill-value">{{ $value }}%</span>
</span>
