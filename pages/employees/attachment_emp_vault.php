<?php $token = csrf_token(); ?>
<meta name="csrf" content="<?php echo $token; ?>">

<style>
/* ── Strip out badges as requested ── */
.vault-cat-chip, .badge-mandatory, .badge-optional, .doc-status { display: none !important; }

/* ── Refined Document Row Grid ── */
.doc-row {
    display: grid !important;
    grid-template-columns: 50px 1.5fr 1fr 140px !important;
    align-items: center !important;
    padding: 16px 24px !important;
    border-bottom: 1px solid #f1f5f9 !important;
    background: #fff !important;
    transition: background 0.2s ease !important;
    gap: 16px !important;
}
.doc-row:last-child { border-bottom: none !important; }
.doc-row:hover { background: var(--primary-light) !important; }

/* Icon Styling (Colors applied for missing documents here) */
.doc-icon-box {
    width: 42px !important;
    height: 42px !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}
.doc-icon-box.uploaded { 
    background: var(--success-bg) !important; 
    color: var(--success) !important; 
    border: 1px solid #bbf7d0 !important; 
}
.doc-icon-box.missing { 
    background: var(--danger-bg) !important; 
    color: var(--danger) !important; 
    border: 2px dashed #fca5a5 !important; 
}

/* Typography Formatting */
.doc-meta { display: flex !important; flex-direction: column !important; justify-content: center !important; gap: 0 !important; }
.doc-name { font-size: 0.85rem !important; font-weight: 700 !important; color: var(--text) !important; }

.doc-file-info { display: flex !important; flex-direction: column !important; justify-content: center !important; gap: 2px !important; }
.doc-file-name { font-size: 0.75rem !important; font-weight: 600 !important; color: var(--text) !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; max-width: 200px; }
.doc-file-date { font-family: 'JetBrains Mono', monospace !important; font-size: 0.7rem !important; color: var(--muted) !important; }

/* Actions */
.doc-actions { display: flex !important; justify-content: flex-end !important; gap: 8px !important; }
.btn-upload-pro { padding: 6px 14px !important; font-size: 0.75rem !important; }
</style>

<div class="page active" id="p-employee-vault">
  <div class="master-profile-wrapper">

    <!-- ── Restored Original Hero Banner ── -->
    <header class="db-hero-banner" style="padding:16px 28px; min-height:auto; position:relative; margin-bottom:20px;">
      <div style="display:flex; align-items:center; gap:20px;">
        
        <!-- Clear Back Button -->
        <button class="btn-glass-pro-slim" 
                onclick="goPage('document-vault', document.querySelector('.sub-link[onclick*=document-vault]'))"
                style="padding: 6px 12px; cursor: pointer;">
          <i data-lucide="arrow-left" size="16"></i>
          
        </button>

        <div class="db-hero-content">
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

    <!-- ── Restored Original Body Grid ── -->
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
            <div class="data-entry" style="background:#f8fafc;">
              <span class="de-label">Total Required</span>
              <span class="de-value">12 Files</span>
            </div>
          </div>
        </div>

        <!-- Mandatory vs optional breakdown -->
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
        <div class="card-label-strip" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="folder-open" size="14"></i>
            <span>Personnel Documents</span>
          </div>
          <!-- SEARCH BAR REMOVED FROM HERE -->
        </div>

        <!-- Each .doc-row is injected here by loadEmployeeVaultDetail() in core.js -->
        <div class="card-content vault-docs-container" id="vault-docs-list">
          <div id="vault-loading-state" style="padding:60px 20px; text-align:center; color:var(--muted);">
            <i data-lucide="loader" size="24" style="animation:spin 1s linear infinite; margin-bottom: 12px; color: var(--primary);"></i>
            <div style="font-size:.8rem; font-weight: 600;">Loading documents…</div>
          </div>
        </div>
      </div><!-- /main doc list -->

    </div><!-- /profile-main-grid -->
  </div><!-- /master-profile-wrapper -->
</div><!-- /page -->

