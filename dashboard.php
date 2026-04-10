<?php
include 'includes/header.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>YDY HRM Enterprise</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://unpkg.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">  

</head>
<body>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeMobileSidebar()"></div> 
<?php
include 'includes/sidebar.php'; 
?>

<main class="main" id="main-content">

<?php
include 'includes/topbar.php'; 
?>
  <div class="content-area" id="content-area">

<!-- DASHBOARD -->
<div class="page active" id="p-dashboard">
  <header class="db-hero-banner" style="padding:16px 28px;min-height:auto;position:relative;">
    <div class="db-hero-content">
      <h1 class="db-hero-title" style="font-size:1.5rem;">Dashboard Intelligence</h1>
      <p class="db-hero-sub">Organization performance is at peak efficiency.</p>
    </div>
    <img src="assets/img/bgt.png" class="db-hero-center-img" alt="Banner Illustration">
    <div class="qa-stack-v2">
      <div class="qa-mini-header">
        <span class="qa-mini-line"></span>
        <span class="qa-mini-label">Quick Actions</span>
        <span class="qa-mini-line"></span>
      </div>
      <div class="qa-button-row">
        <button class="btn-glass-pro-slim" onclick="goPage('employee-directory',this)"><i data-lucide="user-plus" size="14"></i><span>New Hire</span></button>
        <button class="btn-glass-pro-slim" onclick="goPage('leave-requests',this)"><i data-lucide="calendar" size="14"></i><span>Leaves</span></button>
        <button class="btn-glass-pro-slim" onclick="goPage('hr-analytics',this)"><i data-lucide="bar-chart-3" size="14"></i><span>Analytics</span></button>
      </div>
    </div>
  </header>
  <div class="stat-ribbon-art">
    <div class="art-card art-headcount"><span class="art-label">Headcount</span><div class="art-main-row"><span class="art-value">1,248</span><div class="art-meta-group"><span class="art-indicator">Total</span><span class="art-subtext">Monthly Growth</span></div></div></div>
    <div class="art-card art-units"><span class="art-label">Departments</span><div class="art-main-row"><span class="art-value">07</span><div class="art-meta-group"><span class="art-indicator">Active</span><span class="art-subtext">Internal Units</span></div></div></div>
    <div class="art-card art-leave"><span class="art-label">On Leave</span><div class="art-main-row"><span class="art-value">47</span><div class="art-meta-group"><span class="art-indicator">Staff</span><span class="art-subtext"> Outbound</span></div></div></div>
    <div class="art-card art-missing"><span class="art-label">Pending leave approvals</span><div class="art-main-row"><span class="art-value">48</span><div class="art-meta-group"><span class="art-indicator">Leave</span><span class="art-subtext">Requests</span></div></div></div>
    <div class="art-card art-expired"><span class="art-label">Expiring contracts</span><div class="art-main-row"><span class="art-value">18</span><div class="art-meta-group"><span class="art-indicator">Critical</span><span class="art-subtext">15 days or less </span></div></div></div>
  </div>
  <div class="bento-container">
    <div class="card" style="padding:24px;border-radius:20px;background:#fff;">
      <div class="card-header" style="border:none;padding:0 0 20px 0;">
        <div>
          <div class="card-title" style="font-size:1.15rem;color:#0f172a;font-weight:800;">Headcount Distribution</div>
          <div class="page-sub">Departmental workload and personnel breakdown</div>
        </div>
      </div>
      <div style="height:240px;width:100%;"><canvas id="chart-headcount-dept"></canvas></div>
    </div>
    <div class="activity-side-wrap" style="max-height:445px;border-radius:20px;">
      <div class="pulse-header">
        <div class="pulse-label"><div class="live-indicator"></div>Recent activities</div>
        <button class="refresh-pill" onclick="triggerRefresh(this)">
          <i data-lucide="refresh-cw" size="12"></i><span id="refresh-text">Refresh</span>
        </button>
      </div>
      <div class="hr-pulse-feed">
        <div class="pulse-item"><span class="pulse-type type-asset">Inventory</span><div class="pulse-content">MacBook Pro 14" (AST-99) assigned to <b>Sarah Jenkins</b></div><span class="pulse-ts">Just now</span></div>
        <div class="pulse-item"><span class="pulse-type type-crit">Compliance</span><div class="pulse-content">NDA missing for <b>4 employees</b> in Engineering.</div><span class="pulse-ts">12m ago</span></div>
        <div class="pulse-item"><span class="pulse-type type-hire">Talent</span><div class="pulse-content">New application received for <b>Senior UI Designer</b>.</div><span class="pulse-ts">45m ago</span></div>
        <div class="pulse-item"><span class="pulse-type type-prob">Probation</span><div class="pulse-content"><b>John Doe</b> completed 90-day review period.</div><span class="pulse-ts">2h ago</span></div>
      </div>
    </div>
  </div>
