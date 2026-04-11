
<!-- LEAVE REQUESTS -->
<div class="page" id="p-leave-requests">
  <div class="page-header">
    <div><div class="page-title">Leave Requests</div><div class="page-sub">Pending and approved leave applications</div></div>
    <div class="flex-row"><div class="as-combo-container" style="width: 160px;">
  <!-- Visual Input representing the filter selection -->
  <input type="text" id="filter-status-val" class="sel" placeholder="All Status" 
         onfocus="showAsDrop('as-drop-status-filter')" readonly>
  
  <!-- Custom Dropdown Results -->
  <div class="as-combo-results" id="as-drop-status-filter">
    <div class="as-res-item" onclick="selectAsItem('filter-status-val','as-drop-status-filter','All')">All</div>
    <div class="as-res-item" onclick="selectAsItem('filter-status-val','as-drop-status-filter','Pending')">Pending</div>
    <div class="as-res-item" onclick="selectAsItem('filter-status-val','as-drop-status-filter','Approved')">Approved</div>
    <div class="as-res-item" onclick="selectAsItem('filter-status-val','as-drop-status-filter','Rejected')">Rejected</div>
  </div>
</div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>New Request</button></div>
  </div>
  <div id="tbl-leave-requests"></div>
</div>