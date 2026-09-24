<x-layouts::app title="{{ $mission->reference }}">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ \App\Models\Mission::TYPES[$mission->type] ?? 'Mission' }}</div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $mission->reference }}</h1>
                <p class="mt-2 max-w-4xl text-sm text-slate-600 dark:text-slate-300">{{ $mission->objet }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ $mission->departements->isNotEmpty() ? $mission->departements->pluck('nom')->join(', ') : ($mission->departement?->nom ?? 'Sans structure') }}</span>
                    @if ($mission->code_budgetaire)
                        <span aria-hidden="true">•</span>
                        <span>Code budgétaire : {{ $mission->code_budgetaire }}</span>
                    @endif
                    @if ($mission->point_depart)
                        <span aria-hidden="true">•</span>
                        <span>Départ de {{ $mission->point_depart }}</span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('edit_missions')
                    @if ($mission->statut === 'brouillon')
                        <form action="{{ route('missions.finaliser', $mission) }}" method="POST"
                            onsubmit="return confirm('Finaliser cette mission ? Le document sera considéré comme arrêté.');">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500">
                                Finaliser
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('missions.edit', $mission) }}" class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Modifier
                    </a>
                @endcan
                @can('generate_missions_pdf')
                    <button type="button" onclick="{{ $mission->estExterieure() ? "window.exportMissionExterieurePDF(this, 'mission-exterieure-data')" : ($mission->estRegionale() ? "window.exportMissionRegionPDF(this, 'mission-region-data')" : "window.exportMissionMemeVillePDF(this, 'mission-meme-ville-data')") }}"
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

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
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
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $mission->estExterieure() ? 'Zone / Destination' : ($mission->estRegionale() ? 'Région / Étapes' : 'Carburant') }}</p>
                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">
                    {{ $mission->estExterieure() ? ($mission->zone_label ?? 'Zone à définir') : ($mission->estRegionale() ? ($mission->destination ?? 'Région à préciser') : $mission->nombre_tickets_carburant.' ticket(s)') }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $mission->estExterieure() ? ($mission->destination ?? 'Destination à préciser') : ($mission->estRegionale() ? $mission->etapes->count().' étape(s)' : $mission->tickets_carburant_par_jour.' / jour') }}</p>
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
                {{-- Reproduction du budget officiel « même ville » : mêmes colonnes que le modèle CANAM,
                     les colonnes monétaires restant vides puisque seul le carburant est doté. --}}
                <div class="mx-auto max-w-5xl border border-slate-300 bg-white p-8 text-[13px] leading-relaxed text-black shadow-sm">
                    <div class="grid grid-cols-2 gap-6 text-center text-[11px] font-bold uppercase">
                        <div>
                            <p>Ministère de la Santé et du Développement Social</p>
                            <p class="my-0.5">------------------------</p>
                            <p>Caisse Nationale d'Assurance Maladie</p>
                            <img src="{{ asset('logo_canam.png') }}" alt="CANAM" class="mx-auto mt-2 h-16 w-16 object-contain">
                        </div>
                        <div>
                            <p>République du Mali</p>
                            <p class="my-0.5">----------------------</p>
                            <p>Un Peuple – Un But – Une Foi</p>
                        </div>
                    </div>

                    <h3 class="mt-6 text-center text-base font-bold uppercase underline">
                        Budget relatif a l'ordre de mission n°{{ $mission->reference }}
                    </h3>

                    <p class="mt-5 text-justify text-[12px] font-bold uppercase underline">
                        Objet de la mission: {{ $mission->objet }}
                    </p>

                    <div class="mt-5 text-[12px]">
                        <p><span class="font-bold uppercase underline">Durée :</span> <span class="font-bold">{{ $mission->nombre_jours }} jour(s) ouvrable(s)</span></p>
                        <p>{{ $mission->date_depart?->format('d/m/Y') }} au {{ $mission->date_retour?->format('d/m/Y') }}</p>
                    </div>

                    <table class="mt-4 w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="font-bold">
                                <th class="border border-black px-1 py-1">N°</th>
                                <th class="border border-black px-1 py-1">LIBELLE</th>
                                <th class="border border-black px-1 py-1">Nbre de pers.</th>
                                <th class="border border-black px-1 py-1">Mtant par jour</th>
                                <th class="border border-black px-1 py-1">Nbre de Jrs</th>
                                <th class="border border-black px-1 py-1">Frais de mission</th>
                                <th class="border border-black px-1 py-1">Mtant par nuitée</th>
                                <th class="border border-black px-1 py-1">Nbre de nuitées</th>
                                <th class="border border-black px-1 py-1">Indemnités de Mission</th>
                                <th class="border border-black px-1 py-1">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="10" class="border border-black px-1 py-1 text-center font-bold uppercase">I- Frais et indemnites</td></tr>
                            <tr><td class="border border-black px-1 py-1"></td><td class="border border-black px-1 py-1 text-center font-bold uppercase">Prénoms et noms</td><td colspan="8" class="border border-black px-1 py-1"></td></tr>
                            @foreach ($mission->participants as $participant)
                                <tr>
                                    <td class="border border-black px-1 py-1 text-center">{{ $loop->iteration }}</td>
                                    <td class="border border-black px-1 py-1 italic">{{ $participant->nom_complet }}</td>
                                    <td class="border border-black px-1 py-1 text-center font-bold">1</td>
                                    <td class="border border-black px-1 py-1"></td>
                                    <td class="border border-black px-1 py-1 text-center font-bold">{{ $mission->nombre_jours }}</td>
                                    <td class="border border-black px-1 py-1"></td>
                                    <td class="border border-black px-1 py-1"></td>
                                    <td class="border border-black px-1 py-1"></td>
                                    <td class="border border-black px-1 py-1"></td>
                                    <td class="border border-black px-1 py-1"></td>
                                </tr>
                            @endforeach
                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center uppercase">Sous-total 1</td><td class="border border-black px-1 py-1 text-right">-</td></tr>
                            <tr><td colspan="10" class="border border-black px-1 py-1 text-center font-bold uppercase">II- Carburant</td></tr>
                            <tr class="font-bold">
                                <td colspan="4" class="border border-black px-1 py-1 text-center uppercase">Nbre de jours ouvrable</td>
                                <td colspan="6" class="border border-black px-1 py-1 text-center uppercase">Nombre de ticket par jour</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Tickets de carburant</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-center">{{ $mission->nombre_jours }}</td>
                                <td colspan="5" class="border border-black px-1 py-1 text-center">{{ $mission->tickets_carburant_par_jour }}</td>
                                <td class="border border-black px-1 py-1 text-center font-bold">{{ $mission->nombre_tickets_carburant }}</td>
                            </tr>
                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center uppercase">Sous-total 2</td><td class="border border-black px-1 py-1 text-center">{{ $mission->nombre_tickets_carburant }}</td></tr>
                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center text-base uppercase">Total</td><td class="border border-black bg-sky-100 px-1 py-1 text-center">{{ $mission->nombre_tickets_carburant }}</td></tr>
                        </tbody>
                    </table>

                    <p class="mt-4 text-center text-[12px] font-bold uppercase">{{ $mission->tickets_carburant_en_lettres }}</p>

                    <p class="mt-6 text-right text-[12px]">{{ $mission->lieu_signature }} le ....................</p>

                    <div class="mt-6 grid gap-6" style="grid-template-columns: repeat({{ max(1, $mission->signataires->count()) }}, minmax(0, 1fr));">
                        @foreach ($mission->signataires as $signataire)
                            <div class="text-center text-[11px]">
                                <div class="min-h-8 font-bold uppercase">{{ $signataire->libelle }}</div>
                                <div class="mt-16 font-bold uppercase underline">{{ $signataire->nom }}</div>
                                <div class="mt-0.5 italic">{{ $signataire->fonction }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($mission->estExterieure())
                {{-- Reproduction du projet de budget officiel « mission à l'extérieur ». --}}
                <div class="mx-auto max-w-6xl border border-slate-300 bg-white p-8 text-[13px] leading-relaxed text-black shadow-sm">
                    <div class="grid grid-cols-2 gap-6 text-center text-[11px] font-bold uppercase">
                        <div>
                            <p>Ministère de la Santé et du Développement Social</p>
                            <p class="my-0.5">------------------------</p>
                            <p>Caisse Nationale d'Assurance Maladie</p>
                            <img src="{{ asset('logo_canam.png') }}" alt="CANAM" class="mx-auto mt-2 h-16 w-16 object-contain">
                        </div>
                        <div>
                            <p>République du Mali</p>
                            <p class="my-0.5">----------------------</p>
                            <p>Un Peuple – Un But – Une Foi</p>
                        </div>
                    </div>

                    <h3 class="mt-6 text-center text-base font-bold uppercase underline">
                        Projet de budget relatif a la levee d'ordre de mission n°{{ $mission->reference }}
                    </h3>

                    <p class="mt-5 text-center text-[12px] font-bold uppercase underline">Objet de la mission : {{ $mission->objet }}</p>

                    <div class="mt-5 flex items-start justify-between text-[12px]">
                        <div>
                            <p><span class="font-bold uppercase underline">Durée mission :</span> <span class="font-bold">{{ $mission->nombre_jours }} jour(s)</span></p>
                            <p>Durée : Du {{ $mission->date_depart?->format('d/m/Y') }} au {{ $mission->date_retour?->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">{{ $mission->destination }}</p>
                            <p class="text-[11px]">{{ $mission->zone_label }}</p>
                        </div>
                    </div>

                    <table class="mt-4 w-full border-collapse text-[10px]">
                        <thead>
                            <tr class="font-bold">
                                <th class="border border-black px-1 py-1">N°</th>
                                <th class="border border-black px-1 py-1">LIBELLE</th>
                                <th class="border border-black px-1 py-1">Nbre de pers.</th>
                                <th class="border border-black px-1 py-1">Mtant par jour</th>
                                <th class="border border-black px-1 py-1">Nbre de Jrs</th>
                                <th class="border border-black px-1 py-1">Frais de mission</th>
                                <th class="border border-black px-1 py-1">Mtant par nuitée</th>
                                <th class="border border-black px-1 py-1">Nbre de nuitées</th>
                                <th class="border border-black px-1 py-1">Indemnités de Mission</th>
                                <th class="border border-black px-1 py-1">SOUS TOTAL</th>
                                <th class="border border-black px-1 py-1">Taux de majoration par zone {{ number_format((float) $mission->zone_taux, 0) }}%</th>
                                <th class="border border-black px-1 py-1">TOTAL GENERAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="12" class="border border-black px-1 py-1 text-center font-bold uppercase">I- Frais et indemnites</td></tr>
                            <tr><td class="border border-black px-1 py-1"></td><td class="border border-black px-1 py-1 text-center font-bold uppercase">Prénoms et noms</td><td colspan="10" class="border border-black px-1 py-1"></td></tr>
                            @foreach ($mission->participants as $participant)
                                <tr>
                                    <td class="border border-black px-1 py-1 text-center">{{ $loop->iteration }}</td>
                                    <td class="border border-black px-1 py-1 italic">
                                        {{ $participant->nom_complet }}
                                    </td>
                                    <td class="border border-black px-1 py-1 text-center">1</td>
                                    <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $participant->montant_par_jour, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-1 py-1 text-center">{{ $mission->nombre_jours }}</td>
                                    <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $participant->montant_par_jour * $mission->nombre_jours, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $participant->montant_par_nuitee, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-1 py-1 text-center">{{ $participant->nombre_nuitees }}</td>
                                    <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $participant->montant_par_nuitee * $participant->nombre_nuitees, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $participant->sous_total, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $participant->majoration_montant, 0, ',', ' ') }}</td>
                                    <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $participant->total_general, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-bold"><td colspan="11" class="border border-black px-1 py-1 text-center uppercase">Sous total 1</td><td class="border border-black px-1 py-1 text-right">{{ number_format((float) ($mission->montant_indemnites + $mission->montant_majoration), 0, ',', ' ') }}</td></tr>

                            <tr><td colspan="12" class="border border-black px-1 py-1 text-center font-bold uppercase">II- Autres frais</td></tr>
                            <tr class="font-bold">
                                <td colspan="2" class="border border-black px-1 py-1"></td>
                                <td colspan="4" class="border border-black px-1 py-1 text-center uppercase">Nbre de personnes</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center uppercase">Montant par personne</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center uppercase">Montant total</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Frais de participation</td>
                                <td colspan="4" class="border border-black px-1 py-1 text-center">{{ $mission->frais_participation_nombre }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->frais_participation_unitaire, 0, ',', ' ') }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->frais_participation_total, 0, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Frais de visa</td>
                                <td colspan="4" class="border border-black px-1 py-1 text-center">{{ $mission->frais_visa_nombre }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->frais_visa_unitaire, 0, ',', ' ') }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->frais_visa_total, 0, ',', ' ') }}</td>
                            </tr>
                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center uppercase">Sous total 2</td><td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_autres_frais, 0, ',', ' ') }}</td></tr>

                            <tr><td colspan="12" class="border border-black px-1 py-1 text-center font-bold uppercase">III- Billets d'avion</td></tr>
                            <tr class="font-bold">
                                <td colspan="2" class="border border-black px-1 py-1"></td>
                                <td colspan="4" class="border border-black px-1 py-1 text-center uppercase">Nbre de personnes</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center uppercase">Montant unitaire</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center uppercase">Montant total</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Classe affaire</td>
                                <td colspan="4" class="border border-black px-1 py-1 text-center">{{ $mission->billets_affaire_nombre }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->billets_affaire_unitaire, 0, ',', ' ') }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->billets_affaire_total, 0, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Classe économique</td>
                                <td colspan="4" class="border border-black px-1 py-1 text-center">{{ $mission->billets_economique_nombre }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->billets_economique_unitaire, 0, ',', ' ') }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->billets_economique_total, 0, ',', ' ') }}</td>
                            </tr>
                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center uppercase">Sous total 3</td><td colspan="3" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_billets, 0, ',', ' ') }}</td></tr>
                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center text-[12px] uppercase">Total</td><td colspan="3" class="border border-black bg-sky-100 px-1 py-1 text-right text-[12px]">{{ number_format((float) $mission->montant_total, 0, ',', ' ') }}</td></tr>
                        </tbody>
                    </table>

                    <p class="mt-4 text-center text-[12px] font-bold uppercase">
                        {{-- Le libellé en lettres porte déjà « FRANCS CFA ». --}}
                        Arrete a la somme de : {{ $mission->montant_total_en_lettres }}
                    </p>

                    <p class="mt-6 text-right text-[12px]">{{ $mission->lieu_signature }} le ....................</p>

                    <div class="mt-6 grid gap-6" style="grid-template-columns: repeat({{ max(1, $mission->signataires->count()) }}, minmax(0, 1fr));">
                        @foreach ($mission->signataires as $signataire)
                            <div class="text-center text-[11px]">
                                <div class="min-h-8 font-bold uppercase">{{ $signataire->libelle }}</div>
                                <div class="mt-16 font-bold uppercase underline">{{ $signataire->nom }}</div>
                                <div class="mt-0.5 italic">{{ $signataire->fonction }}</div>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-10 text-center text-[12px] font-bold uppercase">Visa du contrôleur financier</p>
                </div>
            @else
                {{-- Reproduction du budget officiel « missions à l'intérieur du pays ». --}}
                @php
                    $repartition = $mission->repartitionRegionale();
                @endphp
                <div class="mx-auto max-w-6xl border border-slate-300 bg-white p-8 text-[13px] leading-relaxed text-black shadow-sm">
                    <div class="grid grid-cols-2 gap-6 text-center text-[11px] font-bold uppercase">
                        <div>
                            <p>Ministère de la Santé et du Développement Social</p>
                            <p class="my-0.5">------------------------</p>
                            <p>Caisse Nationale d'Assurance Maladie</p>
                            <img src="{{ asset('logo_canam.png') }}" alt="CANAM" class="mx-auto mt-2 h-16 w-16 object-contain">
                        </div>
                        <div>
                            <p>République du Mali</p>
                            <p class="my-0.5">----------------------</p>
                            <p>Un Peuple – Un But – Une Foi</p>
                        </div>
                    </div>

                    <h3 class="mt-6 text-center text-base font-bold uppercase underline">
                        Budget relatif a l'ordre de mission n°{{ $mission->reference }}
                    </h3>

                    <p class="mt-5 text-[12px] font-bold uppercase underline">Objet de la mission : {{ $mission->objet }}</p>

                    <div class="mt-5 flex items-start justify-between text-[12px]">
                        <div>
                            <p><span class="font-bold uppercase underline">Durée :</span> <span class="font-bold">{{ $mission->nombre_jours }} jours</span></p>
                            <p>Date : Du {{ $mission->date_depart?->format('d/m/Y') }} au {{ $mission->date_retour?->format('d/m/Y') }}</p>
                        </div>
                        <p class="font-bold">{{ $mission->destination }}</p>
                    </div>

                    <table class="mt-4 w-full border-collapse text-[10px]">
                        <thead>
                            <tr class="font-bold">
                                <th class="border border-black px-1 py-1">N°</th>
                                <th class="border border-black px-1 py-1">LIBELLE</th>
                                <th class="border border-black px-1 py-1">Nbre de pers.</th>
                                <th class="border border-black px-1 py-1">Mtant par jour</th>
                                <th class="border border-black px-1 py-1">Nbre de Jrs</th>
                                <th class="border border-black px-1 py-1">Frais de mission</th>
                                <th class="border border-black px-1 py-1">Mtant par nuitée</th>
                                <th class="border border-black px-1 py-1">Nbre de nuitées</th>
                                <th class="border border-black px-1 py-1">Indemnités de Mission</th>
                                <th class="border border-black px-1 py-1">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="10" class="border border-black px-1 py-1 text-center font-bold uppercase">I- Frais et indemnites</td></tr>
                            <tr><td class="border border-black px-1 py-1"></td><td class="border border-black px-1 py-1 text-center font-bold uppercase">Prénom et nom</td><td colspan="8" class="border border-black px-1 py-1"></td></tr>

                            @forelse ($repartition as $indexGroupe => $groupe)
                                <tr><td colspan="10" class="border border-black px-1 py-1 text-center font-bold uppercase">{{ $indexGroupe + 1 }}- {{ $groupe['libelle'] }}</td></tr>
                                @foreach ($groupe['lignes'] as $indexLigne => $ligne)
                                    <tr>
                                        <td class="border border-black px-1 py-1 text-center">{{ $indexLigne + 1 }}</td>
                                        <td class="border border-black px-1 py-1 italic">{{ $ligne['nom'] }}</td>
                                        <td class="border border-black px-1 py-1 text-center">1</td>
                                        <td class="border border-black px-1 py-1 text-right">{{ number_format($ligne['montant_par_jour'], 0, ',', ' ') }}</td>
                                        <td class="border border-black px-1 py-1 text-center">{{ $ligne['jours'] }}</td>
                                        <td class="border border-black px-1 py-1 text-right">{{ number_format($ligne['frais_mission'], 0, ',', ' ') }}</td>
                                        <td class="border border-black px-1 py-1 text-right">{{ number_format($ligne['montant_par_nuitee'], 0, ',', ' ') }}</td>
                                        <td class="border border-black px-1 py-1 text-center">{{ $ligne['nuitees'] }}</td>
                                        <td class="border border-black px-1 py-1 text-right">{{ number_format($ligne['indemnites'], 0, ',', ' ') }}</td>
                                        <td class="border border-black px-1 py-1 text-right">{{ number_format($ligne['total'], 0, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center uppercase">Sous total {{ $indexGroupe + 1 }}</td><td class="border border-black px-1 py-1 text-right">{{ number_format($groupe['sous_total'], 0, ',', ' ') }}</td></tr>
                            @empty
                                <tr><td colspan="10" class="border border-black px-1 py-1 text-center text-slate-500">Aucune étape renseignée</td></tr>
                            @endforelse

                            <tr><td colspan="10" class="border border-black px-1 py-1 text-center font-bold uppercase">II- Carburant</td></tr>
                            <tr class="font-bold">
                                <td colspan="2" class="border border-black px-1 py-1"></td>
                                <td colspan="2" class="border border-black px-1 py-1 text-center uppercase">Nbre de véhicules</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center uppercase">Qté de carburant / nombre jours</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-center uppercase">Prix du litre / prix unitaire</td>
                                <td class="border border-black px-1 py-1 text-center uppercase">Montant</td>
                            </tr>
                            @php
                                $litresTrajet = (float) $mission->distance_totale_km * (float) $mission->consommation_aux_cent / 100;
                                $litresVille = (float) $mission->litres_par_jour_ville * max(1, (int) $mission->nombre_jours);
                            @endphp
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Montant carburant trajet</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-center">{{ $mission->nombre_vehicules }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center">{{ number_format($litresTrajet, 0, ',', ' ') }}</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->prix_litre_carburant, 0, ',', ' ') }}</td>
                                <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_carburant_trajet, 0, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Montant carburant ville</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-center">{{ $mission->nombre_vehicules }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center">{{ number_format($litresVille, 0, ',', ' ') }}</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->prix_litre_carburant, 0, ',', ' ') }}</td>
                                <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_carburant_ville, 0, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Frais location véhicule</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-center">{{ $mission->nombre_vehicules }}</td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center">{{ $mission->location_vehicule_jours }} jour(s)</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->location_vehicule_tarif, 0, ',', ' ') }}</td>
                                <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_location_vehicule, 0, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-black px-1 py-1 font-bold uppercase">Billet d'avion</td>
                                <td colspan="2" class="border border-black px-1 py-1"></td>
                                <td colspan="3" class="border border-black px-1 py-1 text-center">{{ $mission->billets_economique_nombre }} personne(s)</td>
                                <td colspan="2" class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->billets_economique_unitaire, 0, ',', ' ') }}</td>
                                <td class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_billets, 0, ',', ' ') }}</td>
                            </tr>
                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center uppercase">Sous-total 2</td><td class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_carburant + (float) $mission->montant_location_vehicule + (float) $mission->montant_billets, 0, ',', ' ') }}</td></tr>

                            <tr><td colspan="10" class="border border-black px-1 py-1 text-center font-bold uppercase">III- Péages</td></tr>
                            <tr><td colspan="9" class="border border-black px-1 py-1 font-bold uppercase">Péages</td><td class="border border-black px-1 py-1 text-right">{{ number_format((float) $mission->montant_peages, 0, ',', ' ') }}</td></tr>

                            <tr class="font-bold"><td colspan="9" class="border border-black px-1 py-1 text-center text-[12px] uppercase">Total général</td><td class="border border-black bg-sky-100 px-1 py-1 text-right text-[12px]">{{ number_format((float) $mission->montant_total, 0, ',', ' ') }}</td></tr>
                        </tbody>
                    </table>

                    <p class="mt-4 text-center text-[12px] font-bold uppercase">
                        Arrete a la somme de : {{ $mission->montant_total_en_lettres }}
                    </p>

                    <p class="mt-6 text-right text-[12px]">{{ $mission->lieu_signature }} le ....................</p>

                    <div class="mt-6 grid gap-6" style="grid-template-columns: repeat({{ max(1, $mission->signataires->count()) }}, minmax(0, 1fr));">
                        @foreach ($mission->signataires as $signataire)
                            <div class="text-center text-[11px]">
                                <div class="min-h-8 font-bold uppercase">{{ $signataire->libelle }}</div>
                                <div class="mt-16 font-bold uppercase underline">{{ $signataire->nom }}</div>
                                <div class="mt-0.5 italic">{{ $signataire->fonction }}</div>
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
                'nombre_personnes' => $mission->nombre_personnes,
                'tickets_en_lettres' => $mission->tickets_carburant_en_lettres,
                'participants' => $mission->participants->map(fn ($participant) => ['nom' => $participant->nom_complet])->values()->all(),
            ];
        @endphp
        <script id="mission-meme-ville-data" type="application/json">@json($missionPdf, JSON_UNESCAPED_UNICODE)</script>
        {{-- Logo repris tel quel dans le PDF (même origine : lisible par le canvas). --}}
        <img id="mission-logo" src="{{ asset('logo_canam.png') }}" alt="" class="hidden" aria-hidden="true">
    @elseif ($mission->estExterieure())
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
        <img id="mission-logo" src="{{ asset('logo_canam.png') }}" alt="" class="hidden" aria-hidden="true">
    @else
        @php
            $missionPdf = $missionBase + [
                'destination' => $mission->destination,
                'montant_total' => (float) $mission->montant_total,
                'montant_total_en_lettres' => $mission->montant_total_en_lettres,
                // Blocs « indemnités cercles / régions » du document officiel.
                'groupes' => collect($mission->repartitionRegionale())->map(fn ($groupe) => [
                    'libelle' => $groupe['libelle'],
                    'sous_total' => $groupe['sous_total'],
                    'lignes' => $groupe['lignes'],
                ])->all(),
                'nombre_vehicules' => $mission->nombre_vehicules,
                'litres_trajet' => round((float) $mission->distance_totale_km * (float) $mission->consommation_aux_cent / 100, 2),
                'litres_ville' => round((float) $mission->litres_par_jour_ville * max(1, (int) $mission->nombre_jours), 2),
                'prix_litre' => (float) $mission->prix_litre_carburant,
                'montant_carburant_trajet' => (float) $mission->montant_carburant_trajet,
                'montant_carburant_ville' => (float) $mission->montant_carburant_ville,
                'location_jours' => $mission->location_vehicule_jours,
                'location_tarif' => (float) $mission->location_vehicule_tarif,
                'montant_location' => (float) $mission->montant_location_vehicule,
                'billets_nombre' => $mission->billets_economique_nombre,
                'billets_unitaire' => (float) $mission->billets_economique_unitaire,
                'montant_billets' => (float) $mission->montant_billets,
                'montant_peages' => (float) $mission->montant_peages,
                'participants' => $mission->participants->map(fn ($participant) => [
                    'nom' => $participant->nom_complet,
                    'nombre_nuitees' => $participant->nombre_nuitees,
                    'total_general' => (float) $participant->total_general,
                ])->values()->all(),
                'etapes' => $mission->etapes->map(fn ($etape) => [
                    'type_etape' => \App\Models\Mission::TYPES_ETAPES_REGIONALES[$etape->type_etape] ?? $etape->type_etape,
                    'bareme' => \App\Models\Mission::BAREMES_REGIONAUX[$etape->bareme]['label'] ?? $etape->bareme,
                    'localite' => $etape->localite,
                    'date_depart' => $etape->date_depart?->format('d/m/Y'),
                    'date_retour' => $etape->date_retour?->format('d/m/Y'),
                    'nombre_jours' => $etape->nombre_jours,
                    'nombre_nuitees' => $etape->nombre_nuitees,
                ])->values()->all(),
            ];
        @endphp
        <script id="mission-region-data" type="application/json">@json($missionPdf, JSON_UNESCAPED_UNICODE)</script>
        <img id="mission-logo" src="{{ asset('logo_canam.png') }}" alt="" class="hidden" aria-hidden="true">
    @endif
</x-layouts::app>
