@props(['groupes' => [], 'selected' => null])

@php
    // `selected` accepte un identifiant unique ou une liste (sélection multiple).
    $selectionnes = collect(is_array($selected) || $selected instanceof \Illuminate\Support\Collection ? $selected : [$selected])
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

@foreach ($groupes as $groupe => $entites)
    <optgroup label="{{ $groupe }}">
        @foreach ($entites as $entite)
            <option value="{{ $entite->id }}" @selected(in_array((string) $entite->id, $selectionnes, true))>
                {{ $entite->nom }}@if ($entite->type !== \App\Models\Departement::TYPE_DEPARTEMENT) ({{ $entite->typeLibelle() }})@endif
            </option>
        @endforeach
    </optgroup>
@endforeach
