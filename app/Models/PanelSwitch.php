<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Panel; // 👈 PENTING: Tambahkan baris ini!

class PanelSwitch extends Model
{
    // Nama tabel akan otomatis terdeteksi sebagai 'panel_switches'
    // Jika Anda menggunakan custom table name, Anda bisa tambahkan:
    // protected $table = 'panel_switches'; 

    protected $fillable = ['panel_id', 'switch_name', 'ip_switch', 'total_ports', 'notes'];

    public function panel(): BelongsTo
    {
        // Satu PanelSwitch dimiliki oleh satu Panel
        // Sekarang Panel::class sudah dikenal karena di-use di atas
        return $this->belongsTo(Panel::class);
    }
}