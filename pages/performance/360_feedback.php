<!-- FILE: pages/performance/360_feedback.php -->
<div class="page" id="p-360-feedback">
  <div class="page-header">
    <div>
      <div class="page-title">360° Feedback</div>
      <div class="page-sub">Peer, manager and subordinate evaluations</div>
    </div>
    <button class="btn btn-primary" onclick="openStartFeedbackModal()"><i data-lucide="send" size="13"></i>Send Feedback Form</button>
  </div>

  <!-- STATS -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card"><div class="stat-label">Active Cycles</div><div class="stat-value" style="color:#0891b2;">3</div><div class="stat-sub">Ongoing rounds</div></div>
    <div class="stat-card"><div class="stat-label">Responses Received</div><div class="stat-value" style="color:#16a34a;" id="stat-responses">156</div><div class="stat-sub">/240 requested</div></div>
    <div class="stat-card"><div class="stat-label">Completion Rate</div><div class="stat-value" style="color:#15b201;" id="stat-completion">65%</div><div class="stat-sub">Avg across cycles</div></div>
    <div class="stat-card"><div class="stat-label">Avg Score</div><div class="stat-value" style="color:#f59e0b;" id="stat-avgscore">4.1/5</div><div class="stat-sub">Overall rating</div></div>
  </div>

  <!-- TABS -->
  <div class="flex-row mb-4" style="gap:8px; border-bottom:1px solid var(--border);">
    <button class="btn btn-sm fbk-tab active" onclick="switchFeedbackTab('active', this)">Active Cycles</button>
    <button class="btn btn-sm fbk-tab" onclick="switchFeedbackTab('pending', this)">Pending Response</button>
    <button class="btn btn-sm fbk-tab" onclick="switchFeedbackTab('completed', this)">Completed</button>
  </div>

  <!-- TABLE CARD -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Feedback Cycles</span>
      <div class="search-inner" style="width: 280px;">
        <i data-lucide="search" size="14"></i>
        <input type="text" placeholder="Search employee..." oninput="filterFeedbackTable(this.value)">
      </div>
    </div>
    <div id="feedback-table-container" style="min-height:200px;">
      <div class="vault-loading-pro" id="feedback-loader" style="display:flex; justify-content:center; padding:40px;">
        <div class="loader-ring"></div>
      </div>
    </div>
    <div class="pagination" id="feedback-pagination" style="display:none;"></div>
  </div>
</div>

<!-- SEND FEEDBACK MODAL (select employee/cycle) -->
<div class="modal-overlay" id="modal-send-feedback" onclick="closeModal('modal-send-feedback', event)">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-header">
      <div><div style="font-size:1rem; font-weight:800;">Send 360° Feedback Form</div></div>
      <button class="icon-btn" onclick="closeModal('modal-send-feedback')"><i data-lucide="x" size="16"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Employee *</label><select id="fbk-employee" class="sel"><option value="">Select...</option><option value="John Anderson">John Anderson</option><option value="Sarah Johnson">Sarah Johnson</option></select></div>
      <div class="form-group mt-3"><label>Cycle</label><select id="fbk-cycle" class="sel"><option>Q1 2026</option></select></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-send-feedback')">Cancel</button>
      <button class="btn btn-primary" onclick="submitSendFeedback()">Open Form</button>
    </div>
  </div>
</div>

<!-- FEEDBACK RESPONSE MODAL -->
<div class="modal-overlay" id="modal-feedback-response" onclick="closeModal('modal-feedback-response', event)">
  <div class="modal-box" style="max-width:600px;">
    <div class="modal-header">
      <div><div style="font-size:1rem; font-weight:800;">360° Feedback Submission</div></div>
      <button class="icon-btn" onclick="closeModal('modal-feedback-response')"><i data-lucide="x" size="16"></i></button>
    </div>
    <div class="modal-body">
      <div id="fbk-competencies"></div>
      <div class="form-group mt-3"><label>Strengths & Improvements</label><textarea id="fbk-strengths" class="form-ctrl" placeholder="Provide constructive feedback..."></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-feedback-response')">Discard</button>
      <button class="btn btn-primary" onclick="submitFeedbackResponse()">Submit Feedback</button>
    </div>
  </div>
</div>

<style>
.fbk-tab { background: transparent; border: none; color: var(--muted); font-weight: 600; border-bottom: 2px solid transparent; border-radius:0; }
.fbk-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
</style>