</div>
  <!-- Company and structure -->
  <?php    include 'pages/company_structure/companyprofile.php';    ?>
  <?php    include 'pages/company_structure/orgchart.php';    ?>
  <?php    include 'pages/company_structure/departments.php';    ?>
  <?php    include 'pages/company_structure/jobpositions.php';    ?>
  <?php    include 'pages/company_structure/branchoffices.php';    ?>

 
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
<!-- ADD NEW EMPLOYEE -->
<div class="page" id="p-add-employee">
  <div class="page-header" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:16px;">
      <button class="sidebar-toggle" onclick="goPage('employee-directory')"><i data-lucide="arrow-left" size="16"></i></button>
      <div>
        <div class="page-title" style="font-size:1.4rem;letter-spacing:-0.02em;">Onboard Personnel <span style="color:var(--muted);font-weight:400;">/ Master Data</span></div>
        <p class="page-sub">Establish formal organizational records and legal compliance identities.</p>
      </div>
    </div>
    <div class="flex-row">
       <button class="btn btn-secondary" onclick="goPage('employee-directory')">Discard Draft</button>
       <button class="btn btn-primary" id="btn-save-master" style="padding:10px 28px;opacity:0.4;cursor:not-allowed;" disabled onclick="saveNewEmployee()">
         <i data-lucide="shield-check" size="16"></i> Commit Record
       </button>
    </div>
  </div>
  <div class="onboard-grid">
    <aside class="onboard-sidebar">
      <div style="margin-bottom:32px;">
        <div style="font-size:.6rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;">Master Record Progress</div>
        <div style="height:4px;background:#e2e8f0;border-radius:10px;overflow:hidden;">
          <div id="master-progress-line" style="width:20%;height:100%;background:var(--primary);transition:width .4s ease;"></div>
        </div>
      </div>
      <nav id="onboard-nav-list">
        <div class="step-pro active" data-step="1" onclick="jumpToStep(1)"><div class="step-idx">1</div><span>Identity</span></div>
        <div class="step-pro" data-step="2" onclick="jumpToStep(2)"><div class="step-idx">2</div><span>Contact</span></div>
        <div class="step-pro" data-step="3" onclick="jumpToStep(3)"><div class="step-idx">3</div><span>Employment</span></div>
        <div class="step-pro" data-step="4" onclick="jumpToStep(4)"><div class="step-idx">4</div><span>Finance</span></div>
        <div class="step-pro" data-step="5" onclick="jumpToStep(5)"><div class="step-idx">5</div><span>Compliance</span></div>
        <div class="step-pro" data-step="6" onclick="jumpToStep(6)"><div class="step-idx">6</div><span>Review</span></div>
        <div style="margin-top:auto;padding:20px;background:#fff;border:1px solid var(--border);border-radius:16px;">
          <div style="font-size:.75rem;font-weight:800;color:var(--text);">Integrity Check</div>
          <div id="master-val-text" style="font-size:.65rem;color:var(--danger);margin-top:6px;font-weight:600;">* Missing Required Fields</div>
        </div>

      </nav>
    </aside>
    <main class="onboard-content">
      <section id="ob-step-1" class="form-section-content active">
        <div class="form-header-pro">
          <div class="input-group-label">Personal Identity</div>
          <p class="input-group-sub">Establish the primary legal identity and demographic profile for the master record.</p>
        </div>
        <div class="identity-master-wrapper">
         <div class="avatar-upload-zone">
  <!-- We put the label around everything so the whole area is clickable -->
  <label for="avatar-upload" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 12px;">
    
    <div class="avatar-frame" id="avatar-preview-box">
      <i data-lucide="user" size="40" style="color:#cbd5e1;" id="placeholder-icon"></i>
      <img id="avatar-img-output" src="" style="width:100%;height:100%;object-fit:cover;display:none;">
    </div>
    
    <div class="btn btn-secondary btn-xs" style="border-radius:20px; padding:5px 15px;">
      <i data-lucide="camera" size="12"></i> Upload Photo
    </div>

  </label>
  
  <!-- This is the hidden actual file picker -->
  <input type="file" id="avatar-upload" hidden accept="image/*" onchange="previewAvatar(this)">
</div>
          <div class="identity-fields-container">
            <div class="name-grid-row">
              <div class="form-group"><label>First Name *</label><input type="text" class="form-ctrl master-req" id="o-fname" placeholder="Ex: Abebe"></div>
              <div class="form-group"><label>Middle Name</label><input type="text" class="form-ctrl" id="o-mname" placeholder="Ex: Bikila"></div>
              <div class="form-group"><label>Last Name *</label><input type="text" class="form-ctrl master-req" id="o-lname" placeholder="Ex: Gebre"></div>
            </div>
            <div class="identity-divider"></div>
            <div class="demo-grid-row">
              <div class="form-group"><label>Date of Birth *</label><input type="date" class="form-ctrl master-req" id="o-dob"></div>
              <div class="form-group"><label>Gender *</label><div class="as-combo-container">
  <input type="text" id="o-gender" class="form-ctrl master-req" placeholder="Select Gender..." 
         onfocus="showAsDrop('as-drop-gender')" readonly>
  <div class="as-combo-results" id="as-drop-gender">
    <div class="as-res-item" onclick="selectAsItem('o-gender','as-drop-gender','Male')">Male</div>
    <div class="as-res-item" onclick="selectAsItem('o-gender','as-drop-gender','Female')">Female</div>
  </div>
