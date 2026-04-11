<!-- JOB VACANCIES -->
<div class="page" id="p-job-vacancies">
  <div class="page-header">
    <div><div class="page-title">Job Vacancies</div><div class="page-sub">Positions across all departments</div></div>
    <div class="flex-row"><div class="as-combo-container" style="width: 160px;">
  <input type="text" id="filter-vac-status" class="sel" placeholder="Filter by Status" 
         onfocus="showAsDrop('as-drop-vac-status')" readonly>
  <div class="as-combo-results" id="as-drop-vac-status">
    <div class="as-res-item" onclick="selectAsItem('filter-vac-status','as-drop-vac-status','All Status')">All </div>
    <div class="as-res-item" onclick="selectAsItem('filter-vac-status','as-drop-vac-status','Open')">Open</div>
    <div class="as-res-item" onclick="selectAsItem('filter-vac-status','as-drop-vac-status','On Hold')">On Hold</div>
    <div class="as-res-item" onclick="selectAsItem('filter-vac-status','as-drop-vac-status','Filled')">Filled</div>
  </div>
  </div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Post Vacancy</button></div>
    </div>
    <div id="tbl-vacancies"></div>
</div>