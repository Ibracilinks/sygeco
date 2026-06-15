@props([
    'action',
    'label' => 'Supprimer',
    'iconOnly' => false,
    'confirm' => 'Confirmer la suppression ? Cette action est irréversible.',
])

<form action="{{ $action }}" method="POST" class="inline"
    onsubmit="return confirm(@js($confirm));">
    @csrf
    @method('DELETE')
    <x-action variant="delete" icon="trash" type="submit" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
</form>
