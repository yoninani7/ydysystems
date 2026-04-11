<!-- ROLES & PERMISSIONS -->
<div class="page" id="p-roles-permissions">
  <div class="page-header">
    <div>
      <div class="page-title">Access Control & Governance</div>
      <div class="page-sub">Define module visibility for system roles or specific personnel overrides</div>
    </div>
    <div class="flex-row">
      <div id="save-status-indicator" style="font-size: .65rem; color: var(--success); font-weight: 700; display:none; align-items:center; gap:5px;">
        <i data-lucide="check-circle" size="12"></i> Changes Synced
      </div>
      <button class="btn btn-primary" onclick="savePermissionChanges()">
        <i data-lucide="shield-check" size="13"></i> Commit Permissions
      </button>
    </div>
  </div>

  <!-- MODE SELECTOR TABS -->
  <div class="flex-row mb-4" style="background: #fff; padding: 4px; border-radius: 10px; border: 1px solid var(--border); width: fit-content;">
    <button class="btn btn-sm" id="btn-mode-role" style="background: var(--primary-light); color: var(--primary); border:none;" onclick="switchAccessMode('role')">Standard Roles</button>
    <button class="btn btn-sm" id="btn-mode-user" style="background: transparent; color: var(--muted); border:none;" onclick="switchAccessMode('user')">Individual Roles</button>
  </div>

  <div class="roles-layout">
    <aside class="flex-col">
      <!-- SIDEBAR A: STANDARD ROLES -->
      <div id="side-role-list">
        <div class="role-pill-v2 active" onclick="selRole(this,'Super Admin')">
          <i data-lucide="shield-check" size="16"></i>
          <div><b style="font-size:.75rem">Super Admin</b><p style="font-size:.6rem;color:var(--muted)">Full System Authority</p></div>
        </div>
        <div class="role-pill-v2" onclick="selRole(this,'HRM User')">
          <i data-lucide="user-cog" size="16"></i>
          <div><b style="font-size:.75rem">HRM User</b><p style="font-size:.6rem;color:var(--muted)">Standard HR Operations</p></div>
        </div>
        <div class="role-pill-v2" onclick="selRole(this,'Department Manager')">
          <i data-lucide="briefcase" size="16"></i>
          <div><b style="font-size:.75rem">Dept Manager</b><p style="font-size:.6rem;color:var(--muted)">Limited to Team Data</p></div>
        </div>
      </div>

      <!-- SIDEBAR B: INDIVIDUAL SEARCH (Hidden by default) -->
      <div id="side-user-search" style="display:none;">
        <div class="form-group" style="position:relative;">
          <label>Search Personnel</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-perm-user" class="form-ctrl" placeholder="Enter name..." 
                   onfocus="showAsDrop('as-drop-perm-user')" 
                   oninput="filterAsDrop('as-input-perm-user','as-drop-perm-user')">
            <div class="as-combo-results" id="as-drop-perm-user"></div>
          </div>
        </div>
        <div id="selected-user-card" class="mt-4" style="display:none;">
            <div class="stat-card" style="padding:12px; border:1px solid var(--primary); background: var(--primary-light);">
                <div class="flex-row" style="gap:10px;">
                    <div class="avatar" id="perm-user-avatar">??</div>
                    <div>
                        <div id="perm-user-name" style="font-size:.75rem; font-weight:800; color:var(--primary-dark)">User Name</div>
                        <div id="perm-user-id" style="font-size:.6rem; color:var(--muted); font-family: 'JetBrains Mono'">E-000</div>
                    </div>
                </div>
                <div class="badge badge-success" style="width:100%; margin-top:10px; justify-content:center; font-size:9px;">Custom Profile Active</div>
            </div>
        </div>
      </div>
    </aside>

    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span id="perm-target-label">Standard Role:</span> 
          <span id="active-role-name" style="color:var(--primary)">Super Admin</span>
        </div>
        <div id="override-warning" style="display:none;" class="badge badge-warning">Custom Mode</div>
      </div>
      <div class="table-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width:40px"></th>
              <th>Module</th>
              <th>Access Description</th>
              <th style="text-align:center;width:100px">Visible</th>
            </tr>
          </thead>
          <tbody id="perm-grid"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>