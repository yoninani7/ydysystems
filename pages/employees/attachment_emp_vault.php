<!-- ============================================================
  FILE: pages/employees/attachment_emp_vault.php
  PURPOSE: Per-employee document detail view — shows all 12
           mandatory/optional documents with upload/view actions.
  CHANGES FROM ORIGINAL:
    - Added doc category badge on each row
    - Added mandatory/optional badge
    - Added last-updated date display
    - Added file name display (truncated) on uploaded rows
    - Upload modal trigger replaces the old showNotification stub
    - Added "Download Zip" function stub on header button
    - Progress ring now filled by real compliance % from API
    - Side card shows real mandatory vs optional breakdown
============================================================ -->
<?php $token = csrf_token(); ?>
<meta name="csrf" content="<?php echo $token; ?>">
<div class="page active" id="p-employee-vault">
  <div class="master-profile-wrapper">

    <!-- ── Hero Banner ── -->
    <header class="db-hero-banner" style="padding:16px 28px; min-height:auto; position:relative; margin-bottom:20px;">
      <div style="display:flex; align-items:center; gap:20px;">
        <!-- Back button navigates to the vault matrix -->
        <button class="sidebar-toggle"
                onclick="goPage('document-vault', document.querySelector('.sub-link[onclick*=document-vault]'))"
                style="background:rgba(255,255,255,0.2); border:none; color:white;"
                title="Back to Vault Matrix">
          <i data-lucide="arrow-left" size="18"></i>
        </button>
        <div class="db-hero-content">
          <!-- Both fields populated by openEmployeeVault() in core.js -->
          <h1 class="db-hero-title" style="font-size:1.5rem;" id="v-emp-name">Employee Name</h1>
          <p class="db-hero-sub" id="v-emp-id">EMP-0000 • Personnel Archive</p>
        </div>
      </div>

      <!-- Compliance score + Download Zip -->
      <div class="flex-row" style="gap:12px;">
        <div style="text-align:right; color:white; margin-right:15px;">
          <div style="font-size:.6rem; font-weight:800; text-transform:uppercase; opacity:.8;">Compliance Score</div>
          <div style="font-size:1.2rem; font-weight:800;" id="v-compliance-percent">--%</div>
        </div>
        <!-- Download Zip — wires up in core.js via data-emp-id attribute -->
        <button class="btn-glass-pro-slim" id="btn-download-zip" title="Download all uploaded documents as ZIP">
          <i data-lucide="download" size="14"></i>
          <span>Download Zip</span>
        </button>
      </div>
    </header>

    <!-- ── Body Grid ── -->
    <div class="profile-main-grid" style="grid-template-columns:320px 1fr; gap:20px;">

      <!-- ── Sidebar Info Cards ── -->
      <div class="flex-col" style="gap:20px;">

        <!-- Fulfillment summary -->
        <div class="data-card">
          <div class="card-label-strip"><i data-lucide="pie-chart" size="14"></i><span>Fulfillment Summary</span></div>
          <div class="card-content">
            <div class="data-entry">
              <span class="de-label">Uploaded</span>
              <span class="de-value" style="color:var(--success)" id="v-count-upload">0</span>
            </div>
            <div class="data-entry">
              <span class="de-label">Missing</span>
              <span class="de-value" style="color:var(--danger)" id="v-count-missing">0</span>
            </div>
            <div class="data-entry">
              <span class="de-label">Total Required</span>
              <span class="de-value">12 Files</span>
            </div>
          </div>
        </div>

        <!-- Mandatory vs optional breakdown — values injected by JS -->
        <div class="data-card">
          <div class="card-label-strip"><i data-lucide="shield-check" size="14"></i><span>Mandatory Breakdown</span></div>
          <div class="card-content">
            <div class="data-entry">
              <span class="de-label">Mandatory Done</span>
              <span class="de-value" style="color:var(--success)" id="v-count-mandatory-done">—</span>
            </div>
            <div class="data-entry">
              <span class="de-label">Mandatory Missing</span>
              <span class="de-value" style="color:var(--danger)" id="v-count-mandatory-miss">—</span>
            </div>
            <div class="data-entry">
              <span class="de-label">Optional Done</span>
              <span class="de-value" style="color:var(--muted)" id="v-count-optional-done">—</span>
            </div>
          </div>
        </div>

        <!-- Quick hint card -->
        <div class="data-card" style="background:var(--primary-light); border-color:var(--primary);">
          <div class="card-content" style="padding:12px;">
            <div style="display:flex; gap:8px; align-items:flex-start;">
              <i data-lucide="info" size="16" style="color:var(--primary); flex-shrink:0; margin-top:2px;"></i>
              <div style="font-size:.72rem; line-height:1.5; color:var(--text);">
                Click <strong>Add Document</strong> to upload a file.
                Click <strong>View</strong> to preview an existing document. 
              </div>
            </div>
          </div>
        </div>

      </div><!-- /sidebar -->

      <!-- ── Main Document List ── -->
      <div class="data-card">
        <div class="card-label-strip">
          <i data-lucide="folder-open" size="14"></i>
          <span>Mandatory Personnel Documents</span>
          <!-- Right side: search input for filtering doc list instantly -->
          <div class="ml-auto">
            <input type="text"
                   id="vault-doc-search"
                   placeholder="Filter documents..."
                   oninput="filterVaultDocs(this.value)"
                   style="font-size:.72rem; padding:4px 10px; border:1px solid var(--border);
                          border-radius:8px; outline:none; width:180px;">
          </div>
        </div>

        <!-- Each .doc-row is injected here by loadEmployeeVaultDetail() in core.js -->
        <div class="card-content vault-docs-container" id="vault-docs-list">
          <!-- Loading state shown until JS populates this -->
          <div id="vault-loading-state" style="padding:40px; text-align:center; color:var(--muted);">
            <i data-lucide="loader" size="24" style="animation:spin 1s linear infinite;"></i>
            <div style="margin-top:8px; font-size:.8rem;">Loading documents…</div>
          </div>
        </div>
      </div><!-- /main doc list -->

    </div><!-- /profile-main-grid -->
  </div><!-- /master-profile-wrapper -->
