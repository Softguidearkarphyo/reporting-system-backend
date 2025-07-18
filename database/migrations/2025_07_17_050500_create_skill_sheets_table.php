<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('skill_sheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('staff_id');
            $table->string('grade');
            $table->date('join_date');
            $table->unsignedTinyInteger('japanese_level');
            $table->unsignedTinyInteger('sg_experience');
            $table->unsignedTinyInteger('prev_experience');
            $table->unsignedTinyInteger('total_experience');
            $table->string('expertise');
            $table->unsignedTinyInteger('language_level_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_sheets');
    }
};
