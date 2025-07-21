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
            $table->unsignedTinyInteger('position_id');
            $table->unsignedTinyInteger('grade_id');
            $table->date('join_date');
            $table->unsignedTinyInteger('sg_experience');
            $table->unsignedTinyInteger('prev_experience');
            $table->unsignedTinyInteger('total_experience');
            $table->unsignedTinyInteger('japanese_level_id');
            $table->unsignedTinyInteger('major_tech_stack_id');
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
