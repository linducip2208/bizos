<?php

namespace App\Filament\Resources\Challenges;

use App\Filament\Resources\Challenges\Pages\CreateChallenge;
use App\Filament\Resources\Challenges\Pages\EditChallenge;
use App\Filament\Resources\Challenges\Pages\ListChallenges;
use App\Filament\Resources\Challenges\Schemas\ChallengeForm;
use App\Filament\Resources\Challenges\Tables\ChallengeTable;
use App\Models\Challenge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Learning';
    }

    protected static ?string $label = 'Tantangan';

    protected static ?string $pluralLabel = 'Tantangan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ChallengeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChallengeTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChallenges::route('/'),
            'create' => CreateChallenge::route('/create'),
            'edit' => EditChallenge::route('/{record}/edit'),
        ];
    }
}