<div id="arbFusionnerModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/40 p-4">
    <div class="mx-auto my-12 max-w-3xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold dark:text-white">Arbitrage — Fusionner les activités</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Choisissez, pour chaque champ, la valeur à retenir pour l'activité consolidée. Les activités sources seront archivées et leurs auteurs notifiés.</p>
            </div>
            <button type="button" onclick="closeModal('arbFusionnerModal')" class="text-zinc-500 hover:text-zinc-800 dark:hover:text-white">✕</button>
        </div>

        <form id="arbFusionnerForm" action="{{ route('validations.arbitrer-fusionner') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <div id="fusionIds"></div>
            <div id="fusionFields" class="max-h-[60vh] space-y-4 overflow-y-auto pr-1"></div>

            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Motif (notifié aux auteurs)</label>
                <textarea name="motif" rows="2" class="arb-input mt-1" placeholder="Raison de la fusion (optionnel)"></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('arbFusionnerModal')" class="rounded-lg border border-neutral-300 bg-white px-4 py-2 text-sm text-zinc-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-800 dark:text-white">Annuler</button>
                <button type="submit" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700">Fusionner les activités</button>
            </div>
        </form>
    </div>
</div>
