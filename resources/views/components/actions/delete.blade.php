@props([
    'action',
    'label' => 'Supprimer',
    'iconOnly' => false,
    'confirm' => 'Confirmer la suppression ? Cette action est irréversible.',
    // Suppression en arrière-plan sur une page à zone actualisable (filtres conservés).
    'sansRechargement' => false,
])

<form action="{{ $action }}" method="POST" class="inline"
    @if ($sansRechargement) data-sans-rechargement @endif
    onsubmit="return confirm(@js($confirm));">
    @csrf
    @method('DELETE')
    <x-action variant="delete" icon="trash" type="submit" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
</form>
