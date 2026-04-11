<!-- AUDIT LOGS -->
<div class="page" id="p-audit-logs">
  <div class="page-header">
    <div><div class="page-title">Audit Logs</div><div class="page-sub">Complete system activity trail</div></div>
    <div class="flex-row"><div class="as-combo-container" style="width: 160px;">
  <!-- Visual Input representing the Action filter -->
  <input type="text" id="filter-audit-action" class="sel" placeholder="All Actions" 
         onfocus="showAsDrop('as-drop-audit-action')" readonly>
  
  <!-- Custom Dropdown Results -->
  <div class="as-combo-results" id="as-drop-audit-action">
    <div class="as-res-item" onclick="selectAsItem('filter-audit-action','as-drop-audit-action','All Actions')">All Actions</div>
    <div class="as-res-item" onclick="selectAsItem('filter-audit-action','as-drop-audit-action','Create')">Create</div>
    <div class="as-res-item" onclick="selectAsItem('filter-audit-action','as-drop-audit-action','Update')">Update</div>
    <div class="as-res-item" onclick="selectAsItem('filter-audit-action','as-drop-audit-action','Delete')">Delete</div>
    <div class="as-res-item" onclick="selectAsItem('filter-audit-action','as-drop-audit-action','Login')">Login</div>
  </div>
    </div>
    </div>
  </div>
  <div id="tbl-audit"></div>
</div>