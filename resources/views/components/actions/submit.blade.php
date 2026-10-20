@props([
    'action',
    'label' => 'Soumettre',
    'iconOnly' => false,
    'confirm' => null,
])

{{-- Soumission en arrière-plan quand la page a une zone actualisable (filtres conservés). --}}
<form action="{{ $action }}" method="POST" class="inline" data-sans-rechargement
    @if ($confirm) onsubmit="return confirm(@js($confirm));" @endif>
    @csrf
    <x-action variant="submit" icon="send" type="submit" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
</form>
