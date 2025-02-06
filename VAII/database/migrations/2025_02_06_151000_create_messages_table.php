<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


    /**
     * Run the migrations.
     */
return new class extends Migration {
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('replied')->default(false);
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('replied');
        });
    }
};

