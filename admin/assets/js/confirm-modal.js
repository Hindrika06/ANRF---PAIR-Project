/**
 * ANRF-PAIR Admin Portal — Reusable Confirmation Modal JS Controller
 */

(function (window, document) {
    'use strict';

    let currentCallback = null;
    let previousActiveElement = null;
    let activeType = 'approve';

    // SVG Icons
    const SVG_APPROVE = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>`;

    const SVG_REJECT = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>`;

    function getElements() {
        return {
            backdrop: document.getElementById('anrfConfirmModalBackdrop'),
            card: document.getElementById('anrfModalCard'),
            closeBtn: document.getElementById('anrfModalCloseBtn'),
            iconContainer: document.getElementById('anrfModalIconContainer'),
            title: document.getElementById('anrfModalTitle'),
            message: document.getElementById('anrfModalMessage'),
            textareaGroup: document.getElementById('anrfModalTextareaGroup'),
            textarea: document.getElementById('anrfModalTextarea'),
            cancelBtn: document.getElementById('anrfModalCancelBtn'),
            confirmBtn: document.getElementById('anrfModalConfirmBtn')
        };
    }

    function initListeners() {
        const els = getElements();
        if (!els.backdrop || els.backdrop.dataset.initialized) return;
        els.backdrop.dataset.initialized = 'true';

        // Close button or Cancel button
        if (els.closeBtn) els.closeBtn.addEventListener('click', close);
        if (els.cancelBtn) els.cancelBtn.addEventListener('click', close);

        // Outside backdrop click
        els.backdrop.addEventListener('click', function (e) {
            if (e.target === els.backdrop) {
                close();
            }
        });

        // Keyboard ESC & Focus Trap
        document.addEventListener('keydown', function (e) {
            if (!els.backdrop.classList.contains('anrf-modal-active')) return;

            if (e.key === 'Escape' || e.keyCode === 27) {
                close();
                return;
            }

            if (e.key === 'Tab' || e.keyCode === 9) {
                trapFocus(e, els.card);
            }
        });

        // Textarea input validation for Reject modal
        if (els.textarea) {
            els.textarea.addEventListener('input', function () {
                if (activeType === 'reject') {
                    const hasText = els.textarea.value.trim().length > 0;
                    els.confirmBtn.disabled = !hasText;
                }
            });
        }

        // Confirm button action
        if (els.confirmBtn) {
            els.confirmBtn.addEventListener('click', function () {
                if (els.confirmBtn.disabled) return;
                const callback = currentCallback;
                const reason = els.textarea ? els.textarea.value.trim() : '';
                close();
                if (typeof callback === 'function') {
                    callback(reason);
                }
            });
        }
    }

    function trapFocus(e, container) {
        const focusable = container.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === first) {
                last.focus();
                e.preventDefault();
            }
        } else {
            if (document.activeElement === last) {
                first.focus();
                e.preventDefault();
            }
        }
    }

    function open(options) {
        initListeners();
        const els = getElements();
        if (!els.backdrop) return;

        previousActiveElement = document.activeElement;
        currentCallback = options.onConfirm || null;
        activeType = options.type || 'approve';

        // Reset Textarea
        if (els.textarea) {
            els.textarea.value = '';
        }

        // Set Title & Message
        els.title.textContent = options.title || (activeType === 'approve' ? 'Approve Request?' : 'Reject Request?');
        els.message.textContent = options.message || '';

        // Configure Styling based on type
        els.iconContainer.className = 'anrf-modal-icon-container';
        els.confirmBtn.className = 'anrf-modal-btn';

        if (activeType === 'approve') {
            els.iconContainer.classList.add('anrf-icon-approve');
            els.iconContainer.innerHTML = SVG_APPROVE;
            els.confirmBtn.classList.add('anrf-modal-btn-approve');
            els.confirmBtn.textContent = options.confirmText || 'Approve';
            els.confirmBtn.disabled = false;
            els.textareaGroup.style.display = 'none';
        } else {
            els.iconContainer.classList.add('anrf-icon-reject');
            els.iconContainer.innerHTML = SVG_REJECT;
            els.confirmBtn.classList.add('anrf-modal-btn-reject');
            els.confirmBtn.textContent = options.confirmText || 'Reject';
            els.textareaGroup.style.display = options.showTextarea !== false ? 'block' : 'none';
            if (options.showTextarea !== false) {
                els.confirmBtn.disabled = true; // Disabled until reason entered
                if (els.textarea) {
                    els.textarea.placeholder = options.placeholder || 'Enter rejection reason...';
                }
            } else {
                els.confirmBtn.disabled = false;
            }
        }

        els.cancelBtn.textContent = options.cancelText || 'Cancel';

        // Display Modal with animation
        els.backdrop.setAttribute('aria-hidden', 'false');
        els.backdrop.classList.add('anrf-modal-active');

        // Set Initial Focus
        setTimeout(function () {
            if (activeType === 'reject' && options.showTextarea !== false && els.textarea) {
                els.textarea.focus();
            } else {
                els.confirmBtn.focus();
            }
        }, 50);
    }

    function close() {
        const els = getElements();
        if (!els.backdrop) return;

        els.backdrop.classList.remove('anrf-modal-active');
        els.backdrop.setAttribute('aria-hidden', 'true');

        if (previousActiveElement && typeof previousActiveElement.focus === 'function') {
            previousActiveElement.focus();
        }
        currentCallback = null;
    }

    // Public API
    window.ANRFModal = {
        open: open,
        close: close,
        showApprove: function (options) {
            open(Object.assign({
                type: 'approve',
                title: 'Approve Request?',
                message: 'Are you sure you want to approve this request?\nThis action will publish the submitted record.',
                confirmText: 'Approve',
                cancelText: 'Cancel'
            }, options));
        },
        showReject: function (options) {
            open(Object.assign({
                type: 'reject',
                title: 'Reject Request?',
                message: 'Are you sure you want to reject this request?\nThis action cannot be undone.',
                confirmText: 'Reject',
                cancelText: 'Cancel',
                placeholder: 'Enter rejection reason...'
            }, options));
        },
        confirm: function (options) {
            open(Object.assign({
                type: options.type || 'reject',
                title: options.title || 'Confirm Action?',
                message: options.message || 'Are you sure you want to proceed?',
                confirmText: options.confirmText || 'Confirm',
                cancelText: options.cancelText || 'Cancel',
                showTextarea: options.showTextarea || false
            }, options));
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initListeners);
    } else {
        initListeners();
    }
})(window, document);
