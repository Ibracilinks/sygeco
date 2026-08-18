<x-layouts::app title="{{ $mission->reference }}">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ \App\Models\Mission::TYPES[$mission->type] ?? 'Mission' }}</div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $mission->reference }}</h1>
                <p class="mt-2 max-w-4xl text-sm text-slate-600 dark:text-slate-300">{{ $mission->objet }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('edit_missions')
                    <a href="{{ route('missions.edit', $mission) }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Modifier
                    </a>
                @endcan
                @can('generate_missions_pdf')
                    <button type="button" onclick="{{ $mission->estExterieure() ? "window.exportMissionExterieurePDF(this, 'mission-exterieure-data')" : "window.exportMissionMemeVillePDF(this, 'mission-meme-ville-data')" }}"
                        class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        Générer le PDF
                    </button>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Participants</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $mission->participants->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Durée</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $mission->nombre_jours }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $mission->duree_texte }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $mission->estExterieure() ? 'Zone / Destination' : 'Carburant' }}</p>
                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">
                    {{ $mission->estExterieure() ? ($mission->zone_label ?? 'Zone à définir') : $mission->nombre_tickets_carburant.' ticket(s)' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $mission->estExterieure() ? ($mission->destination ?? 'Destination à préciser') : $mission->tickets_carburant_par_jour.' / jour' }}</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/70 dark:bg-sky-950/30">
                <p class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">Montant total</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-100">{{ number_format((float) $mission->montant_total, 0, ',', ' ') }}</p>
                <p class="text-xs text-sky-700/80 dark:text-sky-300/80">FCFA</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Aperçu du document</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Rendu adapté au type de mission, utilisé pour la génération du PDF.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $mission->statut === 'finalise' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' }}">
                    {{ \App\Models\Mission::STATUTS[$mission->statut] ?? $mission->statut }}
                </span>
            </div>

            @if ($mission->estMemeVille())
                <div class="mx-auto max-w-5xl border border-slate-300 bg-white p-6 text-[13px] leading-relaxed text-black shadow-sm">
                    <div class="border border-black px-4 py-3 text-center text-sm font-bold uppercase">
                        Budget relatif a l'ordre de mission n°{{ $mission->reference }}
                    </div>
                    <div class="mt-4 border border-black px-4 py-3">
                        <span class="font-bold uppercase">Objet de la mission:</span>
                        <span class="uppercase">{{ $mission->objet }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="border border-black px-4 py-3"><span class="font-bold uppercase">Durée:</span> {{ $mission->nombre_jours }} jour(s)</div>
                        <div class="border border-black px-4 py-3">{{ $mission->duree_texte }}</div>
                    </div>
                    <table class="mt-5 w-full border-collapse text-xs">
                        <thead>
                            <tr>
                                <th class="border border-black px-2 py-2">N°</th><th class="border border-black px-2 py-2 text-left">LIBELLE</th><th class="border border-black px-2 py-2">Nbre de pers.</th><th class="border border-black px-2 py-2">Mtant par jour</th><th class="border border-black px-2 py-2">Nbre de Jrs</th><th class="border border-black px-2 py-2">Frais de mission</th><th class="border border-black px-2 py-2">Mtant par nuitée</th><th class="border border-black px-2 py-2">Nbre de nuitées</th><th class="border border-black px-2 py-2">Indemnités de Mission</th><th class="border border-black px-2 py-2">TOTAL</th>
                            </tr>
                            <tr><th colspan="10" class="border border-black px-2 py-2 text-left font-bold uppercase">I- Frais et indemnites</th></tr>
                            <tr><th class="border border-black px-2 py-2"></th><th class="border border-black px-2 py-2 text-left">PRENOMS ET NOMS</th><th colspan="8" class="border border-black px-2 py-2"></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($mission->participants as $participant)
                                <tr>
                                    <td class="border border-black px-2 py-2 text-center">{{ $loop->iteration }}</td>
                                    <td class="border border-black px-2 py-2">{{ $participant->nom_complet }}</td>
                                    <td class="border border-black px-2 py-2 text-center">1</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_par_jour, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-center">{{ $mission->nombre_jours }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_par_jour * $mission->nombre_jours, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-center">-</td>
                                    <td class="border border-black px-2 py-2 text-center">-</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_par_jour * $mission->nombre_jours, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_par_jour * $mission->nombre_jours, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-semibold"><td colspan="9" class="border border-black px-2 py-2 text-right">SOUS-TOTAL 1</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_indemnites, 0, ',', ' ') }}</td></tr>
                            <tr><td colspan="10" class="border border-black px-2 py-2 text-left font-bold uppercase">II- Carburant</td></tr>
                            <tr><td colspan="5" class="border border-black px-2 py-2">NBRE DE JOURS OUVRABLE: {{ $mission->nombre_jours }}</td><td colspan="5" class="border border-black px-2 py-2">NOMBRE DE TICKET PAR JOUR: {{ $mission->tickets_carburant_par_jour }}</td></tr>
                            <tr><td colspan="9" class="border border-black px-2 py-2">MONTANT CARBURANT</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_carburant, 0, ',', ' ') }}</td></tr>
                            <tr class="font-semibold"><td colspan="9" class="border border-black px-2 py-2 text-right">SOUS-TOTAL 2</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_carburant, 0, ',', ' ') }}</td></tr>
                            <tr class="font-bold"><td colspan="9" class="border border-black px-2 py-2 text-right">TOTAL</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_total, 0, ',', ' ') }}</td></tr>
                        </tbody>
                    </table>
                    <div class="mt-3 border border-black px-4 py-2 text-center text-sm font-semibold uppercase">{{ $mission->tickets_carburant_en_lettres }}</div>
                    <div class="mt-6 text-right text-sm">{{ $mission->lieu_signature }} le {{ $mission->date_document?->format('d/m/Y') }}</div>
                    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                        @foreach ($mission->signataires as $signataire)
                            <div class="text-center">
                                <div class="min-h-10 text-sm font-semibold uppercase">{{ $signataire->libelle }}</div>
                                <div class="mt-20 text-sm font-bold uppercase">{{ $signataire->nom }}</div>
                                <div class="mt-1 text-xs">{{ $signataire->fonction }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mx-auto max-w-6xl border border-slate-300 bg-white p-6 text-[13px] leading-relaxed text-black shadow-sm">
                    <div class="border border-black px-4 py-3 text-center text-sm font-bold uppercase">
                        Projet de budget relatif a la levee d'ordre de mission n°{{ $mission->reference }}
                    </div>
                    <div class="mt-4 border border-black px-4 py-3"><span class="font-bold uppercase">Objet de la mission:</span> <span class="uppercase">{{ $mission->objet }}</span></div>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="border border-black px-4 py-3"><span class="font-bold uppercase">Durée mission:</span> {{ $mission->nombre_jours }} jour(s)</div>
                        <div class="border border-black px-4 py-3"><span class="font-bold uppercase">Destination:</span> {{ $mission->destination }}</div>
                    </div>
                    <div class="mt-3 border border-black px-4 py-3">DUREE : Du {{ $mission->date_depart?->format('d/m/Y') }} au {{ $mission->date_retour?->format('d/m/Y') }}</div>
                    <table class="mt-5 w-full border-collapse text-xs">
                        <thead>
                            <tr>
                                <th class="border border-black px-2 py-2">N°</th><th class="border border-black px-2 py-2 text-left">LIBELLE</th><th class="border border-black px-2 py-2">Nbre de pers.</th><th class="border border-black px-2 py-2">Mtant par jour</th><th class="border border-black px-2 py-2">Nbre de Jrs</th><th class="border border-black px-2 py-2">Frais de mission</th><th class="border border-black px-2 py-2">Mtant par nuitée</th><th class="border border-black px-2 py-2">Nbre de nuitées</th><th class="border border-black px-2 py-2">Indemnités de Mission</th><th class="border border-black px-2 py-2">SOUS TOTAL</th><th class="border border-black px-2 py-2">Majoration {{ number_format((float) $mission->zone_taux, 0) }}%</th><th class="border border-black px-2 py-2">TOTAL GENERAL</th>
                            </tr>
                            <tr><th colspan="12" class="border border-black px-2 py-2 text-left font-bold uppercase">I- Frais et indemnites</th></tr>
                            <tr><th></th><th class="border border-black px-2 py-2 text-left">PRENOMS ET NOMS</th><th colspan="10" class="border border-black px-2 py-2 text-left">Zone: {{ $mission->zone_label }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($mission->participants as $participant)
                                <tr>
                                    <td class="border border-black px-2 py-2 text-center">{{ $loop->iteration }}</td>
                                    <td class="border border-black px-2 py-2">{{ $participant->nom_complet }} @if($participant->categorie)<div class="text-[10px] text-slate-600">{{ \App\Models\Mission::CATEGORIES_EXTERIEURES[$participant->categorie]['label'] ?? $participant->categorie }}</div>@endif</td>
                                    <td class="border border-black px-2 py-2 text-center">1</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $participant->montant_par_jour, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-center">{{ $mission->nombre_jours }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $participant->montant_par_jour * $mission->nombre_jours, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $participant->montant_par_nuitee, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-center">{{ $participant->nombre_nuitees }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $participant->montant_par_nuitee * $participant->nombre_nuitees, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $participant->sous_total, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $participant->majoration_montant, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-2 py-2 text-right">{{ number_format((float) $participant->total_general, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-semibold"><td colspan="11" class="border border-black px-2 py-2 text-right">SOUS TOTAL 1</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) ($mission->montant_indemnites + $mission->montant_majoration), 0, ',', ' ') }}</td></tr>
                        </tbody>
                    </table>
                    <table class="mt-4 w-full border-collapse text-xs">
                        <tbody>
                            <tr><td colspan="9" class="border border-black px-2 py-2 font-bold uppercase">II- Autres frais</td></tr>
                            <tr><td class="border border-black px-2 py-2">FRAIS DE PARTICIPATION</td><td class="border border-black px-2 py-2 text-center">{{ $mission->frais_participation_nombre }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->frais_participation_unitaire, 0, ',', ' ') }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->frais_participation_total, 0, ',', ' ') }}</td></tr>
                            <tr><td class="border border-black px-2 py-2">FRAIS DE VISA</td><td class="border border-black px-2 py-2 text-center">{{ $mission->frais_visa_nombre }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->frais_visa_unitaire, 0, ',', ' ') }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->frais_visa_total, 0, ',', ' ') }}</td></tr>
                            <tr class="font-semibold"><td colspan="3" class="border border-black px-2 py-2 text-right">SOUS TOTAL 2</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_autres_frais, 0, ',', ' ') }}</td></tr>
                        </tbody>
                    </table>
                    <table class="mt-4 w-full border-collapse text-xs">
                        <tbody>
                            <tr><td colspan="9" class="border border-black px-2 py-2 font-bold uppercase">III- Billets d'avion</td></tr>
                            <tr><td class="border border-black px-2 py-2">CLASSE AFFAIRE</td><td class="border border-black px-2 py-2 text-center">{{ $mission->billets_affaire_nombre }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->billets_affaire_unitaire, 0, ',', ' ') }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->billets_affaire_total, 0, ',', ' ') }}</td></tr>
                            <tr><td class="border border-black px-2 py-2">CLASSE ECONOMIQUE</td><td class="border border-black px-2 py-2 text-center">{{ $mission->billets_economique_nombre }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->billets_economique_unitaire, 0, ',', ' ') }}</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->billets_economique_total, 0, ',', ' ') }}</td></tr>
                            <tr class="font-semibold"><td colspan="3" class="border border-black px-2 py-2 text-right">SOUS TOTAL 3</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_billets, 0, ',', ' ') }}</td></tr>
                            <tr class="font-bold"><td colspan="3" class="border border-black px-2 py-2 text-right">TOTAL</td><td class="border border-black px-2 py-2 text-right">{{ number_format((float) $mission->montant_total, 0, ',', ' ') }}</td></tr>
                        </tbody>
                    </table>
                    <div class="mt-3 border border-black px-4 py-2 text-center text-sm font-semibold uppercase">ARRETE A LA SOMME DE : {{ $mission->montant_total_en_lettres }}</div>
                    <div class="mt-6 text-right text-sm">{{ $mission->lieu_signature }}, le {{ $mission->date_document?->format('d/m/Y') }}</div>
                    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                        @foreach ($mission->signataires as $signataire)
                            <div class="text-center">
                                <div class="min-h-10 text-sm font-semibold uppercase">{{ $signataire->libelle }}</div>
                                <div class="mt-20 text-sm font-bold uppercase">{{ $signataire->nom }}</div>
                                <div class="mt-1 text-xs">{{ $signataire->fonction }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @php
        $missionBase = [
            'reference' => $mission->reference,
            'objet' => $mission->objet,
            'date_document' => $mission->date_document?->format('d/m/Y'),
            'date_depart' => $mission->date_depart?->format('d/m/Y'),
            'date_retour' => $mission->date_retour?->format('d/m/Y'),
            'nombre_jours' => $mission->nombre_jours,
            'lieu_signature' => $mission->lieu_signature,
            'signataires' => $mission->signataires->map(fn ($signataire) => [
                'libelle' => $signataire->libelle,
                'nom' => $signataire->nom,
                'fonction' => $signataire->fonction,
            ])->values()->all(),
        ];
    @endphp

    @if ($mission->estMemeVille())
        @php
            $missionPdf = $missionBase + [
                'tickets_par_jour' => $mission->tickets_carburant_par_jour,
                'nombre_tickets' => $mission->nombre_tickets_carburant,
                'montant_par_jour' => (float) $mission->montant_par_jour,
                'montant_ticket' => (float) $mission->montant_ticket_carburant,
                'montant_indemnites' => (float) $mission->montant_indemnites,
                'montant_carburant' => (float) $mission->montant_carburant,
                'montant_total' => (float) $mission->montant_total,
                'tickets_en_lettres' => $mission->tickets_carburant_en_lettres,
                'participants' => $mission->participants->map(fn ($participant) => ['nom' => $participant->nom_complet])->values()->all(),
            ];
        @endphp
        <script id="mission-meme-ville-data" type="application/json">@json($missionPdf, JSON_UNESCAPED_UNICODE)</script>
    @else
        @php
            $missionPdf = $missionBase + [
                'destination' => $mission->destination,
                'zone_label' => $mission->zone_label,
                'zone_taux' => (float) $mission->zone_taux,
                'montant_indemnites' => (float) $mission->montant_indemnites,
                'montant_majoration' => (float) $mission->montant_majoration,
                'montant_autres_frais' => (float) $mission->montant_autres_frais,
                'montant_billets' => (float) $mission->montant_billets,
                'montant_total' => (float) $mission->montant_total,
                'montant_total_en_lettres' => $mission->montant_total_en_lettres,
                'frais_participation_nombre' => $mission->frais_participation_nombre,
                'frais_participation_unitaire' => (float) $mission->frais_participation_unitaire,
                'frais_participation_total' => (float) $mission->frais_participation_total,
                'frais_visa_nombre' => $mission->frais_visa_nombre,
                'frais_visa_unitaire' => (float) $mission->frais_visa_unitaire,
                'frais_visa_total' => (float) $mission->frais_visa_total,
                'billets_affaire_nombre' => $mission->billets_affaire_nombre,
                'billets_affaire_unitaire' => (float) $mission->billets_affaire_unitaire,
                'billets_affaire_total' => (float) $mission->billets_affaire_total,
                'billets_economique_nombre' => $mission->billets_economique_nombre,
                'billets_economique_unitaire' => (float) $mission->billets_economique_unitaire,
                'billets_economique_total' => (float) $mission->billets_economique_total,
                'participants' => $mission->participants->map(fn ($participant) => [
                    'nom' => $participant->nom_complet,
                    'categorie' => $participant->categorie ? (\App\Models\Mission::CATEGORIES_EXTERIEURES[$participant->categorie]['label'] ?? $participant->categorie) : '',
                    'montant_par_jour' => (float) $participant->montant_par_jour,
                    'montant_par_nuitee' => (float) $participant->montant_par_nuitee,
                    'nombre_nuitees' => $participant->nombre_nuitees,
                    'sous_total' => (float) $participant->sous_total,
                    'majoration_montant' => (float) $participant->majoration_montant,
                    'total_general' => (float) $participant->total_general,
                ])->values()->all(),
            ];
        @endphp
        <script id="mission-exterieure-data" type="application/json">@json($missionPdf, JSON_UNESCAPED_UNICODE)</script>
    @endif
</x-layouts::app>
