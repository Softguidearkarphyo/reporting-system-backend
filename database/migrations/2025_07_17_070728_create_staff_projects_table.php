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
        Schema::create('staff_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')
                ->constrained('staffs')
                ->onDelete('cascade');
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['staff_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_projects');
    }
};
