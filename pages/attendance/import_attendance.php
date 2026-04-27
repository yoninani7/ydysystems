<div class="page-header">
    <div>
        <h1 class="page-title">Attendance Import</h1>
        <p class="page-sub">Bulk-load attendance records from an Excel or CSV file</p>
    </div>
    <button class="btn btn-secondary" onclick="showNotification('System','Downloading template...','info')">
        <i data-lucide="download" size="16" style="margin-right:8px"></i> Download Template
    </button>
</div>

<!-- Instruction Box -->
<div class="mb-4" style="background: #f0fdf4; border: 1.5px solid #dcfce7; border-radius: 12px; padding: 20px; display: flex; gap: 16px;">
    <div style="color: #15b201; flex-shrink: 0;"><i data-lucide="info" size="24"></i></div>
    <div>
        <h4 style="font-size: 0.9rem; font-weight: 800; color: #166534; margin-bottom: 6px;">How to Import</h4>
        <ol style="font-size: 0.8rem; color: #166534; line-height: 1.8; padding-left: 14px;">
            <li>Download the template CSV above</li>
            <li>Fill in columns: <code style="background: rgba(255,255,255,0.6); padding: 2px 4px; border-radius: 4px;">employee_code</code>, <code style="background: rgba(255,255,255,0.6); padding: 2px 4px; border-radius: 4px;">date</code>, <code style="background: rgba(255,255,255,0.6); padding: 2px 4px; border-radius: 4px;">status</code>, <code style="background: rgba(255,255,255,0.6); padding: 2px 4px; border-radius: 4px;">check_in</code>, <code style="background: rgba(255,255,255,0.6); padding: 2px 4px; border-radius: 4px;">check_out</code></li>
            <li>Save and upload the file below</li>
        </ol>
    </div>
</div>

<!-- Drag and Drop Zone -->
<div class="card" id="attendance-upload-container">
    <div class="card-body" style="padding: 40px;">
        <div id="drop-zone" 
             style="border: 2px dashed #e2e8f0; border-radius: 16px; padding: 60px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.2s;"
             onclick="document.getElementById('csv-file-input').click()"
             ondragover="handleDragOver(event)"
             ondragleave="handleDragLeave(event)"
             ondrop="handleDrop(event)">
            
            <input type="file" id="csv-file-input" hidden accept=".csv, .xlsx" onchange="processImportFile(this.files[0])">
            
            <div id="drop-zone-icon" style="color: #15b201; margin-bottom: 16px; transition: transform 0.2s;">
                <i data-lucide="upload-cloud" size="48"></i>
            </div>
            <h3 id="drop-zone-text" style="font-size: 1.1rem; font-weight: 800; color: var(--text);">Drop your CSV file here</h3>
            <p style="font-size: 0.85rem; color: var(--muted); margin-top: 4px; margin-bottom: 24px;">or click to browse</p>
            
            <button class="btn btn-secondary" style="padding: 10px 24px; background: #fff; border: 1.5px solid #e2e8f0;">
                <i data-lucide="folder-open" size="16" style="margin-right:8px"></i> Browse File
            </button>
        </div>
    </div>
</div>

<!-- Instant Preview (Hidden) -->
<div id="attendance-preview-container" style="display: none; animation: modalIn 0.3s ease;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Import Preview & Validation</span>
            <div class="flex-row">
                <button class="btn btn-secondary btn-sm" onclick="goPage('import-attendance')">Cancel</button>
                <button class="btn btn-primary btn-sm" onclick="finalizeImport(this)">
                    <i data-lucide="check-circle" size="14" style="margin-right:6px"></i> Confirm & Save
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>EMP. CODE</th><th>DATE</th><th>STATUS</th><th>CHECK-IN</th><th>CHECK-OUT</th><th style="text-align:right">VALIDATION</th>
                    </tr>
                </thead>
                <tbody id="preview-tbody"></tbody>
            </table>
        </div>
    </div>
</div>