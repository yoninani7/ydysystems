<div class="page-header">
    <div>
        <h1 class="page-title">Promote / Demote</h1>
        <p class="page-sub">Manage all employee position changes and salary adjustments</p>
    </div>
    <!-- Ensure the onclick matches the Modal ID at the bottom -->
<button class="btn btn-primary" onclick="openModal('modal-initiate-movement')" style="background: var(--primary); border-radius: 10px; padding: 10px 24px; font-weight: 700;">
    <i data-lucide="trending-up" size="18" style="margin-right:8px"></i> Initiate Change
</button>

</div>

<!-- Stat Cards -->
<div class="stats-row stats-4 mb-4" style="gap: 20px;">
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 20px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800;">PENDING APPROVALS</div>
            <div class="stat-value" style="color: #f59e0b; font-size: 1.8rem;">2</div>
        </div>
        <div style="background: #fffbeb; color: #f59e0b; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i data-lucide="clock" size="22"></i></div>
    </div>
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 20px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800;">THIS MONTH</div>
            <div class="stat-value" style="color: #1e293b; font-size: 1.8rem;">3</div>
        </div>
        <div style="background: #f0f9ff; color: #0ea5e9; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i data-lucide="calendar" size="22"></i></div>
    </div>
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 20px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800;">PROMOTIONS (YTD)</div>
            <div class="stat-value" style="color: #15b201; font-size: 1.8rem;">6</div>
        </div>
        <div style="background: #f0fdf4; color: #15b201; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i data-lucide="arrow-up-circle" size="22"></i></div>
    </div>
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 20px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800;">DEMOTIONS (YTD)</div>
            <div class="stat-value" style="color: #ef4444; font-size: 1.8rem;">2</div>
        </div>
        <div style="background: #fef2f2; color: #ef4444; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i data-lucide="arrow-down-circle" size="22"></i></div>
    </div>
</div>

<!-- Custom Filter Bar (Refined) -->
<div class="flex-row mb-4" style="gap: 12px; align-items: center;">
    
    <!-- Search Bar -->
    <div class="search-inner" style="height: 40px; box-shadow: none; border: 1px solid #e2e8f0; width: 320px; border-radius: 12px;">
        <i data-lucide="search" size="14" style="color: #94a3b8"></i>
        <input type="text" placeholder="Search employee, position, department..." style="font-size: 13px;">
    </div>

    <!-- Movement Type Filter -->
    <div class="as-combo-container" style="width: 150px;">
        <input type="text" id="filter-mv-type" class="sel" style="width: 100%; border-radius: 12px; height: 40px;" 
               value="All Types" onfocus="showAsDrop('as-drop-mv-type')" readonly>
        
        <div class="as-combo-results" id="as-drop-mv-type"> 
            <div class="as-res-item selected" onclick="selectAsItem('filter-mv-type','as-drop-mv-type','All Types')">All Types</div>
            <div class="as-res-item" onclick="selectAsItem('filter-mv-type','as-drop-mv-type','Promotion')">Promotion</div>
            <div class="as-res-item" onclick="selectAsItem('filter-mv-type','as-drop-mv-type','Demotion')">Demotion</div>
        </div> 
    </div>

    <!-- Status Filter -->
    <div class="as-combo-container" style="width: 160px;">
        <input type="text" id="filter-mv-status" class="sel" style="width: 100%; border-radius: 12px; height: 40px;" 
               value="All Statuses" onfocus="showAsDrop('as-drop-mv-status')" readonly>
        
        <div class="as-combo-results" id="as-drop-mv-status"> 
            <div class="as-res-item selected" onclick="selectAsItem('filter-mv-status','as-drop-mv-status','All Statuses')">All Statuses</div>
            <div class="as-res-item" onclick="selectAsItem('filter-mv-status','as-drop-mv-status','Pending')">Pending</div>
            <div class="as-res-item" onclick="selectAsItem('filter-mv-status','as-drop-mv-status','Approved')">Approved</div>
            <div class="as-res-item" onclick="selectAsItem('filter-mv-status','as-drop-mv-status','Processing')">Processing</div>
        </div> 
    </div>

    <!-- Refresh Button -->
    <button class="btn btn-secondary" style="height: 40px; background: #eff4f9; border: none; border-radius: 12px; padding: 0 16px;" onclick="renderPromoteDemoteTable()">
        <i data-lucide="refresh-cw" size="14"></i>
    </button>
</div>

<div id="tbl-Promote/Demote"></div>

<!-- Modal: View Movement Detail -->
<div class="modal-overlay" id="modal-view-movement" onclick="closeModal('modal-view-movement', event)">
    <div class="modal-box" style="max-width: 580px; border-radius: 24px;">
        <div class="modal-header" style="padding: 24px 30px; border-bottom: 1px solid #f1f5f9;">
            <div>
                <h2 class="modal-title" id="mv-modal-title" style="font-size: 1.25rem; font-weight: 800; color: #1e293b;">--</h2>
                <p id="mv-modal-sub" style="font-size: 0.85rem; color: #94a3b8; font-weight: 600; margin-top: 4px;">--</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-view-movement')" style="background: #f8fafc; border-radius: 12px;">
                <i data-lucide="x" size="20" style="color: #64748b;"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <!-- Row 1 -->
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">CHANGE TYPE</label>
                <div id="mv-modal-type" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">STATUS</label>
                <div id="mv-modal-status" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>

            <!-- Row 2 -->
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">FROM POSITION</label>
                <div id="mv-modal-from" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">TO POSITION</label>
                <div id="mv-modal-to" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>

            <!-- Row 3 -->
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">OLD SALARY</label>
                <div id="mv-modal-sal-old" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">NEW SALARY</label>
                <div id="mv-modal-sal-new" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>

            <!-- Row 4 -->
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">EFFECTIVE DATE</label>
                <div id="mv-modal-date" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">DEPARTMENT</label>
                <div id="mv-modal-dept" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">--</div>
            </div>

            <!-- Row 5 (Full Width) -->
            <div class="info-tile" style="background: #f8fafc; padding: 16px 20px; border-radius: 12px; grid-column: span 2;">
                <label style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: block;">REASON / JUSTIFICATION</label>
                <div id="mv-modal-reason" style="font-weight: 700; color: #1e293b; font-size: 0.9rem; line-height: 1.5;">--</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Initiate Position Change (Wizard) -->
