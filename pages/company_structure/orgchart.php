  <!-- ORG CHART -->
<div class="page" id="p-org-chart">
  <div class="page-header">
    <div><div class="page-title">Organizational Hierarchy</div><div class="page-sub">Department structure with job positions and headcount</div></div>
    <div class="flex-row" style="gap:10px;">
      <div class="icon-btn-group" style="display:flex;background:#fff;border:1px solid var(--border);border-radius:8px;padding:2px;">
        <button class="icon-btn" onclick="zoomOC(-0.1)" title="Zoom Out" style="border:none;"><i data-lucide="minus-circle" size="16"></i></button>
        <div id="oc-zoom-label" style="font-size:.7rem;font-weight:800;width:45px;display:flex;align-items:center;justify-content:center;color:var(--muted);border-left:1px solid var(--border);border-right:1px solid var(--border);">100%</div>
        <button class="icon-btn" onclick="zoomOC(0.1)" title="Zoom In" style="border:none;"><i data-lucide="plus-circle" size="16"></i></button>
        <button class="icon-btn" onclick="resetOC()" title="Reset Chart" style="border:none;color:var(--primary);"><i data-lucide="refresh-cw" size="14"></i></button>
      </div>
      <button class="btn btn-primary"><i data-lucide="printer" size="14"></i> Export PDF</button>
    </div>
  </div>
  <div class="oc-blueprint" id="oc-blueprint-area">
    <div class="oc-container" id="oc-zoom-container" style="transform-origin:top center;transition:transform .2s ease-out;">
      <ul class="oc-tree" id="oc-tree-root">
        <li>
          <div class="oc-node oc-mgr">
            <div class="oc-node-header">
              <span class="oc-id">ORGANIZATION</span>
              <span class="badge badge-primary" style="font-size:8px;">1,248 TOTAL</span>
            </div>
            <div class="oc-node-body">
              <div class="oc-node-avatar" style="background:var(--primary);color:#fff;">
                <i data-lucide="briefcase-business" size="20"></i>
              </div>
              <div class="oc-node-info">
                <div class="oc-node-name">YDY Systems</div>
                <div class="oc-node-role">Structure</div>
              </div>
            </div>
            <div class="oc-node-footer">7 Departments</div>
          </div>
          <ul id="dept-tree-container">
            <!-- Departments with job titles will be inserted here -->
          </ul>
        </li>
      </ul>
    </div>
  </div>
</div>