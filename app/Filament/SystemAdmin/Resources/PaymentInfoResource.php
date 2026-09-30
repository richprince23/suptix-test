<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\PaymentInfoResource\Pages;
use App\Models\PaymentInfo;
use App\Services\PaystackBalanceService;
use App\Services\PaystackService;
use App\Services\PaystackSubaccountService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action as TableAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentInfoResource extends Resource
{
    protected static ?string $model = PaymentInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Sales & Commerce';

    protected static ?string $navigationLabel = 'Payment Accounts';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Organization')->schema([
                    Select::make('organizer_id')
                        ->relationship('organizer', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('Select the organization this payment account belongs to'),
                ])->columns(1),

                // Section::make('Payment Channel')->schema([
                // Select::make('payment_channel')
                //     ->options([
                //         'bank' => 'Bank Account',
                //         'mobile_money' => 'Mobile Money',
                //     ])
                //     ->required()
                //     ->live()
                //     ->default('bank')
                //     ->helperText('Choose the payment method for this account'),
                // ])->columns(1),

                Section::make('Account Information')->schema([
                    TextInput::make('business_name')
                        ->required()
                        ->maxLength(255)
                        ->helperText('The name of the business/account holder'),
                    Select::make('payment_channel')
                        ->options([
                            'bank' => 'Bank Account',
                            'mobile_money' => 'Mobile Money',
                        ])
                        ->required()
                        ->live()
                        ->default('bank')
                        ->helperText('Choose the payment method for this account'),
                    Select::make('bank_code')
                        ->label('Bank/Mobile Money Provider')
                        ->options(function (Get $get) {
                            $channel = $get('payment_channel');

                            return $channel === 'mobile_money'
                                ? self::getMobileMoneyOptions()
                                : self::getBankOptions();
                        })
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, Get $get) {
                            if ($state) {
                                $channel = $get('payment_channel');
                                $options = $channel === 'mobile_money'
                                    ? self::getMobileMoneyOptions()
                                    : self::getBankOptions();
                                $set('settlement_bank', $options[$state] ?? '');
                            }
                        }),

                    TextInput::make('settlement_bank')
                        ->label('Settlement Bank/Provider')
                        ->required()
                        ->hidden()->reactive()
                        ->default(function (Get $get) {
                            $channel = $get('payment_channel');
                            $bankCode = $get('bank_code');
                            if ($bankCode) {
                                $options = $channel === 'mobile_money'
                                    ? self::getMobileMoneyOptions()
                                    : self::getBankOptions();
                                dd($options[$bankCode] ?? '');
                                Log::info($options[$bankCode]);

                                return $options[$bankCode] ?? '';
                            }

                            return '';
                        }),

                    TextInput::make('account_number')
                        ->label(function (Get $get) {
                            return $get('payment_channel') === 'mobile_money'
                                ? 'Mobile Money Number'
                                : 'Account Number';
                        })
                        ->required()
                        ->helperText(function (Get $get) {
                            return $get('payment_channel') === 'mobile_money'
                                ? 'Enter the mobile money number (e.g., 0241234567)'
                                : 'Enter the 10-digit bank account number';
                        })->suffix('Verify')
                        ->live(onBlur: true)
                        ->suffixAction(
                            Action::make('verify_account')
                                ->icon('heroicon-m-check-circle')
                                ->label('Verify')
                                ->color('success')
                                // ->visible(fn (Get $get) => $get('payment_channel') === 'bank')
                                ->action(function (Get $get, $set) {
                                    $accountNumber = $get('account_number');
                                    $bankCode = $get('bank_code');

                                    if (!$accountNumber || !$bankCode) {
                                        Notification::make()
                                            ->title('Missing Information')
                                            ->body('Please select a bank and enter account number first')
                                            ->warning()
                                            ->send();

                                        return;
                                    }

                                    $paystackService = app(PaystackService::class);
                                    $result = $paystackService->verifyBankAccount($accountNumber, $bankCode);

                                    if ($result['success']) {
                                        $set('business_name', $result['account_name']);
                                        Notification::make()
                                            ->title('Account Verified')
                                            ->body("Account belongs to: {$result['account_name']}")
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Verification Failed')
                                            ->body($result['message'])
                                            ->danger()
                                            ->send();
                                    }
                                })
                        ),

                ])->columns(2),

                // settlement
                Section::make('Settlement Configuration')->schema([
                    TextInput::make('percentage_charge')
                        ->label('Transaction Fee (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.50)
                        ->default(7.00)
                        ->suffix('%')
                        ->helperText('Percentage fee to charge on transactions (0-100%)'),

                    Select::make('transaction_fee_payer')
                        ->label('Transaction Fee Payer')
                        ->options([
                            'subaccount' => 'Organizer (Subaccount)',
                            'account' => 'Main Account',
                        ])
                        ->default('account')
                        ->required()
                        ->helperText('Choose who pays the transaction processing fees'),

                    Select::make('settlement_schedule')
                        ->label('Settlement Schedule')
                        ->options([
                            'auto' => 'Automatic (T+1)',
                            'weekly' => 'Weekly',
                            'monthly' => 'Monthly',
                        ])
                        ->default('auto')
                        ->required(),

                    TextInput::make('minimum_settlement_amount')
                        ->label('Minimum Settlement Amount')
                        ->numeric()
                        ->prefix('GHS')
                        ->default(0.00)
                        ->helperText('Minimum amount before settlement is triggered'),
                ])->columns(2),

                // balance info
                Section::make('Balance Information')
                    ->schema([
                        TextInput::make('subaccount_balance')
                            ->label('Actual Net Balance (GHS)')
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? number_format((float) $state, 2) : '0.00')
                            ->prefix('GHS')
                            ->visible(fn($record) => $record !== null),

                        TextInput::make('system_admin_balance')
                            ->label('System Fee Paid (GHS)')
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 2) : '0.00')
                            ->prefix('GHS')
                            ->visible(fn($record) => $record !== null),

                        TextInput::make('total_transaction_volume')
                            ->label('Total Transaction Volume (GHS)')
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? number_format((float) $state, 2) : '0.00')
                            ->prefix('GHS')
                            ->visible(fn($record) => $record !== null),

                        // Forms\Components\Placeholder::make('total_balance_display')
                        //     ->label('Total Balance')
                        //     ->content(fn($record) => $record ? 'GHS ' . number_format((float)$record->subaccount_balance + (float)$record->system_admin_balance, 2) : 'GHS 0.00')
                        //     ->visible(fn($record) => $record !== null),

                        TextInput::make('total_transactions')
                            ->label('Total Transactions')
                            ->disabled()
                            ->visible(fn($record) => $record !== null),

                        TextInput::make('successful_transactions')
                            ->label('Successful Transactions')
                            ->disabled()
                            ->visible(fn($record) => $record !== null),

                        DateTimePicker::make('subaccount_balance_last_synced')
                            ->label('Last Balance Sync')
                            ->disabled()
                            ->visible(fn($record) => $record !== null),

                        DateTimePicker::make('last_transaction_at')
                            ->label('Last Transaction')
                            ->disabled()
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->columns(3)
                    ->visible(fn($record) => $record !== null && $record->paystack_subaccount_code),

                Section::make('Verification & Paystack')
                    ->schema([
                        Toggle::make('paystack_verified')
                            ->label('Account Details Verified')
                            ->disabled()
                            ->visible(fn($record) => $record !== null),

                        Toggle::make('is_verified')
                            ->label('System Verified')
                            ->visible(fn($record) => $record !== null),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->visible(fn($record) => $record !== null),

                        TextInput::make('paystack_subaccount_code')
                            ->label('Subaccount Code')
                            ->maxLength(255)
                            ->disabled()
                            ->visible(fn($record) => $record !== null),

                        DateTimePicker::make('submitted_at')
                            ->disabled()
                            ->visible(fn($record) => $record !== null),

                        DateTimePicker::make('verified_at')
                            ->disabled()
                            ->visible(fn($record) => $record !== null),

                        Placeholder::make('verification_notice')
                            ->label('')
                            ->content('⚠️ Please verify your account details using the "Verify" button above before submitting.')
                            ->visible(fn($record, callable $get) => $record === null && empty($get('account_name'))),
                    ])
                    ->columns(3)
                    ->visible(fn($record) => $record !== null),

                // account sync
                Section::make('Account Sync Status')
                    ->schema([
                        Placeholder::make('sync_error_alert')
                            ->label('')
                            ->content(
                                fn(?PaymentInfo $record) => '⚠️ Sync Error: ' . ($record?->getLastSyncError() ?? '') .
                                '<br><small>Please contact admin to resolve this.</small>'
                            )
                            ->visible(fn(?PaymentInfo $record) => $record && $record->hasRecentSyncError()),

                        Placeholder::make('sync_success_info')
                            ->label('')
                            ->content('✅ PaymentInfo is synced with Paystack. Any changes to business name, bank account, or percentage charge will be automatically synchronized.')
                            ->visible(fn(?PaymentInfo $record) => $record && $record->paystack_subaccount_code && !$record->hasRecentSyncError()),

                        Placeholder::make('no_subaccount_info')
                            ->label('')
                            ->content('ℹ️ Paystack subaccount will be created when this PaymentInfo is approved.')
                            ->visible(fn(?PaymentInfo $record) => $record && !$record->paystack_subaccount_code),
                    ])
                    ->visible(fn($record) => $record !== null),
                Section::make('Account Status')->schema([
                    Placeholder::make('status_display')
                        ->label('Current Status')
                        ->content(function ($record) {
                            if (!$record) {
                                return 'New Account';
                            }

                            return match ($record->status) {
                                'active' => 'Active - Ready for payments',
                                'pending_verification' => 'Pending Verification',
                                'pending_creation' => 'Pending Creation',
                                'inactive' => 'Inactive',
                                default => 'Unknown',
                            };
                        }),

                    Toggle::make('is_active')
                        ->label('Account Active')
                        ->default(true)
                        ->helperText('Activate or deactivate payment processing'),
                ])->columns(2)->visibleOn('edit'),

                Section::make('Balance Information')->schema([
                    Placeholder::make('available_balance_display')
                        ->label('Available Balance')
                        ->content(fn($record) => $record ? $record->formatted_available_balance : 'GHS 0.00'),

                    Placeholder::make('ledger_balance_display')
                        ->label('Ledger Balance')
                        ->content(fn($record) => $record ? $record->formatted_ledger_balance : 'GHS 0.00'),

                    Placeholder::make('last_updated_display')
                        ->label('Last Updated')
                        ->content(fn($record) => $record && $record->balance_last_updated
                            ? $record->balance_last_updated->diffForHumans()
                            : 'Never'),
                ])->columns(3)->visibleOn('edit'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organizer.name')
                    ->label('Organization')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('business_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_channel')
                    ->label('Channel')
                    ->badge()
                    ->colors([
                        'primary' => 'bank',
                        'success' => 'mobile_money',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'bank' => 'Bank',
                            'mobile_money' => 'Mobile Money',
                            default => $state,
                        };
                    }),

                TextColumn::make('settlement_bank')
                    ->label('Provider')
                    ->searchable(),

                TextColumn::make('account_number')
                    ->label('Account/Number')
                    ->formatStateUsing(fn($state) => '***' . substr($state, -4)),

                TextColumn::make('transaction_fee_payer')
                    ->label('Fee Payer')
                    ->badge()
                    ->colors([
                        'primary' => 'subaccount',
                        'warning' => 'account',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'subaccount' => 'Organizer',
                            'account' => 'Main Account',
                            default => $state,
                        };
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending_verification',
                        'info' => 'pending_creation',
                        'danger' => 'inactive',
                    ]),

                TextColumn::make('available_balance')
                    ->label('Available Balance')
                    ->money('GHS')
                    ->sortable(),

                TextColumn::make('balance_last_updated')
                    ->label('Last Sync')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                IconColumn::make('is_verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('sync_error_indicator')
                    ->label('Sync Status')
                    ->getStateUsing(fn(?PaymentInfo $record) => $record && !$record->hasRecentSyncError())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn(?PaymentInfo $record) => $record && $record->hasRecentSyncError()
                        ? 'Recent sync error: ' . $record->getLastSyncError()
                        : 'No recent sync errors')
                    ->visible(fn(?PaymentInfo $record) => $record && $record->paystack_subaccount_code),
            ])
            ->actions([
                TableAction::make('sync_balance')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (PaymentInfo $record) {
                        $paystackService = app(PaystackService::class);
                        $synced = $paystackService->syncSubaccountBalance($record);

                        if ($synced) {
                            Notification::make()
                                ->title('Balance Synced')
                                ->body('Account balance has been updated')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Sync Failed')
                                ->body('Could not sync account balance')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(PaymentInfo $record) => $record->paystack_subaccount_code),

                TableAction::make('create_subaccount')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->action(function (PaymentInfo $record) {
                        $paystackService = app(PaystackService::class);
                        $result = $paystackService->createSubaccount($record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Subaccount Created')
                                ->body('Paystack subaccount has been created successfully')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Creation Failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(PaymentInfo $record) => !$record->paystack_subaccount_code),

                TableAction::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(PaymentInfo $record) => $record->status === 'pending')
                    ->action(function (PaymentInfo $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        // Create Paystack subaccount
                        static::createPaystackSubaccount($record);

                        // Assign organizer admin role to the user who submitted
                        static::assignOrganizerAdminRole($record);

                        Notification::make()
                            ->title('Payment Info Approved')
                            ->body('User has been granted organizer admin access')
                            ->success()
                            ->send();
                    }),

                TableAction::make('sync_with_paystack')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->label('Sync with Paystack')
                    ->requiresConfirmation()
                    ->modalHeading('Sync PaymentInfo with Paystack')
                    ->modalDescription('This will update the Paystack subaccount with any changes made to business name, bank account, or percentage charge.')
                    ->visible(fn(PaymentInfo $record) => $record->status === 'approved' && $record->paystack_subaccount_code)
                    ->action(function (PaymentInfo $record) {
                        try {
                            $record->manualSyncWithPaystack();

                            // Clear any previous sync errors
                            $record->clearSyncError();

                            Notification::make()
                                ->title('Subaccount Synced Successfully')
                                ->body('PaymentInfo has been successfully updated on Paystack.')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            $paystackService = app(PaystackSubaccountService::class);
                            $userMessage = $paystackService->getValidationMessage($e);

                            Notification::make()
                                ->title('Sync Failed')
                                ->body($userMessage)
                                ->danger()
                                ->duration(10000)
                                ->send();
                        }
                    }),

                TableAction::make('sync_balances')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->label('Sync Balances')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Balances from Paystack')
                    ->modalDescription('This will fetch the latest transaction data from Paystack and update the separate balances for subaccount and system admin.')
                    ->visible(fn(PaymentInfo $record) => $record->status === 'approved' && $record->paystack_subaccount_code)
                    ->action(function (PaymentInfo $record) {
                        try {
                            $balanceService = new PaystackBalanceService;
                            $balanceData = $balanceService->syncSubaccountBalances($record);

                            Notification::make()
                                ->title('Balances Synced Successfully')
                                ->body(sprintf(
                                    'Subaccount: GHS %s | System Admin: GHS %s | Total Volume: GHS %s | Split Coverage: %s%%',
                                    number_format($balanceData['subaccount_balance'], 2),
                                    number_format($balanceData['system_admin_balance'], 2),
                                    number_format($balanceData['total_transaction_volume'], 2),
                                    $balanceData['split_data_coverage'] ?? 0
                                ))
                                ->success()
                                ->duration(15000)
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Balance Sync Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Archive Payment Information')
                    ->modalDescription('This will archive the payment information and remove the associated Paystack subaccount. Financial data will be preserved for reporting, but the organizer will no longer be able to receive payments through this account.')
                    ->action(function (PaymentInfo $record) {
                        $subaccountCode = $record->paystack_subaccount_code;
                        $businessName = $record->business_name;

                        // Soft delete the record (this will trigger Paystack deletion via model observer)
                        $record->delete();

                        Notification::make()
                            ->title('Payment Information Archived')
                            ->body("Payment info for '{$businessName}' has been archived" .
                                ($subaccountCode ? " and Paystack subaccount '{$subaccountCode}' has been removed." : "."))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Archive Selected Payment Information')
                        ->modalDescription('This will archive the selected payment information and remove associated Paystack subaccounts. Financial data will be preserved for reporting.')
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
                                ->title('Payment Information Archived')
                                ->body("Archived {$deletedCount} payment info records" .
                                    ($subaccountsRemoved > 0 ? " and removed {$subaccountsRemoved} Paystack subaccounts." : "."))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                    ]),
                    // Soft delete filter: show/hide deleted records
                    TernaryFilter::make('deleted_at')
                        ->label('Show Deleted')
                        ->trueLabel('Only Deleted')
                        ->falseLabel('Only Active')
                        ->native(false)
                        ->queries(
                            true: fn ($query) => $query->onlyTrashed(),
                            false: fn ($query) => $query->withoutTrashed(),
                            blank: fn ($query) => $query,
                        ),
            ]) ->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ]);
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

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }

    private static function getBankOptions(): array
    {
        // Common Ghanaian banks - in production, fetch from Paystack API
        return [
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

        ];
    }

    private static function getMobileMoneyOptions(): array
    {
        // Major mobile money providers in Ghana
        return [
            'MTN' => 'MTN Mobile Money',
            'VOD' => 'Vodafone Cash',
            'ATL' => 'AirtelTigo Money',
            'ZEEPAY' => 'Zeepay',
        ];
    }

    public static function createPaystackSubaccount(PaymentInfo $paymentInfo): void
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/subaccount', [
                        'business_name' => $paymentInfo->business_name,
                        'settlement_bank' => $paymentInfo->bank_code,
                        'account_number' => $paymentInfo->account_number,
                        'percentage_charge' => $paymentInfo->percentage_charge, // 7% charge
                        'description' => "Subaccount for {$paymentInfo->organizer->name}",
                        'primary_contact_email' => $paymentInfo->organizer->email ?? 'support@suptix.live',
                        'primary_contact_name' => $paymentInfo->organizer->name,
                        'primary_contact_phone' => $paymentInfo->organizer->phone ?? '',
                        'metadata' => [
                            'organizer_id' => $paymentInfo->organizer_id,
                            'payment_info_id' => $paymentInfo->id,
                        ],
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status']) {
                    $paymentInfo->update([
                        'paystack_subaccount_code' => $data['data']['subaccount_code'],
                        'subaccount_created_at' => now(),
                        'paystack_response' => $data,
                        'is_verified' => true,
                        'verified_at' => now(),
                    ]);

                    Log::info('Paystack subaccount created successfully', [
                        'subaccount_code' => $data['data']['subaccount_code'],
                        'payment_info_id' => $paymentInfo->id,
                    ]);

                    Notification::make()
                        ->title('Paystack Subaccount Created')
                        ->body("Subaccount code: {$data['data']['subaccount_code']}")
                        ->success()
                        ->send();
                }
            } else {
                Log::error('Failed to create Paystack subaccount', [
                    'response' => $response->body(),
                    'payment_info_id' => $paymentInfo->id,
                ]);

                Notification::make()
                    ->title('Failed to Create Paystack Subaccount')
                    ->body('Please try again or create manually.')
                    ->danger()
                    ->send();
            }

        } catch (\Exception $e) {
            Log::error('Exception creating Paystack subaccount', [
                'error' => $e->getMessage(),
                'payment_info_id' => $paymentInfo->id,
            ]);

            Notification::make()
                ->title('Error Creating Subaccount')
                ->body('An error occurred. Please check logs.')
                ->danger()
                ->send();
        }
    }

    public static function assignOrganizerAdminRole(PaymentInfo $paymentInfo): void
    {
        try {
            $organizer = $paymentInfo->organizer;
            $submittedByUser = $paymentInfo->submittedBy;

            if (!$organizer) {
                Log::error('No organizer found for payment info', [
                    'payment_info_id' => $paymentInfo->id,
                ]);

                return;
            }

            // Check if role exists, create if not
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Organizer Admin']);

            // Get all users associated with this organizer
            $allOrganizerUsers = $organizer->users;

            // Check if there's already an organizer admin
            $hasOrganizerAdmin = $allOrganizerUsers->filter(function ($user) {
                return $user->hasRole('Organizer Admin');
            })->count() > 0;

            $usersAssigned = 0;
            $shouldAssignRole = false;

            // Logic: Only assign organizer admin role if:
            // 1. There's only one user in the organization, OR
            // 2. There is no existing organizer admin
            if ($allOrganizerUsers->count() === 1 || !$hasOrganizerAdmin) {
                $shouldAssignRole = true;
            }

            if (!$shouldAssignRole) {
                Notification::make()
                    ->title('Role Assignment Skipped')
                    ->body('Organization already has an admin or multiple users. No role changes made.')
                    ->info()
                    ->send();

                return;
            }

            // Assign role to the user who submitted the payment info
            if ($submittedByUser && !$submittedByUser->hasRole('Organizer Admin')) {
                $submittedByUser->assignRole('Organizer Admin');

                // Also make sure the user is associated with the organizer
                if (!$organizer->users()->where('user_id', $submittedByUser->id)->exists()) {
                    $organizer->users()->attach($submittedByUser->id);
                }

                $usersAssigned++;

                Log::info('Assigned organizer admin role to submitter', [
                    'user_id' => $submittedByUser->id,
                    'user_email' => $submittedByUser->email,
                    'organizer_id' => $organizer->id,
                    'payment_info_id' => $paymentInfo->id,
                    'reason' => $allOrganizerUsers->count() === 1 ? 'single_user' : 'no_existing_admin',
                ]);
            }

            if ($usersAssigned > 0) {
                $reason = $allOrganizerUsers->count() === 1
                    ? 'as the only user in the organization'
                    : 'as no organizer admin existed';

                Notification::make()
                    ->title('Admin Role Assigned')
                    ->body("Assigned organizer admin role to submitter {$reason}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Role Assignment')
                    ->body('User already has organizer admin role')
                    ->warning()
                    ->send();
            }

        } catch (\Exception $e) {
            Log::error('Failed to assign organizer admin role', [
                'error' => $e->getMessage(),
                'payment_info_id' => $paymentInfo->id,
            ]);

            Notification::make()
                ->title('Role Assignment Failed')
                ->body('Failed to assign organizer admin role. Check logs.')
                ->danger()
                ->send();
        }
    }
}
