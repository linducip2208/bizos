<?php

namespace App\Filament\Resources\RewardResource;

use App\Filament\Resources\RewardResource\Pages\CreateReward;
use App\Filament\Resources\RewardResource\Pages\EditReward;
use App\Filament\Resources\RewardResource\Pages\ListRewards;
use App\Filament\Resources\RewardResource\Schemas\RewardForm;
use App\Filament\Resources\RewardResource\Tables\RewardTable;
use App\Models\Reward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Gamifikasi';
    }

    protected static ?string $label = 'Reward';

    protected static ?string $pluralLabel = 'Reward';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RewardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RewardTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRewards::route('/'),
            'create' => CreateReward::route('/create'),
            'edit' => EditReward::route('/{record}/edit'),
        ];
    }
}
