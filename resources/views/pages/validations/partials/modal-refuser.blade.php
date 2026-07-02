<div id="refuserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40 p-4">
    <div class="mx-auto my-12 max-w-xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="refuserModalTitle" class="text-xl font-semibold dark:text-white">Rejeter l'activité</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Indiquez un motif de rejet et choisissez si vous
                    souhaitez notifier l'utilisateur.</p>
            </div>
            <button type="button" onclick="closeModal('refuserModal')"
                class="text-zinc-500 hover:text-zinc-800 dark:hover:text-white">
                ✕
            </button>
        </div>

        <form id="refuserForm" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Motif du rejet</label>
                <textarea name="motif_refus" rows="4" required
                    class="mt-2 w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm text-zinc-800 outline-none transition focus:border-red-500 dark:border-neutral-700 dark:bg-zinc-800 dark:text-white"></textarea>
            </div>
            <div class="flex items-center gap-3">
                <input id="notifier_utilisateur" name="notifier_utilisateur" type="checkbox"
                    class="h-4 w-4 rounded border-neutral-300 text-red-600 focus:ring-red-500" value="1">
                <label for="notifier_utilisateur" class="text-sm text-zinc-700 dark:text-zinc-200">Notifier
                    l'utilisateur</label>
            </div>

            <div>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Historique récent</p>
                <div id="refuserHistory" class="mt-3 space-y-2"></div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('refuserModal')"
                    class="rounded-lg border border-neutral-300 bg-white px-4 py-2 text-sm text-zinc-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                    Annuler
                </button>
                <button type="submit"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Confirmer le rejet
                </button>
            </div>
        </form>
    </div>
</div>
