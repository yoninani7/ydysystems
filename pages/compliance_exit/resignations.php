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
        <button class="btn btn-sm res-tab active" id="tab-all" onclick="renderResignations('All')" style="border-radius: 8px; padding: 6px 20px;">All</button>
        <button class="btn btn-sm res-tab" onclick="renderResignations('Pending')" style="background:transparent; color:#64748b; border:none; padding: 6px 20px;">Pending</button>
        <button class="btn btn-sm res-tab" onclick="renderResignations('Under Review')" style="background:transparent; color:#64748b; border:none; padding: 6px 20px;">Under Review</button>
        <button class="btn btn-sm res-tab" onclick="renderResignations('Resolved')" style="background:transparent; color:#64748b; border:none; padding: 6px 20px;">Resolved</button>
    </div>
    <div class="search-inner" style="width: 300px; background: #fff;">
        <i data-lucide="search" size="16"></i>
        <input type="text" placeholder="Search employee, type..." oninput="filterResignationTable(this.value)">
    </div>
</div>

<div id="resignation-table-target">
    <!-- Rendered via core.js -->
</div>

<script>
    setTimeout(() => {
        if(typeof renderResignations === 'function') renderResignations('All');
    }, 50);
</script>

<style>
    .res-tab.active { background: #15b201 !important; color: #fff !important; }
</style>