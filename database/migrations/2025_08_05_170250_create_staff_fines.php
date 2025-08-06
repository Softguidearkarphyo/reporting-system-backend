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
        Schema::create('staff_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')
                ->constrained('staffs')
                ->onDelete('cascade');
            $table->date('date');
            $table->time('time')->nullable();
            $table->decimal('amount', 10, 2);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_fines');
    }
};
