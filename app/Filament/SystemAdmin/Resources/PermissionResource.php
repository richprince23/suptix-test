<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\PermissionResource\Pages;
use App\Models\Permission;
use Filament\Forms\Components\Section;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Permissions';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Permission Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('e.g., view_events, create_tickets, manage_users')
                        ->helperText('Permission name should be lowercase with underscores (snake_case)'),

                    TextInput::make('guard_name')
                        ->default('web')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Guard name (usually "web" for web applications)'),

                    Textarea::make('description')
                        ->placeholder('Brief description of what this permission allows')
                        ->helperText('This description will be shown when assigning permissions to roles')
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn ($record) => $record->description ?? 'No description provided'),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->sortable(),

                TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Used in Roles')
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
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this permission? It will be removed from all roles that currently have it.'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
