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