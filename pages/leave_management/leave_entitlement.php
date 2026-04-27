<div class="page-header">
    <div>
        <h1 class="page-title">Leave Entitlement</h1>
        <p class="page-sub">Manage employee leave balances and annual allocations</p>
    </div>
    <div class="flex-row">
      <button class="btn btn-secondary" onclick="openModal('modal-bulk-assign')" style="border-radius: 10px; padding: 10px 18px;">
    <i data-lucide="layers" size="18" style="margin-right:8px"></i> Bulk Assign
</button>
        <button class="btn btn-primary" onclick="openModal('modal-add-entitlement')" style="background: var(--primary); border-radius: 10px; padding: 10px 18px;">
    <i data-lucide="plus" size="18" style="margin-right:8px"></i> Add Entitlement
</button>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4" style="border-radius: 12px; overflow: visible;">
    <div class="card-body" style="padding: 20px 24px;">
        <div class="flex-row" style="gap: 16px; align-items: flex-end;">
            <div style="flex: 0.6;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">FISCAL YEAR</label>
                <input type="text" id="ent-year" class="sel" style="width: 100%;" value="2025" readonly>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">DEPARTMENT</label>
                <div class="as-combo-container">
                    <input type="text" id="ent-dept" class="sel" style="width: 100%;" value="Finance" onfocus="showAsDrop('as-drop-ent-dept')" readonly>
                    <div class="as-combo-results" id="as-drop-ent-dept">
                        <div class="as-res-item" onclick="selectAsItem('ent-dept','as-drop-ent-dept','Engineering')">Engineering</div>
                        <div class="as-res-item selected" onclick="selectAsItem('ent-dept','as-drop-ent-dept','Finance')">Finance</div>
                    </div>
                </div>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">LEAVE TYPE</label>
                <div class="as-combo-container">
                    <input type="text" id="ent-type" class="sel" style="width: 100%;" value="Annual Leave" onfocus="showAsDrop('as-drop-ent-type')" readonly>
                    <div class="as-combo-results" id="as-drop-ent-type">
                        <div class="as-res-item selected" onclick="selectAsItem('ent-type','as-drop-ent-type','Annual Leave')">Annual Leave</div>
                        <div class="as-res-item" onclick="selectAsItem('ent-type','as-drop-ent-type','Sick Leave')">Sick Leave</div>
                    </div>
                </div>
            </div>
            <div style="flex: 1.5;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">SEARCH EMPLOYEE</label>
                <input type="text" class="form-ctrl" placeholder="Name or employee code..." style="height: 38px; border-radius: 8px;">
            </div>
            <button class="btn btn-primary" style="height: 38px; background: #15b201; padding: 0 24px;" onclick="renderLeaveEntitlements()">
                <i data-lucide="search" size="16" style="margin-right:8px"></i> Load Balances
            </button>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="stats-row stats-4 mb-4">
    <div class="stat-card">
        <div class="stat-label">Total Employees</div>
        <div class="stat-value">3</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Avg Days Remaining</div>
        <div class="stat-value" style="color: var(--primary);">7.2d</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Fully Utilized</div>
        <div class="stat-value" style="color: var(--danger);">1</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">High Balance (>15 Days)</div>
        <div class="stat-value" style="color: var(--warning);">0</div>
    </div>
</div>

<div id="entitlement-results-target"></div>

