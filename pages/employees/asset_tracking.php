<!-- ============================================================
  FILE: pages/employees/asset_tracking.php
  PURPOSE: Asset Registry — list, register, reassign, retire assets.
  THEME: Follows YDY HRM design system (CSS vars, card/stat patterns).
  DB FLOW: All data served by api/employees/fetch_assets.php (GET)
           Save/update via api/employees/save_asset.php (POST)
           Reassign via api/employees/reassign_asset.php (POST)
           Retire via api/employees/retire_asset.php (POST)
============================================================ -->

<div class="page" id="p-asset-tracking">

  <!-- ── Page Header ── -->
  <div class="page-header">
    <div>
      <div class="page-title">Asset Registry</div>
      <div class="page-sub">Comprehensive tracking of corporate inventory, valuation &amp; custodians</div>
    </div>
    <div class="flex-row">
      <!-- Category list — opens a simple category management modal -->
      <button class="btn btn-secondary" onclick="openAssetCatModal()">
        <i data-lucide="layout-grid" size="13"></i> Categories
      </button>
      <!-- Register new asset -->
      <button class="btn btn-primary" onclick="openAssetModal()">
        <i data-lucide="plus" size="13"></i> Register Asset
      </button>
    </div>
  </div>

  <!-- ── Live Stat Cards (values injected by initAssetTracking()) ── -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card" style="border-left:4px solid var(--primary);">
      <div class="stat-label">Inventory Value</div>
      <!-- Updated after API responds -->
      <div class="stat-value" id="as-stat-value">—</div>
    </div>
    <div class="stat-card" style="border-left:4px solid var(--info);">
      <div class="stat-label">Total Assets</div>
      <div class="stat-value" id="as-stat-total">—</div>
    </div>
    <div class="stat-card" style="border-left:4px solid var(--warning);">
      <div class="stat-label">Active Custodians</div>
      <div class="stat-value" id="as-stat-custodians">—</div>
    </div>
    <div class="stat-card" style="border-left:4px solid var(--danger);">
      <div class="stat-label">Expiring Warranties</div>
      <!-- Count of assets whose warranty expires within 90 days -->
      <div class="stat-value" id="as-stat-warranty">—</div>
    </div>
  </div>

  <!-- ── Filter Bar ── -->
  <div class="card" style="padding:14px 20px; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
    <!-- Category filter dropdown — options populated by JS -->
    <div style="display:flex; align-items:center; gap:8px;">
      <label style="font-size:.72rem; font-weight:700; color:var(--muted); white-space:nowrap;">Category</label>
      <select id="as-filter-cat" onchange="applyAssetFilters()"
              style="font-size:.75rem; padding:6px 10px; border:1px solid var(--border);
                     border-radius:8px; outline:none; background:#fff; color:var(--text);">
        <option value="">All</option>
        <!-- Options injected by initAssetTracking() -->
      </select>
    </div>

    <!-- Status filter -->
    <div style="display:flex; align-items:center; gap:8px;">
      <label style="font-size:.72rem; font-weight:700; color:var(--muted);">Status</label>
      <select id="as-filter-status" onchange="applyAssetFilters()"
              style="font-size:.75rem; padding:6px 10px; border:1px solid var(--border);
                     border-radius:8px; outline:none; background:#fff; color:var(--text);">
        <option value="">All</option>
        <option value="Active">Active</option>
        <option value="In Repair">In Repair</option>
        <option value="Retired">Retired</option>
        <option value="Lost">Lost</option>
      </select>
    </div>

    <!-- Legend chips -->
    <div class="ml-auto flex-row" style="gap:8px;">
      <span class="asset-status-chip status-active">Active</span>
      <span class="asset-status-chip status-repair">In Repair</span>
      <span class="asset-status-chip status-retired">Retired</span>
      <span class="asset-status-chip status-lost">Lost</span>
    </div>
  </div>

  <!-- ── Server-Paginated Table (mounted by initAssetTracking()) ── -->
  <div id="tbl-assets"></div>

</div><!-- /page -->


