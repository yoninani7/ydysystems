<!-- ATTENDANCE RECORDING MATRIX -->
<div class="page" id="p-attendance">
    <div class="page-header">
        <div>
        <div class="page-title">Attendance Registry</div>
        <p class="page-sub">Centralized monthly ledger for personnel presence.</p>
        </div>
    </div>

    <!-- ATTENDANCE CONTROL BAR -->
    <div class="card" style="padding: 20px; background: #fff; display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
    
    <div class="form-group" style="width: 160px; margin:0">
        <label>Target Month</label>
        <div class="as-combo-container" style="width: 160px;">
    <!-- Visual Input -->
    <input type="text" id="att-m-display" class="form-ctrl" placeholder="Select Month..." 
            onfocus="showAsDrop('as-drop-month')" readonly>
    
    <!-- Hidden Input to store the 0-11 value for your JS buildMatrix function -->
    <input type="hidden" id="att-m-select" value="">

    <div class="as-combo-results" id="as-drop-month">
        <div class="as-res-item" onclick="selectMonth('January', '0')">January</div>
        <div class="as-res-item" onclick="selectMonth('February', '1')">February</div>
        <div class="as-res-item" onclick="selectMonth('March', '2')">March</div>
        <div class="as-res-item" onclick="selectMonth('April', '3')">April</div>
        <div class="as-res-item" onclick="selectMonth('May', '4')">May</div>
        <div class="as-res-item" onclick="selectMonth('June', '5')">June</div>
        <div class="as-res-item" onclick="selectMonth('July', '6')">July</div>
        <div class="as-res-item" onclick="selectMonth('August', '7')">August</div>
        <div class="as-res-item" onclick="selectMonth('September', '8')">September</div>
        <div class="as-res-item" onclick="selectMonth('October', '9')">October</div>
        <div class="as-res-item" onclick="selectMonth('November', '10')">November</div>
        <div class="as-res-item" onclick="selectMonth('December', '11')">December</div>
    </div>
    </div>

    </div>

    <div class="form-group" style="width: 110px; margin:0">
        <label>Fiscal Year</label>
    <div class="as-combo-container" style="width: 110px;">
    <!-- Visual Input (Shows 2026 by default as per your 'selected' attribute) -->
    <input type="text" id="att-y-display" class="form-ctrl" placeholder="Year" value="2026"
            onfocus="showAsDrop('as-drop-year')" readonly>
    
    <!-- Hidden Input to store the actual value for your JS functions -->
    <input type="hidden" id="att-y-select" value="2026">

    <div class="as-combo-results" id="as-drop-year">
        <div class="as-res-item" onclick="selectYear('2025')">2025</div>
        <div class="as-res-item" onclick="selectYear('2026')">2026</div>
    </div>
    </div>
    </div>
    
    <div class="form-group" style="width: 180px; margin:0">
        <label>Department</label>
    <div class="as-combo-container" style="width: 180px;">
    <!-- Visual Input -->
    <input type="text" id="att-dept-display" class="form-ctrl" placeholder="Department" value="All Departments"
            onfocus="showAsDrop('as-drop-att-dept')" readonly>
    
    <!-- Hidden Input to maintain your existing JS logic (ID: att-dept-select) -->
    <input type="hidden" id="att-dept-select" value="All">

    <div class="as-combo-results" id="as-drop-att-dept">
        <div class="as-res-item" onclick="selectAttDept('All Departments', 'All')">All Departments</div>
        <div class="as-res-item" onclick="selectAttDept('Engineering', 'Engineering')">Engineering</div>
        <div class="as-res-item" onclick="selectAttDept('Sales', 'Sales')">Sales</div>
        <div class="as-res-item" onclick="selectAttDept('HR', 'HR')">HR</div>
        <div class="as-res-item" onclick="selectAttDept('Finance', 'Finance')">Finance</div>
        <div class="as-res-item" onclick="selectAttDept('Marketing', 'Marketing')">Marketing</div>
        <div class="as-res-item" onclick="selectAttDept('Operations', 'Operations')">Operations</div>
    </div>
    </div>
    </div>

    <!-- THE DROPDOWN -->
    <div class="form-group" style="width: 240px; margin:0; position:relative;">
        <label>Employee Name</label>
        <div class="as-combo-container">
        <input type="text" id="as-input-att-name" class="form-ctrl" 
                placeholder="Type employee name ..." 
                onfocus="showAsDrop('as-drop-att-name')" 
                oninput="filterAsDrop('as-input-att-name','as-drop-att-name')">
        <div class="as-combo-results" id="as-drop-att-name"></div>
        </div>
    </div>

    <button class="btn btn-primary" style="height: 40px; padding: 0 24px" onclick="buildMatrix()">
        <i data-lucide="fingerprint" size="14"></i> Generate Attendance
    </button>
    </div>

    <!-- META BAR (LEGEND + COMMIT) - NEW POSITION -->
    <div id="att-meta-header" class="att-meta-bar">
        <div class="flex-row" style="gap: 12px;">
        <span class="att-leg-pill pill-p">P: Present</span>
        <span class="att-leg-pill pill-h">H: Sat-Half</span>
        <span class="att-leg-pill pill-a">A: Absent</span>
        <span class="att-leg-pill pill-o">O: Sunday-Off</span>
        </div>
        <div class="flex-row" style="gap: 12px;"> 
        <button class="btn btn-success " onclick="saveAttendance()"><i data-lucide="save" size="14"></i> Commit to Database</button>
        </div>
    </div>

    <!-- THE LEDGER CONTAINER -->
    <div id="ledger-container" class="att-ledger-card" style="display:none;">
        <div class="att-grid-viewport">
        <table class="tbl-ledger">
            <thead id="ledger-head"></thead>
            <tbody id="ledger-body"></tbody>
        </table>
        </div>
    </div>
    <!--  -Attendance EMPTY STATE -->
    <div id="ledger-empty" class="att-empty-state-container">
        <div class="att-empty-glass-card">
            <div class="att-empty-icon-stack">
                <div class="icon-ring pulse"></div>
                <div class="icon-ring delay-1"></div>
                <div class="icon-main">
                    <i data-lucide="calendar-range" size="32"></i>
                </div>
            </div>
            
            <h3 class="att-empty-title">Attendance Intelligence</h3>
            <p class="att-empty-text">
                The personnel registry is synchronized and ready. Select your reporting parameters above to generate the monthly ledger.
            </p>

            <div class="att-empty-steps">
                <div class="att-step">
                    <span class="step-num">01</span>
                    <span class="step-label">Select Month</span>
                </div>
                <div class="att-step-line"></div>
                <div class="att-step">
                    <span class="step-num">02</span>
                    <span class="step-label">Set Fiscal Year</span>
                </div>
                <div class="att-step-line"></div>
                <div class="att-step">
                    <span class="step-num">03</span>
                    <span class="step-label">Generate</span>
                </div>
            </div>
        </div>
    </div>
    </div>