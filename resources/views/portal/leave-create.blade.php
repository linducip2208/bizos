@extends('portal.layout')

@section('title', 'Ajukan Cuti Baru')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Ajukan Cuti Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Isi form berikut untuk mengajukan cuti</p>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if ($balances->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Sisa Cuti {{ date('Y') }}</h3>
        <div class="grid grid-cols-2 gap-3">
            @foreach ($balances as $balance)
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">{{ $balance->leaveType?->name }}</p>
                <p class="text-lg font-bold text-gray-900">{{ $balance->remaining_days }} <span class="text-xs text-gray-400 font-normal">/ {{ $balance->total_days }} hari</span></p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <form action="{{ route('portal.leave.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-4">
            <div>
                <label for="leave_type_id" class="block text-sm font-medium text-gray-700 mb-1">Tipe Cuti <span class="text-red-500">*</span></label>
                <select name="leave_type_id" id="leave_type_id" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    <option value="">Pilih tipe cuti</option>
                    @foreach ($leaveTypes as $type)
                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }} ({{ $type->default_days }} hari/tahun)
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" required min="{{ date('Y-m-d') }}"
                        value="{{ old('start_date') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" id="end_date" required min="{{ date('Y-m-d') }}"
                        value="{{ old('end_date') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Alasan <span class="text-red-500">*</span></label>
                <textarea name="reason" id="reason" rows="3" required maxlength="1000"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                    placeholder="Jelaskan alasan pengajuan cuti Anda">{{ old('reason') }}</textarea>
            </div>

            <div>
                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">Dokumen Pendukung</label>
                <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">Surat dokter, undangan, atau dokumen pendukung (max 5MB)</p>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('portal.leave.index') }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition cursor-pointer">
                    Ajukan Cuti
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
