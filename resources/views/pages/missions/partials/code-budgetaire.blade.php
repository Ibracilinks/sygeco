<div>
    <label for="code_budgetaire" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Code budgétaire</label>
    <select id="code_budgetaire" name="code_budgetaire"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
        <option value="">Sans code budgétaire</option>
        <option value="CE : 630210" @selected(old('code_budgetaire', $mission->code_budgetaire) === 'CE : 630210')>CE : 630210</option>
        <option value="CE : 638600" @selected(old('code_budgetaire', $mission->code_budgetaire) === 'CE : 638600')>CE : 638600</option>
        <option value="CE : 638700" @selected(old('code_budgetaire', $mission->code_budgetaire) === 'CE : 638700')>CE : 638700</option>
        <option value="CE : 630220" @selected(old('code_budgetaire', $mission->code_budgetaire) === 'CE : 630220')>CE : 630220</option>
        <option value="CE : 630280" @selected(old('code_budgetaire', $mission->code_budgetaire) === 'CE : 630280')>CE : 630280</option>
        {{-- @foreach (\App\Models\Mission::CODES_BUDGETAIRES as $code)
        @endforeach --}}
    </select>
    @error('code_budgetaire')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
