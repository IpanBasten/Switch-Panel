<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class Panel extends Model
{
    use HasFactory;

    protected $fillable = [
        'panel_name',
        'building',
        'position',
        'status',
        'notes',
        'active',
    ];

    /**
     * Relasi One-to-Many: Satu Panel memiliki banyak PanelSwitch.
     */
    public function panelSwitches(): HasMany
    {
        return $this->hasMany(PanelSwitch::class);
    }
    
    /**
     * Accessor untuk mendapatkan Total Ports Aggregat.
     * Dapat dipanggil sebagai $panel->total_ports.
     */
    public function getTotalPortsAttribute()
    {
        // Menghitung total_ports dari semua PanelSwitch yang terkait
        return $this->panelSwitches->sum('total_ports');
    }

    // ==========================================================
    // LOGIKA PENTING: CASCADE DELETE
    // Ini memperbaiki error Foreign Key saat menghapus Panel
    // ==========================================================
    protected static function booted()
    {
        // Sebelum Panel dihapus, hapus SEMUA PanelSwitch yang terkait.
        static::deleting(function ($panel) {
            $panel->panelSwitches()->delete();
        });
    }
}