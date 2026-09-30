<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\Resources\OrganizerResource\RelationManagers\SubscriptionsRelationManager;
use App\Filament\SystemAdmin\Resources\OrganizerResource\Pages;
use App\Models\Organizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizerResource extends Resource
{
    protected static ?string $model = Organizer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Organizations';

    protected static ?string $navigationLabel = 'All Organizers';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Organization Information')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(16),
                    TextInput::make('website')
                        ->url()
                        ->maxLength(255),
                ])->columns(2),

                Section::make('Address Information')->schema([
                    TextInput::make('address')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->maxLength(255),
                    Select::make('state')->required()
                        ->options([
                            'ahafo_region' => 'Ahafo Region',
                            'ashanti_region' => 'Ashanti Region',
                            'bono_region' => 'Bono Region',
                            'bono_east_region' => 'Bono East Region',
                            'central_region' => 'Central Region',
                            'eastern_region' => 'Eastern Region',
                            'greater_accra_region' => 'Greater Accra Region',
                            'northern_region' => 'Northern Region',
                            'north_east_region' => 'North East Region',
                            'savanna_region' => 'Savanna Region',
                            'upper_east_region' => 'Upper East Region',
                            'upper_west_region' => 'Upper West Region',
                            'volta_region' => 'Volta Region',
                            'oti_region' => 'Oti Region',
                            'western_region' => 'Western Region',
                            'western_north_region' => 'Western North Region',
                        ])->searchable()->preload(),
                    Select::make('country')->required()
                        ->options([
                            'ghana' => 'Ghana',
                        ])->searchable()->preload(),
                ])->columns(2),

                Section::make('Branding')->schema([
                    Textarea::make('description')
                        ->maxLength(1000)
                        ->rows(3),
                    FileUpload::make('logo')
                        ->image()->maxFiles(1)
                        ->maxSize(5000)->imageEditorAspectRatios([
                            '1:1'
                        ])
                        ->disk(config('filesystems.default'))
                        ->directory('organizer-logos'),
                    FileUpload::make('banner')
                        ->image()->maxFiles(1)->imageEditor()
                        ->imageEditorAspectRatios([
                            '3.5:1', '16:9'
                         ])
                        ->maxSize(5000)
                        ->disk(config('filesystems.default'))
                        ->directory('organizer-banners'),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->circular()
                    ->size(50),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->email),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('website')
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab(),
                TextColumn::make('events_count')
                    ->counts('events')
                    ->label('Events')
                    ->sortable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizers::route('/'),
            'create' => Pages\CreateOrganizer::route('/create'),
            'view' => Pages\ViewOrganizer::route('/{record}'),
            'edit' => Pages\EditOrganizer::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            SubscriptionsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
