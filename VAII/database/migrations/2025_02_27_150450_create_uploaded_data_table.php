<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uploaded_data', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->string('request')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('finalized')->nullable();
            $table->string('vendor')->nullable();
            $table->string('country')->nullable();
            $table->dateTime('imported_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('imported_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uploaded_data');
    }
};
