<div class="page-header">
    <div>
        <h1 class="page-title">Leave Analytics</h1>
        <p class="page-sub">Utilization patterns, trends, and workforce leave intelligence</p>
    </div>
    <button class="btn btn-secondary" style="border-radius: 10px; padding: 10px 18px;">
        <i data-lucide="download" size="18" style="margin-right:8px"></i> Export CSV
    </button>
</div>

<!-- Top Filters -->
<div class="flex-row mb-4" style="gap: 12px; align-items: center;">
    <span style="font-size: 13px; font-weight: 700; color: #1e293b;">Fiscal Year:</span>
    <input type="text" class="sel" value="2026" style="width: 100px; text-align: center; border-radius: 10px;">
    <button class="btn btn-primary btn-sm" style="background: #15b201; border-radius: 8px; height: 36px; padding: 0 16px;" onclick="initLeaveAnalytics()">
        <i data-lucide="refresh-cw" size="14" style="margin-right:6px"></i> Refresh
    </button>
</div>

<!-- Quick Stats -->
<div class="stats-row stats-4 mb-4">
    <div class="stat-card">
        <div class="stat-label">TOTAL LEAVE DAYS TAKEN</div>
        <div class="stat-value" style="color: #15b201;">248</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">AVG DAYS PER EMPLOYEE</div>
        <div class="stat-value">12.4</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">MOST COMMON LEAVE TYPE</div>
        <div class="stat-value" style="font-size: 1.1rem; margin-top: 5px;">Annual Leave</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">PEAK LEAVE MONTH</div>
        <div class="stat-value" style="font-size: 1.1rem; margin-top: 5px;">March</div>
    </div>
</div>

<!-- Main Charts Row -->
<div class="bento-grid mb-4">
    <!-- Bar Chart -->
    <div class="card" style="grid-column: span 8;">
        <div class="card-header" style="background: #fcfdfe; border-bottom: 1px solid #f1f5f9;">
            <span class="card-title" style="display:flex; align-items:center; gap:8px; font-size:11px; color:#64748b;">
                <i data-lucide="bar-chart-big" size="14"></i> LEAVE DAYS BY DEPARTMENT
            </span>
        </div>
        <div class="card-body" style="height: 340px; position: relative; padding: 24px;">
            <canvas id="chart-leave-dept"></canvas>
        </div>
    </div>
    <!-- Doughnut Chart -->
    <div class="card" style="grid-column: span 4;">
        <div class="card-header" style="background: #fcfdfe; border-bottom: 1px solid #f1f5f9;">
            <span class="card-title" style="display:flex; align-items:center; gap:8px; font-size:11px; color:#64748b;">
                <i data-lucide="pie-chart" size="14"></i> LEAVE TYPE DISTRIBUTION
            </span>
        </div>
        <div class="card-body" style="height: 340px; position: relative; padding: 24px;">
            <canvas id="chart-leave-type"></canvas>
        </div>
    </div>
</div>

<!-- Trends & Leaderboard Row -->
<div class="bento-grid">
    <!-- Line Chart -->
    <div class="card" style="grid-column: span 8;">
        <div class="card-header" style="background: #fcfdfe; border-bottom: 1px solid #f1f5f9;">
            <span class="card-title" style="display:flex; align-items:center; gap:8px; font-size:11px; color:#64748b;">
                <i data-lucide="trending-up" size="14"></i> MONTHLY LEAVE TREND
            </span>
        </div>
        <div class="card-body" style="height: 300px; position: relative; padding: 24px;">
            <canvas id="chart-leave-trend"></canvas>
        </div>
    </div> 
    <!-- Leaderboard Card -->
<div class="card" style="grid-column: span 4;">
    <div class="card-header" style="background: #fcfdfe; border-bottom: 1px solid #f1f5f9; padding: 16px 20px;">
        <span class="card-title" style="display:flex; align-items:center; gap:8px; font-size:11px; color:#64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
            <i data-lucide="list-ordered" size="14"></i> TOP LEAVE TAKERS
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div id="leaderboard-area" style="max-height: 310px; overflow-y: auto;">
            <!-- Injected via core.js -->
        </div>
    </div>
</div>
</div>

<script> 
    setTimeout(() => {
        if (typeof initLeaveAnalytics === 'function') {
            initLeaveAnalytics();
        }
    }, 50);
</script>
