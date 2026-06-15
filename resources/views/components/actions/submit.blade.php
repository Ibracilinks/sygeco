@props([
    'action',
    'label' => 'Soumettre',
    'iconOnly' => false,
    'confirm' => null,
])

<form action="{{ $action }}" method="POST" class="inline"
    @if ($confirm) onsubmit="return confirm(@js($confirm));" @endif>
    @csrf
    <x-action variant="submit" icon="send" type="submit" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
</form>
