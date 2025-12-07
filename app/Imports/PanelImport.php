<?php

namespace App\Imports;

use App\Models\Panel;
use App\Models\PanelSwitch;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection; 
use Maatwebsite\Excel\Concerns\WithChunkReading; 

class PanelImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * Tentukan batas membaca per chunk untuk memori (disarankan untuk file besar)
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // Gunakan Transaksi Database untuk memastikan semua data tersimpan atau tidak sama sekali
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Konversi semua kunci ke lowercase dan snake_case agar konsisten dengan DB (meski WithHeadingRow sudah membantu)
                $data = collect($row)->mapWithKeys(function ($value, $key) {
                    return [str_replace(' ', '_', strtolower($key)) => $value];
                });

                $panelName = $data['nama_panel'] ?? null;
                
                // Lewati baris jika nama panel kosong
                if (empty($panelName)) {
                    continue; 
                }

                // 1. Data Panel
                $panelData = [
                    'panel_name' => $panelName,
                    // Gunakan header yang benar dari file Excel
                    'building'   => $data['gedung'] ?? null,
                    'position'   => $data['posisi'] ?? null,
                    'status'     => $data['status_panel'] ?? 'offline', // Menggunakan status_panel
                    'notes'      => $data['catatan_panel'] ?? null,      // Menggunakan catatan_panel
                    // Konversi string/int menjadi boolean
                    'active'     => (strtolower($data['aktif'] ?? 'ya') == 'ya' || ($data['aktif'] ?? 1) == 1), 
                ];

                // 2. Update atau Create Panel (Upsert)
                $panel = Panel::firstOrNew(['panel_name' => $panelName]);
                $panel->fill($panelData)->save();
                
                // 3. Data Switch
                $switchName = $data['nama_switch'] ?? null;
                $ipSwitch = $data['ip_switch'] ?? null;

                // Cek apakah data switch ada di baris ini (seperti pada format Export yang baru)
                if (!empty($switchName) && !empty($ipSwitch) && $switchName != 'n/a') {
                    
                    // Gunakan IP sebagai kunci unik untuk Switch (firstOrNew)
                    $panelSwitch = PanelSwitch::firstOrNew(['ip_switch' => $ipSwitch]);

                    $switchData = [
                        'panel_id'    => $panel->id,
                        'switch_name' => $switchName,
                        'ip_switch'   => $ipSwitch,
                        'total_ports' => $data['total_port'] ?? 0,
                        'notes'       => $data['catatan_switch'] ?? null, // Menggunakan catatan_switch
                    ];

                    $panelSwitch->fill($switchData)->save();
                } 
                // Catatan: Jika baris ini tidak memiliki data switch, kita hanya mengimpor data Panel.
            }
        });
    }
}