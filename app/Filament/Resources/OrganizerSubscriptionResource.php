<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizerSubscriptionResource\Pages;
use App\Models\OrganizerSubscription;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OrganizerSubscriptionResource extends Resource
{
    protected static ?string $model = OrganizerSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?string $modelLabel = 'Subscription';

    protected static ?string $pluralModelLabel = 'Subscriptions';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereBelongsTo(Filament::getTenant());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('organizer_id')
                    ->default(fn () => Filament::getTenant()?->id),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('subscribed_at')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\SelectFilter::make('organizer')
                //     ->relationship('organizer', 'name')
                //     ->searchable()
                //     ->preload(),
                Tables\Filters\Filter::make('recent_subscriptions')
                    ->query(fn (Builder $query): Builder => $query->where('subscribed_at', '>=', now()->subWeek()))
                    ->label('Recent (Last 7 days)'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export All Subscriptions'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Selected'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('subscribed_at', 'desc');
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
            'index' => Pages\ListOrganizerSubscriptions::route('/'),
            'create' => Pages\CreateOrganizerSubscription::route('/create'),
            'edit' => Pages\EditOrganizerSubscription::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();
        if (!$tenant) {
            return null;
        }
        
        return static::getModel()::where('organizer_id', $tenant->id)->count();
    }
}
