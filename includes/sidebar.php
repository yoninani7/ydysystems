<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i data-lucide="layers" size="16"></i></div>
    <span class="sidebar-brand-text">YDY Systems</span>
  </div>
  <div class="sidebar-scroll">
    <div class="nav-group">
      <div class="dash-link active" onclick="goPage('dashboard',this)" title="Dashboard">
        <div class="nav-trigger-left">
          <div class="nav-icon"><i data-lucide="layout-dashboard" size="15"></i></div>
          <span>Dashboard</span>
        </div>
      </div>
    </div>

    <div class="nav-label">Organization</div>
    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-org')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="building-2" size="15"></i></div><span>Company & Structure</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-org">
        <div class="sub-link" onclick="goPage('company-profile',this)"><span class="sub-dot"></span><span>Company Profile</span></div>
        <div class="sub-link" onclick="goPage('org-chart',this)"><span class="sub-dot"></span><span>Organization Chart</span></div>
        <div class="sub-link" onclick="goPage('departments',this)"><span class="sub-dot"></span><span>Departments</span></div>
        <div class="sub-link" onclick="goPage('job-positions',this)"><span class="sub-dot"></span><span>Job Positions</span></div>
        <div class="sub-link" onclick="goPage('branch-offices',this)"><span class="sub-dot"></span><span>Branch Offices</span></div>
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-emp')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="users" size="15"></i></div><span>Employees</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-emp">
        <div class="sub-link" onclick="goPage('employee-directory',this)"><span class="sub-dot"></span><span>Employee Profile</span></div>
        <div class="sub-link" onclick="goPage('employment-types',this)"><span class="sub-dot"></span><span>Employment Types</span></div>
        <div class="sub-link" onclick="goPage('probation-tracker',this)"><span class="sub-dot"></span><span>Probation Tracker</span></div>
        <div class="sub-link" onclick="goPage('contract-renewals',this)"><span class="sub-dot"></span><span>Contract Renewals</span></div>
        <div class="sub-link" onclick="goPage('former-employees',this)"><span class="sub-dot"></span><span>Former employees</span></div>
        <div class="sub-link" onclick="goPage('retirement-planner',this)"><span class="sub-dot"></span><span>Retirement Planner</span></div>
        <div class="sub-link" onclick="goPage('document-vault',this)"><span class="sub-dot"></span><span>Attachment Vault</span></div>
        <div class="sub-link" onclick="goPage('asset-tracking',this)"><span class="sub-dot"></span><span>Asset Tracking</span></div>
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-move')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="arrow-right-left" size="15"></i></div><span>Employee Movement</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-move">
        <div class="sub-link" onclick="goPage('Promote/Demote',this)"><span class="sub-dot"></span><span>Promote/Demote</span></div>
        <div class="sub-link" onclick="goPage('transfers',this)"><span class="sub-dot"></span><span>Department Transfers</span></div>
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-rec')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="user-plus" size="15"></i></div><span>Talent Acquisition</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-rec">
        <div class="sub-link" onclick="goPage('job-vacancies',this)"><span class="sub-dot"></span><span>Add Job Vacancies</span></div>
        <div class="sub-link" onclick="goPage('candidates',this)"><span class="sub-dot"></span><span>Job Applicant's List</span></div>
        <div class="sub-link" onclick="goPage('interview-tracker',this)"><span class="sub-dot"></span><span>Interview Tracker</span></div>
        <div class="sub-link" onclick="goPage('internship',this)"><span class="sub-dot"></span><span>Internship Management</span></div>
      </div>
    </div>

    <div class="nav-label">Operations</div>
    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-att')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="clock" size="15"></i></div><span>Attendance</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-att">
        <!-- Sub-modules as seen in the image -->
        <div class="sub-link" onclick="goPage('shift-management',this)"><span class="sub-dot"></span><span>Shift Management</span></div>
        <div class="sub-link" onclick="goPage('attendance',this)"><span class="sub-dot"></span><span>Record Attendance</span></div>
        <div class="sub-link" onclick="goPage('daily-attendance',this)"><span class="sub-dot"></span><span>Daily Attendance</span></div> 
        <div class="sub-link" onclick="goPage('attendance-reports',this)"><span class="sub-dot"></span><span>Attendance Reports</span></div>
        <div class="sub-link" onclick="goPage('import-attendance',this)"><span class="sub-dot"></span><span>Import from Excel</span></div>
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-leave')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="calendar-days" size="15"></i></div><span>Leave Management</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-leave">
        <div class="sub-link" onclick="goPage('leave-policy',this)"><span class="sub-dot"></span><span>Leave Policy</span></div>  
        <div class="sub-link" onclick="goPage('leave-requests',this)"><span class="sub-dot"></span><span>Leave Requests</span></div>
        <div class="sub-link" onclick="goPage('leave-entitlement',this)"><span class="sub-dot"></span><span>Leave Entitlement</span></div>
        <div class="sub-link" onclick="goPage('leave-calendar',this)"><span class="sub-dot"></span><span>Leave Calendar</span></div>
        <div class="sub-link" onclick="goPage('leave-analytics',this)"><span class="sub-dot"></span><span>Leave Analytics</span></div>

      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-ben')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="heart-pulse" size="15"></i></div><span>Benefits</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-ben">
        <div class="sub-link" onclick="goPage('medical-claims',this)"><span class="sub-dot"></span><span>Medical Claims</span></div>
        <div class="sub-link" onclick="goPage('overtime-requests',this)"><span class="sub-dot"></span><span>Overtime Requests</span></div>
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-comp')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="shield-alert" size="15"></i></div><span>Compliance & Exit</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-comp">
        <div class="sub-link" onclick="goPage('disciplinary-actions',this)"><span class="sub-dot"></span><span>Disciplinary Actions</span></div>
        <div class="sub-link" onclick="goPage('resignations',this)"><span class="sub-dot"></span><span>Resignations</span></div>
        <div class="sub-link" onclick="goPage('termination',this)"><span class="sub-dot"></span><span>Separation & Exit</span></div>
        <div class="sub-link" onclick="goPage('exit-clearance',this)"><span class="sub-dot"></span><span>Exit Clearance</span></div>
      </div>
    </div>

    <div class="nav-label">Growth & Data</div>
    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-train')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="graduation-cap" size="15"></i></div><span>Training & Dev</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-train">
        <div class="sub-link" onclick="goPage('training-needs',this)"><span class="sub-dot"></span><span>Training Needs Analysis</span></div> 
        <div class="sub-link" onclick="goPage('training-schedule',this)"><span class="sub-dot"></span><span>Training Schedule</span></div> 
      </div>
    </div>

 <div class="nav-group">
  <div class="nav-trigger" onclick="toggleNav(this,'m-perf')">
    <div class="nav-trigger-left">
      <div class="nav-icon"><i data-lucide="trending-up" size="15"></i></div>
      <span>Performance</span>
    </div>
    <i data-lucide="chevron-right" size="13" class="chevron"></i>
  </div>
  <div class="submenu" id="m-perf">
    <div class="sub-link" onclick="goPage('performance-reviews',this)">
      <span class="sub-dot"></span><span>Performance Reviews</span>
    </div>
    <div class="sub-link" onclick="goPage('360-feedback',this)">
      <span class="sub-dot"></span><span>360° Feedback</span>
    </div>
    <div class="sub-link" onclick="goPage('goals-development',this)">
      <span class="sub-dot"></span><span>Goals & Development</span>
    </div>
  </div>