</div></div>
              <div class="form-group"><label>Nationality *</label><input type="text" class="form-ctrl master-req" id="o-nat" value="Ethiopian" disabled></div>
            </div>
            <div class="demo-grid-row">
              <div class="form-group"><label>Marital Status</label><div class="as-combo-container">
  <input type="text" id="o-marital" class="form-ctrl" placeholder="Select Marital Status..." 
         onfocus="showAsDrop('as-drop-marital')" readonly>
  <div class="as-combo-results" id="as-drop-marital">
    <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Single')">Single</div>
    <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Married')">Married</div>
    <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Divorced')">Divorced</div>
    <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Widowed')">Widowed</div>
  </div>
</div></div>
              <div class="form-group" style="grid-column:span 2;"><label>Place of Birth (Region)</label><input type="text" class="form-ctrl" id="o-pob" placeholder="Enter region"></div>
            </div>
          </div>
        </div>
      </section>
      <section id="ob-step-2" class="form-section-content">
        <div class="input-group-label">Contact Channels</div>
        <p class="input-group-sub">Employee reachability and residential records.</p>
        <div class="form-grid fg-2">
          <div class="form-group"><label>Personal Phone *</label><input type="tel" class="form-ctrl master-req" id="o-phone"></div>
          <div class="form-group"><label>Personal Email *</label><input type="email" class="form-ctrl master-req" id="o-email"></div>
          <div class="form-group" style="grid-column:span 2;"><label>Permanent Address *</label><textarea class="form-ctrl master-req" id="o-addr" style="min-height:80px;"></textarea></div>
          <div class="form-group"><label>City *</label><input type="text" class="form-ctrl master-req" id="o-city" value="Addis Ababa"></div>
          <div class="form-group"><label>Postal Code</label><input type="text" class="form-ctrl" id="o-zip" value="1000"></div>
        </div>
      </section>
      <!-- EMPLOYMENT PLACEMENT (DYNAMIC VERSION) -->
<section id="ob-step-3" class="form-section-content">
  <div class="form-header-pro">
    <div class="input-group-label">Employment Placement</div>
    <p class="input-group-sub">Mapping the position within the corporate structure based on employment type.</p>
  </div>
  
  <div class="form-grid fg-3">
    <div class="form-group">
      <label>Department *</label>
   <div class="as-combo-container">
  <input type="text" id="o-dept" class="form-ctrl master-req" placeholder="Select Department..." 
         onfocus="showAsDrop('as-drop-dept')" readonly>
  <div class="as-combo-results" id="as-drop-dept">
    <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Engineering')">Engineering</div>
    <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Finance')">Finance</div>
    <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','HR')">HR</div>
    <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Sales')">Sales</div>
    <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Marketing')">Marketing</div>
  </div>
</div>
    </div>
    <div class="form-group">
      <label>Job Position *</label>
     <div class="as-combo-container">
  <input type="text" id="o-pos" class="form-ctrl master-req" placeholder="Select Position..." 
         onfocus="showAsDrop('as-drop-pos')" readonly>
  <div class="as-combo-results" id="as-drop-pos">
    <div class="as-res-item" onclick="selectAsItem('o-pos','as-drop-pos','Senior Dev')">Senior Dev</div>
    <div class="as-res-item" onclick="selectAsItem('o-pos','as-drop-pos','Analyst')">Analyst</div>
    <div class="as-res-item" onclick="selectAsItem('o-pos','as-drop-pos','Marketing Specialist')">Marketing Specialist</div>
  </div>
</div>
    </div>
    <div class="form-group">
      <label>Employment Type *</label>
      <div class="as-combo-container">
  <input type="text" id="o-etype" class="form-ctrl master-req" placeholder="Select Employment Type..." 
         onfocus="showAsDrop('as-drop-etype')" readonly>
  <div class="as-combo-results" id="as-drop-etype">
    <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Permanent / Full-Time'); updateEmploymentFields('full-time')">Permanent / Full-Time</div>
    <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Fixed-Term Contract'); updateEmploymentFields('contract')">Fixed-Term Contract</div>
    <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Part-Time'); updateEmploymentFields('part-time')">Part-Time</div>
    <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Internship'); updateEmploymentFields('internship')">Internship</div>
    <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Temporary / Casual'); updateEmploymentFields('temporary')">Temporary / Casual</div>
  </div>
