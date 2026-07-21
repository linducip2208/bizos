<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Facades\Crypt;

class LicenseService
{
    public function activate(string $key, int $companyId): License
    {
        $decoded = $this->decodeLicenseKey($key);

        return License::create([
            'company_id' => $companyId,
            'license_key_encrypted' => Crypt::encryptString($key),
            'module' => $decoded['module'] ?? 'full',
            'seats' => $decoded['seats'] ?? 10,
            'started_at' => $decoded['started_at'] ?? now()->toDateString(),
            'expires_at' => $decoded['expires_at'] ?? null,
            'status' => 'active',
        ]);
    }

    public function checkValidity(License $license): bool
    {
        return $license->isValid();
    }

    public function getModuleAccess(int $companyId): array
    {
        $modules = [];

        $licenses = License::where('company_id', $companyId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now()->toDateString());
            })
            ->get();

        foreach ($licenses as $license) {
            $modules[] = $license->module;
        }

        return array_unique($modules);
    }

    public function suspendLicense(License $license): void
    {
        $license->update(['status' => 'suspended']);
    }

    public function reactivateLicense(License $license): void
    {
        $license->update(['status' => 'active']);
    }

    public function checkExpiredLicenses(): void
    {
        License::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->toDateString())
            ->update(['status' => 'expired']);
    }

    protected function decodeLicenseKey(string $key): array
    {
        $parts = explode('.', $key);

        if (count($parts) >= 3) {
            $payload = base64_decode($parts[1] ?? '');
            $data = json_decode($payload, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return ['module' => 'full', 'seats' => 10, 'started_at' => now()->toDateString()];
    }

    public function getLicenseInfo(int $companyId): array
    {
        return License::where('company_id', $companyId)
            ->get()
            ->map(fn ($license) => [
                'id' => $license->id,
                'module' => $license->module,
                'seats' => $license->seats,
                'started_at' => $license->started_at->format('Y-m-d'),
                'expires_at' => $license->expires_at?->format('Y-m-d'),
                'status' => $license->status,
                'is_valid' => $license->isValid(),
            ])
            ->toArray();
    }
}
