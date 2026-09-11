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
                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Editable before dispatch</span>
                            </div>
                            <textarea
                                wire:model="emailBody"
                                rows="8"
                                class="w-full font-mono text-xs rounded-lg border-gray-300 bg-gray-50/50 p-3 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
                                style="width: 100%; border-radius: 0.5rem; padding: 0.75rem; font-size: 0.8125rem; font-family: monospace; border: 1px solid #d1d5db; line-height: 1.5;"
                                @disabled(!$selectedPo->canSendPaymentReminderToday())
                            ></textarea>
                        </div>

                        {{-- Dispatch Action Footer --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2" style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
                            <div class="text-xs text-gray-700 dark:text-gray-300 font-medium">
                                Clicking will send this email immediately via corporate SMTP and record the timestamp in the ledger.
                            </div>

                            @if($selectedPo->canSendPaymentReminderToday())
                                <button
                                    type="button"
                                    wire:click="sendEmailReminderFromSection"
                                    wire:loading.attr="disabled"
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
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold shadow-sm cursor-not-allowed bg-slate-100 text-slate-500 border border-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700"
                                    style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 0.5rem; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; cursor: not-allowed;"
                                    title="Anti-spam safeguard: A reminder was already sent today for PO #{{ $selectedPo->po_number }}."
                                >
                                    <x-filament::icon icon="heroicon-m-check-circle" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" style="width: 1rem; height: 1rem;" />
                                    <span>Reminder Dispatched Today (Limit Reached)</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300" style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 1.5rem; text-align: center;">
                        <x-filament::icon icon="heroicon-o-inbox-arrow-down" class="mx-auto h-8 w-8 text-gray-500 dark:text-gray-400 mb-2" style="width: 2rem; height: 2rem; margin: 0 auto 0.5rem auto;" />
                        <p class="font-medium">Select any outstanding purchase order above to automatically generate a tailored payment follow-up email.</p>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- 3. Interactive Ledger Navigation Tabs (Vercel/Linear Segmented Control) --}}
        <div class="pt-2">
            <style>
                .huenics-ledger-tab-bar {
                    display: inline-flex;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 6px;
                    padding: 5px;
                    border-radius: 12px;
                    background-color: #f1f5f9;
                    border: 1px solid #e2e8f0;
                    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
                }
                .dark .huenics-ledger-tab-bar,
                :is(.dark) .huenics-ledger-tab-bar {
                    background-color: rgba(15, 23, 42, 0.75);
                    border: 1px solid rgba(255, 255, 255, 0.09);
                    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.35);
                }
                .huenics-ledger-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 8px 14px;
                    font-size: 12px;
                    font-weight: 700;
                    border-radius: 8px;
                    cursor: pointer;
                    border: 1px solid transparent;
                    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
                    user-select: none;
                    line-height: 1.25;
                    background: transparent;
                    color: #64748b;
                    text-decoration: none;
                }
                .dark .huenics-ledger-btn,
                :is(.dark) .huenics-ledger-btn {
                    color: #94a3b8;
                }
                .huenics-ledger-btn:hover {
                    color: #0f172a;
                    background-color: rgba(0, 0, 0, 0.04);
                }
                .dark .huenics-ledger-btn:hover,
                :is(.dark) .huenics-ledger-btn:hover {
                    color: #f8fafc;
                    background-color: rgba(255, 255, 255, 0.06);
                }
                .huenics-ledger-btn.is-active-all {
                    background-color: #ffffff;
                    color: #0284c7;
                    border-color: #cbd5e1;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
                }
                .dark .huenics-ledger-btn.is-active-all,
                :is(.dark) .huenics-ledger-btn.is-active-all {
                    background-color: #1e293b;
                    color: #38bdf8;
                    border-color: rgba(56, 189, 248, 0.35);
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(56, 189, 248, 0.15);
                }
                .huenics-ledger-btn.is-active-action {
                    background-color: #ffffff;
                    color: #d97706;
                    border-color: rgba(217, 119, 6, 0.35);
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
                }
                .dark .huenics-ledger-btn.is-active-action,
                :is(.dark) .huenics-ledger-btn.is-active-action {
                    background-color: #1e293b;
                    color: #fbbf24;
                    border-color: rgba(251, 191, 36, 0.4);
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(251, 191, 36, 0.18);
                }
                .huenics-ledger-btn.is-active-settled {
                    background-color: #ffffff;
                    color: #16a34a;
                    border-color: rgba(22, 163, 74, 0.35);
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
                }
                .dark .huenics-ledger-btn.is-active-settled,
                :is(.dark) .huenics-ledger-btn.is-active-settled {
                    background-color: #1e293b;
                    color: #4ade80;
                    border-color: rgba(74, 222, 128, 0.4);
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(74, 222, 128, 0.18);
                }
                .huenics-tab-count {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 20px;
                    height: 20px;
                    padding: 0 6px;
                    border-radius: 9999px;
                    font-size: 11px;
                    font-weight: 800;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                    line-height: 1;
                    letter-spacing: -0.02em;
                    margin-left: 4px;
                }
                .huenics-pulse-dot {
                    width: 6px;
                    height: 6px;
                    border-radius: 9999px;
                    background-color: #f59e0b;
                    display: inline-block;
                    animation: huenics-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
                }
                @keyframes huenics-ping {
                    75%, 100% {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            </style>

            <div
                class="huenics-ledger-tab-bar"
                style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 6px; padding: 5px; border-radius: 12px; background-color: #f1f5f9; border: 1px solid #e2e8f0;"
            >
                {{-- Tab 1: All Accounts & Transactions --}}
                @php $isAllActive = $this->activeTab === 'all'; @endphp
                <button
                    type="button"
                    wire:click="setActiveTab('all')"
                    class="huenics-ledger-btn {{ $isAllActive ? 'is-active-all' : '' }}"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; border: 1px solid {{ $isAllActive ? '#cbd5e1' : 'transparent' }}; background-color: {{ $isAllActive ? '#ffffff' : 'transparent' }}; color: {{ $isAllActive ? '#0284c7' : '#64748b' }};"
                >
                    <x-filament::icon icon="heroicon-m-banknotes" class="h-4 w-4 shrink-0" style="width: 1rem; height: 1rem; flex-shrink: 0;" />
                    <span>All Accounts & Transactions</span>
                    <span
                        class="huenics-tab-count"
                        style="display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 9999px; font-size: 11px; font-weight: 800; font-family: monospace; background-color: {{ $isAllActive ? '#e0f2fe' : '#e2e8f0' }}; color: {{ $isAllActive ? '#0369a1' : '#475569' }};"
                    >
                        {{ $stats['totalOrders'] }}
                    </span>
                </button>

                {{-- Tab 2: Action Required --}}
                @php
                    $isActionActive = $this->activeTab === 'follow_up';
                    $actionCount = (int) ($stats['warningCount'] + $stats['overdueCount']);
                @endphp
                <button
                    type="button"
                    wire:click="setActiveTab('follow_up')"
                    class="huenics-ledger-btn {{ $isActionActive ? 'is-active-action' : '' }}"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; border: 1px solid {{ $isActionActive ? 'rgba(217, 119, 6, 0.35)' : 'transparent' }}; background-color: {{ $isActionActive ? '#ffffff' : 'transparent' }}; color: {{ $isActionActive ? '#d97706' : '#64748b' }};"
                >
                    @if($actionCount > 0)
                        <span class="huenics-pulse-dot"></span>
                    @endif
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-4 w-4 shrink-0" style="width: 1rem; height: 1rem; flex-shrink: 0;" />
                    <span>Action Required (≤ 10d & Overdue)</span>
                    <span
                        class="huenics-tab-count"
                        style="display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 9999px; font-size: 11px; font-weight: 800; font-family: monospace; background-color: {{ $actionCount > 0 ? ($isActionActive ? '#fef3c7' : 'rgba(251, 191, 36, 0.15)') : ($isActionActive ? '#fef3c7' : '#e2e8f0') }}; color: {{ $actionCount > 0 ? '#b45309' : '#475569' }};"
                    >
                        {{ $actionCount }}
                    </span>
                </button>

                {{-- Tab 3: Settled & Cleared --}}
                @php $isSettledActive = $this->activeTab === 'payment_history'; @endphp
                <button
                    type="button"
                    wire:click="setActiveTab('payment_history')"
                    class="huenics-ledger-btn {{ $isSettledActive ? 'is-active-settled' : '' }}"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; border: 1px solid {{ $isSettledActive ? 'rgba(22, 163, 74, 0.35)' : 'transparent' }}; background-color: {{ $isSettledActive ? '#ffffff' : 'transparent' }}; color: {{ $isSettledActive ? '#16a34a' : '#64748b' }};"
                >
                    <x-filament::icon icon="heroicon-m-check-badge" class="h-4 w-4 shrink-0" style="width: 1rem; height: 1rem; flex-shrink: 0;" />
                    <span>Payment History (Settled & Cleared)</span>
                    <span
                        class="huenics-tab-count"
                        style="display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 9999px; font-size: 11px; font-weight: 800; font-family: monospace; background-color: {{ $isSettledActive ? '#dcfce7' : '#e2e8f0' }}; color: {{ $isSettledActive ? '#15803d' : '#475569' }};"
                    >
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
