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
    Schema::create('router_devices', function (Blueprint $table) {
        $table->id();
        $table->string('ip_address')->unique();
        $table->string('mac_address')->nullable();
        $table->string('hostname')->nullable();
        $table->timestamp('last_seen_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('router_devices');
    }
};
