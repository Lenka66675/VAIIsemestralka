<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('backlog_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique(); // napr. pondelok týždňa
            $table->unsignedInteger('backlog')->default(0); // počet otvorených
            $table->unsignedInteger('backlog_in_days')->default(0); // súčet dní
            $table->float('avg_processing_days')->nullable(); // priemer pre ukončené
            $table->float('on_time_percentage')->nullable(); // % stihnuté do 4 dní
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlog_snapshots');
    }
};
