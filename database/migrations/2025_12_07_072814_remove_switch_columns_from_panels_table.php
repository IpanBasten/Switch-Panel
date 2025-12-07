<?php

// database/migrations/XXXX_remove_switch_columns_from_panels_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panels', function (Blueprint $table) {
            // Hapus kolom yang akan dipindahkan ke tabel 'switches'
            $table->dropColumn(['switch_name', 'ip_switch', 'total_ports']);
        });
    }

    public function down(): void
    {
       Schema::table('panels', function (Blueprint $table) {
         $table->dropColumn(['switch_name', 'ip_switch', 'total_ports']);
        });
    }
};