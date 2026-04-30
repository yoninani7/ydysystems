<!-- FILE: pages/performance/goals_development.php -->
<div class="page" id="p-goals-development">
  <div class="page-header">
    <div>
      <div class="page-title">Goals & Development</div>
      <div class="page-sub">Set, track, and achieve career development goals</div>
    </div>
    <button class="btn btn-primary" onclick="openCreateGoalModal()"><i data-lucide="plus" size="13"></i>Create Goal</button>
  </div>

  <!-- STATS -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card"><div class="stat-label">Total Goals</div><div class="stat-value" style="color:#0891b2;" id="stat-total-goals">24</div><div class="stat-sub">Active goals</div></div>
    <div class="stat-card"><div class="stat-label">On Track</div><div class="stat-value" style="color:#16a34a;" id="stat-on-track">18</div><div class="stat-sub">75% completion</div></div>
    <div class="stat-card"><div class="stat-label">At Risk</div><div class="stat-value" style="color:#d97706;" id="stat-at-risk">4</div><div class="stat-sub">Needs attention</div></div>
    <div class="stat-card"><div class="stat-label">Completed</div><div class="stat-value" style="color:#15b201;" id="stat-completed">38</div><div class="stat-sub">This year</div></div>
  </div>

  <!-- TABS -->
  <div class="flex-row mb-4" style="gap:8px; border-bottom:1px solid var(--border);">
    <button class="btn btn-sm goal-tab active" onclick="switchGoalTab('active', this)">Active Goals</button>
    <button class="btn btn-sm goal-tab" onclick="switchGoalTab('at-risk', this)">At Risk</button>
    <button class="btn btn-sm goal-tab" onclick="switchGoalTab('completed', this)">Completed</button>
  </div>

  <!-- GOALS LIST CARD -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Development Goals</span>
      <div class="search-inner" style="width: 260px;">
        <i data-lucide="search" size="14"></i>
        <input type="text" placeholder="Search goals..." oninput="filterGoals(this.value)">
      </div>
    </div>
    <div id="goals-container" style="min-height:200px; padding:16px;">
      <div class="vault-loading-pro" id="goals-loader" style="display:flex; justify-content:center; padding:40px;">
        <div class="loader-ring"></div>
      </div>
    </div>
  </div>
</div>

<!-- CREATE GOAL MODAL -->
<div class="modal-overlay" id="modal-create-goal" onclick="closeModal('modal-create-goal', event)">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-header">
      <div><div style="font-size:1rem; font-weight:800;">Create Development Goal</div></div>
      <button class="icon-btn" onclick="closeModal('modal-create-goal')"><i data-lucide="x" size="16"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Goal Title *</label><input id="goal-title" class="form-ctrl"></div>
      <div class="form-grid fg-2 mt-3">
        <div class="form-group"><label>Category</label><select id="goal-category" class="sel"><option value="technical">Technical</option><option value="soft">Soft Skills</option><option value="certification">Certification</option><option value="project">Project</option></select></div>
        <div class="form-group"><label>Target Date</label><input type="date" id="goal-target-date" class="form-ctrl"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-create-goal')">Cancel</button>
      <button class="btn btn-primary" onclick="submitCreateGoal()">Create Goal</button>
    </div>
  </div>
</div>

<!-- EDIT GOAL MODAL -->
<div class="modal-overlay" id="modal-edit-goal" onclick="closeModal('modal-edit-goal', event)">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-header">
      <div><div style="font-size:1rem; font-weight:800;">Edit Development Goal</div></div>
      <button class="icon-btn" onclick="closeModal('modal-edit-goal')"><i data-lucide="x" size="16"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit-goal-id">
      <div class="form-group"><label>Goal Title</label><input id="edit-goal-title" class="form-ctrl" disabled></div>
      <div class="form-group mt-3"><label>Progress (%)</label><input type="range" id="edit-goal-progress" min="0" max="100" value="0" oninput="document.getElementById('edit-progress-val').textContent = this.value + '%'"><span id="edit-progress-val" style="font-weight:700;">0%</span></div>
      <div class="form-group mt-3"><label>Status</label><select id="edit-goal-status" class="sel"><option value="on-track">On Track</option><option value="at-risk">At Risk</option><option value="completed">Completed</option></select></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-edit-goal')">Cancel</button>
      <button class="btn btn-primary" onclick="submitEditGoal()">Save Changes</button>
    </div>
  </div>
</div>

<style>
.goal-tab { background: transparent; border: none; color: var(--muted); font-weight: 600; border-bottom: 2px solid transparent; border-radius:0; }
.goal-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
.goal-card { background: var(--surface); border: 1px solid var(--border); border-radius:12px; padding:16px; margin-bottom:12px; cursor: pointer; transition: all .2s; }
.goal-card:hover { border-color: var(--primary); box-shadow: var(--shadow-md); }
</style>

