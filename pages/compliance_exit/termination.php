<div class="page-header">
    <div>
        <h1 class="page-title">Separation & Exit</h1>
        <p class="page-sub">End-to-end offboarding workflow tracker</p>
    </div>
    <button class="btn btn-danger" style="background: #dc2626; padding: 10px 20px; border-radius: 10px;" onclick="openModal('modal-initiate-separation')">
        <i data-lucide="user-minus" size="18"></i> Initiate Separation
    </button>
</div>

<!-- High-End Statistics Cards -->
<div class="stats-row stats-4">
    <div class="stat-card art-card art-leave" style="--accent-color: #f59e0b;">
        <div class="art-label">Turnover Rate (YTD)</div>
        <div class="art-main-row">
            <div class="art-value">4.2%</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fffbeb; padding: 8px; border-radius: 10px; color: #f59e0b;">
                    <i data-lucide="trending-down" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-leave" style="--accent-color: #f59e0b;">
        <div class="art-label">In Offboarding</div>
        <div class="art-main-row">
            <div class="art-value" style="color: #f59e0b;">3</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fffbeb; padding: 8px; border-radius: 10px; color: #f59e0b;">
                    <i data-lucide="user-x" size="20"></i> 
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-units" style="--accent-color: #0ea5e9;">
        <div class="art-label">Exits This Month</div>
        <div class="art-main-row">
            <div class="art-value">0</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f0f9ff; padding: 8px; border-radius: 10px; color: #0ea5e9;">
                    <i data-lucide="calendar" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-expired" style="--accent-color: #ef4444;">
        <div class="art-label">Pending Clearance</div>
        <div class="art-main-row">
            <div class="art-value" style="color: #ef4444;">1</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fee2e2; padding: 8px; border-radius: 10px; color: #ef4444;">
                    <i data-lucide="clipboard-x" size="20"></i>
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
            <input type="text" placeholder="Search employee, type..." oninput="filterSeparationTable(this.value)">
        </div>
        <div class="as-combo-container" style="width: 180px;">
            <input type="text" class="sel" value="All Statuses" readonly style="background: #fff; width: 100%;" onclick="toggleStaticDrop('as-drop-sep-status')">
            <div class="as-combo-results" id="as-drop-sep-status">
                <div class="as-res-item selected">All Statuses</div>
                <div class="as-res-item">In Progress</div>
                <div class="as-res-item">Complete</div>
            </div>
        </div>
    </div>
</div>

<!-- Table Container -->
<div id="separation-table-target"></div>