<div class="modal-overlay" id="modal-initiate-movement" onclick="closeModal('modal-initiate-movement', event)">
    <div class="modal-box" style="max-width: 580px; border-radius: 24px;">
        <div class="modal-header" style="padding: 24px 30px;">
            <div>
                <h2 class="modal-title" style="font-size: 1.2rem; font-weight: 800; color: #1e293b;">Initiate Position Change</h2>
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 2px;">Record a formal promotion or demotion for an employee</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-initiate-movement')">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 0 30px 30px;">
            <!-- Wizard Progress Header -->
            <div class="flex-row mb-5" style="justify-content: center; position: relative; padding-top: 10px;">
                <div style="position: absolute; top: 22px; left: 15%; right: 15%; height: 2px; background: #e2e8f0; z-index: 1;">
                    <div id="mv-progress-line" style="width: 0%; height: 100%; background: #15b201; transition: width 0.3s ease;"></div>
                </div>
                
                <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; width: 100%;">
                    <div class="mv-step-indicator active" id="mv-ind-1" style="text-align: center; width: 100px;">
                        <div class="mv-step-num" style="width: 32px; height: 32px; border-radius: 50%; background: #15b201; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800; font-size: 14px;">1</div>
                        <div style="font-size: 10px; font-weight: 800; color: #15b201; text-transform: uppercase;">Employee & Role</div>
                    </div>
                    <div class="mv-step-indicator" id="mv-ind-2" style="text-align: center; width: 100px;">
                        <div class="mv-step-num" style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800; font-size: 14px; border: 2px solid #e2e8f0;">2</div>
                        <div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Dates & Salary</div>
                    </div>
                </div>
            </div>
<br>
            <!-- STEP 1: Employee & Role -->
            <div id="mv-step-1-content">
                <div class="form-group mb-4">
                    <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">EMPLOYEE *</label>
                    <input type="text" class="form-ctrl" placeholder="Type employee name..." style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                </div>
                <div class="form-group mb-4">
                    <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">CURRENT POSITION (AUTO-FILLED)</label>
                    <input type="text" class="form-ctrl" value="Select employee first" disabled style="height: 44px; border-radius: 12px; background: #f8fafc;">
                </div>
                <div class="form-grid fg-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">CHANGE TYPE *</label>
                        <input type="text" class="form-ctrl" placeholder="Promotion or Demotion?" style="height: 44px; border-radius: 12px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">NEW POSITION *</label>
                        <input type="text" class="form-ctrl" placeholder="Select new position..." style="height: 44px; border-radius: 12px;">
                    </div>
                </div>
            </div>

            <!-- STEP 2: Dates & Salary (Hidden initially) -->
            <div id="mv-step-2-content" style="display: none;">
                <div class="form-grid fg-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">EFFECTIVE DATE *</label>
                        <input type="date" class="form-ctrl" style="height: 44px; border-radius: 12px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">APPROVED BY</label>
                        <input type="text" class="form-ctrl" placeholder="Approving manager..." style="height: 44px; border-radius: 12px;">
                    </div>
                </div>
                <div class="form-grid fg-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">CURRENT SALARY (ETB)</label>
                        <input type="text" class="form-ctrl" value="28000" disabled style="height: 44px; border-radius: 12px; background: #f8fafc;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">NEW SALARY (ETB) *</label>
                        <input type="text" class="form-ctrl" placeholder="Enter new salary..." style="height: 44px; border-radius: 12px;">
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">REASON / JUSTIFICATION *</label>
                    <textarea class="form-ctrl" placeholder="Document the basis for this change..." style="border-radius: 12px; min-height: 100px; padding-top: 12px;"></textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="padding: 20px 30px; background: #fff; border-top: 1px solid #f1f5f9; border-radius: 0 0 24px 24px; justify-content: flex-end;">
            <!-- Footer Step 1 -->
            <div id="mv-footer-1" class="flex-row" style="gap: 12px;">
                <button class="btn btn-secondary" onclick="closeModal('modal-initiate-movement')" style="background: #f1f5f9; border: none; padding: 10px 24px; font-weight: 700; border-radius: 10px;">Cancel</button>
                <button class="btn btn-primary" onclick="switchMvStep(2)" style="background: #15b201; border: none; padding: 10px 24px; font-weight: 700; border-radius: 10px;">
                    Next <i data-lucide="chevron-right" size="18" style="margin-left: 6px;"></i>
                </button>
            </div>
            <!-- Footer Step 2 -->
            <div id="mv-footer-2" class="flex-row" style="gap: 12px; display: none;">
                <button class="btn btn-secondary" onclick="switchMvStep(1)" style="background: #f1f5f9; border: none; padding: 10px 24px; font-weight: 700; border-radius: 10px;">
                    <i data-lucide="chevron-left" size="18" style="margin-right: 6px;"></i> Back
                </button>
                <button class="btn btn-primary" onclick="submitMovementRequest(this)" style="background: #15b201; border: none; padding: 10px 24px; font-weight: 700; border-radius: 10px;">
                    <i data-lucide="check" size="18" style="margin-right: 8px;"></i> Submit for Approval
                </button>
            </div>
        </div>
    </div>
</div>