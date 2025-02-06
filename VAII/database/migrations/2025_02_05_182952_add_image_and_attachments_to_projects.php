<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('image')->nullable(); // Obrázok projektu (nepovinné)
            $table->json('attachments')->nullable(); // Prílohy ako JSON (nepovinné)
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['image', 'attachments']);
        });
    }
};
