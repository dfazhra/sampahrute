@props(['level' => 'sedang'])
<span {{ $attributes->class(['priority', 'priority-'.$level]) }} title="Prioritas {{ $level }}">{{ ucfirst($level) }}</span>
