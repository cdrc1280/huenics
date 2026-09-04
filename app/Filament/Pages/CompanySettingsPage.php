<?php

namespace App\Filament\Pages;

use App\Models\CompanySetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class CompanySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static UnitEnum|string|null $navigationGroup = 'Settings & System';
    protected static ?string $navigationLabel = 'Company & Business Settings';
    protected static ?string $title = 'Company & Business Settings';
    protected string $view = 'filament.pages.company-settings-page';
    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageUsers() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => CompanySetting::getSetting('company_name', 'Huenics Industrial Sales Inc.'),
            'company_tagline' => CompanySetting::getSetting('company_tagline', 'Direct Importer & Wholesale Industrial Supply'),
            'founding_year' => (int) CompanySetting::getSetting('founding_year', 2022),
            'years_in_business_override' => CompanySetting::getSetting('years_in_business_override'),
            'contact_phone' => CompanySetting::getSetting('contact_phone', '0906-144-2553'),
            'contact_email' => CompanySetting::getSetting('contact_email', 'sales@huenics.com'),
            'office_address' => CompanySetting::getSetting('office_address', '2F Starmall EDSA-Shaw, Mandaluyong City'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Corporate Profile & Operational Years')
                    ->description('Configure company identity and operational longevity displayed across public quotation builders, landing pages, and official headers.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('company_name')
                                ->label('Corporate Legal Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('company_tagline')
                                ->label('Tagline / Subtitle')
                                ->maxLength(255),

                            TextInput::make('founding_year')
                                ->label('Founding Year')
                                ->numeric()
                                ->required()
                                ->minValue(1900)
                                ->maxValue((int) date('Y'))
                                ->helperText('The system automatically calculates longevity (e.g. Current Year ' . date('Y') . ' - Founding Year).'),

                            TextInput::make('years_in_business_override')
                                ->label('Manual Years Override (Optional)')
                                ->numeric()
                                ->nullable()
                                ->minValue(1)
                                ->maxValue(150)
                                ->helperText('If set, this number takes precedence over the founding year calculation.'),
                        ]),
                    ]),

                Section::make('Contact Details & Physical Headquarters')
                    ->description('Contact numbers, official sales email, and office location displayed on client documentation and customer portal.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('contact_phone')
                                ->label('Primary Phone / Hotline')
                                ->maxLength(100),

                            TextInput::make('contact_email')
                                ->label('Sales Email Address')
                                ->email()
                                ->maxLength(150),

                            Textarea::make('office_address')
                                ->label('Corporate / Warehouse Address')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            CompanySetting::setSetting($key, $value);
        }

        Notification::make()
            ->title('Company Settings Updated')
            ->body('Business longevity and corporate profile details have been saved successfully.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->action('save'),
        ];
    }
}
