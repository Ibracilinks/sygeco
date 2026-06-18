<div id="arbModifierModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40 p-4">
    <div class="mx-auto my-12 max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold dark:text-white">Arbitrage — Modifier l'activité</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">L'auteur de l'activité sera notifié de la modification.</p>
            </div>
            <button type="button" onclick="closeModal('arbModifierModal')" class="text-zinc-500 hover:text-zinc-800 dark:hover:text-white">✕</button>
        </div>

        <form id="arbModifierForm" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Nom de l'activité</label>
                <textarea name="nom_activite" rows="2" required class="arb-input mt-1"></textarea>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Indicateur objectivement vérifiable</label>
                    <textarea name="indicateur_objectivement_verifiable" rows="2" required class="arb-input mt-1"></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Moyen de vérification</label>
                    <textarea name="moyen_verification" rows="2" required class="arb-input mt-1"></textarea>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Coût (FCFA)</label>
                    <input type="number" name="cout" min="0" step="1" required class="arb-input mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Chronogramme</label>
                    <div class="mt-2 flex flex-wrap gap-3 text-sm text-zinc-700 dark:text-zinc-200">
                        @foreach (['1', '2', '3', '4'] as $t)
                            <label class="inline-flex items-center gap-1.5">
                                <input type="hidden" name="trimestre_{{ $t }}" value="non">
                                <input type="checkbox" id="arbMod_t{{ $t }}" name="trimestre_{{ $t }}" value="oui" class="rounded border-neutral-300">
                                T{{ $t }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Motif (notifié à l'auteur)</label>
                <textarea name="motif" rows="2" class="arb-input mt-1" placeholder="Raison de la modification (optionnel)"></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('arbModifierModal')" class="rounded-lg border border-neutral-300 bg-white px-4 py-2 text-sm text-zinc-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-800 dark:text-white">Annuler</button>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-400">Enregistrer la modification</button>
            </div>
        </form>
    </div>
</div>
