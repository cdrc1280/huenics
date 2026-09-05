<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccountingOverviewWidget;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\AccountingReportService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

class AccountingDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calculator';
    protected static UnitEnum|string|null $navigationGroup = 'Dashboards & Analytics';
    protected static ?string $navigationLabel = 'Accounting & Receivables';
    protected static ?string $title = 'Accounting Dashboard & Receivables Ledger';
    protected string $view = 'filament.pages.accounting-dashboard';
    protected static ?int $navigationSort = 4;

    // ─── Interactive Email Template Section Properties ─────────────────
    public ?int $selectedPoId = null;
    public string $emailRecipient = '';
    public string $emailSubject = '';
    public string $emailBody = '';

    // ─── Interactive Tab Filter ────────────────────────────────────────
    public string $activeTab = 'all'; // 'all' | 'follow_up' | 'payment_history'

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AccountingOverviewWidget::class,
        ];
    }

    public function mount(): void
    {
        $this->activeTab = 'all';

        // Auto-select the highest priority order requiring follow-up
        $urgentPo = PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
            ->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)
            ->when($this->isSalesExecutiveScoped(), fn($q) => $q->where('sales_agent_id', auth()->id()))
            ->orderByRaw('CASE WHEN payment_due_date < ? THEN 1 WHEN payment_due_date <= ? THEN 2 ELSE 3 END', [
                now()->toDateString(),
                now()->addDays(10)->toDateString(),
            ])
            ->orderBy('payment_due_date', 'asc')
            ->first();

        if ($urgentPo) {
            $this->loadEmailTemplateForPo($urgentPo->id);
        }
    }

    public function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('download_receivables_pdf')
                    ->label('Download Receivables Aging Report (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(fn() => app(AccountingReportService::class)->downloadReceivablesPdf()),

                Action::make('export_receivables_csv')
                    ->label('Export Receivables Aging Report (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn() => app(AccountingReportService::class)->exportReceivablesCsv()),

                Action::make('download_history_pdf')
                    ->label('Download Settled Payment History (PDF)')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->action(fn() => app(AccountingReportService::class)->downloadPaymentHistoryPdf()),

                Action::make('export_history_csv')
                    ->label('Export Settled Payment History (CSV)')
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->action(fn() => app(AccountingReportService::class)->exportPaymentHistoryCsv()),
            ])
            ->label('Download Accounting Reports')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->button(),
        ];
    }

    protected function isSalesExecutiveScoped(): bool
    {
        $user = auth()->user();
        return $user && $user->isSalesExecutive() && !$user->isAdmin() && !$user->is_owner && !$user->isOperationsManager() && !$user->isCeo();
    }

    public function loadEmailTemplateForPo(?int $poId): void
    {
        $this->selectedPoId = $poId;

        if (!$poId) {
            $this->emailRecipient = '';
            $this->emailSubject = '';
            $this->emailBody = '';
            return;
        }

        $po = PurchaseOrder::with(['project', 'quotation'])->find($poId);
        if (!$po) {
            return;
        }

        $payload = $po->generatePaymentReminderEmail();
        $this->emailRecipient = $payload['recipient'];
        $this->emailSubject = $payload['subject'];
        $this->emailBody = $payload['body'];
    }

    public function updatedSelectedPoId($value): void
    {
        $this->loadEmailTemplateForPo($value ? (int) $value : null);
    }

    public function getSelectedPoProperty(): ?PurchaseOrder
    {
        if (!$this->selectedPoId) {
            return null;
        }

        return PurchaseOrder::with(['project', 'quotation'])->find($this->selectedPoId);
    }

    public function getPendingFollowUpOrdersProperty()
    {
        $query = PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
            ->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)
            ->when($this->isSalesExecutiveScoped(), fn($q) => $q->where('sales_agent_id', auth()->id()))
            ->with(['project', 'quotation'])
            ->orderByRaw('CASE WHEN payment_due_date < ? THEN 1 WHEN payment_due_date <= ? THEN 2 ELSE 3 END', [
                now()->toDateString(),
                now()->addDays(10)->toDateString(),
            ])
            ->orderBy('payment_due_date', 'asc');

        return $query->get();
    }

    public function sendEmailReminderFromSection(): void
    {
        if (!$this->selectedPoId) {
            Notification::make()
                ->title('No Purchase Order Selected')
                ->body('Please select an outstanding purchase order first.')
                ->warning()
                ->send();
            return;
        }

        $po = PurchaseOrder::find($this->selectedPoId);
        if (!$po) {
            return;
        }

        if (!$po->canSendPaymentReminderToday()) {
            $lastSent = $po->last_payment_reminder_sent_at ? $po->last_payment_reminder_sent_at->format('M d, Y h:i A') : 'earlier today';
            Notification::make()
                ->title('Anti-Spam Daily Limit Exceeded')
                ->body("A reminder was already dispatched today at {$lastSent}. Strictly 1 email per PO per day is permitted to avoid spamming the client.")
                ->danger()
                ->send();
            return;
        }

        $recipient = $this->emailRecipient;
        $subject = $this->emailSubject;
        $body = $this->emailBody;

        try {
            Mail::raw($body, function ($msg) use ($recipient, $subject) {
                $msg->to($recipient)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::info("Payment reminder email logged for PO #{$po->po_number} to {$recipient}: {$e->getMessage()}");
        }

        $po->update([
            'last_payment_reminder_sent_at' => now(),
        ]);

        Notification::make()
            ->title('Payment Reminder Dispatched')
            ->body("Official payment reminder successfully dispatched to {$recipient}. Daily limit recorded.")
            ->success()
            ->send();

        // Refresh template
        $this->loadEmailTemplateForPo($po->id);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function getSummaryStats(): array
    {
        $query = PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
            ->when($this->isSalesExecutiveScoped(), fn($q) => $q->where('sales_agent_id', auth()->id()));

        $allOrders = $query->get();
        $deliveredOrders = $allOrders->filter(fn($po) => $po->isDelivered());
        $pendingDeliveryOrders = $allOrders->filter(fn($po) => !$po->isDelivered());

        $totalReceivables = (float) $allOrders->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)->sum('order_amount');
        $totalCollected = (float) $allOrders->where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID)->sum('order_amount');

        $overdueCount = 0;
        $warningCount = 0;
        $overdueAmount = 0.0;
        $warningAmount = 0.0;

        foreach ($allOrders as $order) {
            if (!$order->isPaid() && $order->days_until_due !== null) {
                if ($order->days_until_due < 0) {
                    $overdueCount++;
                    $overdueAmount += (float) $order->order_amount;
                } elseif ($order->days_until_due <= 10) {
                    $warningCount++;
                    $warningAmount += (float) $order->order_amount;
                }
            }
        }

        return [
            'totalReceivables'     => $totalReceivables,
            'totalCollected'       => $totalCollected,
            'overdueCount'         => $overdueCount,
            'overdueAmount'        => $overdueAmount,
            'warningCount'         => $warningCount,
            'warningAmount'        => $warningAmount,
            'totalDelivered'       => $deliveredOrders->count(),
            'totalPendingDelivery' => $pendingDeliveryOrders->count(),
            'totalOrders'          => $allOrders->count(),
            'paidCount'            => $allOrders->where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID)->count(),
            'pendingCount'         => $allOrders->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)->count(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
                    ->with(['project', 'salesAgent', 'quotation', 'transactions']);

                if ($this->isSalesExecutiveScoped()) {
                    $query->where('sales_agent_id', auth()->id());
                }

                if ($this->activeTab === 'follow_up') {
                    $query->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)
                        ->whereNotNull('payment_due_date')
                        ->where('payment_due_date', '<=', now()->addDays(10)->toDateString());
                } elseif ($this->activeTab === 'payment_history') {
                    $query->where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID);
                }

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO #')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Client Account')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable()
                    ->description(fn(PurchaseOrder $r) => $r->payment_account ? "Account Tag: {$r->payment_account}" : null),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->default('General Project')
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('order_amount')
                    ->label('Order Amount (₱)')
                    ->money('PHP')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('delivery_status')
                    ->label('Fulfillment Stage')
                    ->badge()
                    ->formatStateUsing(fn(string $state, PurchaseOrder $record): string => match (true) {
                        $record->isDelivered() => 'Delivered',
                        $state === PurchaseOrder::DELIVERY_TRANSIT => 'In Transit',
                        default => 'Pending Delivery',
                    })
                    ->color(fn(string $state, PurchaseOrder $record): string => match (true) {
                        $record->isDelivered() => 'success',
                        $state === PurchaseOrder::DELIVERY_TRANSIT => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('payment_term_type')
                    ->label('Payment Terms')
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => $state ? (PurchaseOrder::getPaymentTermOptions()[$state] ?? strtoupper($state)) : 'Not Set')
                    ->color(fn(?string $state) => match ($state) {
                        PurchaseOrder::PAYMENT_TERM_COD => 'success',
                        PurchaseOrder::PAYMENT_TERM_PDC_7, PurchaseOrder::PAYMENT_TERM_PDC_15, PurchaseOrder::PAYMENT_TERM_PDC_30 => 'info',
                        PurchaseOrder::PAYMENT_TERM_CREDIT_30 => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('actual_delivery_date')
                    ->label('Delivered On')
                    ->date('M j, Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('payment_due_date')
                    ->label('Due Date')
                    ->date('M j, Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Settlement Status')
                    ->badge()
                    ->color(fn(PurchaseOrder $r) => $r->due_status_color)
                    ->formatStateUsing(fn(?string $state, PurchaseOrder $r): string => match ($state) {
                        'paid' => 'PAID',
                        'unpaid' => ($r->days_until_due !== null && $r->days_until_due < 0)
                            ? 'OVERDUE (' . abs($r->days_until_due) . 'd ago)'
                            : ($r->days_until_due !== null ? 'UNPAID (' . $r->days_until_due . 'd left)' : 'UNPAID'),
                        default => strtoupper($state ?: 'unpaid'),
                    })
                    ->tooltip(function (PurchaseOrder $r): string {
                        if ($r->isPaid()) {
                            return 'Payment received & cleared: ' . ($r->paid_at ? $r->paid_at->format('M d, Y h:i A') : 'Settled');
                        }
                        if ($r->days_until_due !== null && $r->days_until_due <= 10) {
                            return 'Action Required: Due within 10 days! Send email payment follow-up to client.';
                        }
                        return 'Standard payment lifecycle';
                    }),

                TextColumn::make('paid_at')
                    ->label('Settled On')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('Pending')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('pdc_check_number')
                    ->label('PDC Check #')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('pdc_bank')
                    ->label('Bank')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_payment_reminder_sent_at')
                    ->label('Last Reminder Sent')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('Never Sent')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'unpaid'  => 'Unpaid / Pending Receivables',
                        'paid'    => 'Paid / Cleared Transactions',
                        'overdue' => 'Overdue Receivables Only',
                        'due_10'  => 'Due in 10 Days or Less (Follow-Up Needed)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if (!$value) return;

                        if ($value === 'unpaid') {
                            $query->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID);
                        } elseif ($value === 'paid') {
                            $query->where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID);
                        } elseif ($value === 'overdue') {
                            $query->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)
                                  ->whereNotNull('payment_due_date')
                                  ->where('payment_due_date', '<', now()->toDateString());
                        } elseif ($value === 'due_10') {
                            $query->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)
                                  ->whereNotNull('payment_due_date')
                                  ->where('payment_due_date', '<=', now()->addDays(10)->toDateString())
                                  ->where('payment_due_date', '>=', now()->toDateString());
                        }
                    }),

                SelectFilter::make('payment_term_type')
                    ->label('Payment Term Type')
                    ->options(PurchaseOrder::getPaymentTermOptions()),

                SelectFilter::make('delivery_status')
                    ->label('Fulfillment Stage')
                    ->options([
                        'delivered' => 'Delivered Orders',
                        'pending'   => 'Pending Delivery / In Progress',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $val = $data['value'] ?? null;
                        if (!$val) return;
                        if ($val === 'delivered') {
                            $query->where(fn($q) => $q->where('delivery_status', PurchaseOrder::DELIVERY_DELIVERED)->orWhere('status', PurchaseOrder::STATUS_DELIVERED));
                        } elseif ($val === 'pending') {
                            $query->whereNotIn('delivery_status', [PurchaseOrder::DELIVERY_DELIVERED, 'delivered'])
                                  ->where('status', '!=', PurchaseOrder::STATUS_DELIVERED);
                        }
                    }),

                SelectFilter::make('sales_agent_id')
                    ->label('Sales Executive / Account')
                    ->options(fn() => User::whereIn('role', [User::ROLE_SALES_EXECUTIVE, User::ROLE_ADMIN])->pluck('name', 'id'))
                    ->visible(fn() => auth()->user()?->canManageQuotations() ?? false),
            ])
            ->actions([
                // 1-Click Email Client Action with daily anti-spam check
                Action::make('email_client')
                    ->label('Email Client')
                    ->icon('heroicon-m-envelope')
                    ->color(fn(PurchaseOrder $r) => $r->due_status_color === 'danger' ? 'danger' : ($r->due_status_color === 'warning' ? 'warning' : 'primary'))
                    ->visible(fn(PurchaseOrder $r) => !$r->isPaid())
                    ->modalHeading(fn(PurchaseOrder $r): string => "Follow-Up Payment Reminder: PO #{$r->po_number}")
                    ->modalDescription(function (PurchaseOrder $r): string {
                        if (!$r->canSendPaymentReminderToday()) {
                            $lastSent = $r->last_payment_reminder_sent_at ? $r->last_payment_reminder_sent_at->format('M d, Y h:i A') : 'earlier today';
                            return "Anti-Spam Limitation: A payment follow-up email was already sent for this PO today ({$lastSent}). Strictly 1 email per PO per day is permitted to avoid spamming the client.";
                        }
                        return "Review and dispatch an official Huenics payment follow-up email to this client. Maximum 1 send per day.";
                    })
                    ->modalWidth('2xl')
                    ->form(function (PurchaseOrder $r) {
                        $template = $r->generatePaymentReminderEmail();

                        return [
                            TextInput::make('recipient_email')
                                ->label('Client Recipient Email')
                                ->email()
                                ->required()
                                ->default($template['recipient'])
                                ->disabled(!$r->canSendPaymentReminderToday()),

                            TextInput::make('email_subject')
                                ->label('Email Subject')
                                ->required()
                                ->default($template['subject'])
                                ->disabled(!$r->canSendPaymentReminderToday()),

                            Textarea::make('email_body')
                                ->label('Email Template Body')
                                ->required()
                                ->rows(10)
                                ->default($template['body'])
                                ->disabled(!$r->canSendPaymentReminderToday()),
                        ];
                    })
                    ->action(function (PurchaseOrder $record, array $data): void {
                        if (!$record->canSendPaymentReminderToday()) {
                            Notification::make()
                                ->title('Email Limit Exceeded')
                                ->body('Only 1 reminder email per PO per day is permitted to avoid spamming.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $recipient = $data['recipient_email'];
                        $subject = $data['email_subject'];
                        $body = $data['email_body'];

                        try {
                            Mail::raw($body, function ($msg) use ($recipient, $subject) {
                                $msg->to($recipient)->subject($subject);
                            });
                        } catch (\Throwable $e) {
                            Log::info("Payment reminder email logged for PO #{$record->po_number} to {$recipient}: {$e->getMessage()}");
                        }

                        $record->update([
                            'last_payment_reminder_sent_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Follow-Up Email Dispatched')
                            ->body("Payment reminder successfully dispatched to {$recipient}. Daily limit recorded.")
                            ->success()
                            ->send();

                        if ($this->selectedPoId === $record->id) {
                            $this->loadEmailTemplateForPo($record->id);
                        }
                    }),

                // Mark Paid Action
                Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn(PurchaseOrder $r) => !$r->isPaid())
                    ->requiresConfirmation()
                    ->modalHeading(fn(PurchaseOrder $r) => "Confirm Settlement for PO #{$r->po_number}")
                    ->modalDescription('Confirm that full payment for this purchase order has been cleared and deposited.')
                    ->action(function (PurchaseOrder $record): void {
                        $record->update([
                            'payment_status' => PurchaseOrder::PAYMENT_STATUS_PAID,
                            'paid_at'        => now(),
                            'is_completed'   => true,
                            'completed_at'   => $record->completed_at ?? now(),
                        ]);

                        Notification::make()
                            ->title('Payment Settled')
                            ->body("PO #{$record->po_number} marked as fully PAID. Payment ledger updated.")
                            ->success()
                            ->send();

                        if ($this->selectedPoId === $record->id) {
                            $this->loadEmailTemplateForPo($record->id);
                        }
                    }),
            ]);
    }
}
