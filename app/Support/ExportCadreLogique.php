<?php

namespace App\Support;

use App\Models\Activite;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Classeur Excel (.xlsx) du cadre logique : Objectif stratégique → Résultat
 * stratégique → Extrant → activités, sur une seule feuille.
 *
 * Commun aux exports du PTA, de l'arbitrage, de la programmation et de
 * l'évaluation : seules les colonnes d'activité changent. En-tête figé et répété
 * à l'impression, montants numériques (sommables dans Excel), mise en page A4
 * paysage ajustée à la largeur.
 */
class ExportCadreLogique
{
    private const POLICE = 'Calibri';

    private const MARINE = '1F3864';

    private const ENTETE = 'D9E1F2';

    private const OBJECTIF = '2F5597';

    private const RESULTAT = 'E2EFDA';

    private const EXTRANT = 'FCE4D6';

    private const SOUS_TOTAL = 'C6E0B4';

    private const TOTAL = '8EA9DB';

    private const FORMAT_MONTANT = '#,##0';

    private Spreadsheet $classeur;

    private Worksheet $feuille;

    private int $ligne = 1;

    private int $nbColonnes;

    /** Indice (1-based) de la colonne des coûts, ou null si l'export n'en a pas. */
    private ?int $colonneCout;

    /**
     * @param  array<int, array{libelle: string, largeur: float, groupe?: string, centre?: bool, valeur: callable(Activite, int): mixed}>  $colonnes
     */
    private function __construct(
        private readonly array $colonnes,
        private readonly string $titre,
        private readonly string $sousTitre,
        private readonly bool $avecTotaux,
    ) {
        $this->nbColonnes = count($colonnes);
        $index = array_search(true, array_map(fn ($c) => $c['cout'] ?? false, $colonnes), true);
        $this->colonneCout = $index === false ? null : $index + 1;

        $this->classeur = new Spreadsheet;
        $this->classeur->getProperties()->setCreator('LEAC — CANAM')->setTitle($titre);
        $this->classeur->getDefaultStyle()->getFont()->setName(self::POLICE)->setSize(10);
        $this->feuille = $this->classeur->getActiveSheet();
        $this->feuille->setTitle('Cadre logique');
    }

    /**
     * Programmation (PTA, arbitrage, liste de programmation) : chronogramme et coûts.
     */
    public static function programmation(Collection $objectifs, string $titre, string $sousTitre): self
    {
        $trimestre = fn (int $t) => fn (Activite $a) => $a->{'trimestre_'.$t} === 'oui' ? 'X' : '';

        $export = new self([
            ['libelle' => 'N°', 'largeur' => 6, 'centre' => true, 'valeur' => fn (Activite $a, int $rang) => $rang],
            ['libelle' => 'ACTIVITÉS', 'largeur' => 48, 'valeur' => fn (Activite $a) => $a->nom_activite],
            ['libelle' => 'INDICATEUR OBJECTIVEMENT VÉRIFIABLE', 'largeur' => 30, 'valeur' => fn (Activite $a) => $a->indicateur_objectivement_verifiable],
            ['libelle' => 'MOYEN DE VÉRIFICATION', 'largeur' => 26, 'valeur' => fn (Activite $a) => $a->moyen_verification],
            ['libelle' => 'T1', 'groupe' => 'CHRONOGRAMME', 'largeur' => 6, 'centre' => true, 'valeur' => $trimestre(1)],
            ['libelle' => 'T2', 'groupe' => 'CHRONOGRAMME', 'largeur' => 6, 'centre' => true, 'valeur' => $trimestre(2)],
            ['libelle' => 'T3', 'groupe' => 'CHRONOGRAMME', 'largeur' => 6, 'centre' => true, 'valeur' => $trimestre(3)],
            ['libelle' => 'T4', 'groupe' => 'CHRONOGRAMME', 'largeur' => 6, 'centre' => true, 'valeur' => $trimestre(4)],
            ['libelle' => 'RESPONSABLES', 'largeur' => 18, 'centre' => true, 'valeur' => fn (Activite $a) => self::responsables($a)],
            ['libelle' => 'COÛT (FCFA)', 'largeur' => 16, 'cout' => true, 'valeur' => fn (Activite $a) => $a->pour_memoire ? 'PM' : (float) $a->cout],
        ], $titre, $sousTitre, avecTotaux: true);

        return $export->remplir($objectifs);
    }