</div><!-- /page -->

<!-- ──────────────────────────────────────────────────────────
     UPLOAD MODAL
     Triggered by openUploadModal(docTypeId, docTypeName, empId)
     Actual file-upload AJAX is wired in core.js
────────────────────────────────────────────────────────────── -->
<div id="upload-modal-overlay" style="display:none; position:fixed; inset:0;
     background:rgba(15,23,42,.6); backdrop-filter:blur(8px);
     z-index:9000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:20px; padding:32px 28px; width:100%;
              max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,.2);">

    <!-- Modal header -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
      <div>
        <div style="font-size:1rem; font-weight:800; color:var(--text);">Upload Document</div>
        <div style="font-size:.72rem; color:var(--muted);" id="upload-modal-doc-name">Document Name</div>
      </div>
      <button onclick="closeUploadModal()" style="background:none; border:none; cursor:pointer; color:var(--muted);">
        <i data-lucide="x" size="20"></i>
      </button>
    </div>

    <!-- Drop zone -->
    <div id="upload-drop-zone"
         style="border:2px dashed var(--border); border-radius:12px; padding:32px;
                text-align:center; cursor:pointer; transition:all .2s;"
         onclick="document.getElementById('upload-file-input').click()"
         ondragover="event.preventDefault(); this.style.borderColor='var(--primary)';"
         ondragleave="this.style.borderColor='var(--border)';"
         ondrop="handleDropZone(event)">
      <i data-lucide="upload-cloud" size="32" style="color:var(--primary); display:block; margin:0 auto 8px;"></i>
      <div style="font-size:.8rem; font-weight:700; color:var(--text);">Click or drag &amp; drop</div>
      <div style="font-size:.7rem; color:var(--muted); margin-top:4px;">PDF, JPG, PNG — max 10 MB</div>
    </div>

    <input type="file" id="upload-file-input" accept=".pdf,.jpg,.jpeg,.png"
           style="display:none;" onchange="onFileSelected(this)">

    <!-- Selected file preview -->
    <div id="upload-file-preview" style="display:none; margin-top:12px; padding:10px 14px;
         background:var(--primary-light); border-radius:10px; font-size:.78rem;
         font-weight:700; color:var(--primary);">
      <i data-lucide="file-check" size="14" style="vertical-align:middle; margin-right:6px;"></i>
      <span id="upload-preview-name"></span>
    </div>

    <!-- Action buttons -->
    <div style="display:flex; gap:10px; margin-top:20px;">
      <button onclick="closeUploadModal()"
              class="btn btn-secondary" style="flex:1;">Cancel</button>
      <button id="btn-confirm-upload" onclick="confirmUpload()"
              class="btn btn-primary" style="flex:2;" disabled>
        <i data-lucide="upload" size="14"></i> Upload
      </button>
    </div>

  </div>
</div>
