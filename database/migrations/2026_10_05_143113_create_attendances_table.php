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
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('staff_id')->constrained('staffs')->cascadeOnDelete();
        $table->date('date');
        $table->time('check_in_time');
        $table->string('ip_address')->nullable();
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->boolean('telegram_notified')->default(false);
        $table->decimal('accuracy', 8, 2)->nullable();
        $table->boolean('is_laptop')->default(false);
        $table->string('device_type', 20)->default('desktop'); 
        $table->string('device_uuid', 255)->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
