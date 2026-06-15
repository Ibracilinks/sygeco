@props([
    'action',
    'label' => 'Dupliquer',
    'iconOnly' => false,
    'confirm' => null,
])

<form action="{{ $action }}" method="POST" class="inline"
    @if ($confirm) onsubmit="return confirm(@js($confirm));" @endif>
    @csrf
    <x-action variant="duplicate" icon="duplicate" type="submit" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
</form>
