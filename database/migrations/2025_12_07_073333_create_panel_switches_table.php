<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PERHATIAN: Nama tabel default Laravel untuk Model PanelSwitch adalah panel_switches
        Schema::create('panel_switches', function (Blueprint $table) {
            $table->id();
            // Kunci Asing
            $table->foreignId('panel_id')->constrained()->onDelete('cascade'); 
            
            $table->string('switch_name');
            $table->string('ip_switch')->unique(); 
            $table->integer('total_ports')->default(0); 
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_switches');
    }
};