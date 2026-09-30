@php
    /** @var \App\Models\Departement $noeud */
    $niveau = $niveau ?? 0;
@endphp
<li>
    <div class="flex items-center gap-2 py-1">
        <span class="font-mono text-xs text-slate-400 dark:text-slate-500">{{ $noeud->code }}</span>
        <a href="{{ route('departements.show', $noeud) }}"
           class="text-sm font-medium text-slate-800 hover:underline dark:text-slate-100">{{ $noeud->nom }}</a>
        @include('pages.departements.partials.type-badge', ['type' => $noeud->type])
        @unless ($noeud->is_active)
            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Inactif</span>
        @endunless
    </div>

    @if ($noeud->enfantsRecursifs->isNotEmpty())
        <ul class="ml-3 border-l border-slate-200 pl-4 dark:border-slate-700">
            @foreach ($noeud->enfantsRecursifs as $enfant)
                @include('pages.departements.partials.arbre-noeud', ['noeud' => $enfant, 'niveau' => $niveau + 1])
            @endforeach
        </ul>
    @endif
</li>
