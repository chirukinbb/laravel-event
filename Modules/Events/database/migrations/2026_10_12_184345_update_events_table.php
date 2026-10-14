<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->addColumn('boolean', 'approved')->default(false);
            $table->addColumn('text', 'reason')->nullable();
            $table->addColumn('integer', 'updated')->default(0);
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};