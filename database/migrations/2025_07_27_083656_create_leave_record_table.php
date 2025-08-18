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
        Schema::create('leave_record', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staffs')->onDelete('cascade');
            $table->year('year')->default(date('Y'));
            $table->date('permanent_date')->nullable();
            $table->float('carry_leaves')->default(0);
            $table->float('remain_leaves')->default(0);
            $table->float('first_annual')->default(0);
            $table->float('second_annual')->default(0);
            $table->float('total_used')->default(0);
            $table->float('total_leaves')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_record');
    }
};
