<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\DemoRequestResource\Pages;
use App\Filament\SystemAdmin\Resources\DemoRequestResource\RelationManagers;
use App\Models\DemoRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DemoRequestResource extends Resource
{
    protected static ?string $model = DemoRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
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
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),
                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('company')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('job_title')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Select::make('company_size')
                            ->options([
                                '1-10' => '1-10 employees',
                                '11-50' => '11-50 employees',
                                '51-200' => '51-200 employees',
                                '201-500' => '201-500 employees',
                                '501-1000' => '501-1000 employees',
                                '1000+' => '1000+ employees',
                            ])
                            ->required(),
                    ]),
                Forms\Components\Section::make('Event Details')
                    ->schema([
                        Forms\Components\CheckboxList::make('event_types')
                            ->options([
                                'conferences' => 'Conferences',
                                'workshops' => 'Workshops',
                                'seminars' => 'Seminars',
                                'trade-shows' => 'Trade Shows',
                                'networking' => 'Networking Events',
                                'corporate' => 'Corporate Events',
                                'weddings' => 'Weddings',
                                'concerts' => 'Concerts/Entertainment',
                                'sports' => 'Sports Events',
                                'fundraising' => 'Fundraising Events',
                                'festivals' => 'Festivals',
                                'other' => 'Other',
                            ])
                            ->columns(3),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('events_per_year')
                                    ->options([
                                        '1-5' => '1-5 events',
                                        '6-10' => '6-10 events',
                                        '11-25' => '11-25 events',
                                        '26-50' => '26-50 events',
                                        '50+' => '50+ events',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('average_attendees')
                                    ->options([
                                        '1-50' => '1-50 attendees',
                                        '51-100' => '51-100 attendees',
                                        '101-500' => '101-500 attendees',
                                        '501-1000' => '501-1000 attendees',
                                        '1000+' => '1000+ attendees',
                                    ])
                                    ->required(),
                            ]),
                        Forms\Components\TextInput::make('current_solution')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('demo_focus')
                            ->required()
                            ->rows(3),
                        Forms\Components\Select::make('timeline')
                            ->options([
                                'immediate' => 'Immediate (within 1 month)',
                                '1-3months' => '1-3 months',
                                '3-6months' => '3-6 months',
                                '6-12months' => '6-12 months',
                                'exploring' => 'Just exploring options',
                            ])
                            ->required(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('preferred_date'),
                                Forms\Components\Select::make('preferred_time')
                                    ->options([
                                        'morning' => 'Morning (9:00 AM - 12:00 PM)',
                                        'afternoon' => 'Afternoon (12:00 PM - 5:00 PM)',
                                        'evening' => 'Evening (5:00 PM - 7:00 PM)',
                                        'flexible' => 'I\'m flexible',
                                    ]),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('newsletter')
                                    ->label('Newsletter Subscription'),
                                Forms\Components\Toggle::make('terms')
                                    ->label('Agreed to Terms'),
                            ]),
                    ]),
                Forms\Components\Section::make('Admin Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('contacted_at')
                            ->label('Contact Date'),
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
                Tables\Columns\TextColumn::make('company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('timeline')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'immediate' => 'danger',
                        '1-3months' => 'warning',
                        '3-6months' => 'info',
                        '6-12months' => 'success',
                        'exploring' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('event_types_list')
                    ->label('Event Types')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\IconColumn::make('contacted_at')
                    ->label('Contacted')
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
                Tables\Filters\SelectFilter::make('timeline')
                    ->options([
                        'immediate' => 'Immediate (within 1 month)',
                        '1-3months' => '1-3 months',
                        '3-6months' => '3-6 months',
                        '6-12months' => '6-12 months',
                        'exploring' => 'Just exploring options',
                    ]),
                Tables\Filters\Filter::make('urgent')
                    ->query(fn (Builder $query): Builder => $query->where('timeline', 'immediate'))
                    ->label('Urgent (Immediate)'),
                Tables\Filters\Filter::make('uncontacted')
                    ->query(fn (Builder $query): Builder => $query->whereNull('contacted_at'))
                    ->label('Uncontacted'),
                Tables\Filters\Filter::make('contacted')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('contacted_at'))
                    ->label('Contacted'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_contacted')
                    ->label('Mark Contacted')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (DemoRequest $record): bool => $record->contacted_at === null)
                    ->action(fn (DemoRequest $record) => $record->markAsContacted())
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
            'index' => Pages\ListDemoRequests::route('/'),
            'create' => Pages\CreateDemoRequest::route('/create'),
            'view' => Pages\ViewDemoRequest::route('/{record}'),
            'edit' => Pages\EditDemoRequest::route('/{record}/edit'),
        ];
    }
}
