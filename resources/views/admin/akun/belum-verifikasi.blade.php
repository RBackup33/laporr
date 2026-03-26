<x-layout>
    <style>
/* Pastikan elemen tidak menyebabkan overflow */
body {
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Hilangkan scroll horizontal */
}

table {
    width: 100%; /* Sesuaikan tabel dengan lebar container */
    table-layout: auto; /* Hindari tabel melebar */
    border-collapse: collapse; /* Agar rapi */
}

td, th {
    word-wrap: break-word; /* Memastikan teks tidak melampaui kolom */
    white-space: nowrap; /* Untuk menjaga teks tetap di baris */
}

.tooltip {
    max-width: 200px; /* Batasi lebar tooltip */
}

/* Tooltip tetap dalam layar */
.hover\:tooltip[data-tooltip] {
    position: relative;
}

.hover\:tooltip[data-tooltip]::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 0, 0, 0.9);
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.875rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    z-index: 1000;
}

.hover\:tooltip[data-tooltip]:hover::after {
    opacity: 1;
}

    </style>
    <div class=" min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center w-full py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 ">Data Akun Yang Belum Di verifikasi</h1>
                </div>

            </div>
            <div class="mb-6">
                <form action="{{ Route('akun.masyarakat.verifikasi') }}" method="GET" class="flex gap-4">
                    <input type="text" name="search" placeholder="Cari Nama atau username..." value="{{ request('search') }}" class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300">
                        Cari
                    </button>
                </form>
            </div>




            <!-- Account Table -->
            <div class="bg-white rounded-md shadow-md overflow-hidden border">
                <h3 class="text-lg font-semibold text-gray-800 p-6 border-b">Daftar Akun Yang Belum di verifikasi</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nik</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Username</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No Telp</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    verifikasi</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">

                            @forelse ($hilmi_masyarakat as $hilmi_akun)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 text-center">
                                    {{ $hilmi_akun->nik }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $hilmi_akun->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $hilmi_akun->username }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $hilmi_akun->telp }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="/admin/akun/masyarakat/unverified/{{ $hilmi_akun->nik }}" method="POST"
                                        class="w-1/2">
                                        @method('put')
                                        @csrf
                                        <button type="submit"
                                            class="w-full px-8 py-2 flex justify-center items-center bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition duration-300 shadow-md ">
                                            <p class="text-center">verifikasi</p>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex justify-center ">
                                    <form action="/admin/akun/masyarakat/unverified/{{ $hilmi_akun->nik }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-green-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user-round-x text-red-600">
                                                <path d="M2 21a8 8 0 0 1 11.873-7" />
                                                <circle cx="10" cy="8" r="5" />
                                                <path d="m17 17 5 5" />
                                                <path d="m22 17-5 5" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty                            
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data Masyarakat yang ditemukan.</td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">
                {{ $hilmi_masyarakat->links() }}
            </div>
        </div>
    </div>
</x-layout>
