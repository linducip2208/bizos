<?php

namespace App\Services;

use App\Models\Company;
use App\Models\MarketplaceApp;
use App\Models\MarketplaceInstall;
use App\Models\MarketplaceReview;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarketplaceService
{
    public function registerApp(array $data): MarketplaceApp
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return MarketplaceApp::create($data);
    }

    public function installApp(int $appId, int $companyId): void
    {
        $app = MarketplaceApp::findOrFail($appId);
        $company = Company::findOrFail($companyId);

        $existing = MarketplaceInstall::where('marketplace_app_id', $appId)
            ->where('company_id', $companyId)
            ->first();

        if ($existing) {
            throw new \RuntimeException('Aplikasi sudah ter-install untuk tenant ini.');
        }

        DB::transaction(function () use ($app, $companyId) {
            $install = MarketplaceInstall::create([
                'marketplace_app_id' => $app->id,
                'company_id' => $companyId,
                'installed_version' => $app->version,
                'status' => $app->price_type === 'free' ? 'active' : 'pending_payment',
                'config' => [],
            ]);

            if ($app->migration_class && class_exists($app->migration_class)) {
                $migration = app($app->migration_class);
                if (method_exists($migration, 'up')) {
                    $migration->up();
                }
            }

            if ($app->seeder_class && class_exists($app->seeder_class)) {
                $seeder = app($app->seeder_class);
                if (method_exists($seeder, 'run')) {
                    $seeder->run();
                }
            }

            $app->increment('total_installs');
        });
    }

    public function uninstallApp(int $appId, int $companyId): void
    {
        $install = MarketplaceInstall::where('marketplace_app_id', $appId)
            ->where('company_id', $companyId)
            ->first();

        if (!$install) {
            throw new \RuntimeException('Aplikasi tidak ditemukan untuk tenant ini.');
        }

        $install->delete();

        $app = MarketplaceApp::find($appId);
        if ($app) {
            $app->decrement('total_installs');
        }
    }

    public function checkUpdates(int $appId): ?string
    {
        $app = MarketplaceApp::findOrFail($appId);
        return $app->version;
    }

    public function updateApp(int $appId, int $companyId): void
    {
        $app = MarketplaceApp::findOrFail($appId);
        $install = MarketplaceInstall::where('marketplace_app_id', $appId)
            ->where('company_id', $companyId)
            ->first();

        if (!$install) {
            throw new \RuntimeException('Aplikasi belum ter-install.');
        }

        if (version_compare($app->version, $install->installed_version, '<=')) {
            throw new \RuntimeException('Aplikasi sudah versi terbaru.');
        }

        $install->update([
            'installed_version' => $app->version,
            'last_checked_at' => now(),
        ]);
    }

    public function getStoreListing(?string $category = null, ?string $search = null): Collection
    {
        $query = MarketplaceApp::published();

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('developer', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('is_featured', 'desc')
            ->orderBy('total_installs', 'desc')
            ->get();
    }

    public function getFeaturedApps(): Collection
    {
        return MarketplaceApp::featured()
            ->orderBy('total_installs', 'desc')
            ->limit(8)
            ->get();
    }

    public function validateAppPackage(string $zipPath): array
    {
        $errors = [];
        $warnings = [];

        if (!file_exists($zipPath)) {
            return ['valid' => false, 'errors' => ['File package tidak ditemukan.']];
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['valid' => false, 'errors' => ['Tidak dapat membuka file ZIP.']];
        }

        $hasManifest = false;
        $hasMigration = false;
        $hasServiceProvider = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (Str::contains($filename, 'manifest.json')) {
                $hasManifest = true;
            }
            if (Str::contains($filename, 'migrations/')) {
                $hasMigration = true;
            }
            if (Str::contains($filename, 'ServiceProvider.php')) {
                $hasServiceProvider = true;
            }
        }

        $zip->close();

        if (!$hasManifest) {
            $errors[] = 'Package tidak memiliki manifest.json.';
        }
        if (!$hasMigration) {
            $warnings[] = 'Package tidak memiliki file migrasi.';
        }
        if (!$hasServiceProvider) {
            $warnings[] = 'Package tidak memiliki ServiceProvider.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    public function publishApp(int $appId): void
    {
        $app = MarketplaceApp::findOrFail($appId);
        $app->update(['is_published' => true]);
    }

    public function calculateAppFee(int $companyId): array
    {
        $installs = MarketplaceInstall::where('company_id', $companyId)
            ->with('app')
            ->get();

        $totalMonthly = 0;
        $breakdown = [];

        foreach ($installs as $install) {
            $app = $install->app;
            if (!$app || $app->price_type !== 'monthly') {
                continue;
            }

            $totalMonthly += (float) $app->price;
            $breakdown[] = [
                'app_name' => $app->name,
                'price' => (float) $app->price,
                'status' => $install->status,
            ];
        }

        return [
            'company_id' => $companyId,
            'total_monthly' => $totalMonthly,
            'breakdown' => $breakdown,
        ];
    }

    public function generateAppInvoice(int $companyId): SubscriptionInvoice
    {
        $feeData = $this->calculateAppFee($companyId);

        return SubscriptionInvoice::create([
            'company_id' => $companyId,
            'invoice_number' => 'APP-' . now()->format('Ym') . '-' . strtoupper(Str::random(6)),
            'amount' => $feeData['total_monthly'],
            'tax_amount' => round($feeData['total_monthly'] * 0.11, 2),
            'total' => round($feeData['total_monthly'] * 1.11, 2),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'status' => 'pending',
            'due_date' => now()->addDays(14),
        ]);
    }

    public function getCategories(): array
    {
        return [
            'crm' => 'CRM',
            'finance' => 'Keuangan',
            'hr' => 'SDM',
            'inventory' => 'Inventaris',
            'marketing' => 'Marketing',
            'project' => 'Proyek',
            'communication' => 'Komunikasi',
            'reporting' => 'Laporan',
            'integration' => 'Integrasi',
            'security' => 'Keamanan',
            'utility' => 'Utilitas',
            'other' => 'Lainnya',
        ];
    }

    public function addReview(int $appId, int $companyId, int $userId, int $rating, ?string $review = null): MarketplaceReview
    {
        $existing = MarketplaceReview::where('marketplace_app_id', $appId)
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->update(compact('rating', 'review'));
            $this->recalculateRating($appId);
            return $existing;
        }

        $reviewModel = MarketplaceReview::create([
            'marketplace_app_id' => $appId,
            'company_id' => $companyId,
            'user_id' => $userId,
            'rating' => $rating,
            'review' => $review,
        ]);

        $this->recalculateRating($appId);
        return $reviewModel;
    }

    protected function recalculateRating(int $appId): void
    {
        $avg = MarketplaceReview::where('marketplace_app_id', $appId)->avg('rating');
        MarketplaceApp::where('id', $appId)->update(['rating' => round($avg ?? 0, 2)]);
    }

    public function getInstalledApps(int $companyId): Collection
    {
        return MarketplaceInstall::where('company_id', $companyId)
            ->with('app')
            ->get();
    }

    public function isAppInstalled(int $appId, int $companyId): bool
    {
        return MarketplaceInstall::where('marketplace_app_id', $appId)
            ->where('company_id', $companyId)
            ->exists();
    }
}
