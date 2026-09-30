<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\NewsletterResource\Pages;
use App\Filament\SystemAdmin\Resources\NewsletterResource\RelationManagers;
use App\Models\Newsletter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class NewsletterResource extends Resource
{
    protected static ?string $model = Newsletter::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    
    protected static ?string $navigationGroup = 'Communications';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscription Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('subscribed_at')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('unsubscribed_at')
                            ->label('Unsubscribed Date'),
                    ]),
                Forms\Components\Section::make('Technical Information')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->maxLength(45),
                        Forms\Components\Textarea::make('user_agent')
                            ->rows(2),
                        Forms\Components\TextInput::make('unsubscribe_token')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Admin Information')
                    ->schema([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('unsubscribed_at')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn (Newsletter $record): bool => $record->isActive()),
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->active())
                    ->label('Active Subscribers'),
                Tables\Filters\Filter::make('unsubscribed')
                    ->query(fn (Builder $query): Builder => $query->unsubscribed())
                    ->label('Unsubscribed'),
                Tables\Filters\Filter::make('recent')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label('Last 30 Days'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('unsubscribe')
                    ->label('Unsubscribe')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Newsletter $record): bool => $record->isActive())
                    ->action(fn (Newsletter $record) => $record->unsubscribe())
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('resubscribe')
                    ->label('Resubscribe')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Newsletter $record): bool => !$record->isActive())
                    ->action(fn (Newsletter $record) => $record->resubscribe())
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_unsubscribe')
                        ->label('Unsubscribe Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each(fn (Newsletter $record) => $record->unsubscribe());
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListNewsletters::route('/'),
            'create' => Pages\CreateNewsletter::route('/create'),
            'view' => Pages\ViewNewsletter::route('/{record}'),
            'edit' => Pages\EditNewsletter::route('/{record}/edit'),
        ];
    }
}
