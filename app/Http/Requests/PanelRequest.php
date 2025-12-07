<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PanelRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang diterapkan pada permintaan.
     */
    public function rules(): array
    {
        $panelId = $this->route('panel'); // Dapatkan ID panel jika ini adalah permintaan UPDATE

        $rules = [
            // Aturan untuk kolom di tabel 'panels'
            'panel_name' => ['required', 'string', 'max:255', Rule::unique('panels')->ignore($panelId)],
            'building'   => ['required', 'string', 'max:255'],
            'position'   => ['required', 'string', 'max:255'],
            // Kolom switch_name/ip_switch/total_ports TIDAK ADA di sini
            'status'     => ['required', 'string', 'in:online,offline,maintenance'],
            'notes'      => ['nullable', 'string'],
            'active'     => ['nullable', 'boolean'],
        ];

        // 🌟 ATURAN UNTUK MULTIPLE SWITCHES (Array Input)
        if ($this->has('panel_switches')) {
            $rules['panel_switches'] = ['nullable', 'array'];
            
            // Aturan validasi untuk setiap elemen dalam array panel_switches
            $rules['panel_switches.*.switch_name'] = ['required', 'string', 'max:255'];
            
            // Aturan UNIK untuk IP Switch: Harus unik di tabel panel_switches, 
            // kecuali IP tersebut dimiliki oleh PanelSwitch lain yang terkait dengan Panel yang sedang di-edit.
            $rules['panel_switches.*.ip_switch'] = [
                'required', 
                'ip', 
                // Gunakan Rule::unique dengan pengecualian ID PanelSwitch.
                // Karena kita menggunakan createMany() di Controller, pendekatan ini cukup kompleks. 
                // Untuk kesederhanaan, kita akan membatasi validasi UNIK hanya berdasarkan IP Address:
                Rule::unique('panel_switches', 'ip_switch')->ignore($this->route('panel'), 'panel_id')
            ];

            // Jika Anda merasa Rule::unique di atas terlalu kompleks, cara paling aman adalah:
            // $rules['panel_switches.*.ip_switch'] = ['required', 'ip']; // TIDAK MENCEGAH DUPLIKAT IP
            // dan tangani error duplikat IP di Controller menggunakan try/catch DB.

            $rules['panel_switches.*.total_ports'] = ['required', 'integer', 'min:1', 'max:100']; // Batasi maksimal 100 port
            $rules['panel_switches.*.notes'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Ubah pesan error default.
     */
    public function messages(): array
    {
        return [
            'panel_switches.*.switch_name.required' => 'Nama Switch wajib diisi.',
            'panel_switches.*.ip_switch.required' => 'IP Switch wajib diisi.',
            'panel_switches.*.ip_switch.ip' => 'IP Switch harus berupa format alamat IP yang valid.',
            'panel_switches.*.ip_switch.unique' => 'Alamat IP ini sudah digunakan oleh Switch lain.',
            'panel_switches.*.total_ports.required' => 'Total Port wajib diisi.',
            'panel_switches.*.total_ports.integer' => 'Total Port harus berupa angka.',
            'panel_switches.*.total_ports.min' => 'Total Port minimal adalah 1.',
        ];
    }
}