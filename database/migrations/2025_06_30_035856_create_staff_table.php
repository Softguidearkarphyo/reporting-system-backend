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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('eng_name');
            $table->string('jp_name');
            $table->string('username');
            $table->string('password');
            $table->text('address');
            $table->string('ph_number')->nullable();
            $table->string('position')->nullable();
            $table->string('role');
            $table->string('email')->nullable();
            $table->date('perment_date')->nullable();
            $table->string('ref_person')->nullable();
            $table->string('ref_ph_number')->nullable();
            $table->string('project')->nullable();
            $table->integer('sort_key')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->timestamp('updated_date')->nullable();
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