</div>
    </div>
  </div>

  <!-- Dynamic Container -->
  <div id="dynamic-employment-fields" class="form-grid fg-3 mt-4" style="border-top: 1px dashed var(--border); padding-top: 20px; display: none;">
    <!-- Content injected via JS -->
  </div>
</section>
      <section id="ob-step-4" class="form-section-content">
        <div class="input-group-label">Financial & Treasury</div>
        <p class="input-group-sub">Payroll disbursement and statutory tax records.</p>
        <div class="form-grid fg-2">
          <div class="form-group"><label>Gross Salary (ETB) *</label><input type="number" class="form-ctrl master-req" id="o-sal"></div>
          <div class="form-group"><label>Tax ID (TIN) *</label><input type="text" class="form-ctrl master-req" id="o-tin"></div>
          <div class="form-group"><label>Bank Name *</label><div class="as-combo-container">
  <input type="text" id="o-bank" class="form-ctrl master-req" placeholder="Select Bank..." 
         onfocus="showAsDrop('as-drop-bank')" readonly>
  <div class="as-combo-results" id="as-drop-bank">
    <div class="as-res-item" onclick="selectAsItem('o-bank','as-drop-bank','CBE')">CBE</div>
    <div class="as-res-item" onclick="selectAsItem('o-bank','as-drop-bank','Awash')">Awash</div>
    <div class="as-res-item" onclick="selectAsItem('o-bank','as-drop-bank','Abyssinia')">Abyssinia</div>
  </div>
</div></div>
          <div class="form-group"><label>Account Number *</label><input type="text" class="form-ctrl master-req" id="o-acc"></div>
        </div>
      </section>
      <section id="ob-step-5" class="form-section-content">
        <div class="input-group-label">Compliance & Legal</div>
        <p class="input-group-sub">Final verification and emergency contact data.</p>
        <div class="form-grid fg-2">
          <div class="form-group"><label>Emergency Contact *</label><input type="text" class="form-ctrl master-req" id="o-ename"></div> 
          <div class="form-group"><label>Emergency Phone *</label><input type="tel" class="form-ctrl master-req" id="o-ephone"></div>
          <div class="form-group" style="grid-column: span 2;"><label>Emergency Relation to employee *</label><input type="text" class="form-ctrl master-req" id="o-idno"></div>
        </div>
      </section>
      <section id="ob-step-6" class="form-section-content">
        <div class="form-header-pro">
          <div class="input-group-label">Final Review</div>
          <p class="input-group-sub">Please verify all information before committing the master record to the database.</p>
        </div>
        <div id="summary-render-area" class="review-container">
          <!-- Data will be injected here by JavaScript -->
        </div>
      </section>
     <div style="margin-top:60px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);padding-top:20px;">
  <button class="btn btn-secondary" id="ob-prev" onclick="moveOnboarding(-1)" style="visibility:hidden;"><i data-lucide="chevron-left" size="14"></i> Previous</button>
  
  <!-- This area swaps between dots and the button -->
  <div id="ob-step-nav-center">
    <div class="dot-progress" id="ob-dots">
      <span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span>
    </div>
    <button class="btn btn-success" id="btn-save-master-bottom" style="display:none; padding:10px 30px;" onclick="saveNewEmployee()">
      <i data-lucide="user-plus" size="16"></i> Add Employee
    </button>
  </div>

  <button class="btn btn-primary" id="ob-next" onclick="moveOnboarding(1)" style="padding:10px 24px;">Next Step <i data-lucide="chevron-right" size="14"></i></button>
</div>
    </main>
  </div>
</div>

<!-- EMPLOYMENT TYPES -->
<div class="page" id="p-employment-types">
  <div class="page-header"><div><div class="page-title">Employment Types</div><div class="page-sub">Configure contract and employment categories</div></div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Add Type</button></div>
  <div id="tbl-employment-types"></div>
</div>

<!-- PROBATION TRACKER -->
<div class="page" id="p-probation-tracker">
  <div class="page-header"><div><div class="page-title">Probation Tracker</div><div class="page-sub">Monitor and confirm probationary employees</div></div><button class="btn btn-primary"><i data-lucide="check-circle" size="13"></i>Confirm Selected</button></div>
  <div id="tbl-probation"></div>
</div>

<!-- CONTRACT RENEWALS -->
<div class="page" id="p-contract-renewals">
  <div class="page-header"><div><div class="page-title">Contract Renewals</div><div class="page-sub">Expiring and up-for-renewal contracts</div></div><button class="btn btn-primary"><i data-lucide="refresh-cw" size="13"></i>Bulk Renew</button></div>
  <div id="tbl-contract-renewals"></div>
</div>

<!-- FORMER EMPLOYEES -->
<div class="page" id="p-former-employees">
  <div class="page-header"><div><div class="page-title">Former Employees</div><div class="page-sub">Historical offboarding archive</div></div><button class="btn btn-secondary"><i data-lucide="download" size="13"></i>Export</button></div>
  <div id="tbl-former-employees"></div>
