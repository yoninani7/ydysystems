<!-- EMPLOYEE SPECIFIC VAULT DETAIL -->
<div class="page active" id="p-employee-vault">
  <div class="master-profile-wrapper">
    
    <!-- Hero Header -->
    <header class="db-hero-banner" style="padding:16px 28px; min-height:auto; position:relative; margin-bottom:20px;">
      <div style="display:flex; align-items:center; gap:20px;">
        <button class="sidebar-toggle" onclick="window.history.back()" style="background:rgba(255,255,255,0.2); border:none; color:white;">
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
          <div style="font-size:1.2rem; font-weight:800;" id="v-compliance-percent">--%</div>
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
          <!-- Dynamic rows will be injected by JavaScript -->
        </div>
      </div>
      
    </div>
  </div>
</div>