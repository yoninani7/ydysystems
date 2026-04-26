<!-- Header Section -->
<div class="page-header">
    <div>
        <h1 class="page-title">Shift Management</h1>
        <p class="page-sub">Define work schedules and assign them to your workforce</p>
    </div>
    <!-- IMPORTANT: Check that this onclick ID matches the modal ID below -->
    <button class="btn btn-primary" onclick="openModal('modal-add-shift')">
        <i data-lucide="plus" size="16"></i> New Shift
    </button>
</div>

<!-- Dynamic Tabs -->
<div class="flex-row mb-4" style="background: #fff; padding: 6px; border-radius: 12px; display: inline-flex; border: 1px solid var(--border);">
    <button class="btn btn-primary btn-sm shift-tab" id="btn-tab-def" onclick="renderShifts()">
        <i data-lucide="clock" size="14"></i> Shift Definitions
    </button>
    <button class="btn btn-secondary btn-sm shift-tab" id="btn-tab-assign" onclick="renderShiftAssignments()" style="border:none;">
        <i data-lucide="users" size="14"></i> Employee Assignments
    </button>
</div>

<!-- Dynamic Container -->
<div id="shift-dynamic-container" class="bento-grid"></div>

<!-- Bottom of shift_management.php -->
<div class="modal-overlay" id="modal-add-shift" onclick="closeModal('modal-add-shift', event)">
    <div class="modal-box" style="max-width: 520px; border-radius: 20px;">
        <div class="modal-header" style="padding: 20px 24px;">
            <div>
                <h2 class="modal-title" style="font-size: 1.15rem; font-weight: 800; color: #1e293b;">New Shift</h2>
                <p style="font-size: 0.78rem; color: var(--muted); margin-top: 2px;">Define schedule parameters for this shift</p>
            </div>
            <button class="icon-btn" onclick="closeModal('modal-add-shift')">
                <i data-lucide="x" size="20"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 24px;">
            <form id="form-new-shift">
                <div class="form-group mb-4">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.02em;">SHIFT NAME *</label>
                    <input type="text" class="form-ctrl" placeholder="e.g. Morning Shift" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                </div>

                <div class="form-grid fg-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.02em;">START TIME *</label>
                        <input type="time" class="form-ctrl" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.02em;">END TIME *</label>
                        <input type="time" class="form-ctrl" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                </div>

                <div class="form-grid fg-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.02em;">GRACE PERIOD (MINUTES)</label>
                        <input type="number" class="form-ctrl" value="15" style="height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.02em;">OVERNIGHT SHIFT?</label>
                        <div class="flex-row" style="justify-content: space-between; background: #fff; padding: 0 16px; border-radius: 12px; border: 1.5px solid #e2e8f0; height: 44px;">
                            <span style="font-size: 0.8rem; color: #64748b;">Crosses midnight</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.02em;">STATUS</label>
                    <div class="as-combo-container">
                        <input type="text" id="as-input-shift-status" class="sel" value="Active" readonly onclick="showAsDrop('as-drop-shift-status')" style="width: 100%; height: 44px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                        <div class="as-combo-results" id="as-drop-shift-status">
                            <div class="as-res-item selected" onclick="selectAsItem('as-input-shift-status','as-drop-shift-status','Active')">Active</div>
                            <div class="as-res-item" onclick="selectAsItem('as-input-shift-status','as-drop-shift-status','Inactive')">Inactive</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="padding: 20px 24px; background: #fff; border-top: 1.5px solid #f1f5f9; border-radius: 0 0 20px 20px;">
            <button class="btn btn-secondary" onclick="closeModal('modal-add-shift')" style="background: #f1f5f9; border: none; color: #475569; padding: 12px 24px; font-weight: 700; border-radius: 10px;">Cancel</button>
            <button class="btn btn-primary" onclick="saveNewShift()" style="background: #15b201; border: none; padding: 12px 28px; font-weight: 700; border-radius: 10px; box-shadow: 0 10px 15px -3px rgba(21, 178, 1, 0.2);">
                <i data-lucide="check" size="18"></i> Create Shift
            </button>
        </div>
    </div>
</div>

 