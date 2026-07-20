<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id');
            $table->foreignId('event_id');
            $table->boolean('is_happened')->default(false);
            $table->text('comment')->nullable();
            $table->string('mark')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('members');
    }
};