<script>
(function() {
  const goalsData = [
    { id: 'GOAL001', title: 'Complete AWS Solutions Architect Certification', category: 'certification', status: 'on-track', progress: 65, targetDate: '2026-06-30' },
    { id: 'GOAL002', title: 'Lead Cross-functional Project', category: 'project', status: 'on-track', progress: 45, targetDate: '2026-05-31' },
    { id: 'GOAL003', title: 'Improve Public Speaking Skills', category: 'soft', status: 'at-risk', progress: 30, targetDate: '2026-04-30' },
    { id: 'GOAL004', title: 'Develop Advanced SQL Skills', category: 'technical', status: 'on-track', progress: 80, targetDate: '2026-03-31' },
    { id: 'GOAL005', title: 'Mentor Junior Developer', category: 'leadership', status: 'on-track', progress: 55, targetDate: '2026-12-31' },
    { id: 'GOAL006', title: 'Improve Time Management', category: 'soft', status: 'at-risk', progress: 40, targetDate: '2026-04-30' },
  ];

  let currentFilter = 'active';

  function updateStats() {
    const all = goalsData;
    const onTrack = all.filter(g => g.status === 'on-track').length;
    const atRisk = all.filter(g => g.status === 'at-risk').length;
    const completed = all.filter(g => g.status === 'completed').length;
    document.getElementById('stat-total-goals').textContent = all.length;
    document.getElementById('stat-on-track').textContent = onTrack;
    document.getElementById('stat-at-risk').textContent = atRisk;
    document.getElementById('stat-completed').textContent = completed;
  }

  window.switchGoalTab = function(tab, btn) {
    document.querySelectorAll('.goal-tab').forEach(b => { b.classList.remove('active'); b.className = 'btn btn-sm goal-tab'; });
    btn.classList.add('active');
    currentFilter = tab;
    renderGoals();
  };

  function filterData() {
    let data = [...goalsData];
    if (currentFilter === 'active') data = data.filter(g => g.status !== 'completed');
    else if (currentFilter === 'at-risk') data = data.filter(g => g.status === 'at-risk');
    else if (currentFilter === 'completed') data = data.filter(g => g.status === 'completed');
    const q = (document.getElementById('goal-search')?.value || '').toLowerCase();
    if (q) data = data.filter(g => g.title.toLowerCase().includes(q));
    return data;
  }

  window.renderGoals = function() {
    const container = document.getElementById('goals-container');
    const data = filterData();
    container.innerHTML = data.length ? data.map(goal => {
      const catColors = { technical: '#166534', certification: '#6d28d9', soft: '#b45309', project: '#0369a1', leadership: '#1e40af' };
      const color = catColors[goal.category] || '#166534';
      const daysLeft = Math.floor((new Date(goal.targetDate) - new Date()) / (1000*60*60*24));
      return `
        <div class="goal-card" onclick="openEditGoalModal('${goal.id}')">
          <div class="flex-row" style="justify-content:space-between;">
            <div style="font-weight:800;">${goal.title}</div>
            <span class="badge" style="background:${color}20; color:${color};">${goal.category}</span>
          </div>
          <div class="mt-2">
            <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--muted);"><span>Progress</span><span>${goal.progress}%</span></div>
            <div style="height:6px;background:var(--border);border-radius:99px;overflow:hidden;margin-top:4px;"><div style="width:${goal.progress}%;height:100%;background:var(--primary);border-radius:99px;"></div></div>
          </div>
          <div class="flex-row mt-2" style="gap:12px; font-size:0.7rem; color:var(--muted);">
            <span><i data-lucide="calendar" size="12"></i> ${daysLeft} days left</span>
            <span><i data-lucide="target" size="12"></i> ${goal.targetDate}</span>
            <span class="badge ${goal.status==='at-risk'?'badge-warning':'badge-info'}">${goal.status}</span>
          </div>
        </div>`;
    }).join('') : '<div style="padding:40px;text-align:center;color:var(--muted);">No goals found.</div>';
    lcIcons(container);
    updateStats();
  };

  window.filterGoals = function(val) {
    let el = document.getElementById('goal-search');
    if (!el) { el = document.createElement('input'); el.type='hidden'; el.id='goal-search'; document.body.appendChild(el); }
    el.value = val;
    renderGoals();
  };

  // ── CREATE GOAL ──
  window.openCreateGoalModal = () => {
    document.getElementById('modal-create-goal').classList.add('open');
  };

  window.submitCreateGoal = () => {
    const title = document.getElementById('goal-title');
    const cat = document.getElementById('goal-category');
    const date = document.getElementById('goal-target-date');
    if (!title.value) { showNotification('Required','Enter a goal title','warning'); return; }
    const newId = 'GOAL' + String(goalsData.length + 1).padStart(3, '0');
    goalsData.push({
      id: newId,
      title: title.value,
      category: cat.value,
      status: 'on-track',
      progress: 0,
      targetDate: date.value || '2026-12-31'
    });
    closeModal('modal-create-goal');
    showNotification('Success','Development goal created.','success');
    renderGoals();
  };

  // ── EDIT GOAL (click card) ──
  window.openEditGoalModal = (id) => {
    const goal = goalsData.find(g => g.id === id);
    if (!goal) return;
    document.getElementById('edit-goal-id').value = goal.id;
    document.getElementById('edit-goal-title').value = goal.title;
    document.getElementById('edit-goal-progress').value = goal.progress;
    document.getElementById('edit-progress-val').textContent = goal.progress + '%';
    document.getElementById('edit-goal-status').value = goal.status;
    document.getElementById('modal-edit-goal').classList.add('open');
    lcIcons(document.getElementById('modal-edit-goal'));
  };

  window.submitEditGoal = () => {
    const id = document.getElementById('edit-goal-id').value;
    const goal = goalsData.find(g => g.id === id);
    if (!goal) return;
    goal.progress = parseInt(document.getElementById('edit-goal-progress').value);
    goal.status = document.getElementById('edit-goal-status').value;
    closeModal('modal-edit-goal');
    showNotification('Goal Updated', `Progress for "${goal.title}" has been saved.`, 'success');
    renderGoals();
  };

  setTimeout(() => {
    document.getElementById('goals-loader').style.display = 'none';
    renderGoals();
  }, 500);
})();
</script>