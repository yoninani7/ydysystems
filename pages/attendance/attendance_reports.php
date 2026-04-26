<div class="page-header">
    <div>
        <h1 class="page-title">Attendance Reports</h1>
        <p class="page-sub">Analyze attendance patterns, tardiness, and workforce presence</p>
    </div>
    <button class="btn btn-secondary" onclick="showNotification('Export','Preparing CSV export...','info')">
        <i data-lucide="download" size="16"></i> Export CSV
    </button>
</div>

<!-- Filter Bar -->
<div class="card mb-4" style="border-radius: 12px; overflow: visible;">
    <div class="card-body" style="padding: 20px 24px;">
        <div class="flex-row" style="gap: 16px; align-items: flex-end;">
            
            <div style="flex: 0.8;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">MONTH</label>
                <div class="as-combo-container">
                    <input type="text" id="rep-month" class="sel" style="width: 100%;" value="January" onfocus="showAsDrop('as-drop-rep-month')" readonly>
                    <div class="as-combo-results" id="as-drop-rep-month">
                        <div class="as-res-item" onclick="selectAsItem('rep-month','as-drop-rep-month','January')">January</div>
                        <div class="as-res-item" onclick="selectAsItem('rep-month','as-drop-rep-month','February')">February</div>
                    </div>
                </div>
            </div>

            <div style="flex: 0.8;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">YEAR</label>
                <div class="as-combo-container">
                    <input type="text" id="rep-year" class="sel" style="width: 100%;" value="2026" onfocus="showAsDrop('as-drop-rep-year')" readonly>
                    <div class="as-combo-results" id="as-drop-rep-year">
                        <div class="as-res-item" onclick="selectAsItem('rep-year','as-drop-rep-year','2026')">2026</div>
                        <div class="as-res-item" onclick="selectAsItem('rep-year','as-drop-rep-year','2025')">2025</div>
                    </div>
                </div>
            </div>

            <div style="flex: 1.2;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">DEPARTMENT</label>
                <div class="as-combo-container">
                    <input type="text" id="rep-dept" class="sel" style="width: 100%;" value="All Departments" onfocus="showAsDrop('as-drop-rep-dept')" readonly>
                    <div class="as-combo-results" id="as-drop-rep-dept">
                        <div class="as-res-item" onclick="selectAsItem('rep-dept','as-drop-rep-dept','All Departments')">All Departments</div>
                        <div class="as-res-item" onclick="selectAsItem('rep-dept','as-drop-rep-dept','Engineering')">Engineering</div>
                    </div>
                </div>
            </div>

            <div style="flex: 1.2;">
                <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">REPORT TYPE</label>
                <div class="as-combo-container">
                    <!-- CRITICAL: The ID here is 'rep-type-val' -->
                    <input type="text" id="rep-type-val" class="sel" style="width: 100%;" value="Summary" onfocus="showAsDrop('as-drop-rep-type')" readonly>
                    <div class="as-combo-results" id="as-drop-rep-type">
                        <div class="as-res-item" onclick="selectAsItem('rep-type-val','as-drop-rep-type','Summary')">Summary</div>
                        <div class="as-res-item" onclick="selectAsItem('rep-type-val','as-drop-rep-type','Late Comers')">Late Comers</div>
                        <div class="as-res-item" onclick="selectAsItem('rep-type-val','as-drop-rep-type','Absentee List')">Absentee List</div>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary" style="height: 38px; background: #15b201; padding: 0 24px;" onclick="runInstantReport()">
                <i data-lucide="bar-chart-2" size="16" style="margin-right:8px"></i> Generate Report
            </button>
        </div>
    </div>
</div>

<div id="report-results-target"></div>