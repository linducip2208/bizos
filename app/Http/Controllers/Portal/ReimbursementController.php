<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use App\Models\ReimbursementCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReimbursementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $status = $request->get('status');
        $reimbursements = Reimbursement::where('employee_id', $employee->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['category', 'reimbursementAttachments'])
            ->latest()
            ->paginate(15);

        return view('portal.reimbursement-index', compact('employee', 'reimbursements', 'status'));
    }

    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard');
        }

        $categories = ReimbursementCategory::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get();

        return view('portal.reimbursement-create', compact('employee', 'categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:reimbursement_categories,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'description' => ['required', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $category = ReimbursementCategory::findOrFail($validated['category_id']);

        if ($category->max_amount && $validated['amount'] > $category->max_amount) {
            return back()->with('error', 'Maksimal reimbursement untuk kategori ini adalah Rp ' . number_format($category->max_amount, 0, ',', '.'));
        }

        $reimbursement = Reimbursement::create([
            'employee_id' => $employee->id,
            'category_id' => $validated['category_id'],
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('reimbursements/' . $employee->id . '/' . $reimbursement->id, 'public');
                ReimbursementAttachment::create([
                    'reimbursement_id' => $reimbursement->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('portal.reimbursement.show', $reimbursement->id)
            ->with('success', 'Pengajuan reimbursement berhasil dibuat.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard');
        }

        $reimbursement = Reimbursement::where('employee_id', $employee->id)
            ->with(['category', 'reimbursementAttachments'])
            ->findOrFail($id);

        return view('portal.reimbursement-show', compact('employee', 'reimbursement'));
    }
}