</div>

    <div class="nav-label">Analytics & System</div>
    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-rep')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="bar-chart-3" size="15"></i></div><span>Reports & Analytics</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-rep">
        <div class="sub-link" onclick="goPage('hr-analytics',this)"><span class="sub-dot"></span><span>HR Analytics</span></div>
        <div class="sub-link" onclick="goPage('custom-reports',this)"><span class="sub-dot"></span><span>Custom Reports</span></div>
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger" onclick="toggleNav(this,'m-sys')">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="settings-2" size="15"></i></div><span>System Admin</span></div>
        <i data-lucide="chevron-right" size="13" class="chevron"></i>
      </div>
      <div class="submenu" id="m-sys">
        <div class="sub-link" onclick="goPage('user-management',this)"><span class="sub-dot"></span><span>User Management</span></div>
        <div class="sub-link" onclick="goPage('roles-permissions',this)"><span class="sub-dot"></span><span>Roles & Permissions</span></div>
        <div class="sub-link" onclick="goPage('audit-logs',this)"><span class="sub-dot"></span><span>Audit Logs</span></div>
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-trigger">
        <div class="nav-trigger-left"><div class="nav-icon"><i data-lucide="settings" size="15"></i></div><span>Settings</span></div> 
      </div> 
    </div>
  </div>
</aside>