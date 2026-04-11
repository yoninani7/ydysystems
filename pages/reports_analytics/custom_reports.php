<!-- CUSTOM REPORTS -->
<div class="page" id="p-custom-reports">
  <div class="page-header"><div><div class="page-title">Custom Reports</div><div class="page-sub">Build your own HR reports</div></div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>New Report</button></div>
  <div class="card"><div class="card-body">
    <div class="form-grid fg-3">
      <div class="form-group"><label>Report Name</label><input placeholder="e.g. Q1 Headcount by Grade"></div>
      <div class="form-group"><label>Module</label><div class="as-combo-container">
  <!-- Visual Input representing the Module selection -->
  <input type="text" id="report-module-val" class="form-ctrl" placeholder="Select Module..." 
         onfocus="showAsDrop('as-drop-report-module')" readonly>
  
  <!-- Custom Dropdown Results -->
  <div class="as-combo-results" id="as-drop-report-module">
    <div class="as-res-item" onclick="selectAsItem('report-module-val','as-drop-report-module','Employees')">Employees</div>
    <div class="as-res-item" onclick="selectAsItem('report-module-val','as-drop-report-module','Payroll')">Payroll</div>
    <div class="as-res-item" onclick="selectAsItem('report-module-val','as-drop-report-module','Attendance')">Attendance</div>
    <div class="as-res-item" onclick="selectAsItem('report-module-val','as-drop-report-module','Leave')">Leave</div>
    <div class="as-res-item" onclick="selectAsItem('report-module-val','as-drop-report-module','Performance')">Performance</div>
  </div>
</div></div>
      <div class="form-group"><label>Date Range</label><input type="date"></div>
    </div>
    <div style="margin-top:16px;"><button class="btn btn-primary"><i data-lucide="play" size="13"></i>Generate Preview</button></div>
  </div></div>
</div>