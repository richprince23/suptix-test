<?php

namespace App\Filament\Pages;

use App\Models\Organizer;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class OrganizationProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static string $view = 'filament.pages.organization-profile';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?string $navigationLabel = 'Organization Profile';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        // $organizer = Auth::user()->currentTenant;
        $organizer = Filament::getTenant();

        if ($organizer) {
            // Fill the form with organizer data - make sure to only get fillable attributes
            $data = $organizer->only($organizer->getFillable());
            $this->form->fill($data);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->description('Update your organization information as it appears to event attendees')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Organization Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Your organization name as it appears to event attendees'),

                        Forms\Components\TextInput::make('email')
                            ->label('Organization Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Primary contact email for your organization')
                            ->prefixIcon('heroicon-m-envelope'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Organization Phone')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Contact phone number')
                            ->prefixIcon('heroicon-m-phone'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Address Information')
                    ->description('Your organization location details')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Address')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Your organization address'),

                        Forms\Components\TextInput::make('city')
                            ->label('City')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('state')
                            ->label('State')
                            ->required()
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
                            ])
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('country')
                            ->label('Country')
                            ->required()
                            ->options([
                                'ghana' => 'Ghana',
                            ])
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Organization Details')
                    ->description('Additional information about your organization')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->rows(4)
                            ->helperText('Describe your organization and what type of events you organize'),

                        Forms\Components\TextInput::make('website')
                            ->label('Website URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Your organization website URL')
                            ->prefixIcon('heroicon-m-globe-alt'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Branding & Media')
                    ->description('Upload your organization logo and banner image')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Organization Logo')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->maxFiles(1)
                            ->maxSize(5120)
                            ->disk(config('filesystems.default'))
                            ->directory('organizer-logos')
                            ->helperText('Upload your organization logo (max 5MB)'),

                        Forms\Components\FileUpload::make('banner')
                            ->label('Banner Image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['3.5:1', '16:9'])
                            ->maxFiles(1)
                            ->maxSize(5120)
                            ->disk(config('filesystems.default'))
                            ->directory('organizer-banners')
                            ->helperText('Upload a banner image for your organization profile (max 5MB)'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // $organizer = Auth::user()->currentTenant;
        $organizer_id = Filament::getTenant()->id;
        $organizer = Organizer::find( $organizer_id);
        $organizer->update($data);

        Notification::make()
            ->success()
            ->title('Organization profile updated')
            ->body('Your organization profile has been successfully updated.')
            ->send();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Organization Profile';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('Organizer Admin') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Changes')
                ->action('save')
                ->keyBindings(['mod+s'])
                ->color('primary'),
        ];
    }
}
