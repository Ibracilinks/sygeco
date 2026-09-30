<x-doc.page
    title="Exercices"
    subtitle="Le calendrier de référence du PTA : année budgétaire, fenêtre de saisie, de mi-parcours et d'évaluation."
    icon="📅"
    current="exercices"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="role">2. Rôle de l'exercice</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="liste">3. Écran « Exercices »</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="creer">4. Créer un exercice</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="fenetres">5. Les fenêtres de la fiche</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="fiche">6. Fiche d'un exercice</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="actif">7. Exercice actif : statut ou contexte ?</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="export">8. Exporter le PTA</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="supprimer">9. Modifier / supprimer</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="impacts">10. Ce que l'exercice déclenche ailleurs</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">11. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">12. Bonnes pratiques</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="acces" titre="1. Qui a accès à ce module">
        <p>
            Le menu <strong>Exercices</strong> (icône 📅) est réservé aux rôles <strong>superadmin</strong> et
            <strong>dbcgoq</strong> : le groupe de routes complet (liste, création, fiche, modification, activation,
            export, suppression) est protégé par un contrôle de rôle qui exclut tous les autres profils.
        </p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Action</th>
                        <th class="py-2 pr-4">Permission requise</th>
                        <th class="py-2">Rôles concernés</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4">Consulter la liste et les fiches</td><td class="py-2 pr-4 font-mono text-xs">view_exercices</td><td class="py-2">superadmin, dbcgoq (en pratique)</td></tr>
                    <tr><td class="py-2 pr-4">Créer, modifier, supprimer</td><td class="py-2 pr-4 font-mono text-xs">manage_exercices</td><td class="py-2">superadmin, dbcgoq</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce>
            Les rôles <em>chef</em>, <em>agent-planification</em> et <em>suivi-evaluation</em> détiennent bien la
            permission <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">view_exercices</code>, mais l'accès au
            module leur reste fermé : cette permission ne leur sert qu'à d'autres écrans (sélecteur d'exercice,
            référentiel). Le module Exercices lui-même n'est utilisé que par superadmin et dbcgoq.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="role" titre="2. Ce que représente un exercice" chapo="Un exercice est l'année budgétaire du Plan de Travail Annuel (PTA). Il porte la période couverte et pilote trois fenêtres temporelles qui rythment l'année.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">📝 Fenêtre de saisie</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ouverture de la programmation des activités, avec une date limite qui déclenche des relances automatiques.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">📊 Fenêtre de mi-parcours</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Période indicative de suivi de l'exécution, couverte par le chapitre <a href="{{ route('documentation.suivi') }}" class="underline">Suivi &amp; évaluation</a>.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🏁 Fenêtre d'évaluation</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Période indicative du bilan de fin d'exercice, également couverte par le chapitre Suivi &amp; évaluation.</p>
            </div>
        </div>
        <p>
            L'ensemble du cadre logique se rattache à l'exercice : les <strong>Objectifs</strong> (potentiellement
            pluriannuels, donc rattachés à plusieurs exercices à la fois) et, à travers eux, les <strong>Résultats</strong>
            et <strong>Extrants</strong> ; les <strong>Activités</strong>, elles, appartiennent chacune à un seul
            exercice. Le détail de cette hiérarchie est décrit au chapitre <a href="{{ route('documentation.planification') }}" class="underline">Planification</a>.
        </p>
    </x-doc.section>

    <x-doc.section id="liste" titre="3. Écran « Exercices »" chapo="La liste se lit de haut en bas : compteurs, filtre, tableau paginé.">
        <x-doc.capture src="images/manuel/exercices.png" alt="Écran Exercices avec compteurs et tableau">Compteurs et tableau des exercices, avec l'indicateur de délai de la fenêtre de saisie.</x-doc.capture>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Les quatre compteurs</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Total</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tous les exercices créés.</p></div>
            <div class="rounded-lg border border-emerald-200 p-3 dark:border-emerald-900/70"><p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Actif</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Exercice(s) au statut « Actif ».</p></div>
            <div class="rounded-lg border border-amber-200 p-3 dark:border-amber-900/70"><p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Clôturés</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Exercices archivés, saisie fermée.</p></div>
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Brouillons</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Exercices en préparation, invisibles pour les structures.</p></div>
        </div>
        <p>Un filtre par <strong>statut</strong> (Tous / Actif / Clôturé / Brouillon) est disponible au-dessus du tableau, avec un lien « Réinitialiser » si un filtre est actif.</p>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Le tableau</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Année</strong> : avec un badge « Contexte actif » si l'exercice correspond à la session en cours (voir §7).</li>
            <li><strong>Période</strong> : dates de début et de fin, format jour/mois/année.</li>
            <li><strong>Fenêtre de saisie</strong> : ouverture → date limite, ou « Non définie » ; un indicateur de délai affiche « J-{n} », « Dernier jour » ou « Délai dépassé » (en rouge).</li>
            <li><strong>Objectifs</strong> : nombre d'objectifs rattachés à l'exercice.</li>
            <li><strong>Statut</strong> : badge Brouillon (gris), Actif (vert), Clôturé (ambre).</li>
            <li><strong>Actions</strong> : bouton « Utiliser » (contexte de session), Voir, et — si vous détenez <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">manage_exercices</code> — Modifier et Supprimer.</li>
        </ul>
        <p>La liste affiche <strong>15 exercices par page</strong>, triés par année décroissante. Message si aucun résultat : « Aucun exercice trouvé ».</p>
    </x-doc.section>

    <x-doc.section id="creer" titre="4. Créer un exercice" chapo="Bouton « Nouvel exercice ». Seul l'essentiel est demandé à la création ; les fenêtres se règlent ensuite depuis la fiche.">
        <ol class="list-decimal space-y-2 pl-5">
            <li>Saisissez l'<strong>Année*</strong> (entre 2000 et 2100, unique, pré-remplie à l'année en cours). Les dates de début/fin se calent automatiquement sur le 1er janvier et le 31 décembre de cette année tant que vous ne les modifiez pas vous-même.</li>
            <li>Ajustez si besoin la <strong>Date de début*</strong> et la <strong>Date de fin*</strong> (la fin doit être postérieure ou égale au début).</li>
            <li>Choisissez le <strong>Statut*</strong> parmi trois cartes : <em>Brouillon</em> (préparation, invisible pour les structures), <em>Actif</em> (exercice de travail courant — un seul à la fois) ou <em>Clôturé</em> (archivé, plus aucune saisie).</li>
            <li>Enregistrez : message « Exercice créé. ». Un bandeau rappelle alors d'ouvrir la fiche pour régler les fenêtres.</li>
        </ol>
    </x-doc.section>

    <x-doc.section id="fenetres" titre="5. Détail des champs & fenêtres (formulaire de modification)" chapo="Le formulaire de modification reprend l'année, les dates et le statut, et ajoute les six dates de fenêtres.">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Champ</th>
                        <th class="py-2 pr-4">Règle</th>
                        <th class="py-2">Effet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Ouverture de la saisie</td><td class="py-2 pr-4">Doit être ≥ date de début</td><td class="py-2">Dès cette date, une notification et un nouveau mot de passe sont envoyés à tous les comptes non-administrateurs (voir §10).</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Date limite de saisie</td><td class="py-2 pr-4">≥ ouverture, ≤ date de fin</td><td class="py-2">Déclenche les relances automatiques à J-15, J-10, J-7, J-5, J-3, J-2, J-1 et J.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Début / fin du mi-parcours</td><td class="py-2 pr-4">Comprises entre les dates de l'exercice</td><td class="py-2">Ouvre l'information de suivi mi-parcours et déclenche sa notification (voir chapitre Suivi &amp; évaluation).</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Début / fin de l'évaluation</td><td class="py-2 pr-4">Comprises entre les dates de l'exercice</td><td class="py-2">Ouvre l'information du bilan de fin d'exercice et déclenche sa notification.</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce type="attention">
            Ces fenêtres sont désormais <strong>indicatives</strong> : elles affichent un bandeau d'information sur les
            écrans de saisie et déclenchent les notifications, mais elles ne <strong>bloquent plus</strong> la saisie
            de l'exécution — celle-ci reste ouverte en permanence, quelle que soit la date. Voir le chapitre
            <a href="{{ route('documentation.suivi') }}" class="font-medium underline">Suivi &amp; évaluation</a>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="fiche" titre="6. Fiche d'un exercice">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>En-tête</strong> : badge de statut, badge « Contexte actif » le cas échéant, période et durée en jours.</li>
            <li><strong>Cinq cartes KPI</strong> : Objectifs, Résultats, Extrants, Activités, Budget total (FCFA).</li>
            <li><strong>Fenêtre de saisie</strong> : ouverture, date limite, échéance, et « Notification d'ouverture : envoyée le… » ou « non envoyée ».</li>
            <li><strong>Relances envoyées</strong> : historique des relances par palier (J-15 à J), avec date et nombre de destinataires.</li>
            <li><strong>Objectifs de l'exercice</strong> : liste avec code, libellé, nombre de résultats/extrants.</li>
            <li><strong>Activités</strong> : répartition par statut (brouillon/en attente/validé/rejeté) et par avancement d'exécution, taux de réalisation, budget moyen.</li>
            <li><strong>Analyse graphique</strong> : structure par niveau, budget par objectif/résultat, activités par statut et par extrant, chronogramme trimestriel, distribution budgétaire par tranche de coût.</li>
        </ul>
        <p>Boutons disponibles : « Utiliser cet exercice », « Exporter (Excel) », « Modifier » (si <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">manage_exercices</code>), « Retour ».</p>
    </x-doc.section>

    <x-doc.section id="actif" titre="7. « Exercice actif » : deux notions à ne pas confondre" chapo="Le mot « actif » recouvre deux mécanismes distincts dans LEAC.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-emerald-200 p-4 dark:border-emerald-900/70">
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">Statut « Actif » (donnée persistée)</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Un seul exercice peut avoir ce statut. Le mettre sur un exercice bascule <strong>automatiquement</strong> tous les autres exercices actifs vers « Clôturé ». C'est ce statut que suivent les tâches planifiées (relances, notifications).</p>
            </div>
            <div class="rounded-lg border border-sky-200 p-4 dark:border-sky-900/70">
                <p class="text-sm font-medium text-sky-800 dark:text-sky-200">Contexte actif de session (bouton « Utiliser »)</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Change uniquement l'exercice affiché par défaut pour votre session (sidebar, filtres) — sans modifier le statut de quiconque en base, et sans désactiver d'autre exercice.</p>
            </div>
        </div>
        <x-doc.astuce type="attention">
            Cliquer sur « Utiliser » pour consulter un exercice passé <strong>ne rouvre pas</strong> sa saisie et ne
            change pas son statut : c'est un simple confort de consultation pour la session en cours. Pour rouvrir
            réellement un exercice, modifiez son <strong>statut</strong>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="export" titre="8. Exporter le PTA de l'exercice">
        <p>
            Le bouton <strong>« Exporter (Excel) »</strong> de la fiche génère un fichier
            <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">PTA_exercice_{année}_{date}.xls</code> reprenant
            l'intégralité du cadre logique de l'exercice : Objectif → Résultat stratégique → Extrant → Activités, avec
            pour chaque activité l'indicateur, le moyen de vérification, le chronogramme (colonnes 1er à 4e trimestre),
            les structures responsables et le coût, sous-totalisés par résultat puis totalisés en fin de document.
        </p>
        <x-doc.astuce>
            Le fichier s'ouvre directement dans Excel ou LibreOffice, mais il s'agit techniquement d'un tableau HTML
            portant l'extension <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">.xls</code> : ne le renommez
            pas et ouvrez-le avec un tableur plutôt qu'un navigateur si votre poste vous propose ce choix.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="supprimer" titre="9. Modifier / supprimer un exercice">
        <p>Le formulaire de modification reprend tous les champs cités aux §4 et §5 ; l'unicité de l'année s'apprécie hors de l'exercice en cours.</p>
        <x-doc.astuce type="interdit">
            La suppression est <strong>refusée si au moins un objectif est rattaché</strong> à l'exercice, avec le
            message exact : <em>« Impossible de supprimer un exercice lié à des objectifs. »</em> Retirez d'abord
            l'exercice de la liste des exercices couverts par chacun de ces objectifs (module
            <a href="{{ route('documentation.planification') }}" class="font-medium underline">Planification</a>), ou
            supprimez les objectifs qui ne concernent que cet exercice.
        </x-doc.astuce>
        <p>Si la suppression réussit, le message « Exercice supprimé. » s'affiche ; si l'exercice supprimé était le contexte actif de votre session, celui-ci est réinitialisé automatiquement.</p>
    </x-doc.section>

    <x-doc.section id="impacts" titre="10. Ce que l'exercice déclenche ailleurs dans LEAC" chapo="L'exercice n'est pas qu'un repère calendaire : il pilote plusieurs automatismes.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Ouverture de la saisie</strong> : à la date d'ouverture, tous les comptes non-administrateurs (hors rôle dbcgoq) reçoivent un nouveau mot de passe généré automatiquement et une notification avec leurs accès. Les comptes dbcgoq sont volontairement épargnés, pour ne jamais rendre un compte d'administration inaccessible.</li>
            <li><strong>Relances de date limite</strong> : des rappels sont envoyés aux paliers J-15, J-10, J-7, J-5, J-3, J-2, J-1 et J avant la date limite de saisie.</li>
            <li><strong>Relance des brouillons</strong> : les chefs ayant des activités restées à l'état « brouillon » sur l'exercice actif reçoivent un rappel, au maximum une fois toutes les 72 heures.</li>
            <li><strong>Fenêtres de suivi</strong> : l'ouverture du mi-parcours et de l'évaluation notifie les chefs et le DBCGOQ (voir chapitre <a href="{{ route('documentation.suivi') }}" class="underline">Suivi &amp; évaluation</a>).</li>
            <li><strong>Sidebar &amp; tableau de bord</strong> : l'année affichée par défaut est celle du contexte actif de session (§7), avec repli sur l'année civile si aucun exercice n'existe.</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="11. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« La valeur du champ année est déjà utilisée. »</summary>
                <p class="mt-2">Un exercice existe déjà pour cette année. Recherchez-le dans la liste plutôt que d'en recréer un.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Le champ date de fin doit être une date postérieure ou égale au date de début. »</summary>
                <p class="mt-2">Corrigez l'ordre des deux dates ; la même règle s'applique à chaque fenêtre par rapport à ses propres bornes.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Impossible de supprimer un exercice lié à des objectifs. »</summary>
                <p class="mt-2">Voir §9 : détachez ou supprimez d'abord les objectifs qui couvrent cet exercice.</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="12. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Créez le nouvel exercice <strong>avant</strong> la fin du précédent, en statut « Brouillon », puis basculez-le en « Actif » le moment venu — l'ancien exercice sera automatiquement clôturé.</li>
            <li>Réglez systématiquement les <strong>fenêtres</strong> juste après la création : elles conditionnent les relances et les notifications.</li>
            <li>N'utilisez le bouton « Utiliser » que pour <strong>consulter</strong> un exercice différent de celui en cours : il ne rouvre rien.</li>
            <li>Avant de supprimer un exercice de test, vérifiez qu'aucun objectif ne le couvre.</li>
            <li>Utilisez l'export Excel de la fiche pour produire rapidement le document de synthèse du PTA à transmettre à la Direction Générale.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre suivant : <a href="{{ route('documentation.planification') }}" class="font-medium underline">🎯 Planification</a>.
        </p>
    </x-doc.section>
</x-doc.page>
