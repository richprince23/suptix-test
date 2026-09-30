<?php

namespace App\Filament\Resources\PaymentInfoResource\Pages;

use App\Filament\Resources\PaymentInfoResource;
use App\Models\Organizer;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListPaymentInfos extends ListRecords
{
    protected static string $resource = PaymentInfoResource::class;

    protected function getHeaderActions(): array
    {
        $orgs = Organizer::where('id', Filament::getTenant()->id)->first();
        $count = $orgs->paymentInfos()->count();

        return [

            Actions\CreateAction::make()->hidden($count > 0),
        ];
    }
}
