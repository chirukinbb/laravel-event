<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('condition', array_keys(config('events.conditions')))->default('free');
            $table->integer('price')->default(0);
            $table->boolean('is_public')->default(true);
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};