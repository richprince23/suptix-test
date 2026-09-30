<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\RoleResource\Pages;
use App\Models\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
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
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Roles & Permissions';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Role Information')->schema([
                    TextInput::make('name')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('Role name (e.g., Event Manager, Customer Service)'),
                ])->columns(1),

                Section::make('Permissions')->schema([
                    CheckboxList::make('permissions')
                        ->relationship('permissions', 'name')
                        ->options(function () {
                            return Permission::all()->pluck('name', 'id')->toArray();
                        })
                        ->columns(2)
                        ->gridDirection('row')
                        ->searchable()
                        ->descriptions(function () {
                            return Permission::all()
                                ->filter(fn ($permission) => ! empty($permission->description))
                                ->pluck('description', 'name')
                                ->toArray();
                        })
                        ->helperText('Select permissions for this role. Users with this role will have access to the selected features.'),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->permissions->count().' permissions'),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable(),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
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
                    ->modalDescription('Are you sure you want to delete this role? Users with this role will lose their permissions.'),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
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
