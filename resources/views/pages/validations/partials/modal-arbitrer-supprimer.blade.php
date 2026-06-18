<div id="arbSupprimerModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40 p-4">
    <div class="mx-auto my-12 max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold dark:text-white">Arbitrage — Supprimer l'activité</h2>
                <p id="arbSupprimerNom" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"></p>
            </div>
            <button type="button" onclick="closeModal('arbSupprimerModal')" class="text-zinc-500 hover:text-zinc-800 dark:hover:text-white">✕</button>
        </div>

        <form id="arbSupprimerForm" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Motif de la suppression (notifié à l'auteur)</label>
                <textarea name="motif" rows="3" required minlength="5" class="arb-input mt-1"></textarea>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('arbSupprimerModal')" class="rounded-lg border border-neutral-300 bg-white px-4 py-2 text-sm text-zinc-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-800 dark:text-white">Annuler</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Supprimer l'activité</button>
            </div>
        </form>
    </div>
</div>
