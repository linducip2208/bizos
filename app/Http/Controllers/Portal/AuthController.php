<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'pin' => ['nullable', 'string', 'size:6'],
        ]);

        $pin = $request->input('pin');
        if ($pin) {
            $user = User::where('email', $credentials['email'])->first();
            if ($user && $user->pin === $pin && Hash::check($credentials['password'], $user->password)) {
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                $user->update(['last_login_at' => now()]);
                return redirect()->intended(route('portal.dashboard'));
            }
            return back()->withErrors(['email' => 'Email, password, atau PIN tidak sesuai.'])->onlyInput('email');
        }

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::user()->update(['last_login_at' => now()]);
            return redirect()->intended(route('portal.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak ditemukan.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }

    public function showForgotPassword()
    {
        return view('portal.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link reset password telah dikirim ke email Anda.')
            : back()->withErrors(['email' => 'Email tidak ditemukan.']);
    }

    public function showResetPassword(string $token)
    {
        return view('portal.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('portal.login')->with('status', 'Password berhasil direset. Silakan login.')
            : back()->withErrors(['email' => 'Token reset tidak valid.']);
    }

    public function showPinSetup()
    {
        $user = Auth::user();
        return view('portal.pin-setup', ['hasPin' => !empty($user->pin)]);
    }

    public function setupPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6', 'confirmed'],
            'password' => ['required'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update(['pin' => $request->pin]);

        return back()->with('status', 'PIN berhasil diatur.');
    }

    public function removePin(Request $request)
    {
        $request->validate(['password' => ['required']]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update(['pin' => null]);

        return back()->with('status', 'PIN berhasil dihapus.');
    }
}
