<?php

namespace App\Filament\Resources;

use App\Actions\ReconcileDocumentTotals;
use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\Project;
use App\Models\Vendor;
use App\Services\DocumentParsers\DynamicDocumentParser;
use BackedEnum;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function canCreate(): bool
    {
        return auth()->user()?->canCreateDocuments() ?? true;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Document')
                    ->description(
                        'Upload Purchase Orders, Order Slips, or Quotations in PDF or Image format (JPG, PNG, WEBP).'
                    )
                    ->components([
                        Forms\Components\FileUpload::make('disk_path')
                            ->label('Document File (PDF or Image)')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->disk('local')
                            ->directory('documents/uploads')
                            ->preserveFilenames()
                            ->storeFileNamesIn('original_filename')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if ($state instanceof TemporaryUploadedFile) {
                                    $hash = hash_file(
                                        'sha256',
                                        $state->getRealPath()
                                    );

                                    $set('file_hash', $hash);
                                }
                            }),

                        Forms\Components\Hidden::make('original_filename'),

                        Forms\Components\Hidden::make('file_hash'),

                        Forms\Components\Hidden::make('uploaded_by')
                            ->default(fn() => auth()->id() ?: 1),

                        Grid::make(1)
                            ->components([
                                Forms\Components\Select::make('document_type')
                                    ->label('Document Type')
                                    ->options([
                                        Document::TYPE_PURCHASE_ORDER =>
                                            'Purchase Order (Customer)',

                                        Document::TYPE_ORDER_SLIP =>
                                            'Order Slip (Internal)',

                                        Document::TYPE_VENDORS_AGREEMENT =>
                                            'Vendors Agreement Form (Quotation)',
                                    ])
                                    ->default(Document::TYPE_PURCHASE_ORDER)
                                    ->required(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Doc #')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            Document::TYPE_PURCHASE_ORDER =>
                            'Purchase Order',

                            Document::TYPE_ORDER_SLIP =>
                            'Order Slip',

                            Document::TYPE_VENDORS_AGREEMENT =>
                            'Quotation / Agreement',

                            default => $state,
                        }
                    )
                    ->color(
                        fn(string $state): string => match ($state) {
                            Document::TYPE_PURCHASE_ORDER => 'primary',
                            Document::TYPE_ORDER_SLIP => 'info',
                            Document::TYPE_VENDORS_AGREEMENT => 'warning',
                            default => 'gray',
                        }
                    )
                    ->tooltip(fn (Document $record): string => "Document Type: " . strtoupper(str_replace('_', ' ', $record->document_type))),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn (Document $record): string => "Vendor / Supplier: " . ($record->vendor?->name ?? 'Unassigned')),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn (Document $record): string => "Project Site: " . ($record->project?->name ?? 'Unassigned')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            Document::STATUS_UPLOADED => 'gray',
                            Document::STATUS_PROCESSING => 'info',
                            Document::STATUS_REQUIRES_REVIEW => 'warning',
                            Document::STATUS_VERIFIED => 'success',
                            Document::STATUS_FAILED => 'danger',
                            Document::STATUS_REJECTED => 'gray',
                            default => 'gray',
                        }
                    )
                    ->tooltip(fn (Document $record): string => "Ingestion status: {$record->status}"),

                Tables\Columns\IconColumn::make('mismatch_flag')
                    ->label('Issues')
                    ->state(
                        fn(Document $record): bool => $record->hasMismatches()
                    )
                    ->boolean()
                    ->trueIcon('heroicon-s-exclamation-triangle')
                    ->falseIcon('heroicon-s-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(
                        fn(Document $record): string =>
                        $record->hasMismatches()
                        ? 'Arithmetic or VAT mismatch detected'
                        : 'Clean / Reconciled'
                    ),

                Tables\Columns\TextColumn::make('totals.printed_total')
                    ->label('Total (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn (Document $record): string => "Printed total amount: ₱" . number_format((float) ($record->totals?->printed_total ?? 0), 2)),

                Tables\Columns\TextColumn::make('extraction_confidence')
                    ->label('Confidence')
                    ->suffix('%')
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn (Document $record): string => "Extraction parser confidence score: {$record->extraction_confidence}%"),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->tooltip('Timestamp when PDF was uploaded into the system'),
            ])

            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        Document::TYPE_PURCHASE_ORDER =>
                            'Purchase Order',

                        Document::TYPE_ORDER_SLIP =>
                            'Order Slip',

                        Document::TYPE_VENDORS_AGREEMENT =>
                            'Quotation / Agreement',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Document::STATUS_REQUIRES_REVIEW =>
                            'Requires Review',

                        Document::STATUS_VERIFIED =>
                            'Verified',

                        Document::STATUS_PROCESSING =>
                            'Processing',

                        Document::STATUS_FAILED =>
                            'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('vendor_id')
                    ->relationship('vendor', 'name'),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('review')
                        ->label('Review & Verify')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->url(
                            fn(Document $record): string =>
                            ReviewQueuePage::getUrl([
                                'document_id' => $record->id,
                            ])
                        )
                        ->visible(
                            fn(Document $record): bool =>
                            in_array(
                                $record->status,
                                [
                                    Document::STATUS_REQUIRES_REVIEW,
                                    Document::STATUS_UPLOADED,
                                    Document::STATUS_FAILED,
                                    Document::STATUS_VERIFIED,
                                ],
                                true
                            )
                        ),

                    Action::make('reextract')
                        ->label('Re-Parse')
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(
                            function (Document $record, DynamicDocumentParser $parser, ReconcileDocumentTotals $reconciler): void {
                                try {
                                    $res = $parser->parseDocument($record);

                                    $reconciler->execute($record);

                                    Notification::make()
                                        ->title('Document parsed successfully')
                                        ->body(
                                            "Extracted {$res['line_items_count']} line items with {$res['confidence']}% confidence."
                                        )
                                        ->success()
                                        ->send();
                                } catch (\Throwable $e) {
                                    Notification::make()
                                        ->title('Extraction failed')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }
                        ),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
        ];
    }
}
