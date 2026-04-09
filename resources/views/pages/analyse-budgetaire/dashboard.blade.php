<x-layouts::app :title="__('Analyse Budgétaire')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Analyse Budgétaire</h1>
                <p class="text-sm text-gray-500">Visualisation des budgets par objectif</p>
            </div>
        </div>

        {{-- Budget total --}}
        <div class="rounded-xl border bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white">
            <p class="text-sm opacity-90">Budget total</p>
            <p class="text-4xl font-bold">{{ number_format($budgetTotal, 0, ',', ' ') }} FCFA</p>
        </div>

        {{-- Budget par objectif --}}
        <div class="rounded-xl border">
            <div class="border-b p-4">
                <h3 class="text-lg font-semibold">Budget par objectif stratégique</h3>
            </div>
            <div class="p-6">
                @foreach ($budgetParObjectif as $item)
                    <div class="mb-4">
                        <div class="flex justify-between mb-1">
                            <span class="font-medium">{{ $item['code'] }} -
                                {{ Str::limit($item['libelle'], 50) }}</span>
                            <span>{{ number_format($item['budget'], 0, ',', ' ') }} FCFA
                                ({{ $item['pourcentage'] }}%)</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $item['pourcentage'] }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top activités --}}
        <div class="rounded-xl border">
            <div class="border-b p-4">
                <h3 class="text-lg font-semibold">Top 10 activités les plus coûteuses</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Code</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Libellé</th>
                            <th class="px-4 py-3 text-right text-xs uppercase">Budget</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topActivites as $activite)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-sm">{{ $activite->code }}</td>
                                <td class="px-4 py-3">{{ Str::limit($activite->libelle, 50) }}</td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ number_format($activite->budget_previsionnel_global, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
