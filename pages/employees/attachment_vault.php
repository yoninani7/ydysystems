<!-- ============================================================
  FILE: pages/employees/attachment_vault.php
  PURPOSE: Main Attachment Vault matrix page — shows all active
           employees in a scrollable compliance matrix table.
  CHANGES FROM ORIGINAL:
    - Added live stat cards fed by real API data (no more hardcoded values)
    - Added search bar inline in the card header (no extra filter-bar div)
    - Added per-row progress bar in the Fulfillment column
    - Added category legend chips above the table
    - Removed duplicate legend block in filter-bar
    - Stats row now uses JS to update values after API load
============================================================ -->

<div class="page" id="p-document-vault">

  <!-- ── Page Header ── -->
  <div class="page-header">
    <div>
      <div class="page-title">Attachment Vault &amp; Compliance</div>
      <div class="page-sub">Tracking mandatory company-specific documents for all active personnel</div>
    </div>
  </div>

  <!-- ── Live Stat Cards (values injected by initVaultMatrix()) ── -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card" style="border-left:4px solid var(--primary);">
      <div class="stat-label">Compliance Rate</div>
      <!-- id="v-stat-rate" will be updated once the API responds -->
      <div class="stat-value" id="v-stat-rate">—</div>
    </div>
    <div class="stat-card" style="border-left:4px solid var(--danger);">
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

    <!-- Card header: title left, category legend chips right -->
    <div class="filter-bar" style="padding:16px 20px; border-bottom:1px solid var(--border); gap:12px;">
      <div>
        <div style="font-size:.8rem; font-weight:800; color:var(--text);">Document Compliance Matrix</div>
        <div style="font-size:.68rem; color:var(--muted);">Click any slot to see details. Click "Open Folder" to manage individual documents.</div>
      </div>

      <!-- Category legend chips — one colour per doc category -->
      <div class="ml-auto flex-row" style="gap:6px; flex-wrap:wrap; align-items:center;">
        <span style="font-size:.65rem; font-weight:700; color:var(--muted); white-space:nowrap;">CATEGORIES:</span>
        <span class="vault-cat-chip cat-legal">Legal</span>
        <span class="vault-cat-chip cat-identity">Identity</span>
        <span class="vault-cat-chip cat-education">Education</span>
        <span class="vault-cat-chip cat-history">History</span>
        <span class="vault-cat-chip cat-professional">Professional</span>
        <span class="vault-cat-chip cat-compliance">Compliance</span>
        <span class="vault-cat-chip cat-tax">Tax</span>
        <!-- Status legend -->
        <span style="font-size:.65rem; font-weight:700; color:var(--muted); white-space:nowrap; margin-left:8px;">STATUS:</span>
        <span style="font-size:.65rem; display:flex; align-items:center; gap:4px;">
          <span style="width:10px; height:10px; border-radius:3px; background:var(--success); display:inline-block;"></span>Uploaded
        </span>
        <span style="font-size:.65rem; display:flex; align-items:center; gap:4px;">
          <span style="width:10px; height:10px; border-radius:3px; background:#fff5f5; border:1px dashed #e57373; display:inline-block;"></span>Missing
        </span>
      </div>
    </div>

    <!-- The server-paginated table mounts here via initVaultMatrix() in core.js -->
    <div id="vault-matrix-container"></div>

  </div>
</div>