    /**
     * Évaluation (mi-parcours ou fin d'année) : statut d'exécution et observations.
     */
    public static function evaluation(Collection $objectifs, string $titre, string $sousTitre, string $periode, string $entetePeriode): self
    {
        $statut = fn (string $attendu) => fn (Activite $a) => ($a->evaluation($periode)?->statut_execution) === $attendu ? 'X' : '';

        $export = new self([
            ['libelle' => 'N°', 'largeur' => 6, 'centre' => true, 'valeur' => fn (Activite $a, int $rang) => $rang],
            ['libelle' => 'ACTIVITÉS', 'largeur' => 48, 'valeur' => fn (Activite $a) => $a->nom_activite],
            ['libelle' => 'INDICATEUR OBJECTIVEMENT VÉRIFIABLE', 'largeur' => 30, 'valeur' => fn (Activite $a) => $a->indicateur_objectivement_verifiable],
            ['libelle' => "VALEUR DE L'INDICATEUR", 'largeur' => 22, 'valeur' => fn (Activite $a) => $a->evaluation($periode)?->valeur_indicateur],
            ['libelle' => 'R', 'groupe' => $entetePeriode, 'largeur' => 6, 'centre' => true, 'valeur' => $statut('realise')],
            ['libelle' => 'ER', 'groupe' => $entetePeriode, 'largeur' => 6, 'centre' => true, 'valeur' => $statut('en_cours')],
            ['libelle' => 'NR', 'groupe' => $entetePeriode, 'largeur' => 6, 'centre' => true, 'valeur' => $statut('non_realise')],
            ['libelle' => 'RESPONSABLES', 'largeur' => 18, 'centre' => true, 'valeur' => fn (Activite $a) => self::responsables($a)],
            ['libelle' => 'OBSERVATIONS', 'largeur' => 34, 'valeur' => fn (Activite $a) => $a->evaluation($periode)?->observation],
        ], $titre, $sousTitre, avecTotaux: false);

        return $export->remplir($objectifs);
    }

