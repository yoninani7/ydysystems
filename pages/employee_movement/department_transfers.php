<div class="page-header">
    <div>
        <h1 class="page-title">Department Transfers</h1>
        <p class="page-sub">Interdepartmental and cross-branch employee movements</p>
    </div>
    <div class="flex-row">
        <button class="btn btn-primary" style="background: #15b201; padding: 10px 20px; border-radius: 12px;" onclick="openModal('modal-initiate-transfer')">
            <i data-lucide="arrow-right-left" size="18"></i> Initiate Transfer
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-row stats-4">
    <div class="stat-card art-card art-leave" style="--accent-color: #f59e0b;">
        <div class="art-label">Pending Transfers</div>
        <div class="art-main-row">
            <div class="art-value">4</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fffbeb; padding: 8px; border-radius: 10px;">
                    <i data-lucide="clock" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-units" style="--accent-color: #0ea5e9;">
        <div class="art-label">This Month</div>
        <div class="art-main-row">
            <div class="art-value">1</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f0f9ff; padding: 8px; border-radius: 10px;">
                    <i data-lucide="calendar" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-headcount" style="--accent-color: #15b201;">
        <div class="art-label">Cross-Branch</div>
        <div class="art-main-row">
            <div class="art-value">4</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f1fcf0; padding: 8px; border-radius: 10px;">
                    <i data-lucide="git-branch" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-headcount" style="--accent-color: #15b201;">
        <div class="art-label">Approved (YTD)</div>
        <div class="art-main-row">
            <div class="art-value">3</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f1fcf0; padding: 8px; border-radius: 10px;">
                    <i data-lucide="check-circle" size="20"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: none; background: transparent;">
    <div class="flex-row" style="gap: 12px;">
        <div class="search-inner" style="max-width: 350px; background: #fff;">
            <i data-lucide="search" size="16"></i>
            <input type="text" placeholder="Search employee, department, branch..." oninput="filterTransferTable(this.value)">
        </div>
        <div class="as-combo-container" style="width: 180px;">
            <input type="text" class="sel" value="All Statuses" readonly style="background: #fff; width: 100%;" onclick="toggleStaticDrop('as-drop-tr-status')">
            <div class="as-combo-results" id="as-drop-tr-status">
                <div class="as-res-item selected" onclick="selectThemedItem(this, '')">All Statuses</div>
                <div class="as-res-item">Pending</div>
                <div class="as-res-item">Approved</div>
                <div class="as-res-item">Rejected</div>
            </div>
        </div>
    </div>
</div>

<!-- Table Area -->
<div id="transfer-table-target">
    <!-- Rendered via JS -->
</div>

<!-- Modal: Initiate Transfer -->
<div class="modal-overlay" id="modal-initiate-transfer" onclick="closeModal('modal-initiate-transfer', event)">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <h2 class="card-title" style="font-size: 1.1rem;">Initiate Transfer</h2>
                <p style="font-size: 0.75rem; color: var(--muted); margin-top: 2px;">Request an employee move to a new department or branch</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-initiate-transfer')" style="border:none; background:transparent;">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <!-- Employee Selection -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>EMPLOYEE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="tr-input-emp" class="form-ctrl" placeholder="Type employee name..." 
                               onfocus="showAsDrop('tr-drop-emp')" oninput="filterAsDrop('tr-input-emp','tr-drop-emp')">
                        <div class="as-combo-results" id="tr-drop-emp">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>

                <!-- Current Info (Auto) -->
                <div class="form-grid fg-2" style="grid-column: span 2;">
                    <div class="form-group">
                        <label>CURRENT DEPARTMENT (AUTO)</label>
                        <input type="text" id="tr-curr-dept" class="form-ctrl" value="Select employee" disabled style="background:#f8fafc;">
                    </div>
                    <div class="form-group">
                        <label>CURRENT BRANCH (AUTO)</label>
                        <input type="text" id="tr-curr-branch" class="form-ctrl" value="Select employee" disabled style="background:#f8fafc;">
                    </div>
                </div>

                <!-- Transfer Destinations -->
                <div class="form-grid fg-2" style="grid-column: span 2;">
                    <div class="form-group">
                        <label>TRANSFER TO DEPARTMENT *</label>
                        <div class="as-combo-container">
                            <input type="text" id="tr-input-dept" class="form-ctrl" placeholder="Select department..." 
                                   onfocus="showAsDrop('tr-drop-dept')" oninput="filterAsDrop('tr-input-dept','tr-drop-dept')">
                            <div class="as-combo-results" id="tr-drop-dept"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>TRANSFER TO BRANCH *</label>
                        <div class="as-combo-container">
                            <input type="text" id="tr-input-branch" class="form-ctrl" placeholder="Select branch..." 
                                   onfocus="showAsDrop('tr-drop-branch')" oninput="filterAsDrop('tr-input-branch','tr-drop-branch')">
                            <div class="as-combo-results" id="tr-drop-branch"></div>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="form-grid fg-2" style="grid-column: span 2;">
                    <div class="form-group">
                        <label>REQUEST DATE *</label>
                        <input type="date" id="tr-req-date" class="form-ctrl" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>EFFECTIVE DATE *</label>
                        <input type="date" id="tr-eff-date" class="form-ctrl" placeholder="mm/dd/yyyy">
                    </div>
                </div>

                <!-- Reason -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>REASON / BUSINESS JUSTIFICATION *</label>
                    <textarea id="tr-reason" class="form-ctrl" placeholder="State the business need or employee request rationale..." style="min-height: 100px;"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="background: #fff; padding: 20px 24px;">
            <button class="btn btn-secondary" onclick="closeModal('modal-initiate-transfer')" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; background: #eff4f9; border:none; color:#1e293b;">Cancel</button>
            <button class="btn btn-primary" onclick="submitTransferRequest(this)" style="background: #15b201; padding: 10px 24px; border-radius: 10px; font-weight: 700; flex: 1; justify-content: center;">
                <i data-lucide="check" size="18"></i> Submit Transfer Request
            </button>
        </div>
    </div>
</div>

<script>
    // Initialize standard page load
    setTimeout(() => {
        if(typeof renderDepartmentTransfers === 'function') renderDepartmentTransfers();
    }, 50);
</script>