<!-- ──────────────────────────────────────────────────────────
     UPLOAD MODAL (Original Clean Modal)
────────────────────────────────────────────────────────────── -->
<div id="upload-modal-overlay" style="display:none; position:fixed; inset:0;
     background:rgba(15,23,42,.6); backdrop-filter:blur(4px);
     z-index:9000; align-items:center; justify-content:center; padding: 20px;">
  <div class="modal-box" style="width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,.2);">

    <!-- Modal header -->
    <div class="modal-header" style="align-items: center;">
      <div>
        <div style="font-size:1rem; font-weight:800; color:var(--text); margin-bottom: 2px;">Upload Document</div>
        <div style="font-size:.72rem; color:var(--muted); font-weight: 600;" id="upload-modal-doc-name">Document Name</div>
      </div>
      <button onclick="closeUploadModal()" style="background:#f1f5f9; border:none; cursor:pointer; color:var(--muted); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
        <i data-lucide="x" size="16"></i>
      </button>
    </div>

    <!-- Drop zone -->
    <div class="modal-body">
      <div id="upload-drop-zone"
           style="border:2px dashed var(--border); border-radius:12px; padding:40px 20px;
                  text-align:center; cursor:pointer; transition:all .2s; background: #fafafa;"
           onclick="document.getElementById('upload-file-input').click()"
           ondragover="event.preventDefault(); this.style.borderColor='var(--primary)'; this.style.background='#f1fcf0';"
           ondragleave="this.style.borderColor='var(--border)'; this.style.background='#fafafa';"
           ondrop="handleDropZone(event)">
        <i data-lucide="upload-cloud" size="32" style="color:var(--primary); display:block; margin:0 auto 12px;"></i>
        <div style="font-size:.85rem; font-weight:700; color:var(--text); margin-bottom: 4px;">Click or drag &amp; drop file</div>
        <div style="font-size:.7rem; color:var(--muted);">PDF, JPG, PNG — max 10 MB</div>
      </div>

      <input type="file" id="upload-file-input" accept=".pdf,.jpg,.jpeg,.png"
             style="display:none;" onchange="onFileSelected(this)">

<!-- Selected file preview -->
<div id="upload-file-preview" style="display:none; margin-top:16px; padding:12px 16px;
     background: #f0fdf4; border-radius:12px; border: 1px solid #bbf7d0;
     /* Ensure it's a single row, items centered vertically, and button pushed to far right */
     display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important;">
  
  <!-- Left Side: Icon and Name grouped together -->
  <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
    <i data-lucide="file-check" size="18" style="color: #22c55e; flex-shrink: 0;"></i>
    <span id="upload-preview-name" style="font-size:.85rem; font-weight:700; color:#166534; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px;"></span>
  <!-- Right Side: Delete Button -->
  <button type="button" onclick="removeSelectedFile()" 
          style="background: transparent; border: none; cursor: pointer; color: #ef4444; padding: 4px; display: flex; align-items: center; flex-shrink: 0;right:0;">
    <i data-lucide="trash-2" size="18"></i>
  </button>

 </div>  
</div>
    </div>

    <!-- Action buttons -->
    <div class="modal-footer" style="background: #f8fafc;">
      <button onclick="closeUploadModal()" class="btn btn-secondary" style="padding: 8px 20px;">Cancel</button>
      <button id="btn-confirm-upload" onclick="confirmUpload()" class="btn btn-primary" style="padding: 8px 20px;" disabled>
        <i data-lucide="check" size="14"></i> Upload document
      </button>
    </div>

  </div>
</div>
<script>
  function removeSelectedFile() {
    // 1. Clear the hidden file input (so you can re-select the same file if you want)
    const fileInput = document.getElementById('upload-file-input');
    fileInput.value = "";

    // 2. Hide the green preview box
    const previewBox = document.getElementById('upload-file-preview');
    previewBox.style.display = 'none';

    // 3. Disable the "Commit Upload" button
    const commitBtn = document.getElementById('btn-confirm-upload');
    commitBtn.disabled = true;
}
</script>