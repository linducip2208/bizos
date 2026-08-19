<?php

namespace App\Filament\Pages;

use App\Services\ApprovalCenterService;
use Filament\Pages\Page;

class ApprovalCenter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pusat Persetujuan';

    protected static ?string $slug = 'approval-center';

    protected string $view = 'filament.pages.approval-center';

    public array $pendingApprovals = [];
    public array $approvalHistory = [];
    public int $pendingCount = 0;
    public array $modules = [];
    public string $activeTab = 'pending';
    public string $activeModule = 'all';

    public static function getNavigationGroup(): ?string
    {
        return 'Automation';
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $user = auth()->user();
            if (!$user) return null;
            $service = app(ApprovalCenterService::class);
            $count = $service->getApprovalCount($user);
            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $user = auth()->user();

        $service = app(ApprovalCenterService::class);

        $this->pendingApprovals = $service->getPendingApprovals($user)->toArray();
        $this->approvalHistory = $service->getApprovalHistory($user)->toArray();
        $this->pendingCount = count($this->pendingApprovals);

        $this->modules = collect($this->pendingApprovals)->pluck('module')->unique()->values()->toArray();
    }

    public function getFilteredApprovals(): array
    {
        $items = $this->activeTab === 'pending'
            ? $this->pendingApprovals
            : $this->approvalHistory;

        if ($this->activeModule !== 'all') {
            $items = array_values(array_filter($items, function ($item) {
                return ($item['module'] ?? '') === $this->activeModule;
            }));
        }

        return $items;
    }

    public function getModuleCounts(): array
    {
        $counts = ['all' => count($this->pendingApprovals)];

        foreach ($this->pendingApprovals as $item) {
            $module = $item['module'] ?? 'unknown';
            if (!isset($counts[$module])) {
                $counts[$module] = 0;
            }
            $counts[$module]++;
        }

        return $counts;
    }

    public function getModuleLabel(string $module): string
    {
        return match ($module) {
            'leave' => 'Cuti',
            'overtime' => 'Lembur',
            'reimbursement' => 'Reimburse',
            'purchase_requisition' => 'PR',
            'purchase_order' => 'PO',
            'budget' => 'Anggaran',
            'payment' => 'Pembayaran',
            'contract' => 'Kontrak',
            'intercompany_transaction' => 'Antar Perusahaan',
            'payroll' => 'Penggajian',
            'all' => 'Semua',
            default => ucfirst($module),
        };
    }

    public function getModuleColor(string $module): string
    {
        return match ($module) {
            'leave' => 'blue',
            'overtime' => 'gray',
            'reimbursement' => 'amber',
            'purchase_requisition' => 'rose',
            'purchase_order' => 'indigo',
            'budget' => 'emerald',
            'payment' => 'emerald',
            'contract' => 'purple',
            'intercompany_transaction' => 'cyan',
            'payroll' => 'orange',
            default => 'gray',
        };
    }

    public function getModuleIcon(string $module): string
    {
        return match ($module) {
            'leave' => 'calendar',
            'overtime' => 'clock',
            'reimbursement' => 'receipt-refund',
            'purchase_requisition' => 'clipboard-document-list',
            'purchase_order' => 'shopping-cart',
            'budget' => 'chart-pie',
            'payment' => 'banknotes',
            'contract' => 'document-text',
            'intercompany_transaction' => 'arrows-right-left',
            'payroll' => 'currency-dollar',
            default => 'document-check',
        };
    }

    public function getData(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $service = app(ApprovalCenterService::class);

        $pendingApprovals = $service->getPendingApprovals($user)->toArray();
        $approvalHistory = $service->getApprovalHistory($user)->toArray();
        $pendingCount = count($pendingApprovals);
        $modules = collect($pendingApprovals)->pluck('module')->unique()->values()->toArray();

        $counts = ['all' => $pendingCount];
        foreach ($pendingApprovals as $item) {
            $module = $item['module'] ?? 'unknown';
            if (!isset($counts[$module])) {
                $counts[$module] = 0;
            }
            $counts[$module]++;
        }

        return response()->json([
            'pendingApprovals' => $pendingApprovals,
            'approvalHistory' => $approvalHistory,
            'pendingCount' => $pendingCount,
            'historyCount' => count($approvalHistory),
            'modules' => $modules,
            'moduleCounts' => $counts,
        ]);
    }

    public function handleApprove(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $data = request()->validate([
                'module' => 'required|string',
                'id' => 'required|integer',
            ]);

            $service = app(ApprovalCenterService::class);
            $service->approve($data['module'], $data['id'], $user);

            return response()->json(['success' => true, 'message' => 'Berhasil disetujui.']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.'], 500);
        }
    }

    public function handleReject(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $data = request()->validate([
                'module' => 'required|string',
                'id' => 'required|integer',
                'reason' => 'nullable|string',
            ]);

            $service = app(ApprovalCenterService::class);
            $service->reject($data['module'], $data['id'], $user, $data['reason'] ?? null);

            return response()->json(['success' => true, 'message' => 'Pengajuan ditolak.']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.'], 500);
        }
    }
}
