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
            $table->string('source_type');  // Vendor, Change Request, Service Request
            $table->string('request')->nullable();  // ID požiadavky (Vendor ID, Change Request ID, Service Request ID)
            $table->text('description')->nullable();  // Popis
            $table->string('status')->nullable();  // Stav
            $table->string('type')->nullable();  // Kategória
            $table->dateTime('created')->nullable();  // Dátum vytvorenia
            $table->dateTime('finalized')->nullable();  // Dátum dokončenia
            $table->string('vendor')->nullable();  // ID alebo názov dodávateľa
            $table->string('country')->nullable();  // Krajina

            // Meta údaje
            $table->dateTime('imported_at')->default(DB::raw('CURRENT_TIMESTAMP'));  // Čas importu
            $table->string('imported_by')->nullable();  // Kto nahrával súbor

            // Laravel timestamps (created_at, updated_at)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uploaded_data');
    }
};