<!-- ══════════════════════════════════════════════════════════
     MODAL 1: Register New Asset
     POST fields: name, category_id, serial, custodian_id,
                  location_id, value, purchase_date, warranty_expiry,
                  notes, csrf_token
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-add-asset" onclick="closeAssetModal(event)">
  <div class="modal-box" style="max-width:540px; overflow:visible;">

    <div class="modal-header">
      <div>
        <div style="font-size:1.1rem; font-weight:800;">Register New Asset</div>
        <div style="font-size:.75rem; color:var(--muted); margin-top:3px;">Add an item to the corporate inventory registry</div>
      </div>
      <button class="icon-btn" onclick="closeAssetModal()"><i data-lucide="x" size="15"></i></button>
    </div>

    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid">

        <!-- Asset Name -->
        <div class="form-group" style="grid-column:1/-1;">
          <label>Asset Name <span style="color:var(--danger);">*</span></label>
          <input type="text" id="as-new-name" placeholder="e.g. MacBook Pro 14 (2024)">
        </div>

        <!-- Category -->
        <div class="form-group">
          <label>Category <span style="color:var(--danger);">*</span></label>
          <select id="as-new-category">
            <option value="">Select category…</option>
            <!-- Populated by openAssetModal() from cached categories -->
          </select>
        </div>

        <!-- Serial / Reference -->
        <div class="form-group">
          <label>Serial / Reference No.</label>
          <input type="text" id="as-new-serial" placeholder="e.g. SN-88293-X">
        </div>

        <!-- Custodian (searchable combo) -->
        <div class="form-group" style="position:relative;">
          <label>Assigned Custodian</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-custodian"
                   placeholder="Type name to search…"
                   onfocus="showAsDrop('as-drop-custodian')"
                   oninput="filterAsDrop('as-input-custodian','as-drop-custodian')">
            <!-- Hidden field holds the selected employee_id -->
            <input type="hidden" id="as-hidden-custodian-id">
            <div class="as-combo-results" id="as-drop-custodian"></div>
          </div>
        </div>

        <!-- Location (searchable combo) -->
        <div class="form-group" style="position:relative;">
          <label>Location / Branch</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-location"
                   placeholder="Type branch name…"
                   onfocus="showAsDrop('as-drop-location')"
                   oninput="filterAsDrop('as-input-location','as-drop-location')">
            <input type="hidden" id="as-hidden-location-id">
            <div class="as-combo-results" id="as-drop-location"></div>
          </div>
        </div>

        <!-- Asset Value -->
        <div class="form-group">
          <label>Asset Value (ETB)</label>
          <input type="number" id="as-new-value" placeholder="e.g. 45000" min="0" step="0.01">
        </div>

        <!-- Purchase Date -->
        <div class="form-group">
          <label>Purchase Date</label>
          <input type="date" id="as-new-purchase-date">
        </div>

        <!-- Warranty Expiry -->
        <div class="form-group">
          <label>Warranty Expiry</label>
          <input type="date" id="as-new-warranty">
        </div>

        <!-- Status -->
        <div class="form-group">
          <label>Status</label>
          <select id="as-new-status">
            <option value="Active">Active</option>
            <option value="In Repair">In Repair</option>
            <option value="Retired">Retired</option>
            <option value="Lost">Lost</option>
          </select>
        </div>

        <!-- Notes -->
        <div class="form-group" style="grid-column:1/-1;">
          <label>Notes</label>
          <textarea id="as-new-notes" rows="2"
                    placeholder="Optional: condition notes, purchase order ref, etc."
                    style="resize:vertical;"></textarea>
        </div>

      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeAssetModal()">Cancel</button>
      <button class="btn btn-primary" id="btn-save-asset" onclick="saveNewAsset()">
        <i data-lucide="save" size="14"></i> Save Asset
      </button>
    </div>

  </div>
</div>


<!-- ══════════════════════════════════════════════════════════
     MODAL 2: Reassign Asset
     POST fields: asset_id, new_custodian_id, reason, csrf_token
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-reassign-asset" onclick="closeReassignModal(event)">
  <div class="modal-box" style="max-width:440px; overflow:visible;">

    <div class="modal-header">
      <div>
        <div style="font-size:1.1rem; font-weight:800;">Reassign Asset</div>
        <div style="font-size:.75rem; color:var(--muted); margin-top:3px;">Transfer ownership to another employee</div>
      </div>
      <button class="icon-btn" onclick="closeReassignModal()"><i data-lucide="x" size="15"></i></button>
    </div>

    <div class="modal-body" style="overflow:visible;">
      <div class="flex-col" style="gap:16px;">

        <!-- Current asset info tiles -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
          <div class="info-tile">
            <label class="tile-label">Asset Being Transferred</label>
            <div id="reassign-display-name" class="tile-val">—</div>
          </div>
          <div class="info-tile">
            <label class="tile-label">Current Custodian</label>
            <div id="reassign-display-curr" class="tile-val">—</div>
          </div>
        </div>

        <!-- Hidden: stores asset_id for POST -->
        <input type="hidden" id="reassign-asset-id">

        <div class="divider" style="margin:0;"></div>

        <!-- New Custodian (searchable combo) -->
        <div class="form-group" style="position:relative;">
          <label style="font-weight:700; color:var(--primary);">Select New Custodian <span style="color:var(--danger);">*</span></label>
          <div class="as-combo-container">
            <input type="text" id="as-input-reassign"
                   placeholder="Search by name…"
                   onfocus="showAsDrop('as-drop-reassign')"
                   oninput="filterAsDrop('as-input-reassign','as-drop-reassign')">
            <input type="hidden" id="as-hidden-reassign-id">
            <div class="as-combo-results" id="as-drop-reassign"></div>
          </div>
        </div>

        <!-- Reason for transfer -->
        <div class="form-group">
          <label>Reason for Transfer</label>
          <input type="text" id="reassign-reason" placeholder="e.g. Employee resigned, Department change">
        </div>

      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeReassignModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveReassignment()">
        <i data-lucide="check-circle" size="14"></i> Confirm Transfer
      </button>
    </div>

  </div>
</div>


<!-- ══════════════════════════════════════════════════════════
     MODAL 3: View Asset Details (read-only)
     Opened by clicking the eye icon in the table row.
     Populated by loadAssetDetail(assetId).
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-view-asset" onclick="closeViewAssetModal(event)">
  <div class="modal-box" style="max-width:500px;">

    <div class="modal-header">
      <div>
        <div style="font-size:1.1rem; font-weight:800;" id="view-asset-title">Asset Details</div>
        <div style="font-size:.75rem; color:var(--muted); margin-top:3px;" id="view-asset-id-label">—</div>
      </div>
      <button class="icon-btn" onclick="closeViewAssetModal()"><i data-lucide="x" size="15"></i></button>
    </div>

    <div class="modal-body">
      <!-- Detail rows injected by loadAssetDetail() -->
      <div id="view-asset-body" style="display:flex; flex-direction:column; gap:0;">
        <div style="padding:40px; text-align:center; color:var(--muted);">
          <i data-lucide="loader" size="24" style="animation:spin 1s linear infinite;"></i>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeViewAssetModal()">Close</button>
      <!-- Edit button transitions to edit mode (future enhancement) -->
      <button class="btn btn-primary" id="btn-edit-from-view" onclick="closeViewAssetModal()">
        <i data-lucide="pencil" size="14"></i> Edit
      </button>
    </div>

  </div>
</div>