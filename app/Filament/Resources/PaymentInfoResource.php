<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentInfoResource\Pages;
use App\Models\PaymentInfo;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentInfoResource extends Resource
{
    protected static ?string $model = PaymentInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payment Info';

    protected static ?string $modelLabel = 'Payment Info Submission';

    protected static ?string $pluralModelLabel = 'Payment Info Submissions';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Hidden::make('organizer_id')->default(fn () => Filament::getTenant()->id)
                    ->required(),

                Forms\Components\Hidden::make('submitted_by')->default(fn () => auth()->id()),

                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\TextInput::make('business_name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('This will appear on your Paystack subaccount'),

                        Forms\Components\Select::make('payment_channel')
                            ->options([
                                'bank' => 'Bank Account',
                                'mobile_money' => 'Mobile Money',
                            ])
                            ->required()
                            ->reactive()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\Select::make('bank_code')
                            ->label('Bank/Provider')
                            ->options(function (callable $get) {
                                $channel = $get('payment_channel');
                                $banks = [
                                    '190100' => 'Stanbic Bank Ghana',
                                    '030100' => 'Absa Bank Ghana',
                                    '280100' => 'Access Bank Ghana',
                                    '080100' => 'ADB Bank Limited',
                                    '070101' => 'ARB Apex Bank',
                                    '140100' => 'CAL Bank',
                                    '340100' => 'Consolidated Bank Ghana Limited',
                                    '130100' => 'Ecobank Ghana',
                                    '060100' => 'UBA Ghana',
                                    '240100' => 'Fidelity Bank Ghana',
                                    '040100' => 'GCB Bank Limited',
                                    '180100' => 'Prudential Bank',
                                    '110100' => 'Republic Bank Ghana',
                                    '090100' => 'Societe Generale Ghana',
                                    '020100' => 'Standard Chartered Bank Ghana',
                                    '100100' => 'Universal Merchant Bank',
                                    '120100' => 'Zenith Bank Ghana',
                                    '010100' => 'Bank of Ghana',
                                    '230100' => 'GT Bank Ghana',
                                    'MTN' => 'MTN Mobile Money',
                                    'VOD' => 'Vodafone Cash',
                                    'ATL' => 'AirtelTigo Money',
                                    'ZEEPAY' => 'Zeepay',
                                ];

                                if ($channel === 'mobile_money') {
                                    return array_filter($banks, function ($key) {
                                        return in_array($key, ['MTN', 'VOD', 'ATL', 'ZEEPAY']);
                                    }, ARRAY_FILTER_USE_KEY);
                                } else {
                                    return array_filter($banks, function ($key) {
                                        return ! in_array($key, ['MTN', 'VOD', 'ATL', 'ZEEPAY']);
                                    }, ARRAY_FILTER_USE_KEY);
                                }
                            })
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\TextInput::make('account_number')
                            ->label(fn (callable $get) => $get('payment_channel') === 'mobile_money' ? 'Mobile Money Number' : 'Account Number')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->hint('Click on the check icon to very details')
                            ->disabled(fn ($record) => $record !== null)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('verify')
                                    ->label('Verify')
                                    ->icon('heroicon-m-check-circle')
                                    ->color('success')
                                    ->visible(fn ($record) => $record === null)
                                    ->action(function (callable $get, callable $set) {
                                        $accountNumber = $get('account_number');
                                        $bankCode = $get('bank_code');

                                        if (! $accountNumber || ! $bankCode) {
                                            Notification::make()
                                                ->title('Missing Information')
                                                ->body('Please fill in both bank/provider and account number first.')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        try {
                                            $response = Http::withHeaders([
                                                'Authorization' => 'Bearer '.config('services.paystack.secret_key'),
                                                'Content-Type' => 'application/json',
                                            ])->get('https://api.paystack.co/bank/resolve', [
                                                'account_number' => $accountNumber,
                                                'bank_code' => $bankCode,
                                            ]);

                                            if ($response->successful()) {
                                                $data = $response->json();

                                                if ($data['status']) {
                                                    // Set all the verified data
                                                    $set('account_name', $data['data']['account_name']);
                                                    $set('paystack_verified', true);
                                                    $set('verification_response', json_encode($data));

                                                    // Set settlement bank name
                                                    $banks = [
                                                        '190100' => 'Stanbic Bank Ghana',
                                                        '030100' => 'Absa Bank Ghana',
                                                        '280100' => 'Access Bank Ghana',
                                                        '080100' => 'ADB Bank Limited',
                                                        '070101' => 'ARB Apex Bank',
                                                        '140100' => 'CAL Bank',
                                                        '340100' => 'Consolidated Bank Ghana Limited',
                                                        '130100' => 'Ecobank Ghana',
                                                        '060100' => 'UBA Ghana',
                                                        '240100' => 'Fidelity Bank Ghana',
                                                        '040100' => 'GCB Bank Limited',
                                                        '180100' => 'Prudential Bank',
                                                        '110100' => 'Republic Bank Ghana',
                                                        '090100' => 'Societe Generale Ghana',
                                                        '020100' => 'Standard Chartered Bank Ghana',
                                                        '100100' => 'Universal Merchant Bank',
                                                        '120100' => 'Zenith Bank Ghana',
                                                        '010100' => 'Bank of Ghana',
                                                        '230100' => 'GT Bank Ghana',
                                                        'MTN' => 'MTN Mobile Money',
                                                        'VOD' => 'Vodafone Cash',
                                                        'ATL' => 'AirtelTigo Money',
                                                        'ZEEPAY' => 'Zeepay',
                                                    ];
                                                    $set('settlement_bank', $banks[$bankCode] ?? $bankCode);

                                                    Notification::make()
                                                        ->title('Account Verified')
                                                        ->body("Account Name: {$data['data']['account_name']}")
                                                        ->success()
                                                        ->send();
                                                } else {
                                                    Notification::make()
                                                        ->title('Verification Failed')
                                                        ->body('Invalid account number or bank code.')
                                                        ->danger()
                                                        ->send();
                                                }
                                            } else {
                                                Notification::make()
                                                    ->title('Verification Error')
                                                    ->body('Unable to verify account at this time.')
                                                    ->danger()
                                                    ->send();
                                            }
                                        } catch (\Exception $e) {
                                            Log::error('Account verification failed in form', [
                                                'error' => $e->getMessage(),
                                                'account_number' => $accountNumber,
                                                'bank_code' => $bankCode,
                                            ]);

                                            Notification::make()
                                                ->title('Verification Error')
                                                ->body('An error occurred during verification.')
                                                ->danger()
                                                ->send();
                                        }
                                    })
                            ),

                        Forms\Components\TextInput::make('account_name')
                            ->label('Verified Account Name')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (callable $get) => ! empty($get('account_name')))
                            ->suffixIcon('heroicon-m-check-circle')
                            ->suffixIconColor('success'),

                        Forms\Components\TextInput::make('settlement_bank')
                            ->label('Bank/Provider')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (callable $get) => ! empty($get('settlement_bank'))),

                        Forms\Components\Hidden::make('paystack_verified')
                            ->dehydrated()
                            ->default(false),

                        Forms\Components\Hidden::make('verification_response')
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Approval')
                    ->schema([
                        Forms\Components\Placeholder::make('status_display')
                            ->label('Status')
                            ->content(function ($record) {
                                if (! $record) {
                                    return 'Will be set to "Pending Review" after submission';
                                }

                                return match ($record->status) {
                                    'pending' => '🔄 Pending Review',
                                    'approved' => '✅ Approved',
                                    'rejected' => '❌ Rejected',
                                    default => $record->status,
                                };
                            }),

                        Forms\Components\Placeholder::make('approved_info')
                            ->label('Approval Information')
                            ->content(function ($record) {
                                if (! $record || $record->status === 'pending') {
                                    return 'Approval pending by system administrator';
                                }

                                $info = [];
                                if ($record->approved_by) {
                                    $info[] = 'Approved by: '.($record->approvedBy?->name ?? 'User ID '.$record->approved_by);
                                }
                                if ($record->approved_at) {
                                    $info[] = "Date: {$record->approved_at->format('M j, Y H:i')}";
                                }
                                if ($record->rejection_reason) {
                                    $info[] = "Reason: {$record->rejection_reason}";
                                }

                                return empty($info) ? 'No additional information' : implode('<br>', $info);
                            })
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null),

                Forms\Components\Section::make('Balance Information')
                    ->schema([
                        Forms\Components\TextInput::make('subaccount_balance')
                            ->label('Actual Net Balance (GHS)')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? number_format((float) $state, 2) : '0.00')
                            ->prefix('GHS')
                            ->visible(fn ($record) => $record !== null),

                        // Forms\Components\TextInput::make('system_admin_balance')
                        //     ->label('System Fee Paid (GHS)')
                        //     ->disabled()
                        //     ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 2) : '0.00')
                        //     ->prefix('GHS')
                        //     ->visible(fn($record) => $record !== null),

                        Forms\Components\TextInput::make('total_transaction_volume')
                            ->label('Total Transaction Volume (GHS)')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? number_format((float) $state, 2) : '0.00')
                            ->prefix('GHS')
                            ->visible(fn ($record) => $record !== null),

                        // Forms\Components\Placeholder::make('total_balance_display')
                        //     ->label('Total Balance')
                        //     ->content(fn($record) => $record ? 'GHS ' . number_format((float)$record->subaccount_balance + (float)$record->system_admin_balance, 2) : 'GHS 0.00')
                        //     ->visible(fn($record) => $record !== null),

                        Forms\Components\TextInput::make('total_transactions')
                            ->label('Total Transactions')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\TextInput::make('successful_transactions')
                            ->label('Successful Transactions')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\DateTimePicker::make('subaccount_balance_last_synced')
                            ->label('Last Balance Sync')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\DateTimePicker::make('last_transaction_at')
                            ->label('Last Transaction')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null && $record->paystack_subaccount_code),

                Forms\Components\Section::make('Verification & Paystack')
                    ->schema([
                        Forms\Components\Toggle::make('paystack_verified')
                            ->label('Account Details Verified')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\Toggle::make('is_verified')
                            ->label('System Verified')
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\TextInput::make('paystack_subaccount_code')
                            ->label('Subaccount Code')
                            ->maxLength(255)
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->disabled()
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\Placeholder::make('verification_notice')
                            ->label('')
                            ->content('⚠️ Please verify your account details using the "Verify" button above before submitting.')
                            ->visible(fn ($record, callable $get) => $record === null && empty($get('account_name'))),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),

                Forms\Components\Section::make('Account Sync Status')
                    ->schema([
                        Forms\Components\Placeholder::make('sync_error_alert')
                            ->label('')
                            ->content(fn (?PaymentInfo $record) => '⚠️ Sync Error: '.($record?->getLastSyncError() ?? '').
                                '<br><small>Please contact admin to resolve this.</small>'
                            )
                            ->visible(fn (?PaymentInfo $record) => $record && $record->hasRecentSyncError()),

                        Forms\Components\Placeholder::make('sync_success_info')
                            ->label('')
                            ->content('✅ PaymentInfo is synced with Paystack. Any changes to business name, bank account, or percentage charge will be automatically synchronized.')
                            ->visible(fn (?PaymentInfo $record) => $record && $record->paystack_subaccount_code && ! $record->hasRecentSyncError()),

                        Forms\Components\Placeholder::make('no_subaccount_info')
                            ->label('')
                            ->content('ℹ️ Paystack subaccount will be created when this PaymentInfo is approved.')
                            ->visible(fn (?PaymentInfo $record) => $record && ! $record->paystack_subaccount_code),
                    ])
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('submittedBy.name')
                    ->label('Submitted By')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('business_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account_number')
                    ->label('Account No.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_name')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('settlement_bank')
                    ->label('Bank')
                    ->searchable()
                    ->limit(15),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_channel')
                    ->colors([
                        'primary' => 'bank',
                        'secondary' => 'mobile_money',
                    ]),

                Tables\Columns\IconColumn::make('paystack_verified')
                    ->label('Paystack Verified')
                    ->boolean(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('subaccount_balance')
                    ->label('Subaccount Balance')
                    ->formatStateUsing(fn ($state) => 'GHS '.number_format((float) $state, 2))
                    ->sortable(),

                // Tables\Columns\TextColumn::make('system_admin_balance')
                //     ->label('Admin Balance')
                //     ->formatStateUsing(fn ($state) => 'GHS '.number_format((float) $state, 2))
                //     ->sortable(),

                Tables\Columns\TextColumn::make('total_transactions')
                    ->label('Transactions')
                    ->sortable(),

                Tables\Columns\IconColumn::make('sync_error_indicator')
                    ->label('Sync Status')
                    ->getStateUsing(fn (?PaymentInfo $record) => $record && ! $record->hasRecentSyncError())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn (?PaymentInfo $record) => $record && $record->hasRecentSyncError()
                        ? 'Recent sync error: '.$record->getLastSyncError()
                        : 'No recent sync errors')
                    ->visible(fn (?PaymentInfo $record) => $record && $record->paystack_subaccount_code),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('payment_channel')
                    ->options([
                        'bank' => 'Bank Account',
                        'mobile_money' => 'Mobile Money',
                    ]),

                Tables\Filters\TernaryFilter::make('paystack_verified')
                    ->label('Paystack Verified'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Payment Info'),
            ])
            ->actions([

                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Selected'),
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Payment Information')
                        ->modalDescription('This will permanently delete your payment information and remove the associated Paystack subaccount. You will no longer be able to receive payments. Are you sure you want to continue?')
                        ->action(function ($records) {
                            $deletedCount = 0;
                            $subaccountsRemoved = 0;

                            foreach ($records as $record) {
                                if ($record->paystack_subaccount_code) {
                                    $subaccountsRemoved++;
                                }
                                $record->delete(); // This triggers Paystack deletion via observer
                                $deletedCount++;
                            }

                            Notification::make()
                                ->title('Payment Information Deleted')
                                ->body("Deleted {$deletedCount} payment info record(s)" .
                                       ($subaccountsRemoved > 0 ? " and removed {$subaccountsRemoved} Paystack subaccount(s)." : "."))
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListPaymentInfos::route('/'),
            'create' => Pages\CreatePaymentInfo::route('/create'),
            'view' => Pages\ViewPaymentInfo::route('/{record}'),
            'edit' => Pages\EditPaymentInfo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['organizer', 'approvedBy', 'submittedBy']);
    }
}