</div>

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
<!-- ATTACHMENT VAULT -->
<div class="page" id="p-document-vault">
  <div class="page-header"><div><div class="page-title">Attachment Vault & Compliance</div><div class="page-sub">Tracking mandatory company-specific documents for all personnel</div></div></div>
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card" style="border-left:4px solid var(--primary);"><div class="stat-label"> Compliance rate</div><div class="stat-value">72.4%</div></div>
    <div class="stat-card" style="border-left:4px solid var(--danger);"><div class="stat-label">Missing  Files</div><div class="stat-value">48</div></div> 
    <div class="stat-card" style="border-left:4px solid var(--success);"><div class="stat-label">Non-Compliant Staff</div><div class="stat-value">12</div></div>
    <div class="stat-card" style="border-left:4px solid var(--success);"><div class="stat-label">Compliant Staff</div><div class="stat-value">156</div></div>
  </div>
  <div class="card">
    <div class="filter-bar">
      <div class="ml-auto flex-row">
        <span style="font-size:.7rem;color:var(--muted);font-weight:600;">LEGEND:</span>
        <span style="font-size:.65rem;display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:2px;background:var(--success);"></span>Uploaded</span>
        <span style="font-size:.65rem;display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:2px;background:#e2e8f0;border:1px dashed #cbd5e1;"></span>Missing</span>
      </div>
    </div>
    <div id="vault-matrix-container"></div>
  </div>
</div>

<!-- EMPLOYEE SPECIFIC VAULT DETAIL (Enterprise Theme) -->
<div class="page" id="p-employee-vault">
  <div class="master-profile-wrapper">
    
    <!-- Hero Header -->
    <header class="db-hero-banner" style="padding:16px 28px; min-height:auto; position:relative; margin-bottom:20px;">
      <div style="display:flex; align-items:center; gap:20px;">
        <button class="sidebar-toggle" onclick="goPage('document-vault')" style="background:rgba(255,255,255,0.2); border:none; color:white;">
          <i data-lucide="arrow-left" size="18"></i>
        </button>
        <div class="db-hero-content">
          <h1 class="db-hero-title" style="font-size:1.5rem;" id="v-emp-name">Employee Name</h1>
          <p class="db-hero-sub" id="v-emp-id">EMP-0000 • Personnel Archive</p>
        </div>
      </div>
      <div class="flex-row" style="gap:12px;">
        <div style="text-align:right; color:white; margin-right:15px;">
          <div style="font-size:0.6rem; font-weight:800; text-transform:uppercase; opacity:0.8;">Compliance Score</div>
          <div style="font-size:1.2rem; font-weight:800;" id="v-compliance-percent">85%</div>
        </div>
        <button class="btn-glass-pro-slim"><i data-lucide="download" size="14"></i><span>Download Zip</span></button>
      </div>
    </header>

    <div class="profile-main-grid" style="grid-template-columns: 320px 1fr; gap:20px;">
      
      <!-- Side Info -->
      <div class="flex-col" style="gap:20px;">
        <div class="data-card">
          <div class="card-label-strip"><i data-lucide="info" size="14"></i><span>Fulfillment Summary</span></div>
          <div class="card-content">
            <div class="data-entry"><span class="de-label">Uploaded</span><span class="de-value" style="color:var(--success)" id="v-count-upload">0</span></div>
            <div class="data-entry"><span class="de-label">Missing</span><span class="de-value" style="color:var(--danger)" id="v-count-missing">0</span></div>
            <div class="data-entry"><span class="de-label">Total Required</span><span class="de-value">12 Files</span></div>
            
          </div>
        </div>
  
      </div>

      <!-- Main Document List -->
      <div class="data-card">
        <div class="card-label-strip">
          <i data-lucide="folder-open" size="14"></i>
          <span>Mandatory Personnel Documents</span>
        </div>
        <div class="card-content" id="vault-docs-list">
            <!-- Dynamic rows will be injected here -->
        </div>
      </div>
      
      <br>

    </div>
  </div>
</div>

<!-- ASSET TRACKING   -->
<div class="page" id="p-asset-tracking">
  <div class="page-header">
    <div>
      <div class="page-title">Asset Registry</div>
      <div class="page-sub">Comprehensive tracking of corporate inventory, valuation, and protection plans</div>
    </div>
    <div class="flex-row">
      <button class="btn btn-secondary"><i data-lucide="layout-grid" size="13"></i> Category list</button>
      <button class="btn btn-primary" onclick="openAssetModal()">
        <i data-lucide="plus" size="13"></i> Register New Asset
      </button>
    </div>
  </div>

  <!-- Summary Ribbon -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card" style="border-left:4px solid var(--primary);"><div class="stat-label">Inventory Value</div><div class="stat-value">ETB 4.2M</div></div>
    <div class="stat-card" style="border-left:4px solid var(--info);"><div class="stat-label">Total Assets</div><div class="stat-value">256</div></div> 
    <div class="stat-card" style="border-left:4px solid var(--danger);"><div class="stat-label">Total custodians</div><div class="stat-value">03</div></div>
  </div>

  <!-- Standard Table Container -->
  <div id="tbl-assets"></div>
