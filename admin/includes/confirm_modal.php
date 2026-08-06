<?php
/**
 * admin/includes/confirm_modal.php — ANRF–PAIR Custom Confirmation Modal HTML Template
 * Reusable modal structure for Approve, Reject, and generic action confirmations.
 */
?>
<!-- ANRF-PAIR Custom Confirmation Modal -->
<div id="anrfConfirmModalBackdrop" class="anrf-modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="anrf-modal-card" id="anrfModalCard" tabindex="-1">
        <button type="button" class="anrf-modal-close-btn" id="anrfModalCloseBtn" aria-label="Close modal">&times;</button>
        
        <!-- Icon Container -->
        <div class="anrf-modal-icon-container" id="anrfModalIconContainer">
            <!-- Dynamically populated SVG -->
        </div>

        <!-- Title & Message -->
        <h3 class="anrf-modal-title" id="anrfModalTitle">Title</h3>
        <p class="anrf-modal-message" id="anrfModalMessage">Message</p>

        <!-- Optional Textarea (used for Rejection Reason) -->
        <div class="anrf-modal-textarea-group" id="anrfModalTextareaGroup" style="display: none;">
            <textarea class="anrf-modal-textarea" id="anrfModalTextarea" placeholder="Enter rejection reason..."></textarea>
        </div>

        <!-- Actions -->
        <div class="anrf-modal-actions">
            <button type="button" class="anrf-modal-btn anrf-modal-btn-cancel" id="anrfModalCancelBtn">Cancel</button>
            <button type="button" class="anrf-modal-btn" id="anrfModalConfirmBtn">Confirm</button>
        </div>
    </div>
</div>
