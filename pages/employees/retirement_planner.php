<!-- RETIREMENT PLANNER -->
<div class="page" id="p-retirement-planner">
  <div class="page-header">
    <div>
      <div class="page-title">Retirement & Succession Planner</div>
      <div class="page-sub">Monitoring personnel reaching the statutory retirement age (60 years)</div>
    </div>
    <div class="flex-row">
      <button class="btn btn-secondary"><i data-lucide="download" size="13"></i> Export Report</button>
      <button class="btn btn-primary"><i data-lucide="bell" size="13"></i> Notify Managers</button>
    </div>
  </div>

  <!-- Retirement Quick Stats -->
  <div class="stats-row stats-3 mb-4">
    <div class="stat-card" style="border-left: 4px solid var(--warning);">
      <div class="stat-label">Upcoming (90 Days)</div>
      <div class="stat-value" id="count-upcoming-ret">0</div>
      <div class="stat-change" style="color:var(--muted);">Critical for succession</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--primary);">
      <div class="stat-label">Retired this Fiscal Year</div>
      <div class="stat-value">24</div>
      <div class="stat-change stat-up">Processed via Pension</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--info);">
      <div class="stat-label">Avg. Service period</div>
      <div class="stat-value">28.5 Yrs</div>
      <div class="stat-change">Lifetime Service</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
        <div class="card-title">Retirement Registry</div>
        <div class="flex-row">
            <span class="badge badge-warning" style="font-size: 10px;">Upcoming</span>
            <span class="badge badge-neutral" style="font-size: 10px;">Already Retired</span>
        </div>
    </div>
    <div id="tbl-retirement"></div>
  </div>
</div>