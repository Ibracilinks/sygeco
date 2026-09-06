<x-doc.page
    title="Suivi & évaluation"
    subtitle="Renseigner l'exécution des activités validées, à mi-parcours et en fin d'exercice."
    icon="📊"
    current="suivi"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="periodes">2. Les deux périodes & la fenêtre</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="ecran">3. Écran de suivi</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="renseigner">4. Renseigner l'évaluation</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="qui-evalue">5. Qui peut évaluer quelles activités</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="export">6. Exporter</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="difference">7. Mi-parcours vs fin d'année</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="notifications">8. Notifications d'ouverture</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">9. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">10. Bonnes pratiques</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="acces" titre="1. Qui a accès à ce module">
        <p>
            L'écran est ouvert aux rôles <strong>superadmin</strong>, <strong>dbcgoq</strong>, <strong>chef</strong>,
            <strong>agent</strong> et <strong>suivi-evaluation</strong>. La capacité réelle à
            <strong>renseigner</strong> une évaluation dépend toutefois de la permission
            <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">evaluate_activites</code> (ou
            <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">validate_activites</code>), détenue par
            superadmin, dbcgoq, chef et suivi-evaluation.
        </p>
        <x-doc.astuce type="attention">
            Un <strong>agent</strong> seul (sans rôle chef ni dbcgoq) peut consulter l'écran mais <strong>ne peut pas
            renseigner d'évaluation</strong> : il ne détient pas la permission requise. Le message « Vous n'êtes pas
            autorisé à renseigner l'évaluation. » s'affiche s'il tente l'action.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="periodes" titre="2. Les deux périodes & le sens de la « fenêtre »" chapo="Mi-parcours et fin d'année sont deux moments du suivi, réglés depuis la fiche de l'exercice.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">📊 Mi-parcours</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ne liste que les activités programmées sur le 1er ou le 2e trimestre.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🏁 Fin d'année</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Reprend tout le chronogramme, des quatre trimestres.</p>
            </div>
        </div>
        <x-doc.astuce type="attention">
            Contrairement à une fenêtre qui « fermerait » la saisie, les dates réglées sur l'exercice (module
            <a href="{{ route('documentation.exercices') }}" class="font-medium underline">Exercices</a>) sont
            désormais <strong>purement indicatives</strong> : elles affichent un bandeau (fenêtre ouverte ou hors
            période) et déclenchent une notification d'ouverture, mais <strong>la saisie reste possible en
            permanence</strong>, avant, pendant et après ces dates.
        </x-doc.astuce>
        <p>
            Le bandeau affiché en haut de l'écran indique soit <em>« 🟢 Fenêtre de saisie {période} ouverte —
            jusqu'au JJ/MM/AAAA »</em>, soit <em>« Hors période {période} (JJ/MM/AAAA → JJ/MM/AAAA) — la saisie reste
            ouverte en permanence »</em>.
        </p>
    </x-doc.section>

    <x-doc.section id="ecran" titre="3. Écran de suivi" chapo="Un écran distinct par période (mi-parcours / fin d'année), avec ses propres filtres et compteurs.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">État d'exécution</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Non réalisé / En cours / Réalisé, avec une observation obligatoire.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Budget utilisé</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Facultatif, réservé aux profils d'administration (superadmin/dbcgoq).</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Valeur de l'indicateur</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Texte libre obligatoire (un nombre, une fraction ou une observation).</p>
            </div>
        </div>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Filtres</strong> : recherche par nom, extrant, structure, état d'exécution (y compris « Non évaluée », pour les activités sans aucune saisie sur la période).</li>
            <li><strong>Périmètre</strong> : uniquement les activités <strong>validées</strong> de l'exercice actif.</li>
            <li><strong>Compteurs</strong> : Activités validées (total + % saisie), Réalisé, En cours, Non réalisé, Non évaluée, Taux de réalisation.</li>
        </ul>
        <x-doc.astuce type="attention">
            Une activité <strong>jamais évaluée</strong> compte uniquement dans « Non évaluée » — elle n'est pas
            comptée comme « Non réalisée ». Le <strong>taux de réalisation</strong> se calcule uniquement sur les
            activités effectivement évaluées, pas sur le total des activités validées.
        </x-doc.astuce>
        <p>
            Le tableau affiche par activité : nom, extrant/structure, coût planifié, badge d'état d'exécution, date et
            auteur de la dernière mise à jour, observation, budget utilisé et écart, valeur d'indicateur, et le bouton
            <strong>« ✎ Renseigner {période} »</strong>. Pagination : 20 activités par page.
        </p>
    </x-doc.section>

    <x-doc.section id="renseigner" titre="4. Renseigner l'évaluation">
        <p>Le bouton « ✎ Renseigner » ouvre une fenêtre avec les champs suivants :</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Champ</th>
                        <th class="py-2 pr-4">Obligatoire</th>
                        <th class="py-2">Précisions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">État d'exécution</td><td class="py-2 pr-4">Oui</td><td class="py-2">Non réalisé / En cours / Réalisé.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Observation</td><td class="py-2 pr-4">Oui</td><td class="py-2">1000 caractères max. Message si vide : « L'observation est obligatoire. »</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Budget utilisé (FCFA)</td><td class="py-2 pr-4">Non</td><td class="py-2">Visible uniquement pour superadmin/dbcgoq ; pour les autres profils, le champ n'est pas proposé et toute valeur postée est ignorée.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Valeur de l'indicateur</td><td class="py-2 pr-4">Oui</td><td class="py-2">Texte libre, 255 caractères max. (ex. « 12 », « 3 sur 5 », « Rapport produit »). Message si vide : « La valeur de l'indicateur est obligatoire. »</td></tr>
                </tbody>
            </table>
        </div>
        <p>Après enregistrement, la ligne du tableau se met à jour immédiatement, sans recharger la page. Message de succès : « Évaluation Mi-parcours enregistrée. » (ou « Évaluation Fin d'année enregistrée. »).</p>
        <x-doc.astuce>
            L'<strong>écart budgétaire</strong> affiché (planifié − utilisé) est positif pour une économie et négatif
            pour un dépassement (affiché en rouge) ; il n'existe pas de seuil d'alerte automatique — seule la couleur
            distingue les deux cas.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="qui-evalue" titre="5. Qui peut évaluer quelles activités">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Seules les activités au statut <strong>Validé</strong> sont évaluables ; sinon : « Seules les activités validées peuvent être évaluées. »</li>
            <li><strong>superadmin</strong> : toutes les activités.</li>
            <li><strong>dbcgoq</strong> : toutes les activités validées.</li>
            <li><strong>chef</strong> : son entité et tout son sous-arbre.</li>
            <li><strong>suivi-evaluation</strong> : toutes les activités validées de l'exercice, sans restriction de structure.</li>
            <li><strong>agent</strong> : n'a pas la permission d'évaluer (voir §1).</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="export" titre="6. Exporter">
        <p>
            Le bouton d'export génère un fichier
            <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">cadre-logique-evaluation-{période}-{date}.xls</code>
            reprenant le cadre logique complet (Objectif → Résultat → Extrant → Activités) avec, pour chaque
            activité : indicateur, valeur atteinte, état d'exécution (Réalisé / En cours / Non réalisé), structure(s)
            responsable(s) et observations. Il respecte les mêmes filtres que l'écran de suivi.
        </p>
    </x-doc.section>

    <x-doc.section id="difference" titre="7. Différence mi-parcours / fin d'année">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Les deux évaluations sont <strong>indépendantes</strong> : une saisie de fin d'année n'écrase pas celle de mi-parcours, chacune reste consultable séparément sur son propre écran.</li>
            <li>Rien n'empêche de revenir modifier l'évaluation de mi-parcours après l'ouverture de la période de fin d'année : la saisie reste ouverte en permanence pour les deux périodes, indépendamment l'une de l'autre.</li>
            <li>Les indicateurs globaux affichés ailleurs dans l'application (tableau de bord, fiches) reflètent la <strong>dernière évaluation saisie</strong>, qu'elle provienne du mi-parcours ou de la fin d'année.</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="notifications" titre="8. Notifications d'ouverture de fenêtre">
        <p>
            À la date d'ouverture d'une fenêtre (mi-parcours ou évaluation, réglée sur l'exercice), les rôles
            <strong>chef</strong> et <strong>dbcgoq</strong> reçoivent une notification (mail et in-app) les invitant à
            renseigner l'état d'exécution de leurs activités. Chaque fenêtre n'est notifiée <strong>qu'une seule
            fois</strong> par ouverture.
        </p>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="9. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Vous n'êtes pas autorisé à renseigner l'évaluation. »</summary>
                <p class="mt-2">Votre rôle ne détient pas la permission d'évaluation (cas d'un simple agent). Demandez à votre chef ou à la cellule suivi &amp; évaluation de renseigner la donnée.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Seules les activités validées peuvent être évaluées. »</summary>
                <p class="mt-2">L'activité est encore en brouillon, en attente ou a été rejetée : elle doit d'abord passer par le module <a href="{{ route('documentation.validation') }}" class="underline">Validation</a>.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« L'observation est obligatoire. » / « La valeur de l'indicateur est obligatoire. »</summary>
                <p class="mt-2">Ces deux champs ne peuvent pas être laissés vides à l'enregistrement, quel que soit l'état d'exécution choisi.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Je ne vois pas le champ « Budget utilisé ».</summary>
                <p class="mt-2">Ce champ n'est proposé qu'aux profils superadmin et dbcgoq. Signalez le montant réellement consommé à la DBCGOQ si vous n'y avez pas accès.</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="10. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Renseignez le suivi <strong>dès que l'activité progresse</strong>, sans attendre la fermeture indicative d'une fenêtre — la saisie reste toujours ouverte.</li>
            <li>Rédigez une <strong>observation utile</strong> : elle constitue la trace qualitative de l'exécution consultée par la Direction Générale.</li>
            <li>Utilisez le filtre <strong>« Non évaluée »</strong> avant l'échéance pour identifier rapidement les activités encore sans saisie.</li>
            <li>Ne confondez pas <strong>mi-parcours</strong> et <strong>fin d'année</strong> : renseignez chaque période sur son propre écran, sans attendre que l'une remplace l'autre.</li>
            <li>Utilisez l'export pour produire le document de synthèse d'exécution à transmettre à la Direction Générale.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre précédent : <a href="{{ route('documentation.validation') }}" class="font-medium underline">✅ Validation</a>.
        </p>
    </x-doc.section>
</x-doc.page>
