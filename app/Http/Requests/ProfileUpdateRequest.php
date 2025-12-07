<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PanelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ubah jika perlu role-based
    }

    public function rules(): array
    {
        $panelId = $this->route('panel')?->id ?? null;

        return [
            'panel_name' => ['required','string','max:191','unique:panels,panel_name,'.($panelId ?? 'NULL')],
            'building' => ['nullable','string','max:100'],
            'position' => ['nullable','string','max:100'],
            'switch_name' => ['nullable','string','max:191'],
            'ip_switch' => ['nullable','ip'],
            'total_ports' => ['required','integer','min:1','max:128'],
            'status' => ['required','in:online,offline,maintenance'],
            'notes' => ['nullable','string'],
            'active' => ['nullable','boolean'],
        ];
    }
}
