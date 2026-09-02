<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMissionRequest;
use App\Http\Requests\UpdateMissionRequest;
use App\Models\Departement;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'status' => (string) $request->string('status'),
            'type' => (string) $request->string('type'),
            'sort' => (string) $request->string('sort', 'date_document'),
            'direction' => (string) $request->string('direction', 'desc'),
        ];

        $query = Mission::query()
            ->with(['departement:id,nom', 'departements:id,nom', 'createur:id,name'])
            ->withCount(['participants', 'signataires']);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'], $filters['direction']);

        $missions = $query->paginate(15)->withQueryString();

        $summaryQuery = Mission::query();
        $this->applyFilters($summaryQuery, $filters);

        $summary = [
            'total' => (clone $summaryQuery)->count('*'),
            'brouillons' => (clone $summaryQuery)->where('statut', 'brouillon')->count('*'),
            'finalisees' => (clone $summaryQuery)->where('statut', 'finalise')->count('*'),
            'budget_total' => (float) (clone $summaryQuery)->sum('montant_total'),
        ];

        return view('pages.missions.index', compact('missions', 'filters', 'summary'));
    }

    public function create(Request $request)
    {
        $type = in_array((string) $request->string('type'), array_keys(Mission::TYPES), true)
            ? (string) $request->string('type')
            : Mission::TYPE_MEME_VILLE;

        $departements = Departement::query()->active()->ordered()->get(['id', 'nom']);
        $precedente = Mission::query()
            ->where('type', $type)
            ->with(['participants', 'signataires', 'etapes', 'departements'])
            ->latest('date_document')
            ->latest('id')
            ->first();

        $mission = new Mission([
            'reference' => Mission::prochaineReference(),
            'type' => $type,
            'date_document' => today(),
            'date_depart' => today(),
            'date_retour' => today(),
            'nombre_jours' => 1,
            'tickets_carburant_par_jour' => (int) ($precedente?->tickets_carburant_par_jour ?? 1),
            'montant_par_jour' => (float) ($precedente?->montant_par_jour ?? 0),
            'montant_ticket_carburant' => (float) ($precedente?->montant_ticket_carburant ?? 0),
            'destination' => $precedente?->destination,
            'point_depart' => $precedente?->point_depart ?? 'Bamako',
            'code_budgetaire' => $precedente?->code_budgetaire,
            'zone_code' => $precedente?->zone_code,
            'frais_participation_nombre' => (int) ($precedente?->frais_participation_nombre ?? 0),
            'frais_participation_unitaire' => (float) ($precedente?->frais_participation_unitaire ?? 0),
            'frais_visa_nombre' => (int) ($precedente?->frais_visa_nombre ?? 0),
            'frais_visa_unitaire' => (float) ($precedente?->frais_visa_unitaire ?? 0),
            'billets_affaire_nombre' => (int) ($precedente?->billets_affaire_nombre ?? 0),
            'billets_affaire_unitaire' => (float) ($precedente?->billets_affaire_unitaire ?? 0),
            'billets_economique_nombre' => (int) ($precedente?->billets_economique_nombre ?? 0),
            'billets_economique_unitaire' => (float) ($precedente?->billets_economique_unitaire ?? 0),
            'lieu_signature' => $precedente?->lieu_signature ?? 'Bamako',
            'statut' => 'brouillon',
        ]);

        $participants = $precedente?->participants
            ? $precedente->participants->map(fn ($participant) => [
                'nom_complet' => $participant->nom_complet,
                'categorie' => $participant->categorie,
                'nombre_nuitees' => $participant->nombre_nuitees,
            ])->all()
            : [['nom_complet' => '', 'categorie' => null, 'nombre_nuitees' => null]];

        $etapes = $precedente?->etapes
            ? $precedente->etapes->map(fn ($etape) => [
                'type_etape' => $etape->type_etape,
                'bareme' => $etape->bareme,
                'localite' => $etape->localite,
                'date_depart' => optional($etape->date_depart)->format('Y-m-d'),
                'date_retour' => optional($etape->date_retour)->format('Y-m-d'),
                'premiere_nuitee_payee' => (bool) $etape->premiere_nuitee_payee,
            ])->all()
            : [[
                'type_etape' => 'region',
                'bareme' => 'national',
                'localite' => '',
                'date_depart' => today()->format('Y-m-d'),
                'date_retour' => today()->format('Y-m-d'),
                'premiere_nuitee_payee' => false,
            ]];

        $signataires = $precedente?->signataires
            ? $precedente->signataires->map(fn ($signataire) => [
                'libelle' => $signataire->libelle,
                'nom' => $signataire->nom,
                'fonction' => $signataire->fonction,
            ])->all()
            : $this->signatairesParDefaut($type);

        $structuresDemandeuses = $precedente?->departements->pluck('id')->all() ?? [];

        $formPartial = $this->formPartialFor($type);

        return view('pages.missions.create', compact('mission', 'departements', 'participants', 'signataires', 'etapes', 'precedente', 'formPartial', 'structuresDemandeuses'));
    }

    public function store(StoreMissionRequest $request)
    {
        $mission = DB::transaction(function () use ($request) {
            $participants = $request->validated('participants');
            $etapes = $request->validated('etapes', []);

            $mission = new Mission($request->safe()->except(['participants', 'signataires', 'etapes', 'structures_demandeuses']));
            $mission->cree_par = Auth::id();
            $mission->maj_par = Auth::id();
            $participants = $mission->appliquerCalculs($participants, $etapes);
            $mission->save();

            $mission->departements()->sync($request->validated('structures_demandeuses', []));
            $this->syncParticipants($mission, $participants);
            $this->syncEtapes($mission, $etapes);
            $this->syncSignataires($mission, $request->validated('signataires'));

            return $mission;
        });

        return redirect()->route('missions.show', $mission)
            ->with('success', 'Mission enregistrée avec succès.');
    }

    /**
     * Passe une mission du brouillon au statut finalisé : le document est réputé
     * arrêté, plus rien ne doit bouger sans repasser par une modification explicite.
     */
    public function finaliser(Mission $mission)
    {
        if ($mission->statut !== 'brouillon') {
            return redirect()->route('missions.show', $mission)
                ->with('error', 'Cette mission est déjà finalisée.');
        }

        $mission->statut = 'finalise';
        $mission->maj_par = Auth::id();
        $mission->save();

        return redirect()->route('missions.show', $mission)
            ->with('success', 'Mission finalisée.');
    }

    public function show(Mission $mission)
    {
        $mission->load([
            'departement:id,nom',
            'departements:id,nom',
            'createur:id,name',
            'participants',
            'signataires',
            'etapes',
        ]);

        return view('pages.missions.show', compact('mission'));
    }

    public function edit(Mission $mission)
    {
        $mission->load(['participants', 'signataires', 'etapes', 'departements']);

        $departements = Departement::query()->active()->ordered()->get(['id', 'nom']);
        $participants = $mission->participants->map(fn ($participant) => [
            'nom_complet' => $participant->nom_complet,
            'categorie' => $participant->categorie,
            'nombre_nuitees' => $participant->nombre_nuitees,
        ])->all();
        $etapes = $mission->etapes->map(fn ($etape) => [
            'type_etape' => $etape->type_etape,
            'bareme' => $etape->bareme,
            'localite' => $etape->localite,
            'date_depart' => optional($etape->date_depart)->format('Y-m-d'),
            'date_retour' => optional($etape->date_retour)->format('Y-m-d'),
            'premiere_nuitee_payee' => (bool) $etape->premiere_nuitee_payee,
        ])->all();
        $signataires = $mission->signataires->map(fn ($signataire) => [
            'libelle' => $signataire->libelle,
            'nom' => $signataire->nom,
            'fonction' => $signataire->fonction,
        ])->all();

        if ($participants === []) {
            $participants = [['nom_complet' => '', 'categorie' => null, 'nombre_nuitees' => null]];
        }

        if ($etapes === []) {
            $etapes = [[
                'type_etape' => 'region',
                'bareme' => 'national',
                'localite' => '',
                'date_depart' => optional($mission->date_depart)->format('Y-m-d'),
                'date_retour' => optional($mission->date_retour)->format('Y-m-d'),
                'premiere_nuitee_payee' => false,
            ]];
        }

        if ($signataires === []) {
            $signataires = $this->signatairesParDefaut($mission->type);
        }

        $structuresDemandeuses = $mission->departements->pluck('id')->all();

        $formPartial = $this->formPartialFor($mission->type);

        return view('pages.missions.edit', compact('mission', 'departements', 'participants', 'signataires', 'etapes', 'formPartial', 'structuresDemandeuses'));
    }

    public function update(UpdateMissionRequest $request, Mission $mission)
    {
        DB::transaction(function () use ($request, $mission) {
            $participants = $request->validated('participants');
            $etapes = $request->validated('etapes', []);

            $mission->fill($request->safe()->except(['participants', 'signataires', 'etapes', 'structures_demandeuses']));
            $mission->maj_par = Auth::id();
            $participants = $mission->appliquerCalculs($participants, $etapes);
            $mission->save();

            $mission->departements()->sync($request->validated('structures_demandeuses', []));
            $this->syncParticipants($mission, $participants);
            $this->syncEtapes($mission, $etapes);
            $this->syncSignataires($mission, $request->validated('signataires'));
        });

        return redirect()->route('missions.show', $mission)
            ->with('success', 'Mission mise à jour.');
    }

    public function destroy(Mission $mission)
    {
        $mission->delete();

        return redirect()->route('missions.index')
            ->with('success', 'Mission supprimée.');
    }

    /**
     * @param  array{search: string, status: string, type: string, sort?: string, direction?: string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $term = '%'.str_replace(' ', '%', $filters['search']).'%';

            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('reference', 'like', $term)
                    ->orWhere('objet', 'like', $term)
                    ->orWhere('destination', 'like', $term)
                    ->orWhere('lieu_signature', 'like', $term);
            });
        }

        if (in_array($filters['status'], array_keys(Mission::STATUTS), true)) {
            $query->where('statut', $filters['status']);
        }

        if (in_array($filters['type'], array_keys(Mission::TYPES), true)) {
            $query->where('type', $filters['type']);
        }
    }

    private function applySort(Builder $query, string $sort, string $direction): void
    {
        $allowedSorts = ['date_document', 'reference', 'montant_total', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'date_document';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query->orderBy($sort, $direction)->orderBy('id', 'desc');
    }

    /**
     * @param  array<int, array<string, mixed>>  $participants
     */
    private function syncParticipants(Mission $mission, array $participants): void
    {
        $mission->participants()->delete();

        foreach (array_values($participants) as $index => $participant) {
            $mission->participants()->create([
                'ordre' => $index + 1,
                'nom_complet' => $participant['nom_complet'],
                'categorie' => $participant['categorie'] ?? null,
                'montant_par_jour' => $participant['montant_par_jour'] ?? 0,
                'montant_par_nuitee' => $participant['montant_par_nuitee'] ?? 0,
                'nombre_nuitees' => $participant['nombre_nuitees'] ?? 0,
                'sous_total' => $participant['sous_total'] ?? 0,
                'majoration_taux' => $participant['majoration_taux'] ?? 0,
                'majoration_montant' => $participant['majoration_montant'] ?? 0,
                'total_general' => $participant['total_general'] ?? 0,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $etapes
     */
    private function syncEtapes(Mission $mission, array $etapes): void
    {
        $mission->etapes()->delete();

        foreach (array_values($etapes) as $index => $etape) {
            $mission->etapes()->create([
                'ordre' => $index + 1,
                'type_etape' => $etape['type_etape'],
                'bareme' => $etape['bareme'] ?? 'national',
                'localite' => $etape['localite'],
                'date_depart' => $etape['date_depart'],
                'date_retour' => $etape['date_retour'],
                'nombre_jours' => $etape['nombre_jours'] ?? 1,
                'nombre_nuitees' => $etape['nombre_nuitees'] ?? 0,
                'premiere_nuitee_payee' => (bool) ($etape['premiere_nuitee_payee'] ?? false),
            ]);
        }
    }

    /**
     * @param  array<int, array{libelle: ?string, nom: string, fonction: string}>  $signataires
     */
    private function syncSignataires(Mission $mission, array $signataires): void
    {
        $mission->signataires()->delete();

        foreach (array_values($signataires) as $index => $signataire) {
            $mission->signataires()->create([
                'ordre' => $index + 1,
                'libelle' => $signataire['libelle'],
                'nom' => $signataire['nom'],
                'fonction' => $signataire['fonction'] ?? '',
            ]);
        }
    }

    /**
     * @return array<int, array{libelle: ?string, nom: string, fonction: string}>
     */
    private function signatairesParDefaut(string $type): array
    {
        if ($type === Mission::TYPE_EXTERIEURE) {
            return [
                ['libelle' => 'LA DBCGOQ', 'nom' => '', 'fonction' => ''],
                ['libelle' => "L'AGENT COMPTABLE", 'nom' => '', 'fonction' => ''],
                ['libelle' => 'LE DIRECTEUR GENERAL', 'nom' => '', 'fonction' => ''],
            ];
        }

        return [
            ['libelle' => 'P/LA DBCGOQ/PO', 'nom' => '', 'fonction' => ''],
            ['libelle' => "L'AGENT COMPTABLE", 'nom' => '', 'fonction' => ''],
            ['libelle' => 'LE DIRECTEUR GENERAL', 'nom' => '', 'fonction' => ''],
        ];
    }

    private function formPartialFor(string $type): string
    {
        return match ($type) {
            Mission::TYPE_EXTERIEURE => 'pages.missions.partials.form-exterieure',
            Mission::TYPE_REGION => 'pages.missions.partials.form-region',
            default => 'pages.missions.partials.form-meme-ville',
        };
    }
}
