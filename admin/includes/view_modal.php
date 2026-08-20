<!-- ── READ-ONLY KPI DETAILS MODAL COMPONENT ───────────────────────────── -->
<style>
    #kpiViewModal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    #kpiViewModal .modal-header {
        background: #1e293b;
        color: #ffffff;
        padding: 16px 24px;
        border-bottom: 1px solid #334155;
    }

    #kpiViewModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .kpi-view-header-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #ffffff;
    }

    .kpi-view-header-sub {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
        font-weight: 400;
    }

    .kpi-view-badges-container {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .kpi-view-badge-inst {
        background-color: #bc2121;
        color: #ffffff;
        font-weight: 700;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }

    .kpi-view-badge-status {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
    }

    .kpi-view-body {
        padding: 20px 24px;
        background-color: #f8fafc;
        max-height: 75vh;
        overflow-y: auto;
    }

    .kpi-view-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    @media (max-width: 768px) {
        .kpi-view-grid {
            grid-template-columns: 1fr;
        }
    }

    .kpi-view-field-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 14px;
    }

    .kpi-view-field-item.full-width {
        grid-column: 1 / -1;
    }

    .kpi-view-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .kpi-view-value {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.5;
        word-break: break-word;
    }

    .kpi-view-value-longtext {
        font-size: 13px;
        font-weight: 400;
        color: #334155;
        line-height: 1.6;
        white-space: pre-wrap;
        background: #f1f5f9;
        padding: 10px 12px;
        border-radius: 6px;
        border-left: 3px solid #bc2121;
        margin-top: 4px;
    }

    .kpi-view-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .kpi-view-link-btn:hover {
        background-color: #dbeafe;
        color: #1d4ed8;
        text-decoration: none;
    }

    .kpi-view-img-preview {
        max-width: 100%;
        max-height: 220px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        object-fit: contain;
        margin-top: 6px;
        background: #ffffff;
        padding: 4px;
    }

    .kpi-view-close-btn {
        background-color: #bc2121 !important;
        border-color: #bc2121 !important;
        color: #ffffff !important;
        font-weight: 600;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .kpi-view-close-btn:hover,
    .kpi-view-close-btn:focus,
    .kpi-view-close-btn:active {
        background-color: #a31c1c !important;
        border-color: #a31c1c !important;
        color: #ffffff !important;
    }
</style>

<div class="modal fade" id="kpiViewModal" tabindex="-1" aria-labelledby="kpiViewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="kpi-view-header-title" id="kpiViewModalTitle">
                        <i class="fa-solid fa-eye me-1"></i> Record Details
                    </h5>
                    <div class="kpi-view-header-sub" id="kpiViewModalSubtitle"></div>
                    <div class="kpi-view-badges-container" id="kpiViewBadges"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body kpi-view-body">
                <div class="kpi-view-grid" id="kpiViewModalBody">
                    <!-- Fields dynamically populated -->
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-2">
                <button type="button" class="btn btn-sm px-4 kpi-view-close-btn" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Opens and renders the KPI View Modal.
 * @param {Object} options Configuration object:
 *   - moduleTitle: e.g. "Publication Details"
 *   - recordTitle: e.g. "Deep Learning Methods for Quantum Simulation"
 *   - institutePrefix: e.g. "uoh"
 *   - instituteName: e.g. "University of Hyderabad"
 *   - approvalStatus: e.g. "Approved", "Pending", "Rejected"
 *   - publishStatus: e.g. 1 or 0 (optional)
 *   - fields: Array of field definitions: [{ label, value, type, fullWidth, icon }]
 */
function openKpiRecordViewModal(options) {
    const titleElem = document.getElementById('kpiViewModalTitle');
    const subtitleElem = document.getElementById('kpiViewModalSubtitle');
    const badgesElem = document.getElementById('kpiViewBadges');
    const bodyElem = document.getElementById('kpiViewModalBody');

    if (!bodyElem) return;

    // Title & Subtitle
    titleElem.innerHTML = `<i class="fa-solid fa-eye me-1"></i> ${escapeHtml(options.moduleTitle || 'Record Details')}`;
    subtitleElem.innerText = options.recordTitle ? options.recordTitle : '';

    // Badges
    let badgesHtml = '';
    if (options.institutePrefix || options.instituteName) {
        const instLabel = options.instituteName || (options.institutePrefix ? options.institutePrefix.toUpperCase() : '');
        badgesHtml += `<span class="kpi-view-badge-inst"><i class="fa-solid fa-building-columns me-1"></i>${escapeHtml(instLabel)}</span>`;
    }

    if (options.approvalStatus) {
        const status = options.approvalStatus;
        let badgeClass = 'bg-secondary text-white';
        if (status === 'Approved') badgeClass = 'bg-success text-white';
        else if (status === 'Pending') badgeClass = 'bg-warning text-dark';
        else if (status === 'Rejected') badgeClass = 'bg-danger text-white';

        badgesHtml += `<span class="badge ${badgeClass} kpi-view-badge-status"><i class="fa-solid fa-shield-halved me-1"></i>Approval: ${escapeHtml(status)}</span>`;
    }

    if (typeof options.publishStatus !== 'undefined' && options.publishStatus !== null) {
        const isPub = options.publishStatus == 1 || options.publishStatus === '1' || options.publishStatus === 'Published';
        const pubLabel = isPub ? 'Published' : 'Draft';
        const pubClass = isPub ? 'bg-info text-dark' : 'bg-secondary text-white';
        badgesHtml += `<span class="badge ${pubClass} kpi-view-badge-status">${pubLabel}</span>`;
    }

    if (options.extraStatus) {
        badgesHtml += `<span class="badge bg-primary text-white kpi-view-badge-status">${escapeHtml(options.extraStatus)}</span>`;
    }

    badgesElem.innerHTML = badgesHtml;

    // Body Fields rendering
    bodyElem.innerHTML = '';
    const fields = options.fields || [];

    let count = 0;
    fields.forEach(field => {
        if (!field || field.value === null || field.value === undefined || field.value === '') {
            return; // Do not show empty or null fields
        }

        const isFull = field.fullWidth || field.type === 'longtext' || field.type === 'image' || field.type === 'file';
        const fieldCard = document.createElement('div');
        fieldCard.className = `kpi-view-field-item ${isFull ? 'full-width' : ''}`;

        const iconHtml = field.icon ? `<i class="${field.icon}"></i>` : '<i class="fa-solid fa-circle-info"></i>';
        const labelHtml = `<div class="kpi-view-label">${iconHtml} ${escapeHtml(field.label)}</div>`;

        let valueHtml = '';
        if (field.type === 'link') {
            let url = field.value.trim();
            if (!/^https?:\/\//i.test(url) && !url.startsWith('mailto:') && !url.startsWith('tel:')) {
                url = 'https://' + url;
            }
            valueHtml = `<div class="kpi-view-value">
                <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" class="kpi-view-link-btn">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> ${escapeHtml(field.linkText || field.value)}
                </a>
            </div>`;
        } else if (field.type === 'doi') {
            let doiVal = field.value.trim();
            let doiUrl = doiVal.startsWith('http') ? doiVal : `https://doi.org/${doiVal.replace(/^doi:/i, '')}`;
            valueHtml = `<div class="kpi-view-value">
                <a href="${escapeHtml(doiUrl)}" target="_blank" rel="noopener noreferrer" class="kpi-view-link-btn">
                    <i class="fa-solid fa-fingerprint"></i> ${escapeHtml(doiVal)}
                </a>
            </div>`;
        } else if (field.type === 'file') {
            valueHtml = `<div class="kpi-view-value">
                <a href="${escapeHtml(field.value)}" target="_blank" rel="noopener noreferrer" class="kpi-view-link-btn">
                    <i class="fa-solid fa-file-pdf text-danger"></i> View Document / Attachment
                </a>
            </div>`;
        } else if (field.type === 'image') {
            valueHtml = `<div class="kpi-view-value">
                <a href="${escapeHtml(field.value)}" target="_blank" rel="noopener noreferrer">
                    <img src="${escapeHtml(field.value)}" alt="${escapeHtml(field.label)}" class="kpi-view-img-preview" />
                </a>
            </div>`;
        } else if (field.type === 'longtext') {
            valueHtml = `<div class="kpi-view-value-longtext">${escapeHtml(field.value)}</div>`;
        } else if (field.type === 'badge') {
            valueHtml = `<div class="kpi-view-value"><span class="badge bg-primary">${escapeHtml(field.value)}</span></div>`;
        } else {
            valueHtml = `<div class="kpi-view-value">${escapeHtml(field.value)}</div>`;
        }

        fieldCard.innerHTML = labelHtml + valueHtml;
        bodyElem.appendChild(fieldCard);
        count++;
    });

    if (count === 0) {
        bodyElem.innerHTML = `<div class="kpi-view-field-item full-width text-center text-muted py-4">No additional details recorded for this entry.</div>`;
    }

    const modalInst = new bootstrap.Modal(document.getElementById('kpiViewModal'));
    modalInst.show();
}

function escapeHtml(str) {
    if (typeof str !== 'string') return str;
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch (e) {
        return dateStr;
    }
}
</script>
