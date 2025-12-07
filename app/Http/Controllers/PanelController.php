<?php

namespace App\Http\Controllers;

use App\Models\Panel;
use App\Models\PanelSwitch;
use App\Http\Requests\PanelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PanelExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PanelImport;

class PanelController extends Controller
{
    /**
     * Menampilkan daftar Panel (Dashboard Index).
     */
    public function index(Request $request)
    {
        // Logika untuk filtering dan search
        $q = $request->query('q');
        $status = $request->query('status');

        $panels = Panel::query()
            // Eager load relasi panelSwitches untuk performa
            ->with('panelSwitches') 
            ->when($q, fn($queryBuilder) => $queryBuilder->where(function($s) use ($q) {
                // Search sekarang hanya di kolom Panel, karena switch_name sudah dipindah
                $s->where('panel_name','like',"%{$q}%")
                    ->orWhere('building','like',"%{$q}%")
                    ->orWhere('position','like',"%{$q}%");
            }))
            ->when($status, fn($queryBuilder) => $queryBuilder->where('status', $status))
            ->orderBy('panel_name', 'asc')
            ->paginate(10);

        // Menghitung data ringkasan (Contoh: Total Ports sekarang dihitung dari Accessor)
        $totalPanel = Panel::count();
        $totalBuilding = Panel::distinct('building')->count('building');
        $totalSwitch = PanelSwitch::count(); // Ambil dari tabel PanelSwitch yang baru
        $lastUpdate = Panel::latest('updated_at')->first();
        $lastUpdateFormatted = $lastUpdate ? $lastUpdate->updated_at->format('d M Y H:i') : 'N/A';

        return view('dashboard', compact('panels', 'totalPanel', 'totalBuilding', 'totalSwitch', 'lastUpdateFormatted'));
    }

    /**
     * Tampilkan form untuk membuat Panel baru.
     */
    public function create()
    {
        return view('panels.create');
    }

    /**
     * Simpan data Panel dan Switch terkait yang baru dibuat.
     */
    public function store(PanelRequest $request)
    {
        // Mulai transaksi untuk memastikan Panel dan Switches tersimpan bersamaan
        DB::beginTransaction();

        try {
            // 1. Simpan data Panel (Model utama)
            $panel = Panel::create($request->validated());

            // 2. Simpan data Switches terkait
            if ($request->has('panel_switches') && is_array($request->input('panel_switches'))) {
                
                $switchesData = collect($request->input('panel_switches'))
                    ->filter(fn($switch) => !empty($switch['switch_name']) && !empty($switch['ip_switch']));

                // Simpan data switches melalui relasi
                $panel->panelSwitches()->createMany($switchesData->all());
            }

            DB::commit(); // Selesai, simpan semua perubahan
            return redirect()->route('dashboard')->with('success', 'Panel dan Switch berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack(); // Jika ada error, batalkan semua perubahan
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan form untuk mengedit Panel yang sudah ada.
     */
    public function edit(Panel $panel)
    {
        // Eager load Switches untuk pre-fill form
        $panel->load('panelSwitches');
        return view('panels.edit', compact('panel'));
    }

    /**
     * Perbarui Panel dan Switch terkait yang sudah ada.
     */
    public function update(PanelRequest $request, Panel $panel)
    {
        DB::beginTransaction();

        try {
            // 1. Update data Panel (Model utama)
            $panel->update($request->validated());

            // 2. Kelola data Switches terkait
            if ($request->has('panel_switches') && is_array($request->input('panel_switches'))) {
                
                $switchesData = collect($request->input('panel_switches'))
                    ->filter(fn($switch) => !empty($switch['switch_name']) && !empty($switch['ip_switch']));

                // Metode sederhana untuk update One-to-Many: hapus semua yang lama, buat yang baru.
                // Jika data Switch mengandung ID dan Anda ingin update spesifik, ini harus lebih kompleks.
                $panel->panelSwitches()->delete();
                $panel->panelSwitches()->createMany($switchesData->all());
            } else {
                 // Jika tidak ada data switch yang dikirim, hapus semua switch lama
                 $panel->panelSwitches()->delete();
            }
            
            DB::commit();
            return redirect()->route('dashboard')->with('success', 'Panel dan Switch berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }


    
    // ... destroy method ...
    public function destroy(Panel $panel)
    {
    // Logika Cascade Delete sudah kita tambahkan di Panel Model (metode booted)
    // Jadi, cukup panggil delete() pada Panel, dan Switches terkait akan ikut terhapus.
    try {
        $panelName = $panel->panel_name;
        $panel->delete();

        return redirect()->route('dashboard')->with('success', "Panel '{$panelName}' dan semua Switches terkait berhasil dihapus!");
        
    } catch (\Exception $e) {
        // Tangani jika terjadi error database lain (misalnya, foreign key yang tidak terduga)
        return redirect()->back()->with('error', 'Gagal menghapus Panel: ' . $e->getMessage());
    }
    }

   public function export(Request $request)
    {
    $q = $request->query('q');
    $status = $request->query('status');

    $panelsQuery = Panel::query()
        ->with('panelSwitches') // Wajib: agar data Switches tersedia saat looping
        ->when($q, fn($queryBuilder) => $queryBuilder->where(function($s) use ($q) {
            // Logika search yang sama dengan index()
            $s->where('panel_name','like',"%{$q}%")
                ->orWhere('building','like',"%{$q}%")
                ->orWhere('position','like',"%{$q}%")
                ->orWhereHas('panelSwitches', function ($query) use ($q) {
                    $query->where('switch_name', 'like', "%{$q}%")
                          ->orWhere('ip_switch', 'like', "%{$q}%");
                });
        }))
        ->when($status, fn($queryBuilder) => $queryBuilder->where('status', $status))
        ->orderBy('panel_name', 'asc');

    $filteredPanels = $panelsQuery->get();

    return Excel::download(new PanelExport($filteredPanels), 'data_panel_switch_' . now()->format('Ymd_His') . '.xlsx');
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'file.required' => 'File harus diunggah.',
            'file.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
        ]);

        try {
            // Panggil kelas PanelImport untuk mengimpor file
            Excel::import(new PanelImport, $request->file('file'));

            return redirect()->route('dashboard')->with('success', 'Data Panel dan Switches berhasil diimpor!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: {$failure->errors()[0]}";
            }

            // Gabungkan pesan error menjadi satu string atau array yang mudah dibaca
            $errorMessage = "Gagal mengimpor data! Ada kesalahan pada: " . implode('; ', array_slice($errorMessages, 0, 5)) . (count($errorMessages) > 5 ? ' dan ' . (count($errorMessages) - 5) . ' kesalahan lainnya.' : '');

            return redirect()->back()->with('error', $errorMessage);

        } catch (\Exception $e) {
            // Tangani error umum lainnya
            // Anda dapat log $e->getMessage() untuk debugging lebih lanjut
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}