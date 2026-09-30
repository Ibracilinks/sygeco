{{-- Services demandeurs : plusieurs structures peuvent porter la même mission. --}}
@php($structuresSelectionnees = collect(old('structures_demandeuses', $structuresDemandeuses ?? []))->map(fn ($id) => (string) $id)->all())

<div>
    <label for="structures_demandeuses" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Services demandeurs</label>
    <select id="structures_demandeuses" name="structures_demandeuses[]" multiple size="4" data-ux-enhance data-placeholder="Choisir un ou plusieurs services…"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        @foreach ($departements as $departement)
            <option value="{{ $departement->id }}" @selected(in_array((string) $departement->id, $structuresSelectionnees, true))>{{ $departement->nom }}</option>
        @endforeach
    </select>
    @error('structures_demandeuses')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    @error('structures_demandeuses.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
