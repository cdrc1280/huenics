<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestedQuotationResource\Pages;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\RequestedQuotation;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RequestedQuotationResource extends Resource
{
    protected static ?string $model = RequestedQuotation::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static ?string $navigationParentItem = 'Quotations';
    protected static ?string $navigationLabel = 'Requested Quotations';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageQuotations() ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = RequestedQuotation::where('status', Quotation::STATUS_PENDING)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['salesAgent', 'project', 'lineItems']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Client & Request Information')
                ->description('Details submitted by customer from the online portal')
                ->schema([
                    TextInput::make('quotation_number')
                        ->label('Reference Number')
                        ->disabled(),

                    TextInput::make('customer_name')
                        ->label('Client Name')
                        ->required(),

                    TextInput::make('customer_company')
                        ->label('Company / Organization'),

                    TextInput::make('customer_email')
                        ->label('Email Address')
                        ->email(),

                    TextInput::make('phone_no')
                        ->label('Phone Number'),

                    TextInput::make('project_name')
                        ->label('Project Title'),

                    TextInput::make('project_location')
                        ->label('Jobsite / Delivery Location'),

                    Select::make('status')
                        ->options([
                            Quotation::STATUS_PENDING  => 'Pending Review',
                            Quotation::STATUS_APPROVED => 'Approved / Converted',
                            Quotation::STATUS_REJECTED => 'Rejected',
                        ])
                        ->default(Quotation::STATUS_PENDING),

                    TextInput::make('client_ip')
                        ->label('Client Origin IP')
                        ->disabled(),

                    DatePicker::make('quotation_date')
                        ->label('Requested Date')
                        ->disabled(),

                    Textarea::make('notes')
                        ->label('Customer Remarks')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Requested Bill of Quantities / Line Items')
                ->schema([
                    Repeater::make('lineItems')
                        ->relationship('lineItems')
                        ->schema([
                            Select::make('product_id')
                                ->label('Linked Product')
                                ->options(Product::pluck('canonical_name', 'id'))
                                ->searchable()
                                ->disabled(),

                            TextInput::make('description')
                                ->label('Item Description')
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('qty')
                                ->label('Quantity')
                                ->numeric()
                                ->required(),

                            TextInput::make('unit')
                                ->label('Unit')
                                ->default('pcs'),

                            TextInput::make('unit_price')
                                ->label('Unit Price (₱)')
                                ->numeric()
                                ->prefix('₱'),

                            TextInput::make('line_total')
                                ->label('Line Total (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->disabled(),
                        ])
                        ->columns(7)
                        ->columnSpanFull()
                        ->addable(false)
                        ->deletable(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('quotation_number')
                    ->label('Reference #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->tooltip('Customer Online Quote Reference'),

                TextColumn::make('customer_name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn(RequestedQuotation $r) => $r->customer_company ?: 'Individual / Direct'),

                TextColumn::make('customer_email')
                    ->label('Contact')
                    ->searchable()
                    ->description(fn(RequestedQuotation $r) => $r->phone_no ?: 'No Phone')
                    ->copyable(),

                TextColumn::make('project_name')
                    ->label('Project')
                    ->searchable()
                    ->wrap()
                    ->default('General Project')
                    ->description(fn(RequestedQuotation $r) => $r->project_location ?: 'Metro Manila'),

                TextColumn::make('line_items_count')
                    ->label('Items')
                    ->counts('lineItems')
                    ->badge()
                    ->color('gray')
                    ->alignment('center'),

                TextColumn::make('total_amount')
                    ->label('Estimated Total')
                    ->money('PHP')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Quotation::STATUS_PENDING  => 'warning',
                        Quotation::STATUS_APPROVED, Quotation::STATUS_CONVERTED => 'success',
                        Quotation::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Quotation::STATUS_PENDING  => 'Pending Review',
                        Quotation::STATUS_APPROVED => 'Approved / Official',
                        Quotation::STATUS_CONVERTED => 'Converted to PO',
                        Quotation::STATUS_REJECTED => 'Rejected',
                        default => ucfirst($state),
                    }),

                TextColumn::make('created_at')
                    ->label('Received Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('client_ip')
                    ->label('Client IP')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Quotation::STATUS_PENDING  => 'Pending Review',
                        Quotation::STATUS_APPROVED => 'Approved / Official',
                        Quotation::STATUS_REJECTED => 'Rejected',
                    ]),
            ])
            ->actions([
                Action::make('convert_to_official')
                    ->label('Convert to Official')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(RequestedQuotation $record) => $record->is_online_request)
                    ->form([
                        Select::make('sales_agent_id')
                            ->label('Assign Sales Agent')
                            ->options(fn() => User::whereIn('role', [User::ROLE_SALES_EXECUTIVE, User::ROLE_ADMIN])->pluck('name', 'id'))
                            ->default(auth()->id())
                            ->required()
                            ->helperText('Select the internal sales executive assigned to manage this quotation and follow-up with the client.'),

                        Textarea::make('remarks')
                            ->label('Internal Approval Notes')
                            ->placeholder('e.g. Verified quantities and pricing. Ready to send formal contract proposal.')
                            ->rows(2),
                    ])
                    ->action(function (RequestedQuotation $record, array $data): void {
                        $record->convertToOfficialQuotation($data['sales_agent_id']);
                        if (!empty($data['remarks'])) {
                            $record->notes = ($record->notes ? $record->notes . "\n" : '') . "Approval Notes: " . $data['remarks'];
                            $record->save();
                        }

                        Notification::make()
                            ->title('Quotation Converted')
                            ->body("Online Request {$record->quotation_number} has been converted into an official quotation and assigned to the sales agent.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(RequestedQuotation $record) => $record->status === Quotation::STATUS_PENDING)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->placeholder('e.g. Incomplete specifications, unsupported delivery location, or duplicate submission.')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (RequestedQuotation $record, array $data): void {
                        $record->status = Quotation::STATUS_REJECTED;
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();

                        Notification::make()
                            ->title('Request Rejected')
                            ->body("Online Request {$record->quotation_number} has been rejected.")
                            ->warning()
                            ->send();
                    }),

                ActionGroup::make([
                    ViewAction::make(),
                    DeleteAction::make(),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequestedQuotations::route('/'),
            'view'  => Pages\ViewRequestedQuotation::route('/{record}'),
        ];
    }
}
