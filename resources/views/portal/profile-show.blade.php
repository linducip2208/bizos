@extends('portal.layout')

@section('title', 'Profil')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1></div>

    @if (session('success'))<div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Data Pribadi</h2></div>
                <div class="p-6 space-y-4">
                    <form action="{{ route('portal.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">NIK</label><p class="text-sm text-gray-900">{{ $employee->id_number ?? '-' }}</p></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">NPWP</label><p class="text-sm text-gray-900">{{ $employee->tax_number ?? '-' }}</p></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Departemen</label><p class="text-sm text-gray-900">{{ $employee->department?->name ?? '-' }}</p></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Jabatan</label><p class="text-sm text-gray-900">{{ $employee->position?->name ?? '-' }}</p></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk</label><p class="text-sm text-gray-900">{{ $employee->join_date?->format('d M Y') ?? '-' }}</p></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Status</label><p class="text-sm text-gray-900 capitalize">{{ $employee->status ?? '-' }}</p></div>
                        </div>
                        <div class="space-y-3">
                            <div><label for="phone" class="block text-xs font-medium text-gray-500 mb-1">No. Telepon</label><input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></div>
                            <div><label for="address" class="block text-xs font-medium text-gray-500 mb-1">Alamat</label><textarea name="address" id="address" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ old('address', $employee->address) }}</textarea></div>
                            <div class="grid grid-cols-3 gap-3"><div><label class="block text-xs font-medium text-gray-500 mb-1">Kota</label><input type="text" name="city" value="{{ old('city', $employee->city) }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></div><div><label class="block text-xs font-medium text-gray-500 mb-1">Provinsi</label><input type="text" name="province" value="{{ old('province', $employee->province) }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></div><div><label class="block text-xs font-medium text-gray-500 mb-1">Kode Pos</label><input type="text" name="postal_code" value="{{ old('postal_code', $employee->postal_code) }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></div></div>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition cursor-pointer">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Ubah Password</h2></div>
                <div class="p-6">
                    <form action="{{ route('portal.profile.password') }}" method="POST">
                        @csrf
                        <div class="space-y-3 max-w-sm">
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Password Saat Ini</label><input type="password" name="current_password" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Password Baru</label><input type="password" name="new_password" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Konfirmasi Password Baru</label><input type="password" name="new_password_confirmation" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></div>
                            @if ($errors->has('current_password'))<p class="text-xs text-red-600">{{ $errors->first('current_password') }}</p>@endif
                            <button type="submit" class="px-4 py-2 bg-gray-700 text-white text-xs font-semibold rounded-lg hover:bg-gray-800 transition cursor-pointer">Ubah Password</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Dokumen</h2></div>
                <div class="p-6">
                    <form action="{{ route('portal.profile.document.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-3 pb-4 border-b border-gray-100 mb-4">
                        @csrf
                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">Tipe</label><select name="document_type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs"><option value="ktp">KTP</option><option value="npwp">NPWP</option><option value="kk">Kartu Keluarga</option><option value="ijazah">Ijazah</option><option value="sertifikat">Sertifikat</option><option value="other">Lainnya</option></select></div>
                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">Nama Dokumen</label><input type="text" name="document_name" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs"></div>
                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">File</label><input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs"></div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">Upload</button>
                    </form>
                    @if($employee->documents->isNotEmpty())
                    <div class="space-y-2">@foreach($employee->documents as $doc)<div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg"><div class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><div><p class="text-xs font-medium text-gray-900">{{ $doc->document_name }}</p><p class="text-xs text-gray-400">{{ strtoupper($doc->document_type) }} @if($doc->expiry_date) | Kadaluarsa: {{ $doc->expiry_date->format('d M Y') }}@endif</p></div></div><div class="flex items-center gap-2">@if($doc->file_path)<a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="text-xs text-indigo-600">Lihat</a>@endif<form action="{{ route('portal.profile.document.delete', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen?')">@csrf @method('DELETE')<button class="text-xs text-red-500 hover:text-red-700">Hapus</button></form></div></div>@endforeach</div>
                    @else<p class="text-xs text-gray-400">Belum ada dokumen.</p>@endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Anggota Keluarga</h2></div>
                <div class="p-6">
                    <form action="{{ route('portal.profile.family.add') }}" method="POST" class="grid grid-cols-4 gap-3 pb-4 border-b border-gray-100 mb-4">
                        @csrf
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Nama</label><input type="text" name="name" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs"></div>
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Hubungan</label><select name="relationship" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs"><option value="spouse">Pasangan</option><option value="child">Anak</option><option value="parent">Orang Tua</option><option value="sibling">Saudara</option></select></div>
                        <div><label class="block text-xs font-medium text-gray-500 mb-1">Tgl Lahir</label><input type="date" name="birth_date" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs"></div>
                        <div class="flex items-end gap-2">
                            <label class="text-xs"><input type="checkbox" name="is_emergency_contact" value="1"> Darurat</label>
                            <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">Tambah</button>
                        </div>
                    </form>
                    @if($employee->familyMembers->isNotEmpty())
                    <div class="space-y-2">@foreach($employee->familyMembers as $fm)<div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg"><div><p class="text-xs font-medium text-gray-900">{{ $fm->name }} <span class="text-gray-400">({{ $fm->relationship }})</span></p>@if($fm->birth_date)<p class="text-xs text-gray-400">{{ $fm->birth_date->format('d M Y') }}</p>@endif</div><div class="flex items-center gap-2">@if($fm->is_emergency_contact)<span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Kontak Darurat</span>@endif<form action="{{ route('portal.profile.family.remove', $fm->id) }}" method="POST">@csrf @method('DELETE')<button class="text-xs text-red-500 hover:text-red-700">Hapus</button></form></div></div>@endforeach</div>
                    @else<p class="text-xs text-gray-400">Belum ada data keluarga.</p>@endif
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm text-center">
                @if($employee->photo)
                <img src="{{ asset('storage/'.$employee->photo) }}" alt="Foto" class="w-24 h-24 rounded-full mx-auto mb-3 object-cover border-2 border-indigo-100">
                @else
                <div class="w-24 h-24 rounded-full mx-auto mb-3 bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-500">{{ strtoupper(substr($employee->first_name, 0, 1)) }}</div>
                @endif
                <h3 class="text-lg font-bold text-gray-900">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
                <p class="text-xs text-gray-500">{{ $employee->employee_code }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $employee->email }}</p>
                <form action="{{ route('portal.profile.update') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700">
                    <button type="submit" class="mt-2 w-full px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-100 transition">Upload Foto</button>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Info Bank</h3>
                <p class="text-xs text-gray-500 mb-1">Bank</p><p class="text-sm text-gray-900 mb-2">{{ $employee->bank_name ?? '-' }}</p>
                <p class="text-xs text-gray-500 mb-1">No. Rekening</p><p class="text-sm text-gray-900 mb-2">{{ $employee->bank_account_number ?? '-' }}</p>
                <p class="text-xs text-gray-500 mb-1">Atas Nama</p><p class="text-sm text-gray-900">{{ $employee->bank_account_name ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Kontrak</h3>
                <p class="text-xs text-gray-500 mb-1">Tipe Karyawan</p><p class="text-sm text-gray-900 mb-2">{{ $employee->employee_type ?? '-' }}</p>
                <p class="text-xs text-gray-500 mb-1">Mulai Kontrak</p><p class="text-sm text-gray-900 mb-2">{{ $employee->contract_start?->format('d M Y') ?? '-' }}</p>
                <p class="text-xs text-gray-500 mb-1">Selesai Kontrak</p><p class="text-sm text-gray-900">{{ $employee->contract_end?->format('d M Y') ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
