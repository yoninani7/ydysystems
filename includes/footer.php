
<!-- MODALS -->
<div class="modal-overlay" id="modal-add-dept" onclick="closeDeptModal(event)">
  <div class="modal-box" style="max-width:500px;overflow:visible;">
    <div class="modal-header">
      <div><div style="font-size:1rem;font-weight:800;">Add New Department</div><div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Create a new organizational unit</div></div>
      <button class="icon-btn" onclick="closeDeptModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <div class="form-group" style="grid-column:span 2;">
          <label>Department Name *</label>
          <input id="dept-name" placeholder="e.g. Engineering" class="form-ctrl">
        </div>
        <div class="form-group" style="position:relative;grid-column:span 2;">
          <label>Head of Department</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-dept-head" 
                   data-dropdown-type="employees"
                   placeholder="Type name to search..." 
                   onfocus="showAsDrop('as-drop-dept-head')" 
                   oninput="filterAsDrop('as-input-dept-head','as-drop-dept-head')"
                   autocomplete="off">
            <div class="as-combo-results" id="as-drop-dept-head"></div>
          </div>
        </div>
        <div class="form-group" style="grid-column:span 2;">
          <label>Status</label>
          <div class="as-combo-container">
            <input type="text" id="dept-status" class="form-ctrl" value="Active"onfocus="toggleStaticDrop('as-drop-dept-status')" readonly>
            <div class="as-combo-results" id="as-drop-dept-status">
              <div class="as-res-item" onclick="selectAsItem('dept-status','as-drop-dept-status','Active')">Active</div>
              <div class="as-res-item" onclick="selectAsItem('dept-status','as-drop-dept-status','Inactive')">Inactive</div>
            </div>
          </div>
        </div>
      </div>
      <!-- CSRF token -->
      <input type="hidden" id="dept_csrf_token" value="<?php echo csrf_token(); ?>">
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeDeptModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveDepartment()"><i data-lucide="check" size="13"></i>Create Department</button>
    </div>
  </div>
</div>

