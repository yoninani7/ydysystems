<div class="page-header">
    <div>
        <h1 class="page-title">Resignations & Grievances</h1>
        <p class="page-sub">Track resignation notices and employee grievance cases</p>
    </div>
    <button class="btn btn-primary" style="background: #15b201; padding: 10px 20px; border-radius: 10px;" onclick="openModal('modal-log-resignation')">
        <i data-lucide="plus" size="18"></i> Log Record
    </button>
</div>

<!-- Statistics Cards -->
<div class="stats-row stats-4">
    <div class="stat-card art-card art-units" style="--accent-color: #0ea5e9;">
        <div class="art-label">Total Records</div>
        <div class="art-main-row">
            <div class="art-value">5</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f1fcf0; padding: 8px; border-radius: 10px; color: #15b201;">
                    <i data-lucide="archive" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-leave" style="--accent-color: #f59e0b;">
        <div class="art-label">Pending</div>
        <div class="art-main-row">
            <div class="art-value" style="color: #f59e0b;">3</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fffbeb; padding: 8px; border-radius: 10px; color: #f59e0b;">
                    <i data-lucide="clock" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-units" style="--accent-color: #06b6d4;">
        <div class="art-label">Under Review</div>
        <div class="art-main-row">
            <div class="art-value" style="color: #0ea5e9;">1</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #ecfeff; padding: 8px; border-radius: 10px; color: #06b6d4;">
                    <div style="width:20px; height:20px; background:#cffafe; border-radius:4px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-headcount" style="--accent-color: #15b201;">
        <div class="art-label">Resolved</div>
        <div class="art-main-row">
            <div class="art-value" style="color: #15b201;">1</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f1fcf0; padding: 8px; border-radius: 10px; color: #15b201;">
                    <i data-lucide="check-circle" size="20"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integrated Filter Bar (Tabs + Search) -->
<div class="flex-row mb-4" style="justify-content: space-between; align-items: center;">
    <div class="card" style="border-radius: 10px; padding: 4px; display: flex; gap: 4px; flex-direction: row; border: 1px solid #e2e8f0; background: #fff;">
    <button class="btn btn-sm res-tab" id="tab-all" onclick="renderResignations('All', this)">All</button>
    <button class="btn btn-sm res-tab" onclick="renderResignations('Pending', this)">Pending</button>
    <button class="btn btn-sm res-tab" onclick="renderResignations('Under Review', this)">Under Review</button>
    <button class="btn btn-sm res-tab" onclick="renderResignations('Resolved', this)">Resolved</button>
</div>
    <div class="search-inner" style="width: 300px; background: #fff;">
        <i data-lucide="search" size="16"></i>
        <input type="text" placeholder="Search employee, type..." oninput="filterResignationTable(this.value)">
    </div>
</div>

<div id="resignation-table-target">
    <!-- Rendered via core.js -->
</div>

<!-- Modal: Log Resignation / Grievance -->
<div class="modal-overlay" id="modal-log-resignation" onclick="closeModal('modal-log-resignation', event)">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <h2 class="card-title" style="font-size: 1.1rem;">Log Resignation / Grievance</h2>
                <p style="font-size: 0.75rem; color: var(--muted); margin-top: 2px;">Record an employee's resignation notice or workplace complaint</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-log-resignation')" style="border:none; background:transparent;">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <!-- Employee -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>EMPLOYEE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="res-input-emp" class="form-ctrl" placeholder="Type employee name..." 
                               onfocus="showAsDrop('res-drop-emp')" oninput="filterAsDrop('res-input-emp','res-drop-emp')">
                        <div class="as-combo-results" id="res-drop-emp"></div>
                    </div>
                </div>

                <!-- Record Type & Reason Type -->
                <div class="form-grid fg-2" style="grid-column: span 2;">
                    <div class="form-group">
                        <label>RECORD TYPE *</label>
                        <div class="as-combo-container">
                            <input type="text" id="res-input-cat" class="form-ctrl" placeholder="Resignation or Grievance?" 
                                   onclick="toggleStaticDrop('res-drop-cat')" readonly>
                            <div class="as-combo-results" id="res-drop-cat">
                                <div class="as-res-item" onclick="selectAsItem('res-input-cat', 'res-drop-cat', 'Resignation')">Resignation</div>
                                <div class="as-res-item" onclick="selectAsItem('res-input-cat', 'res-drop-cat', 'Grievance')">Grievance</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>REASON TYPE *</label>
                        <div class="as-combo-container">
                            <input type="text" id="res-input-reason" class="form-ctrl" placeholder="Select reason..." 
                                   onclick="toggleStaticDrop('res-drop-reason')" readonly>
                            <div class="as-combo-results" id="res-drop-reason">
                                <div class="as-res-item" onclick="selectAsItem('res-input-reason', 'res-drop-reason', 'Personal')">Personal</div>
                                <div class="as-res-item" onclick="selectAsItem('res-input-reason', 'res-drop-reason', 'Harassment')">Harassment</div>
                                <div class="as-res-item" onclick="selectAsItem('res-input-reason', 'res-drop-reason', 'Pay Dispute')">Pay Dispute</div>
                                <div class="as-res-item" onclick="selectAsItem('res-input-reason', 'res-drop-reason', 'Work Conditions')">Work Conditions</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filed Date & Priority -->
                <div class="form-grid fg-2" style="grid-column: span 2;">
                    <div class="form-group">
                        <label>FILED DATE *</label>
                        <input type="date" id="res-filed-date" class="form-ctrl" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>PRIORITY</label>
                        <div class="as-combo-container">
                            <input type="text" id="res-input-priority" class="form-ctrl" value="Medium" 
                                   onclick="toggleStaticDrop('res-drop-priority')" readonly>
                            <div class="as-combo-results" id="res-drop-priority">
                                <div class="as-res-item" onclick="selectAsItem('res-input-priority', 'res-drop-priority', 'High')">High</div>
                                <div class="as-res-item" onclick="selectAsItem('res-input-priority', 'res-drop-priority', 'Medium')">Medium</div>
                                <div class="as-res-item" onclick="selectAsItem('res-input-priority', 'res-drop-priority', 'Low')">Low</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assign To -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>ASSIGN TO</label>
                    <div class="as-combo-container">
                        <input type="text" id="res-input-assign" class="form-ctrl" placeholder="HR staff member..." 
                               onfocus="showAsDrop('res-drop-assign')" oninput="filterAsDrop('res-input-assign','res-drop-assign')">
                        <div class="as-combo-results" id="res-drop-assign"></div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>NOTES / DETAILS</label>
                    <textarea id="res-notes" class="form-ctrl" placeholder="Additional context, verbal statements, or action notes..." style="min-height: 100px;"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="background: #fff; padding: 20px 24px;">
            <button class="btn btn-secondary" onclick="closeModal('modal-log-resignation')" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; background: #eff4f9; border:none; color:#1e293b;">Cancel</button>
            <button class="btn btn-primary" onclick="submitResignationLog(this)" style="background: #15b201; padding: 10px 24px; border-radius: 10px; font-weight: 700; flex: 1; justify-content: center;">
                <i data-lucide="check" size="18"></i> Save Record
            </button>
        </div>
    </div>
</div>
<script>
    setTimeout(() => {
        if(typeof renderResignations === 'function') renderResignations('All');
    }, 50);
</script>

<style>
    .res-tab.active { background: #15b201 !important; color: #fff !important; }
</style>