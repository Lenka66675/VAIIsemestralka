<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskUserTable extends Migration
{
    public function up()
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id'); // Task ID
            $table->unsignedBigInteger('user_id'); // User ID
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending'); // Stav tasku
            $table->text('solution')->nullable(); // Riešenie používateľa
            $table->string('attachment')->nullable(); // Cesta k prílohe
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_user');
    }
}

