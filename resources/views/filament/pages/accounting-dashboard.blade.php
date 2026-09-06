<x-filament-panels::page>
    @php
        $stats = $this->getSummaryStats();
        $selectedPo = $this->selectedPo;
        $pendingOrders = $this->pendingFollowUpOrders;
    @endphp

    <div class="space-y-6 pb-6" style="display: flex; flex-direction: column; gap: 1.5rem;">

        {{-- 1. Urgent Attention Banner (10-Day Settlement Window & Overdue Alert) --}}
        @if($stats['overdueCount'] > 0 || $stats['warningCount'] > 0)
            <div class="fi-ta-header flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-xl border border-amber-300/80 bg-amber-50/90 p-4 shadow-sm dark:border-amber-700/50 dark:bg-amber-950/30"
                 style="border-radius: 0.75rem; padding: 1rem 1.25rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                <div class="flex items-start gap-3" style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <div class="shrink-0 pt-0.5 text-amber-600 dark:text-amber-400" style="flex-shrink: 0; padding-top: 0.125rem;">
                        <x-filament::icon
                            icon="heroicon-o-exclamation-triangle"
                            class="h-6 w-6 text-amber-600 dark:text-amber-400"
                            style="width: 1.5rem; height: 1.5rem;"
                        />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-950 dark:text-amber-200" style="font-size: 0.9375rem; font-weight: 700; margin: 0;">
                            Attention Accountant: Outstanding Accounts Require Client Follow-Up
                        </h3>
                        <p class="mt-1 text-xs text-amber-900/80 dark:text-amber-300/80 leading-relaxed" style="font-size: 0.8125rem; margin-top: 0.25rem;">
                            There are <strong>{{ $stats['warningCount'] }} purchase order(s)</strong> due within the 10-day settlement window (₱{{ number_format($stats['warningAmount'], 2) }})
                            @if($stats['overdueCount'] > 0)
                                and <strong>{{ $stats['overdueCount'] }} overdue order(s)</strong> past the strict 30-day limit (₱{{ number_format($stats['overdueAmount'], 2) }}).
                            @else
                                requiring proactive reminder dispatch.
                            @endif
                            Use the interactive <strong>Email Dispatch Center</strong> below or the <strong>"Email Client"</strong> action in the ledger to dispatch reminders.
                        </p>
                    </div>
                </div>
                <div class="shrink-0 self-start sm:self-center" style="flex-shrink: 0;">
                    <x-filament::badge color="warning" size="lg">
                        {{ $stats['warningCount'] + $stats['overdueCount'] }} Action Items
                    </x-filament::badge>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200/80 bg-emerald-50/80 p-3.5 shadow-sm dark:border-emerald-800/40 dark:bg-emerald-950/20"
                 style="border-radius: 0.75rem; padding: 0.875rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                <x-filament::icon
                    icon="heroicon-o-check-circle"
                    class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0"
                    style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;"
                />
                <span class="text-xs font-medium text-emerald-900 dark:text-emerald-300" style="font-size: 0.8125rem; font-weight: 500;">
                    All accounts receivable are within healthy settlement windows. Zero overdue orders detected.
                </span>
            </div>
        @endif

        {{-- 2. Dedicated Client Email Communications & Payment Reminder Dispatch Center --}}
        <x-filament::section
            icon="heroicon-o-envelope"
            collapsible
        >
            <x-slot name="heading">
                Client Payment Reminder Dispatch Center & Ready-to-Send Template
            </x-slot>
            <x-slot name="description">
                Interactive email console with pre-formatted corporate templates and daily anti-spam protection (1 email per PO per day).
            </x-slot>

            <div class="space-y-4 pt-2" style="display: flex; flex-direction: column; gap: 1rem;">
                {{-- Account / PO Selector Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                    <div class="lg:col-span-2">
                        <label for="reminder-po-select" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" style="font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.25rem;">
                            Select Outstanding Purchase Order / Account:
                        </label>
                        <select
                            id="reminder-po-select"
                            wire:model.live="selectedPoId"
                            class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            style="width: 100%; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid #d1d5db;"
                        >
                            <option value="">— Select an Outstanding PO to Load Template —</option>
                            @foreach($pendingOrders as $order)
                                @php
                                    $dueStr = $order->payment_due_date ? $order->payment_due_date->format('M d, Y') : 'No due date';
                                    $urgencyTag = $order->days_until_due !== null
                                        ? ($order->days_until_due < 0 ? ' [OVERDUE ' . abs($order->days_until_due) . 'd]' : ($order->days_until_due <= 10 ? ' [DUE IN ' . $order->days_until_due . 'd]' : ''))
                                        : '';
                                @endphp
                                <option value="{{ $order->id }}">
                                    PO #{{ $order->po_number }} • {{ $order->customer_name }} • ₱{{ number_format((float) $order->order_amount, 2) }} • Due: {{ $dueStr }}{{ $urgencyTag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selected Account Quick Status Cards --}}
                    @if($selectedPo)
                        <div class="rounded-lg border border-gray-200 bg-gray-50/70 p-3.5 dark:border-white/10 dark:bg-white/5" style="border-radius: 0.5rem; padding: 0.875rem;">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400" style="font-size: 0.75rem; color: #6b7280;">Account Status</span>
                                <x-filament::badge :color="$selectedPo->due_status_color" size="sm">
                                    {{ $selectedPo->isPaid() ? 'Paid' : ($selectedPo->days_until_due !== null && $selectedPo->days_until_due < 0 ? 'Overdue' : ($selectedPo->days_until_due <= 10 ? 'Due in ≤ 10 Days' : 'Unpaid')) }}
                                </x-filament::badge>
                            </div>
                            <div class="mt-2 text-sm font-bold text-gray-900 dark:text-white" style="font-size: 0.9375rem; font-weight: 700;">
                                {{ $selectedPo->customer_name }}
                            </div>
                            <div class="mt-1 flex items-center justify-between text-xs text-gray-600 dark:text-gray-400" style="font-size: 0.8125rem;">
                                <span>Amount: ₱{{ number_format((float) $selectedPo->order_amount, 2) }}</span>
                                <span>Terms: {{ $selectedPo->payment_terms ?: '30 Days' }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Template Composer & Dispatcher --}}
                @if($selectedPo)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900" style="border-radius: 0.75rem; padding: 1rem; border: 1px solid #e5e7eb;">
                        {{-- Anti-Spam Security Strip --}}
                        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3 dark:border-gray-800" style="margin-bottom: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                            <div class="flex items-center gap-2">
                                <x-filament::icon icon="heroicon-m-shield-check" class="h-4 w-4 text-primary-600 dark:text-primary-400" style="width: 1rem; height: 1rem;" />
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300" style="font-size: 0.8125rem;">
                                    Corporate Anti-Spam Protocol (Max 1 Send / Day / Account)
                                </span>
                            </div>
                            <div>
                                @if($selectedPo->canSendPaymentReminderToday())
                                    <x-filament::badge color="success" icon="heroicon-m-check-circle">
                                        Ready to Send (No reminder sent today)
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="warning" icon="heroicon-m-clock">
                                        Dispatched Today at {{ $selectedPo->last_payment_reminder_sent_at?->format('h:i A') }} (Next available tomorrow)
                                    </x-filament::badge>
                                @endif
                            </div>
                        </div>

                        {{-- Recipient & Subject --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" style="font-size: 0.8125rem; font-weight: 600;">
                                    Client Recipient Email:
                                </label>
                                <input
                                    type="email"
                                    wire:model="emailRecipient"
                                    class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    style="width: 100%; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid #d1d5db;"
                                    placeholder="client@company.com"
                                    @disabled(!$selectedPo->canSendPaymentReminderToday())
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" style="font-size: 0.8125rem; font-weight: 600;">
                                    Subject Line:
                                </label>
                                <input
                                    type="text"
                                    wire:model="emailSubject"
                                    class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    style="width: 100%; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid #d1d5db;"
                                    @disabled(!$selectedPo->canSendPaymentReminderToday())
                                />
                            </div>
                        </div>

                        {{-- Email Body Textarea --}}
                        <div class="mb-4" style="margin-bottom: 1rem;">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300" style="font-size: 0.8125rem; font-weight: 600;">
                                    Email Message Template (Ready to Send):
                                </label>
                                <span class="text-xs text-gray-400" style="font-size: 0.75rem; color: #9ca3af;">Editable before dispatch</span>
                            </div>
                            <textarea
                                wire:model="emailBody"
                                rows="8"
                                class="w-full font-mono text-xs rounded-lg border-gray-300 bg-gray-50/50 p-3 text-gray-800 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200"
                                style="width: 100%; border-radius: 0.5rem; padding: 0.75rem; font-size: 0.8125rem; font-family: monospace; border: 1px solid #d1d5db; line-height: 1.5;"
                                @disabled(!$selectedPo->canSendPaymentReminderToday())
                            ></textarea>
                        </div>

                        {{-- Dispatch Action Footer --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2" style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
                            <div class="text-xs text-gray-500 dark:text-gray-400" style="font-size: 0.8125rem; color: #6b7280;">
                                Clicking will send this email immediately via corporate SMTP and record the timestamp in the ledger.
                            </div>

                            <button
                                type="button"
                                wire:click="sendEmailReminderFromSection"
                                wire:loading.attr="disabled"
                                @disabled(!$selectedPo->canSendPaymentReminderToday())
                                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 0.5rem; background-color: #2563eb; color: white; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer;"
                            >
                                <span wire:loading wire:target="sendEmailReminderFromSection" class="inline-block animate-spin">
                                    <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" style="width: 1rem; height: 1rem;" />
                                </span>
                                <span wire:loading.remove wire:target="sendEmailReminderFromSection">
                                    <x-filament::icon icon="heroicon-m-paper-airplane" class="h-4 w-4" style="width: 1rem; height: 1rem;" />
                                </span>
                                <span>Send 1-Click Payment Reminder</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400" style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 1.5rem; text-align: center;">
                        <x-filament::icon icon="heroicon-o-inbox-arrow-down" class="mx-auto h-8 w-8 text-gray-400 mb-2" style="width: 2rem; height: 2rem; margin: 0 auto 0.5rem auto;" />
                        <p>Select any outstanding purchase order above to automatically generate a tailored payment follow-up email.</p>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- 3. Interactive Ledger Navigation Tabs (Admin Theme Harmonized) --}}
        <div class="pt-2">
            <div class="inline-flex items-center gap-1.5 p-1 rounded-xl border border-gray-200 bg-gray-100/80 dark:border-white/10 dark:bg-gray-900/80"
                 style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem; border-radius: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.1); background-color: rgba(15, 23, 42, 0.6);">
                {{-- Tab 1: All Accounts & Transactions --}}
                <button
                    type="button"
                    wire:click="setActiveTab('all')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-all"
                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer; border: none; outline: none; transition: all 150ms ease;
                           {{ $this->activeTab === 'all'
                               ? 'background-color: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);'
                               : 'background-color: transparent; color: #94a3b8; border: 1px solid transparent;' }}"
                >
                    <x-filament::icon icon="heroicon-m-banknotes" class="h-4 w-4 shrink-0" style="width: 1rem; height: 1rem;" />
                    <span>All Accounts & Transactions</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;
                                 {{ $this->activeTab === 'all' ? 'background-color: rgba(59, 130, 246, 0.3); color: #bfdbfe;' : 'background-color: rgba(148, 163, 184, 0.15); color: #94a3b8;' }}">
                        {{ $stats['totalOrders'] }}
                    </span>
                </button>

                {{-- Tab 2: Action Required --}}
                <button
                    type="button"
                    wire:click="setActiveTab('follow_up')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-all"
                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer; border: none; outline: none; transition: all 150ms ease;
                           {{ $this->activeTab === 'follow_up'
                               ? 'background-color: rgba(217, 119, 6, 0.2); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.4); box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);'
                               : 'background-color: transparent; color: #94a3b8; border: 1px solid transparent;' }}"
                >
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-4 w-4 shrink-0" style="width: 1rem; height: 1rem;" />
                    <span>Action Required (≤ 10d & Overdue)</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;
                                 {{ $this->activeTab === 'follow_up' ? 'background-color: rgba(245, 158, 11, 0.3); color: #fde68a;' : 'background-color: rgba(217, 119, 6, 0.15); color: #f59e0b;' }}">
                        {{ $stats['warningCount'] + $stats['overdueCount'] }}
                    </span>
                </button>

                {{-- Tab 3: Settled & Cleared --}}
                <button
                    type="button"
                    wire:click="setActiveTab('payment_history')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg transition-all"
                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer; border: none; outline: none; transition: all 150ms ease;
                           {{ $this->activeTab === 'payment_history'
                               ? 'background-color: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);'
                               : 'background-color: transparent; color: #94a3b8; border: 1px solid transparent;' }}"
                >
                    <x-filament::icon icon="heroicon-m-check-badge" class="h-4 w-4 shrink-0" style="width: 1rem; height: 1rem;" />
                    <span>Payment History (Settled & Cleared)</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;
                                 {{ $this->activeTab === 'payment_history' ? 'background-color: rgba(16, 185, 129, 0.3); color: #a7f3d0;' : 'background-color: rgba(34, 197, 94, 0.15); color: #22c55e;' }}">
                        {{ $stats['paidCount'] }}
                    </span>
                </button>
            </div>
        </div>

        {{-- 4. Receivables & Payment Settlement Ledger Table --}}
        <section aria-label="Purchase Order Receivables & Settlement Ledger" class="w-full">
            {{ $this->table }}
        </section>

        {{-- 5. Treasury & Credit Terms Operational Policy Section --}}
        <x-filament::section
            icon="heroicon-o-shield-check"
            collapsible
        >
            <x-slot name="heading">
                Credit & Payment Terms Operational Policy
            </x-slot>
            <x-slot name="description">
                Treasury compliance rules, settlement milestones, and client communications standards
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
                <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-white/10 dark:bg-white/5" style="border-radius: 0.5rem; padding: 1rem;">
                    <div class="flex items-center gap-2 text-sm font-semibold text-success-600 dark:text-success-400" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600;">
                        <x-filament::icon icon="heroicon-m-check-circle" class="h-5 w-5 shrink-0 text-success-600 dark:text-success-400" style="width: 1.25rem; height: 1.25rem;" />
                        COD & PDC Terms (7, 15, 30 Days)
                    </div>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400 leading-relaxed" style="font-size: 0.8125rem; margin-top: 0.5rem; line-height: 1.5;">
                        Considered cleared and settled upon check issuance or signed delivery receipt endorsement. Auto-marked as Paid in the sales and inventory ledgers.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-white/10 dark:bg-white/5" style="border-radius: 0.5rem; padding: 1rem;">
                    <div class="flex items-center gap-2 text-sm font-semibold text-warning-600 dark:text-warning-400" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600;">
                        <x-filament::icon icon="heroicon-m-clock" class="h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400" style="width: 1.25rem; height: 1.25rem;" />
                        30-Day Credit Term (Manual Counter)
                    </div>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400 leading-relaxed" style="font-size: 0.8125rem; margin-top: 0.5rem; line-height: 1.5;">
                        Strict 30-day limit from actual delivery date. 10 days before the due date, accounting is alerted with an action badge to dispatch reminders.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-white/10 dark:bg-white/5" style="border-radius: 0.5rem; padding: 1rem;">
                    <div class="flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-400" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600;">
                        <x-filament::icon icon="heroicon-m-envelope" class="h-5 w-5 shrink-0 text-primary-600 dark:text-primary-400" style="width: 1.25rem; height: 1.25rem;" />
                        Anti-Spam Email Protection
                    </div>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400 leading-relaxed" style="font-size: 0.8125rem; margin-top: 0.5rem; line-height: 1.5;">
                        Strict limit of 1 email reminder per PO per calendar day to protect corporate domain reputation and maintain high delivery rates.
                    </p>
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
