<x-layouts::app title="Manuel d'utilisation">
    <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6">
        {{-- En-tête --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-2xl dark:bg-sky-950/40">📖</div>
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Manuel d'utilisation</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Guide pratique de la plateforme SYGECO — planification, suivi et évaluation des activités.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[220px_1fr]">
            {{-- Sommaire --}}
            <aside class="lg:sticky lg:top-6 lg:self-start">
                <nav class="rounded-xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Sommaire</p>
                    <ul class="space-y-1.5">
                        @foreach ([
                            'presentation' => '1. Présentation',
                            'roles' => '2. Rôles & accès',
                            'connexion' => '3. Connexion',
                            'dashboard' => '4. Tableau de bord',
                            'planification' => '5. Planification (PTA)',
                            'activites' => '6. Gestion des activités',
                            'validation' => '7. Circuit de validation',
                            'suivi' => '8. Suivi & évaluation',
                            'budget' => '9. Analyse budgétaire',
                            'notifications' => '10. Notifications',
                            'faq' => '11. Questions fréquentes',
                        ] as $anchor => $label)
                            <li>
                                <a href="#{{ $anchor }}" class="block rounded px-2 py-1 text-slate-600 transition hover:bg-slate-100 hover:text-sky-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-sky-300">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </aside>

            {{-- Contenu --}}
            <div class="space-y-6">
                {{-- 1. Présentation --}}
                <section id="presentation" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">1. Présentation de la plateforme</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        SYGECO est l'outil de gestion et de coordination des activités de la CANAM. Il permet de
                        structurer le <strong>Plan de Travail Annuel (PTA)</strong> autour des résultats stratégiques,
                        objectifs, extrants et activités, puis d'en assurer le <strong>suivi de l'exécution</strong> et
                        l'<strong>évaluation</strong> (mi-parcours et fin d'exercice).
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">🎯 Planifier</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Décliner objectifs, extrants et activités par exercice et par structure.</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/40">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">📊 Suivre & évaluer</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Renseigner l'état d'avancement, le budget consommé et les indicateurs.</p>
                        </div>
                    </div>
                </section>

                {{-- 2. Rôles --}}
                <section id="roles" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">2. Rôles & niveaux d'accès</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Les droits dépendent de votre rôle et de votre rattachement dans la hiérarchie des structures.</p>
                    <div class="mt-4 overflow-x-auto">
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
                                    <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Super administrateur</td>
                                    <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Global</td>
                                    <td class="py-2 text-slate-600 dark:text-slate-300">Paramétrage complet, utilisateurs, structures, exercices.</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">DBCGOQ</td>
                                    <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Global</td>
                                    <td class="py-2 text-slate-600 dark:text-slate-300">Consolidation, validation finale, analyse budgétaire, ouverture des périodes de suivi.</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Chef de structure</td>
                                    <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Sa structure & sous-structures</td>
                                    <td class="py-2 text-slate-600 dark:text-slate-300">Saisie, soumission, validation montante des activités de son périmètre.</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-slate-800 dark:text-slate-100">Agent</td>
                                    <td class="py-2 pr-4 text-slate-500 dark:text-slate-400">Son entité</td>
                                    <td class="py-2 text-slate-600 dark:text-slate-300">Saisie des activités et de leur exécution.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- 3. Connexion --}}
                <section id="connexion" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">3. Connexion & profil</h2>
                    <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-600 dark:text-slate-300">
                        <li>Saisissez votre adresse e-mail professionnelle et votre mot de passe sur l'écran de connexion.</li>
                        <li>Accédez à votre profil via le menu en bas de la barre latérale (votre nom) → <strong>Paramètres</strong>.</li>
                        <li>Vous pouvez y modifier vos informations, votre mot de passe et vos préférences d'affichage (thème clair/sombre).</li>
                    </ol>
                </section>

                {{-- 4. Dashboard --}}
                <section id="dashboard" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">4. Tableau de bord</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Le tableau de bord synthétise l'exercice sélectionné :</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm text-slate-600 dark:text-slate-300">
                        <li><strong>Indicateurs clés</strong> : nombre d'objectifs, d'extrants, d'activités, budget total, taux de réalisation.</li>
                        <li><strong>Évolution trimestrielle</strong> : volume d'activités planifiées et dynamique budgétaire par trimestre (T1 → T4).</li>
                        <li><strong>Répartitions</strong> : budget par objectif, activités par statut, par trimestre et par structure.</li>
                    </ul>
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">💡 Utilisez le sélecteur d'exercice pour changer d'année de référence.</p>
                </section>

                {{-- 5. Planification --}}
                <section id="planification" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">5. Planification (PTA)</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">La planification se structure en cascade :</p>
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                            <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800 dark:bg-sky-950/50 dark:text-sky-200">Résultat stratégique</span>
                            <span class="text-slate-500 dark:text-slate-400">→ orientation de haut niveau</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                            <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-200">Objectif</span>
                            <span class="text-slate-500 dark:text-slate-400">→ rattaché à un exercice</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">Extrant / Résultat</span>
                            <span class="text-slate-500 dark:text-slate-400">→ produit attendu</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950/40">
                            <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">Activité</span>
                            <span class="text-slate-500 dark:text-slate-400">→ action concrète, budgétée et planifiée par trimestre</span>
                        </div>
                    </div>
                </section>

                {{-- 6. Activités --}}
                <section id="activites" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">6. Gestion des activités</h2>
                    <h3 class="mt-3 text-sm font-semibold text-slate-800 dark:text-slate-100">Créer une activité</h3>
                    <ol class="mt-2 list-decimal space-y-1.5 pl-5 text-sm text-slate-600 dark:text-slate-300">
                        <li>Depuis le menu <strong>Activités → Ajouter</strong>, sélectionnez l'extrant de rattachement.</li>
                        <li>Renseignez l'intitulé, le coût (FCFA), l'indicateur objectivement vérifiable et le moyen de vérification.</li>
                        <li>Cochez les trimestres de réalisation prévus (T1 à T4).</li>
                        <li>Enregistrez en <strong>brouillon</strong>, puis soumettez pour validation.</li>
                    </ol>
                    <h3 class="mt-4 text-sm font-semibold text-slate-800 dark:text-slate-100">Activité non programmée</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        Une activité imprévue (hors PTA) peut être ajoutée depuis la page <strong>Suivi</strong> via le bouton
                        « + Activité non programmée ». Elle est rattachée directement à l'exercice en cours.
                    </p>
                </section>

                {{-- 7. Validation --}}
                <section id="validation" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">7. Circuit de validation</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Les activités suivent une <strong>validation montante</strong> le long de la hiérarchie :</p>
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-medium">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">📝 Brouillon</span>
                        <span class="text-slate-400">→</span>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">⏳ En attente</span>
                        <span class="text-slate-400">→</span>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">✅ Validé</span>
                        <span class="text-slate-400">ou</span>
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-rose-800 dark:bg-rose-950/40 dark:text-rose-200">❌ Rejeté</span>
                    </div>
                    <ul class="mt-4 list-disc space-y-1.5 pl-5 text-sm text-slate-600 dark:text-slate-300">
                        <li>Une activité <strong>rejetée</strong> peut être corrigée puis re-soumise ; le motif de refus est indiqué.</li>
                        <li>Le responsable peut procéder à un <strong>arbitrage budgétaire</strong> (modification/fusion) avant validation.</li>
                        <li>Chaque action est tracée dans l'historique de validation de l'activité.</li>
                    </ul>
                </section>

                {{-- 8. Suivi & évaluation --}}
                <section id="suivi" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">8. Suivi & évaluation</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                        La saisie de l'exécution n'est ouverte que pendant les <strong>fenêtres de mi-parcours ou d'évaluation</strong>
                        (le DBCGOQ y a accès en permanence). Depuis la page <strong>Suivi</strong> ou le détail d'une activité,
                        cliquez sur <strong>« ✎ Renseigner l'évaluation »</strong> pour ouvrir la fenêtre de saisie.
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">État d'exécution</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Non réalisé / En cours / Réalisé, avec une observation libre.</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Budget utilisé</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Montant réellement consommé, comparé au budget planifié.</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Valeur de l'indicateur</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Résultat mesuré de l'indicateur de l'activité.</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Comparatif analytique</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Sur le détail : écart / économie, taux de consommation du budget et alerte de dépassement.</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">💡 Un écart budgétaire positif indique une économie ; en rouge, un dépassement du budget planifié.</p>
                </section>

                {{-- 9. Budget --}}
                <section id="budget" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">9. Analyse budgétaire</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                        Réservée aux profils de consolidation (DBCGOQ / super administrateur), la page d'analyse budgétaire
                        offre une vue transversale : répartition par structure, par objectif, distribution par tranches de coût
                        et comparaison planifié / consommé.
                    </p>
                </section>

                {{-- 10. Notifications --}}
                <section id="notifications" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">10. Notifications</h2>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                        Vous êtes notifié (en application et par e-mail) lors des événements clés : soumission d'une activité,
                        validation ou rejet, changement de budget, ouverture d'une période d'évaluation. La cloche de
                        notifications regroupe les alertes non lues ; vous pouvez les marquer comme lues individuellement ou en bloc.
                    </p>
                </section>

                {{-- 11. FAQ --}}
                <section id="faq" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">11. Questions fréquentes</h2>
                    <div class="mt-3 space-y-3 text-sm">
                        <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Je ne peux pas saisir l'exécution d'une activité.</summary>
                            <p class="mt-2 text-slate-600 dark:text-slate-300">La saisie n'est possible que pendant les périodes de mi-parcours ou d'évaluation ouvertes par le DBCGOQ. Un bandeau vous indique l'état de la fenêtre sur la page Suivi.</p>
                        </details>
                        <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Mon activité a été rejetée, que faire ?</summary>
                            <p class="mt-2 text-slate-600 dark:text-slate-300">Consultez le motif de refus, corrigez l'activité (elle redevient modifiable) puis re-soumettez-la pour validation.</p>
                        </details>
                        <details class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-100">Je ne vois pas toutes les activités.</summary>
                            <p class="mt-2 text-slate-600 dark:text-slate-300">Votre périmètre d'affichage dépend de votre structure : un chef voit son sous-arbre, un agent voit son entité. Les filtres en haut de liste permettent d'affiner l'affichage.</p>
                        </details>
                    </div>
                </section>

                <p class="pb-6 text-center text-xs text-slate-400">SYGECO — CANAM · Besoin d'aide supplémentaire ? Contactez la DBCGOQ.</p>
            </div>
        </div>
    </div>
</x-layouts::app>
