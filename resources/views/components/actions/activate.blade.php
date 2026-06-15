@props([
    'action',
    'label' => 'Activer',
    'iconOnly' => false,
    'confirm' => null,
])

<form action="{{ $action }}" method="POST" class="inline"
    @if ($confirm) onsubmit="return confirm(@js($confirm));" @endif>
    @csrf
    <x-action variant="activate" icon="power" type="submit" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
</form>
