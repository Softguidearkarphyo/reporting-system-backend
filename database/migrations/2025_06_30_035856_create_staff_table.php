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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->string('staff_no', 30);
            $table->string('eng_name', 40);
            $table->string('jp_name', 40);
            $table->string('username', 40);
            $table->string('password');
            $table->string('address', 250);
            $table->string('ph_number', 100)->nullable();
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('role');
            $table->string('email')->nullable();
            $table->date('permanent_date')->nullable();
            $table->string('ref_person', 40)->nullable();
            $table->string('ref_ph_number', 100)->nullable();
            $table->unsignedTinyInteger('project',)->nullable();
            $table->unsignedTinyInteger('sort_key')->nullable();
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