<!-- MODAL: ADD NEW JOB POSITION -->
<div class="modal-overlay" id="modal-add-job-position" onclick="closeJobModal(event)">
  <input type="hidden" id="job_csrf_token" value="<?php echo csrf_token(); ?>">
  <div class="modal-box" style="max-width:500px;overflow:visible;">
    <div class="modal-header">
      <div>
        <div style="font-size:1rem;font-weight:800;">Add New Job Position</div>
        <div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Define a new role and headcount requirements</div>
      </div>
      <button class="icon-btn" onclick="closeJobModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <!-- Job Title -->
        <div class="form-group" style="grid-column:span 2;">
          <label>Job Title *</label>
          <input id="job-title" placeholder="e.g. Senior Software Engineer" class="form-ctrl" oninput="this.classList.remove('field-error')">
        </div>
        
        <!-- Department Selection (Dynamic Dropdown) -->
        <div class="form-group" style="position:relative;grid-column:span 2;">
          <label>Department *</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-job-dept" 
                   data-dropdown-type="departments"
                   placeholder="Search department..." 
                   onfocus="showAsDrop('as-drop-job-dept')" 
                   oninput="filterAsDrop('as-input-job-dept','as-drop-job-dept')"
                   autocomplete="off">
            <div class="as-combo-results" id="as-drop-job-dept"></div>
          </div>
        </div>

        <!-- Status (Static Dropdown) -->
      <div class="form-group" style="grid-column:span 2;">
        <label>Status</label>
        <div class="as-combo-container">
          <input type="text" id="job-status" class="form-ctrl" value="Active" 
       onclick="toggleStaticDrop('as-drop-job-status')" style="cursor:pointer;" readonly>
          <div class="as-combo-results" id="as-drop-job-status">
            <div class="as-res-item" onclick="selectAsItem('job-status','as-drop-job-status','Active')">Active</div>
            <div class="as-res-item" onclick="selectAsItem('job-status','as-drop-job-status','Inactive')">Inactive</div>
          </div>
        </div>
      </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeJobModal()">Cancel</button>
      <button class="btn btn-primary" id="btn-save-job" onclick="saveJobPosition()">
        <i data-lucide="check" size="13"></i>Create Position
      </button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-add-branch" onclick="closeBranchModal(event)">
  <div class="modal-box" style="max-width:550px; overflow:visible;">
    <div class="modal-header">
      <div>
        <div style="font-size:1rem; font-weight:800;">Add New Branch</div>
        <div style="font-size:.75rem; color:var(--muted); margin-top:3px;">Register a new corporate location and contact details</div>
      </div>
      <button class="icon-btn" onclick="closeBranchModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <!-- Branch Name -->
        <div class="form-group" style="grid-column: span 2;">
          <label>Branch Name *</label>
          <input type="text" id="branch-name" placeholder="e.g. Headquarters / North Hub" class="form-ctrl">
        </div>

        <!-- Branch Manager (Dynamic Dropdown from employees) -->
        <div class="form-group" style="position:relative;">
          <label>Branch Manager</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-branch-mgr" 
                   data-dropdown-type="employees"
                   placeholder="Search manager..." 
                   onfocus="showAsDrop('as-drop-branch-mgr')" 
                   oninput="filterAsDrop('as-input-branch-mgr','as-drop-branch-mgr')"
                   autocomplete="off">
            <div class="as-combo-results" id="as-drop-branch-mgr"></div>
          </div>
        </div>

        <!-- Status (Static Dropdown) -->
        <div class="form-group">
          <label>Operating Status</label>
          <div class="as-combo-container">
            <input type="text" id="branch-status" class="form-ctrl" value="Active" 
                   onfocus="toggleStaticDrop('as-drop-branch-status')" readonly>
            <div class="as-combo-results" id="as-drop-branch-status">
              <div class="as-res-item" onclick="selectAsItem('branch-status','as-drop-branch-status','Active')">Active</div>
              <div class="as-res-item" onclick="selectAsItem('branch-status','as-drop-branch-status','Inactive')">Inactive</div>
            </div>
          </div>
        </div>

        <!-- Phone & Email -->
        <div class="form-group">
          <label>Phone Number</label>
          <input type="text" id="branch-phone" placeholder="+251..." class="form-ctrl">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" id="branch-email" placeholder="branch@company.com" class="form-ctrl">
        </div>

        <!-- City & Address -->
        <div class="form-group">
          <label>City</label>
          <input type="text" id="branch-city" placeholder="e.g. Addis Ababa" class="form-ctrl">
        </div>
        <div class="form-group">
          <label>Specific Address</label>
          <input type="text" id="branch-address" placeholder="Sub-city, Woreda, Building..." class="form-ctrl">
        </div>
      </div>
      <!-- CSRF Token -->
      <input type="hidden" id="branch_csrf_token" value="<?php echo csrf_token(); ?>">
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeBranchModal()">Cancel</button>
      <button class="btn btn-primary" id="btn-save-branch" onclick="saveBranch()">
        <i data-lucide="check" size="13"></i> Create Branch
      </button>
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
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="modal-overlay" id="confirm-modal" onclick="if(event.target.id==='confirm-modal') closeConfirm()">
  <div class="modal-box" style="max-width: 400px;">
    <div class="modal-header">
      <div>
        <div id="confirm-title" style="font-weight: 800; font-size: 1.1rem;">Confirm Action</div>
      </div>
      <button class="icon-btn" onclick="closeConfirm()"><i data-lucide="x" size="18"></i></button>
    </div>
    <div class="modal-body" id="confirm-body" style="font-size: 0.85rem;">
      Are you sure?
    </div>
    <div class="modal-footer" style="background: #f8fafc; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
      <button class="btn btn-secondary" onclick="closeConfirm()" style="font-size: 0.8rem; padding: 8px 20px;">Cancel</button>
      <button class="btn btn-primary" id="confirm-btn-yes" style="font-size: 0.8rem; padding: 8px 20px;">Confirm</button>
    </div>
  </div>