    public function telecharger(string $nomFichier): StreamedResponse
    {
        $writer = new Xlsx($this->classeur);

        return new StreamedResponse(fn () => $writer->save('php://output'), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$nomFichier.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * @param  Collection<int, array{objectif: mixed, resultats: iterable}>  $objectifs
     */
    private function remplir(Collection $objectifs): self
    {
        $this->enTete();

        $rang = 0;
        $totalGeneral = 0.0;

        foreach ($objectifs as $bloc) {
            $objectif = $bloc['objectif'];
            $totalObjectif = 0.0;
            $nbObjectif = 0;

            $this->bandeau(
                'OBJECTIF STRATÉGIQUE : '.($objectif?->description ?: ($objectif?->libelle ?? 'Activités non rattachées au cadre logique')),
                self::OBJECTIF, blanc: true, taille: 11,
            );

            foreach ($bloc['resultats'] as $blocResultat) {
                $resultat = $blocResultat['resultat'];
                $totalResultat = 0.0;

                if ($resultat) {
                    $this->bandeau(trim($resultat->code.' '.$resultat->libelle), self::RESULTAT);
                }

                foreach ($blocResultat['extrants'] as $blocExtrant) {
                    $extrant = $blocExtrant['extrant'];

                    if ($extrant) {
                        $this->bandeau(trim($extrant->code.' '.$extrant->libelle), self::EXTRANT, gauche: false);
                    }

                    if (collect($blocExtrant['activites'])->isEmpty()) {
                        $this->bandeau('Aucune activité', 'FFFFFF', gras: false, gauche: false, gris: true);
                    }

                    foreach ($blocExtrant['activites'] as $activite) {
                        $this->activite($activite, ++$rang);
                        $nbObjectif++;
                        $totalResultat += $activite->pour_memoire ? 0 : (float) $activite->cout;
                    }
                }

                if ($this->avecTotaux && $resultat) {
                    $this->total('Sous-total '.$resultat->code, $totalResultat, self::SOUS_TOTAL);
                }

                $totalObjectif += $totalResultat;
            }

            if ($this->avecTotaux) {
                $this->total('Total objectif ('.$nbObjectif.' activité'.($nbObjectif > 1 ? 's' : '').')', $totalObjectif, self::TOTAL);
            }

            $totalGeneral += $totalObjectif;
            $this->ligne++;
        }

        if ($objectifs->isEmpty()) {
            $this->bandeau('Aucune activité à exporter.', 'FFFFFF', gras: false, gauche: false, gris: true);
        } elseif ($this->avecTotaux) {
            $this->total('TOTAL GÉNÉRAL ('.$rang.' activité'.($rang > 1 ? 's' : '').')', $totalGeneral, self::MARINE, blanc: true);
        }

        $this->miseEnPage();

        return $this;
    }

    /**
     * Titre, sous-titre et en-tête de colonnes (deux lignes quand des colonnes
     * sont regroupées, ex. CHRONOGRAMME au-dessus de T1…T4).
     */
    private function enTete(): void
    {
        $derniere = Coordinate::stringFromColumnIndex($this->nbColonnes);

        $this->feuille->setCellValue('A1', mb_strtoupper($this->titre));
        $this->feuille->mergeCells("A1:{$derniere}1");
        $this->feuille->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => self::MARINE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $this->feuille->getRowDimension(1)->setRowHeight(24);

        $this->feuille->setCellValue('A2', $this->sousTitre);
        $this->feuille->mergeCells("A2:{$derniere}2");
        $this->feuille->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '595959']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $haut = 4;
        $groupee = collect($this->colonnes)->contains(fn ($c) => isset($c['groupe']));
        $bas = $groupee ? $haut + 1 : $haut;

        foreach ($this->colonnes as $i => $colonne) {
            $lettre = Coordinate::stringFromColumnIndex($i + 1);
            $this->feuille->getColumnDimension($lettre)->setWidth($colonne['largeur']);

            if (isset($colonne['groupe'])) {
                $this->feuille->setCellValue($lettre.$bas, $colonne['libelle']);
                $this->feuille->setCellValue($lettre.$haut, $colonne['groupe']);
            } else {
                $this->feuille->setCellValue($lettre.$haut, $colonne['libelle']);
                if ($groupee) {
                    $this->feuille->mergeCells($lettre.$haut.':'.$lettre.$bas);
                }
            }
        }

        // Fusion horizontale des groupes (CHRONOGRAMME, période d'évaluation).
        collect($this->colonnes)->map(fn ($c, $i) => [$c['groupe'] ?? null, $i + 1])
            ->filter(fn ($c) => $c[0] !== null)
            ->groupBy(fn ($c) => $c[0])
            ->each(function ($indices) use ($haut) {
                $debut = Coordinate::stringFromColumnIndex($indices->first()[1]);
                $fin = Coordinate::stringFromColumnIndex($indices->last()[1]);
                $this->feuille->mergeCells("{$debut}{$haut}:{$fin}{$haut}");
            });

        $this->feuille->getStyle("A{$haut}:{$derniere}{$bas}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => self::MARINE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ENTETE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '808080']]],
        ]);
        $this->feuille->getRowDimension($haut)->setRowHeight($groupee ? 20 : 32);

        // L'en-tête reste visible au défilement et se répète à chaque page imprimée.
        $this->feuille->freezePane('A'.($bas + 1));
        $this->feuille->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($haut, $bas);

        $this->ligne = $bas + 1;
    }