<!-- 2. Add the Modal HTML at the bottom -->
<div class="modal-overlay" id="modal-bulk-assign" onclick="closeModal('modal-bulk-assign', event)">
    <div class="modal-box" style="max-width: 520px; border-radius: 20px;">
        <div class="modal-header" style="padding: 20px 24px;">
            <div>
                <h2 class="modal-title" style="font-size: 1.15rem; font-weight: 800; color: #1e293b;">Bulk Assign Entitlement</h2>
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 2px;">Apply entitlement to all employees in a department at once</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-bulk-assign')" style="background: #f8fafc; border-radius: 10px;">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 24px;">
            <!-- Note Box -->
            <div class="mb-4" style="background: #fff7ed; border-left: 4px solid #f97316; padding: 16px; border-radius: 4px 12px 12px 4px;">
                <p style="font-size: 0.78rem; color: #9a3412; line-height: 1.5;">
                    <b style="color: #7c2d12;">Note:</b> This will INSERT new entitlements only. Existing records for the same employee + leave type + year will not be overwritten.
                </p>
            </div>

            <form id="form-bulk-assign">
                <div class="form-group mb-4">
                    <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">DEPARTMENT *</label>
                    <div class="as-combo-container">
                        <input type="text" id="bulk-dept" class="sel" style="width: 100%; height: 44px; border-radius: 12px;" placeholder="Select department..." onfocus="showAsDrop('as-drop-bulk-dept')" readonly>
                        <div class="as-combo-results" id="as-drop-bulk-dept">
                            <div class="as-res-item" onclick="selectAsItem('bulk-dept','as-drop-bulk-dept','Engineering')">Engineering</div>
                            <div class="as-res-item" onclick="selectAsItem('bulk-dept','as-drop-bulk-dept','Finance')">Finance</div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">LEAVE TYPE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="bulk-type" class="sel" style="width: 100%; height: 44px; border-radius: 12px;" placeholder="Select leave type..." onfocus="showAsDrop('as-drop-bulk-type')" readonly>
                        <div class="as-combo-results" id="as-drop-bulk-type">
                            <div class="as-res-item" onclick="selectAsItem('bulk-type','as-drop-bulk-type','Annual Leave')">Annual Leave</div>
                            <div class="as-res-item" onclick="selectAsItem('bulk-type','as-drop-bulk-type','Sick Leave')">Sick Leave</div>
                        </div>
                    </div>
                </div>

                <div class="form-grid fg-2 mb-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">FISCAL YEAR *</label>
                        <input type="text" class="form-ctrl" value="2026" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">DAYS TO ALLOCATE *</label>
                        <input type="text" class="form-ctrl" placeholder="e.g. 20" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer" style="padding: 20px 24px; background: #fff; border-top: 1.5px solid #f1f5f9; border-radius: 0 0 20px 20px;">
            <button class="btn btn-secondary" onclick="closeModal('modal-bulk-assign')" style="background: #f1f5f9; border: none; color: #475569; padding: 12px 24px; font-weight: 700; border-radius: 10px;">Cancel</button>
            <button class="btn btn-primary" onclick="processBulkAssign(this)" style="background: #15b201; border: none; padding: 12px 24px; font-weight: 700; border-radius: 10px;">
                <i data-lucide="layers" size="18" style="margin-right:8px"></i> Assign to Department
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-add-entitlement" onclick="closeModal('modal-add-entitlement', event)">
    <div class="modal-box" style="max-width: 520px; border-radius: 20px;">
        <div class="modal-header" style="padding: 20px 24px;">
            <div>
                <h2 class="modal-title" style="font-size: 1.15rem; font-weight: 800; color: #1e293b;">Add Entitlement</h2>
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 2px;">Set leave allocation for one employee</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-add-entitlement')" style="background: #f8fafc; border-radius: 10px;">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 24px;">
            <form id="form-add-entitlement">
                <!-- Employee Search -->
                <div class="form-group mb-4">
                    <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">EMPLOYEE *</label>
                    <input type="text" class="form-ctrl" placeholder="Type name or code..." style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                </div>

                <!-- Leave Type Dropdown -->
                <div class="form-group mb-4">
                    <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">LEAVE TYPE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="add-ent-type" class="sel" style="width: 100%; height: 44px; border-radius: 12px;" placeholder="Select leave type..." onfocus="showAsDrop('as-drop-add-ent-type')" readonly>
                        <div class="as-combo-results" id="as-drop-add-ent-type">
                            <div class="as-res-item" onclick="selectAsItem('add-ent-type','as-drop-add-ent-type','Annual Leave')">Annual Leave</div>
                            <div class="as-res-item" onclick="selectAsItem('add-ent-type','as-drop-add-ent-type','Sick Leave')">Sick Leave</div>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Fiscal Year & Allocated Days -->
                <div class="form-grid fg-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">FISCAL YEAR *</label>
                        <input type="text" class="form-ctrl" value="2026" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">ALLOCATED DAYS *</label>
                        <input type="text" class="form-ctrl" placeholder="e.g. 20" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                </div>

                <!-- Row 2: Carried Over & Used Days -->
                <div class="form-grid fg-2 mb-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">CARRIED OVER DAYS</label>
                        <input type="text" class="form-ctrl" value="0" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">ALREADY USED DAYS</label>
                        <input type="text" class="form-ctrl" value="0" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer" style="padding: 20px 24px; background: #fff; border-top: 1.5px solid #f1f5f9; border-radius: 0 0 20px 20px;">
            <button class="btn btn-secondary" onclick="closeModal('modal-add-entitlement')" style="background: #f1f5f9; border: none; color: #475569; padding: 12px 24px; font-weight: 700; border-radius: 10px;">Cancel</button>
            <button class="btn btn-primary" onclick="saveIndividualEntitlement(this)" style="background: #15b201; border: none; padding: 12px 32px; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 12px rgba(21, 178, 1, 0.2);">
                <i data-lucide="check" size="20" style="margin-right:8px"></i> Save
            </button>
        </div>
    </div>
</div>