<?php

namespace App\Exports;

use App\Models\Panel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class PanelExport implements FromCollection, WithHeadings
{
    protected $panels;

    public function __construct($panels = null)
    {
        // Jika tidak ada data yang difilter, ambil semua
        $this->panels = $panels ?: Panel::all();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $exportData = new Collection();
        
        // Loop melalui Panel yang sudah difilter atau semua Panel
        foreach ($this->panels as $panel) {
            
            // Jika Panel memiliki Switches, buat baris untuk setiap Switch
            if ($panel->panelSwitches->isNotEmpty()) {
                foreach ($panel->panelSwitches as $switch) {
                    $exportData->push([
                        // Detail Panel Utama
                        'nama_panel' => $panel->panel_name,
                        'gedung' => $panel->building,
                        'posisi' => $panel->position,
                        'status_panel' => $panel->status,
                        'catatan_panel' => $panel->notes,
                        'aktif' => $panel->active ? 'YA' : 'TIDAK',

                        // Detail Switch (BARU)
                        'nama_switch' => $switch->switch_name,
                        'ip_switch' => $switch->ip_switch,
                        'total_port' => $switch->total_ports,
                        'catatan_switch' => $switch->notes,
                    ]);
                }
            } else {
                // Jika Panel tidak memiliki Switch, tetap buat satu baris kosong
                $exportData->push([
                    'nama_panel' => $panel->panel_name,
                    'gedung' => $panel->building,
                    'posisi' => $panel->position,
                    'status_panel' => $panel->status,
                    'catatan_panel' => $panel->notes,
                    'aktif' => $panel->active ? 'YA' : 'TIDAK',

                    // Detail Switch (Kosong)
                    'nama_switch' => 'N/A',
                    'ip_switch' => 'N/A',
                    'total_port' => 0,
                    'catatan_switch' => null,
                ]);
            }
        }
        
        return $exportData;
    }

    /**
     * Tentukan Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'NAMA PANEL',
            'GEDUNG',
            'POSISI',
            'STATUS PANEL',
            'CATATAN PANEL',
            'AKTIF',
            'NAMA SWITCH', // Header Baru
            'IP SWITCH',   // Header Baru
            'TOTAL PORT',  // Header Baru
            'CATATAN SWITCH', // Header Baru
        ];
    }
}