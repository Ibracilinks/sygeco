<?php

namespace App\Console\Commands;

use App\Models\Activite;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reprise de l'existant pour la règle de remontée de la soumission :
 *  1. les activités soumises avant la règle gardent le service qui les a saisies :
 *     elles sont rattachées, comme le ferait la soumission, à l'entité qui chapeaute
 *     ce service (Direction Centrale, Agence Comptable), hors Direction Générale ;
 *  2. une première version de la règle ajoutait le service aux structures
 *     intervenantes (« DBCGOQ / SCG ») : il en est retiré, seul l'historique garde
 *     la trace du service d'origine.
 */
class ReattribuerActivitesEnAttenteCommand extends Command
{
    protected $signature = 'activites:reattribuer-entite
        {--dry-run : Afficher les changements sans les appliquer}
        {--force : Appliquer sans demander de confirmation}';

    protected $description = "Rattache les activités en attente à l'entité qui chapeaute leur service (hors DG) et retire ce service des structures intervenantes";

    public function handle(): int
    {
        $aRattacher = $this->activitesARattacher();
        $aNettoyer = $this->activitesANettoyer();

        if ($aRattacher->isEmpty() && $aNettoyer->isEmpty()) {
            $this->info('Aucune activité à reprendre : tout est déjà en place.');

            return self::SUCCESS;
        }

        if ($aRattacher->isNotEmpty()) {
            $this->line('Activités en attente à rattacher à leur entité :');
            $this->table(
                ['Activité', 'Nom', 'Structure actuelle', 'Nouvelle structure'],
                $aRattacher->map(fn (array $ligne) => [
                    $ligne['activite']->id,
                    $this->nom($ligne['activite']),
                    $ligne['service']->nom,
                    $ligne['entite']->nom,
                ])->all(),
            );
        }

        if ($aNettoyer->isNotEmpty()) {
            $this->line('Services à retirer des structures intervenantes :');
            $this->table(
                ['Activité', 'Nom', 'Structure', 'Service retiré'],
                $aNettoyer->map(fn (array $ligne) => [
                    $ligne['activite']->id,
                    $this->nom($ligne['activite']),
                    $ligne['activite']->departement?->nom,
                    $ligne['services']->pluck('nom')->join(', '),
                ])->all(),
            );
        }

        $resume = "{$aRattacher->count()} activité(s) à rattacher, {$aNettoyer->count()} à nettoyer";

        if ($this->option('dry-run')) {
            $this->warn("Simulation : {$resume}. Rien n'a été modifié.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Appliquer ({$resume}) ?")) {
            $this->warn('Annulé : rien n\'a été modifié.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($aRattacher, $aNettoyer) {
            $aRattacher->each(fn (array $ligne) => $ligne['activite']->reattribuerAEntiteDeSoumission());
            $aNettoyer->each(fn (array $ligne) => $ligne['activite']->departements()->detach($ligne['services']->pluck('id')));
        });

        $this->info("Terminé : {$resume}.");

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, array{activite: Activite, service: mixed, entite: mixed}>
     */
    private function activitesARattacher(): Collection
    {
        return Activite::query()
            ->where('statut', 'en_attente')
            ->with('departement.parent.parent.parent')
            ->orderBy('id')
            ->get()
            ->map(fn (Activite $activite) => [
                'activite' => $activite,
                'service' => $activite->departement,
                'entite' => $activite->departement?->entiteDeSoumission(),
            ])
            ->filter(fn (array $ligne) => $ligne['entite'] !== null)
            ->values();
    }

    /**
     * @return Collection<int, array{activite: Activite, services: Collection}>
     */
    private function activitesANettoyer(): Collection
    {
        return Activite::query()
            ->whereHas('departements')
            ->with(['departement', 'departements.parent.parent.parent'])
            ->orderBy('id')
            ->get()
            ->map(fn (Activite $activite) => [
                'activite' => $activite,
                'services' => $activite->servicesRemontesParmiIntervenantes(),
            ])
            ->filter(fn (array $ligne) => $ligne['services']->isNotEmpty())
            ->values();
    }

    private function nom(Activite $activite): string
    {
        return mb_strimwidth((string) $activite->nom_activite, 0, 60, '…');
    }
}
