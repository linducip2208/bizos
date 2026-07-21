<?php

namespace App\Filament\Resources\VehicleAssignment;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\VehicleAssignment\Pages\ListVehicleAssignments;
use App\Filament\Resources\VehicleAssignment\Pages\CreateVehicleAssignment;
use App\Filament\Resources\VehicleAssignment\Pages\EditVehicleAssignment;
use App\Filament\Resources\VehicleAssignment\Schemas\VehicleAssignmentForm;
use App\Filament\Resources\VehicleAssignment\Tables\VehicleAssignmentTable;
use App\Models\VehicleAssignment;

class VehicleAssignmentResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = VehicleAssignment::class;
    public static function getNavigationGroup(): string|null { return 'Master Data'; }
    protected static ?string $label = 'Penugasan Kendaraan';
    protected static ?string $pluralLabel = 'Penugasan Kendaraan';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?int $navigationSort = 11;
    protected static ?string $recordTitleAttribute = 'id';
    public static function form(Schema $schema): Schema { return VehicleAssignmentForm::configure($schema); }
    public static function table(Table $table): Table { return VehicleAssignmentTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListVehicleAssignments::route('/'),
        'create' => CreateVehicleAssignment::route('/create'),
        'edit' => EditVehicleAssignment::route('/{record}/edit'),
    ];}
}