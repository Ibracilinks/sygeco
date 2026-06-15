<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 200);
            $table->enum('type', ['departement', 'bureau_regional', 'direction'])->default('bureau_regional');
            $table->foreignId('parent_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->string('responsable_nom')->nullable();
            $table->string('responsable_email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};
