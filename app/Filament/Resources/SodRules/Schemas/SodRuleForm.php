<?php

namespace App\Filament\Resources\SodRules\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SodRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Aturan Pemisahan Tugas (Segregation of Duties)')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        TextInput::make('name')
                            ->label('Nama Aturan')
                            ->required()
                            ->maxLength(255)
                            ->hint('Contoh: Buat PO vs Approve PO'),
                        Select::make('sensitive_function')
                            ->label('Fungsi Sensitif')
                            ->options(static::permissionOptions())
                            ->required()
                            ->searchable(),
                        Select::make('conflicting_function')
                            ->label('Fungsi Konflik')
                            ->options(static::permissionOptions())
                            ->required()
                            ->searchable()
                            ->different('sensitive_function'),
                        Select::make('risk_level')
                            ->label('Level Risiko')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'critical' => 'Kritis',
                            ])
                            ->required()
                            ->default('high'),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'procurement' => 'Pengadaan',
                                'finance' => 'Keuangan',
                                'payroll' => 'Penggajian',
                                'hr' => 'SDM',
                                'asset' => 'Aset',
                                'inventory' => 'Inventaris',
                                'sales' => 'Penjualan',
                                'system' => 'Sistem',
                            ]),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Toggle::make('is_system_default')
                            ->label('Default Sistem')
                            ->default(false),
                    ]),
                Section::make('Detail')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2),
                        Textarea::make('compensating_controls')
                            ->label('Compensating Controls')
                            ->rows(2)
                            ->hint('Kontrol pengganti jika pemisahan tidak memungkinkan'),
                    ]),
            ]);
    }

    private static function permissionOptions(): array
    {
        return [
            'buat-pr' => 'Buat Purchase Requisition',
            'approve-pr' => 'Approve PR',
            'buat-po' => 'Buat Purchase Order',
            'approve-po' => 'Approve PO',
            'terima-barang' => 'Terima Barang',
            'vendor-master' => 'Master Vendor',
            'buat-invoice' => 'Buat Invoice',
            'approve-invoice' => 'Approve Invoice',
            'buat-journal' => 'Buat Journal',
            'approve-journal' => 'Approve Journal',
            'payment-run' => 'Payment Run',
            'approve-payment' => 'Approve Payment',
            'bank-reconciliation' => 'Bank Reconciliation',
            'setup-coa' => 'Setup COA',
            'manage-budget' => 'Manage Budget',
            'input-payroll' => 'Input Payroll',
            'approve-payroll' => 'Approve Payroll',
            'employee-master' => 'Master Karyawan',
            'input-attendance' => 'Input Attendance',
            'approve-attendance' => 'Approve Attendance',
            'manage-leave' => 'Manage Leave',
            'approve-leave' => 'Approve Leave',
            'asset-disposal' => 'Asset Disposal',
            'approve-disposal' => 'Approve Disposal',
            'asset-record' => 'Asset Record',
            'asset-verification' => 'Asset Verification',
            'stock-adjustment' => 'Stock Adjustment',
            'approve-adjustment' => 'Approve Adjustment',
            'warehouse-master' => 'Master Gudang',
            'stock-movement' => 'Stock Movement',
            'set-pricing' => 'Set Pricing',
            'create-sales-order' => 'Create Sales Order',
            'manage-discount' => 'Manage Discount',
            'approve-sales' => 'Approve Sales',
            'manage-role' => 'Manage Role',
            'manage-user' => 'Manage User',
        ];
    }
}