</div>
<!-- MODAL: EDIT JOB POSITION -->
<div class="modal-overlay" id="modal-edit-job-position" onclick="closeEditJobModal(event)">
  <input type="hidden" id="edit_job_csrf_token" value="<?php echo csrf_token(); ?>">
  <input type="hidden" id="edit_job_id">
  <div class="modal-box" style="max-width:500px;overflow:visible;">
    <div class="modal-header">
      <div>
        <div style="font-size:1rem;font-weight:800;">Edit Job Position</div>
        <div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Update title or reassign department</div>
      </div>
      <button class="icon-btn" onclick="closeEditJobModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <!-- Job Title -->
        <div class="form-group" style="grid-column:span 2;">
          <label>Job Title *</label>
          <input id="edit-job-title" placeholder="e.g. Senior Software Engineer" class="form-ctrl">
        </div>
        
        <!-- Department Selection -->
        <div class="form-group" style="position:relative;grid-column:span 2;">
          <label>Department *</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-edit-job-dept" 
                   data-dropdown-type="departments"
                   placeholder="Search department..." 
                   onfocus="showAsDrop('as-drop-edit-job-dept')" 
                   oninput="filterAsDrop('as-input-edit-job-dept','as-drop-edit-job-dept')" 
                   autocomplete="off">
            <div class="as-combo-results" id="as-drop-edit-job-dept"></div>
          </div>
        </div>

        <!-- Status -->
        <div class="form-group" style="grid-column:span 2;">
          <label>Status</label>
          <div class="as-combo-container">
            <input type="text" id="edit-job-status" class="form-ctrl" value="Active" 
                   onfocus="toggleStaticDrop('as-drop-edit-job-status')" style="cursor:pointer;" readonly>
            <div class="as-combo-results" id="as-drop-edit-job-status">
              <div class="as-res-item" onclick="selectAsItem('edit-job-status','as-drop-edit-job-status','Active')">Active</div>
              <div class="as-res-item" onclick="selectAsItem('edit-job-status','as-drop-edit-job-status','Inactive')">Inactive</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeEditJobModal()">Cancel</button>
      <button class="btn btn-primary" id="btn-update-job" onclick="updateJobPosition()">
        <i data-lucide="check" size="13"></i> Save Changes
      </button>
    </div>
  </div>
</div>
<!-- MODAL: EDIT DEPARTMENT -->
<div class="modal-overlay" id="modal-edit-dept" onclick="closeEditDeptModal(event)">
  <input type="hidden" id="edit_dept_csrf_token" value="<?php echo csrf_token(); ?>">
  <input type="hidden" id="edit_dept_id">
  <div class="modal-box" style="max-width:500px;overflow:visible;">
    <div class="modal-header">
      <div>
        <div style="font-size:1rem;font-weight:800;">Edit Department</div>
        <div style="font-size:.75rem;color:var(--muted);margin-top:3px;">Rename department or change head</div>
      </div>
      <button class="icon-btn" onclick="closeEditDeptModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <!-- Department Name -->
        <div class="form-group" style="grid-column:span 2;">
          <label>Department Name *</label>
          <input id="edit-dept-name" placeholder="e.g. Engineering" class="form-ctrl">
        </div>
        
        <!-- Head of Department (Employee Dropdown) -->
        <div class="form-group" style="position:relative;grid-column:span 2;">
          <label>Head of Department</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-edit-dept-head" 
                   data-dropdown-type="employees"
                   placeholder="Search employee..." 
                   onfocus="showAsDrop('as-drop-edit-dept-head')" 
                   oninput="filterAsDrop('as-input-edit-dept-head','as-drop-edit-dept-head')"
                   autocomplete="off">
            <div class="as-combo-results" id="as-drop-edit-dept-head"></div>
          </div>
        </div>

        <!-- Status -->
        <div class="form-group" style="grid-column:span 2;">
          <label>Status</label>
          <div class="as-combo-container">
            <input type="text" id="edit-dept-status" class="form-ctrl" value="Active" 
                   onfocus="toggleStaticDrop('as-drop-edit-dept-status')" style="cursor:pointer;" readonly>
            <div class="as-combo-results" id="as-drop-edit-dept-status">
              <div class="as-res-item" onclick="selectAsItem('edit-dept-status','as-drop-edit-dept-status','Active')">Active</div>
              <div class="as-res-item" onclick="selectAsItem('edit-dept-status','as-drop-edit-dept-status','Inactive')">Inactive</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeEditDeptModal()">Cancel</button>
      <button class="btn btn-primary" id="btn-update-dept" onclick="updateDepartment()">
        <i data-lucide="check" size="13"></i> Save Changes
      </button>
    </div>
  </div>
