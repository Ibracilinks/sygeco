<x-doc.page
    title="Missions"
    subtitle="Ordres de mission (même ville, à l'étranger, intérieur du pays) : participants, calcul automatique, barèmes et génération PDF."
    icon="🧳"
    current="missions"
>
    <x-slot:sommaire>
        <x-doc.lien-sommaire ancre="acces">1. Qui a accès</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="types">2. Les trois types de mission</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="liste">3. Écran « Missions »</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="creer">4. Créer une mission</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="champs">5. Détail des champs communs</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="listes">6. Participants, étapes & signataires</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="calcul">7. Calcul automatique du montant</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="baremes">8. Barèmes des missions</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="cycle">9. Brouillon & finalisation</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="fiche">10. Fiche & génération PDF</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="limites">11. Ce que ce module ne fait pas</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="erreurs">12. Messages d'erreur</x-doc.lien-sommaire>
        <x-doc.lien-sommaire ancre="pratiques">13. Bonnes pratiques</x-doc.lien-sommaire>
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
                    <tr><td class="py-2 pr-4">Consulter la liste et les fiches</td><td class="py-2 pr-4 font-mono text-xs">view_missions</td><td class="py-2">superadmin, dbcgoq, service-budget (chef selon paramétrage, voir ci-dessous)</td></tr>
                    <tr><td class="py-2 pr-4">Créer / modifier une mission</td><td class="py-2 pr-4 font-mono text-xs">create_missions / edit_missions</td><td class="py-2">superadmin, dbcgoq, service-budget (chef selon paramétrage)</td></tr>
                    <tr><td class="py-2 pr-4">Supprimer une mission</td><td class="py-2 pr-4 font-mono text-xs">delete_missions</td><td class="py-2">superadmin, dbcgoq, service-budget</td></tr>
                    <tr><td class="py-2 pr-4">Générer le PDF</td><td class="py-2 pr-4 font-mono text-xs">generate_missions_pdf</td><td class="py-2">superadmin, dbcgoq, service-budget (chef selon paramétrage)</td></tr>
                    <tr><td class="py-2 pr-4">Réviser les barèmes</td><td class="py-2 pr-4 font-mono text-xs">—</td><td class="py-2">superadmin, dbcgoq uniquement</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce>
            Le rôle <strong>service-budget</strong> est le profil dédié à ce module : il n'a accès, dans toute
            l'application, qu'aux missions (créer, modifier, supprimer, générer le PDF), et n'accède ni aux barèmes ni
            à aucun autre module.
        </x-doc.astuce>
        <x-doc.astuce type="attention">
            Le rôle <strong>chef</strong> peut techniquement ouvrir les écrans du module (la route l'y autorise), mais
            l'affichage des boutons Créer/Modifier/Supprimer/Générer PDF dépend ensuite des permissions réellement
            attribuées à ce rôle sur votre installation — elles peuvent varier d'un paramétrage à l'autre. Si un chef
            ne voit aucun bouton d'action sur ce module, c'est un point à vérifier auprès de l'administrateur plutôt
            qu'une anomalie de l'écran.
        </x-doc.astuce>
        <p>Les barèmes (module <a href="#baremes" class="underline">Barèmes des missions</a>) restent, eux, strictement réservés à superadmin et dbcgoq.</p>
    </x-doc.section>

    <x-doc.section id="types" titre="2. Les trois types de mission" chapo="Chaque type possède son propre formulaire, dédié — il n'y a pas d'onglets communs à remplir en partie.">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🏙️ Même ville</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Déplacement local. Formulaire allégé, pas de montant total calculé (les tickets de carburant sont l'unique poste).</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">✈️ À l'étranger</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Zone de majoration, billets d'avion, frais de participation et de visa, catégorie par participant.</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🚗 Intérieur du pays</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Une ou plusieurs régions de destination, étapes de déplacement, carburant et location de véhicule.</p>
            </div>
        </div>
        <p>Depuis l'écran « Missions », le bouton de création correspond directement à un type (« + Même ville », « + À l'étranger », « + Intérieur du pays ») : le type se fixe à la création et ne peut plus être changé ensuite.</p>
    </x-doc.section>

    <x-doc.section id="liste" titre="3. Écran « Missions »" chapo="Sous-titre affiché en haut de la page : « Ordres de mission avec participants, signataires et génération PDF. »">
        <x-doc.capture src="images/manuel/missions.png" alt="Liste des missions avec compteurs, filtres et tableau">L'écran « Toutes les missions » : compteurs, filtres et tableau.</x-doc.capture>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Total</p></div>
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Brouillons</p></div>
            <div class="rounded-lg border border-emerald-200 p-3 dark:border-emerald-900/70"><p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Finalisées</p></div>
            <div class="rounded-lg border border-sky-200 p-3 dark:border-sky-900/70"><p class="text-xs font-semibold text-sky-700 dark:text-sky-300">Budget cumulé</p></div>
        </div>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Filtres</strong> : recherche (référence, objet, destination ou lieu de signature), statut, type, tri (date du document / référence / montant total), sens.</li>
            <li><strong>Colonnes</strong> : Référence / Objet (type, structures, auteur), Période (date du document et durée), Données (nombre de participants, de jours, de tickets ou zone), Budget (montant total en FCFA, ou « — » pour une mission « Même ville », qui n'a pas de montant total), Statut, Actions.</li>
            <li>Pagination : <strong>15 missions par page</strong>. Message si aucune : « Aucune mission trouvée avec les filtres actuels. »</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="creer" titre="4. Créer une mission" chapo="Le squelette du formulaire (référence, objet, dates, participants, signataires) est commun aux trois types, mais chacun a ses propres champs de calcul — voir le détail par cas ci-dessous.">
        <ol class="list-decimal space-y-1.5 pl-5">
            <li>Depuis la liste, cliquez sur le bouton du <strong>type</strong> souhaité (§2) : « + Même ville », « + À l'étranger » ou « + Intérieur du pays ». Ce choix est définitif.</li>
            <li>Renseignez la <strong>référence</strong> de l'ordre (unique), l'<strong>objet</strong> de la mission et le <strong>code budgétaire</strong> (liste fermée : « Sans code budgétaire », CE 630210, 638600, 638700, 630220, 630280).</li>
            <li>Choisissez la ou les <strong>structures demandeuses</strong> — la première sélectionnée devient la structure principale portée sur le document officiel.</li>
            <li>Renseignez les <strong>dates de départ et de retour</strong> ; le nombre de jours se calcule automatiquement (voir §7) mais reste modifiable pour « Même ville ».</li>
            <li>Complétez les champs propres au type choisi (détail par cas ci-dessous), ajoutez au moins un <strong>participant</strong> et au moins un <strong>signataire</strong> (§6).</li>
            <li>Enregistrez en <strong>Brouillon</strong> : message « Mission enregistrée avec succès. »</li>
        </ol>

        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">🏙️ Cas « Même ville »</h3>
        <x-doc.capture src="images/manuel/missions-creer.png" alt="Formulaire de création d'une mission même ville">Le formulaire « Même ville », le plus court des trois : pas de destination, pas de catégorie, pas de zone.</x-doc.capture>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Nombre de jours ouvrables</strong> (les week-ends sont exclus du décompte, contrairement aux deux autres types) calculé à partir des dates, mais modifiable.</li>
            <li><strong>Tickets carburant / jour</strong> : c'est l'unique poste budgétaire de ce type — aucun montant total en FCFA n'est calculé (§7), la fiche PDF affiche uniquement le nombre de tickets (§10).</li>
            <li>Chaque participant ne demande qu'un <strong>nom complet</strong> : pas de catégorie, pas de nuitée à saisir.</li>
        </ul>

        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">✈️ Cas « À l'étranger »</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Destination</strong> (texte libre) et <strong>zone de majoration</strong> obligatoire (liste fermée de 12 zones, §8) : elle fixe le taux (25 à 50 %) qui majore le sous-total indemnités de chaque participant.</li>
            <li><strong>Nombre de jours</strong> calendaires (week-ends inclus) ; les nuitées de chaque participant sont automatiquement fixées à <em>jours − 1</em>, quelle que soit la valeur saisie.</li>
            <li>Deux blocs de frais annexes en saisie libre : <strong>frais de participation</strong> et <strong>frais de visa</strong>, chacun en nombre de personnes × montant unitaire.</li>
            <li><strong>Billets d'avion</strong> : deux lignes distinctes à saisir, classe affaire et classe économique (nombre × prix unitaire chacune).</li>
            <li>Chaque participant doit avoir une <strong>catégorie</strong> (liste fermée de 7 catégories étrangères, §8) : elle fixe son frais de mission par jour et son indemnité par nuitée.</li>
        </ul>

        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">🚗 Cas « Intérieur du pays »</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Point de départ</strong> et une ou plusieurs <strong>régions de destination</strong>, choisis dans la liste fermée des régions administratives du Mali.</li>
            <li>Au moins une <strong>étape</strong> de déplacement (§6) : type d'étape, barème, localité, jours et nuitées.</li>
            <li>Bloc « Carburant » en saisie libre : nombre de véhicules, distance totale (km), consommation (L / 100 km), prix du litre, carburant en ville (L / jour) — sert au calcul du carburant trajet et du carburant ville.</li>
            <li>Bloc « Location & péages » : location de véhicule (nombre de jours × tarif par jour) et péages (montant global).</li>
            <li><strong>Billets d'avion</strong> : une seule ligne, classe économique uniquement (pas de classe affaire pour ce type).</li>
            <li>Chaque participant doit avoir une <strong>catégorie</strong> (liste fermée de 7 catégories nationales, §8), sauf sur une étape au forfait « même région »/« même cercle », où le montant forfaitaire prend le pas sur la catégorie (voir §6).</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="champs" titre="5. Détail des champs communs">
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
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Référence de l'ordre</td><td class="py-2 pr-4">Oui</td><td class="py-2">80 caractères max., unique dans toute l'application.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Objet de la mission</td><td class="py-2 pr-4">Oui</td><td class="py-2">5 000 caractères max.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Date de départ / de retour</td><td class="py-2 pr-4">Oui</td><td class="py-2">La date de retour doit être postérieure ou égale à la date de départ.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Nombre de jours</td><td class="py-2 pr-4">Non (calculé)</td><td class="py-2">Entre 1 et 365 ; une valeur saisie manuellement prend toujours le pas sur le calcul automatique.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Zone de majoration</td><td class="py-2 pr-4">Oui pour « À l'étranger »</td><td class="py-2">« La zone de majoration est requise pour une mission extérieure. » si absente.</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Régions de destination</td><td class="py-2 pr-4">Oui pour « Intérieur du pays »</td><td class="py-2">« Au moins une région de destination est requise pour une mission région. »</td></tr>
                    <tr><td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Lieu de signature</td><td class="py-2 pr-4">Non</td><td class="py-2">100 caractères max., « Bamako » par défaut.</td></tr>
                </tbody>
            </table>
        </div>
        <x-doc.astuce>
            Le <strong>nombre de jours</strong> se calcule différemment selon le type : jours <em>ouvrables</em>
            (week-ends exclus) pour une mission « Même ville », jours <em>calendaires</em> inclusifs pour les missions
            « À l'étranger » et « Intérieur du pays ».
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="listes" titre="6. Participants, étapes & signataires" chapo="Trois listes à lignes ajoutables/supprimables dynamiquement, communes aux formulaires de création et de modification.">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Participants (au moins un)</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Nom complet*</strong> (255 caractères max.).</li>
            <li><strong>Catégorie</strong> : obligatoire pour les missions « À l'étranger » et « Intérieur du pays » (message : « La catégorie du participant est requise pour une mission {extérieure/région}. ») — c'est elle qui détermine le taux appliqué (voir §7). Chaque participant d'une même mission peut avoir une catégorie différente.</li>
            <li><strong>Nombre de nuitées</strong> : pour une mission « À l'étranger », il est de toute façon forcé à <em>jours − 1</em>, quelle que soit la valeur saisie.</li>
        </ul>
        <x-doc.astuce type="attention">
            Chaque enregistrement de la mission <strong>remplace entièrement</strong> la liste des participants (et des
            étapes) : leurs identifiants internes ne sont pas conservés d'une modification à l'autre. Ceci n'a pas
            d'incidence visible, sauf si un lien externe pointait vers un participant précis.
        </x-doc.astuce>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Étapes (missions « Intérieur du pays » uniquement)</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Au moins une étape est requise (« Au moins une étape est requise pour une mission région. »).</li>
            <li><strong>Type d'étape*</strong> : Chef-lieu de région / Cercle / Commune / Autre localité.</li>
            <li><strong>Barème*</strong> : « Barème national par catégorie » (lit le frais de mission et l'indemnité dans la catégorie du participant, §7), « Mission à l'intérieur d'une même région » ou « Mission à l'intérieur d'un même cercle » — ces deux derniers appliquent le même forfait fixe (7 500 FCFA de frais de mission, 10 000 FCFA d'indemnité par jour/nuitée), seule l'étiquette diffère selon l'échelon administratif traversé.</li>
            <li><strong>Localité</strong>, nombre de jours et de nuitées ; les dates de chaque étape s'enchaînent automatiquement à partir de la date de départ de la mission, selon leur durée.</li>
            <li>Le total des jours des étapes ne peut pas dépasser la durée de la mission : « Le total des jours d'étapes ({X}) dépasse la durée de la mission ({Y} jours). »</li>
            <li>Case <strong>« Première nuitée payée »</strong> : si cochée, le nombre de nuitées est égal au nombre de jours (au lieu de jours − 1).</li>
        </ul>
        <x-doc.astuce>
            Les forfaits « même région » et « même cercle » sont des montants fixes intégrés à l'application :
            contrairement aux catégories nationales et étrangères, ils ne figurent pas dans l'écran « Barèmes des
            missions » (§8) et ne peuvent pas être modifiés depuis l'interface.
        </x-doc.astuce>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Signataires (au moins un)</h3>
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Nom*</strong> obligatoire ; <strong>fonction</strong> facultative.</li>
            <li>Trois signataires sont pré-remplis à la création (adaptés au type) : pour une mission à l'étranger, LA DBCGOQ / L'AGENT COMPTABLE / LE DIRECTEUR GENERAL ; pour les autres types, P/LA DBCGOQ/PO / L'AGENT COMPTABLE / LE DIRECTEUR GENERAL. Modifiez-les librement selon le circuit de signature réel.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">Bloc d'erreurs global : « {n} champ(s) à corriger avant d'enregistrer : », avec un repère « Participant n°X », « Étape n°X » ou « Signataire n°X » pour chaque ligne en défaut.</p>
    </x-doc.section>

    <x-doc.section id="calcul" titre="7. Calcul automatique du montant" chapo="Les indemnités et frais de mission ne se saisissent jamais à la main : ils découlent du barème correspondant à la catégorie choisie.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Pour chaque participant, le <strong>frais de mission</strong> (par jour) et l'<strong>indemnité</strong> (par nuitée) sont lus dans le barème de sa <strong>catégorie</strong>, puis multipliés par le nombre de jours/nuitées.</li>
            <li>Pour une mission « À l'étranger », une <strong>majoration</strong> (en %) s'applique ensuite sur ce sous-total, selon le <strong>taux de la zone</strong> choisie.</li>
            <li>Les postes <strong>carburant, location de véhicule, péages, billets d'avion, frais de participation et de visa</strong> restent, eux, en <strong>saisie manuelle</strong> (quantité × prix unitaire) : ils ne dépendent d'aucun barème.</li>
            <li>Le <strong>montant total</strong> de la mission additionne l'ensemble de ces postes ; il n'existe pas pour une mission « Même ville » (seuls les tickets de carburant y sont suivis).</li>
        </ul>
        <x-doc.astuce type="attention">
            Une modification ultérieure des barèmes (§8) ne recalcule <strong>pas</strong> les missions déjà
            enregistrées : elles conservent les montants calculés au moment de leur saisie. Seule une nouvelle mission,
            ou une mission ré-enregistrée après la mise à jour du barème, applique les nouveaux montants.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="baremes" titre="8. Barèmes des missions" chapo="Écran séparé, réservé à superadmin et dbcgoq, accessible hors du module Missions.">
        <x-doc.capture src="images/manuel/missions-baremes.png" alt="Écran des barèmes des missions">Le groupe « Catégories — missions à l'étranger », avec ses montants modifiables.</x-doc.capture>
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Trois groupes de barèmes : <strong>Catégories — missions à l'étranger</strong> et <strong>Catégories — missions intérieur du pays</strong> (7 catégories chacune, avec un frais de mission par jour et une indemnité par nuitée), et <strong>Zones de majoration</strong> (12 zones, avec un taux en %).</li>
            <li>Seuls les <strong>montants</strong> (libellé, description, frais de mission, indemnités, taux) sont modifiables : la liste des catégories et des zones elle-même est <strong>réglementaire</strong>, fixée par l'application — on ne peut ni en ajouter ni en supprimer, pour ne jamais laisser une mission existante orpheline de son barème.</li>
            <li>Enregistrez avec le bouton de mise à jour : message « Barèmes des missions mis à jour. »</li>
        </ul>
        <x-doc.astuce type="interdit">
            Ce module est <strong>inaccessible au rôle service-budget</strong> et au rôle <em>chef</em> — un chef qui
            tente d'y accéder se voit refuser l'accès. La révision des barèmes reste une prérogative exclusive de
            superadmin et dbcgoq.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="cycle" titre="9. Brouillon & finalisation" chapo="Un cycle de vie volontairement simple : deux statuts seulement.">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">📝 Brouillon</span>
            <span class="text-slate-400">→</span>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">🔒 Finalisé</span>
        </div>
        <p>
            Le bouton <strong>« Finaliser »</strong> (visible uniquement sur une mission encore en brouillon) marque le
            document comme arrêté, après confirmation : « Finaliser cette mission ? Le document sera considéré comme
            arrêté. » Succès : « Mission finalisée. »
        </p>
        <x-doc.astuce type="attention">
            La finalisation est avant tout un <strong>repère de statut</strong> : elle ne verrouille pas techniquement
            la mission contre une modification ultérieure si vous en avez la permission — c'est une convention
            d'usage, pas un blocage. Évitez néanmoins de modifier une mission finalisée, le document étant réputé
            arrêté pour le circuit de signature.
        </x-doc.astuce>
        <p>Tenter de finaliser une mission déjà finalisée renvoie le message : « Cette mission est déjà finalisée. »</p>
    </x-doc.section>

    <x-doc.section id="fiche" titre="10. Fiche d'une mission & génération PDF">
        <p>
            La fiche reproduit fidèlement le formulaire officiel CANAM correspondant au type de mission (en-têtes
            « Ministère de la Santé et du Développement Social », « Caisse Nationale d'Assurance Maladie »), avec les
            montants totaux exprimés aussi en toutes lettres. La mise en page — sections, orientation de la page —
            diffère selon le type :
        </p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">Format</th>
                        <th class="py-2">Sections du document</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">🏙️ Même ville</td>
                        <td class="py-2 pr-4">A4 portrait</td>
                        <td class="py-2">I- Frais et indemnités (Sous-total 1) · II- Carburant, tickets par participant (Sous-total 2) · Total en tickets, sans montant en FCFA.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">✈️ À l'étranger</td>
                        <td class="py-2 pr-4">A4 paysage</td>
                        <td class="py-2">I- Frais et indemnités (Sous-total 1) · II- Autres frais : participation, visa (Sous-total 2) · III- Billets d'avion : affaire, économique (Sous-total 3) · Total général · Visa du contrôleur financier.</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">🚗 Intérieur du pays</td>
                        <td class="py-2 pr-4">A4 paysage</td>
                        <td class="py-2">I- Frais et indemnités, un sous-total par étape/groupe · II- Carburant : trajet, ville, location véhicule, billet d'avion (Sous-total 2) · III- Péages · Total général.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>
            Le bouton <strong>« Générer le PDF »</strong> (si vous détenez <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">generate_missions_pdf</code>)
            produit le document directement dans votre navigateur, à partir des données déjà affichées sur la page —
            aucun aller-retour serveur n'est nécessaire.
        </p>
        <x-doc.astuce>
            Le bloc « Arrêté à la somme de » suivi du lieu, de la date et des signatures bascule automatiquement sur
            une nouvelle page s'il ne tient pas dans l'espace restant, plutôt que de déborder hors du document — utile
            pour les missions à nombreux participants.
        </x-doc.astuce>
    </x-doc.section>

    <x-doc.section id="limites" titre="11. Ce que ce module ne fait pas (à ne pas chercher)" chapo="Pour éviter toute confusion, quelques points expressément absents de ce module.">
        <ul class="list-disc space-y-1.5 pl-5">
            <li><strong>Aucun contrôle de solde budgétaire</strong> : le « code budgétaire » est un simple champ de traçabilité, sans lien avec un plafond ni un budget consommé.</li>
            <li><strong>Aucun suivi de l'engagement au paiement</strong> : le module s'arrête au document « Brouillon »/« Finalisé » ; il ne trace pas la liquidation ni le paiement effectif de la mission.</li>
            <li><strong>Aucune notification</strong> (ni e-mail, ni in-app) n'est envoyée à la création, la modification, la finalisation ou la suppression d'une mission.</li>
            <li><strong>Aucune restauration</strong> depuis l'interface pour une mission supprimée (la suppression reste techniquement réversible en base, mais aucun écran ne permet de l'annuler vous-même).</li>
        </ul>
    </x-doc.section>

    <x-doc.section id="erreurs" titre="12. Messages d'erreur & que faire">
        <div class="space-y-3">
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« La zone de majoration est requise pour une mission extérieure. »</summary>
                <p class="mt-2">Choisissez une zone de majoration avant d'enregistrer une mission « À l'étranger ».</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« La catégorie du participant est requise pour une mission extérieure / région. »</summary>
                <p class="mt-2">Renseignez la catégorie de chaque participant : c'est elle qui détermine le taux appliqué (§7).</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Au moins une région de destination / une étape est requise pour une mission région. »</summary>
                <p class="mt-2">Une mission « Intérieur du pays » exige au moins une région sélectionnée et au moins une étape renseignée.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Le total des jours d'étapes ({X}) dépasse la durée de la mission ({Y} jours). »</summary>
                <p class="mt-2">Ajustez la durée des étapes, ou la durée globale de la mission, pour que la somme des jours d'étape ne dépasse pas le nombre total de jours de la mission.</p>
            </details>
            <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">« Cette mission est déjà finalisée. »</summary>
                <p class="mt-2">La mission n'était plus en brouillon au moment de cliquer sur « Finaliser » — rafraîchissez la fiche.</p>
            </details>
        </div>
    </x-doc.section>

    <x-doc.section id="pratiques" titre="13. Bonnes pratiques">
        <ul class="list-disc space-y-1.5 pl-5">
            <li>Choisissez la <strong>catégorie</strong> de chaque participant avec soin : c'est elle, et non une saisie manuelle, qui fixe le montant des indemnités et frais de mission.</li>
            <li>Vérifiez les <strong>barèmes</strong> avant une campagne de missions importante : leurs montants ne s'appliquent pas rétroactivement aux missions déjà enregistrées.</li>
            <li>Adaptez systématiquement les <strong>signataires</strong> pré-remplis au circuit de signature réel de la mission avant de finaliser.</li>
            <li>Utilisez la <strong>finalisation</strong> comme un repère de gestion (« ce document ne doit plus bouger ») plutôt que comme une protection technique : évitez toute modification après ce point.</li>
            <li>Suivez le paiement effectif des missions dans l'outil comptable dédié : SYGECO ne trace que le document et son montant calculé, pas son règlement.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Chapitre précédent : <a href="{{ route('documentation.suivi') }}" class="font-medium underline">📊 Suivi &amp; évaluation</a>.
        </p>
    </x-doc.section>
</x-doc.page>
