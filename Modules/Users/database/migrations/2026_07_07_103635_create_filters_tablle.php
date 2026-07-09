<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('filters', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->string('center')->nullable();
            $table->integer('radius')->nullable();
            $table->text('categories')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('filters');
    }
};