</div>

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

<!-- CANDIDATES -->
<div class="page" id="p-candidates">
  <div class="page-header"><div><div class="page-title">Job Applicant's List</div><div class="page-sub">Track applicants through hiring stages</div></div><div class="flex-row"><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Add Candidate</button></div></div>
  <div id="tbl-candidates"></div>
</div>

<!-- INTERVIEW TRACKER -->
<div class="page" id="p-interview-tracker">
  <div class="page-header"><div><div class="page-title">Interview Tracker</div><div class="page-sub">Schedule and track interview outcomes</div></div><button class="btn btn-primary"><i data-lucide="calendar-plus" size="13"></i>Schedule Interview</button></div>
  <div id="tbl-interviews"></div>
</div>

<!-- INTERNSHIP MANAGEMENT PAGE -->
<div class="page" id="p-internship">
  <div class="page-header"><div><div class="page-title">Internship Management</div><div class="page-sub">Academic partnerships and student trainees</div></div><div class="flex-row"><button class="btn btn-secondary"><i data-lucide="graduation-cap" size="13"></i>Potential hire</button><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Add Intern</button></div></div>
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card"><div class="stat-label">Active Interns</div><div class="stat-value">32</div></div>
    <div class="stat-card"><div class="stat-label">Evaluations Due</div><div class="stat-value" style="color:var(--warning);">08</div></div> 
    <div class="stat-card"><div class="stat-label">Average Evaluation Score</div><div class="stat-value" style="color:var(--success);">15%</div></div>
  </div>
  <div id="tbl-internship"></div>
</div>
 
<!-- DAILY ATTENDANCE -->
<div class="page" id="p-daily-attendance">
  <div class="page-header"><div><div class="page-title">Daily Attendance</div><div class="page-sub">Today — March 25, 2026</div></div><input type="date" value="2026-03-25" class="sel" style="font-family:inherit;"></div>
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card"><div class="stat-label">Present</div><div class="stat-value" style="color:var(--success);">1,148</div></div>
    <div class="stat-card"><div class="stat-label">Absent</div><div class="stat-value" style="color:var(--danger);">53</div></div>
    <div class="stat-card"><div class="stat-label">On Leave</div><div class="stat-value" style="color:var(--warning);">47</div></div>
    <div class="stat-card"><div class="stat-label">Late Arrivals</div><div class="stat-value" style="color:var(--info);">18</div></div>
  </div>
  <div id="tbl-attendance"></div>
</div>

<!-- OVERTIME REQUESTS -->
<div class="page" id="p-overtime-requests">
  <div class="page-header"><div><div class="page-title">Overtime Requests</div><div class="page-sub">Review and approve OT submissions</div></div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Submit OT Request</button></div>
  <div id="tbl-overtime"></div>
</div>

<div class="page" id="p-attendance-reports">
  <div class="page-header"><div><div class="page-title">Attendance Reports</div></div></div>
  <div id="tbl-attendance-reports"></div>
</div>
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

<!-- LEAVE TYPES -->
<div class="page" id="p-leave-types">
  <div class="page-header"><div><div class="page-title">Leave Types</div><div class="page-sub">Configure leave categories and entitlements</div></div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Add Leave Type</button></div>
  <div id="tbl-leave-types"></div>
</div>

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

<!-- LEAVE ENTITLEMENT -->
<div class="page" id="p-leave-entitlement">
  <div class="page-header"><div><div class="page-title">Leave Entitlement</div><div class="page-sub">Per-employee leave balance and policy</div></div></div>
  <div id="tbl-leave-entitlement"></div>
</div>

<!-- MEDICAL CLAIMS -->
<div class="page" id="p-medical-claims">
  <div class="page-header"><div><div class="page-title">Medical Claims</div><div class="page-sub">Process and track employee medical reimbursements</div></div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>New Claim</button></div>
  <div id="tbl-medical"></div>
</div>

<!-- TRAINING NEEDS -->
<div class="page" id="p-training-needs">
  <div class="page-header"><div><div class="page-title">Training Needs Analysis</div><div class="page-sub">Identify skill gaps across the organization</div></div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Add TNA</button></div>
  <div id="tbl-training-needs"></div>
</div> 

<!-- TRAINING SCHEDULE -->
<div class="page" id="p-training-schedule">
  <div class="page-header"><div><div class="page-title">Training Schedule</div><div class="page-sub">Upcoming sessions and enrollment</div></div><button class="btn btn-primary"><i data-lucide="calendar-plus" size="13"></i>Schedule Session</button></div>
  <div id="tbl-training-schedule"></div>
