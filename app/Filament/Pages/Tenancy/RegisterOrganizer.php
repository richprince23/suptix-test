<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Organizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterOrganizer extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Organization';
    }

    public function form(Form $form): Form
    {
        // register a new tennant / organizer from here
        // this form is auto autimacally called after user registration
        // check adminservice provider for tennant registration and hasTennants methods
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Basic Info')->schema([
                        TextInput::make('name')->required()->label('Organization Name'),
                        TextInput::make('email')->required()->email()->label('Organization Email'),
                        TextInput::make('phone')->required()->tel()->label('Organization Phone'),
                    ]),
                    Step::make('Address Info')->schema([
                        TextInput::make('address')->required()->label('Address'),
                        TextInput::make('city')->required()->label('City'),
                        Select::make('state')->required()
                            ->options([
                                'ahafo_region' => 'Ahafo Region',
                                'ashanti_region' => 'Ashanti Region',
                                'bono_region' => 'Bono Region',
                                'bono_east_region' => 'Bono East Region',
                                'central_region' => 'Central Region',
                                'eastern_region' => 'Eastern Region',
                                'greater_accra_region' => 'Greater Accra Region',
                                'northern_region' => 'Northern Region',
                                'north_east_region' => 'North East Region',
                                'savanna_region' => 'Savanna Region',
                                'upper_east_region' => 'Upper East Region',
                                'upper_west_region' => 'Upper West Region',
                                'volta_region' => 'Volta Region',
                                'oti_region' => 'Oti Region',
                                'western_region' => 'Western Region',
                                'western_north_region' => 'Western North Region',
                            ])->searchable()->preload()->label('State'),
                        Select::make('country')->required()
                            ->options([
                                'ghana' => 'Ghana',
                            ])->searchable()->preload()->label('Country'),
                    ]),
                    Step::make('Team Info')->schema([
                        Textarea::make('description')->label('Description'),
                        TextInput::make('website')->url(),
                        FileUpload::make('logo')->image()->imageEditorAspectRatios([
                            '1:1'
                        ])->imageEditor()->maxFiles(1)->maxSize(5120)->disk(config('filesystems.default'))->directory('organizer-logos'),
                        FileUpload::make('banner')->image()->imageEditor()->imageEditorAspectRatios([
                            '3.5:1',
                            '16:9'
                        ])->maxFiles(1)->maxSize(5120)->disk(config('filesystems.default'))->directory('organizer-banners'),
                    ]),
                ]),
            ]);
    }

    protected function handleRegistration(array $data): Organizer
    {
        $organizer = Organizer::create($data);

        $organizer->users()->attach(auth()->user());

        return $organizer;
    }

}
