<x-doc.page
    title="Utilisateurs"
    subtitle="Créer et administrer les comptes : identité, rattachement à une structure, rôles applicatifs et accès."
    icon="👥"
    current="utilisateurs"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="principes">2. Principes de base</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="liste">3. Écran « Utilisateurs »</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="creer">4. Créer un compte</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="champs">5. Détail des champs</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="roles">6. Les rôles en détail</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="perimetre">7. Structure & périmètre</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="fiche">8. Fiche d'un utilisateur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="modifier">9. Modifier un compte</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="motdepasse">10. Mots de passe</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="supprimer">11. Supprimer un compte</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">12. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">13. Bonnes pratiques</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="acces" titre="1. Qui a accès à ce module">
        <p>
            Le menu <strong>Utilisateurs</strong> (icône 👥 dans la barre latérale) est réservé aux rôles
            <strong>superadmin</strong> et <strong>dbcgoq</strong>. Un <em>chef</em> ou un <em>agent</em> ne peut pas
            consulter l'annuaire des comptes ; il gère uniquement son propre profil via
            <strong>son nom (bas de la barre latérale) → Paramètres</strong>.
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
                    <tr><td class="py-2 pr-4">Consulter la liste et les fiches</td><td class="py-2 pr-4 font-mono text-xs">view_users</td><td class="py-2">superadmin, dbcgoq</td></tr>
                    <tr><td class="py-2 pr-4">Créer un compte</td><td class="py-2 pr-4 font-mono text-xs">create_users</td><td class="py-2">superadmin, dbcgoq</td></tr>
                    <tr><td class="py-2 pr-4">Modifier un compte</td><td class="py-2 pr-4 font-mono text-xs">edit_users</td><td class="py-2">superadmin, dbcgoq</td></tr>
                    <tr><td class="py-2 pr-4">Supprimer un compte</td><td class="py-2 pr-4 font-mono text-xs">delete_users</td><td class="py-2">superadmin, dbcgoq</td></tr>
                </tbody>
            </table>
        </div>
    </x-doc.section>

    <x-doc.section id="principes" titre="2. Principes de base" chapo="Un compte SYGECO repose sur trois éléments indissociables.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🪪 Une identité</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Nom, e-mail (identifiant de connexion), poste et téléphone.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🏢 Une structure</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Le rattachement qui définit <strong>ce que</strong> l'utilisateur voit.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🔑 Un ou plusieurs rôles</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Les rôles définissent <strong>ce qu'il peut faire</strong>.</p>
            </div>
        </div>
        <x-doc.astuce>
            Retenez la règle : <strong>le rôle donne les actions, la structure donne le périmètre</strong>.
            Un « chef » sans structure n'a rien à valider ; un compte bien rattaché mais sans rôle adéquat ne peut rien saisir.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="liste" titre="3. Écran « Utilisateurs »">
        <x-doc.capture src="images/manuel/utilisateurs.png" alt="Écran Utilisateurs avec compteurs et tableau">Compteurs, filtres et tableau des comptes.</x-doc.capture>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Les quatre compteurs</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Total</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Comptes correspondant aux filtres.</p></div>
            <div class="rounded-lg border border-emerald-200 p-3 dark:border-emerald-900/70"><p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Comptes vérifiés</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Adresse e-mail confirmée par l'utilisateur.</p></div>
            <div class="rounded-lg border border-amber-200 p-3 dark:border-amber-900/70"><p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Sans structure</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Comptes à corriger en priorité : périmètre indéterminé.</p></div>
            <div class="rounded-lg border border-sky-200 p-3 dark:border-sky-900/70"><p class="text-xs font-semibold text-sky-700 dark:text-sky-300">Avec rôles</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Comptes disposant d'au moins un rôle.</p></div>
        </div>
        <x-doc.astuce type="attention">
            Comme dans le module Directions Centrales, ces compteurs <strong>reflètent les filtres actifs</strong>.
            Un écart entre « Total » et « Avec rôles » signale des comptes non opérationnels : traitez-les en priorité.
        </x-doc.astuce>

        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">La barre de filtres</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Filtre</th>
                        <th class="py-2">Comportement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Rechercher</td><td class="py-2">Recherche partielle sur le <strong>nom</strong>, l'<strong>e-mail</strong>, le <strong>poste</strong> et le <strong>téléphone</strong>. Les espaces agissent comme des jokers.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Toutes structures</td><td class="py-2">Filtre sur une entité précise. Seules les <strong>structures actives</strong> sont proposées dans cette liste.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Tous rôles</td><td class="py-2">Filtre sur un rôle : superadmin, dbcgoq, chef, agent.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Trier par</td><td class="py-2">Nom (défaut), e-mail, nombre de rôles, date de création.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Sens</td><td class="py-2">Croissant (défaut) ou décroissant.</td></tr>
                </tbody>
            </table>
        </div>
        <p>
            Les critères restent inscrits dans l'adresse de la page : la pagination les conserve et le lien d'une vue
            filtrée peut être partagé. Pour repérer les nouveaux comptes, triez par <strong>date de création</strong> en sens décroissant.
        </p>

        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Le tableau</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Identité</strong> : nom, e-mail, et la mention « Email vérifié » / « Email non vérifié ».</li>
            <li><strong>Organisation</strong> : structure de rattachement (ou « Aucune structure ») et poste (ou « Poste non défini »).</li>
            <li><strong>Contact</strong> : téléphone et date de création du compte.</li>
            <li><strong>Rôles</strong> : une pastille bleue par rôle, ou « Aucun rôle ».</li>
            <li><strong>Actions</strong> : voir 👁, modifier ✎, supprimer 🗑 (selon vos permissions).</li>
        </ul>
        <p>La liste affiche <strong>15 comptes par page</strong>.</p>
    </x-doc.section>

    <x-doc.section id="creer" titre="4. Créer un compte" chapo="Bouton « Nouvel utilisateur », en haut à droite de la liste.">
        <ol class="list-decimal space-y-2 pl-5">
            <li>Renseignez le <strong>Nom</strong> complet et l'<strong>Email</strong> professionnel (il servira d'identifiant de connexion).</li>
            <li>Définissez un <strong>Mot de passe</strong> (8 caractères minimum) et saisissez-le à l'identique dans <strong>Confirmation mot de passe</strong>.</li>
            <li>Sélectionnez la <strong>Structure</strong> de rattachement.</li>
            <li>Complétez le <strong>Poste</strong> et le <strong>Téléphone</strong> (facultatifs mais recommandés).</li>
            <li>Cochez <strong>au moins un rôle</strong>.</li>
            <li>Enregistrez : le message « Utilisateur créé et notifié par e-mail. » confirme l'opération.</li>
        </ol>
        <x-doc.astuce>
            À la création, un <strong>e-mail de bienvenue</strong> est envoyé automatiquement au nouvel utilisateur avec
            son adresse, le mot de passe défini et un bouton « Se connecter ». Une notification in-app lui est également
            déposée — sans le mot de passe, qui ne circule que par e-mail. Vérifiez donc l'adresse avant d'enregistrer.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="champs" titre="5. Détail des champs du formulaire">
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
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Nom</td>
                        <td class="py-2 pr-4">Oui</td>
                        <td class="py-2">255 caractères maximum. Affiché partout : listes, validations, historique.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Email</td>
                        <td class="py-2 pr-4">Oui</td>
                        <td class="py-2">Format e-mail valide, 255 caractères maximum, <strong>unique</strong>. C'est l'identifiant de connexion et l'adresse de réception des notifications.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Mot de passe</td>
                        <td class="py-2 pr-4">À la création</td>
                        <td class="py-2">8 caractères minimum. En modification, le champ est facultatif : laissé vide, le mot de passe actuel est conservé.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Confirmation</td>
                        <td class="py-2 pr-4">Si mot de passe saisi</td>
                        <td class="py-2">Doit être strictement identique au mot de passe.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Structure</td>
                        <td class="py-2 pr-4">Non (fortement conseillé)</td>
                        <td class="py-2">Seules les structures <strong>actives</strong> sont proposées, triées selon l'ordre d'affichage de l'organigramme. « Aucun » laisse le compte sans périmètre.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Poste</td>
                        <td class="py-2 pr-4">Non</td>
                        <td class="py-2">100 caractères maximum. Indexé par la recherche.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Téléphone</td>
                        <td class="py-2 pr-4">Non</td>
                        <td class="py-2">30 caractères maximum (format libre, ex. +223 XX XX XX XX). Indexé par la recherche et affiché sur la fiche de l'entité dont le compte est responsable.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Rôles</td>
                        <td class="py-2 pr-4">Oui — au moins un</td>
                        <td class="py-2">Cases à cocher, cumul possible. À l'enregistrement, les rôles cochés <strong>remplacent</strong> intégralement les rôles précédents.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-doc.section>

    <x-doc.section id="roles" titre="6. Les rôles en détail" chapo="Quatre rôles existent. Ils déterminent les menus visibles et les actions autorisées.">
        <div class="space-y-3">
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">superadmin</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Équipe informatique. Dispose de <strong>toutes</strong> les permissions : structures, utilisateurs, exercices, référentiel de planification, validation, journal d'activité.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">dbcgoq</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pilote métier de la plateforme. Mêmes permissions que superadmin : consolidation, validation finale, arbitrage budgétaire, analyse budgétaire, ouverture des périodes de suivi. C'est le seul profil, avec superadmin, à accéder en permanence à la saisie d'exécution.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">chef</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Responsable d'une entité. Peut consulter, créer, modifier et soumettre des activités, et valider celles remontant de ses entités enfants. Consulte le référentiel (objectifs, résultats, extrants) en lecture seule et n'accède ni au menu Utilisateurs ni au menu Directions Centrales. <strong>Son niveau hiérarchique découle du type de l'entité à laquelle il est rattaché</strong> — il n'y a pas de rôle distinct par niveau.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">agent</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Utilisateur de terrain. Accède aux activités de son entité et à la saisie de leur exécution pendant les fenêtres ouvertes.</p>
            </div>
        </div>
        <x-doc.astuce type="attention">
            Attribuer le rôle <strong>chef</strong> ne suffit pas à faire de quelqu'un le responsable d'une entité :
            pensez également à le désigner comme <strong>Responsable</strong> dans la fiche de l'entité concernée
            (module <a href="{{ route('documentation.directions-centrales') }}#champs" class="font-medium underline">Directions Centrales</a>).
        </x-doc.astuce>
        <x-doc.astuce type="interdit">
            N'attribuez <strong>superadmin</strong> ou <strong>dbcgoq</strong> qu'à un nombre restreint de personnes :
            ces deux rôles donnent accès à l'ensemble des données et au paramétrage de la plateforme.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="perimetre" titre="7. Structure & périmètre de visibilité" chapo="Le rattachement décide de ce que l'utilisateur voit dans les activités, le suivi et les tableaux de bord.">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Profil</th>
                        <th class="py-2">Ce qu'il voit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">superadmin / dbcgoq</td><td class="py-2">Toutes les structures et toutes les activités.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">chef</td><td class="py-2">Son entité <strong>et tout son sous-arbre</strong> : directions centrales et services rattachés, à toute profondeur.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">agent</td><td class="py-2">Uniquement son entité de rattachement.</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce type="attention">
            Un compte <strong>sans structure</strong> (« Aucune structure » dans la liste) n'a aucun périmètre : il se
            connecte mais ne voit ni activités ni suivi. Le compteur « Sans structure » de l'écran permet de repérer
            ces comptes ; complétez leur rattachement.
        </x-doc.astuce>
        <p>
            Changer la structure d'un compte modifie immédiatement son périmètre. Les activités qu'il a déjà créées
            restent rattachées à leur entité d'origine : il peut donc perdre l'accès à des activités qu'il a lui-même saisies.
        </p>
    </x-doc.section>

    <x-doc.section id="fiche" titre="8. La fiche d'un utilisateur" chapo="Accessible par l'action 👁 du tableau.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>En-tête</strong> : nom et e-mail, avec les boutons Modifier et Retour.</li>
            <li><strong>Quatre cartes</strong> : structure, poste, téléphone et nombre de rôles.</li>
            <li><strong>Rôles attribués</strong> : la liste des rôles, ou « Aucun rôle attribué. ».</li>
            <li><strong>Métadonnées</strong> : date de création, date de dernière mise à jour et statut de l'e-mail (Vérifié / Non vérifié).</li>
            <li><strong>Collègues de la même structure</strong> : jusqu'à 8 comptes rattachés à la même entité, avec leur poste — utile pour contrôler la cohérence d'une équipe. Ce bloc n'apparaît que si l'utilisateur a une structure et au moins un collègue.</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="modifier" titre="9. Modifier un compte">
        <p>
            Le formulaire d'édition reprend les mêmes champs, avec deux particularités :
            l'e-mail doit rester unique <em>hors du compte en cours</em> (vous pouvez le conserver tel quel), et
            le champ mot de passe affiche « Laisser vide pour conserver ».
        </p>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Les rôles actuellement détenus sont pré-cochés. <strong>Décocher un rôle le retire</strong> dès l'enregistrement : la sélection remplace l'existant.</li>
            <li>Au moins un rôle doit rester coché, sinon l'enregistrement est refusé.</li>
            <li>La modification d'un compte <strong>ne déclenche aucun e-mail</strong> : prévenez l'utilisateur vous-même si son mot de passe ou son périmètre change.</li>
        </ul>
        <x-doc.astuce type="attention">
            Modifier votre propre compte peut vous faire perdre l'accès au module si vous retirez votre rôle
            superadmin/dbcgoq. Assurez-vous qu'un autre administrateur conserve ces droits.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="motdepasse" titre="10. Mots de passe">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Le mot de passe est défini par l'administrateur à la création, puis transmis à l'utilisateur par e-mail.</li>
            <li>Longueur minimale : <strong>8 caractères</strong>, avec confirmation obligatoire.</li>
            <li>Un mot de passe oublié se <strong>réinitialise</strong> depuis cet écran : ouvrez la fiche → Modifier → saisissez le nouveau mot de passe et sa confirmation → Enregistrer, puis communiquez-le à l'intéressé.</li>
            <li>Chaque utilisateur peut ensuite le changer lui-même via <strong>son nom → Paramètres</strong>.</li>
        </ul>
        <x-doc.astuce>
            Invitez chaque nouvel utilisateur à modifier son mot de passe dès sa première connexion :
            le mot de passe initial a circulé par e-mail.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="supprimer" titre="11. Supprimer un compte">
        <p>
            L'action 🗑 demande une confirmation, puis retire le compte de l'application. Le message
            « Utilisateur supprimé. » confirme l'opération.
        </p>
        <x-doc.astuce type="interdit">
            Vous ne pouvez pas supprimer <strong>votre propre compte</strong> : le message « Vous ne pouvez pas
            supprimer votre propre compte. » s'affiche. Demandez à un autre administrateur de le faire.
        </x-doc.astuce>
        <x-doc.astuce type="attention">
            Avant de supprimer, vérifiez que le compte n'est pas <strong>responsable d'une entité</strong> : la fiche de
            l'entité afficherait alors un responsable manquant, ce qui perturbe le circuit de validation et les
            notifications. Désignez d'abord un remplaçant dans le module Directions Centrales.
        </x-doc.astuce>
        <p>
            Pour un départ temporaire, il est souvent préférable de <strong>retirer les rôles opérationnels</strong>
            et de réaffecter la structure plutôt que de supprimer le compte, afin de préserver la traçabilité des
            saisies et validations passées.
        </p>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="12. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">L'e-mail est déjà utilisé</summary>
                <p class="mt-2">Un compte existe déjà avec cette adresse. Recherchez-le dans la liste : il s'agit probablement du même agent, à réactiver ou à mettre à jour plutôt qu'à recréer.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">La confirmation du mot de passe ne correspond pas</summary>
                <p class="mt-2">Ressaisissez les deux champs à l'identique. Ils sont masqués : attention à la disposition du clavier et à la touche majuscule.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Le champ « rôles » est obligatoire</summary>
                <p class="mt-2">Aucune case n'est cochée. Sélectionnez au moins un rôle avant d'enregistrer.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Vous ne pouvez pas supprimer votre propre compte. »</summary>
                <p class="mt-2">Protection volontaire. Un autre administrateur doit réaliser l'opération.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">La structure recherchée n'apparaît pas dans la liste « Structure »</summary>
                <p class="mt-2">Seules les entités <strong>actives</strong> sont proposées. Réactivez l'entité depuis le module Directions Centrales, ou vérifiez qu'elle a bien été créée.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">L'utilisateur dit ne pas avoir reçu son e-mail de bienvenue</summary>
                <p class="mt-2">Faites vérifier le dossier « courrier indésirable », puis contrôlez l'orthographe de l'adresse sur la fiche. Vous pouvez, à défaut, lui communiquer ses identifiants directement après avoir réinitialisé son mot de passe.</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="13. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Créez d'abord l'<strong>organigramme</strong>, puis les comptes : la structure doit exister et être active pour être sélectionnable.</li>
            <li>Utilisez systématiquement l'<strong>adresse professionnelle</strong> : elle sert d'identifiant et reçoit toutes les notifications.</li>
            <li>Attribuez le <strong>rôle minimal nécessaire</strong> ; évitez le cumul de rôles sans raison fonctionnelle.</li>
            <li>Renseignez <strong>poste et téléphone</strong> : ils facilitent la recherche et l'annuaire interne.</li>
            <li>Contrôlez régulièrement les compteurs <strong>« Sans structure »</strong> et <strong>« Avec rôles »</strong> pour détecter les comptes incomplets.</li>
            <li>À chaque mouvement de personnel, mettez à jour <strong>la structure, les rôles et le responsable de l'entité</strong> concernée.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre précédent : <a href="{{ route('documentation.directions-centrales') }}" class="font-medium underline">🏢 Directions Centrales</a>.
        </p>
    </x-doc.section>
</x-doc.page>
