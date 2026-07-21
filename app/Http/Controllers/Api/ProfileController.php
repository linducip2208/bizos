<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use App\Models\Payroll;
use App\Models\PaySlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $employee->load(['department', 'position', 'branch', 'designation', 'grade', 'familyMembers', 'documents']);

        return response()->json([
            'data' => [
                'employee_code' => $employee->employee_code,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'photo' => $employee->photo ? asset('storage/' . $employee->photo) : null,
                'gender' => $employee->gender,
                'birth_date' => $employee->birth_date?->format('Y-m-d'),
                'birth_place' => $employee->birth_place,
                'religion' => $employee->religion,
                'marital_status' => $employee->marital_status,
                'nationality' => $employee->nationality,
                'id_number' => $employee->id_number,
                'tax_number' => $employee->tax_number,
                'address' => $employee->address,
                'city' => $employee->city,
                'province' => $employee->province,
                'postal_code' => $employee->postal_code,
                'join_date' => $employee->join_date?->format('Y-m-d'),
                'contract_start' => $employee->contract_start?->format('Y-m-d'),
                'contract_end' => $employee->contract_end?->format('Y-m-d'),
                'employee_type' => $employee->employee_type,
                'status' => $employee->status,
                'department' => $employee->department?->name,
                'position' => $employee->position?->name,
                'branch' => $employee->branch?->name,
                'bank_name' => $employee->bank_name,
                'bank_account_number' => $employee->bank_account_number,
                'bank_account_name' => $employee->bank_account_name,
                'basic_salary' => $employee->basic_salary,
                'family_members' => $employee->familyMembers->map(fn($f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'relationship' => $f->relationship,
                    'gender' => $f->gender,
                    'birth_date' => $f->birth_date?->format('Y-m-d'),
                    'occupation' => $f->occupation,
                    'phone' => $f->phone,
                    'is_emergency_contact' => $f->is_emergency_contact,
                    'is_dependent' => $f->is_dependent,
                ]),
                'documents' => $employee->documents->map(fn($d) => [
                    'id' => $d->id,
                    'document_type' => $d->document_type,
                    'document_name' => $d->document_name,
                    'file_url' => $d->file_path ? asset('storage/' . $d->file_path) : null,
                    'issue_date' => $d->issue_date?->format('Y-m-d'),
                    'expiry_date' => $d->expiry_date?->format('Y-m-d'),
                ]),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'religion' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:50'],
        ]);

        $employee->update($validated);

        return response()->json(['message' => 'Profil berhasil diperbarui.']);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo_base64' => ['required', 'string'],
        ]);

        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $photoData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->photo_base64));
        $filename = 'employees/photos/' . $employee->id . '_' . time() . '.jpg';
        Storage::disk('public')->put($filename, $photoData);

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->update(['photo' => $filename]);

        return response()->json([
            'message' => 'Foto berhasil diunggah.',
            'photo_url' => asset('storage/' . $filename),
        ]);
    }

    public function payslips(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->with(['period', 'paySlip'])
            ->latest()
            ->paginate(12);

        $data = $payrolls->through(function ($p) {
            return [
                'id' => $p->id,
                'period' => $p->period?->period_code,
                'start_date' => $p->period?->start_date?->format('Y-m-d'),
                'end_date' => $p->period?->end_date?->format('Y-m-d'),
                'payment_date' => $p->period?->payment_date?->format('Y-m-d'),
                'gross_salary' => $p->gross_salary,
                'net_salary' => $p->net_salary,
                'status' => $p->status,
                'has_slip' => (bool) $p->paySlip,
                'slip_id' => $p->paySlip?->id,
                'viewed_at' => $p->paySlip?->viewed_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($data);
    }

    public function payslipPdf(Request $request, $id)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $paySlip = PaySlip::whereHas('payroll', function ($q) use ($employee) {
            $q->where('employee_id', $employee->id);
        })->findOrFail($id);

        if ($paySlip->file_path && file_exists(storage_path('app/public/' . $paySlip->file_path))) {
            $paySlip->update(['viewed_at' => now()]);
            return response()->download(storage_path('app/public/' . $paySlip->file_path));
        }

        return response()->json(['message' => 'File slip gaji tidak ditemukan.'], 404);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['Password saat ini tidak sesuai.']]);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }
}
