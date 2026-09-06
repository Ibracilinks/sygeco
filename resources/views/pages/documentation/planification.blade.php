<x-doc.page
    title="Planification"
    subtitle="Le référentiel du cadre logique : objectifs stratégiques, résultats et extrants sur lesquels s'appuient les activités."
    icon="🎯"
    current="planification"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="hierarchie">2. La hiérarchie du cadre logique</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="objectifs">3. Objectifs stratégiques</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="resultats">4. Résultats stratégiques</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="extrants">5. Extrants</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="activation">6. Activer / désactiver / supprimer</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="lien-activites">7. Lien avec les activités</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">8. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">9. Bonnes pratiques</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="acces" titre="1. Qui a accès à ce module">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Action</th>
                        <th class="py-2">Rôles concernés</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4">Consulter (liste et fiches) les objectifs, résultats et extrants</td><td class="py-2">superadmin, dbcgoq, chef, agent-planification, suivi-evaluation</td></tr>
                    <tr><td class="py-2 pr-4">Créer, modifier, supprimer, activer/désactiver un objectif ou un extrant</td><td class="py-2">superadmin, dbcgoq</td></tr>
                    <tr><td class="py-2 pr-4">Créer, modifier, supprimer, activer/désactiver un résultat</td><td class="py-2">dbcgoq uniquement</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce type="attention">
            Sur les <strong>résultats</strong>, seul le rôle <strong>dbcgoq</strong> voit apparaître les boutons
            « Nouveau résultat », « Modifier » et « Supprimer » — même un compte superadmin ne les verra pas. Si vous
            devez créer ou modifier un résultat et que vous ne voyez pas ces boutons, demandez à un utilisateur dbcgoq
            de s'en charger.
        </x-doc.astuce>
        <p>
            Un <em>chef</em> qui consulte la liste ne voit que les objectifs/résultats/extrants portant au moins une
            activité de son entité ; la fiche d'un élément hors de son périmètre lui est inaccessible (page introuvable).
        </p>
    </x-doc.section>

    <x-doc.section id="hierarchie" titre="2. La hiérarchie du cadre logique" chapo="Quatre niveaux s'enchaînent, du plus stratégique au plus opérationnel.">
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
                <span class="text-slate-500 dark:text-slate-400">→ rattaché à un résultat (l'objectif en est dérivé automatiquement)</span>
            </div>
            <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800 dark:bg-sky-950/50 dark:text-sky-200">Activité</span>
                <span class="text-slate-500 dark:text-slate-400">→ rattachée à un extrant, seul niveau du référentiel qui en accueille</span>
            </div>
        </div>
        <p>
            « Résultat » et « Extrant » ne sont <strong>pas synonymes</strong> : ce sont deux niveaux successifs et
            distincts du cadre logique. Un <strong>objectif</strong> peut être <em>pluriannuel</em> — il couvre alors
            plusieurs exercices à la fois (par exemple 2026 à 2030) ; la fiche de l'objectif affiche dans ce cas un
            badge « pluriannuel » et sa période sous la forme « 2026-2030 ».
        </p>
    </x-doc.section>

    <x-doc.section id="objectifs" titre="3. Objectifs stratégiques">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Écran « Objectifs stratégiques »</h3>
        <p>Quatre cartes de synthèse (Total, Actifs, Inactifs, Avec résultats) puis un tableau paginé à 15 lignes, filtrable par recherche (code ou libellé), exercice, année et statut, triable par ordre, code, année ou date de création.</p>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Objectif</strong> : code + libellé tronqué.</li>
            <li><strong>Exercice</strong> : période couverte et liste des exercices rattachés (ou « Aucun exercice »).</li>
            <li><strong>Structure</strong> : ordre d'affichage, nombre de résultats et d'extrants.</li>
            <li><strong>Statut</strong> : badge Actif / Inactif.</li>
        </ul>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Créer un objectif</h3>
        <ol class="list-decimal space-y-1.5 pl-5">
            <li>Cochez <strong>au moins un exercice couvert*</strong> (plusieurs cases possibles pour un objectif pluriannuel).</li>
            <li>Renseignez le <strong>Code*</strong> (20 caractères max., unique) et l'<strong>Ordre</strong> d'affichage.</li>
            <li>Rédigez le <strong>Libellé*</strong> (500 caractères max.) et, si besoin, une <strong>Description</strong> libre.</li>
            <li>Choisissez le <strong>Statut*</strong> (Actif par défaut).</li>
            <li>Enregistrez : message « Objectif {code} créé avec succès. ».</li>
        </ol>
        <x-doc.astuce type="attention">
            Si aucun exercice n'existe encore, le formulaire affiche « Aucun exercice disponible. Créez d'abord un
            exercice. » avec un lien direct vers le module <a href="{{ route('documentation.exercices') }}" class="font-medium underline">Exercices</a>.
        </x-doc.astuce>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Fiche d'un objectif</h3>
        <p>Cartes Statut / Résultats / Extrants / Activités / Budget total, bloc « Résultats et extrants » avec le nombre d'extrants par résultat, et un bloc « Indicateurs clés » (budget moyen par activité, ordre, résultats actifs, description).</p>
    </x-doc.section>

    <x-doc.section id="resultats" titre="4. Résultats stratégiques">
        <p>Écran « Résultats stratégiques » : cartes Total / Actifs / Inactifs / Avec extrants ; filtres par recherche, objectif (limité aux objectifs actifs de l'exercice actif) et statut.</p>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Créer un résultat</h3>
        <ol class="list-decimal space-y-1.5 pl-5">
            <li>Sélectionnez l'<strong>Objectif*</strong> de rattachement (libellé affiché : code — période — début du texte).</li>
            <li>Renseignez le <strong>Code*</strong> (20 caractères max., unique) et l'<strong>Ordre</strong>.</li>
            <li>Rédigez le <strong>Libellé*</strong> et, si besoin, une <strong>Description</strong>.</li>
            <li>Laissez « Résultat actif » coché pour qu'il soit immédiatement utilisable.</li>
            <li>Enregistrez : message « Résultat {code} créé avec succès. ».</li>
        </ol>
        <p>La fiche d'un résultat affiche Statut / Extrants / Activités / Budget total / Budget moyen, la liste des extrants rattachés avec leur budget et un lien « Ajouter un extrant ».</p>
    </x-doc.section>

    <x-doc.section id="extrants" titre="5. Extrants">
        <p>Écran « Extrants stratégiques » : cartes Total / Actifs / Inactifs / Avec activités ; filtres par recherche, objectif et statut, avec bouton « Reset ».</p>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Créer un extrant</h3>
        <ol class="list-decimal space-y-1.5 pl-5">
            <li>Sélectionnez le <strong>Résultat*</strong> de rattachement — l'aide précise : « L'objectif de rattachement est déterminé automatiquement par le résultat choisi. »</li>
            <li>Renseignez le <strong>Code*</strong> (20 caractères max.) et l'<strong>Ordre</strong>.</li>
            <li>Cochez « Actif » si l'extrant doit être immédiatement utilisable dans les activités.</li>
            <li>Rédigez le <strong>Libellé*</strong> et, en option, une <strong>Description</strong>.</li>
            <li>Enregistrez : message « Extrant {code} créé avec succès. ».</li>
        </ol>
        <x-doc.astuce>
            Contrairement au code d'un objectif ou d'un résultat, le code d'un extrant <strong>n'est pas unique dans
            toute la base</strong> : la même nomenclature (« EXT_001 ») peut être réutilisée d'un exercice à l'autre.
            L'unicité s'apprécie seulement <strong>au sein des exercices couverts par l'objectif</strong> du résultat
            choisi ; en cas de doublon sur ces exercices, le message précise l'exercice en conflit.
        </x-doc.astuce>
        <p>La fiche d'un extrant affiche Statut / Activités / Structures / Budget total / Budget moyen, les 10 dernières activités rattachées et un lien « Ajouter une activité ».</p>
    </x-doc.section>

    <x-doc.section id="activation" titre="6. Activer, désactiver ou supprimer" chapo="Les trois niveaux partagent les mêmes règles.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Activer/désactiver</strong> (icône dédiée) bascule le statut immédiatement, sans aucune vérification des enfants : vous pouvez désactiver un objectif dont les résultats restent actifs, ou un résultat dont les extrants restent actifs. Un élément <strong>inactif</strong> disparaît des listes de sélection des niveaux inférieurs et des activités, mais reste consultable et conserve son historique.</li>
            <li><strong>Supprimer</strong>, à l'inverse, est bloqué dès que l'élément a des enfants directs :</li>
        </ul>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Suppression de</th>
                        <th class="py-2 pr-4">Bloquée si</th>
                        <th class="py-2">Message exact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Objectif</td><td class="py-2 pr-4">Il a des extrants</td><td class="py-2">« Impossible de supprimer un objectif qui a des extrants. »</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Résultat</td><td class="py-2 pr-4">Il a des extrants</td><td class="py-2">« Impossible de supprimer un résultat qui a des extrants. »</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Extrant</td><td class="py-2 pr-4">Il a des activités</td><td class="py-2">« Impossible de supprimer un extrant qui a des activités. »</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce type="attention">
            Un objectif ayant des <strong>résultats mais aucun extrant</strong> peut néanmoins être supprimé : la
            vérification porte sur la présence d'extrants, pas de résultats. Vérifiez le bloc « Résultats et extrants »
            de la fiche avant de supprimer un objectif.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="lien-activites" titre="7. Lien avec les activités">
        <p>
            Lorsqu'une activité est créée ou modifiée, la liste d'extrants proposée ne présente que les extrants
            <strong>actifs</strong> dont l'objectif couvre l'<strong>exercice actif</strong> de la session. Un extrant
            désactivé, ou rattaché à un objectif qui ne couvre pas l'exercice en cours, n'apparaît donc pas dans le
            sélecteur — même s'il existe déjà des activités qui lui sont rattachées.
        </p>
        <p>L'écran « Activités » regroupe systématiquement son affichage selon ce même cadre logique (Résultat → Extrant → Activités), voir le chapitre <a href="{{ route('documentation.activites') }}" class="underline">Activités</a>.</p>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="8. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Sélectionnez au moins un exercice couvert par cet objectif. »</summary>
                <p class="mt-2">Aucune case « Exercices couverts » n'est cochée sur le formulaire d'objectif. Cochez au moins un exercice existant.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Le code « … » est déjà utilisé par un extrant de l'exercice {année}. »</summary>
                <p class="mt-2">Un autre extrant, rattaché à un objectif couvrant le même exercice, porte déjà ce code. Choisissez un code différent ou vérifiez qu'il ne s'agit pas d'un doublon.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Impossible de supprimer un objectif / résultat / extrant qui a des extrants / activités. »</summary>
                <p class="mt-2">Retirez ou déplacez d'abord les enfants directs, ou désactivez l'élément plutôt que de le supprimer (voir §6).</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Je ne vois pas les boutons de gestion des résultats.</summary>
                <p class="mt-2">Ces boutons ne sont visibles que pour le rôle <strong>dbcgoq</strong> (voir §1). Demandez à un utilisateur disposant de ce rôle d'effectuer l'opération.</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="9. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Construisez le référentiel <strong>de haut en bas</strong> : objectifs, puis résultats, puis extrants — chaque niveau a besoin du précédent.</li>
            <li>Adoptez une convention de <strong>codes stable</strong> (ex. <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">OS_001</code>, <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">RS_001</code>, <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">EXT_001</code>) : elle apparaît dans les exports et les cadres logiques.</li>
            <li>Préférez la <strong>désactivation</strong> à la suppression pour tout élément déjà utilisé par des activités validées.</li>
            <li>Pour un objectif pluriannuel, vérifiez que la liste des « Exercices couverts » est mise à jour chaque année tant que l'objectif reste d'actualité.</li>
            <li>Avant de créer une activité, contrôlez que son extrant apparaît bien dans le sélecteur (extrant actif, exercice couvert) — sinon, corrigez le référentiel plutôt que l'activité.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre précédent : <a href="{{ route('documentation.exercices') }}" class="font-medium underline">📅 Exercices</a> ·
            Chapitre suivant : <a href="{{ route('documentation.activites') }}" class="font-medium underline">📋 Activités</a>.
        </p>
    </x-doc.section>
</x-doc.page>
