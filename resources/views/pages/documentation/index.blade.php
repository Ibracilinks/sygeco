<x-doc.page
    title="Manuel d'utilisation"
    subtitle="Guide pratique de la plateforme LEAC (Logiciel d'évaluation des activités de la CANAM) — paramétrage, planification, suivi et évaluation des activités."
    icon="📖"
    current="index"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="presentation">1. Présentation</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="chapitres">2. Chapitres détaillés</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="roles">3. Rôles & accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="connexion">4. Connexion</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="dashboard">5. Tableau de bord</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="planification">6. Planification (PTA)</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="activites">7. Activités</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="validation">8. Validation</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="suivi">9. Suivi & évaluation</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="budget">10. Analyse budgétaire</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="notifications">11. Notifications</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="faq">12. Questions fréquentes</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="presentation" titre="1. Présentation de la plateforme">
        <p>
            LEAC (Logiciel d'évaluation des activités de la CANAM) est l'outil de gestion et de coordination des activités de la CANAM. Il permet de
            structurer le <strong>Plan de Travail Annuel (PTA)</strong> autour des résultats stratégiques,
            objectifs, extrants et activités, puis d'en assurer le <strong>suivi de l'exécution</strong> et
            l'<strong>évaluation</strong> (mi-parcours et fin d'exercice).
        </p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">⚙️ Paramétrer</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Décrire l'organigramme (directions, directions centrales, services) et créer les comptes utilisateurs.</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🎯 Planifier</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Décliner objectifs, extrants et activités par exercice et par structure.</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">📊 Suivre & évaluer</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Renseigner l'état d'avancement, le budget consommé et les indicateurs.</p>
            </div>
        </div>
    </x-doc.section>

    <x-doc.section id="chapitres" titre="2. Chapitres détaillés" chapo="Chaque module dispose d'un chapitre dédié, détaillant écran par écran les champs, les règles de gestion et les cas particuliers.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <a href="{{ route('documentation.directions-centrales') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">🏢 Directions Centrales</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Organigramme à trois niveaux, création et rattachement des entités, responsables, statut actif/inactif, suppression.</p>
            </a>
            <a href="{{ route('documentation.utilisateurs') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">👥 Utilisateurs</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Comptes, rattachement à une structure, attribution des rôles, mot de passe, mail de bienvenue, périmètre de visibilité.</p>
            </a>
            <a href="{{ route('documentation.exercices') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">📅 Exercices</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Année budgétaire, fenêtres de saisie, de mi-parcours et d'évaluation, statut actif, export du PTA.</p>
            </a>
            <a href="{{ route('documentation.planification') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">🎯 Planification</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Objectifs stratégiques, résultats et extrants : hiérarchie du cadre logique, création, activation, suppression.</p>
            </a>
            <a href="{{ route('documentation.activites') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">📋 Activités</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Création, chronogramme, coût, activité non programmée, pièces jointes, soumission pour validation.</p>
            </a>
            <a href="{{ route('documentation.validation') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">✅ Validation</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Circuit montant, validation groupée, arbitrage budgétaire (modifier, supprimer, fusionner), historique.</p>
            </a>
            <a href="{{ route('documentation.suivi') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">📊 Suivi &amp; évaluation</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Renseigner l'exécution à mi-parcours et en fin d'exercice, comparatif budgétaire, export.</p>
            </a>
            <a href="{{ route('documentation.missions') }}" class="group rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50 dark:border-slate-700 dark:hover:border-sky-800 dark:hover:bg-sky-950/20">
                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-800 dark:text-slate-100 dark:group-hover:text-sky-200">🧳 Missions</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ordres de mission, participants et signataires, calcul automatique par barème, finalisation et PDF.</p>
            </a>
        </div>
    </x-doc.section>

    <x-doc.section id="roles" titre="3. Rôles & niveaux d'accès" chapo="Les droits dépendent de votre rôle et de votre rattachement dans la hiérarchie des structures.">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Rôle</th>
                        <th class="py-2 pr-4">Périmètre</th>
                        <th class="py-2">Principales actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">superadmin</td>
                        <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Global</td>
                        <td class="py-2">Paramétrage complet : utilisateurs, structures, exercices, référentiel de planification.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">dbcgoq</td>
                        <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Global</td>
                        <td class="py-2">Consolidation, validation finale, analyse budgétaire, ouverture des périodes de suivi. Mêmes permissions que superadmin.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">chef</td>
                        <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Sa structure & ses sous-structures</td>
                        <td class="py-2">Saisie, soumission, validation montante des activités de son périmètre. Consultation seule du référentiel.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">agent</td>
                        <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Son entité</td>
                        <td class="py-2">Consultation et saisie des activités de son entité.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce>
            Un utilisateur peut cumuler plusieurs rôles. Le détail des permissions et de leur effet sur les menus
            est décrit dans le chapitre <a href="{{ route('documentation.utilisateurs') }}#roles" class="font-medium underline">Utilisateurs</a>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="connexion" titre="4. Connexion & profil">
        <ol class="list-decimal space-y-2 pl-5">
            <li>Saisissez votre adresse e-mail professionnelle et votre mot de passe sur l'écran de connexion.</li>
            <li>Accédez à votre profil via le menu en bas de la barre latérale (votre nom) → <strong>Paramètres</strong>.</li>
            <li>Vous pouvez y modifier vos informations, votre mot de passe et vos préférences d'affichage (thème clair/sombre).</li>
        </ol>
    </x-doc.section>

    <x-doc.section id="dashboard" titre="5. Tableau de bord" chapo="Le tableau de bord synthétise l'exercice sélectionné :">
        <x-doc.capture src="images/manuel/dashboard.png" alt="Tableau de bord LEAC">Le tableau de bord : indicateurs clés, chronogramme et répartitions.</x-doc.capture>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Indicateurs clés</strong> : nombre d'objectifs, d'extrants, d'activités, budget total, taux de réalisation.</li>
            <li><strong>Évolution du chronogramme</strong> : volume d'activités planifiées et dynamique budgétaire par période (T1 → T4).</li>
            <li><strong>Répartitions</strong> : budget par objectif, activités par statut, par période du chronogramme et par structure.</li>
        </ul>
        <x-doc.astuce>Utilisez le sélecteur d'exercice pour changer d'année de référence.</x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="planification" titre="6. Planification (PTA)" chapo="Le cadre logique se structure en cascade, de l'objectif stratégique jusqu'à l'activité :">
        <div class="space-y-2">
            <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-200">Objectif stratégique</span>
                <span class="text-slate-500 dark:text-slate-400">→ couvre un ou plusieurs exercices</span>
            </div>
            <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">Résultat stratégique</span>
                <span class="text-slate-500 dark:text-slate-400">→ rattaché à un objectif</span>
            </div>
            <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">Extrant</span>
                <span class="text-slate-500 dark:text-slate-400">→ rattaché à un résultat</span>
            </div>
            <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800 dark:bg-sky-950/50 dark:text-sky-200">Activité</span>
                <span class="text-slate-500 dark:text-slate-400">→ action concrète, budgétée et positionnée sur le chronogramme</span>
            </div>
        </div>
        <x-doc.astuce>
            Le détail de ce référentiel (champs, activation, suppression) fait l'objet du chapitre
            <a href="{{ route('documentation.planification') }}" class="font-medium underline">🎯 Planification</a>,
            et le calendrier (fenêtres, statut actif) du chapitre <a href="{{ route('documentation.exercices') }}" class="font-medium underline">📅 Exercices</a>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="activites" titre="7. Gestion des activités">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Créer une activité</h3>
        <ol class="list-decimal space-y-1.5 pl-5">
            <li>Depuis le menu <strong>Activités → Ajouter</strong>, sélectionnez l'extrant de rattachement.</li>
            <li>Renseignez l'intitulé, le coût (FCFA), l'indicateur objectivement vérifiable et le moyen de vérification.</li>
            <li>Cochez les périodes du chronogramme prévues (T1 à T4).</li>
            <li>Enregistrez en <strong>brouillon</strong>, puis soumettez pour validation.</li>
        </ol>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Activité non programmée</h3>
        <p>
            Une activité imprévue (hors PTA) peut être ajoutée depuis la page <strong>Suivi</strong> via le bouton
            « + Activité non programmée ». Elle est rattachée directement à l'exercice en cours.
        </p>
        <x-doc.astuce>
            Détail complet des champs, du cycle de vie et des pièces jointes dans le chapitre
            <a href="{{ route('documentation.activites') }}" class="font-medium underline">📋 Activités</a>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="validation" titre="8. Circuit de validation" chapo="Les activités suivent une validation montante le long de la hiérarchie :">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">📝 Brouillon</span>
            <span class="text-slate-400">→</span>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">⏳ En attente</span>
            <span class="text-slate-400">→</span>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">✅ Validé</span>
            <span class="text-slate-400">ou</span>
            <span class="rounded-full bg-rose-100 px-3 py-1 text-rose-800 dark:bg-rose-950/40 dark:text-rose-200">❌ Rejeté</span>
        </div>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Une activité <strong>rejetée</strong> peut être corrigée puis re-soumise ; le motif de refus est indiqué.</li>
            <li>Le responsable peut procéder à un <strong>arbitrage budgétaire</strong> (modification, suppression ou fusion) avant validation.</li>
            <li>Chaque action est tracée dans l'historique de validation de l'activité.</li>
        </ul>
        <x-doc.astuce>
            Écrans, périmètre exact du chef et arbitrage détaillés dans le chapitre
            <a href="{{ route('documentation.validation') }}" class="font-medium underline">✅ Validation</a>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="suivi" titre="9. Suivi & évaluation">
        <p>
            Depuis la page <strong>Suivi</strong> (mi-parcours ou fin d'année) ou le détail d'une activité, cliquez sur
            <strong>« ✎ Renseigner l'évaluation »</strong> pour ouvrir la fenêtre de saisie. La saisie de l'exécution
            reste <strong>ouverte en permanence</strong> : les dates de mi-parcours et d'évaluation réglées sur
            l'exercice n'affichent qu'un bandeau indicatif, elles ne ferment jamais le formulaire.
        </p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">État d'exécution</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Non réalisé / En cours / Réalisé, avec une observation obligatoire.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Budget utilisé</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Montant réellement consommé ; visible uniquement pour superadmin/dbcgoq.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Valeur de l'indicateur</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Résultat mesuré de l'indicateur de l'activité (texte libre).</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Écart budgétaire</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Planifié moins utilisé, sur le détail de l'activité.</p>
            </div>
        </div>
        <x-doc.astuce>
            Un écart budgétaire positif indique une économie ; en rouge, un dépassement du budget planifié. Détail
            complet dans le chapitre <a href="{{ route('documentation.suivi') }}" class="font-medium underline">📊 Suivi &amp; évaluation</a>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="budget" titre="10. Analyse budgétaire">
        <p>
            Réservée aux profils de consolidation (DBCGOQ / super administrateur), la page d'analyse budgétaire
            offre une vue transversale : répartition par structure, par objectif, distribution par tranches de coût
            et comparaison planifié / consommé.
        </p>
    </x-doc.section>

    <x-doc.section id="notifications" titre="11. Notifications">
        <p>
            Vous êtes notifié (en application et par e-mail) lors des événements clés : soumission d'une activité,
            validation ou rejet, changement de budget, ouverture d'une période d'évaluation. La cloche de
            notifications regroupe les alertes non lues ; vous pouvez les marquer comme lues individuellement ou en bloc.
        </p>
    </x-doc.section>

    <x-doc.section id="faq" titre="12. Questions fréquentes">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Je ne peux pas saisir l'exécution d'une activité.</summary>
                <p class="mt-2">La saisie n'est possible que pendant les périodes de mi-parcours ou d'évaluation ouvertes par le DBCGOQ. Un bandeau vous indique l'état de la fenêtre sur la page Suivi.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Mon activité a été rejetée, que faire ?</summary>
                <p class="mt-2">Consultez le motif de refus, corrigez l'activité (elle redevient modifiable) puis re-soumettez-la pour validation.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Je ne vois pas toutes les activités.</summary>
                <p class="mt-2">Votre périmètre d'affichage dépend de votre structure : un chef voit son sous-arbre, un agent voit son entité. Les filtres en haut de liste permettent d'affiner l'affichage.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Je ne vois pas les menus « Directions Centrales » et « Utilisateurs ».</summary>
                <p class="mt-2">Ces deux menus sont réservés aux rôles <strong>superadmin</strong> et <strong>dbcgoq</strong>. Les autres profils n'y ont pas accès, même en lecture.</p>
            </details>
        </div>
    </x-doc.section>
</x-doc.page>
