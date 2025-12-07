<x-guest-layout>
    <div class="min-h-screen flex items-stretch justify-center p-0">
        
        <div class="flex w-full h-screen">

            <div class="w-full md:w-1/2 p-8 flex flex-col items-center justify-center bg-white border-r border-gray-200">
                
                {{-- Header --}}
                <div class="mb-8 text-center">
                    <img src="{{ asset('images/router.png') }}" alt="Router Icon" class="w-20 h-20 mx-auto mb-2 object-contain">
                    <h2 class="text-2xl font-bold text-gray-800">WELCOME!</h2>
                    <p class="text-gray-600">SWITCH PANEL NETWORK</p>
                </div>
                
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm">
                    @csrf

                    <div class="mb-4">
                             <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label> 
                        <div class="flex rounded-md overflow-hidden shadow-sm border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500">
                            <input id="email" class="w-full px-3 py-2 border-none focus:ring-0" 
                                type="email" name="email" :value="old('email')" required autofocus 
                                autocomplete="username" placeholder="Basadmin@filament.com">
                            <div class="flex items-center justify-center w-12 bg-blue-600 text-white flex-shrink-0">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-2-2a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V6z"></path></svg>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                         <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label> 
                        <div class="flex rounded-md overflow-hidden shadow-sm border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500">
                            <input id="password" class="w-full px-3 py-2 border-none focus:ring-0" 
                                type="password" name="password" required autocomplete="current-password" 
                                placeholder="········">
                            <div class="flex items-center justify-center w-12 bg-blue-600 text-white flex-shrink-0">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6-4h12V6a2 2 0 00-2-2H8a2 2 0 00-2 2v7zm12 0h-12V6a2 2 0 012-2h8a2 2 0 012 2v7z"></path></svg>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-center mt-6">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                            LOGIN NOW
                        </button>
                    </div>

                    {{-- START: Tambahan Link Register --}}
                    @if (Route::has('register'))
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600">
                                Dont have an Account ? 
                                <a class="font-medium text-blue-600 hover:text-blue-700 underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('register') }}">
                                    {{ __('Register') }}
                                </a>
                            </p>
                        </div>
                    @endif
                    {{-- END: Tambahan Link Register --}}
                    
                </form>

            </div>

            <div class="hidden md:block md:w-1/2 h-full p-8 flex items-center justify-center" style="background-color: #f3f4f6;">
                <img src="{{ asset('images/server-illustration.png') }}" alt="Server Illustration" class="max-w-full max-h-full object-contain">
            </div>
        </div>
    </div>
</x-guest-layout>