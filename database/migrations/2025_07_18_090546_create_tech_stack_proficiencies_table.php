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
        Schema::create('tech_stack_proficiencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staffs')->onDelete('cascade');
            $table->foreignId('tech_stack_id')->constrained('tech_stacks')->onDelete('cascade');
            $table->foreignId('proficiency_level_id')->constrained('proficiency_levels')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['staff_id', 'tech_stack_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tech_stack_proficiencies');
    }
};
