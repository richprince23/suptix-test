<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            @if (!$hasPaymentInfo)
                <div class="flex flex-col md:flex-row items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex flex-col md:flex-row items-center space-x-3">
                        <div class="flex-shrink-0 mx-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <div class="flex-1 ml-4 space-3">
                            <h3 class="text-sm font-medium text-yellow-800">Payment Setup Required</h3>
                            <p class="text-sm text-yellow-700">Submit your payment information to start receiving
                                payments for your events.</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ \App\Filament\Resources\PaymentInfoResource::getUrl('create') }}"
                            class="inline-flex items-center px-3 py-2 border border-border text-sm leading-4 font-medium rounded-md text-primary bg-primary">
                            Submit Payment Info
                        </a>
                        {{-- <x-filament::link :href="route('filament.admin.resources.payment-infos.create', \Filament\Facades\Filament::getTenant())">
                            Setup
                        </x-filament::link> --}}
                    </div>
                </div>
            @elseif($isPending)
                <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Payment Info Under Review</h3>
                            <p class="text-sm text-blue-700">Your payment information is being reviewed by our admin
                                team.</p>
                            <p class="text-xs text-blue-600 mt-1">Business: {{ $paymentInfo->business_name }} | Account:
                                {{ $paymentInfo->account_number }}</p>
                        </div>
                    </div>
                </div>
            @elseif($isApproved)
                <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-green-800">Payment Setup Complete</h3>
                            <p class="text-sm text-green-700">Your payment account is active. You can now receive
                                payments for your events.</p>
                            <p class="text-xs text-green-600 mt-1">Account: {{ $paymentInfo->account_name }}
                                ({{ $paymentInfo->settlement_bank }})</p>

                            @if ($hasTransactions)
                                <div class="mt-3 space-y-2">
                                    <!-- Balance Information -->
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="bg-green-100 rounded px-2 py-1.5">
                                            <span class="font-medium text-green-800">Your Balance:</span>
                                            <div class="text-green-700 font-semibold">
                                                {{ $paymentInfo->formatted_subaccount_balance }}</div>
                                        </div>
                                        <div class="bg-orange-100 rounded px-2 py-1.5">
                                            <span class="font-medium text-orange-800">Platform Fees:</span>
                                            <div class="text-orange-700 font-semibold">
                                                {{ $paymentInfo->formatted_system_admin_balance }}</div>
                                        </div>
                                    </div>

                                    <!-- Transaction Statistics -->
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="bg-blue-100 rounded px-2 py-1.5">
                                            <span class="font-medium text-blue-800">Total Volume:</span>
                                            <div class="text-blue-700 font-semibold">
                                                {{ $paymentInfo->formatted_total_transaction_volume }}</div>
                                        </div>
                                        <div class="bg-purple-100 rounded px-2 py-1.5">
                                            <span class="font-medium text-purple-800">Transactions:</span>
                                            <div class="text-purple-700 font-semibold">
                                                {{ $paymentInfo->successful_transactions }}/{{ $paymentInfo->total_transactions }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Last Transaction -->
                                    @if ($paymentInfo->last_transaction_at)
                                        <div class="bg-gray-100 rounded px-2 py-1.5 text-xs">
                                            <span class="font-medium text-gray-800">Last Transaction:</span>
                                            <span
                                                class="text-gray-700">{{ $paymentInfo->last_transaction_at->format('M j, Y') }}</span>
                                            @if ($paymentInfo->last_transaction_amount)
                                                <span class="text-gray-600">(GHS
                                                    {{ number_format($paymentInfo->last_transaction_amount, 2) }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @elseif($isFullySetup)
                                <div class="mt-2 text-xs text-green-600">
                                    <div class="bg-green-50 rounded px-2 py-1.5">
                                        <span class="font-medium">Ready to receive payments!</span>
                                        <div class="text-green-500 text-xs mt-1">No transactions yet</div>
                                    </div>
                                </div>
                            @endif

                            @if ($needsSync && $isApproved)
                                <div class="mt-2 text-xs">
                                    <div class="bg-yellow-50 border border-yellow-200 rounded px-2 py-1.5">
                                        <span class="text-yellow-800 font-medium">⚠️ Balance sync recommended</span>
                                        @if ($lastSyncAgo)
                                            <div class="text-yellow-700 text-xs">Last synced {{ $lastSyncAgo }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 space-y-2">
                        <div class="flex flex-col items-end space-y-2">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $isFullySetup ? 'Active' : 'Approved' }}
                            </span>

                            @if ($hasEarnings)
                                <div class="text-right">
                                    <div class="text-lg font-bold text-green-600">
                                        {{ $paymentInfo->formatted_subaccount_balance }}</div>
                                    <div class="text-xs text-gray-500">Available Balance</div>
                                </div>
                            @endif

                            @if ($hasTransactions && $paymentInfo->total_transactions > 0)
                                <div class="text-right text-xs text-gray-500">
                                    <div>{{ $paymentInfo->total_transactions }} transactions</div>
                                    @if ($paymentInfo->percentage_charge)
                                        <div>{{ $paymentInfo->percentage_charge }}% fee</div>
                                    @endif
                                </div>
                            @endif

                            @if ($needsSync)
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⚠️ Sync needed
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($isRejected)
                <div class="flex items-center justify-between p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800">Payment Info Rejected</h3>
                            <p class="text-sm text-red-700">Your submission was rejected. Please resubmit with correct
                                information.</p>
                            @if ($paymentInfo->rejection_reason)
                                <p class="text-xs text-red-600 mt-1"><strong>Reason:</strong>
                                    {{ $paymentInfo->rejection_reason }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ \App\Filament\Resources\PaymentInfoResource::getUrl('create') }}"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Resubmit
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
