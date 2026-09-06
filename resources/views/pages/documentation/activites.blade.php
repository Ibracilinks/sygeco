<x-doc.page
    title="Activités"
    subtitle="Créer, programmer et soumettre les actions concrètes du PTA, budgétées et positionnées sur le chronogramme."
    icon="📋"
    current="activites"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="cycle">2. Cycle de vie d'une activité</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="liste">3. Écran « Activités »</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="creer">4. Créer une activité</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="champs">5. Détail des champs</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="non-programmee">6. Activité non programmée</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="fiche">7. Fiche d'une activité</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="soumission">8. Modifier & soumettre</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pieces-jointes">9. Pièces jointes</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="notifications">10. Notifications liées</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">11. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">12. Bonnes pratiques</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="acces" titre="1. Qui a accès à ce module">
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
                    <tr><td class="py-2 pr-4">Consulter la liste et les fiches</td><td class="py-2 pr-4 font-mono text-xs">view_activites</td><td class="py-2">superadmin, dbcgoq, chef, agent, agent-planification, suivi-evaluation</td></tr>
                    <tr><td class="py-2 pr-4">Créer une activité</td><td class="py-2 pr-4 font-mono text-xs">create_activites</td><td class="py-2">superadmin, dbcgoq, chef, agent-planification</td></tr>
                    <tr><td class="py-2 pr-4">Modifier / supprimer une activité</td><td class="py-2 pr-4 font-mono text-xs">edit_activites / delete_activites</td><td class="py-2">superadmin, dbcgoq, et son auteur (chef/agent-planification) tant qu'elle reste modifiable</td></tr>
                    <tr><td class="py-2 pr-4">Soumettre pour validation</td><td class="py-2 pr-4 font-mono text-xs">submit_activites</td><td class="py-2">superadmin, dbcgoq, chef, agent-planification</td></tr>
                </tbody>
            </table>
        </div>
        <p>
            Un <em>agent</em> (rôle seul, sans <em>chef</em> ni <em>dbcgoq</em>) ne voit que <strong>ses propres
            saisies</strong>. Un <em>chef</em> voit toute son entité et son sous-arbre. Les rôles
            <em>agent-planification</em> et <em>suivi-evaluation</em> sont cantonnés à leur propre entité (sans
            sous-arbre). La validation elle-même — accorder ou refuser une activité — se passe dans le module
            <a href="{{ route('documentation.validation') }}" class="underline">Validation</a>, pas ici.
        </p>
    </x-doc.section>

    <x-doc.section id="cycle" titre="2. Cycle de vie d'une activité" chapo="Quatre statuts, un circuit de validation montant.">
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
            <li>Une activité n'est <strong>modifiable</strong> par son auteur que tant qu'elle est en <strong>Brouillon</strong> ou <strong>Rejetée</strong>.</li>
            <li>Seule une activité <strong>Brouillon</strong> ou <strong>Rejetée</strong> peut être <strong>soumise</strong> (ou re-soumise).</li>
            <li>Une fois <strong>En attente</strong>, elle échappe à son auteur : seul l'arbitrage du module Validation peut encore la modifier, la supprimer ou la fusionner avant décision.</li>
            <li>Une activité <strong>Validée</strong> n'est plus modifiable du tout par ce module ; elle devient éligible à la saisie d'exécution du chapitre <a href="{{ route('documentation.suivi') }}" class="underline">Suivi &amp; évaluation</a>.</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="liste" titre="3. Écran « Activités »" chapo="La liste se présente comme un cadre logique, pas comme un tableau plat : Résultat → Extrant → Activités.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Total</p></div>
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Brouillon</p></div>
            <div class="rounded-lg border border-amber-200 p-3 dark:border-amber-900/70"><p class="text-xs font-semibold text-amber-700 dark:text-amber-300">En attente</p></div>
            <div class="rounded-lg border border-emerald-200 p-3 dark:border-emerald-900/70"><p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Validé</p></div>
        </div>
        <p>Ces compteurs tiennent compte de votre périmètre de visibilité, de l'exercice actif et des filtres appliqués.</p>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Filtres</strong> : recherche (nom ou indicateur), extrant, structure, statut, trimestre.</li>
            <li><strong>Regroupement</strong> : chaque bloc « Résultat » liste ses extrants, chacun listant ses activités (intitulé, IOV, chronogramme, structure(s), coût ou « PM », statut).</li>
            <li><strong>Actions par ligne</strong> : Voir (toujours), Modifier/Supprimer (si l'activité est encore modifiable et que vous en avez la permission), Soumettre (si elle est soumettable et que vous en avez le droit).</li>
        </ul>
        <p>La liste affiche <strong>15 activités par page</strong>.</p>
        <x-doc.astuce type="attention">
            Il n'existe pas de bouton « Dupliquer » ni « Exporter » sur cette liste : pour obtenir un export Excel du
            cadre logique, utilisez le bouton d'export du module
            <a href="{{ route('documentation.validation') }}" class="font-medium underline">Validation</a> ou de celui de
            <a href="{{ route('documentation.suivi') }}" class="font-medium underline">Suivi &amp; évaluation</a>.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="creer" titre="4. Créer une activité" chapo="Depuis le menu Activités → Ajouter.">
        <ol class="list-decimal space-y-1.5 pl-5">
            <li>Sélectionnez l'<strong>Extrant</strong> de rattachement (seuls les extrants actifs de l'exercice en cours sont proposés).</li>
            <li>Renseignez l'<strong>intitulé</strong>, l'<strong>indicateur objectivement vérifiable</strong> et le <strong>moyen de vérification</strong>.</li>
            <li>Indiquez le <strong>coût</strong> en FCFA — ou cochez <strong>« Pour mémoire »</strong> si le coût est déjà porté par une autre activité (le coût est alors forcé à 0 et sorti du budget).</li>
            <li>Cochez au moins une case du <strong>chronogramme</strong> (T1 à T4) : c'est obligatoire pour une activité programmée normalement.</li>
            <li>Choisissez la <strong>structure porteuse</strong> et, si besoin, une ou plusieurs <strong>structures intervenantes</strong>.</li>
            <li>Enregistrez en <strong>brouillon</strong> : message « Activité créée avec succès. » — puis soumettez quand elle est prête (§8).</li>
        </ol>
    </x-doc.section>

    <x-doc.section id="champs" titre="5. Détail des champs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Champ</th>
                        <th class="py-2 pr-4">Obligatoire</th>
                        <th class="py-2">Règles & précisions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Extrant</td><td class="py-2 pr-4">Oui</td><td class="py-2">Doit exister et, en pratique, être actif et couvrir l'exercice en cours.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Structure porteuse</td><td class="py-2 pr-4">Oui</td><td class="py-2">Recadrée automatiquement sur votre propre entité si vous n'êtes ni superadmin ni dbcgoq et que vous tentez d'en choisir une autre.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Intitulé</td><td class="py-2 pr-4">Oui</td><td class="py-2">Doit être unique pour l'exercice : un même intitulé ne peut pas être programmé deux fois sur le même exercice (un intitulé libéré par une suppression redevient disponible).</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Indicateur objectivement vérifiable / Moyen de vérification</td><td class="py-2 pr-4">Oui</td><td class="py-2">Texte libre.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Coût (FCFA)</td><td class="py-2 pr-4">Oui, sauf « Pour mémoire »</td><td class="py-2">Numérique, ≥ 0, plafonné à 9 999 999 999 999,99 FCFA.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Pour mémoire (PM)</td><td class="py-2 pr-4">Non</td><td class="py-2">Coché, le coût est ignoré (mis à 0) : utile quand la dépense est déjà budgétée par une autre activité.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Chronogramme T1-T4</td><td class="py-2 pr-4">Au moins une case</td><td class="py-2">« Le chronogramme est obligatoire : sélectionnez au moins une période. » si aucune case cochée (hors activité non programmée).</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Structures intervenantes</td><td class="py-2 pr-4">Non</td><td class="py-2">Structures additionnelles associées à l'activité ; la structure porteuse en est automatiquement exclue.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Commentaires</td><td class="py-2 pr-4">Non</td><td class="py-2">Texte libre, non transmis à la validation.</td></tr>
                </tbody>
            </table>
        </div>
    </x-doc.section>

    <x-doc.section id="non-programmee" titre="6. Activité non programmée">
        <p>
            Une activité imprévue (hors PTA initial) s'ajoute depuis la page
            <a href="{{ route('documentation.suivi') }}" class="underline">Suivi</a> via le bouton
            « + Activité non programmée ». Elle diffère du flux normal sur plusieurs points :
        </p>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Elle n'exige <strong>ni extrant</strong>, ni chronogramme, ni structures intervenantes : elle se rattache directement à l'exercice actif.</li>
            <li>L'indicateur et le moyen de vérification deviennent facultatifs — laissés vides, ils sont remplis par la mention « Non spécifié (activité non programmée) ».</li>
            <li>Un <strong>état d'exécution</strong> peut être renseigné dès la création (par défaut « Réalisé », puisqu'elle est en général enregistrée après coup).</li>
            <li>Son statut de départ dépend de votre profil : <strong>Validée</strong> directement si vous détenez la permission de validation (superadmin/dbcgoq), sinon <strong>En attente</strong>.</li>
            <li>Un <strong>exercice actif</strong> est indispensable : sans lui, le message « Aucun exercice actif : impossible d'enregistrer une activité non programmée. » s'affiche.</li>
        </ul>
        <p>Message de succès : « Activité non programmée enregistrée dans le suivi. » — vous êtes redirigé vers l'écran de suivi mi-parcours.</p>
    </x-doc.section>

    <x-doc.section id="fiche" titre="7. Fiche d'une activité">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>En-tête</strong> : badges statut et « Non programmée » le cas échéant, exercice de rattachement.</li>
            <li><strong>Fil du cadre logique</strong> : Objectif › Résultat › Extrant, chaque maillon cliquable.</li>
            <li><strong>Chiffres clés</strong> : coût, chronogramme, coût moyen par période, structure responsable.</li>
            <li><strong>Traçabilité</strong> : saisie (auteur, date), soumission, validation ou refus (avec motif).</li>
            <li><strong>Historique des actions</strong> : chaque étape (soumission, validation, refus, arbitrage) horodatée et attribuée à son auteur.</li>
            <li><strong>Pièces jointes</strong> : liste des fichiers déjà déposés et formulaire d'ajout (§9).</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="soumission" titre="8. Modifier & soumettre">
        <p>Le bouton porte le libellé <strong>« Soumettre »</strong>, ou <strong>« Re-soumettre »</strong> si l'activité a déjà été rejetée une fois. Il n'apparaît que si l'activité est encore soumettable et que vous en avez la permission.</p>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Soumettre fait passer l'activité en <strong>En attente</strong>, horodate la soumission, et notifie les chefs de la structure porteuse (à défaut, tous les validateurs).</li>
            <li>Si l'activité ne peut pas être soumise (statut incompatible), le message « Impossible de soumettre cette activité. » s'affiche.</li>
            <li>Succès : « Activité soumise avec succès. »</li>
        </ul>
        <x-doc.astuce>
            Une activité <strong>rejetée</strong> redevient modifiable : corrigez-la (le motif de refus reste visible sur
            la fiche jusqu'à la nouvelle soumission), puis re-soumettez-la.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="pieces-jointes" titre="9. Pièces jointes">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Un fichier de <strong>10 Mo maximum</strong> peut être joint, avec une description facultative (255 caractères max.).</li>
            <li>Le formulaire d'ajout n'apparaît que si vous détenez l'une des permissions <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">edit_activites</code>, <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">validate_activites</code> ou <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">evaluate_activites</code> — la cellule suivi &amp; évaluation peut ainsi joindre des justificatifs d'exécution sans droit de modification de l'activité elle-même.</li>
            <li>Message de succès : « Fichier ajouté avec succès. » ; en cas de droits insuffisants : « Vous n'êtes pas autorisé à ajouter des fichiers. »</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="notifications" titre="10. Notifications liées à ce module">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Soumission</strong> : le(s) chef(s) de la structure porteuse (ou les validateurs) sont notifiés qu'une activité attend leur décision.</li>
            <li><strong>Validation / Refus</strong> : l'auteur de l'activité est notifié, avec le motif en cas de refus.</li>
            <li><strong>Changement de coût</strong> : si une modification (y compris un arbitrage) change le coût d'une activité, le responsable de la structure porteuse et le directeur de la Direction Centrale de rattachement en sont notifiés.</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="11. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Une activité portant ce nom est déjà programmée pour cet exercice. »</summary>
                <p class="mt-2">Choisissez un intitulé différent, ou vérifiez qu'il ne s'agit pas d'un doublon à modifier plutôt qu'à recréer.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Le chronogramme est obligatoire : sélectionnez au moins une période. »</summary>
                <p class="mt-2">Cochez au moins un trimestre (T1 à T4) avant d'enregistrer.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Impossible de soumettre cette activité. »</summary>
                <p class="mt-2">L'activité n'est ni en brouillon ni rejetée (par exemple déjà en attente ou validée) : elle ne peut plus être soumise depuis cet écran.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Aucun exercice actif : impossible d'enregistrer une activité non programmée. »</summary>
                <p class="mt-2">Demandez à un superadmin/dbcgoq d'activer un exercice (module <a href="{{ route('documentation.exercices') }}" class="underline">Exercices</a>) avant de réessayer.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Vous n'êtes pas autorisé à ajouter des fichiers. »</summary>
                <p class="mt-2">Aucune des permissions requises (modification, validation, évaluation) ne vous est attribuée pour cette activité.</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="12. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Vérifiez le <strong>référentiel</strong> (extrant actif, exercice couvert) avant de saisir une activité — un extrant manquant à l'appel doit se corriger dans <a href="{{ route('documentation.planification') }}" class="underline">Planification</a>, pas en forçant l'activité.</li>
            <li>Utilisez <strong>« Pour mémoire »</strong> plutôt que de dupliquer un coût déjà porté ailleurs, pour ne pas fausser le budget total.</li>
            <li>Renseignez un <strong>intitulé précis et distinctif</strong> : il doit rester unique sur l'exercice.</li>
            <li>Réservez l'<strong>activité non programmée</strong> aux imprévus réels : le PTA initial doit rester le canal normal de programmation.</li>
            <li>Une fois rejetée, <strong>corrigez avant de re-soumettre</strong> plutôt que de recréer une nouvelle activité, pour conserver l'historique.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre précédent : <a href="{{ route('documentation.planification') }}" class="font-medium underline">🎯 Planification</a> ·
            Chapitre suivant : <a href="{{ route('documentation.validation') }}" class="font-medium underline">✅ Validation</a>.
        </p>
    </x-doc.section>
</x-doc.page>
