
<!-- DASHBOARD -->
<div class="page active" id="p-dashboard">
  <header class="db-hero-banner" style="padding:16px 28px;min-height:auto;position:relative;">
    <div class="db-hero-content">
      <h1 class="db-hero-title" style="font-size:1.5rem;">Dashboard Intelligence</h1>
      <p class="db-hero-sub">Where the organization data is centralized</p>
    </div>
    <img src="assets/img/bgt.png" class="db-hero-center-img" alt="Banner Illustration">
    <div class="qa-stack-v2">
      <div class="qa-mini-header">
        <span class="qa-mini-line"></span>
        <span class="qa-mini-label">Quick Actions</span>
        <span class="qa-mini-line"></span>
      </div>
      <div class="qa-button-row">
        <button class="btn-glass-pro-slim" onclick="goPage('employee-directory',this)"><i data-lucide="user-plus" size="14"></i><span>New Hire</span></button>
        <button class="btn-glass-pro-slim" onclick="goPage('leave-requests',this)"><i data-lucide="calendar" size="14"></i><span>Leaves</span></button>
        <button class="btn-glass-pro-slim" onclick="goPage('hr-analytics',this)"><i data-lucide="bar-chart-3" size="14"></i><span>Analytics</span></button>
      </div>
    </div>
  </header>
  <div class="stat-ribbon-art">
    <div class="art-card art-headcount"><span class="art-label">Employees</span><div class="art-main-row"><span class="art-value">1,248</span><div class="art-meta-group"><span class="art-indicator">Total</span><span class="art-subtext">Employee count</span></div></div></div>
    <div class="art-card art-units"><span class="art-label">Departments</span><div class="art-main-row"><span class="art-value">07</span><div class="art-meta-group"><span class="art-indicator">Active</span><span class="art-subtext">Internal Units</span></div></div></div>
    <div class="art-card art-leave"><span class="art-label">On Leave</span><div class="art-main-row"><span class="art-value">47</span><div class="art-meta-group"><span class="art-indicator">Staff</span><span class="art-subtext"> Outbound</span></div></div></div>
    <div class="art-card art-missing"><span class="art-label">Pending leave approvals</span><div class="art-main-row"><span class="art-value">48</span><div class="art-meta-group"><span class="art-indicator">Leave</span><span class="art-subtext">Requests</span></div></div></div>
    <div class="art-card art-expired"><span class="art-label">Expiring contracts</span><div class="art-main-row"><span class="art-value">18</span><div class="art-meta-group"><span class="art-indicator">Critical</span><span class="art-subtext">15 days or less </span></div></div></div>
  </div>
  <div class="bento-container">
    <div class="card" style="padding:24px;border-radius:20px;background:#fff;">
      <div class="card-header" style="border:none;padding:0 0 20px 0;">
        <div>
          <div class="card-title" style="font-size:1.15rem;color:#0f172a;font-weight:800;">Employee Distribution</div>
          <div class="page-sub">Departmental workload and personnel breakdown</div>
        </div>
      </div>
      <div style="height:240px;width:100%;"><canvas id="chart-headcount-dept"></canvas></div>
    </div>
    <div class="activity-side-wrap" style="max-height:445px;border-radius:20px;">
      <div class="pulse-header">
        <div class="pulse-label"><div class="live-indicator"></div>Recent activities</div>
        <button class="refresh-pill" onclick="triggerRefresh(this)">
          <i data-lucide="refresh-cw" size="12"></i><span id="refresh-text">Refresh</span>
        </button>
      </div>
      <div class="hr-pulse-feed">
        <div class="pulse-item"><span class="pulse-type type-asset">Inventory</span><div class="pulse-content">MacBook Pro 14" (AST-99) assigned to <b>Sarah Jenkins</b></div><span class="pulse-ts">Just now</span></div>
        <div class="pulse-item"><span class="pulse-type type-crit">Compliance</span><div class="pulse-content">NDA missing for <b>4 employees</b> in Engineering.</div><span class="pulse-ts">12m ago</span></div>
        <div class="pulse-item"><span class="pulse-type type-hire">Talent</span><div class="pulse-content">New application received for <b>Senior UI Designer</b>.</div><span class="pulse-ts">45m ago</span></div>
        <div class="pulse-item"><span class="pulse-type type-prob">Probation</span><div class="pulse-content"><b>John Doe</b> completed 90-day review period.</div><span class="pulse-ts">2h ago</span></div>
      </div>
    </div>
  </div>
</div>