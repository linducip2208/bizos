<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('supplier-portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'company_code' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $supplier = Supplier::where('code', $credentials['company_code'])->first();

        if (!$supplier) {
            return back()->withErrors(['company_code' => 'Kode perusahaan tidak ditemukan.'])->onlyInput('email', 'company_code');
        }

        if (Auth::guard('supplier')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::guard('supplier')->user();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('supplier.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau password tidak ditemukan.'])->onlyInput('email', 'company_code');
    }

    public function logout(Request $request)
    {
        Auth::guard('supplier')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('supplier.login');
    }

    public function showRegistration(Request $request)
    {
        $token = $request->get('token');
        $supplierId = $request->get('supplier_id');

        if (!$token || !$supplierId) {
            return redirect()->route('supplier.login')->with('error', 'Link undangan tidak valid.');
        }

        $expectedToken = hash_hmac('sha256', $supplierId, config('app.key'));

        if (!hash_equals($expectedToken, $token)) {
            return redirect()->route('supplier.login')->with('error', 'Link undangan tidak valid atau sudah kadaluarsa.');
        }

        $supplier = Supplier::findOrFail($supplierId);

        return view('supplier-portal.register', compact('supplier', 'token'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:supplier_users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'token' => ['required'],
        ]);

        $expectedToken = hash_hmac('sha256', $validated['supplier_id'], config('app.key'));

        if (!hash_equals($expectedToken, $validated['token'])) {
            return back()->with('error', 'Token tidak valid.');
        }

        if (SupplierUser::where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => 'Email sudah terdaftar.'])->withInput();
        }

        SupplierUser::create([
            'supplier_id' => $validated['supplier_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('supplier.login')->with('status', 'Registrasi berhasil. Silakan login.');
    }

    public function showForgotPassword()
    {
        return view('supplier-portal.forgot-password');
    }
}
