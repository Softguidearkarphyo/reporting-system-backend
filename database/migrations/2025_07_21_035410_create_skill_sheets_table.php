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
            $table->foreignId('staff_id')
                ->constrained('staffs')
                ->onDelete('cascade');
            $table->foreignId('position_id')
                ->constrained('positions')
                ->onDelete('cascade');
            $table->foreignId('grade_id')
                ->constrained('grades')
                ->onDelete('cascade');
            $table->date('join_date');
            $table->unsignedTinyInteger('sg_experience')->default(0);
            $table->unsignedTinyInteger('prev_experience')->default(0);
            $table->unsignedTinyInteger('total_experience')->default(0);
            $table->foreignId('japanese_level_id')
                ->constrained('japanese_levels')
                ->onDelete('cascade');
            $table->foreignId('major_tech_stack_id')
                ->constrained('tech_stacks')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes()->nullable();

            $table->unique(['staff_id'], 'staff_skill_sheet_uk');
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
