<?php

namespace App\Filament\Resources\SuccessionPlans;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SuccessionPlans\Pages\ListSuccessionPlans;
use App\Filament\Resources\SuccessionPlans\Pages\CreateSuccessionPlan;
use App\Filament\Resources\SuccessionPlans\Pages\EditSuccessionPlan;
use App\Filament\Resources\SuccessionPlans\Schemas\SuccessionPlanForm;
use App\Filament\Resources\SuccessionPlans\Tables\SuccessionPlanTable;
use App\Models\SuccessionPlan;

class SuccessionPlanResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = SuccessionPlan::class;
    public static function getNavigationGroup(): string|null { return 'HRM'; }
    protected static ?string $label = 'Rencana Suksesi';
    protected static ?string $pluralLabel = 'Rencana Suksesi';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;
    protected static ?int $navigationSort = 130;
    protected static ?string $recordTitleAttribute = 'id';
    public static function form(Schema $schema): Schema { return SuccessionPlanForm::configure($schema); }
    public static function table(Table $table): Table { return SuccessionPlanTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListSuccessionPlans::route('/'),
        'create' => CreateSuccessionPlan::route('/create'),
        'edit' => EditSuccessionPlan::route('/{record}/edit'),
    ];}
}