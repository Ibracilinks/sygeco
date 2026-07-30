@props(['groupes' => [], 'selected' => null])

@foreach ($groupes as $groupe => $entites)
    <optgroup label="{{ $groupe }}">
        @foreach ($entites as $entite)
            <option value="{{ $entite->id }}" @selected((string) $selected === (string) $entite->id)>
                {{ $entite->nom }}@if ($entite->type !== \App\Models\Departement::TYPE_DEPARTEMENT) ({{ $entite->typeLibelle() }})@endif
            </option>
        @endforeach
    </optgroup>
@endforeach
