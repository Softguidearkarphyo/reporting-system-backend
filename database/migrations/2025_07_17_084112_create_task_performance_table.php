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
        Schema::create('task_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')
                ->constrained('staffs')
                ->onDelete('cascade');
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->onDelete('cascade');
            $table->time('period');
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_performance');
    }
};
