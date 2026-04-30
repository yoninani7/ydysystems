<!-- FILE: pages/performance/performance_reviews.php -->
<div class="page" id="p-performance-reviews">
  <div class="page-header">
    <div>
      <div class="page-title">Performance Reviews</div>
      <div class="page-sub">Q1 2026 review cycle — Track and manage employee evaluations</div>
    </div>
    <button class="btn btn-primary" onclick="openStartReviewModal()"><i data-lucide="plus" size="13"></i>Start Review</button>
  </div>

  <!-- STATS -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card"><div class="stat-label">Total Reviews</div><div class="stat-value" style="color: #0891b2;">42</div><div class="stat-sub">Active cycle</div></div>
    <div class="stat-card"><div class="stat-label">Completed</div><div class="stat-value" style="color: #16a34a;">28</div><div class="stat-sub">67% complete</div></div>
    <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value" style="color: #d97706;">14</div><div class="stat-sub">Awaiting submission</div></div>
    <div class="stat-card"><div class="stat-label">Avg Rating</div><div class="stat-value" style="color: #15b201;">4.2/5</div><div class="stat-sub">Overall performance</div></div>
  </div>

  <!-- TABS -->
  <div class="flex-row mb-4" style="gap:8px; border-bottom:1px solid var(--border);">
    <button class="btn btn-sm rev-tab active" onclick="switchReviewTab('all', this)">All Reviews</button>
    <button class="btn btn-sm rev-tab" onclick="switchReviewTab('pending', this)">Pending</button>
    <button class="btn btn-sm rev-tab" onclick="switchReviewTab('completed', this)">Completed</button>
    <button class="btn btn-sm rev-tab" onclick="switchReviewTab('overdue', this)">Overdue</button>
  </div>

  <!-- TABLE CARD -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Review Records</span>
      <div class="search-inner" style="width: 280px;">
        <i data-lucide="search" size="14"></i>
        <input type="text" placeholder="Search employee..." oninput="filterReviewsTable(this.value)">
      </div>
    </div>
    <div id="reviews-table-container" style="min-height:200px;">
      <div class="vault-loading-pro" id="reviews-loader" style="display:flex; justify-content:center; padding:40px;">
        <div class="loader-ring"></div>
      </div>
    </div>
    <div class="pagination" id="reviews-pagination" style="display:none;"></div>
  </div>
</div>

<!-- MODAL: Start New Review -->
<div class="modal-overlay" id="modal-start-review" onclick="closeModal('modal-start-review', event)">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-header">
      <div>
        <div style="font-size:1rem; font-weight:800;">Start New Performance Review</div>
        <div style="font-size:.75rem; color:var(--muted);">Select employee and review type</div>
      </div>
      <button class="icon-btn" onclick="closeModal('modal-start-review')"><i data-lucide="x" size="16"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Employee *</label><select id="rev-employee" class="sel"><option value="">Select...</option><option value="EMP001">John Anderson</option><option value="EMP002">Sarah Johnson</option></select></div>
      <div class="form-group mt-3"><label>Review Type</label><select id="rev-type" class="sel"><option value="annual">Annual Review</option><option value="mid-year">Mid-Year Check-in</option><option value="probation">Probation Review</option><option value="project">Project-based Review</option></select></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-start-review')">Cancel</button>
      <button class="btn btn-primary" onclick="submitStartReview()">Start Review</button>
    </div>
  </div>
</div>

<!-- MODAL: View/Edit Review Detail -->
<div class="modal-overlay" id="modal-review-detail" onclick="closeModal('modal-review-detail', event)">
  <div class="modal-box" style="max-width:700px;">
    <div class="modal-header">
      <div class="page-title" id="modal-review-title" style="font-size:1rem;">Performance Review</div>
      <button class="icon-btn" onclick="closeModal('modal-review-detail')"><i data-lucide="x" size="16"></i></button>
    </div>
    <div id="review-modal-content" class="modal-body">
      <!-- dynamically filled -->
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-review-detail')">Close</button>
      <button class="btn btn-primary" id="btn-save-review" style="display:none;" onclick="submitReviewDetail()">Save & Submit</button>
    </div>
  </div>
</div>

<style>
.rev-tab { background: transparent; border: none; color: var(--muted); font-weight: 600; border-bottom: 2px solid transparent; border-radius:0; }
.rev-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
</style>

