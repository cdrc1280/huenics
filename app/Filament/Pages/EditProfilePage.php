<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class EditProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Edit Profile';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user';
    protected static ?string $slug = 'edit-profile';
    protected static UnitEnum|string|null $navigationGroup = 'System Administration';
    protected string $view = 'filament.pages.edit-profile-page';
    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->form->fill([
                'name' => $user->name,
                'email' => $user->email,
                'e_signature_path' => $user->e_signature_path,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Information & E-Signature')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required(),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required(),

                        TextInput::make('password')
                            ->label('New Password (leave blank to keep current)')
                            ->password()
                            ->nullable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state)),

                        FileUpload::make('e_signature_path')
                            ->label('Digital E-Signature (PNG / JPEG with transparent background)')
                            ->image()
                            ->disk('local')
                            ->directory('signatures')
                            ->visibility('private')
                            ->preserveFilenames()
                            ->helperText('This e-signature will be automatically attached to Quotations and approved Purchase Orders created or authorized by you.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $user = auth()->user();

        if ($user) {
            $updateData = [
                'name' => $state['name'],
                'email' => $state['email'],
                'e_signature_path' => $state['e_signature_path'] ?? $user->e_signature_path,
            ];

            if (!empty($state['password'])) {
                $updateData['password'] = Hash::make($state['password']);
            }

            $user->update($updateData);

            Notification::make()
                ->title('Profile & E-Signature Saved Successfully')
                ->success()
                ->send();
        }
    }
}
