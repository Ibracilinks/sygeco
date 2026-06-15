<x-layouts::app title="Gestion des Structures">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold dark:text-white">Structures</h1>
            <a href="{{ route('structures.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvelle Structure
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-zinc-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Code</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Libellé</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Type</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Parent</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Responsable</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Statut</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach ($structures as $structure)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm dark:text-white">
                                {{ $structure->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium dark:text-white">
                                {{ $structure->libelle }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    if ($structure->type == 'direction') {
                                        $badgeClass =
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
                                        $typeLabel = 'Direction';
                                    } elseif ($structure->type == 'departement') {
                                        $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                        $typeLabel = 'Département';
                                    } elseif ($structure->type == 'bureau_regional') {
                                        $badgeClass =
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                        $typeLabel = 'Bureau Régional';
                                    } else {
                                        $badgeClass = 'bg-gray-100 text-gray-800';
                                        $typeLabel = $structure->type;
                                    }
                                @endphp
                                <span
                                    class="px-2 py-1 text-xs rounded-full {{ $badgeClass }}">{{ $typeLabel }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap dark:text-white">
                                {{ $structure->parent?->libelle ?? '-' }}</td>
                            <td class="px-6 py-4 dark:text-white">
                                {{ $structure->responsable_nom ?? '-' }}
                                @if ($structure->responsable_email)
                                    <div class="text-xs text-zinc-500">{{ $structure->responsable_email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($structure->is_active)
                                    <span class="text-green-600 dark:text-green-400">✓ Actif</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400">✗ Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <x-actions.view :href="route('structures.show', $structure)" :icon-only="true" />
                                    <x-actions.edit :href="route('structures.edit', $structure)" :icon-only="true" />
                                    <x-actions.delete :action="route('structures.destroy', $structure)" :icon-only="true" confirm="Confirmer la suppression ?" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $structures->links() }}
        </div>
    </div>
</x-layouts::app>