<!-- Modal: Initiate Separation Wizard -->
<div class="modal-overlay" id="modal-initiate-separation" onclick="closeModal('modal-initiate-separation', event)">
    <div class="modal-box" style="max-width: 640px; overflow: visible;">
        <div class="modal-header">
            <div>
                <h2 class="card-title" style="font-size: 1.1rem;">Initiate Separation</h2>
                <p style="font-size: 0.75rem; color: var(--muted); margin-top: 2px;">Begin the formal offboarding process for an employee</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-initiate-separation')" style="border:none; background:transparent;">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 30px 40px;">
            <!-- Progress Indicator -->
            <div class="sep-wizard-nav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; position: relative;">
                <div style="position: absolute; top: 18px; left: 40px; right: 40px; height: 2px; background: #e2e8f0; z-index: 1;">
                    <div id="sep-progress-line" style="width: 0%; height: 100%; background: #15b201; transition: width 0.3s ease;"></div>
                </div>
                
                <!-- Step 1 Indicator -->
                <div class="sep-step-ind active" id="sep-ind-1" style="position: relative; z-index: 2; text-align: center; width: 100px;">
                    <div class="sep-step-num" style="width: 36px; height: 36px; border-radius: 50%; background: #15b201; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; margin: 0 auto 8px; border: 4px solid #fff; box-shadow: 0 0 0 1px #15b201;">1</div>
                    <div style="font-size: 10px; font-weight: 800; color: #15b201; text-transform: uppercase;">Employee & Type</div>
                </div>

                <!-- Step 2 Indicator -->
                <div class="sep-step-ind" id="sep-ind-2" style="position: relative; z-index: 2; text-align: center; width: 100px;">
                    <div class="sep-step-num" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: 800; margin: 0 auto 8px; border: 4px solid #fff; box-shadow: 0 0 0 1px #e2e8f0;">2</div>
                    <div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Dates & Details</div>
                </div>

                <!-- Step 3 Indicator -->
                <div class="sep-step-ind" id="sep-ind-3" style="position: relative; z-index: 2; text-align: center; width: 100px;">
                    <div class="sep-step-num" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: 800; margin: 0 auto 8px; border: 4px solid #fff; box-shadow: 0 0 0 1px #e2e8f0;">3</div>
                    <div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Settlement</div>
                </div>
            </div>

            <!-- STEP 1 CONTENT -->
            <div id="sep-step-1-content" class="sep-step-content">
                <div class="form-group mb-4">
                    <label>EMPLOYEE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="sep-input-emp" class="form-ctrl" placeholder="Type employee name..." onfocus="showAsDrop('sep-drop-emp')" oninput="filterAsDrop('sep-input-emp','sep-drop-emp')">
                        <div class="as-combo-results" id="sep-drop-emp"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>SEPARATION TYPE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="sep-input-type" class="form-ctrl" placeholder="Select separation type..." onclick="toggleStaticDrop('sep-drop-type')" readonly>
                        <div class="as-combo-results" id="sep-drop-type">
                            <div class="as-res-item" onclick="selectAsItem('sep-input-type', 'sep-drop-type', 'Resignation')">Resignation</div>
                            <div class="as-res-item" onclick="selectAsItem('sep-input-type', 'sep-drop-type', 'Involuntary')">Involuntary</div>
                            <div class="as-res-item" onclick="selectAsItem('sep-input-type', 'sep-drop-type', 'Retirement')">Retirement</div>
                            <div class="as-res-item" onclick="selectAsItem('sep-input-type', 'sep-drop-type', 'End of Contract')">End of Contract</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2 CONTENT -->
            <div id="sep-step-2-content" class="sep-step-content" style="display: none;">
                <div class="form-grid fg-2 mb-4">
                    <div class="form-group">
                        <label>NOTICE DATE *</label>
                        <input type="date" id="sep-notice-date" class="form-ctrl" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="form-group">
                        <label>LAST WORKING DAY *</label>
                        <input type="date" id="sep-last-day" class="form-ctrl" placeholder="mm/dd/yyyy">
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label>PROCESSED BY (HR)</label>
                    <input type="text" id="sep-processed-by" class="form-ctrl" placeholder="HR staff member...">
                </div>
                <div class="form-group">
                    <label>NOTES</label>
                    <textarea id="sep-notes" class="form-ctrl" placeholder="Context, linked disciplinary case #, resignation ref #..." style="min-height: 100px;"></textarea>
                </div>
            </div>

            <!-- STEP 3 CONTENT -->
            <div id="sep-step-3-content" class="sep-step-content" style="display: none;">
                <div class="form-group mb-4">
                    <label>FINAL SETTLEMENT AMOUNT</label>
                    <input type="number" id="sep-settlement" class="form-ctrl" placeholder="0.00" step="0.01">
                    <p style="font-size: 0.7rem; color: var(--muted); margin-top: 6px;">Leave blank if not yet calculated. Can be updated later.</p>
                </div>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; display: flex; gap: 16px; align-items: flex-start;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid #15b201; color: #15b201; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="info" size="20"></i>
                    </div>
                    <div>
                        <b style="font-size: 0.85rem; color: #1e293b; display: block; margin-bottom: 4px;">Exit Clearance Will Be Auto-Created</b>
                        <p style="font-size: 0.78rem; color: #64748b; line-height: 1.5;">Saving this separation will automatically create an Exit Clearance checklist for this employee. HR, IT, Finance, Admin, and Assets will need to sign off.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="background: #f8fafc; padding: 20px 24px;">
            <!-- Step 1 Footer -->
            <div id="sep-footer-1" style="display: flex; gap: 12px; width: 100%; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeModal('modal-initiate-separation')" style="padding: 10px 24px; border-radius: 10px; font-weight: 700;">Cancel</button>
                <button class="btn btn-primary" onclick="switchSepStep(2)" style="background: #15b201; padding: 10px 24px; border-radius: 10px; font-weight: 700;">Next <i data-lucide="chevron-right" size="18"></i></button>
            </div>
            <!-- Step 2 Footer -->
            <div id="sep-footer-2" style="display: none; gap: 12px; width: 100%; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="switchSepStep(1)" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; background:#eff4f9; border:none;"><i data-lucide="chevron-left" size="18"></i> Back</button>
                <button class="btn btn-primary" onclick="switchSepStep(3)" style="background: #15b201; padding: 10px 24px; border-radius: 10px; font-weight: 700;">Next <i data-lucide="chevron-right" size="18"></i></button>
            </div>
            <!-- Step 3 Footer -->
            <div id="sep-footer-3" style="display: none; gap: 12px; width: 100%; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="switchSepStep(2)" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; background:#eff4f9; border:none;"><i data-lucide="chevron-left" size="18"></i> Back</button>
                <button class="btn btn-danger" onclick="submitInitiateSeparation(this)" style="background: #dc2626; padding: 10px 24px; border-radius: 10px; font-weight: 700; flex: 1; justify-content: center;">
                    <i data-lucide="user-minus" size="18"></i> Initiate Separation
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(() => {
        if(typeof renderSeparations === 'function') renderSeparations();
    }, 50);
</script>