<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LaporKetua</title>
    @vite('resources/css/app.css')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Left Side - Blue Section -->
        <div class="hidden lg:flex lg:w-[40%] bg-blue-600 p-12 flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 text-white">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold">LaporKetua</span>
                </div>

                <div class="mt-16">
                    <h1 class="text-4xl font-bold text-white mb-4">Buat Akun Baru</h1>
                    <p class="text-blue-100 text-lg">
                        Platform pelaporan observasi bahaya dalam lingkungan kerja migas. Sampaikan laporan Anda dengan mudah dan aman.
                    </p>
                </div>
            </div>

            <!-- Testimonial Card -->

        </div>

        <!-- Right Side - Form Section -->
        <div class="flex-1 flex items-center justify-center py-3 bg-gray-50">
            <div class="absolute top-5 right-4">
                <a href="/">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left-from-line text-blue-600"><path d="m9 6-6 6 6 6"/><path d="M3 12h14"/><path d="M21 19V5"/></svg>
                </a>
            </div>
            
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Mari Mulai</h2>
                    <p class="text-gray-600 mt-2">Lengkapi data diri Anda untuk membuat akun</p>
                </div>

                <!-- Progress Indicator -->
                <div class="flex justify-between mb-8">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-blue-600"></div>
                        <div class="w-3 h-3 rounded-full bg-gray-200"></div>
                        <div class="w-3 h-3 rounded-full bg-gray-200"></div>
                        <div class="w-3 h-3 rounded-full bg-gray-200"></div>
                    </div>
                </div>

                <form action="{{ route('register.post') }}" method="POST" class="space-y-6" novalidate>
                    @csrf
                    <div class="flex gap-4">
                        <div>
                            <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                            <input type="number" min="0" max="16" name="nik" id="nik" required value="{{ old('nik') }}"
                                class="mt-1 block w-56 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('nik')
                                    <p class="text-red-500 text-sm">{{ $message  }} </p>
                                @enderror
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" id="username" required value="{{ old('username') }}"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('username')
                                    <p class="text-red-500">{{ $message  }} </p>
                                @enderror
                        </div>

                    </div>

                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('nama')
                            <p class="text-red-500">{{ $message  }} </p>
                            @enderror
                    </div>

                    <div>
                        <label for="no_telp" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                +62
                            </span>
                            <input type="number" min="0" name="telp" id="telp" required value="{{ old('telp') }}"
                                class="flex-1 block w-full px-3 py-2 border border-gray-300 rounded-none rounded-r-md focus:ring-blue-500 focus:border-blue-500">
                                @error('telp')
                                    <p class="text-red-500">{{ $message  }} </p>
                                @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" required 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('password')
                                <p class="text-red-500">{{ $message  }} </p>
                            @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="terms" id="terms" required 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            Saya setuju dengan <a href="#" class="text-blue-600 hover:text-blue-500">Syarat dan Ketentuan</a>
                        </label>
                    </div>

                    <button type="submit" 
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Daftar Sekarang
                    </button>
                </form>

                {{-- @dump($message) --}}

                <p class="mt-6 text-center text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>