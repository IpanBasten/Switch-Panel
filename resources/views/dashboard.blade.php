<x-app-layout>
    {{-- Halaman Dashboard --}}
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- HEADER: Tombol Add Panel --}}
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">SWITCH PANEL NETWORK</h2>
                <div class="text-right">
                   <a href="{{ route('panels.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">ADD PANEL</a>    
                </div>
            </div>
            
            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Error: {{ session('error') }}</span>
                </div>
            @endif

            <h3 class="text-lg font-semibold text-gray-800 mb-4">PANEL ACTIVE</h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                
                {{-- Kartu 1: TOTAL PANEL --}}
                <div class="bg-teal-500 rounded-lg shadow-md p-4 h-32 flex flex-col justify-between">
                    <p class="text-white text-sm">TOTAL PANEL</p>
                    <h2 class="text-white text-4xl font-extrabold">{{ $totalPanel ?? 0 }}</h2>
                </div>

                {{-- Kartu 2: TOTAL BUILDING --}}
                <div class="bg-blue-400 rounded-lg shadow-md p-4 h-32 flex flex-col justify-between">
                    <p class="text-white text-sm">TOTAL BUILDING</p>
                    <h2 class="text-white text-4xl font-extrabold">{{ $totalBuilding ?? 0 }}</h2>
                </div>

                {{-- Kartu 3: TOTAL SWITCH (Menggunakan PanelSwitch::count() dari Controller) --}}
                <div class="bg-purple-400 rounded-lg shadow-md p-4 h-32 flex flex-col justify-between">
                    <p class="text-white text-sm">TOTAL SWITCH</p>
                    <h2 class="text-white text-4xl font-extrabold">{{ $totalSwitch ?? 0 }}</h2>
                </div>

                {{-- Kartu 4: LAST UPDATE --}}
                <div class="bg-orange-400 rounded-lg shadow-md p-4 h-32 flex flex-col justify-between">
                    <p class="text-white text-sm">LAST UPDATE</p>
                    <h2 class="text-white text-sm font-bold">
                        {{ $lastUpdateFormatted }}
                    </h2>
                </div>
            </div>

            <div class="flex justify-between items-center mb-4">
                
                {{-- Form Search & Filter --}}
                <form action="{{ route('dashboard') }}" method="GET" class="flex items-center w-full max-w-lg space-x-2">
                    <div class="relative w-full">
                        <input type="text" name="q" placeholder="Search Panel Name, Switch, or IP..." 
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-green-500 focus:border-green-500 pr-10"
                            value="{{ request('q') }}">
                        <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                
                    {{-- Filter Status (Select) --}}
                    <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-lg py-2 px-3 focus:ring-green-500 focus:border-green-500">
                        <option value="">All Status</option>
                        <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>

                    @if(request('q') || request('status'))
                        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-red-500 text-sm">Clear</a>
                    @endif
                </form>

                {{-- Group Tombol Import dan Export --}}
                <div class="flex space-x-2">
                    {{-- Tombol Import --}}
                    <button type="button" onclick="document.getElementById('import-form-container').classList.toggle('hidden')"
                        class="bg-purple-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-purple-600 uppercase">
                        IMPORT
                    </button>

                    {{-- Export Button --}}
                    <a href="{{ route('panels.export', ['q' => request('q'), 'status' => request('status')]) }}" 
                       class="bg-green-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-600 uppercase">
                        EXPORT
                    </a>
                </div>
            </div>

            {{-- Form Upload Import (Awalnya tersembunyi) --}}
            <div id="import-form-container" class="mt-4 p-4 border border-gray-300 rounded-lg bg-gray-50 mb-6 hidden">
                <form action="{{ route('panels.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-4">
                    @csrf
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Upload File Panel:</label>
                    <input type="file" name="file" required 
                           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none">
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 whitespace-nowrap">
                        Submit Import
                    </button>
                </form>
            </div>
            
            <div class="bg-white overflow-x-auto shadow-md sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PANEL NAME</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BUILDING</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">POSITION</th>
                            
                            {{-- KOLOM BARU UNTUK DATA RINGKAS --}}
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL SWITCHES</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL PORTS</th>
                            
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($panels as $panel)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $panel->panel_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $panel->building }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $panel->position }}</td>
                            
                            {{-- DATA RINGKAS DARI RELASI --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 font-bold">
                                {{ $panel->panelSwitches->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 font-bold">
                                {{-- Menggunakan Accessor getTotalPortsAttribute() yang sudah dibuat --}}
                                {{ $panel->total_ports }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $color = match($panel->status) {
                                        'online' => 'bg-green-100 text-green-800',
                                        'offline' => 'bg-red-100 text-red-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                    {{ strtoupper($panel->status) }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex justify-center space-x-2">
                                {{-- Edit Button --}}
                                <a href="{{ route('panels.edit', $panel->id) }}" class="text-orange-500 hover:text-orange-700" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-5L20 4m-7 7l4 4"></path></svg>
                                </a>
                                {{-- Delete Button --}}
                                <form action="{{ route('panels.destroy', $panel->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus panel {{ $panel->panel_name }}? Semua data Switch terkait akan ikut terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">Tidak ada data Panel ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                {{-- Pagination Links --}}
                <div class="p-4">
                    {{ $panels->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>