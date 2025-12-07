<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panels', function (Blueprint $table) {
            $table->id();
            $table->string('panel_name')->unique();
            $table->string('building')->nullable();
            $table->string('position')->nullable(); // e.g. Rack A, Floor 2
            $table->string('switch_name')->nullable();
            $table->string('ip_switch')->nullable();
            $table->unsignedSmallInteger('total_ports')->default(24);
            $table->enum('status', ['online','offline','maintenance'])->default('online');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panels');
    }
};
