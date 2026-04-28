<div class="page-header">
    <div>
        <h1 class="page-title">Disciplinary Actions</h1>
        <p class="page-sub">Formal case management for warnings, suspensions and conduct violations</p>
    </div>
    <button class="btn btn-danger" style="background: #dc2626; padding: 10px 20px; border-radius: 10px;" onclick="openModal('modal-record-disciplinary')">
        <i data-lucide="alert-triangle" size="18"></i> Record Action
    </button>
</div>

<!-- Statistics Cards -->
<div class="stats-row stats-4">
    <div class="stat-card art-card art-expired" style="--accent-color: #ef4444;">
        <div class="art-label">Active Cases</div>
        <div class="art-main-row">
            <div class="art-value">8</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fee2e2; padding: 8px; border-radius: 10px; color: #ef4444;">
                    <i data-lucide="shield-alert" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-leave" style="--accent-color: #f59e0b;">
        <div class="art-label">This Month</div>
        <div class="art-main-row">
            <div class="art-value">2</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fffbeb; padding: 8px; border-radius: 10px; color: #f59e0b;">
                    <i data-lucide="calendar" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-expired" style="--accent-color: #ef4444;">
        <div class="art-label">Repeat Offenders</div>
        <div class="art-main-row">
            <div class="art-value" style="color: #ef4444;">1</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fee2e2; padding: 8px; border-radius: 10px; color: #ef4444;">
                    <i data-lucide="refresh-cw" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-missing" style="--accent-color: #06b6d4;">
        <div class="art-label">Suspensions (YTD)</div>
        <div class="art-main-row">
            <div class="art-value">1</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #ecfeff; padding: 8px; border-radius: 10px; color: #06b6d4;">
                    <i data-lucide="user-x" size="20"></i>
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
            <input type="text" placeholder="Search employee, department..." oninput="filterDisciplinaryTable(this.value)">
        </div>
        <div class="as-combo-container" style="width: 180px;">
            <input type="text" class="sel" value="All Types" readonly style="background: #fff; width: 100%;" onclick="toggleStaticDrop('as-drop-disc-type')">
            <div class="as-combo-results" id="as-drop-disc-type">
                <div class="as-res-item selected">All Types</div>
                <div class="as-res-item">Verbal Warning</div>
                <div class="as-res-item">Written Warning</div>
                <div class="as-res-item">Final Warning</div>
                <div class="as-res-item">Suspension</div>
            </div>
        </div>
    </div>
</div>

<!-- Table Area -->
<div id="disciplinary-table-target">
    <!-- Rendered via core.js -->
</div>

<!-- Modal: Record Disciplinary Action -->
<div class="modal-overlay" id="modal-record-disciplinary" onclick="closeModal('modal-record-disciplinary', event)">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <h2 class="card-title" style="font-size: 1.1rem;">Record Disciplinary Action</h2>
                <p style="font-size: 0.75rem; color: var(--muted); margin-top: 2px;">Document a formal workplace conduct case</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-record-disciplinary')" style="border:none; background:transparent;">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <!-- Employee Selection -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>EMPLOYEE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="disc-input-emp" class="form-ctrl" placeholder="Type employee name or code..." 
                               onfocus="showAsDrop('disc-drop-emp')" oninput="filterAsDrop('disc-input-emp','disc-drop-emp')">
                        <div class="as-combo-results" id="disc-drop-emp"></div>
                    </div>
                </div>

                <!-- Action Type & Incident Date -->
                <div class="form-grid fg-2" style="grid-column: span 2;">
                    <div class="form-group">
                        <label>ACTION TYPE *</label>
                        <div class="as-combo-container">
                            <input type="text" id="disc-input-type" class="form-ctrl" placeholder="Select action type..." 
                                   onfocus="toggleStaticDrop('disc-drop-type')" readonly>
                            <div class="as-combo-results" id="disc-drop-type">
                                <div class="as-res-item" onclick="selectAsItem('disc-input-type', 'disc-drop-type', 'Verbal Warning')">Verbal Warning</div>
                                <div class="as-res-item" onclick="selectAsItem('disc-input-type', 'disc-drop-type', 'Written Warning')">Written Warning</div>
                                <div class="as-res-item" onclick="selectAsItem('disc-input-type', 'disc-drop-type', 'Final Warning')">Final Warning</div>
                                <div class="as-res-item" onclick="selectAsItem('disc-input-type', 'disc-drop-type', 'Suspension')">Suspension</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>INCIDENT DATE *</label>
                        <input type="date" id="disc-incident-date" class="form-ctrl" placeholder="mm/dd/yyyy">
                    </div>
                </div>

                <!-- Issued Date & Issued By -->
                <div class="form-grid fg-2" style="grid-column: span 2;">
                    <div class="form-group">
                        <label>ISSUED DATE *</label>
                        <input type="date" id="disc-issued-date" class="form-ctrl" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>ISSUED BY</label>
                        <input type="text" id="disc-issuer" class="form-ctrl" placeholder="Supervisor / Manager name...">
                    </div>
                </div>

                <!-- Incident Description -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>INCIDENT DESCRIPTION *</label>
                    <textarea id="disc-description" class="form-ctrl" placeholder="Describe the incident, evidence gathered, and the basis for this action..." style="min-height: 120px;"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="background: #fff; padding: 20px 24px;">
            <button class="btn btn-secondary" onclick="closeModal('modal-record-disciplinary')" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; background: #eff4f9; border:none; color:#1e293b;">Cancel</button>
            <button class="btn btn-danger" onclick="submitDisciplinaryAction(this)" style="background: #dc2626; padding: 10px 24px; border-radius: 10px; font-weight: 700; flex: 1; justify-content: center;">
                <i data-lucide="check" size="18"></i> Record Action
            </button>
        </div>
    </div>
</div>
<script>
    setTimeout(() => {
        if(typeof renderDisciplinaryTable === 'function') renderDisciplinaryTable();
    }, 50);
</script>