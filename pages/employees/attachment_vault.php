<!-- ============================================================
  FILE: pages/employees/attachment_vault.php
  PURPOSE: Main Attachment Vault matrix page
============================================================ -->
<?php $token = csrf_token(); ?>
<meta name="csrf" content="<?php echo $token; ?>">

<style>
/* ── Strip out badges & noise ── */
.vault-cat-chip, .badge-mandatory, .badge-optional, .doc-status { display: none !important; }

/* ── Refined Matrix Slots ── */
#vault-matrix-container .vault-slot {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.2s ease;
}
#vault-matrix-container .vault-slot.filled {
    background: var(--success-bg);
    color: var(--success);
    border: 1px solid rgba(22, 163, 74, 0.2);
} 
</style>

<div class="page active" id="p-document-vault">

  <!-- ── Page Header ── -->
  <div class="page-header">
    <div>
      <div class="page-title">Attachment Vault &amp; Compliance</div>
      <div class="page-sub">Tracking mandatory company-specific documents for all active personnel</div>
    </div>
  </div>

  <!-- ── Live Stat Cards ── -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card" style="border-left:4px solid var(--primary);">
      <div class="stat-label">Compliance Rate</div>
      <div class="stat-value" id="v-stat-rate">—</div>
    </div>
    <div class="stat-card" style="border-left:4px solid var(--warning);">
      <div class="stat-label">Missing Files</div>
      <div class="stat-value" id="v-stat-missing">—</div>
    </div>
    <div class="stat-card" style="border-left:4px solid var(--danger);">
      <div class="stat-label">Non-Compliant Staff</div>
      <div class="stat-value" id="v-stat-non-compliant">—</div>
    </div>
    <div class="stat-card" style="border-left:4px solid var(--success);">
      <div class="stat-label">Compliant Staff</div>
      <div class="stat-value" id="v-stat-compliant">—</div>
    </div>
  </div>

  <!-- ── Main Card ── -->
  <div class="card">

    <!-- Card Header / Toolbar -->
    <div class="filter-bar" style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
      <div>
        <div style="font-size: 0.95rem; font-weight: 800; color: var(--text);">Document Compliance Matrix</div>
        <div style="font-size: 0.75rem; color: var(--muted); margin-top: 4px;">Click any status slot to manage individual documents.</div>
      </div>

      <!-- Clean Minimal Legend -->
      <div style="display: flex; gap: 20px; align-items: center; font-size: 0.72rem; font-weight: 700; color: var(--muted);">
        <div style="display: flex; align-items: center; gap: 8px;">
          <div style="width: 10px; height: 10px; border-radius: 4px; background: var(--success);"></div>
          <span>Verified</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <div style="width: 10px; height: 10px; border-radius: 4px; background: #f8fafc; border: 1px dashed #cbd5e1;"></div>
          <span>Missing</span>
        </div>
      </div>
    </div>

    <!-- The server-paginated table mounts here via initVaultMatrix() in core.js -->
    <div id="vault-matrix-container"></div>

  </div>
</div>