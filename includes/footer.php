
<!-- MODALS -->
<div class="modal-overlay" id="modal-add-dept" onclick="closeDeptModal(event)">
  <div class="modal-box" style="max-width:500px;overflow:visible;">
    <div class="modal-header">
      <div><div style="font-size:1rem;font-weight:800;">Add New Department</div><div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Create a new organizational unit</div></div>
      <button class="icon-btn" onclick="closeDeptModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <div class="form-group" style="grid-column:span 2;"><label>Department Name *</label><input id="dept-name" placeholder="e.g. Engineering"></div>
        <div class="form-group" style="position:relative;">
          <label>Head of Department</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-dept-head" placeholder="Type name to search..." onfocus="showAsDrop('as-drop-dept-head')" oninput="filterAsDrop('as-input-dept-head','as-drop-dept-head')">
            <div class="as-combo-results" id="as-drop-dept-head"></div>
          </div>
        </div>
        <div class="form-group">
          <label>Location</label>
          <div class="as-combo-container">
  <input type="text" id="dept-location" class="form-ctrl" placeholder="Select Location..." 
         onfocus="showAsDrop('as-drop-dept-loc')" readonly>
  <div class="as-combo-results" id="as-drop-dept-loc">
    <div class="as-res-item" onclick="selectAsItem('dept-location','as-drop-dept-loc','Addis Ababa')">Addis Ababa</div>
    <div class="as-res-item" onclick="selectAsItem('dept-location','as-drop-dept-loc','Mekelle')">Mekelle</div>
    <div class="as-res-item" onclick="selectAsItem('dept-location','as-drop-dept-loc','Adama')">Adama</div>
    <div class="as-res-item" onclick="selectAsItem('dept-location','as-drop-dept-loc','Remote')">Remote</div>
  </div>
</div>
        </div>
        <div class="form-group" style="grid-column:span 2;"><label>Status</label><div class="as-combo-container">
  <input type="text" id="dept-status" class="form-ctrl" placeholder="Select Status..." 
         onfocus="showAsDrop('as-drop-dept-status')" readonly>
  <div class="as-combo-results" id="as-drop-dept-status">
    <div class="as-res-item" onclick="selectAsItem('dept-status','as-drop-dept-status','Active')">Active</div>
    <div class="as-res-item" onclick="selectAsItem('dept-status','as-drop-dept-status','Inactive')">Inactive</div>
  </div>
</div></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeDeptModal()">Cancel</button>
      <button class="btn btn-primary" onclick=""><i data-lucide="check" size="13"></i>Create Department</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-add-job-position" onclick="closeJobModal(event)">
  <div class="modal-box" style="max-width:500px;overflow:visible;">
    <div class="modal-header">
      <div><div style="font-size:1rem;font-weight:800;">Add New Job Position</div><div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Define a new role and headcount requirements</div></div>
      <button class="icon-btn" onclick="closeJobModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <div class="form-group" style="grid-column:span 2;"><label>Job Title *</label><input id="job-title" placeholder="e.g. Senior Software Engineer"></div>
        <div class="form-group" style="position:relative;grid-column:span 2;">
          <label>Department</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-job-dept" placeholder="Search department..." onfocus="showAsDrop('as-drop-job-dept')" oninput="filterAsDrop('as-input-job-dept','as-drop-job-dept')">
            <div class="as-combo-results" id="as-drop-job-dept"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeJobModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveJobPosition()"><i data-lucide="check" size="13"></i>Create Position</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-add-branch" onclick="closeBranchModal(event)">
  <div class="modal-box" style="max-width:500px;overflow:visible;">
    <div class="modal-header">
      <div><div style="font-size:1rem;font-weight:800;">Add New Branch</div><div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Register a new corporate location</div></div>
      <button class="icon-btn" onclick="closeBranchModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <div class="form-group"><label>Branch Name *</label><input id="branch-name" placeholder="e.g. East Hub"></div>
        <div class="form-group"><label>City</label><input id="branch-city" placeholder="e.g. Adama"></div>
        <div class="form-group" style="position:relative;">
          <label>Branch Manager</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-branch-mgr" placeholder="Search manager..." onfocus="showAsDrop('as-drop-branch-mgr')" oninput="filterAsDrop('as-input-branch-mgr','as-drop-branch-mgr')">
            <div class="as-combo-results" id="as-drop-branch-mgr"></div>
          </div>
        </div>
        <div class="form-group" style="grid-column:span 2;"><label>Status</label><div class="as-combo-container">
  <input type="text" id="branch-status" class="form-ctrl" placeholder="Select Status..." 
         onfocus="showAsDrop('as-drop-branch-status')" readonly>
  <div class="as-combo-results" id="as-drop-branch-status">
    <div class="as-res-item" onclick="selectAsItem('branch-status','as-drop-branch-status','Active')">Active</div>
    <div class="as-res-item" onclick="selectAsItem('branch-status','as-drop-branch-status','Inactive')">Inactive</div>
  </div>
