<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'All Users';

    protected static ?int $navigationSort = 1;

public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->whereNot('email', 'admin@suptix.live');
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('password')
                        ->password()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->revealable()
                        ->minLength(8)
                        ->helperText('Minimum 8 characters. Leave blank to keep current password when editing.'),
                ])->columns(2),

                Section::make('Organization Access')->schema([
                    Select::make('organizers')
                        ->relationship('organizers', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText('Select which organizations this user can access'),
                ])->columns(1),

                Section::make('Role Assignment')->schema([
                    Select::make('roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText('Assign system-wide roles to this user'),
                ])->columns(1),

                Section::make('Access Settings')->schema([
                    Toggle::make('force_renew_password')
                        ->label('Force Password Reset on First Login')
                        ->default(true)
                        ->helperText('User will be required to change password on first login'),
                    Toggle::make('is_system_admin')
                        ->label('System Administrator')
                        ->helperText('Grant full system administration access'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('organizers.name')
                    ->label('Organizations')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),
                IconColumn::make('is_system_admin')
                    ->label('System Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                IconColumn::make('force_renew_password')
                    ->label('Password Reset Required')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organizers')
                    ->relationship('organizers', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
                // SelectFilter::make('is_system_admin')->options([
                //     'true' => 'Yes',
                //     'false' => 'No',
                // ])
                // ->query(fn (Builder $query) => $query->where('is_system_admin', '=', true))
                // ->default(fn (Builder $query, Get $get) => $query->where('is_system_admin', '=', $get('is_system_admin'))),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
