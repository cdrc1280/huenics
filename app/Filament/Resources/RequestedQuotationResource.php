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
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
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
                ->icon('heroicon-o-user-circle')
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
                            Quotation::STATUS_PENDING => 'Pending Review',
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
                ])->columns(2),

            Section::make('Requested Bill of Quantities / Line Items')
                ->icon('heroicon-o-list-bullet')
                ->description('Line items submitted by customer, linked catalog references, specifications, and estimated values')
                ->schema([
                    Repeater::make('lineItems')
                        ->relationship('lineItems')
                        ->label('Line Items')
                        ->itemLabel(function (array $state): ?string {
                            $lineNo = $state['line_no'] ?? null;
                            $code = ! empty($state['item_code']) ? "[{$state['item_code']}] " : '';
                            $desc = $state['description'] ?? null;
                            $total = isset($state['line_total']) ? ' • ₱'.number_format((float) $state['line_total'], 2) : '';

                            $label = $lineNo ? "Line #{$lineNo}: " : 'Line Item: ';
                            $label .= $code.($desc ? Str::limit($desc, 50) : 'Product');
                            $label .= $total;

                            return $label;
                        })
                        ->collapsible()
                        ->collapsed(false)
                        ->schema([
                            TextInput::make('line_no')
                                ->label('#')
                                ->numeric()
                                ->default(1)
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(1),

                            TextInput::make('item_code')
                                ->label('Item Code / SKU')
                                ->placeholder('—')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            Select::make('product_id')
                                ->label('Linked Catalog Product')
                                ->options(Product::pluck('canonical_name', 'id'))
                                ->searchable()
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(8),

                            Placeholder::make('product_image_preview')
                                ->label('Photo')
                                ->content(function ($get) {
                                    $pId = $get('product_id');
                                    if (! $pId) {
                                        return new HtmlString('<div class="w-8 h-8 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 text-[10px] select-none">—</div>');
                                    }
                                    $product = Product::find($pId);
                                    $url = $product?->image_url;
                                    if (! $url) {
                                        return new HtmlString('<div class="w-8 h-8 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 text-[10px] select-none">—</div>');
                                    }

                                    return new HtmlString('<img src="'.e($url).'" alt="Product" class="w-8 h-8 object-contain rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-0.5 shadow-2xs" />');
                                })
                                ->columnSpan(1),

                            Textarea::make('description')
                                ->label('Item Description / Customer Specifications')
                                ->rows(2)
                                ->disabled()
                                ->dehydrated()
                                ->columnSpanFull(),

                            TextInput::make('qty')
                                ->label('Quantity')
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            TextInput::make('unit')
                                ->label('Unit')
                                ->default('pcs')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            TextInput::make('unit_price')
                                ->label('Unit Price (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            TextInput::make('discounted_price')
                                ->label('Discounted Price (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->placeholder('—')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(3),

                            TextInput::make('line_total')
                                ->label('Line Total (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(3),
                        ])
                        ->columns(12)
                        ->columnSpanFull()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ]),

            Section::make('Financial Overview')
                ->icon('heroicon-o-calculator')
                ->description('Summary of submitted estimates and totals')
                ->schema([
                    TextInput::make('total_amount')
                        ->label('Total Estimated Amount')
                        ->prefix('₱')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    TextInput::make('negotiated_amount')
                        ->label('Negotiated / Discounted Subtotal')
                        ->prefix('₱')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),
                ])->columns(2),
        ])->columns(1);
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
                    ->description(fn (RequestedQuotation $r) => $r->customer_company ?: 'Individual / Direct'),

                TextColumn::make('customer_email')
                    ->label('Contact')
                    ->searchable()
                    ->description(fn (RequestedQuotation $r) => $r->phone_no ?: 'No Phone')
                    ->copyable(),

                TextColumn::make('project_name')
                    ->label('Project')
                    ->searchable()
                    ->wrap()
                    ->default('General Project')
                    ->description(fn (RequestedQuotation $r) => $r->project_location ?: 'Metro Manila'),

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
                    ->color(fn (string $state): string => match ($state) {
                        Quotation::STATUS_PENDING => 'warning',
                        Quotation::STATUS_APPROVED, Quotation::STATUS_CONVERTED => 'success',
                        Quotation::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Quotation::STATUS_PENDING => 'Pending Review',
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
                        Quotation::STATUS_PENDING => 'Pending Review',
                        Quotation::STATUS_APPROVED => 'Approved / Official',
                        Quotation::STATUS_REJECTED => 'Rejected',
                    ]),
            ])
            ->actions([

                ActionGroup::make([
                    Action::make('accept_quotation')
                        ->label('Accept Quotation')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (RequestedQuotation $record) => $record->is_online_request)
                        ->modalHeading('Accept Requested Quotation')
                        ->modalDescription('Confirm and accept this customer requested quotation. It will be transferred to Quotations as Pending where it can be reviewed and approved by management.')
                        ->modalSubmitActionLabel('Accept Quotation')
                        ->form([
                            Select::make('sales_agent_id')
                                ->label('Assign Sales Agent')
                                ->options(fn () => User::whereIn('role', [User::ROLE_SALES_EXECUTIVE, User::ROLE_ADMIN])->pluck('name', 'id'))
                                ->default(auth()->id())
                                ->required()
                                ->helperText('Select the internal sales executive assigned to manage this quotation and follow-up with the client.'),

                            Textarea::make('remarks')
                                ->label('Acceptance Notes / Internal Remarks')
                                ->placeholder('e.g. Verified client specifications and quantities. Transferred for pricing review and management approval.')
                                ->rows(2),
                        ])
                        ->action(function (RequestedQuotation $record, array $data): void {
                            $record->acceptAndTransferToQuotations($data['sales_agent_id'], $data['remarks'] ?? null);

                            Notification::make()
                                ->title('Quotation Accepted')
                                ->body("Online Request {$record->quotation_number} has been accepted and transferred to Quotations (Pending Review & Approval).")
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        // ->visible(fn (RequestedQuotaztion $record) => $record->status === Quotation::STATUS_PENDING)
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
            'view' => Pages\ViewRequestedQuotation::route('/{record}'),
        ];
    }
}
