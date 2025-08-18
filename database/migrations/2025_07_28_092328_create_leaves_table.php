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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rec_id')->constrained('leave_record')->onDelete('cascade');
            $table->date('leave_date')->nullable();
            $table->string('duration')->nullable();
            $table->integer('day_count')->default(1);;
            $table->text('reason')->nullable();
            $table->boolean('leave_type')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
