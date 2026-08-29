<style>
    /* Ensure toast notifications are positioned above everything with pointer events */
    .fi-no {
        position: fixed !important;
        inset-block-start: 1rem !important;
        inset-inline-end: 1rem !important;
        z-index: 99999 !important;
        pointer-events: none !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-end !important;
        gap: 0.75rem !important;
        max-width: calc(100vw - 2rem) !important;
    }

    .fi-no-notification {
        pointer-events: auto !important;
    }

    /* Standalone Toast Card for Frontend Notification Bridge */
    .fi-toast-card {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        width: 100%;
        max-width: 24rem;
        padding: 0.875rem 1rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.18), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
        border-width: 1px;
        border-style: solid;
        transform: translateX(110%);
        opacity: 0;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
        background-color: #ffffff;
        color: #0f172a;
    }

    html.dark .fi-toast-card {
        background-color: #1e293b;
        color: #f8fafc;
    }

    .fi-toast-card.fi-toast-show {
        transform: translateX(0);
        opacity: 1;
    }

    .fi-toast-card.fi-toast-hide {
        transform: translateX(110%);
        opacity: 0;
    }

    /* Status Colors */
    .fi-toast-card.fi-toast-success {
        border-color: rgba(16, 185, 129, 0.4);
    }
    .fi-toast-card.fi-toast-danger {
        border-color: rgba(239, 68, 68, 0.4);
    }
    .fi-toast-card.fi-toast-warning {
        border-color: rgba(245, 158, 11, 0.4);
    }
    .fi-toast-card.fi-toast-info {
        border-color: rgba(59, 130, 246, 0.4);
    }

    /* Icon styling */
    .fi-toast-icon {
        flex-shrink: 0;
        width: 1.375rem;
        height: 1.375rem;
        margin-top: 0.125rem;
    }
    .fi-toast-success .fi-toast-icon { color: #10b981; }
    .fi-toast-danger .fi-toast-icon { color: #ef4444; }
    .fi-toast-warning .fi-toast-icon { color: #f59e0b; }
    .fi-toast-info .fi-toast-icon { color: #3b82f6; }

    /* Content styling */
    .fi-toast-content {
        flex: 1 1 0%;
        min-width: 0;
    }
    .fi-toast-title {
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.25rem;
        margin: 0;
    }
    .fi-toast-body {
        font-size: 0.8125rem;
        line-height: 1.125rem;
        margin-top: 0.25rem;
        color: #64748b;
    }
    html.dark .fi-toast-body {
        color: #94a3b8;
    }

    /* Close button */
    .fi-toast-close {
        flex-shrink: 0;
        background: transparent;
        border: none;
        padding: 0.25rem;
        cursor: pointer;
        color: #94a3b8;
        border-radius: 0.375rem;
        line-height: 1;
        transition: color 0.15s ease, background-color 0.15s ease;
    }
    .fi-toast-close:hover {
        color: #334155;
        background-color: rgba(0, 0, 0, 0.05);
    }
    html.dark .fi-toast-close:hover {
        color: #f1f5f9;
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>

<script>
    (function () {
        if (window.__filamentToastBridgeInitialized) return;
        window.__filamentToastBridgeInitialized = true;

        const shownNotificationIds = new Set();

        function getOrCreateToastContainer() {
            let container = document.querySelector('.fi-no');
            if (!container) {
                container = document.createElement('div');
                container.className = 'fi-no fi-align-right fi-vertical-align-start';
                container.setAttribute('role', 'status');
                container.setAttribute('aria-atomic', 'false');
                document.body.appendChild(container);
            }
            return container;
        }

        const ICONS = {
            success: `<svg class="fi-toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            danger: `<svg class="fi-toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            warning: `<svg class="fi-toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
            info: `<svg class="fi-toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
        };

        function showToast(notification) {
            if (!notification || typeof notification !== 'object') return;

            const id = notification.id || ('toast_' + Math.random().toString(36).substr(2, 9));
            if (shownNotificationIds.has(id)) return;
            shownNotificationIds.add(id);

            // Evict old IDs to prevent unbounded memory growth
            if (shownNotificationIds.size > 200) {
                const first = shownNotificationIds.values().next().value;
                shownNotificationIds.delete(first);
            }

            const title = notification.title || '';
            const body = notification.body || '';
            let status = (notification.status || notification.color || 'info').toLowerCase();
            if (!ICONS[status]) status = 'info';

            const duration = parseInt(notification.duration, 10) || 6000;
            const container = getOrCreateToastContainer();

            const toast = document.createElement('div');
            toast.className = `fi-toast-card fi-toast-${status}`;
            toast.setAttribute('data-notification-id', id);

            toast.innerHTML = `
                ${ICONS[status]}
                <div class="fi-toast-content">
                    ${title ? `<h3 class="fi-toast-title">${escapeHtml(title)}</h3>` : ''}
                    ${body ? `<div class="fi-toast-body">${escapeHtml(body)}</div>` : ''}
                </div>
                <button type="button" class="fi-toast-close" aria-label="Close notification">
                    <svg style="width: 0.875rem; height: 0.875rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;

            container.appendChild(toast);

            // Trigger smooth slide-in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    toast.classList.add('fi-toast-show');
                });
            });

            let timer = null;
            let remaining = duration;
            let start = Date.now();

            function startTimer() {
                if (duration <= 0 || duration === 'persistent') return;
                start = Date.now();
                timer = setTimeout(dismiss, remaining);
            }

            function pauseTimer() {
                clearTimeout(timer);
                remaining -= Date.now() - start;
            }

            function dismiss() {
                clearTimeout(timer);
                toast.classList.remove('fi-toast-show');
                toast.classList.add('fi-toast-hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }

            toast.querySelector('.fi-toast-close').addEventListener('click', dismiss);
            toast.addEventListener('mouseenter', pauseTimer);
            toast.addEventListener('mouseleave', () => {
                if (remaining > 0) startTimer();
            });

            startTimer();
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Self-heal any native Filament notifications that got stuck in `visibility: hidden`
        function healStuckNotifications() {
            document.querySelectorAll('.fi-no-notification').forEach(el => {
                if (getComputedStyle(el).visibility === 'hidden') {
                    el.style.setProperty('visibility', 'visible', 'important');
                    el.style.setProperty('opacity', '1', 'important');
                    el.style.setProperty('transform', 'none', 'important');
                }
            });
        }

        // 1. Listen for browser CustomEvents
        window.addEventListener('notificationSent', function (e) {
            const data = e.detail?.notification || e.detail;
            showToast(data);
        });

        // 2. Listen for Livewire 3 commit effects
        function registerLivewireHook() {
            if (!window.Livewire?.hook) return;

            Livewire.hook('commit', ({ succeed }) => {
                succeed(({ effect }) => {
                    if (effect && Array.isArray(effect.dispatches)) {
                        effect.dispatches.forEach(d => {
                            if (d.name === 'notificationSent' && d.params?.notification) {
                                showToast(d.params.notification);
                            }
                        });
                    }
                });
            });
        }

        if (window.Livewire) {
            registerLivewireHook();
        } else {
            document.addEventListener('livewire:init', registerLivewireHook);
        }

        // 3. Monitor .fi-no container with MutationObserver to self-heal stuck elements
        const observer = new MutationObserver(() => healStuckNotifications());
        const initObserver = () => {
            const container = document.querySelector('.fi-no');
            if (container) {
                observer.observe(container, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class'] });
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initObserver);
        } else {
            initObserver();
        }

        // Expose bridge globally
        window.FilamentToastBridge = {
            show: showToast,
            heal: healStuckNotifications,
        };
    })();
</script>
