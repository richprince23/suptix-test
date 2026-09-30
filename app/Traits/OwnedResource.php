<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait OwnedResource
{
    public static function getEloquentQuery(): Builder
    {
        $organizer = User::where('id', Auth::user()->id)->get()->first(); // get lecturer id from user

        // var_dump($lecturer->id);
        return parent::getEloquentQuery()
            ->where('organizer_id', '=', $organizer->id);
    }
}
