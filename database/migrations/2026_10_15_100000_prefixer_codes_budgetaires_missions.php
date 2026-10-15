<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Les codes budgétaires des missions prennent le préfixe « G » (CE : 630210 →
 * CE : G630210) : les missions déjà saisies sont alignées sur la nouvelle liste.
 */
return new class extends Migration
{
    private const CODES = ['630210', '638600', '638700', '630220', '630280'];

    public function up(): void
    {
        foreach (self::CODES as $code) {
            DB::table('missions')->where('code_budgetaire', "CE : {$code}")->update(['code_budgetaire' => "CE : G{$code}"]);
        }
    }

    public function down(): void
    {
        foreach (self::CODES as $code) {
            DB::table('missions')->where('code_budgetaire', "CE : G{$code}")->update(['code_budgetaire' => "CE : {$code}"]);
        }
    }
};