<script>
(function() {
  const feedbackData = [
    { id: 'FBK001', employee: 'John Anderson', cycle: 'Q1 2026', status: 'Active', responses: 7, requested: 10, completionRate: 70, dueDate: '2026-04-15', avgScore: 4.3 },
    { id: 'FBK002', employee: 'Sarah Johnson', cycle: 'Q1 2026', status: 'Active', responses: 9, requested: 10, completionRate: 90, dueDate: '2026-04-15', avgScore: 4.5 },
    { id: 'FBK003', employee: 'Michael Chen', cycle: 'Q1 2026', status: 'Active', responses: 6, requested: 8, completionRate: 75, dueDate: '2026-04-15', avgScore: 4.1 },
    { id: 'FBK004', employee: 'Emma Davis', cycle: 'Q4 2025', status: 'Completed', responses: 8, requested: 8, completionRate: 100, dueDate: '2026-01-31', avgScore: 3.9 },
    { id: 'FBK005', employee: 'Robert Wilson', cycle: 'Q4 2025', status: 'Completed', responses: 7, requested: 8, completionRate: 87, dueDate: '2026-01-31', avgScore: 4.2 },
    { id: 'FBK006', employee: 'Lisa Martinez', cycle: 'Q1 2026', status: 'Active', responses: 4, requested: 8, completionRate: 50, dueDate: '2026-04-15', avgScore: 3.8 },
  ];

  const competencies = [
    { name: 'Technical Expertise' }, { name: 'Leadership & Vision' }, { name: 'Communication Skills' }, { name: 'Teamwork & Collaboration' }, { name: 'Initiative & Innovation' }
  ];

  let currentFilter = 'active';
  let activeCycleId = null; // for response modal

  function updateStats() {
    const active = feedbackData.filter(f => f.status === 'Active');
    const all = feedbackData;
    const totResponses = all.reduce((a,b) => a + b.responses, 0);
    const totRequested = all.reduce((a,b) => a + b.requested, 0);
    const avgComp = active.length ? Math.round(active.reduce((a,b) => a + b.completionRate, 0) / active.length) : 0;
    const avgScore = (active.reduce((a,b) => a + b.avgScore, 0) / active.length || 0).toFixed(1);
    document.getElementById('stat-responses').textContent = totResponses;
    document.getElementById('stat-completion').textContent = avgComp + '%';
    document.getElementById('stat-avgscore').textContent = avgScore + '/5';
  }

  window.switchFeedbackTab = function(tab, btn) {
    document.querySelectorAll('.fbk-tab').forEach(b => { b.classList.remove('active'); b.className = 'btn btn-sm fbk-tab'; });
    btn.classList.add('active');
    currentFilter = tab;
    renderFeedbackTable();
  };

  function filterData() {
    let data = [...feedbackData];
    if (currentFilter === 'active') data = data.filter(d => d.status === 'Active');
    else if (currentFilter === 'pending') data = data.filter(d => d.status === 'Active' && d.completionRate < 100);
    else if (currentFilter === 'completed') data = data.filter(d => d.status === 'Completed');
    const q = (document.getElementById('feedback-search')?.value || '').toLowerCase();
    if (q) data = data.filter(d => d.employee.toLowerCase().includes(q));
    return data;
  }

  window.renderFeedbackTable = function() {
    const container = document.getElementById('feedback-table-container');
    const data = filterData();
    let html = `<table class="tbl">
      <thead><tr><th>Employee</th><th>Cycle</th><th>Responses</th><th>Completion</th><th>Avg Score</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead><tbody>`;
    data.forEach(row => {
      html += `<tr>
        <td><strong>${row.employee}</strong></td>
        <td>${row.cycle}</td>
        <td>${row.responses} / ${row.requested}</td>
        <td><div style="display:flex;align-items:center;gap:8px;"><div style="flex:1;height:6px;background:var(--border);border-radius:99px;overflow:hidden;"><div style="width:${row.completionRate}%;height:100%;background:${row.completionRate >= 80 ? '#16a34a' : '#f59e0b'};border-radius:99px;"></div></div><span style="font-size:0.8rem;">${row.completionRate}%</span></div></td>
        <td><span style="color:#f59e0b; font-weight:600;">⭐ ${row.avgScore}</span></td>
        <td>${row.dueDate}</td>
        <td><span class="badge ${row.status==='Completed'?'badge-success':'badge-info'}">${row.status}</span></td>
        <td>
          <button class="btn btn-xs btn-secondary" onclick="openFeedbackForm('${row.id}')" title="Submit Feedback"><i data-lucide="send" size="14"></i></button>
          <button class="btn btn-xs btn-secondary" onclick="downloadFeedbackReport('${row.id}')" title="Download"><i data-lucide="download" size="14"></i></button>
        </td>
      </tr>`;
    });
    html += `</tbody></table>`;
    container.innerHTML = html;
    lcIcons(container);
    document.getElementById('feedback-pagination').innerHTML = `<span class="pagination-info">${data.length} records</span>`;
    document.getElementById('feedback-pagination').style.display = 'flex';
    updateStats();
  };

  window.filterFeedbackTable = function(val) {
    let el = document.getElementById('feedback-search');
    if (!el) { el = document.createElement('input'); el.type='hidden'; el.id='feedback-search'; document.body.appendChild(el); }
    el.value = val;
    renderFeedbackTable();
  };

  // ── Start new feedback cycle ──
  window.openStartFeedbackModal = () => {
    document.getElementById('modal-send-feedback').classList.add('open');
  };

  window.submitSendFeedback = () => {
    const emp = document.getElementById('fbk-employee');
    const cycle = document.getElementById('fbk-cycle');
    if (!emp.value) { showNotification('Required','Select an employee','warning'); return; }
    // Find or create active cycle for that employee
    let cycleObj = feedbackData.find(f => f.employee === emp.value && f.status === 'Active');
    if (!cycleObj) {
      const newId = 'FBK' + String(feedbackData.length + 1).padStart(3, '0');
      cycleObj = { id: newId, employee: emp.value, cycle: cycle.value, status: 'Active', responses: 0, requested: 8, completionRate: 0, dueDate: '2026-05-01', avgScore: 0 };
      feedbackData.push(cycleObj);
    }
    activeCycleId = cycleObj.id;
    // prepare response modal
    const compDiv = document.getElementById('fbk-competencies');
    compDiv.innerHTML = competencies.map((c,i) => `
      <div class="flex-row" style="justify-content:space-between; align-items:center; padding:8px; background:#f8fafc; border-radius:8px; margin-bottom:6px;">
        <span style="font-weight:600; font-size:0.85rem;">${c.name}</span>
        <div class="rating-stars" id="stars-${i}">
          ${[1,2,3,4,5].map(n => `<span class="rating-star" onclick="setFeedbackRating(${i}, ${n})" style="font-size:1.2rem; cursor:pointer;">☆</span>`).join('')}
        </div>
      </div>`).join('');
    document.getElementById('fbk-strengths').value = '';
    closeModal('modal-send-feedback');
    document.getElementById('modal-feedback-response').classList.add('open');
    lcIcons(compDiv);
  };

  // also called from table row button
  window.openFeedbackForm = (id) => {
    const cycle = feedbackData.find(f => f.id === id);
    if (!cycle) return;
    activeCycleId = id;
    document.getElementById('fbk-employee').value = cycle.employee; // not really used
    document.getElementById('fbk-cycle').value = cycle.cycle;
    // response modal same as above
    const compDiv = document.getElementById('fbk-competencies');
    compDiv.innerHTML = competencies.map((c,i) => `
      <div class="flex-row" style="justify-content:space-between; align-items:center; padding:8px; background:#f8fafc; border-radius:8px; margin-bottom:6px;">
        <span style="font-weight:600; font-size:0.85rem;">${c.name}</span>
        <div class="rating-stars" id="stars-${i}">
          ${[1,2,3,4,5].map(n => `<span class="rating-star" onclick="setFeedbackRating(${i}, ${n})" style="font-size:1.2rem; cursor:pointer;">☆</span>`).join('')}
        </div>
      </div>`).join('');
    document.getElementById('fbk-strengths').value = '';
    document.getElementById('modal-feedback-response').classList.add('open');
    lcIcons(compDiv);
  };

  window.setFeedbackRating = (idx, val) => {
    const stars = document.getElementById(`stars-${idx}`).querySelectorAll('.rating-star');
    stars.forEach((s,i) => s.textContent = i < val ? '★' : '☆');
  };

  window.submitFeedbackResponse = () => {
    const cycle = feedbackData.find(f => f.id === activeCycleId);
    if (!cycle) return;
    // collect ratings
    let total = 0, count = 0;
    document.querySelectorAll('#fbk-competencies .rating-stars').forEach(starsDiv => {
      const stars = starsDiv.querySelectorAll('.rating-star');
      stars.forEach((s,i) => { if (s.textContent === '★') total += (i+1); count++; });
    });
    const avg = count ? Math.round(total / count * 10) / 10 : 0;
    cycle.responses += 1;
    cycle.completionRate = Math.round((cycle.responses / cycle.requested) * 100);
    cycle.avgScore = avg;
    if (cycle.responses >= cycle.requested) cycle.status = 'Completed';
    closeModal('modal-feedback-response');
    showNotification('Submitted', 'Your feedback has been recorded anonymously.', 'success');
    renderFeedbackTable();
  };

  window.downloadFeedbackReport = (id) => showNotification('Success', `Feedback report for ${id} downloaded.`, 'success');

  setTimeout(() => {
    document.getElementById('feedback-loader').style.display = 'none';
    renderFeedbackTable();
  }, 600);
})();
</script>