</div>  

<!-- PERFORMANCE REVIEWS -->
<div class="page" id="p-performance-reviews">
  <div class="page-header"><div><div class="page-title">Performance Reviews</div><div class="page-sub">Q1 2026 review cycle</div></div><button class="btn btn-primary"><i data-lucide="plus" size="13"></i>Start Review</button></div>
  <div id="tbl-reviews"></div>
</div>

<!-- 360 FEEDBACK -->
<div class="page" id="p-360-feedback">
  <div class="page-header"><div><div class="page-title">360° Feedback</div><div class="page-sub">Peer, manager and subordinate evaluations</div></div><button class="btn btn-primary"><i data-lucide="send" size="13"></i>Send Feedback Form</button></div>
  <div id="tbl-360"></div>
</div>  

<!-- PROMOTE / DEMOTE -->
<div class="page" id="p-Promote/Demote">
  <div class="page-header"><div><div class="page-title">Promote/Demote</div><div class="page-sub">Employee promotion requests and approvals</div></div><button class="btn btn-primary"><i data-lucide="trending-up" size="13"></i>Initiate Change</button></div>
  <div id="tbl-Promote/Demote"></div>
</div>

<!-- TRANSFERS -->
<div class="page" id="p-transfers">
  <div class="page-header"><div><div class="page-title">Department Transfers</div><div class="page-sub">Interdepartmental and branch transfers</div></div><button class="btn btn-primary"><i data-lucide="arrow-right-left" size="13"></i>Request Transfer</button></div>
  <div id="tbl-transfers-dept"></div>
</div>

<!-- DISCIPLINARY ACTIONS -->
<div class="page" id="p-disciplinary-actions">
  <div class="page-header"><div><div class="page-title">Disciplinary Actions</div><div class="page-sub">Warnings, suspensions and formal actions</div></div><button class="btn btn-danger"><i data-lucide="alert-triangle" size="13"></i>Record Action</button></div>
  <div id="tbl-disciplinary"></div>
</div>

<!-- Resignations -->
<div class="page" id="p-resignations">
  <div class="page-header"><div><div class="page-title">Resignations Management</div><div class="page-sub">Employee complaints and resolution tracking</div></div> </div>
  <div id="tbl-resignations"></div>
</div>

<!-- TERMINATION -->
<div class="page" id="p-termination">
  <div class="page-header"><div><div class="page-title">Separation & Exit</div><div class="page-sub">Manage employee offboarding</div></div><button class="btn btn-danger"><i data-lucide="user-minus" size="13"></i>Initiate Separation</button></div>
  <div class="stats-row stats-3 mb-4">
    <div class="stat-card"><div class="stat-label">Turnover Rate (YTD)</div><div class="stat-value">4.2%</div></div>
    <div class="stat-card"><div class="stat-label">In Offboarding</div><div class="stat-value" style="color:var(--warning);">8</div></div>
    <div class="stat-card"><div class="stat-label">Exits This Month</div><div class="stat-value">12</div></div>
  </div>
  <div id="tbl-termination"></div>
</div>

<!-- EXIT CLEARANCE -->
<div class="page" id="p-exit-clearance">
  <div class="page-header"><div><div class="page-title">Exit Clearance</div><div class="page-sub">Asset return and department sign-offs</div></div><button class="btn btn-secondary"><i data-lucide="printer" size="13"></i>Print Clearance Form</button></div>
  <div id="tbl-clearance"></div>
</div>

<!-- HR ANALYTICS -->
<div class="page" id="p-hr-analytics">
  <div class="page-header"><div><div class="page-title">HR Analytics</div><div class="page-sub">Executive workforce intelligence dashboard</div></div></div>
  <div class="stats-row stats-4">
    <div class="stat-card"><div class="stat-label">Headcount</div><div class="stat-value">1,248</div><div class="stat-change stat-up">↑ 12 this month</div></div>
    <div class="stat-card"><div class="stat-label">Avg Tenure</div><div class="stat-value">3.4 yrs</div><div class="stat-change stat-up">↑ vs 3.1 last year</div></div>
    <div class="stat-card"><div class="stat-label">Gender Ratio</div><div class="stat-value">54/46</div><div class="stat-change" style="color:var(--muted);">M/F ratio</div></div>
    <div class="stat-card"><div class="stat-label">Eng. Score</div><div class="stat-value">7.8/10</div><div class="stat-change stat-up">↑ 0.3 vs Q4</div></div>
  </div>
  <div class="dash-chart-row mt-4">
    <div class="card"><div class="card-header"><div class="card-title">Hire vs. Attrition Trend</div></div><div class="card-body" style="height:220px;"><canvas id="chart-hire-attrition"></canvas></div></div>
    <div class="card"><div class="card-header"><div class="card-title">Headcount by Age Group</div></div><div class="card-body" style="height:220px;"><canvas id="chart-age"></canvas></div></div>
  </div>
