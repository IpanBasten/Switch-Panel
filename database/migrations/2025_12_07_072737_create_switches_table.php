<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('switches', function (Blueprint $table) {
            $table->id();
            // Kunci Asing: Menghubungkan Switch ini ke Panel tertentu
            $table->foreignId('panel_id')->constrained()->onDelete('cascade'); 
            
            $table->string('switch_name');
            $table->string('ip_switch')->unique(); // IP Switch harus unik
            $table->integer('total_ports')->default(0); 
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('switches');
    }
};
