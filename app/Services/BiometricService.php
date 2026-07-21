<?php

namespace App\Services;

use App\Models\BiometricRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BiometricService
{
    protected int $challengeTtlSeconds = 300;

    public function registerBiometric(int $userId, string $deviceId, string $publicKey): void
    {
        $existing = BiometricRegistration::where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->first();

        if ($existing) {
            $existing->update([
                'public_key' => $publicKey,
                'is_active' => true,
                'registered_at' => now(),
            ]);
            return;
        }

        BiometricRegistration::create([
            'user_id' => $userId,
            'device_id' => $deviceId,
            'public_key' => $publicKey,
            'device_name' => request()->input('device_name'),
            'platform' => request()->input('platform', 'android'),
            'registered_at' => now(),
            'is_active' => true,
        ]);
    }

    public function verifyChallenge(int $userId, string $deviceId, string $signedChallenge): bool
    {
        $registration = BiometricRegistration::where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$registration) {
            return false;
        }

        $cacheKey = "biometric_challenge:{$userId}:{$deviceId}";
        $originalChallenge = Cache::get($cacheKey);

        if (!$originalChallenge) {
            return false;
        }

        $publicKey = $registration->public_key;
        $publicKeyResource = openssl_pkey_get_public($publicKey);

        if (!$publicKeyResource) {
            return false;
        }

        $verified = openssl_verify(
            $originalChallenge,
            base64_decode($signedChallenge),
            $publicKeyResource,
            OPENSSL_ALGO_SHA256
        );

        if ($verified === 1) {
            Cache::forget($cacheKey);
            $registration->update(['last_used_at' => now()]);
            return true;
        }

        return false;
    }

    public function generateChallenge(int $userId): array
    {
        $challenge = Str::random(64);
        $deviceId = request()->input('device_id');
        $cacheKey = "biometric_challenge:{$userId}:{$deviceId}";

        Cache::put($cacheKey, $challenge, $this->challengeTtlSeconds);

        return [
            'challenge' => $challenge,
            'expires_in' => $this->challengeTtlSeconds,
            'expires_at' => now()->addSeconds($this->challengeTtlSeconds)->toIso8601String(),
        ];
    }

    public function isRegistered(int $userId, string $deviceId): bool
    {
        return BiometricRegistration::where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('is_active', true)
            ->exists();
    }

    public function revokeBiometric(int $userId, string $deviceId): void
    {
        BiometricRegistration::where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->update(['is_active' => false]);
    }

    public function getRegisteredDevices(int $userId): array
    {
        return BiometricRegistration::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'device_id' => $r->device_id,
                'device_name' => $r->device_name,
                'platform' => $r->platform,
                'registered_at' => $r->registered_at?->toIso8601String(),
                'last_used_at' => $r->last_used_at?->toIso8601String(),
            ])
            ->toArray();
    }

    public function revokeAllForUser(int $userId): void
    {
        BiometricRegistration::where('user_id', $userId)
            ->update(['is_active' => false]);
    }
}