</div>

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

<!-- USER MANAGEMENT -->
<div class="page" id="p-user-management">
  <div class="page-header"><div><div class="page-title">User Management</div><div class="page-sub">System accounts and access control</div></div><button class="btn btn-primary"><i data-lucide="user-plus" size="13"></i>Invite User</button></div>
  <div id="tbl-users"></div>
</div>

<!-- ROLES & PERMISSIONS -->
<div class="page" id="p-roles-permissions">
  <div class="page-header">
    <div>
      <div class="page-title">Access Control & Governance</div>
      <div class="page-sub">Define module visibility for system roles or specific personnel overrides</div>
    </div>
    <div class="flex-row">
      <div id="save-status-indicator" style="font-size: .65rem; color: var(--success); font-weight: 700; display:none; align-items:center; gap:5px;">
        <i data-lucide="check-circle" size="12"></i> Changes Synced
      </div>
      <button class="btn btn-primary" onclick="savePermissionChanges()">
        <i data-lucide="shield-check" size="13"></i> Commit Permissions
      </button>
    </div>
  </div>

  <!-- MODE SELECTOR TABS -->
  <div class="flex-row mb-4" style="background: #fff; padding: 4px; border-radius: 10px; border: 1px solid var(--border); width: fit-content;">
    <button class="btn btn-sm" id="btn-mode-role" style="background: var(--primary-light); color: var(--primary); border:none;" onclick="switchAccessMode('role')">Standard Roles</button>
    <button class="btn btn-sm" id="btn-mode-user" style="background: transparent; color: var(--muted); border:none;" onclick="switchAccessMode('user')">Individual Roles</button>
  </div>

  <div class="roles-layout">
    <aside class="flex-col">
      <!-- SIDEBAR A: STANDARD ROLES -->
      <div id="side-role-list">
        <div class="role-pill-v2 active" onclick="selRole(this,'Super Admin')">
          <i data-lucide="shield-check" size="16"></i>
          <div><b style="font-size:.75rem">Super Admin</b><p style="font-size:.6rem;color:var(--muted)">Full System Authority</p></div>
        </div>
        <div class="role-pill-v2" onclick="selRole(this,'HRM User')">
          <i data-lucide="user-cog" size="16"></i>
          <div><b style="font-size:.75rem">HRM User</b><p style="font-size:.6rem;color:var(--muted)">Standard HR Operations</p></div>
        </div>
        <div class="role-pill-v2" onclick="selRole(this,'Department Manager')">
          <i data-lucide="briefcase" size="16"></i>
          <div><b style="font-size:.75rem">Dept Manager</b><p style="font-size:.6rem;color:var(--muted)">Limited to Team Data</p></div>
        </div>
      </div>

      <!-- SIDEBAR B: INDIVIDUAL SEARCH (Hidden by default) -->
      <div id="side-user-search" style="display:none;">
        <div class="form-group" style="position:relative;">
          <label>Search Personnel</label>
          <div class="as-combo-container">
            <input type="text" id="as-input-perm-user" class="form-ctrl" placeholder="Enter name..." 
                   onfocus="showAsDrop('as-drop-perm-user')" 
                   oninput="filterAsDrop('as-input-perm-user','as-drop-perm-user')">
            <div class="as-combo-results" id="as-drop-perm-user"></div>
          </div>
        </div>
        <div id="selected-user-card" class="mt-4" style="display:none;">
            <div class="stat-card" style="padding:12px; border:1px solid var(--primary); background: var(--primary-light);">
                <div class="flex-row" style="gap:10px;">
                    <div class="avatar" id="perm-user-avatar">??</div>
                    <div>
                        <div id="perm-user-name" style="font-size:.75rem; font-weight:800; color:var(--primary-dark)">User Name</div>
                        <div id="perm-user-id" style="font-size:.6rem; color:var(--muted); font-family: 'JetBrains Mono'">E-000</div>
                    </div>
                </div>
                <div class="badge badge-success" style="width:100%; margin-top:10px; justify-content:center; font-size:9px;">Custom Profile Active</div>
            </div>
        </div>
      </div>
    </aside>

    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span id="perm-target-label">Standard Role:</span> 
          <span id="active-role-name" style="color:var(--primary)">Super Admin</span>
        </div>
        <div id="override-warning" style="display:none;" class="badge badge-warning">Custom Mode</div>
      </div>
      <div class="table-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width:40px"></th>
              <th>Module</th>
              <th>Access Description</th>
              <th style="text-align:center;width:100px">Visible</th>
            </tr>
          </thead>
          <tbody id="perm-grid"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

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
</div></div>
  </div>
  <div id="tbl-audit"></div>
</div>

  </div>
</main>
<?php
include 'includes/footer.php'; 
?>
<!-- Toast Notification Container -->
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>
</body>
</html>