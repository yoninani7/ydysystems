<div class="page-header">
    <div>
        <h1 class="page-title">Leave Requests</h1>
        <p class="page-sub">Submit, review, and approve employee leave applications</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-new-leave-request')" style="background: #15b201; border-radius: 10px; padding: 10px 24px; font-weight: 700;">
        <i data-lucide="plus" size="18" style="margin-right:8px"></i> New Request
    </button>
</div>

<!-- Stat Cards: Exactly matching image layout -->
<div class="stats-row stats-4 mb-4" style="gap: 20px;">
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 24px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800; letter-spacing: 0.05em;">PENDING APPROVAL</div>
            <div class="stat-value" style="color: #f59e0b; font-size: 2rem;">1</div>
        </div>
        <div style="background: #fffbeb; color: #f59e0b; width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center;"><i data-lucide="clock" size="28"></i></div>
    </div>
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 24px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800; letter-spacing: 0.05em;">APPROVED THIS MONTH</div>
            <div class="stat-value" style="color: #15b201; font-size: 2rem;">1</div>
        </div>
        <div style="background: #f0fdf4; color: #15b201; width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center;"><i data-lucide="check-circle-2" size="28"></i></div>
    </div>
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 24px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800; letter-spacing: 0.05em;">ON LEAVE TODAY</div>
            <div class="stat-value" style="color: #0ea5e9; font-size: 2rem;">0</div>
        </div>
        <div style="background: #f0f9ff; color: #0ea5e9; width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center;"><i data-lucide="users" size="28"></i></div>
    </div>
    <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; border-radius: 16px; padding: 24px;">
        <div>
            <div class="stat-label" style="font-size: 10px; color: #94a3b8; font-weight: 800; letter-spacing: 0.05em;">REJECTED</div>
            <div class="stat-value" style="color: #ef4444; font-size: 2rem;">0</div>
        </div>
        <div style="background: #fef2f2; color: #ef4444; width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center;"><i data-lucide="x-circle" size="28"></i></div>
    </div>
</div>

<!-- Filters Bar --> 
<div class="flex-row mb-4" style="justify-content: space-between; align-items: center;">
    
    <!-- Tab Pill Container -->
    <div class="flex-row" style="background: #fff; padding: 6px; border-radius: 12px; display: inline-flex; border: 1px solid var(--border); box-shadow: var(--shadow);">
        <button class="btn btn-primary btn-sm lr-tab" id="lr-tab-all" onclick="renderLeaveRequests('All')" style="border-radius: 10px; padding: 8px 18px;">
            <i data-lucide="layers" size="14" style="margin-right:6px"></i> All
        </button>
        <button class="btn btn-secondary btn-sm lr-tab" id="lr-tab-pending" onclick="renderLeaveRequests('Pending')" style="border:none; background:transparent; padding: 8px 32px 8px 18px; position: relative; border-radius: 10px;">
            <i data-lucide="clock" size="14" style="margin-right:6px"></i> Pending 
            <span style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #f97316; color: #fff; font-size: 10px; padding: 1px 6px; border-radius: 10px; font-weight: 800;">1</span>
        </button>
        <button class="btn btn-secondary btn-sm lr-tab" id="lr-tab-approved" onclick="renderLeaveRequests('Approved')" style="border:none; background:transparent; padding: 8px 18px; border-radius: 10px;">
            <i data-lucide="check-circle" size="14" style="margin-right:6px"></i> Approved
        </button>
        <button class="btn btn-secondary btn-sm lr-tab" id="lr-tab-rejected" onclick="renderLeaveRequests('Rejected')" style="border:none; background:transparent; padding: 8px 18px; border-radius: 10px;">
            <i data-lucide="x-circle" size="14" style="margin-right:6px"></i> Rejected
        </button>
    </div>

    <!-- Date & Search (Right Side) -->
    <div class="flex-row" style="gap: 12px;">
        <div style="position: relative;">
            <input type="text" class="sel" value="April 2026" style="width: 170px; padding: 8px 14px; border-radius: 12px; font-weight: 700;" readonly>
            <i data-lucide="calendar" size="16" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
        </div>
        <div class="search-inner" style="height: 40px; box-shadow: none; border: 1px solid #e2e8f0; width: 240px; border-radius: 12px;">
            <i data-lucide="search" size="14" style="color: #94a3b8"></i>
            <input type="text" placeholder="Search name or type..." style="font-size: 13px;">
        </div>
    </div>
