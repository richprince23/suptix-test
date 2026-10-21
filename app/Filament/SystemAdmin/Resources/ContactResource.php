<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\ContactResource\Pages;
use App\Filament\SystemAdmin\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Select::make('subject')
                            ->options([
                                'general' => 'General Inquiry',
                                'support' => 'Technical Support',
                                'billing' => 'Billing Question',
                                'refund' => 'Refund Request',
                                'organizer' => 'Event Organizer Support',
                                'partnership' => 'Partnership Opportunity',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->rows(4),
                        Forms\Components\Toggle::make('newsletter')
                            ->label('Subscribed to Newsletter'),
                    ]),
                Forms\Components\Section::make('Admin Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('responded_at')
                            ->label('Response Date'),
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
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('subject')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'support' => 'warning',
                        'billing' => 'info',
                        'refund' => 'danger',
                        'organizer' => 'success',
                        'partnership' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\IconColumn::make('responded_at')
                    ->label('Responded')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->options([
                        'general' => 'General Inquiry',
                        'support' => 'Technical Support',
                        'billing' => 'Billing Question',
                        'refund' => 'Refund Request',
                        'organizer' => 'Event Organizer Support',
                        'partnership' => 'Partnership Opportunity',
                        'other' => 'Other',
                    ]),
                Tables\Filters\Filter::make('unresponded')
                    ->query(fn (Builder $query): Builder => $query->whereNull('responded_at'))
                    ->label('Unresponded'),
                Tables\Filters\Filter::make('responded')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('responded_at'))
                    ->label('Responded'),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('mark_responded')
                    ->label('Mark Responded')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Contact $record): bool => $record->responded_at === null)
                    ->action(fn (Contact $record) => $record->markAsResponded())
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'view' => Pages\ViewContact::route('/{record}'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
