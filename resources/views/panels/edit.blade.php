<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Edit Panel Jaringan: {{ $panel->panel_name }}</h2>

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('panels.update', $panel) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h3 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Detail Panel Utama</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        {{-- Nama Panel --}}
                        <div>
                            <label for="panel_name" class="block font-medium text-sm text-gray-700">Nama Panel <span class="text-red-500">*</span></label>
                            <input type="text" name="panel_name" id="panel_name" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                value="{{ old('panel_name', $panel->panel_name) }}" required>
                            @error('panel_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Gedung --}}
                        <div>
                            <label for="building" class="block font-medium text-sm text-gray-700">Gedung <span class="text-red-500">*</span></label>
                            <input type="text" name="building" id="building" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                value="{{ old('building', $panel->building) }}" required>
                            @error('building')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        {{-- Posisi --}}
                        <div>
                            <label for="position" class="block font-medium text-sm text-gray-700">Posisi <span class="text-red-500">*</span></label>
                            <input type="text" name="position" id="position" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                value="{{ old('position', $panel->position) }}" required>
                            @error('position')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block font-medium text-sm text-gray-700">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="online" {{ old('status', $panel->status) == 'online' ? 'selected' : '' }}>Online</option>
                                <option value="offline" {{ old('status', $panel->status) == 'offline' ? 'selected' : '' }}>Offline</option>
                                <option value="maintenance" {{ old('status', $panel->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        {{-- Active --}}
                        <div class="flex items-center mt-2">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" id="active" value="1" 
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('active', $panel->active) ? 'checked' : '' }}>
                            <label for="active" class="ml-2 block text-sm text-gray-900">Panel Aktif</label>
                        </div>
                        
                    </div>
                    
                    {{-- Notes Panel --}}
                    <div class="mb-6">
                        <label for="notes" class="block font-medium text-sm text-gray-700">Catatan Panel</label>
                        <textarea name="notes" id="notes" rows="3" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $panel->notes) }}</textarea>
                        @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <hr class="my-8">

                    <h3 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Detail Switches Jaringan</h3>
                    <p class="text-sm text-gray-600 mb-4">Ubah atau tambahkan perangkat Switch yang terhubung ke Panel ini.</p>
                    
                    <div id="switches-container">
                        </div>

                    <button type="button" id="add-switch-btn" 
                        class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-150 ease-in-out mt-4">
                        Tambah Switch
                    </button>
                    
                    <hr class="my-8">

                    <div class="flex justify-end">
                        <button type="submit" 
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md shadow-lg transition duration-150 ease-in-out">
                            Perbarui Panel dan Switches
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('switches-container');
            const addButton = document.getElementById('add-switch-btn');
            let switchIndex = 0;

            const existingSwitches = @json($panel->panelSwitches ?? []);
            const oldSwitches = @json(old('panel_switches', []));

            // Tentukan data yang akan dimuat: old input (jika ada error) atau data dari DB
            const dataToLoad = Object.keys(oldSwitches).length > 0 ? Object.values(oldSwitches) : existingSwitches;


            // Template HTML untuk satu set input Switch
            const switchTemplate = (index, data = {}) => {
                const switchName = data.switch_name || '';
                const ipSwitch = data.ip_switch || '';
                const totalPorts = data.total_ports || '';
                const notes = data.notes || '';
                
                // Mendapatkan error spesifik untuk item ini
                const getError = (fieldName) => {
                    const errorKey = `panel_switches.${index}.${fieldName}`;
                    const errors = @json($errors->toArray());
                    return errors[errorKey] ? errors[errorKey][0] : '';
                };

                return `
                    <div class="switch-item border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50" data-index="${index}">
                        <h4 class="font-semibold text-gray-700 mb-3">Switch #${index + 1}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Switch <span class="text-red-500">*</span></label>
                                <input type="text" name="panel_switches[${index}][switch_name]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="${switchName}" required>
                                ${getError('switch_name') ? `<p class="text-sm text-red-600 mt-1">${getError('switch_name')}</p>` : ''}
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IP Switch <span class="text-red-500">*</span></label>
                                <input type="text" name="panel_switches[${index}][ip_switch]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="${ipSwitch}" required>
                                ${getError('ip_switch') ? `<p class="text-sm text-red-600 mt-1">${getError('ip_switch')}</p>` : ''}
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Ports <span class="text-red-500">*</span></label>
                                <input type="number" name="panel_switches[${index}][total_ports]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="1" value="${totalPorts}" required>
                                ${getError('total_ports') ? `<p class="text-sm text-red-600 mt-1">${getError('total_ports')}</p>` : ''}
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Catatan Switch</label>
                            <textarea name="panel_switches[${index}][notes]" rows="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">${notes}</textarea>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button" class="btn btn-sm text-red-500 hover:text-red-700 remove-switch-btn font-semibold">
                                Hapus Switch
                            </button>
                        </div>
                    </div>
                `;
            };

            // Fungsi untuk menambahkan Switch baru
            function addSwitch(data = {}) {
                const newSwitchHtml = switchTemplate(switchIndex, data);
                container.insertAdjacentHTML('beforeend', newSwitchHtml);
                switchIndex++;
            }

            // Muat data lama
            if (dataToLoad.length > 0) {
                dataToLoad.forEach(data => addSwitch(data));
            } else {
                // Jika tidak ada switch, tampilkan 1 input kosong
                addSwitch();
            }

            // Event Listeners
            addButton.addEventListener('click', () => addSwitch());

            container.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-switch-btn')) {
                    event.target.closest('.switch-item').remove();
                }
            });
        });
    </script>
</x-app-layout>