</div>

<div id="leave-requests-target"></div>

<!-- Modal: New Leave Request (Refined) -->
<div class="modal-overlay" id="modal-new-leave-request" onclick="closeModal('modal-new-leave-request', event)">
    <div class="modal-box" style="max-width: 520px; border-radius: 24px;">
        <div class="modal-header" style="padding: 24px;">
            <div>
                <h2 class="modal-title" style="font-size: 1.2rem; font-weight: 800; color: #1e293b;">New Leave Request</h2>
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 4px;">Submit a leave application for review</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-new-leave-request')" style="background: #f8fafc; border-radius: 12px;">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 0 24px 24px;">
            <form id="form-new-leave">
                <!-- Employee -->
                <div class="form-group mb-4">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">EMPLOYEE *</label>
                    <input type="text" class="form-ctrl" placeholder="Type employee name..." style="height: 46px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                </div>

                <!-- Leave Type -->
                <div class="form-group mb-4">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">LEAVE TYPE *</label>
                    <div class="as-combo-container">
                        <input type="text" id="nl-leave-type" class="sel" style="width: 100%; height: 46px; border-radius: 12px;" placeholder="Select leave type..." onfocus="showAsDrop('as-drop-nl-type')" readonly>
                        <div class="as-combo-results" id="as-drop-nl-type">
                            <div class="as-res-item" onclick="selectAsItem('nl-leave-type','as-drop-nl-type','Annual Leave')">Annual Leave</div>
                            <div class="as-res-item" onclick="selectAsItem('nl-leave-type','as-drop-nl-type','Sick Leave')">Sick Leave</div>
                        </div>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="form-grid fg-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">FROM DATE *</label>
                        <input type="date" id="lr-date-from" class="form-ctrl" onchange="calculateLeaveDays()" style="height: 46px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">TO DATE *</label>
                        <input type="date" id="lr-date-to" class="form-ctrl" onchange="calculateLeaveDays()" style="height: 46px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                </div>

                <!-- High-End Half Day Toggle -->
                <div class="flex-row mb-4" style="justify-content: space-between; background: #fcfdfe; padding: 12px 16px; border-radius: 14px; border: 1.5px solid #eef2f6;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #334155;">Half Day?</div>
                        <div style="font-size: 0.7rem; color: #94a3b8;">Applicable for single-day requests</div>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="lr-half-day-toggle" onchange="calculateLeaveDays()">
                        <span class="slider"></span>
                    </label>
                </div>

                <!-- Calculated Days Result -->
                <div id="lr-calc-box" class="mb-4" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 14px 18px; display: flex; align-items: center; gap: 12px; transition: all 0.3s ease;">
                    <div id="lr-calc-icon" style="color: #94a3b8;"><i data-lucide="calculator" size="20"></i></div>
                    <span id="lr-calc-text" style="font-size: 0.85rem; font-weight: 700; color: #64748b;">Select dates to calculate duration</span>
                </div>

                <!-- Reason -->
                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;">REASON *</label>
                    <textarea class="form-ctrl" placeholder="Explain the purpose of this leave..." style="border-radius: 12px; min-height: 80px; border: 1.5px solid #e2e8f0; padding-top: 12px;"></textarea>
                </div>
            </form>
        </div>

        <div class="modal-footer" style="padding: 20px 24px; background: #fff; border-top: 1px solid #f1f5f9; border-radius: 0 0 24px 24px;">
            <button class="btn btn-secondary" onclick="closeModal('modal-new-leave-request')" style="background: #f8fafc; color: #64748b; border: none; padding: 12px 24px; font-weight: 700;">Cancel</button>
            <button class="btn btn-primary" onclick="submitNewLeaveRequest(this)" style="background: #15b201; border: none; padding: 12px 32px; font-weight: 700; border-radius: 12px; box-shadow: 0 8px 20px -4px rgba(21, 178, 1, 0.3);">
                <i data-lucide="send" size="18" style="margin-right:8px"></i> Submit Request
            </button>
        </div>
    </div>
</div>