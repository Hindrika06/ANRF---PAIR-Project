/**
 * ANRF-PAIR Admin Portal — Real-time Approval Notifications & Toast Controller
 */

(function (window, document) {
    'use strict';

    // ── Toast Notification System ──
    const ANRFToast = {
        container: null,

        initContainer: function () {
            if (!this.container) {
                let c = document.getElementById('anrfToastContainer');
                if (!c) {
                    c = document.createElement('div');
                    c.id = 'anrfToastContainer';
                    c.className = 'anrf-toast-container';
                    document.body.appendChild(c);
                }
                this.container = c;
            }
        },

        show: function (options) {
            this.initContainer();

            const title = options.title || 'Notification';
            const message = options.message || '';
            const type = options.type || 'info'; // success, danger, warning, info
            const link = options.link || null;
            const duration = options.duration || 5000;

            const toast = document.createElement('div');
            toast.className = `anrf-toast anrf-toast-${type}`;

            let iconHtml = '<i class="fas fa-info-circle"></i>';
            if (type === 'success') iconHtml = '<i class="fas fa-check-circle"></i>';
            if (type === 'danger') iconHtml = '<i class="fas fa-exclamation-circle"></i>';
            if (type === 'warning') iconHtml = '<i class="fas fa-bell"></i>';

            toast.innerHTML = `
                <div class="anrf-toast-icon">${iconHtml}</div>
                <div class="anrf-toast-content">
                    <div class="anrf-toast-title">${escapeHtml(title)}</div>
                    <div class="anrf-toast-message">${escapeHtml(message)}</div>
                </div>
                <button type="button" class="anrf-toast-close" aria-label="Close">&times;</button>
            `;

            if (link) {
                toast.style.cursor = 'pointer';
                toast.addEventListener('click', function (e) {
                    if (e.target.classList.contains('anrf-toast-close')) return;
                    window.location.href = link;
                });
            }

            const closeBtn = toast.querySelector('.anrf-toast-close');
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                toast.classList.add('anrf-toast-hiding');
                setTimeout(() => toast.remove(), 300);
            });

            this.container.appendChild(toast);

            // Animate entrance
            setTimeout(() => toast.classList.add('anrf-toast-show'), 10);

            // Auto dismiss
            if (duration > 0) {
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.classList.add('anrf-toast-hiding');
                        setTimeout(() => toast.remove(), 300);
                    }
                }, duration);
            }
        }
    };

    // ── Real-Time Notification Bell & Dropdown Controller ──
    const ANRFNotifications = {
        bellBtn: null,
        badgeElem: null,
        dropdownMenu: null,
        lastPendingCount: null,
        isSuperAdmin: false,

        init: function () {
            this.bellBtn = document.querySelector('.nav-notification-bell-btn');
            this.dropdownMenu = document.getElementById('notificationDropdownMenu');

            if (!this.bellBtn) return;

            // Ensure bell button container positioning
            const parentLi = this.bellBtn.closest('li');
            if (parentLi) {
                parentLi.style.position = 'relative';
            }

            // Create badge element if not present
            this.badgeElem = this.bellBtn.querySelector('.notification-badge');
            if (!this.badgeElem) {
                this.badgeElem = document.createElement('span');
                this.badgeElem.className = 'notification-badge';
                this.badgeElem.style.display = 'none';
                this.bellBtn.appendChild(this.badgeElem);
            }

            // Toggle dropdown click listener
            this.bellBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (this.dropdownMenu) {
                    const isShown = this.dropdownMenu.classList.contains('show');
                    if (isShown) {
                        this.hideDropdown();
                    } else {
                        // Close profile dropdown if open
                        const profileMenu = document.getElementById('profileDropdownMenu');
                        if (profileMenu) profileMenu.classList.remove('show');
                        this.showDropdown();
                        this.fetchNotifications();
                    }
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (this.dropdownMenu && !this.dropdownMenu.contains(e.target) && !this.bellBtn.contains(e.target)) {
                    this.hideDropdown();
                }
            });

            // Initial fetch & set up 20-second interval polling
            this.fetchNotifications(true);
            setInterval(() => this.fetchNotifications(false), 20000);
        },

        showDropdown: function () {
            if (this.dropdownMenu) {
                this.dropdownMenu.classList.add('show');
                this.bellBtn.setAttribute('aria-expanded', 'true');
            }
        },

        hideDropdown: function () {
            if (this.dropdownMenu) {
                this.dropdownMenu.classList.remove('show');
                this.bellBtn.setAttribute('aria-expanded', 'false');
            }
        },

        fetchNotifications: function (isInitial = false) {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        this.isSuperAdmin = data.is_super;
                        this.updateBadge(data.pending_count);
                        this.renderDropdown(data.notifications, data.pending_count);

                        // If Super Admin and new pending approvals arrived, trigger toast popup!
                        if (!isInitial && this.isSuperAdmin && this.lastPendingCount !== null) {
                            if (data.pending_count > this.lastPendingCount) {
                                const newCount = data.pending_count - this.lastPendingCount;
                                ANRFToast.show({
                                    title: '🔔 New Approval Request Received!',
                                    message: `${newCount} new approval request(s) awaiting your review.`,
                                    type: 'warning',
                                    link: 'approvals.php',
                                    duration: 7000
                                });
                            }
                        }

                        this.lastPendingCount = data.pending_count;
                    }
                })
                .catch(err => {
                    console.warn('Notifications fetch error:', err);
                });
        },

        updateBadge: function (count) {
            if (!this.badgeElem) return;
            if (count > 0) {
                this.badgeElem.textContent = count > 99 ? '99+' : count;
                this.badgeElem.style.display = 'inline-flex';
            } else {
                this.badgeElem.style.display = 'none';
            }
        },

        renderDropdown: function (notifications, pendingCount) {
            if (!this.dropdownMenu) return;

            let html = `
                <div class="dropdown-header-custom d-flex justify-content-between align-items-center" style="padding: 14px 16px; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
                    <div style="font-weight: 700; font-size: 15px; color: #0f172a;">
                        <i class="fas fa-bell me-2" style="color: #bc2121;"></i> Notifications
                    </div>
                    ${pendingCount > 0 ? `<span class="badge bg-danger rounded-pill" style="font-size: 11px;">${pendingCount} Pending</span>` : '<span class="badge bg-success rounded-pill" style="font-size: 11px;">All Clear</span>'}
                </div>
                <div class="notification-list-scroll" style="max-height: 330px; overflow-y: auto;">
            `;

            if (!notifications || notifications.length === 0) {
                html += `
                    <div class="text-center text-muted py-4 px-3" style="font-size: 13px;">
                        <i class="fas fa-check-circle text-success mb-2" style="font-size: 24px; display: block;"></i>
                        No recent approval notifications.
                    </div>
                `;
            } else {
                notifications.forEach(n => {
                    let badgeClass = 'bg-secondary';
                    if (n.status === 'Pending') badgeClass = 'bg-warning text-dark';
                    if (n.status === 'Approved') badgeClass = 'bg-success text-white';
                    if (n.status === 'Rejected') badgeClass = 'bg-danger text-white';

                    html += `
                        <a href="${n.link}" class="notification-item d-flex align-items-start gap-2 p-3 text-decoration-none" style="border-bottom: 1px solid #f8fafc; transition: background 0.2s ease;">
                            <div class="notification-item-icon mt-1">
                                ${n.status === 'Approved' ? '<i class="fas fa-check-circle text-success" style="font-size: 16px;"></i>' : (n.status === 'Rejected' ? '<i class="fas fa-times-circle text-danger" style="font-size: 16px;"></i>' : '<i class="fas fa-clock text-warning" style="font-size: 16px;"></i>')}
                            </div>
                            <div class="notification-item-body flex-grow-1" style="line-height: 1.3;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong style="font-size: 13px; color: #1e293b;">${escapeHtml(n.title)}</strong>
                                    <span class="badge ${badgeClass}" style="font-size: 9px; padding: 2px 6px;">${escapeHtml(n.status)}</span>
                                </div>
                                <div style="font-size: 12px; color: #475569; margin-bottom: 4px;">${escapeHtml(n.message)}</div>
                                <div style="font-size: 10px; color: #94a3b8;"><i class="far fa-clock me-1"></i> ${escapeHtml(n.timestamp)}</div>
                            </div>
                        </a>
                    `;
                });
            }

            html += `
                </div>
                <div class="dropdown-footer-custom text-center p-2" style="background: #f8fafc; border-top: 1px solid #f1f5f9;">
                    <a href="approvals.php" class="text-decoration-none font-weight-bold" style="font-size: 12px; color: #024283;">
                        View All Approval Logs <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            `;

            this.dropdownMenu.innerHTML = html;
        }
    };

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Expose APIs
    window.ANRFToast = ANRFToast;
    window.ANRFNotifications = ANRFNotifications;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ANRFNotifications.init());
    } else {
        ANRFNotifications.init();
    }
})(window, document);