    private function activite(Activite $activite, int $rang): void
    {
        foreach ($this->colonnes as $i => $colonne) {
            $valeur = $colonne['valeur']($activite, $rang);
            $this->feuille->setCellValue(Coordinate::stringFromColumnIndex($i + 1).$this->ligne, $valeur === null ? '' : $valeur);
        }

        $plage = $this->plage();
        $this->feuille->getStyle($plage)->applyFromArray([
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']]],
        ]);

        foreach ($this->colonnes as $i => $colonne) {
            $cellule = Coordinate::stringFromColumnIndex($i + 1).$this->ligne;
            if ($colonne['centre'] ?? false) {
                $this->feuille->getStyle($cellule)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        if ($this->colonneCout !== null) {
            $cellule = Coordinate::stringFromColumnIndex($this->colonneCout).$this->ligne;
            $this->feuille->getStyle($cellule)->getNumberFormat()->setFormatCode(self::FORMAT_MONTANT);
            $this->feuille->getStyle($cellule)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $this->ligne++;
    }

    /** Ligne pleine largeur : objectif, résultat, extrant ou mention. */
    private function bandeau(string $texte, string $fond, bool $blanc = false, bool $gras = true, bool $gauche = true, bool $gris = false, int $taille = 10): void
    {
        $plage = $this->plage();
        $this->feuille->setCellValue('A'.$this->ligne, $texte);
        $this->feuille->mergeCells($plage);
        $this->feuille->getStyle($plage)->applyFromArray([
            'font' => [
                'bold' => $gras, 'size' => $taille, 'italic' => $gris,
                'color' => ['rgb' => $blanc ? 'FFFFFF' : ($gris ? '808080' : '000000')],
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fond]],
            'alignment' => [
                'horizontal' => $gauche ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true, 'indent' => $gauche ? 1 : 0,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '808080']]],
        ]);
        // Une cellule fusionnée ne s'ajuste pas seule en hauteur : on l'estime.
        $largeur = array_sum(array_column($this->colonnes, 'largeur'));
        $lignes = max(1, (int) ceil(mb_strlen($texte) * 1.1 / $largeur));
        $this->feuille->getRowDimension($this->ligne)->setRowHeight(max(18, $lignes * 14 + 4));

        $this->ligne++;
    }

    private function total(string $libelle, float $montant, string $fond, bool $blanc = false): void
    {
        $avant = Coordinate::stringFromColumnIndex($this->colonneCout - 1);
        $colonne = Coordinate::stringFromColumnIndex($this->colonneCout);
        $plage = $this->plage();

        $this->feuille->setCellValue('A'.$this->ligne, $libelle);
        $this->feuille->mergeCells("A{$this->ligne}:{$avant}{$this->ligne}");
        $this->feuille->setCellValue($colonne.$this->ligne, $montant);

        $this->feuille->getStyle($plage)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $blanc ? 'FFFFFF' : '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fond]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '808080']]],
        ]);
        $this->feuille->getStyle($colonne.$this->ligne)->getNumberFormat()->setFormatCode(self::FORMAT_MONTANT);
        $this->feuille->getRowDimension($this->ligne)->setRowHeight(18);

        $this->ligne++;
    }

    private function miseEnPage(): void
    {
        $page = $this->feuille->getPageSetup();
        $page->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $this->feuille->getPageMargins()->setTop(0.5)->setBottom(0.6)->setLeft(0.4)->setRight(0.4);
        $this->feuille->getHeaderFooter()
            ->setOddFooter('&L&8'.$this->titre.'&R&8Page &P / &N');
        $this->feuille->setSelectedCell('A1');
    }

    private function plage(): string
    {
        return 'A'.$this->ligne.':'.Coordinate::stringFromColumnIndex($this->nbColonnes).$this->ligne;
    }

    /**
     * Structures intervenantes, à défaut la structure porteuse, par leur code
     * (le nom complet seulement pour une entité sans code).
     */
    private static function responsables(Activite $activite): string
    {
        $structures = $activite->departements->isNotEmpty()
            ? $activite->departements
            : collect([$activite->departement])->filter();

        return $structures->map(fn ($structure) => $structure->code ?: $structure->nom)->join(' / ');
    }
}
