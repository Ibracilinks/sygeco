<?php

namespace App\Console\Commands;

use App\Models\Activite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reprise de l'existant : les activités soumises avant la règle de remontée gardent
 * le service qui les a saisies. Cette commande les rattache, comme le ferait
 * désormais la soumission, à l'entité qui chapeaute ce service (Direction
 * Centrale, Agence Comptable), hors Direction Générale.
 */
class ReattribuerActivitesEnAttenteCommand extends Command
{
    protected $signature = 'activites:reattribuer-entite
        {--dry-run : Afficher les changements sans les appliquer}
        {--force : Appliquer sans demander de confirmation}';

    protected $description = "Rattache les activités en attente de validation à l'entité qui chapeaute leur service (hors DG)";

    public function handle(): int
    {
        $aDeplacer = Activite::query()
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

        if ($aDeplacer->isEmpty()) {
            $this->info('Aucune activité en attente à rattacher : tout est déjà en place.');

            return self::SUCCESS;
        }

        $this->table(
            ['Activité', 'Nom', 'Structure actuelle', 'Nouvelle structure'],
            $aDeplacer->map(fn (array $ligne) => [
                $ligne['activite']->id,
                mb_strimwidth((string) $ligne['activite']->nom_activite, 0, 60, '…'),
                $ligne['service']->nom,
                $ligne['entite']->nom,
            ])->all(),
        );

        if ($this->option('dry-run')) {
            $this->warn("Simulation : {$aDeplacer->count()} activité(s) seraient rattachées. Rien n'a été modifié.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Rattacher ces {$aDeplacer->count()} activité(s) ?")) {
            $this->warn('Annulé : rien n\'a été modifié.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($aDeplacer) {
            $aDeplacer->each(fn (array $ligne) => $ligne['activite']->reattribuerAEntiteDeSoumission());
        });

        $this->info("{$aDeplacer->count()} activité(s) rattachée(s) à leur entité. Le service d'origine reste parmi les structures intervenantes.");

        return self::SUCCESS;
    }
}
