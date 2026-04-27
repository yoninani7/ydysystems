<div class="page-header">
    <div>
        <h1 class="page-title">Leave Policy</h1>
        <p class="page-sub">Configure leave types, entitlement rules, and company-wide restrictions</p>
    </div> 
</div>

<!-- Navigation Tabs -->
<div class="flex-row mb-4" style="background: #fff; padding: 6px; border-radius: 14px; display: inline-flex; border: 1px solid var(--border); box-shadow: var(--shadow);">
    <button class="btn btn-primary btn-sm policy-tab" id="tab-lt" onclick="renderLeaveTypes()" style="border-radius: 10px; padding: 8px 18px;">
        <i data-lucide="tag" size="14" style="margin-right:8px"></i> Leave Types
    </button>
    <!-- Inside pages/leave_management/leave_policy.php -->
<button class="btn btn-secondary btn-sm policy-tab" id="tab-ph" onclick="renderPublicHolidays()">
    <i data-lucide="calendar-days" size="14"></i> Public Holidays
</button> 
</div>

<!-- Ledger Container -->
<div id="leave-policy-ledger-stack" class="etype-ledger">
    <!-- List items injected via core.js -->
</div>