</div>
<!-- MODAL: EDIT BRANCH -->
<div class="modal-overlay" id="modal-edit-branch" onclick="closeEditBranchModal(event)">
  <input type="hidden" id="edit_branch_csrf_token" value="<?php echo csrf_token(); ?>">
  <input type="hidden" id="edit_branch_id">
  <div class="modal-box" style="max-width:550px; overflow:visible;">
    <div class="modal-header">
      <div>
        <div style="font-size:1rem; font-weight:800;">Edit Branch</div>
        <div style="font-size:.75rem; color:var(--muted); margin-top:3px;">Update branch details and management</div>
      </div>
      <button class="icon-btn" onclick="closeEditBranchModal()"><i data-lucide="x" size="15"></i></button>
    </div>
    
    <div class="modal-body" style="overflow:visible;">
      <div class="form-grid fg-2">
        <!-- Branch Name -->
        <div class="form-group" style="grid-column: span 2;">
          <label>Branch Name *</label>
          <input type="text" id="edit-branch-name" placeholder="e.g. Headquarters / North Hub" class="form-ctrl">
        </div>

        <!-- Branch Manager (Dynamic Dropdown) -->
        <div class="form-group" style="position:relative;">
          <label>Branch Manager</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-edit-branch-mgr" 
                   data-dropdown-type="employees"
                   placeholder="Search manager..." 
                   onfocus="showAsDrop('as-drop-edit-branch-mgr')" 
                   oninput="filterAsDrop('as-input-edit-branch-mgr','as-drop-edit-branch-mgr')"
                   autocomplete="off">
            <div class="as-combo-results" id="as-drop-edit-branch-mgr"></div>
          </div>
        </div>

        <!-- Status -->
        <div class="form-group">
          <label>Operating Status</label>
          <div class="as-combo-container">
            <input type="text" id="edit-branch-status" class="form-ctrl" value="Active" 
                   onfocus="toggleStaticDrop('as-drop-edit-branch-status')" readonly>
            <div class="as-combo-results" id="as-drop-edit-branch-status">
              <div class="as-res-item" onclick="selectAsItem('edit-branch-status','as-drop-edit-branch-status','Active')">Active</div>
              <div class="as-res-item" onclick="selectAsItem('edit-branch-status','as-drop-edit-branch-status','Inactive')">Inactive</div>
            </div>
          </div>
        </div>

        <!-- Phone & Email -->
        <div class="form-group">
          <label>Phone Number</label>
          <input type="text" id="edit-branch-phone" placeholder="+251..." class="form-ctrl">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" id="edit-branch-email" placeholder="branch@company.com" class="form-ctrl">
        </div>

        <!-- City & Address -->
        <div class="form-group">
          <label>City</label>
          <input type="text" id="edit-branch-city" placeholder="e.g. Addis Ababa" class="form-ctrl">
        </div>
        <div class="form-group">
          <label>Specific Address</label>
          <input type="text" id="edit-branch-address" placeholder="Sub-city, Woreda, Building..." class="form-ctrl">
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeEditBranchModal()">Cancel</button>
      <button class="btn btn-primary" id="btn-update-branch" onclick="updateBranch()">
        <i data-lucide="check" size="13"></i> Save Changes
      </button>
    </div>
  </div>
</div>
<!-- MODAL: EXTEND PROBATION -->
<div class="modal-overlay" id="modal-extend-probation" onclick="closeModal('modal-extend-probation', event)">
  <div class="modal-box" style="max-width: 460px;">
    <div class="modal-header">
      <div>
        <div style="font-size:1.1rem; font-weight:800;">Extend Probation Period</div>
        <div style="font-size:.75rem; color:var(--muted); margin-top:3px;">Set a new end date for the probation</div>
      </div>
      <button class="icon-btn" onclick="closeModal('modal-extend-probation')"><i data-lucide="x" size="18"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="extend-emp-id">
      <input type="hidden" id="extend-csrf-token" value="<?php echo csrf_token(); ?>">
      <div class="info-tile" style="margin-bottom:20px;">
        <label class="tile-label">Current Probation End Date</label>
        <div id="current-end-date" class="tile-val" style="font-family:'JetBrains Mono';">—</div>
      </div>
      <div class="form-group">
        <label style="font-weight:700;">New End Date *</label>
        <input type="date" id="new-end-date" class="form-ctrl" style="width:100%;">
        <div style="font-size:0.7rem; color:var(--muted); margin-top:6px;">Must be on or after current end date.</div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-extend-probation')">Cancel</button>
      <button class="btn btn-primary" onclick="submitExtendProbation()">
        <i data-lucide="calendar-plus" size="14"></i> Confirm Extension
      </button>
    </div>
  </div>
</div>