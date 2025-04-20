<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('uploaded_data', function (Blueprint $table) {
            $table->unsignedBigInteger('import_id')->nullable()->after('source_type');

            $table->foreign('import_id')
                ->references('id')
                ->on('imported_files')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('uploaded_data', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropColumn('import_id');
        });
    }
};
