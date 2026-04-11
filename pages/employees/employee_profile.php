<!-- EMPLOYEE DIRECTORY -->
      <div class="page" id="p-employee-directory">
        <div class="page-header">
          <div>
            <div class="page-title">Employee Profile</div>
            <div class="page-sub">1,248 employees across all branches</div>
          </div>
          
          <!-- Corrected Positioning Container -->
          <div class="flex-row" style="gap: 12px;">
            
            <!-- Department Filter -->
            <div class="as-combo-container" style="width: 220px;">
              <input type="text" id="filter-dept-val" class="sel" style="width: 100%;" 
                    placeholder="Filter by Department" 
                    onfocus="showAsDrop('as-drop-dept-filter')" readonly>
              
              <div class="as-combo-results" id="as-drop-dept-filter"> 
                <div class="as-res-item" onclick="selectAsItem('filter-dept-val','as-drop-dept-filter','Engineering')">Engineering</div>
                <div class="as-res-item" onclick="selectAsItem('filter-dept-val','as-drop-dept-filter','Sales')">Sales</div>
                <div class="as-res-item" onclick="selectAsItem('filter-dept-val','as-drop-dept-filter','HR')">HR</div>
                <div class="as-res-item" onclick="selectAsItem('filter-dept-val','as-drop-dept-filter','Finance')">Finance</div>
              </div> 
            </div>

            <!-- Action Button -->
            <button class="btn btn-primary" onclick="goPage('add-employee', this)">
              <i data-lucide="user-plus" size="13"></i> Add Employee
            </button>
          </div>
        </div>
        
        <div id="tbl-employees"></div>
      </div>