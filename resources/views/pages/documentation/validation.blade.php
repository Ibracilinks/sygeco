<x-doc.page
    title="Validation"
    subtitle="Le circuit de validation montante des activités soumises : valider, refuser, arbitrer le budget."
    icon="✅"
    current="validation"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès & périmètre du chef</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="index">2. Écran « Validations »</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="entite">3. Écran d'arbitrage d'une entité</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="fiche">4. Fiche d'une activité en attente</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="valider-refuser">5. Valider / refuser</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="groupee">6. Validation groupée</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="arbitrage">7. Arbitrage budgétaire</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="export">8. Exporter le cadre logique</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="historique">9. Historique de validation</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">10. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">11. Bonnes pratiques</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="acces" titre="1. Qui a accès à ce module & périmètre exact du chef">
        <p>
            Le module <strong>Validation</strong> est accessible aux rôles <strong>superadmin</strong>,
            <strong>dbcgoq</strong> et <strong>chef</strong>. Ni <em>agent</em>, ni <em>agent-planification</em>, ni
            <em>suivi-evaluation</em> n'y accèdent.
        </p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Profil</th>
                        <th class="py-2">Ce qu'il peut arbitrer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">superadmin / dbcgoq</td><td class="py-2">Toutes les activités en attente, sans restriction.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">chef</td><td class="py-2">Uniquement les activités des entités <strong>directement rattachées</strong> sous la sienne (ses « enfants » dans l'organigramme) — pas ses propres activités, ni celles de ses « petits-enfants » (déjà concentrées plus haut, voir ci-dessous).</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce type="attention">
            Un <strong>service</strong> n'est jamais arbitré pour lui-même : ses activités sont automatiquement
            concentrées dans l'entité de rattachement la plus proche qui n'est pas elle-même un service (par exemple,
            un service de la DAGRH remonte à la DAGRH). C'est donc le responsable de cette entité de rattachement qui
            voit et arbitre ces activités, pas un éventuel responsable du service lui-même.
        </x-doc.astuce>
        <x-doc.astuce>
            Nuance à retenir : la liste des entités à arbitrer (écran « Validations ») ne montre au chef que ses
            entités <strong>enfants directes</strong> ; mais une fois entré dans le détail d'une entité (§3), il en
            voit tout le <strong>sous-arbre</strong>, afin que les activités des services rattachés à une direction
            centrale restent visibles au même endroit.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="index" titre="2. Écran « Validations » (vue globale)" chapo="Page d'accueil du module : les activités en attente, groupées par entité.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Ne montre que les activités au statut <strong>En attente</strong>, regroupées par entité de rattachement, elles-mêmes classées par type (Direction, Direction Centrale, Service, Agence Comptable, Bureau Régional, autres).</li>
            <li>Chaque entité affiche le nombre d'activités en attente et le budget total soumis, avec un bouton <strong>« Arbitrer → »</strong> vers l'écran détaillé (§3).</li>
            <li>En-tête : compteur global d'activités soumises, coût total à arbitrer, et un bouton <strong>« Exporter (Cadre logique) »</strong> (§8).</li>
            <li>Message si aucune activité : « Aucune activité en attente d'arbitrage. »</li>
        </ul>
        <x-doc.astuce type="attention">
            Cette vue n'est <strong>pas filtrée par exercice</strong> : elle affiche les activités en attente de tous
            les exercices, pour ne jamais faire disparaître une soumission ancienne de la file d'arbitrage.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="entite" titre="3. Écran d'arbitrage d'une entité" chapo="Le vrai plan de travail de l'arbitre : cadre logique complet, actions groupées.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Reprend les activités <strong>en attente</strong> (actionnables) et <strong>déjà validées</strong> (lecture seule, affichées en vert, sans case ni bouton d'action) du sous-arbre de l'entité.</li>
            <li>Présentation en cadre logique : Résultat stratégique → Extrant → activités.</li>
            <li>En-tête : nombre d'activités en attente, coût total à arbitrer, nombre déjà validées ; pour une Direction Centrale, la note « Inclut les activités des services rattachés à cette Direction Centrale. » rappelle le périmètre élargi.</li>
            <li>Colonnes : case à cocher (activités en attente uniquement), Activité (code + nom + auteur), Responsable (structure porteuse), IOV, Moyen de vérification, Chronogramme, Validé le (+ validateur), Coût, Actions.</li>
            <li>Boutons de haut de page : « Exporter (Cadre logique) », « Fusionner sélection », « Valider sélection ».</li>
            <li>Message si aucune activité en attente : « Aucune activité en attente pour cette entité. »</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="fiche" titre="4. Fiche d'une activité en attente">
        <p>
            Accessible via l'icône « Voir » de l'écran d'entité. Affiche nom, coût, structure, extrant, date de
            soumission, auteur, indicateur, moyen de vérification, commentaires (ou « Aucun commentaire »), et
            l'historique de validation (§9). Boutons : Retour, Valider, Rejeter.
        </p>
        <x-doc.astuce type="attention">
            Si l'activité n'est plus au statut « En attente » (déjà traitée entre-temps), vous êtes redirigé vers la
            liste avec le message « Cette activité n'est pas en attente de validation. »
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="valider-refuser" titre="5. Valider / refuser une activité">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Valider</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Un <strong>commentaire est facultatif</strong> (1000 caractères max.).</li>
            <li>Fait passer l'activité au statut <strong>Validé</strong>, horodate la validation, notifie systématiquement l'auteur.</li>
            <li>Succès : « Activité validée avec succès. » — Refus si l'activité n'est plus en attente : « Cette activité ne peut pas être validée. »</li>
        </ul>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Refuser</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Le <strong>motif de refus est obligatoire</strong> (10 à 1000 caractères).</li>
            <li>Une case à cocher facultative <strong>« Notifier l'utilisateur »</strong> conditionne l'envoi de la notification à l'auteur — contrairement à la validation, qui notifie toujours.</li>
            <li>Fait passer l'activité au statut <strong>Rejeté</strong> ; elle redevient modifiable et re-soumettable par son auteur.</li>
            <li>Succès : « Activité refusée avec succès. » — Refus si l'activité n'est plus en attente : « Cette activité ne peut pas être refusée. »</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="groupee" titre="6. Validation groupée («&nbsp;Valider sélection&nbsp;»)">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Cochez les activités souhaitées (case par ligne, ou « tout cocher » par bloc Résultat), puis cliquez « Valider sélection ».</li>
            <li>Seules les activités <strong>en attente</strong> et dans votre périmètre sont réellement validées ; les autres sont silencieusement écartées de la sélection.</li>
            <li>Aucun commentaire n'est possible en validation groupée.</li>
            <li>Si aucune activité de la sélection n'est finalement validable : « Aucune activité sélectionnée ne peut être validée par votre profil. »</li>
            <li>Succès : « Sélection des activités validée avec succès. »</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="arbitrage" titre="7. Arbitrage budgétaire" chapo="Avant de valider ou refuser, l'arbitre peut modifier, supprimer ou fusionner des activités encore en attente.">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Modifier (arbitrer-modifier)</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Uniquement sur une activité <strong>en attente</strong> et dans votre périmètre.</li>
            <li>Champs modifiables : intitulé, indicateur, moyen de vérification, coût, chronogramme (T1-T4), et un motif facultatif (non appliqué à l'activité, seulement journalisé et transmis dans la notification).</li>
            <li>L'auteur est systématiquement notifié. Si le <strong>coût a changé</strong>, le responsable de la structure porteuse et le directeur de la Direction Centrale de rattachement reçoivent en plus une notification de changement de budget.</li>
            <li>Succès : « Activité modifiée et l'auteur a été notifié. »</li>
        </ul>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Supprimer (arbitrer-supprimer)</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Un motif est <strong>obligatoire</strong> (5 à 1000 caractères).</li>
            <li>L'activité est archivée (suppression réversible), l'auteur est notifié.</li>
            <li>Succès : « Activité supprimée et l'auteur a été notifié. »</li>
        </ul>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Fusionner (arbitrer-fusionner)</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Sélectionnez <strong>au moins deux</strong> activités en attente de votre périmètre, puis « Fusionner sélection ».</li>
            <li>Pour chaque champ (intitulé, IOV, moyen de vérification), choisissez la valeur d'une des sources ou saisissez un texte final ; pour le coût, choisissez une des valeurs ou la <strong>somme</strong> (proposée par défaut) ; le chronogramme reprend les trimestres cochés par au moins une source ; extrant et structure responsable se choisissent parmi les valeurs déjà présentes dans la sélection.</li>
            <li>Une <strong>nouvelle activité consolidée</strong> est créée, au statut <strong>En attente</strong> (elle devra donc être validée à son tour) ; chacune des activités sources est archivée (même mécanisme que « Supprimer »).</li>
            <li>Chaque auteur d'une activité fusionnée est notifié, avec le nom de la nouvelle activité consolidée.</li>
            <li>Message si moins de deux sources valides : « Sélectionnez au moins deux activités fusionnables de votre périmètre. »</li>
            <li>Succès : « {n} activités fusionnées et les auteurs ont été notifiés. »</li>
        </ul>
        <x-doc.astuce type="attention">
            La fusion n'est <strong>pas instantanément définitive</strong> : l'activité consolidée reste à valider
            comme n'importe quelle autre soumission — elle n'hérite pas automatiquement du statut « Validé ».
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="export" titre="8. Exporter le cadre logique">
        <p>
            Le bouton <strong>« Exporter (Cadre logique) »</strong> génère un fichier
            <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">cadre-logique-arbitrage-{date}.xls</code>
            couvrant <strong>toutes les activités de l'exercice en cours, tous statuts confondus</strong> (pas
            seulement celles en attente), dans votre périmètre et selon les filtres actifs (structure, extrant,
            trimestre). Depuis l'écran d'une entité, l'export se restreint à cette entité.
        </p>
    </x-doc.section>

    <x-doc.section id="historique" titre="9. Historique de validation">
        <p>
            Chaque étape (soumission, validation, refus, modification d'arbitrage, suppression d'arbitrage, fusion)
            est journalisée avec sa date, son auteur, le motif ou commentaire éventuel, et l'ancien/nouveau statut. La
            fiche d'une activité (module Activités) affiche l'historique complet ; les modales de validation et
            l'écran d'entité n'en montrent que les dernières entrées.
        </p>
        <p>Message si aucun historique : « Aucun historique disponible. »</p>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="10. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Cette activité n'est pas en attente de validation. »</summary>
                <p class="mt-2">Un autre arbitre a déjà traité l'activité (ou vous avez ouvert un lien obsolète). Revenez à la liste pour voir son statut actuel.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Cette activité ne peut pas être validée / refusée. »</summary>
                <p class="mt-2">Elle n'est plus au statut « En attente ». Rafraîchissez la liste.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Aucune activité sélectionnée ne peut être validée par votre profil. »</summary>
                <p class="mt-2">Toutes les activités cochées sont hors de votre périmètre (par exemple vos propres activités, ou celles d'une entité que vous ne supervisez pas directement).</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Sélectionnez au moins deux activités fusionnables de votre périmètre. »</summary>
                <p class="mt-2">Cochez au moins deux activités en attente qui sont bien dans votre périmètre avant de lancer la fusion.</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="11. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Traitez en priorité les entités affichant le <strong>plus gros budget soumis</strong> depuis la vue globale (§2).</li>
            <li>Utilisez l'<strong>arbitrage « Modifier »</strong> plutôt que de refuser puis attendre une re-soumission, pour les corrections mineures (coût, chronogramme, formulation).</li>
            <li>Réservez le <strong>refus</strong> aux activités nécessitant une révision de fond par leur auteur, avec un motif clair et exploitable.</li>
            <li>Avant une <strong>fusion</strong>, vérifiez que les activités sources concernent bien le même extrant et la même structure, pour limiter les choix à trancher.</li>
            <li>Utilisez la <strong>validation groupée</strong> pour les activités déjà cohérentes et sans ambiguïté, et réservez le traitement unitaire à celles nécessitant un commentaire ou un arbitrage.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre précédent : <a href="{{ route('documentation.activites') }}" class="font-medium underline">📋 Activités</a> ·
            Chapitre suivant : <a href="{{ route('documentation.suivi') }}" class="font-medium underline">📊 Suivi &amp; évaluation</a>.
        </p>
    </x-doc.section>
</x-doc.page>
