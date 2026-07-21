<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['nullable', 'string'],
            'device_token' => ['nullable', 'string'],
            'platform' => ['nullable', 'string', 'in:ios,android'],
            'pin' => ['nullable', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        if ($request->filled('pin') && $user->pin && $user->pin !== $request->pin) {
            throw ValidationException::withMessages([
                'pin' => ['PIN tidak sesuai.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun tidak aktif. Hubungi administrator.'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'mobile-app')->plainTextToken;

        $user->update(['last_login_at' => now()]);

        if ($request->filled('device_token')) {
            $user->deviceTokens()->updateOrCreate(
                ['token' => $request->device_token],
                [
                    'platform' => $request->platform ?? 'android',
                    'device_name' => $request->device_name,
                ]
            );
        }

        $employee = $user->employee;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'employee' => $employee ? [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'photo' => $employee->photo ? asset('storage/' . $employee->photo) : null,
                    'department' => $employee->department?->name,
                    'position' => $employee->position?->name,
                    'branch' => $employee->branch?->name,
                    'employee_type' => $employee->employee_type,
                    'status' => $employee->status,
                ] : null,
                'role' => $user->role?->name,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'has_pin' => !empty($user->pin),
                'employee' => $employee ? [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'photo' => $employee->photo ? asset('storage/' . $employee->photo) : null,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'gender' => $employee->gender,
                    'birth_date' => $employee->birth_date?->format('Y-m-d'),
                    'join_date' => $employee->join_date?->format('Y-m-d'),
                    'employee_type' => $employee->employee_type,
                    'status' => $employee->status,
                    'department' => $employee->department?->name,
                    'position' => $employee->position?->name,
                    'branch' => $employee->branch?->name,
                ] : null,
                'role' => $user->role?->name,
            ],
        ]);
    }

    public function setupPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6'],
            'password' => ['required'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['password' => ['Password saat ini tidak sesuai.']]);
        }

        $user->update(['pin' => $request->pin]);

        return response()->json(['message' => 'PIN berhasil diatur.']);
    }

    public function removePin(Request $request)
    {
        $request->validate(['password' => ['required']]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['password' => ['Password saat ini tidak sesuai.']]);
        }

        $user->update(['pin' => null]);

        return response()->json(['message' => 'PIN berhasil dihapus.']);
    }

    public function registerDeviceToken(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['required', 'string', 'in:ios,android'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        $user->deviceTokens()->updateOrCreate(
            ['token' => $request->token],
            [
                'platform' => $request->platform,
                'device_name' => $request->device_name,
            ]
        );

        return response()->json(['message' => 'Device token berhasil didaftarkan.']);
    }

    public function updatePassword(Request $request)
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