<script>
(function() {
  // mutable data store
  const reviewsData = [
    { id: 'REV001', employee: 'John Anderson', dept: 'Sales', reviewer: 'Michael Brown', type: 'Annual', rating: 4.5, status: 'Completed', dueDate: '2026-03-15', submittedDate: '2026-03-10', comments: 'Excellent sales performance and team leadership.' },
    { id: 'REV002', employee: 'Sarah Johnson', dept: 'Engineering', reviewer: 'David Kumar', type: 'Annual', rating: 4.7, status: 'Completed', dueDate: '2026-03-15', submittedDate: '2026-03-12', comments: 'Outstanding technical contributions.' },
    { id: 'REV003', employee: 'Michael Chen', dept: 'HR', reviewer: 'Jennifer Lee', type: 'Mid-Year', rating: 4.0, status: 'Completed', dueDate: '2026-03-10', submittedDate: '2026-03-08', comments: 'Good communication skills.' },
    { id: 'REV004', employee: 'Emma Davis', dept: 'Marketing', reviewer: 'Robert Wilson', type: 'Annual', rating: 0, status: 'Pending', dueDate: '2026-03-20', submittedDate: null, comments: '' },
    { id: 'REV005', employee: 'Robert Wilson', dept: 'Finance', reviewer: 'Angela Thompson', type: 'Annual', rating: 4.2, status: 'Completed', dueDate: '2026-03-15', submittedDate: '2026-03-14', comments: 'Strong analytical skills.' },
    { id: 'REV006', employee: 'Lisa Martinez', dept: 'Operations', reviewer: 'James Peterson', type: 'Probation', rating: 0, status: 'Pending', dueDate: '2026-03-05', submittedDate: null, comments: '' },
    { id: 'REV007', employee: 'Chris Thompson', dept: 'IT', reviewer: 'Mark Johnson', type: 'Annual', rating: 4.3, status: 'Completed', dueDate: '2026-03-18', submittedDate: '2026-03-17', comments: 'Good technical expertise.' },
    { id: 'REV008', employee: 'Patricia Johnson', dept: 'Sales', reviewer: 'Michael Brown', type: 'Annual', rating: 3.6, status: 'Overdue', dueDate: '2026-02-28', submittedDate: null, comments: 'Follow up required.' },
  ];

  let currentFilter = 'all';
  let currentReviewId = null; // for detail modal editing

  window.switchReviewTab = function(tabName, btn) {
    document.querySelectorAll('.rev-tab').forEach(b => { b.classList.remove('active'); b.className = 'btn btn-sm rev-tab'; });
    btn.classList.add('active');
    currentFilter = tabName;
    renderReviewsTable();
  };

  function filterData() {
    let data = [...reviewsData];
    if (currentFilter === 'pending') data = data.filter(r => r.status === 'Pending');
    else if (currentFilter === 'completed') data = data.filter(r => r.status === 'Completed');
    else if (currentFilter === 'overdue') data = data.filter(r => r.status === 'Overdue');
    const searchTerm = (document.getElementById('reviews-search')?.value || '').toLowerCase();
    if (searchTerm) data = data.filter(r => r.employee.toLowerCase().includes(searchTerm) || r.dept.toLowerCase().includes(searchTerm));
    return data;
  }

  window.renderReviewsTable = function() {
    const container = document.getElementById('reviews-table-container');
    const data = filterData();
    const now = new Date();

    let html = `<table class="tbl">
      <thead><tr>
        <th>Employee</th><th>Department</th><th>Review Type</th><th>Reviewer</th><th>Rating</th><th>Status</th><th>Due Date</th><th>Actions</th>
      </tr></thead><tbody>`;

    data.forEach(row => {
      const statusColor = row.status === 'Completed' ? '#16a34a' : (row.status === 'Pending' ? '#d97706' : '#dc2626');
      const statusBg = row.status === 'Completed' ? '#dcfce7' : (row.status === 'Pending' ? '#fef3c7' : '#fee2e2');
      const stars = row.rating > 0 ? '★'.repeat(Math.floor(row.rating)) + '☆'.repeat(5 - Math.floor(row.rating)) : '☆☆☆☆☆';
      const due = new Date(row.dueDate);
      const isOverdue = row.status !== 'Completed' && due < now;

      html += `<tr>
        <td><strong>${row.employee}</strong></td>
        <td>${row.dept}</td>
        <td><span class="badge badge-neutral">${row.type}</span></td>
        <td>${row.reviewer}</td>
        <td><span style="color:#f59e0b; font-weight:600;">${stars} ${row.rating || '—'}</span></td>
        <td><span style="background:${statusBg}; color:${statusColor}; padding:3px 10px; border-radius:6px; font-size:0.8rem;">${row.status}</span></td>
        <td>${isOverdue ? '<span style="color:#dc2626;">⚠ ' + row.dueDate + '</span>' : row.dueDate}</td>
        <td>
          <button class="btn btn-xs btn-secondary" onclick="openReviewDetail('${row.id}')"><i data-lucide="eye" size="14"></i></button>
          <button class="btn btn-xs btn-secondary" onclick="downloadReviewPDF('${row.id}')"><i data-lucide="download" size="14"></i></button>
        </td>
      </tr>`;
    });

    html += `</tbody></table>`;
    container.innerHTML = html;
    lcIcons(container);

    document.getElementById('reviews-pagination').innerHTML = `<span class="pagination-info">Showing ${data.length} reviews</span>`;
    document.getElementById('reviews-pagination').style.display = 'flex';
  };

  window.filterReviewsTable = function(val) {
    let el = document.getElementById('reviews-search');
    if (!el) { el = document.createElement('input'); el.type='hidden'; el.id='reviews-search'; document.body.appendChild(el); }
    el.value = val;
    renderReviewsTable();
  };

  // ── MODAL: Start New Review ──
  window.openStartReviewModal = () => {
    document.getElementById('modal-start-review').classList.add('open');
  };

  window.submitStartReview = () => {
    const emp = document.getElementById('rev-employee');
    const type = document.getElementById('rev-type');
    if (!emp.value) { showNotification('Required','Please select an employee.','warning'); return; }

    const newId = 'REV' + String(reviewsData.length + 1).padStart(3, '0');
    const now = new Date();
    const dueDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 14).toISOString().split('T')[0];
    const initial = { id: newId, employee: emp.options[emp.selectedIndex].text, dept: 'Various', reviewer: 'Assigned HR', type: type.value, rating: 0, status: 'Pending', dueDate: dueDate, submittedDate: null, comments: '' };
    reviewsData.push(initial);
    closeModal('modal-start-review');
    showNotification('Review Started', `New review for ${initial.employee} has been created.`, 'success');
    renderReviewsTable();
  };

  // ── MODAL: View / Edit Review Detail ──
  window.openReviewDetail = (reviewId) => {
    const review = reviewsData.find(r => r.id === reviewId);
    if (!review) return;
    currentReviewId = reviewId;
    document.getElementById('modal-review-title').textContent = `Review: ${review.employee}`;
    const content = document.getElementById('review-modal-content');

    const readOnly = review.status === 'Completed' || review.status === 'Overdue';
    const starsHtml = [1,2,3,4,5].map(i => {
      const filled = i <= Math.floor(review.rating) ? '★' : '☆';
      const editable = !readOnly ? `onclick="setReviewRating(${i})" style="cursor:pointer;"` : '';
      return `<span ${editable} id="review-star-${i}" style="font-size:1.5rem; color:${i <= Math.floor(review.rating) ? '#f59e0b' : '#cbd5e1'};">${filled}</span>`;
    }).join('');

    content.innerHTML = `
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div class="info-tile"><label class="tile-label">Employee</label><div class="tile-val">${review.employee}</div></div>
        <div class="info-tile"><label class="tile-label">Review Type</label><div class="tile-val">${review.type}</div></div>
        <div class="info-tile"><label class="tile-label">Reviewer</label><div class="tile-val">${review.reviewer}</div></div>
        <div class="info-tile"><label class="tile-label">Status</label><div class="tile-val">${review.status}</div></div>
        <div class="info-tile" style="grid-column:span 2;">
          <label class="tile-label">Overall Rating</label>
          <div class="flex-row" style="gap:4px;">${starsHtml}</div>
        </div>
        <div class="info-tile" style="grid-column:span 2;">
          <label class="tile-label">Comments</label>
          <textarea id="edit-review-comments" class="form-ctrl" ${readOnly ? 'disabled' : ''}>${review.comments}</textarea>
        </div>
      </div>`;

    const saveBtn = document.getElementById('btn-save-review');
    if (!readOnly) {
      saveBtn.style.display = 'inline-flex';
    } else {
      saveBtn.style.display = 'none';
    }

    document.getElementById('modal-review-detail').classList.add('open');
    lcIcons(content);
  };

  window.setReviewRating = (val) => {
    const review = reviewsData.find(r => r.id === currentReviewId);
    if (!review) return;
    review.rating = val;
    for (let i = 1; i <= 5; i++) {
      const star = document.getElementById('review-star-' + i);
      star.textContent = i <= val ? '★' : '☆';
      star.style.color = i <= val ? '#f59e0b' : '#cbd5e1';
    }
  };

  window.submitReviewDetail = () => {
    const review = reviewsData.find(r => r.id === currentReviewId);
    if (!review) return;
    review.comments = document.getElementById('edit-review-comments').value;
    review.status = 'Completed';
    review.submittedDate = new Date().toISOString().split('T')[0];
    closeModal('modal-review-detail');
    showNotification('Review Submitted', `Review for ${review.employee} has been completed.`, 'success');
    renderReviewsTable();
  };

  window.downloadReviewPDF = (reviewId) => {
    showNotification('PDF Exported', `Report generated for review ${reviewId}`, 'success');
  };

  // simulate initial load
  setTimeout(() => {
    document.getElementById('reviews-loader').style.display = 'none';
    renderReviewsTable();
  }, 600);
})();
</script>