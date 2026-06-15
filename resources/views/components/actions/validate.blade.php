@props([
    'action',
    'label' => 'Valider',
    'iconOnly' => false,
    'confirm' => null,
])

<form action="{{ $action }}" method="POST" class="inline"
    @if ($confirm) onsubmit="return confirm(@js($confirm));" @endif>
    @csrf
    <x-action variant="validate" icon="check" type="submit" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
</form>