</div></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeBranchModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveBranch()"><i data-lucide="check" size="13"></i>Create Branch</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-add-asset" onclick="closeAssetModal(event)">
  <div class="modal-box" style="max-width:460px;overflow:visible;">
    <div class="modal-header">
      <div><div style="font-size:1.1rem;font-weight:800;">Register New Asset</div><div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Assign inventory to the system registry</div></div>
      <button class="icon-btn" onclick="closeAssetModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid">
        <div class="form-group"><label>Asset Name</label><input type="text" id="as-new-name" placeholder="e.g. MacBook Pro 14"></div>
        <div class="form-group" style="position:relative;">
          <label>Custodian</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-custodian" placeholder="Type name to search..." onfocus="showAsDrop('as-drop-custodian')" oninput="filterAsDrop('as-input-custodian','as-drop-custodian')">
            <div class="as-combo-results" id="as-drop-custodian"></div>
          </div>
        </div>
        <div class="form-group"><label>Serial / Reference</label><input type="text" id="as-new-serial" placeholder="e.g. SN-88293-X"></div>
        <div class="form-group" style="position:relative;">
          <label>Location / Branch</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-location" placeholder="Type branch name..." onfocus="showAsDrop('as-drop-location')" oninput="filterAsDrop('as-input-location','as-drop-location')">
            <div class="as-combo-results" id="as-drop-location"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeAssetModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveNewAsset()">Save Asset</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-reassign-asset" onclick="closeReassignModal(event)">
  <div class="modal-box" style="max-width:440px;overflow:visible;">
    <div class="modal-header">
      <div><div style="font-size:1.1rem;font-weight:800;">Reassign Asset</div><div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Transfer ownership to another employee</div></div>
      <button class="icon-btn" onclick="closeReassignModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="flex-col" style="gap:16px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="info-tile"><label class="tile-label">Asset to Transfer</label><div id="reassign-display-name" class="tile-val">---</div></div>
          <div class="info-tile"><label class="tile-label">Current Custodian</label><div id="reassign-display-curr" class="tile-val">---</div></div>
        </div>
        <div class="divider" style="margin:8px 0;"></div>
        <div class="form-group" style="position:relative;">
          <label style="font-weight:700;color:var(--primary);">Select New Custodian</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-reassign" placeholder="Search for new owner..." onfocus="showAsDrop('as-drop-reassign')" oninput="filterAsDrop('as-input-reassign','as-drop-reassign')">
            <div class="as-combo-results" id="as-drop-reassign"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeReassignModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveReassignment()"><i data-lucide="check-circle" size="14"></i>Save Changes</button>
    </div>
  </div>
</div>

 

<script src="assets/js/core.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="modal-overlay" id="confirm-modal">
  <div class="modal-box" style="max-width: 400px;">
    <div class="modal-header">
      <div id="confirm-title" style="font-weight: 800;">Confirm Action</div>
    </div>
    <div class="modal-body" id="confirm-body" style="font-size: 0.85rem;">
      Are you sure?
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeConfirm()">Cancel</button>
      <button class="btn btn-primary" id="confirm-btn-yes">Yes, Logout</button>
    </div>
  </div>
</div>