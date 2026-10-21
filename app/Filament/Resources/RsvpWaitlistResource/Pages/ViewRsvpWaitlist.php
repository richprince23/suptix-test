<?php

namespace App\Filament\Resources\RsvpWaitlistResource\Pages;

use App\Filament\Resources\RsvpWaitlistResource;
use App\Models\RsvpWaitlist;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRsvpWaitlist extends ViewRecord
{
    protected static string $resource = RsvpWaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->action(function () {
                    RsvpWaitlistResource::handleApprove($this->record);
                    $this->refreshFormData(['status', 'actioned_at', 'actioned_by']);
                }),
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'rejected',
                        'actioned_at' => now(),
                        'actioned_by' => auth()->id(),
                    ]);

                    $this->refreshFormData(['status', 'actioned_at', 'actioned_by']);

                    Notification::make()
                        ->title('Entry rejected')
                        ->success()
                        ->send();
                }),
        ];
    }
}
