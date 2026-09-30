<div class="space-y-4">
    @if(empty($syncLog))
        <p class="text-gray-500 text-center py-4">No balance sync history available.</p>
    @else
        <div class="space-y-3">
            @foreach(array_reverse($syncLog) as $entry)
                <div class="bg-gray-50 rounded-lg p-4 border">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($entry['synced_at'])->format('M j, Y g:i A') }}
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $entry['sync_method'] ?? 'unknown')) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-gray-600">Subaccount Balance</div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-400">GHS {{ number_format($entry['previous_subaccount_balance'] ?? 0, 2) }}</span>
                                <span class="text-gray-400">→</span>
                                <span class="font-medium text-green-600">GHS {{ number_format($entry['new_subaccount_balance'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <div class="text-gray-600">System Admin Balance</div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-400">GHS {{ number_format($entry['previous_system_admin_balance'] ?? 0, 2) }}</span>
                                <span class="text-gray-400">→</span>
                                <span class="font-medium text-orange-600">GHS {{ number_format($entry['new_system_admin_balance'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $subaccountChange = ($entry['new_subaccount_balance'] ?? 0) - ($entry['previous_subaccount_balance'] ?? 0);
                        $adminChange = ($entry['new_system_admin_balance'] ?? 0) - ($entry['previous_system_admin_balance'] ?? 0);
                    @endphp
                    
                    @if($subaccountChange != 0 || $adminChange != 0)
                        <div class="mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500">
                            Changes: 
                            @if($subaccountChange != 0)
                                Subaccount {{ $subaccountChange > 0 ? '+' : '' }}GHS {{ number_format($subaccountChange, 2) }}
                            @endif
                            @if($subaccountChange != 0 && $adminChange != 0), @endif
                            @if($adminChange != 0)
                                Admin {{ $adminChange > 0 ? '+' : '' }}GHS {{ number_format($adminChange, 2) }}
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
            <div class="text-sm text-blue-800">
                <strong>Current Balances:</strong>
            </div>
            <div class="text-sm text-blue-700 mt-1">
                Subaccount: {{ $paymentInfo->formatted_subaccount_balance }} | 
                System Admin: {{ $paymentInfo->formatted_system_admin_balance }} | 
                Total Volume: {{ $paymentInfo->formatted_total_transaction_volume }}
            </div>
            @if($paymentInfo->subaccount_balance_last_synced)
                <div class="text-xs text-blue-600 mt-1">
                    Last synced: {{ $paymentInfo->subaccount_balance_last_synced->diffForHumans() }}
                </div>
            @endif
        </div>
    @endif
</div>