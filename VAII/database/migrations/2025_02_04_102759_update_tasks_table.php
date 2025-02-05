<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium'); // Priorita
            }

            if (!Schema::hasColumn('tasks', 'created_at')) {
                $table->timestamp('created_at')->useCurrent(); // Dátum vytvorenia
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::hasColumn('tasks', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
