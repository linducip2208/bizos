@extends('portal.layout')

@section('title', 'Ajukan Reimbursement')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Ajukan Reimbursement</h1><p class="text-sm text-gray-500 mt-1">Isi form untuk mengajukan reimbursement biaya</p></div>
    @if ($errors->any())<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
    @if (session('error'))<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>@endif

    <form action="{{ route('portal.reimbursement.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-4">
            <div><label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label><select name="category_id" id="category_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"><option value="">Pilih kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}@if($c->max_amount) (Max: Rp {{ number_format($c->max_amount, 0, ',', '.') }})@endif</option>@endforeach</select></div>
            <div><label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label><input type="date" name="date" id="date" required max="{{ date('Y-m-d') }}" value="{{ old('date', date('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"></div>
            <div><label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Rp) <span class="text-red-500">*</span></label><input type="number" name="amount" id="amount" required min="1000" value="{{ old('amount') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="Masukkan jumlah"></div>
            <div><label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label><textarea name="description" id="description" rows="3" required maxlength="2000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="Jelaskan detail biaya yang diajukan">{{ old('description') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Bukti / Lampiran</label><input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"><p class="text-xs text-gray-400 mt-1">Upload nota/bukti bayar (max 5MB per file)</p></div>
            <div class="flex gap-3 pt-2"><a href="{{ route('portal.reimbursement.index') }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</a><button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition cursor-pointer">Ajukan Reimbursement</button></div>
        </div>
    </form>
</div>
@endsection
