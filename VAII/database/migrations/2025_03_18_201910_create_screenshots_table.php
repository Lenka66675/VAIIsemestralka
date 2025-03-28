<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up() {
        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Prepojenie na používateľa
            $table->string('image_path'); // Cesta k obrázku v storage
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('screenshots');
    }
};
