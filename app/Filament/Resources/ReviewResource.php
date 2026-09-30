<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Feedback';

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', Filament::getTenant()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('event_id')
                    ->relationship('event', 'title', function (Builder $query) {
                        return $query->where('organizer_id', Filament::getTenant()->id);
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('reviewer_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('reviewer_email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('rating')
                    ->options([
                        1 => '1 Star - Poor',
                        2 => '2 Stars - Fair',
                        3 => '3 Stars - Good',
                        4 => '4 Stars - Very Good',
                        5 => '5 Stars - Excellent',
                    ])
                    ->required()
                    ->default(5),
                Textarea::make('comment')
                    ->rows(4)
                    ->maxLength(1000),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('reviewer_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reviewer_email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->badge()
                    ->colors([
                        'danger' => 1,
                        'warning' => 2,
                        'primary' => 3,
                        'info' => 4,
                        'success' => 5,
                    ])
                    ->formatStateUsing(fn ($state) => $state.' ⭐')
                    ->sortable(),
                TextColumn::make('comment')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('event')
                    ->relationship('event', 'title', function (Builder $query) {
                        return $query->where('organizer_id', Filament::getTenant()->id);
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            // 'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('status', 'pending')->count();
    }
}
