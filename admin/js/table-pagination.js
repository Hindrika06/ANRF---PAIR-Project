/**
 * Table Pagination Module
 * ─────────────────────────────────────────────────────────
 * Drop-in client-side pagination for admin dashboard tables.
 *
 * Usage (auto-init):
 *   Add data-paginate="true" to any <table>.
 *   The script finds the nearest ".d-flex … border-top" footer
 *   and replaces its static pagination with a working one.
 *
 * Manual init:
 *   initTablePagination(tableEl, footerEl);
 */

(function () {
    'use strict';

    var PAGE_SIZES = [5, 10, 15, 20, 25, 30, 40, 50];
    var DEFAULT_SIZE = 5;

    /**
     * Core: wire up pagination for one table + its footer bar.
     */
    function initTablePagination(table, footer) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var allRows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        // Exclude the "no records" placeholder row from pagination
        var dataRows = allRows.filter(function (r) {
            return !r.querySelector('td[colspan]');
        });

        // If there are no real data rows, leave the table as-is
        if (dataRows.length === 0) return;

        var currentPage = 1;
        var pageSize = DEFAULT_SIZE;

        // ── Build "Show entries" selector ──────────────────────────
        var showEntriesWrap = document.createElement('div');
        showEntriesWrap.className = 'd-flex align-items-center gap-2';
        showEntriesWrap.style.cssText = 'font-size:13px; color:#64748b; font-weight:500;';

        var labelBefore = document.createElement('span');
        labelBefore.textContent = 'Show';

        var select = document.createElement('select');
        select.className = 'form-select form-select-sm';
        select.style.cssText = 'width:auto; display:inline-block; min-width:64px; font-size:13px; font-weight:600; color:#334155; border:1px solid #cbd5e1; border-radius:6px; padding:4px 28px 4px 10px; cursor:pointer;';

        PAGE_SIZES.forEach(function (s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            if (s === DEFAULT_SIZE) opt.selected = true;
            select.appendChild(opt);
        });

        var labelAfter = document.createElement('span');
        labelAfter.textContent = 'entries';

        showEntriesWrap.appendChild(labelBefore);
        showEntriesWrap.appendChild(select);
        showEntriesWrap.appendChild(labelAfter);

        // ── Insert the selector into the card-header area ─────────
        // Find the card-header (title + add button row) above the table
        var card = table.closest('.card, .registry-card');
        var cardHeader = card ? card.querySelector('.card-header') : null;

        if (cardHeader) {
            // Insert between header and table, as a sub-bar
            var entriesBar = document.createElement('div');
            entriesBar.className = 'd-flex align-items-center px-3 py-2 bg-white';
            entriesBar.style.cssText = 'border-bottom: 1px solid #f1f5f9;';
            entriesBar.appendChild(showEntriesWrap);

            // Insert right after the card-header
            var tableResponsive = card.querySelector('.table-responsive');
            if (tableResponsive) {
                tableResponsive.parentNode.insertBefore(entriesBar, tableResponsive);
            }
        }

        // ── Footer: total text + pagination nav ───────────────────
        var totalText = footer.querySelector('p');
        var navContainer = footer.querySelector('nav');

        // Clear the static pagination
        if (navContainer) {
            navContainer.innerHTML = '';
        } else {
            navContainer = document.createElement('nav');
            navContainer.setAttribute('aria-label', 'Pagination control block');
            footer.appendChild(navContainer);
        }

        // ── Render logic ──────────────────────────────────────────
        function render() {
            var totalVisible = 0;
            // Count visible (non-filtered) rows
            var visibleRows = dataRows.filter(function (r) {
                return r.getAttribute('data-pagination-filtered') !== 'true';
            });
            totalVisible = visibleRows.length;

            var totalPages = Math.max(1, Math.ceil(totalVisible / pageSize));
            if (currentPage > totalPages) currentPage = totalPages;

            var start = (currentPage - 1) * pageSize;
            var end = start + pageSize;

            // Hide all data rows first
            dataRows.forEach(function (r) { r.style.display = 'none'; });

            // Show only the current page's rows (among visible/non-filtered)
            var snoCounter = start + 1;
            visibleRows.forEach(function (r, i) {
                if (i >= start && i < end) {
                    r.style.display = '';
                    // Update S.No badge if present
                    var badge = r.querySelector('.index-badge-circle');
                    if (badge) badge.textContent = snoCounter;
                    snoCounter++;
                }
            });

            // Update total text
            if (totalText) {
                var originalLabel = totalText.getAttribute('data-original-label');
                if (!originalLabel) {
                    originalLabel = totalText.textContent.replace(/Total:\s*\d+\s*/, '').trim();
                    totalText.setAttribute('data-original-label', originalLabel);
                }
                totalText.innerHTML = 'Total: <strong>' + totalVisible + '</strong> ' + originalLabel;
            }

            // Build pagination buttons
            navContainer.innerHTML = '';
            if (totalPages <= 1) return;

            var ul = document.createElement('ul');
            ul.className = 'pagination pagination-sm mb-0 pagination-theme-sapphire';

            // Prev button
            var prevLi = document.createElement('li');
            prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
            var prevA = document.createElement('a');
            prevA.className = 'page-link';
            prevA.href = 'javascript:void(0);';
            prevA.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
            prevA.addEventListener('click', function () {
                if (currentPage > 1) { currentPage--; render(); }
            });
            prevLi.appendChild(prevA);
            ul.appendChild(prevLi);

            // Page numbers (show max 5 pages with ellipsis)
            var pages = getPageRange(currentPage, totalPages);
            pages.forEach(function (p) {
                var li = document.createElement('li');
                if (p === '...') {
                    li.className = 'page-item disabled';
                    var span = document.createElement('span');
                    span.className = 'page-link';
                    span.textContent = '…';
                    li.appendChild(span);
                } else {
                    li.className = 'page-item' + (p === currentPage ? ' active' : '');
                    var a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = 'javascript:void(0);';
                    a.textContent = p;
                    (function (pageNum) {
                        a.addEventListener('click', function () {
                            currentPage = pageNum;
                            render();
                        });
                    })(p);
                    li.appendChild(a);
                }
                ul.appendChild(li);
            });

            // Next button
            var nextLi = document.createElement('li');
            nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
            var nextA = document.createElement('a');
            nextA.className = 'page-link';
            nextA.href = 'javascript:void(0);';
            nextA.innerHTML = '<i class="fa-solid fa-angle-right"></i>';
            nextA.addEventListener('click', function () {
                if (currentPage < totalPages) { currentPage++; render(); }
            });
            nextLi.appendChild(nextA);
            ul.appendChild(nextLi);

            navContainer.appendChild(ul);
        }

        function getPageRange(current, total) {
            if (total <= 7) {
                var arr = [];
                for (var i = 1; i <= total; i++) arr.push(i);
                return arr;
            }
            var pages = [];
            if (current <= 3) {
                pages = [1, 2, 3, 4, '...', total];
            } else if (current >= total - 2) {
                pages = [1, '...', total - 3, total - 2, total - 1, total];
            } else {
                pages = [1, '...', current - 1, current, current + 1, '...', total];
            }
            return pages;
        }

        // ── Page size change ──────────────────────────────────────
        select.addEventListener('change', function () {
            pageSize = parseInt(this.value, 10);
            currentPage = 1;
            render();
        });

        // ── Integrate with header search (if present) ─────────────
        // The header.php search filters rows by toggling display.
        // We hook into it via MutationObserver to keep pagination in sync.
        function hookSearch() {
            // Watch for the global search input
            var searchInput = document.querySelector('.header-right .input-group input[type="text"]');
            if (!searchInput) return;

            searchInput.addEventListener('input', function () {
                var term = this.value.toLowerCase().trim();
                dataRows.forEach(function (r) {
                    var text = r.textContent.toLowerCase();
                    if (term === '' || text.indexOf(term) !== -1) {
                        r.setAttribute('data-pagination-filtered', 'false');
                    } else {
                        r.setAttribute('data-pagination-filtered', 'true');
                    }
                });
                currentPage = 1;
                render();
            });
        }

        // Initial render
        render();
        hookSearch();
    }

    // ── Auto-init on DOMContentLoaded ─────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        var tables = document.querySelectorAll('table[data-paginate="true"]');
        tables.forEach(function (table) {
            // Find the footer bar (the d-flex border-top div after the table)
            var card = table.closest('.card, .registry-card');
            if (!card) return;
            var footer = card.querySelector('.d-flex.border-top');
            if (!footer) return;
            initTablePagination(table, footer);
        });
    });
})();
