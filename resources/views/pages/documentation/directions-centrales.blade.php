<x-doc.page
    title="Directions Centrales"
    subtitle="Décrire l'organigramme de la CANAM : directions, directions centrales et services, leurs responsables et leur activation."
    icon="🏢"
    current="directions-centrales"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="vocabulaire">2. Vocabulaire & niveaux</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="regles">3. Règles de rattachement</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="liste">4. Écran « Directions Centrales »</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="organigramme">5. Organigramme</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="creer">6. Créer une entité</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="champs">7. Détail des champs</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="fiche">8. Fiche d'une entité</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="modifier">9. Modifier</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="desactiver">10. Désactiver / supprimer</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="impacts">11. Impacts de la structure</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">12. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">13. Bonnes pratiques</x-doc.lien-sommaire>
    </x-slot:sommaire>

    <x-doc.section id="acces" titre="1. Qui a accès à ce module">
        <p>
            Le menu <strong>Directions Centrales</strong> (icône 🏢 dans la barre latérale) est réservé aux rôles
            <strong>superadmin</strong> et <strong>dbcgoq</strong>. Les rôles <em>chef</em> et <em>agent</em> ne voient
            pas ce menu : ils consultent leur rattachement depuis leur profil.
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
                    <tr><td class="py-2 pr-4">Consulter la liste et les fiches</td><td class="py-2 pr-4 font-mono text-xs">view_departements</td><td class="py-2">superadmin, dbcgoq</td></tr>
                    <tr><td class="py-2 pr-4">Créer une entité</td><td class="py-2 pr-4 font-mono text-xs">create_departements</td><td class="py-2">superadmin, dbcgoq</td></tr>
                    <tr><td class="py-2 pr-4">Modifier une entité</td><td class="py-2 pr-4 font-mono text-xs">edit_departements</td><td class="py-2">superadmin, dbcgoq</td></tr>
                    <tr><td class="py-2 pr-4">Supprimer une entité</td><td class="py-2 pr-4 font-mono text-xs">delete_departements</td><td class="py-2">superadmin, dbcgoq</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce>
            Les boutons « Nouvelle entité », « Modifier » et « Supprimer » n'apparaissent que si vous détenez la
            permission correspondante : si un bouton est absent, c'est un problème de droits, pas un bug d'affichage.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="vocabulaire" titre="2. Vocabulaire & niveaux hiérarchiques" chapo="L'organisation de la CANAM est décrite sur trois niveaux. Le module les gère tous, même si le menu porte le nom du niveau le plus utilisé.">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">Direction</span>
                <span class="text-slate-500 dark:text-slate-400">Niveau 1 — sommet de l'organigramme (direction générale). Aucune entité au-dessus.</span>
            </div>
            <div class="flex flex-wrap items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">Direction Centrale</span>
                <span class="text-slate-500 dark:text-slate-400">Niveau 2 — toujours rattachée à une direction. Peut porter des services.</span>
            </div>
            <div class="flex flex-wrap items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">Service</span>
                <span class="text-slate-500 dark:text-slate-400">Niveau 3 — feuille de l'arbre. Ne peut jamais avoir d'entité rattachée.</span>
            </div>
        </div>
        <p>
            Ces trois libellés apparaissent sous forme de <strong>badges de couleur</strong> partout dans l'application
            (liste, organigramme, fiche, sous-entités), ce qui permet d'identifier le niveau d'un coup d'œil.
        </p>
        <x-doc.astuce>
            Un service peut être rattaché <strong>directement à une direction</strong> : c'est le cas des
            « services rattachés » qui ne dépendent d'aucune direction centrale.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="regles" titre="3. Règles de rattachement" chapo="La cohérence niveau ↔ parent est contrôlée à l'enregistrement. Un rattachement invalide est refusé avec un message explicite.">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Type créé</th>
                        <th class="py-2 pr-4">Parent obligatoire ?</th>
                        <th class="py-2">Parents autorisés</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Direction</td>
                        <td class="py-2 pr-4">Non — et interdit</td>
                        <td class="py-2">Aucun : c'est une entité racine.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Direction Centrale</td>
                        <td class="py-2 pr-4">Oui</td>
                        <td class="py-2">Une <strong>Direction</strong> uniquement.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Service</td>
                        <td class="py-2 pr-4">Oui</td>
                        <td class="py-2">Une <strong>Direction Centrale</strong>, ou une <strong>Direction</strong> (service rattaché).</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>La liste « Rattaché à » ne propose que des <strong>directions</strong> et des <strong>directions centrales</strong> : un service n'est jamais parent.</li>
            <li>Le champ « Rattaché à » se <strong>masque automatiquement</strong> lorsque vous choisissez le type « Direction », et les options proposées se filtrent selon le type sélectionné.</li>
            <li>En modification, l'entité en cours est retirée de la liste : une entité ne peut pas être son propre parent.</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="liste" titre="4. Écran « Directions Centrales »" chapo="L'écran principal se lit de haut en bas : compteurs, filtres, organigramme, tableau paginé.">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Les quatre compteurs</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Total</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Nombre d'entités correspondant aux filtres.</p></div>
            <div class="rounded-lg border border-emerald-200 p-3 dark:border-emerald-900/70"><p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Actifs</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Entités utilisables dans les formulaires.</p></div>
            <div class="rounded-lg border border-amber-200 p-3 dark:border-amber-900/70"><p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Inactifs</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Entités conservées mais retirées des listes de sélection.</p></div>
            <div class="rounded-lg border border-sky-200 p-3 dark:border-sky-900/70"><p class="text-xs font-semibold text-sky-700 dark:text-sky-300">Avec responsable</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Entités dont le responsable est renseigné.</p></div>
        </div>
        <x-doc.astuce type="attention">
            Les compteurs tiennent compte des filtres actifs. Après un filtrage, « Total » n'est donc plus le nombre
            total d'entités de la CANAM mais celui du résultat affiché. Videz les filtres pour retrouver la vue globale.
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
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Rechercher</td><td class="py-2">Recherche partielle sur le <strong>code</strong>, le <strong>nom</strong> et la <strong>description</strong>. Les espaces saisis agissent comme des jokers : « dir centr » retrouve « Direction Centrale… ».</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Tous les niveaux</td><td class="py-2">Restreint à Direction, Direction Centrale ou Service.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Tous les statuts</td><td class="py-2">Actifs / Inactifs.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Trier par</td><td class="py-2">Ordre d'affichage (défaut), nom, nombre d'utilisateurs, nombre d'activités.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Sens</td><td class="py-2">Croissant (défaut) ou décroissant.</td></tr>
                </tbody>
            </table>
        </div>
        <p>
            Cliquez sur <strong>Filtrer</strong> pour appliquer. Les critères sont conservés dans l'adresse de la page :
            vous pouvez donc <strong>copier le lien</strong> d'une vue filtrée ou changer de page de résultats sans les perdre.
            Pour tout réinitialiser, remettez les listes sur leur valeur par défaut et videz la recherche, puis filtrez à nouveau.
        </p>

        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Le tableau</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Entité</strong> : nom, code (police monospace), ordre d'affichage et début de la description (tronquée à 90 caractères).</li>
            <li><strong>Type / Rattachement</strong> : badge de niveau, puis lien cliquable vers l'entité parente — ou la mention « Entité racine ».</li>
            <li><strong>Responsable</strong> : nom et e-mail, ou « Aucun responsable ».</li>
            <li><strong>Données</strong> : nombre d'utilisateurs rattachés et nombre d'activités portées par l'entité.</li>
            <li><strong>Statut</strong> : pastille verte « Actif » ou orange « Inactif ».</li>
            <li><strong>Actions</strong> : voir 👁, modifier ✎, supprimer 🗑 (selon vos permissions).</li>
        </ul>
        <p>La liste affiche <strong>15 entités par page</strong> ; la pagination se trouve sous le tableau.</p>
        <x-doc.astuce>
            Les compteurs « utilisateur(s) » et « activité(s) » ne concernent que l'entité de la ligne :
            ils <strong>n'agrègent pas</strong> les sous-entités.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="organigramme" titre="5. L'organigramme" chapo="Le bloc « Organigramme », affiché déplié au-dessus du tableau, présente l'arbre complet des entités.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Il part des <strong>entités racines</strong> (les directions) et descend récursivement jusqu'aux services.</li>
            <li>Chaque nœud affiche le code, le nom cliquable (vers la fiche), le badge de niveau et, le cas échéant, une pastille « Inactif ».</li>
            <li>Cliquez sur l'en-tête « Organigramme » (chevron ▾) pour le replier et gagner de la place.</li>
        </ul>
        <x-doc.astuce type="attention">
            L'organigramme est <strong>indépendant des filtres</strong> : il montre toujours l'arbre complet, y compris les
            entités inactives. C'est le moyen le plus fiable de vérifier un rattachement après une création.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="creer" titre="6. Créer une entité" chapo="Bouton « Nouvelle entité », en haut à droite de la liste.">
        <ol class="list-decimal space-y-2 pl-5">
            <li>Saisissez le <strong>Code</strong> (unique, ex. <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">DEP_001</code>) et le <strong>Nom</strong> de l'entité.</li>
            <li>Choisissez le <strong>Type d'entité</strong> : Direction, Direction Centrale ou Service.</li>
            <li>Sélectionnez l'entité de rattachement dans <strong>« Rattaché à »</strong> (champ masqué pour une Direction).</li>
            <li>Complétez si besoin la <strong>Description</strong>, le <strong>Responsable</strong> et l'<strong>Ordre d'affichage</strong>.</li>
            <li>Laissez <strong>« Entité active »</strong> cochée pour que la structure soit immédiatement utilisable.</li>
            <li>Enregistrez : vous revenez à la liste avec le message « Département créé avec succès. ».</li>
        </ol>
        <x-doc.astuce>
            Créez toujours l'organigramme <strong>de haut en bas</strong> : les directions d'abord, puis les directions
            centrales, puis les services. Sans parent existant, un niveau 2 ou 3 ne peut pas être enregistré.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="champs" titre="7. Détail des champs du formulaire">
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
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Code</td>
                        <td class="py-2 pr-4">Oui</td>
                        <td class="py-2">20 caractères maximum, <strong>unique</strong> dans toute l'application. Sert d'identifiant lisible dans l'organigramme et les exports.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Nom</td>
                        <td class="py-2 pr-4">Oui</td>
                        <td class="py-2">200 caractères maximum. C'est le libellé affiché dans toutes les listes déroulantes.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Type d'entité</td>
                        <td class="py-2 pr-4">Oui</td>
                        <td class="py-2">Direction / Direction Centrale / Service. Conditionne l'affichage et le contrôle du champ « Rattaché à ».</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Rattaché à</td>
                        <td class="py-2 pr-4">Selon le type</td>
                        <td class="py-2">Obligatoire pour une Direction Centrale et pour un Service ; interdit pour une Direction. Voir <a href="#regles" class="underline">les règles de rattachement</a>.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Description</td>
                        <td class="py-2 pr-4">Non</td>
                        <td class="py-2">1 000 caractères maximum. Elle est <strong>indexée par la recherche</strong> et s'affiche tronquée dans le tableau.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Responsable</td>
                        <td class="py-2 pr-4">Non</td>
                        <td class="py-2">Choisi parmi <strong>tous</strong> les comptes existants (nom + e-mail), qu'ils soient ou non rattachés à cette entité. Son téléphone apparaît sur la fiche.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Ordre d'affichage</td>
                        <td class="py-2 pr-4">Non</td>
                        <td class="py-2">Entier ≥ 1. S'il est laissé vide à la création, le système attribue automatiquement <strong>l'ordre le plus élevé + 1</strong> (l'entité se place en fin de liste). À valeur d'ordre égale, le tri se fait par nom.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Entité active</td>
                        <td class="py-2 pr-4">Non</td>
                        <td class="py-2">Cochée par défaut à la création. Décochée, l'entité reste visible ici mais disparaît des listes de sélection des autres écrans.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce type="attention">
            Si un champ est refusé, le message d'erreur s'affiche en rouge sous le champ concerné et
            <strong>les valeurs déjà saisies sont conservées</strong> : corrigez uniquement le point signalé, sans tout ressaisir.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="fiche" titre="8. La fiche d'une entité" chapo="Accessible par l'action 👁 du tableau, par un nom cliquable de l'organigramme ou par un lien de rattachement.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Fil d'Ariane</strong> : la chaîne complète des entités supérieures, chaque maillon étant cliquable.</li>
            <li><strong>Trois compteurs</strong> : utilisateurs rattachés, activités portées, statut actif/inactif.</li>
            <li><strong>Informations générales</strong> : description, ordre d'affichage, dates de création et de dernière mise à jour (jour et heure).</li>
            <li><strong>Responsable</strong> : nom, e-mail et téléphone, ou « Non défini ».</li>
            <li><strong>Sous-entités</strong> : liste des entités directement rattachées, avec badge de niveau et pastille « Inactif » s'il y a lieu. Ce bloc n'apparaît pas sur un Service, qui ne peut pas en avoir.</li>
            <li><strong>Activités récentes</strong> : les 6 dernières activités de l'entité (intitulé, statut, coût en FCFA, date de création).</li>
            <li><strong>Utilisateurs associés</strong> : nom et e-mail des comptes rattachés (bloc masqué s'il n'y en a aucun).</li>
        </ul>
        <p>Les boutons <strong>Modifier</strong> et <strong>Retour</strong> se trouvent en haut à droite de la fiche.</p>
    </x-doc.section>

    <x-doc.section id="modifier" titre="9. Modifier une entité">
        <p>
            Le formulaire de modification reprend exactement les mêmes champs que la création. Deux différences :
            le code doit rester unique <em>hors de l'entité en cours</em> (vous pouvez donc le conserver tel quel),
            et l'entité elle-même est exclue de la liste « Rattaché à ».
        </p>
        <x-doc.astuce type="attention">
            Changer le <strong>type</strong> ou le <strong>parent</strong> d'une entité déplace tout son sous-arbre dans
            l'organigramme et modifie le périmètre de visibilité des chefs concernés. Vérifiez l'organigramme après
            l'enregistrement. Attention : faire passer une entité qui porte des sous-entités au type « Service » crée une
            situation incohérente, ce niveau étant censé être une feuille — déplacez d'abord ses enfants.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="desactiver" titre="10. Désactiver ou supprimer une entité">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Désactiver (recommandé)</h3>
        <p>
            Décochez « Entité active » puis enregistrez. L'entité conserve son historique, ses activités et ses
            utilisateurs, mais elle n'est plus proposée dans les listes déroulantes (notamment le champ
            « Structure » de la fiche utilisateur). C'est la bonne méthode pour une structure dissoute ou réorganisée.
        </p>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Supprimer</h3>
        <p>
            L'action 🗑 demande une confirmation, puis retire l'entité de l'application (suppression réversible en base,
            l'historique n'étant pas détruit).
        </p>
        <x-doc.astuce type="interdit">
            La suppression est <strong>refusée si au moins un utilisateur est rattaché</strong> à l'entité : le message
            « Impossible de supprimer un département qui a des utilisateurs. » s'affiche. Réaffectez d'abord ces comptes
            à une autre structure depuis le module Utilisateurs.
        </x-doc.astuce>
        <x-doc.astuce type="attention">
            La suppression ne réaffecte pas automatiquement les sous-entités : supprimez ou déplacez les enfants
            avant de supprimer un parent, faute de quoi ils resteront rattachés à une entité disparue et n'apparaîtront
            plus dans l'organigramme.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="impacts" titre="11. Ce que la structure détermine ailleurs dans SYGECO" chapo="L'organigramme n'est pas un simple annuaire : il pilote la visibilité et le circuit de validation.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Périmètre de consultation</strong> : un <em>chef</em> voit son entité <strong>et tout son sous-arbre</strong> (directions centrales et services rattachés, à toute profondeur) ; un <em>agent</em> ne voit que son entité.</li>
            <li><strong>Circuit de validation montant</strong> : une activité soumise remonte au responsable du niveau supérieur défini par le rattachement. Un rattachement erroné envoie donc les validations au mauvais interlocuteur.</li>
            <li><strong>Répartitions du tableau de bord et analyse budgétaire</strong> : les vues « par structure » s'appuient sur ces entités.</li>
            <li><strong>Listes déroulantes</strong> : seules les entités <strong>actives</strong> sont proposées lors de la création ou modification d'un utilisateur.</li>
            <li><strong>Notifications</strong> : les destinataires des alertes de soumission et de validation découlent du responsable et de la position de l'entité.</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="12. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Une direction est au sommet et ne peut pas avoir de parent. »</summary>
                <p class="mt-2">Vous avez choisi le type Direction tout en sélectionnant une entité de rattachement. Videz le champ « Rattaché à », ou choisissez plutôt le type Direction Centrale.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Une entité de ce niveau doit être rattachée à une entité supérieure. »</summary>
                <p class="mt-2">Le champ « Rattaché à » est vide pour une Direction Centrale ou un Service. Sélectionnez le parent adéquat.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Une direction centrale doit être rattachée à une direction. »</summary>
                <p class="mt-2">Le parent choisi n'est pas une Direction. Corrigez le parent, ou créez d'abord la direction manquante.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Un service doit être rattaché à une direction centrale, ou directement à une direction. »</summary>
                <p class="mt-2">Le parent choisi n'est pas valide pour un service. Rappel : un service ne peut jamais être rattaché à un autre service.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« The code has already been taken » / code déjà utilisé</summary>
                <p class="mt-2">Un autre code doit être choisi : les codes sont uniques. Recherchez le code existant depuis la barre de recherche pour vérifier s'il ne s'agit pas d'un doublon d'entité.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Impossible de supprimer un département qui a des utilisateurs. »</summary>
                <p class="mt-2">Ouvrez la fiche de l'entité, notez les utilisateurs associés et réaffectez-les depuis le module Utilisateurs avant de recommencer — ou désactivez simplement l'entité.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Aucune structure trouvée avec les filtres actuels. »</summary>
                <p class="mt-2">Ce n'est pas une erreur : vos filtres sont trop restrictifs. Videz la recherche et remettez les listes « Tous les niveaux » / « Tous les statuts ».</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="13. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Adoptez une <strong>convention de codes</strong> stable et lisible (par exemple <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">DG</code>, <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">DC_SI</code>, <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">SRV_RH</code>) : le code est repris dans l'organigramme et les exports.</li>
            <li>Renseignez systématiquement un <strong>responsable</strong> : il conditionne le bon acheminement des validations et des notifications.</li>
            <li>Utilisez l'<strong>ordre d'affichage</strong> pour refléter l'ordre protocolaire de l'organigramme plutôt que l'ordre alphabétique.</li>
            <li><strong>Désactivez</strong> plutôt que de supprimer : l'historique des activités reste consultable.</li>
            <li>Après toute réorganisation, contrôlez l'<strong>organigramme</strong> puis le rattachement des comptes concernés dans le module Utilisateurs.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre suivant : <a href="{{ route('documentation.utilisateurs') }}" class="font-medium underline">👥 Utilisateurs</a>.
        </p>
    </x-doc.section>
</x-doc.page>
