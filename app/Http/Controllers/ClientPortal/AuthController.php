<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('client-portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('client')->attempt(
            array_merge($credentials, ['is_active' => true]),
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            $user = Auth::guard('client')->user();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau password tidak ditemukan.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login');
    }

    public function showRegister()
    {
        return view('client-portal.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:client_users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'client_code' => ['nullable', 'string', 'exists:clients,client_code'],
        ]);

        $clientId = null;

        if (!empty($validated['client_code'])) {
            $client = Client::where('client_code', $validated['client_code'])->first();
            if ($client) {
                $clientId = $client->id;
            }
        }

        ClientUser::create([
            'client_id' => $clientId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('client.login')->with('status', 'Registrasi berhasil. Silakan login.');
    }

    public function showForgotPassword()
    {
        return view('client-portal.forgot-password');
    }
}
