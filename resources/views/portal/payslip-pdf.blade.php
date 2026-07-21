<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payroll->employee?->first_name ?? '' }} {{ $payroll->employee?->last_name ?? '' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }</style>
</head>
<body class="bg-white p-8 max-w-2xl mx-auto print:p-4">
    <div class="border border-gray-300 rounded-xl p-8">
        <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">SLIP GAJI</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $payroll->period?->period_code ?? '-' }}</p>
            <p class="text-xs text-gray-400">Periode: {{ $payroll->period?->start_date?->format('d M Y') ?? '-' }} - {{ $payroll->period?->end_date?->format('d M Y') ?? '-' }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-xs text-gray-500 mb-1">Nama</p>
                <p class="text-sm font-semibold">{{ $payroll->employee?->first_name ?? '' }} {{ $payroll->employee?->last_name ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">NIP</p>
                <p class="text-sm font-semibold">{{ $payroll->employee?->employee_code ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Jabatan</p>
                <p class="text-sm font-semibold">{{ $payroll->employee?->position?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Departemen</p>
                <p class="text-sm font-semibold">{{ $payroll->employee?->department?->name ?? '-' }}</p>
            </div>
        </div>

        <table class="w-full text-sm mb-4">
            <thead><tr class="border-b border-gray-300"><th class="py-2 text-left text-xs font-semibold text-gray-600 uppercase">Komponen</th><th class="py-2 text-right text-xs font-semibold text-gray-600 uppercase">Jumlah</th></tr></thead>
            <tbody>
                <tr class="border-b border-gray-200"><td class="py-2 text-gray-700">Gaji Pokok</td><td class="py-2 text-right font-mono text-gray-900">Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</td></tr>
                @if ($payroll->overtime_pay > 0)<tr class="border-b border-gray-200"><td class="py-2 text-gray-700">Upah Lembur</td><td class="py-2 text-right font-mono text-gray-900">Rp {{ number_format($payroll->overtime_pay, 0, ',', '.') }}</td></tr>@endif
                @foreach ($payroll->payrollItems as $item)
                <tr class="border-b border-gray-200"><td class="py-2 text-gray-700">{{ $item->salaryComponent?->name ?? 'Komponen' }}</td><td class="py-2 text-right font-mono {{ $item->type === 'deduction' ? 'text-red-600' : 'text-gray-900' }}">{{ $item->type === 'deduction' ? '-' : '' }}Rp {{ number_format($item->amount, 0, ',', '.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td class="py-2 text-sm font-bold text-gray-900">Total Pendapatan</td><td class="py-2 text-right font-mono font-bold text-gray-900">Rp {{ number_format($payroll->total_income_components, 0, ',', '.') }}</td></tr>
                <tr><td class="py-2 text-sm font-bold text-gray-900">Total Potongan</td><td class="py-2 text-right font-mono font-bold text-red-600">-Rp {{ number_format($payroll->total_deduction_components, 0, ',', '.') }}</td></tr>
                <tr class="border-t-2 border-gray-800"><td class="py-3 text-base font-extrabold text-gray-900">GAJI BERSIH</td><td class="py-3 text-right font-mono font-extrabold text-gray-900 text-lg">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td></tr>
            </tfoot>
        </table>

        <div class="text-xs text-gray-400 border-t border-gray-200 pt-4 mt-4">
            <p>Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="px-6 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition cursor-pointer">Cetak PDF</button>
    </div>
</body>
</html>
