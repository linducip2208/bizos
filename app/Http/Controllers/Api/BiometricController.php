<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BiometricService;
use Illuminate\Http\Request;

class BiometricController extends Controller
{
    protected BiometricService $biometricService;

    public function __construct(BiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'public_key' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'string', 'in:ios,android'],
        ]);

        $user = $request->user();

        $this->biometricService->registerBiometric(
            $user->id,
            $request->device_id,
            $request->public_key
        );

        return response()->json([
            'success' => true,
            'message' => 'Biometric berhasil didaftarkan.',
            'data' => [
                'device_id' => $request->device_id,
                'registered_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function getChallenge(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        if (!$this->biometricService->isRegistered($user->id, $request->device_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Biometric belum terdaftar di perangkat ini.',
            ], 404);
        }

        $challenge = $this->biometricService->generateChallenge($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Challenge berhasil dibuat.',
            'data' => $challenge,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'signed_challenge' => ['required', 'string'],
        ]);

        $user = $request->user();

        $verified = $this->biometricService->verifyChallenge(
            $user->id,
            $request->device_id,
            $request->signed_challenge
        );

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi biometric gagal.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi biometric berhasil.',
            'data' => [
                'verified' => true,
                'verified_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function revoke(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $this->biometricService->revokeBiometric($user->id, $request->device_id);

        return response()->json([
            'success' => true,
            'message' => 'Biometric berhasil dicabut.',
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $deviceId = $request->get('device_id');

        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter device_id diperlukan.',
            ], 400);
        }

        $isRegistered = $this->biometricService->isRegistered($user->id, $deviceId);

        return response()->json([
            'success' => true,
            'message' => 'Status biometric.',
            'data' => [
                'is_registered' => $isRegistered,
                'device_id' => $deviceId,
            ],
        ]);
    }

    public function devices(Request $request)
    {
        $user = $request->user();
        $devices = $this->biometricService->getRegisteredDevices($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Daftar perangkat biometric.',
            'data' => [
                'devices' => $devices,
                'total' => count($devices),
            ],
        ]);
    }
}
