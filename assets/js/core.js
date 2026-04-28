// ── ICON HELPER: targeted scan when possible, full scan as fallback ──
const lcIcons = (el) => el ? lucide.createIcons({ nodes: [el] }) : lucide.createIcons();
// ── VALIDATION HELPERS ──────────────────────────────────────────────────
// ─────────────────────────────────────────────────────────────────────────────
// FIELD VALIDATION RULES (mirror server‑side add_employee.php)
// ─────────────────────────────────────────────────────────────────────────────

const VALIDATORS = {
  'o-dob': (val) => {
    if (!val) return { valid: false, error: 'Date of Birth is required.' };
    const dob = new Date(val);
    if (isNaN(dob.getTime())) return { valid: false, error: 'Invalid date format.' };
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    if (age < 16) return { valid: false, error: 'Employee must be at least 16 years old.' };
    if (age > 100) return { valid: false, error: 'Age cannot exceed 100 years.' };
    return { valid: true };
  },
  'o-email': (val) => {
    if (!val) return { valid: true };
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(val) ? { valid: true } : { valid: false, error: 'Invalid email address.' };
  },
  'o-sal': (val) => {
    if (!val) return { valid: true };
    const num = parseFloat(val);
    return (!isNaN(num) && num > 0) ? { valid: true } : { valid: false, error: 'Salary must be a positive number.' };
  },
  'o-end-date': (val) => {
    if (!val) return { valid: true };
    const endDate = new Date(val);
    if (isNaN(endDate.getTime())) return { valid: false, error: 'Invalid end date.' };
    const hireField = document.getElementById('o-hire');
    if (hireField && hireField.value) {
      const hireDate = new Date(hireField.value);
      if (endDate <= hireDate) return { valid: false, error: 'End date must be after start date.' };
    }
    return { valid: true };
  }
};

// Helper to validate a single field by ID
function validateField(fieldId) {
  const field = document.getElementById(fieldId);
  if (!field) return { valid: true };
  const validator = VALIDATORS[fieldId];
  if (!validator) return { valid: true };
  const result = validator(field.value.trim());
  if (!result.valid) {
    field.classList.add('field-error');
  } else {
    field.classList.remove('field-error');
  }
  return result;
}

// ── SIDEBAR ──
let sidebarCollapsed = false;
const isMobile = () => window.innerWidth <= 768;
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');
const overlay = document.getElementById('sidebar-overlay');

function toggleSidebar() {
  if (isMobile()) {
    const isOpen = sidebar.classList.contains('mobile-open');
    if (isOpen) {
      sidebar.classList.remove('mobile-open');
      overlay.classList.remove('visible');
    } else {
      sidebar.classList.remove('collapsed');
      mainContent.classList.remove('sidebar-collapsed');
      sidebar.classList.add('mobile-open');
      overlay.classList.add('visible');
    }
  } else {
    sidebarCollapsed = !sidebarCollapsed;
    sidebar.classList.toggle('collapsed', sidebarCollapsed);
    mainContent.classList.toggle('sidebar-collapsed', sidebarCollapsed);
    const icon = document.getElementById('toggle-icon');
    icon.setAttribute('data-lucide', sidebarCollapsed ? 'panel-left-open' : 'menu');
    if (sidebarCollapsed) {
      document.querySelectorAll('.submenu').forEach(s => s.classList.remove('open'));
      document.querySelectorAll('.nav-trigger').forEach(t => t.classList.remove('open'));
    }
    lcIcons(document.getElementById('sidebar-toggle-btn'));
  }
}

function closeMobileSidebar() {
  sidebar.classList.remove('mobile-open');
  overlay.classList.remove('visible');
}

function syncSidebarWithPage(pageId) {
  const pageParentMap = {
    'add-employee': 'employee-directory',
    'employee-vault': 'document-vault'
  };

  const targetPage = pageParentMap[pageId] || pageId;

  const activeLink = document.querySelector(`.sub-link[onclick*="'${targetPage}'"], .dash-link[onclick*="'${targetPage}'"]`);
  if (!activeLink) return;

  document.querySelectorAll('.sub-link, .dash-link').forEach(l => l.classList.remove('active'));
  activeLink.classList.add('active');

  const submenu = activeLink.closest('.submenu');
  if (submenu) {
    submenu.classList.add('open');
    const trigger = submenu.closest('.nav-group')?.querySelector('.nav-trigger');
    if (trigger) trigger.classList.add('open');
  }
}

window.addEventListener('resize', () => {
  if (!isMobile()) {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('visible');
  }
}, { passive: true });

// ── COLLAPSED SIDEBAR FLYOUT (with delay fix) ──
let _flyoutTimer = null;
let _activeGroup = null;

function showFlyout(group) {
  clearTimeout(_flyoutTimer);
  if (_activeGroup && _activeGroup !== group) {
    _activeGroup.classList.remove('flyout-active');
  }

  const rect = group.getBoundingClientRect();
  const submenu = group.querySelector('.submenu');
  const label = group.querySelector('.nav-trigger-left span');

  if (submenu) submenu.style.top = rect.top + 'px';
  if (label) label.style.top = rect.top + 'px';

  group.classList.add('flyout-active');
  _activeGroup = group;
}

function hideFlyout() {
  _flyoutTimer = setTimeout(() => {
    if (_activeGroup) {
      _activeGroup.classList.remove('flyout-active');
      _activeGroup = null;
    }
  }, 400);
}

document.querySelectorAll('.nav-group').forEach(group => {
  group.addEventListener('mouseenter', () => {
    if (!sidebar.classList.contains('collapsed') || isMobile()) return;
    showFlyout(group);
  });
  group.addEventListener('mouseleave', () => {
    if (!sidebar.classList.contains('collapsed') || isMobile()) return;
    hideFlyout();
  });
});

// ── NAVIGATION ──
function toggleNav(el, id) {
  if (sidebarCollapsed && !isMobile()) { toggleSidebar(); return; }
  const sub = document.getElementById(id);
  const isOpen = sub.classList.contains('open');
  document.querySelectorAll('.nav-trigger').forEach(t => { if (t !== el) t.classList.remove('open'); });
  document.querySelectorAll('.submenu').forEach(s => { if (s !== sub) s.classList.remove('open'); });
  el.classList.toggle('open', !isOpen);
  sub.classList.toggle('open', !isOpen);
}

function goPage(id, el) {
  document.querySelectorAll('.sub-link, .dash-link').forEach(l => l.classList.remove('active'));
  if (el) el.classList.add('active');

  const url = new URL(window.location.href);
  url.searchParams.set('page', id);
  history.pushState({ page: id }, '', url.toString());

  const contentArea = document.getElementById('content-area');
  contentArea.style.opacity = '0.4';

  // Clear caches so the page refreshes completely
  inited.delete(id);
  inited.delete('dashboard-main-chart');
  inited.delete('analytics');

  fetch('dashboard.php?page=' + encodeURIComponent(id) + '&ajax=1')
    .then(r => r.text())
    .then(html => {
      contentArea.innerHTML = html;
      contentArea.style.opacity = '1';
      lcIcons(contentArea);
      syncSidebarWithPage(id);
      // Remove data-built to allow re‑initialisation
      contentArea.querySelectorAll('[data-built]').forEach(el => {
        delete el.dataset.built;
      });

      const titleEl = document.getElementById('page-title');
      if (titleEl) {
        let rawText = el ? el.textContent.trim() : id.replace(/-/g, ' ');
        const capitalizedText = rawText.charAt(0).toUpperCase() + rawText.slice(1);
        titleEl.textContent = capitalizedText;
      }

      contentArea.querySelectorAll('script').forEach(oldScript => {
        const newScript = document.createElement('script');
        [...oldScript.attributes].forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.textContent = oldScript.textContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
      });

      if (typeof initPage === 'function') initPage(id);
    })
    .catch(() => { contentArea.style.opacity = '1'; });
}

// Handle browser back/forward
window.addEventListener('popstate', (e) => {
  const page = (e.state && e.state.page) || 'dashboard';
  const link = document.querySelector(`.sub-link[onclick*="'${page}'"], .dash-link[onclick*="'${page}'"]`);
  goPage(page, link);
});

window.addEventListener('DOMContentLoaded', () => {
  lcIcons();

  const urlParams = new URLSearchParams(window.location.search);
  const currentPage = urlParams.get('page') || 'dashboard';

  document.querySelectorAll('.sub-link, .dash-link').forEach(l => l.classList.remove('active'));
  const activeLink = document.querySelector(`.sub-link[onclick*="'${currentPage}'"], .dash-link[onclick*="'${currentPage}'"]`);
  if (activeLink) activeLink.classList.add('active');

  const titleEl = document.getElementById('page-title');
  if (titleEl) {
    titleEl.textContent = activeLink ? activeLink.textContent.trim() : currentPage.replace(/-/g, ' ');
  }

  const contentArea = document.getElementById('content-area');
  if (contentArea) {
    const pageDiv = contentArea.querySelector('.page');
    if (pageDiv) pageDiv.classList.add('active');
  }

  if (typeof initPage === 'function') {
    initPage(currentPage);
  }
  setTimeout(() => syncSidebarWithPage(currentPage), 20);
});

// ── PAGINATED TABLE BUILDER ──
const b = (cls, txt) => `<span class="badge badge-${cls}">${txt}</span>`;
const statusBadge = {
  active: b('success', 'Active'),
  inactive: b('neutral', 'Inactive'),
  pending: b('warning', 'Pending'),
  approved: b('success', 'Approved'),
  rejected: b('danger', 'Rejected')
};

// ── ORG CHART ──
let ocZoom = 1.0;
function zoomOC(delta) { const n = ocZoom + delta; if (n >= 0.3 && n <= 1.5) { ocZoom = n; applyOCZoom(); } }
function resetOC() { ocZoom = 1.0; applyOCZoom(); const bp = document.getElementById('oc-blueprint-area'); if (bp) bp.scrollLeft = (bp.scrollWidth - bp.clientWidth) / 2; }
function applyOCZoom() { const c = document.getElementById('oc-zoom-container'), l = document.getElementById('oc-zoom-label'); if (c) c.style.transform = `scale(${ocZoom})`; if (l) l.textContent = Math.round(ocZoom * 100) + '%'; }
function initOrgChart() {
  applyOCZoom();
  initOrgChartDrag();
}
function initOrgChartDrag() {
  const slider = document.getElementById('oc-blueprint-area');
  if (!slider || slider.dataset.drag) return; slider.dataset.drag = '1';
  let down = false, sx, sy, sl, st, ticking = false;
  slider.addEventListener('mousedown', e => { down = true; sx = e.pageX - slider.offsetLeft; sy = e.pageY - slider.offsetTop; sl = slider.scrollLeft; st = slider.scrollTop; });
  slider.addEventListener('mouseleave', () => down = false);
  slider.addEventListener('mouseup', () => down = false);
  slider.addEventListener('mousemove', e => {
    if (!down) return;
    e.preventDefault();
    if (!ticking) {
      window.requestAnimationFrame(() => {
        slider.scrollLeft = sl - (e.pageX - slider.offsetLeft - sx) * 2;
        slider.scrollTop = st - (e.pageY - slider.offsetTop - sy) * 2;
        ticking = false;
      });
      ticking = true;
    }
  }, { passive: false });
}
function generateOrgChart() {
  const emptyState = document.getElementById('org-chart-empty');
  const blueprint = document.getElementById('oc-blueprint-area');
  const generateBtn = document.getElementById('btn-generate-org');
  const controlsWrapper = document.getElementById('oc-controls-wrapper');
  const btnIconRight = generateBtn.querySelector('.btn-icon-right');

  generateBtn.disabled = true;
  const originalIconHtml = btnIconRight.innerHTML;
  btnIconRight.innerHTML = `<i data-lucide="loader-2" class="spin" size="16"></i>`;
  lcIcons(btnIconRight);

  Promise.all([
    fetchOrgChartData(),
    new Promise(resolve => setTimeout(resolve, 1000))
  ])
    .then(() => {
      emptyState.style.display = 'none';
      blueprint.style.display = 'block';
      if (controlsWrapper) controlsWrapper.style.display = 'block';
    })
    .catch(err => {
      showNotification('Error', 'Could not load chart: ' + (err.message || err), 'error');
      blueprint.style.display = 'none';
      emptyState.style.display = 'flex';
    })
    .finally(() => {
      generateBtn.disabled = false;
      btnIconRight.innerHTML = originalIconHtml;
      lcIcons(btnIconRight);
    });
}

// ── VAULT MATRIX ──
function initVaultMatrix() {
  const container = document.getElementById('vault-matrix-container');
  if (!container || container.dataset.built) return;

  fetch('api/employees/fetch_vault_matrix.php?limit=1')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);

      const cols = [{ key: 'name', label: 'Employee' }];
      res.docTypes.forEach(doc => {
        cols.push({
          key: 'doc_' + doc.id,
          label: doc.code,
          render: (val) => val
            ? `<div class="vault-slot filled" title="Uploaded"><i data-lucide="check" size="10"></i></div>`
            : `<div class="vault-slot expired" title="Missing"><i data-lucide="alert-circle" size="10"></i></div>`
        });
      });
      cols.push({ key: 'progress', label: 'Fulfillment', render: v => `<span style="font-size:.7rem;font-weight:800;color:var(--primary);">${v}%</span>` });
      cols.push({
        key: 'updated_by_name',
        label: 'Last Updated By',
        render: (v) => v ? v : '<span style="color:var(--muted); font-style:italic;">—</span>'
      });
      cols.push({
        key: '_',
        label: 'Actions',
        render: (v, row) => `<button class="btn btn-xs btn-secondary" onclick="openEmployeeVault('${row.name}', '${row.empId}')">Open Folder</button>`
      });

      initServerPaginatedTable('vault-matrix-container', 'api/employees/fetch_vault_matrix.php', {
        columns: cols,
        perPage: 15,
        searchPlaceholder: 'Search employees...'
      });
    })
    .catch(err => {
      container.innerHTML = `<div style="padding:40px; text-align:center; color:var(--danger);">Error: ${err.message}</div>`;
    });
}

// ── DASHBOARD CHART ──
function initDashboard() {
  if (inited.has('dashboard-main-chart')) return;
  inited.add('dashboard-main-chart');

  const canvas = document.getElementById('chart-headcount-dept');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  const gradient = ctx.createLinearGradient(0, 300, 0, 0);
  gradient.addColorStop(0, '#44c100');
  gradient.addColorStop(1, '#d4fc79');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Engineering', 'Sales', 'Marketing', 'Finance', 'HR', 'Legal', 'IT'],
      datasets: [{
        label: 'Personnel',
        data: [485, 312, 195, 110, 82, 48, 16],
        backgroundColor: gradient,
        borderRadius: 6,
        borderSkipped: false,
        barThickness: 28,
      }]
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          padding: 12,
          titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: '800' },
          bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
          cornerRadius: 8,
          displayColors: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 500,
          ticks: {
            stepSize: 100,
            font: { family: 'JetBrains Mono', size: 11 },
            color: '#94a3b8'
          },
          grid: {
            color: '#f1f5f9',
            drawBorder: false
          }
        },
        x: {
          grid: { display: false },
          ticks: {
            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
            color: '#334155'
          }
        }
      }
    }
  });
}

// ── ANALYTICS CHARTS ──
function initAnalytics() {
  if (inited.has('analytics')) return;
  inited.add('analytics');
  requestAnimationFrame(() => {
    const gridCfg = { color: '#f1f5f9' };
    const legOpts = { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } };
    const c4 = document.getElementById('chart-hire-attrition');
    if (c4) new Chart(c4, { type: 'bar', data: { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], datasets: [{ label: 'New Hires', data: [15, 8, 12, 0, 0, 0], backgroundColor: '#16a34a', borderRadius: 4 }, { label: 'Attrition', data: [5, 4, 7, 0, 0, 0], backgroundColor: '#dc2626', borderRadius: 4 }] }, options: { maintainAspectRatio: false, plugins: { legend: legOpts }, scales: { y: { beginAtZero: true, grid: gridCfg }, x: { grid: { display: false } } } } });
    const c5 = document.getElementById('chart-age');
    if (c5) new Chart(c5, { type: 'bar', data: { labels: ['18–25', '26–35', '36–45', '46–55', '55+'], datasets: [{ label: 'Employees', data: [180, 420, 380, 200, 68], backgroundColor: ['#15b201', '#16a34a', '#d97706', '#dc2626', '#64748b'], borderRadius: 4 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: gridCfg }, x: { grid: { display: false } } } } });
  });
}

// ── MODALS ──
function openModal(id) { document.getElementById(id).classList.add('open'); lcIcons(document.getElementById(id)); }
function closeModal(id, e) { if (!e || e.target === e.currentTarget) document.getElementById(id).classList.remove('open'); }

function openDeptModal() {
  openModal('modal-add-dept');

  const headInput = document.getElementById('as-input-dept-head');
  let hidden = document.getElementById('as-input-dept-head_id');
  if (!hidden) {
    hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.id = 'as-input-dept-head_id';
    headInput.parentNode.appendChild(hidden);
  }

  document.getElementById('dept-name').value = '';
  headInput.value = '';
  hidden.value = '';
  document.getElementById('dept-name').classList.remove('field-error');
  headInput.classList.remove('field-error');
  enforceDropdownOnBlur('as-input-dept-head');
}

function openCompanyEditModal() {
  const getValue = (labelText) => {
    const entries = document.querySelectorAll('.data-entry');
    for (let entry of entries) {
      const label = entry.querySelector('.de-label')?.textContent.trim();
      if (label === labelText) return entry.querySelector('.de-value')?.textContent.trim() || '';
    }
    return '';
  };

  document.getElementById('edit_legal_name').value = getValue('Legal Name');
  document.getElementById('edit_trading_name').value = getValue('Trading Name');
  document.getElementById('edit_ceo_name').value = getValue('CEO');
  document.getElementById('edit_head_office').value = getValue('Head office');
  document.getElementById('edit_entity_type').value = getValue('Entity Type');
  const estDateText = getValue('Establishment');
  if (estDateText !== '-' && estDateText !== '—') {
    const d = new Date(estDateText);
    if (!isNaN(d)) document.getElementById('edit_establishment_date').value = d.toISOString().split('T')[0];
  }
  document.getElementById('edit_registration_no').value = getValue('Registration No.');
  document.getElementById('edit_tin').value = getValue('Tax ID (TIN)');
  document.getElementById('edit_vat_reg_number').value = getValue('VAT Reg Number');
  document.getElementById('edit_trade_license_no').value = getValue('Trade License No.');
  document.getElementById('edit_work_week_desc').value = getValue('Standard Work Week');
  document.getElementById('edit_probation_days').value = getValue('Probation Period');
  document.getElementById('edit_retirement_age').value = getValue('Retirement Policy');
  document.getElementById('edit_main_bank').value = getValue('Main Bank');
  document.getElementById('edit_bank_account_primary').value = getValue('Account (Primary)');
  document.getElementById('edit_base_currency').value = getValue('Base Currency');
  document.getElementById('edit_fiscal_start').value = getValue('Fiscal Start');
  document.getElementById('edit_website').value = getValue('Official Website');
  document.getElementById('edit_corporate_email').value = getValue('Corporate Email');
  document.getElementById('edit_corporate_phone').value = getValue('Corporate Phone');
  document.getElementById('edit_telegram').value = getValue('Telegram:') || getValue('Telegram');
  document.getElementById('edit_whatsapp').value = getValue('WhatsApp:') || getValue('WhatsApp');
  document.getElementById('edit_linkedin').value = getValue('LinkedIn:') || getValue('LinkedIn');
 
  disableCompanyEdit();
  document.getElementById('btn-enable-edit').style.display = 'inline-flex';
  document.getElementById('btn-save-company').style.display = 'none';
  openModal('modal-edit-company');
}

function enableCompanyEdit() {
  const form = document.getElementById('company-profile-form');
  form.querySelectorAll('input, select, textarea').forEach(input => input.disabled = false);
  document.getElementById('btn-enable-edit').style.display = 'none';
  document.getElementById('btn-save-company').style.display = 'inline-flex';
}

function disableCompanyEdit() {
  const form = document.getElementById('company-profile-form');
  form.querySelectorAll('input, select, textarea').forEach(input => input.disabled = true);
}

function saveCompanyProfile() {
  const btn = document.getElementById('btn-save-company');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="14"></i> Saving...`;
  lcIcons(btn);

  const formData = new FormData(document.getElementById('company-profile-form'));

  if (!formData.get('legal_name')) {
    showNotification('Validation Error', 'Legal Name is required.', 'error');
    btn.disabled = false;
    btn.innerHTML = originalHtml;
    lcIcons(btn);
    return;
  }

  fetch('api/companyprofile/update_company_profile.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        showNotification('Success', result.message || 'Company profile updated.', 'success');
        closeCompanyModal();
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification('Error', result.message || 'Update failed.', 'error');
      }
    })
    .catch(error => showNotification('Network Error', error.message, 'error'))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      lcIcons(btn);
      disableCompanyEdit();
      document.getElementById('btn-enable-edit').style.display = 'inline-flex';
      document.getElementById('btn-save-company').style.display = 'none';
    });
}

function closeCompanyModal(e) {
  if (!e || e.target.classList.contains('modal-overlay')) closeModal('modal-edit-company');
}

// Attach company edit button listener on DOM ready
document.addEventListener('DOMContentLoaded', function () {
  const updateBtn = document.querySelector('#p-company-profile .btn-glass-pro-slim');
  if (updateBtn) updateBtn.addEventListener('click', openCompanyEditModal);
});

function closeDeptModal(e) { closeModal('modal-add-dept', e); }

function saveDepartment() {
  const deptName = document.getElementById('dept-name');
  const headInput = document.getElementById('as-input-dept-head');
  const headId = document.getElementById('as-input-dept-head_id')?.value || '';
  const headText = headInput.value.trim();
  const status = document.getElementById('dept-status').value || 'Active';
  const csrfToken = document.getElementById('dept_csrf_token')?.value || '';

  deptName.classList.remove('field-error');
  headInput.classList.remove('field-error');

  if (!deptName.value.trim()) {
    deptName.classList.add('field-error');
    showNotification("Required", "Department name is mandatory.", "warning");
    return;
  }
  if (headText !== '' && headId === '') {
    headInput.classList.add('field-error');
    showNotification('Selection Required', 'Please select an employee from the dropdown list or leave the field empty.', 'warning');
    headInput.focus();
    return;
  }

  const btn = document.querySelector('#modal-add-dept .btn-primary');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  const data = {
    dept_name: deptName.value.trim(),
    dept_head_id: headId,
    dept_status: status,
    csrf_token: csrfToken
  };

  fetch('api/companyprofile/add_department.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('departments');
        showNotification("Success", result.message, "success");
        closeDeptModal();
        inited.delete('departments');
        goPage('departments');
      } else {
        showNotification("Error", result.message, "error");
      }
    })
    .catch(error => showNotification("Error", "Network error: " + error.message, "error"))
    .finally(() => {
      if (btn && btn.isConnected) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);
      }
    });
}

async function openEditDeptModal(deptName, headName, deptId) {
  document.getElementById('edit_dept_id').value = deptId;
  document.getElementById('edit-dept-name').value = deptName;
  document.getElementById('edit-dept-status').value = 'Active';

  const headInput = document.getElementById('as-input-edit-dept-head');
  headInput.value = headName === '—' ? '' : headName;
  headInput.classList.remove('field-error');

  const oldHidden = document.getElementById('as-input-edit-dept-head_id');
  if (oldHidden) oldHidden.remove();

  const cacheKey = 'employees:';
  let employees = dropdownCache[cacheKey];
  if (!employees) {
    try {
      const response = await fetch('api/1common/fetch_dropdown.php?type=employees');
      const result = await response.json();
      if (result.success && result.data) {
        employees = result.data;
        dropdownCache[cacheKey] = employees;
      }
    } catch (err) { /* ignore */ }
  }

  if (employees && headName && headName !== '—') {
    const emp = employees.find(e => e.label.includes(headName));
    if (emp) {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.id = 'as-input-edit-dept-head_id';
      hidden.value = emp.value;
      headInput.parentNode.appendChild(hidden);
    }
  }

  const dropContainer = document.getElementById('as-drop-edit-dept-head');
  if (dropContainer && employees) renderDropdownItems(dropContainer, employees, headName);
  enforceDropdownOnBlur('as-input-edit-dept-head');
  openModal('modal-edit-dept');
}

function closeEditDeptModal(e) { closeModal('modal-edit-dept', e); }

function updateDepartment() {
  const deptId = document.getElementById('edit_dept_id').value;
  const deptName = document.getElementById('edit-dept-name');
  const headInput = document.getElementById('as-input-edit-dept-head');
  const headId = document.getElementById('as-input-edit-dept-head_id')?.value || '';
  const status = document.getElementById('edit-dept-status').value;
  const csrfToken = document.getElementById('edit_dept_csrf_token').value;
  const btn = document.getElementById('btn-update-dept');

  deptName.classList.remove('field-error');
  headInput.classList.remove('field-error');

  if (!deptName.value.trim()) {
    deptName.classList.add('field-error');
    showNotification('Required', 'Department name is mandatory.', 'warning');
    return;
  }
  if (headInput.value.trim() !== '' && !headId) {
    headInput.classList.add('field-error');
    showNotification('Invalid Selection', 'Please select a valid employee from the dropdown, or leave the field empty.', 'warning');
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  const data = {
    dept_id: deptId,
    dept_name: deptName.value.trim(),
    dept_head_id: headId,
    dept_status: status,
    csrf_token: csrfToken
  };

  fetch('api/companyprofile/update_department.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('departments');
        showNotification('Success', result.message, 'success');
        closeEditDeptModal();
        inited.delete('departments');
        goPage('departments');
      } else {
        showNotification('Error', result.message, 'error');
      }
    })
    .catch(error => showNotification('Error', 'Network error: ' + error.message, 'error'))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      lcIcons(btn);
    });
}

// Asset modals
function openAssetModal() { openModal('modal-add-asset'); }
function closeAssetModal(e) { closeModal('modal-add-asset', e); }
function saveNewAsset() { const name = document.getElementById('as-new-name').value; if (!name) { alert('Asset Name is required.'); return; } alert(`Asset "${name}" registered successfully.`); closeAssetModal(); }
function openReassignModal(assetName, currentCustodian) {
  document.getElementById('reassign-display-name').textContent = assetName;
  document.getElementById('reassign-display-curr').textContent = currentCustodian;
  document.getElementById('as-input-reassign').value = '';
  openModal('modal-reassign-asset');
}
function closeReassignModal(e) { closeModal('modal-reassign-asset', e); }
function saveReassignment() {
  const o = document.getElementById('as-input-reassign').value,
        a = document.getElementById('reassign-display-name').textContent;
  if (!o) { alert('Please select a new custodian.'); return; }
  alert(`Reassignment Successful: ${a} has been transferred to ${o}.`);
  closeReassignModal();
}

// Job position modals
function closeJobModal(e) { closeModal('modal-add-job-position', e); }
function openJobModal() {
  openModal('modal-add-job-position');
  document.getElementById('job-title').value = '';
  document.getElementById('as-input-job-dept').value = '';
  const existingHidden = document.getElementById('as-input-job-dept_id');
  if (existingHidden) existingHidden.remove();
  document.getElementById('job-status').value = 'Active';
  enforceDropdownOnBlur('as-input-job-dept');
}
function saveJobPosition() {
  const titleEl = document.getElementById('job-title');
  const deptId = document.getElementById('as-input-job-dept_id')?.value || '';
  const status = document.getElementById('job-status').value || 'Active';
  const csrfToken = document.getElementById('job_csrf_token')?.value || '';
  const btn = document.getElementById('btn-save-job');

  titleEl.classList.remove('field-error');
  const deptInput = document.getElementById('as-input-job-dept');
  deptInput.classList.remove('field-error');

  if (!titleEl.value.trim()) {
    titleEl.classList.add('field-error');
    showNotification("Required Fields", "Job Title is required.", "warning");
    return;
  }
  if (!deptId) {
    deptInput.classList.add('field-error');
    showNotification("Required Fields", "Please select a Department.", "warning");
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Creating...`;
  lcIcons(btn);

  const data = {
    job_title: titleEl.value.trim(),
    job_dept_id: deptId,
    job_status: status,
    csrf_token: csrfToken
  };

  fetch('api/companyprofile/add_job_position.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('job_positions');
        showNotification("Success", result.message, "success");
        closeJobModal();
        inited.delete('job-positions');
        goPage('job-positions');
      } else {
        showNotification("Error", result.message, "error");
      }
    })
    .catch(error => showNotification("Error", "Network error: " + error.message, "error"))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      lcIcons(btn);
    });
}

async function openEditJobModal(title, deptName, jobId) {
  document.getElementById('edit_job_id').value = jobId;
  document.getElementById('edit-job-title').value = title;

  const deptInput = document.getElementById('as-input-edit-job-dept');
  deptInput.value = deptName;
  deptInput.classList.remove('field-error');

  let hidden = document.getElementById('as-input-edit-job-dept_id');
  if (!hidden) {
    hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.id = 'as-input-edit-job-dept_id';
    deptInput.parentNode.appendChild(hidden);
  }

  const cacheKey = 'departments:';
  let departments = dropdownCache[cacheKey];
  if (!departments) {
    try {
      const response = await fetch('api/1common/fetch_dropdown.php?type=departments');
      const result = await response.json();
      if (result.success && result.data) {
        departments = result.data;
        dropdownCache[cacheKey] = departments;
      }
    } catch (err) { /* ignore */ }
  }

  if (departments) {
    const d = departments.find(item => item.label === deptName);
    hidden.value = d ? d.value : '';
    const dropContainer = document.getElementById('as-drop-edit-job-dept');
    if (dropContainer) renderDropdownItems(dropContainer, departments, deptName);
  }

  enforceDropdownOnBlur('as-input-edit-job-dept');
  openModal('modal-edit-job-position');
}

function closeEditJobModal(e) { closeModal('modal-edit-job-position', e); }

function updateJobPosition() {
  const jobId = document.getElementById('edit_job_id').value;
  const titleEl = document.getElementById('edit-job-title');
  const deptInput = document.getElementById('as-input-edit-job-dept');
  const deptId = document.getElementById('as-input-edit-job-dept_id')?.value || '';
  const deptText = deptInput.value.trim();
  const btn = document.getElementById('btn-update-job');

  titleEl.classList.remove('field-error');
  deptInput.classList.remove('field-error');

  if (!titleEl.value.trim()) {
    titleEl.classList.add('field-error');
    showNotification('Required', 'Job Title is required.', 'warning');
    return;
  }
  if (deptText !== '' && !deptId) {
    deptInput.classList.add('field-error');
    showNotification('Invalid Selection', 'Please select a valid department from the dropdown list.', 'warning');
    return;
  }
  if (deptText === '') {
    deptInput.classList.add('field-error');
    showNotification('Required', 'Please select a department.', 'warning');
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  const data = {
    job_id: jobId,
    job_title: titleEl.value.trim(),
    job_dept_id: deptId,
    job_status: document.getElementById('edit-job-status').value,
    csrf_token: document.getElementById('edit_job_csrf_token').value
  };

  fetch('api/companyprofile/update_job_position.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showNotification('Success', res.message, 'success');
        closeEditJobModal();
        inited.delete('job-positions');
        goPage('job-positions');
      } else {
        showNotification('Error', res.message, 'error');
      }
    })
    .catch(error => showNotification('Error', 'Network error: ' + error.message, 'error'))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      lcIcons(btn);
    });
}

// Branch modals
function openBranchModal() {
  openModal('modal-add-branch');
  document.getElementById('branch-name').value = '';
  document.getElementById('branch-name').classList.remove('field-error');
  document.getElementById('as-input-branch-mgr').value = '';
  document.getElementById('as-input-branch-mgr').classList.remove('field-error');
  document.getElementById('branch-status').value = 'Active';
  document.getElementById('branch-phone').value = '';
  document.getElementById('branch-email').value = '';
  document.getElementById('branch-city').value = '';
  document.getElementById('branch-address').value = '';
  const existingHidden = document.getElementById('as-input-branch-mgr_id');
  if (existingHidden) existingHidden.remove();
  enforceDropdownOnBlur('as-input-branch-mgr');
}

function closeBranchModal(e) { closeModal('modal-add-branch', e); }

function saveBranch() {
  const branchName = document.getElementById('branch-name');
  const managerInput = document.getElementById('as-input-branch-mgr');
  const managerId = document.getElementById('as-input-branch-mgr_id')?.value || '';
  const managerText = managerInput.value.trim();
  const status = document.getElementById('branch-status').value || 'Active';
  const phone = document.getElementById('branch-phone').value.trim();
  const email = document.getElementById('branch-email').value.trim();
  const city = document.getElementById('branch-city').value.trim();
  const address = document.getElementById('branch-address').value.trim();
  const csrfToken = document.getElementById('branch_csrf_token')?.value || '';

  branchName.classList.remove('field-error');
  managerInput.classList.remove('field-error');

  if (!branchName.value.trim()) {
    branchName.classList.add('field-error');
    showNotification("Required", "Branch Name is mandatory.", "warning");
    return;
  }
  if (managerText !== '' && managerId === '') {
    managerInput.classList.add('field-error');
    showNotification("Invalid Manager", "Please select a manager from the dropdown list or leave empty field", "warning");
    return;
  }

  const btn = document.getElementById('btn-save-branch');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  const data = {
    branch_name: branchName.value.trim(),
    branch_manager_id: managerId,
    branch_status: status,
    branch_phone: phone,
    branch_email: email,
    branch_city: city,
    branch_address: address,
    csrf_token: csrfToken
  };

  fetch('api/companyprofile/add_branch.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('branches');
        showNotification("Success", result.message, "success");
        closeBranchModal();
        inited.delete('branch-offices');
        goPage('branch-offices');
      } else {
        showNotification("Error", result.message, "error");
      }
    })
    .catch(error => showNotification("Error", "Network error: " + error.message, "error"))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      lcIcons(btn);
    });
}

async function openEditBranchModal(name, manager, phone, email, city, address, status, branchId) {
  document.getElementById('edit_branch_id').value = branchId;
  document.getElementById('edit-branch-name').value = name;
  document.getElementById('edit-branch-phone').value = phone === '—' ? '' : phone;
  document.getElementById('edit-branch-email').value = email === '—' ? '' : email;
  document.getElementById('edit-branch-city').value = city === '—' ? '' : city;
  document.getElementById('edit-branch-address').value = address === '—' ? '' : address;
  document.getElementById('edit-branch-status').value = status;

  const mgrInput = document.getElementById('as-input-edit-branch-mgr');
  mgrInput.value = manager === '—' ? '' : manager;
  mgrInput.classList.remove('field-error');

  const oldHidden = document.getElementById('as-input-edit-branch-mgr_id');
  if (oldHidden) oldHidden.remove();

  const cacheKey = 'employees:';
  let employees = dropdownCache[cacheKey];
  if (!employees) {
    try {
      const response = await fetch('api/1common/fetch_dropdown.php?type=employees');
      const result = await response.json();
      if (result.success && result.data) {
        employees = result.data;
        dropdownCache[cacheKey] = employees;
      }
    } catch (err) { /* ignore */ }
  }

  if (employees && manager && manager !== '—') {
    const emp = employees.find(e => e.label.includes(manager));
    if (emp) {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.id = 'as-input-edit-branch-mgr_id';
      hidden.value = emp.value;
      mgrInput.parentNode.appendChild(hidden);
    }
  }

  const dropContainer = document.getElementById('as-drop-edit-branch-mgr');
  if (dropContainer && employees) renderDropdownItems(dropContainer, employees, manager);
  enforceDropdownOnBlur('as-input-edit-branch-mgr');
  openModal('modal-edit-branch');
}

function closeEditBranchModal(e) { closeModal('modal-edit-branch', e); }

function updateBranch() {
  const branchId = document.getElementById('edit_branch_id').value;
  const branchName = document.getElementById('edit-branch-name');
  const managerInput = document.getElementById('as-input-edit-branch-mgr');
  const managerId = document.getElementById('as-input-edit-branch-mgr_id')?.value || '';
  const managerText = managerInput.value.trim();
  const status = document.getElementById('edit-branch-status').value;
  const phone = document.getElementById('edit-branch-phone').value.trim();
  const email = document.getElementById('edit-branch-email').value.trim();
  const city = document.getElementById('edit-branch-city').value.trim();
  const address = document.getElementById('edit-branch-address').value.trim();
  const csrfToken = document.getElementById('edit_branch_csrf_token').value;
  const btn = document.getElementById('btn-update-branch');

  branchName.classList.remove('field-error');
  managerInput.classList.remove('field-error');

  if (!branchName.value.trim()) {
    branchName.classList.add('field-error');
    showNotification('Required', 'Branch Name is mandatory.', 'warning');
    return;
  }
  if (managerText !== '' && !managerId) {
    managerInput.classList.add('field-error');
    showNotification('Invalid Selection', 'Please select a valid manager from the dropdown, or leave the field empty.', 'warning');
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  const data = {
    branch_id: branchId,
    branch_name: branchName.value.trim(),
    branch_manager_id: managerId,
    branch_status: status,
    branch_phone: phone,
    branch_email: email,
    branch_city: city,
    branch_address: address,
    csrf_token: csrfToken
  };

  fetch('api/companyprofile/update_branch.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('branches');
        showNotification('Success', result.message, 'success');
        closeEditBranchModal();
        inited.delete('branch-offices');
        goPage('branch-offices');
      } else {
        showNotification('Error', result.message, 'error');
      }
    })
    .catch(error => showNotification('Error', 'Network error: ' + error.message, 'error'))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      lcIcons(btn);
    });
}

// ── DROPDOWNS ──
function enforceDropdownOnBlur(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;

  const handler = function () {
    const hidden = document.getElementById(this.id + '_id');
    if (hidden && !hidden.value && this.value.trim() !== '') {
      this.classList.add('field-error');
    } else {
      this.classList.remove('field-error');
    }
  };

  input.removeEventListener('blur', handler);
  input.addEventListener('blur', handler);
}

// ── ONBOARDING WIZARD ──
let currentObStep = 1;
const totalObSteps = 6;

function moveOnboarding(dir) {
  if (dir > 0) {
    if (!validateStep(currentObStep)) return;
  }
  const t = currentObStep + dir;
  if (t >= 1 && t <= totalObSteps) jumpToStep(t);
}

function validateStep(step) {
  const stepFields = [...document.querySelectorAll(`#ob-step-${step} .master-req`)];
  let allValid = true;

  stepFields.forEach(field => {
    const isEmpty = !field.value.trim();
    field.classList.toggle('field-error', isEmpty);
    if (isEmpty) allValid = false;
  });

  if (!allValid) {
    showNotification('Missing Required Fields', 'Please fill in all required fields before proceeding.', 'warning');
    return false;
  }

  const fieldIdsInStep = getFieldIdsForStep(step);
  for (const fieldId of fieldIdsInStep) {
    const result = validateField(fieldId);
    if (!result.valid) {
      showNotification('Invalid Input', result.error, 'warning');
      return false;
    }
  }

  // cross‑field for step 3
  if (step === 3) {
    const hireField = document.getElementById('o-hire');
    const endField = document.getElementById('o-end-date');
    if (hireField && endField && hireField.value && endField.value) {
      const hireDate = new Date(hireField.value);
      const endDate = new Date(endField.value);
      if (endDate <= hireDate) {
        endField.classList.add('field-error');
        showNotification('Invalid Date Range', 'Contract end date must be after start date.', 'warning');
        return false;
      }
    }
  }

  return true;
}

function getFieldIdsForStep(step) {
  const stepMap = {
    1: ['o-dob'],
    2: ['o-email'],
    3: [],
    4: ['o-sal', 'o-tin', 'o-acc'],
    5: ['o-ephone']
  };
  let ids = stepMap[step] || [];

  if (step === 3) {
    ['o-hire', 'o-end-date', 'o-hours'].forEach(id => {
      if (document.getElementById(id)) ids.push(id);
    });
  }

  return ids;
}

function jumpToStep(step) {
  if (step > currentObStep) {
    const cur = [...document.querySelectorAll(`#ob-step-${currentObStep} .master-req`)];
    if (cur.some(i => !i.value.trim())) { moveOnboarding(1); return; }
  }

  currentObStep = step;
  if (step === 6) renderSummary();

  document.querySelectorAll('#p-add-employee .form-section-content').forEach(s => s.classList.remove('active'));
  document.getElementById(`ob-step-${step}`).classList.add('active');

  document.querySelectorAll('.step-pro').forEach((item, idx) => {
    const sNum = idx + 1;
    item.classList.toggle('active', sNum === step);
    item.classList.toggle('done', sNum < step);
    item.querySelector('.step-idx').innerHTML = sNum < step ? '<i data-lucide="check" size="14"></i>' : sNum;
  });

  const dots = document.getElementById('ob-dots'),
        next = document.getElementById('ob-next'),
        bottomCommit = document.getElementById('btn-save-master-bottom'),
        prev = document.getElementById('ob-prev');

  prev.style.visibility = step === 1 ? 'hidden' : 'visible';
  dots.style.display = step === 6 ? 'none' : 'flex';
  next.style.display = step === 6 ? 'none' : 'flex';
  bottomCommit.style.display = step === 6 ? 'flex' : 'none';
  next.innerHTML = step === 5 ? 'Review All Steps' : 'Next Step <i data-lucide="chevron-right" size="14"></i>';

  document.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('active', (i + 1) === step));
  document.getElementById('master-progress-line').style.width = (step / totalObSteps * 100) + '%';

  validateMasterRecord();
  lcIcons(document.getElementById('p-add-employee'));

  fetch('api/get_csrf.php')
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const csrfInput = document.getElementById('csrf_token') || document.querySelector('input[name="csrf_token"]');
        if (csrfInput) csrfInput.value = data.token;
      }
    });
}

function renderSummary() {
  const area = document.getElementById('summary-render-area');
  if (!area) return;

  const getV = (id) => {
    const el = document.getElementById(id);
    return el && el.value.trim() !== "" ? el.value.trim() : '<span style="color:var(--muted); font-weight:400;">—</span>';
  };

  const fullName = `${getV('o-fname')} ${document.getElementById('o-mname').value} ${getV('o-lname')}`.replace(/\s+/g, ' ');
  document.getElementById('rev-full-name').innerHTML = fullName;
  document.getElementById('rev-badge-dept').textContent = getV('o-dept');
  document.getElementById('rev-badge-type').textContent = getV('o-etype');

  const sourceImg = document.getElementById('avatar-img-output');
  const targetImg = document.getElementById('rev-img');
  if (sourceImg && sourceImg.style.display !== 'none') {
    targetImg.src = sourceImg.src;
    targetImg.style.opacity = "1";
  } else {
    targetImg.src = 'assets/img/bgwhitel.png';
    targetImg.style.opacity = "0.3";
  }

  document.getElementById('rev-dob').innerHTML = getV('o-dob');
  document.getElementById('rev-gender').innerHTML = getV('o-gender');
  document.getElementById('rev-phone').innerHTML = getV('o-phone');
  document.getElementById('rev-email').innerHTML = getV('o-email');
  document.getElementById('rev-pos').innerHTML = getV('o-pos');
  document.getElementById('rev-dept').innerHTML = getV('o-dept');
  document.getElementById('rev-etype').innerHTML = getV('o-etype');

  const rawSal = document.getElementById('o-sal').value;
  document.getElementById('rev-sal').innerHTML = rawSal ? 'ETB ' + parseFloat(rawSal).toLocaleString() : '—';
  document.getElementById('rev-bank').innerHTML = getV('o-bank');
  document.getElementById('rev-acc').innerHTML = getV('o-acc');
  document.getElementById('rev-tin').innerHTML = getV('o-tin');
  document.getElementById('rev-ename').innerHTML = getV('o-ename');
  document.getElementById('rev-relation').innerHTML = getV('o-idno');
  document.getElementById('rev-ephone').innerHTML = getV('o-ephone');

  const dynamicContainer = document.getElementById('dynamic-employment-fields');
  const dynamicSummaryArea = document.getElementById('rev-dynamic-fields-area');
  dynamicSummaryArea.innerHTML = '';

  if (dynamicContainer) {
    dynamicContainer.querySelectorAll('input').forEach(input => {
      if (input.type === 'hidden') return;
      const labelText = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || 'Detail';
      let val = input.value.trim() || '—';
      if (input.id === 'o-probation_val') {
        const days = input.value.trim();
        if (days === '0') val = 'No probation';
        else if (days !== '') val = days + ' Days';
      }
      const row = document.createElement('div');
      row.className = 'review-row';
      row.innerHTML = `<span class="rev-label">${labelText}</span><span class="rev-val">${val}</span>`;
      dynamicSummaryArea.appendChild(row);
    });
  }
}

function validateMasterRecord() {
  const all = [...document.querySelectorAll('#p-add-employee .master-req')],
        cur = [...document.querySelectorAll(`#ob-step-${currentObStep} .master-req`)];
  const allOk = all.every(i => i.value.trim()),
        stepOk = cur.every(i => i.value.trim());

  const commitTop = document.getElementById('btn-save-master'),
        commitBtn = document.getElementById('btn-save-master-bottom'),
        next = document.getElementById('ob-next'),
        val = document.getElementById('master-val-text');

  if (next) {
    next.style.opacity = stepOk ? '1' : '0.4';
    next.style.cursor = stepOk ? 'pointer' : 'not-allowed';
  }

  [commitTop, commitBtn].forEach(btn => {
    if (btn) {
      btn.disabled = !allOk;
      btn.style.opacity = allOk ? '1' : '0.4';
      btn.style.cursor = allOk ? 'pointer' : 'not-allowed';
    }
  });

  val.innerHTML = allOk ? '<i data-lucide="check-circle" size="12"></i> Verified' : `* Required fields missing`;
  val.style.color = allOk ? 'var(--success)' : 'var(--danger)';

  all.forEach(i => {
    if (!i.value.trim()) return;
    if (VALIDATORS[i.id]) {
      const result = VALIDATORS[i.id](i.value);
      if (result.valid) i.classList.remove('field-error');
    } else {
      i.classList.remove('field-error');
    }
  });
  lcIcons(val);
}

let debounceTimer;
document.addEventListener('input', (e) => {
  if (e.target.closest('#p-add-employee') && ['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => validateMasterRecord(), 150);
  }
});

function validateAllSteps() {
  const allReq = [...document.querySelectorAll('#p-add-employee .master-req')];
  let valid = true;

  allReq.forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('field-error');
      valid = false;
    } else {
      input.classList.remove('field-error');
    }
  });

  return valid;
}

function saveNewEmployee() {
  if (typeof validateAllSteps !== 'function') {
    showNotification('System Error', 'Validation function missing. Refresh page.', 'error');
    return;
  }
  if (!validateAllSteps()) {
    showNotification('Validation Failed', 'Please correct all errors before submitting.', 'error');
    validateMasterRecord();
    return;
  }

  const btnTop = document.getElementById('btn-save-master'),
        btnBottom = document.getElementById('btn-save-master-bottom'),
        allBtns = [btnTop, btnBottom].filter(b => b);

  allBtns.forEach(btn => {
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin"></i> Saving...`;
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [btn] });
  });

  const formData = new FormData();
  const getVal = (id) => document.getElementById(id)?.value?.trim() || '';
  const getHiddenVal = (id) => document.getElementById(id + '_id')?.value || '';

  formData.append('first_name', getVal('o-fname'));
  formData.append('middle_name', getVal('o-mname'));
  formData.append('last_name', getVal('o-lname'));
  formData.append('date_of_birth', getVal('o-dob'));
  formData.append('gender', getVal('o-gender'));
  formData.append('marital_status', getVal('o-marital'));
  formData.append('nationality', getVal('o-nat'));
  formData.append('place_of_birth', getVal('o-pob'));

  formData.append('personal_phone', getVal('o-phone'));
  formData.append('personal_email', getVal('o-email'));
  formData.append('address', getVal('o-addr'));
  formData.append('city', getVal('o-city'));
  formData.append('postal_code', getVal('o-zip'));

  formData.append('department_id', getHiddenVal('o-dept'));
  formData.append('branch_id', getHiddenVal('o-branch'));
  formData.append('job_position_id', getHiddenVal('o-pos'));
  formData.append('employment_type_id', getHiddenVal('o-etype'));

  const hireDate = document.getElementById('o-hire');
  if (hireDate) formData.append('hire_date', hireDate.value);
  const endDate = document.getElementById('o-end-date');
  if (endDate) formData.append('contract_end_date', endDate.value);
  const probation = document.getElementById('o-probation');
  if (probation) formData.append('probation_period', probation.value);
  const probationDays = document.getElementById('o-probation_val');
  if (probationDays) formData.append('probation_days', probationDays.value);
  const reportsTo = document.getElementById('o-reports_id');
  if (reportsTo) formData.append('reports_to_id', reportsTo.value);
  const hours = document.getElementById('o-hours');
  if (hours) formData.append('hours_per_week', hours.value);
  const project = document.getElementById('o-project');
  if (project) formData.append('project_name', project.value.trim());
  const institution = document.getElementById('o-institution');
  if (institution) formData.append('institution', institution.value.trim());

  formData.append('salary', getVal('o-sal'));
  formData.append('bank_name', getVal('o-bank'));
  formData.append('bank_account', getVal('o-acc'));
  formData.append('tin', getVal('o-tin'));

  formData.append('emergency_name', getVal('o-ename'));
  formData.append('emergency_phone', getVal('o-ephone'));
  formData.append('emergency_relation', getVal('o-idno'));

  const csrfInput = document.getElementById('csrf_token') || document.querySelector('input[name="csrf_token"]');
  formData.append('csrf_token', csrfInput ? csrfInput.value : '');

  const avatarInput = document.getElementById('avatar-upload');
  if (avatarInput && avatarInput.files.length > 0) formData.append('avatar', avatarInput.files[0]);

  fetch('api/employees/add_employee.php', {
    method: 'POST',
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(text => { throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`); });
      }
      return response.json();
    })
    .then(result => {
      if (result.success) {
        clearDropdownCache('employees');
        showNotification('Success', result.message, 'success');
        setTimeout(() => goPage('employee-directory'), 1500);
      } else {
        if (result.errors) {
          const fieldMap = {
            'first_name': 'o-fname',
            'middle_name': 'o-mname',
            'last_name': 'o-lname',
            'date_of_birth': 'o-dob',
            'gender': 'o-gender',
            'department_id': 'o-dept',
            'branch_id': 'o-branch',
            'job_position_id': 'o-pos',
            'employment_type_id': 'o-etype',
            'hire_date': 'o-hire',
            'contract_end_date': 'o-end-date',
            'salary': 'o-sal',
            'emergency_phone': 'o-ephone',
            'reports_to_id': 'o-reports',
            'hours_per_week': 'o-hours',
            'project_name': 'o-project',
            'institution': 'o-institution',
            'probation_days': 'o-probation_val',
            'probation_period': 'o-probation',
            'bank_account': 'o-acc',
            'tin': 'o-tin',
            'bank_name': 'o-bank',
            'emergency_name': 'o-ename',
            'emergency_relation': 'o-idno',
            'personal_email': 'o-email',
            'personal_phone': 'o-phone',
            'permanent_address': 'o-addr',
            'city': 'o-city',
            'postal_code': 'o-zip',
            'marital_status': 'o-marital',
            'place_of_birth': 'o-pob'
        };
          document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
          Object.keys(result.errors).forEach(field => {
            const inputId = fieldMap[field] || field;
            const input = document.getElementById(inputId);
            if (input) input.classList.add('field-error');
          });
          showNotification('Validation Failed', result.message || 'Please check highlighted fields.', 'error');
        } else {
          showNotification('Error', result.message || 'Failed to create employee.', 'error');
        }
      }
    })
    .catch(error => showNotification('Network Error', error.message || 'Could not connect to server. Check console for details.', 'error'))
    .finally(() => {
      [document.getElementById('btn-save-master'), document.getElementById('btn-save-master-bottom')].forEach(btn => {
        if (btn && btn.isConnected) {
          btn.disabled = false;
          btn.innerHTML = btn.id === 'btn-save-master'
            ? `<i data-lucide="shield-check"></i> Commit Record`
            : `<i data-lucide="user-plus"></i> Add Employee`;
          lucide.createIcons({ nodes: [btn] });
        }
      });
    });
}

// ── PAGE INIT ROUTER ──
const inited = new Set();
let pendingEmployeeVaultData = null;

// Org chart caching
let orgChartCache = null;
let cacheTimestamp = 0;
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

function fetchOrgChartData() {
  const container = document.getElementById('dept-tree-container');
  if (!container) return Promise.reject('Container not found');

  // Check cache first
  if (orgChartCache && Date.now() - cacheTimestamp < CACHE_DURATION) {
    renderOrgChartFromData(orgChartCache);
    return Promise.resolve(orgChartCache);
  }

  container.innerHTML = '<div style="padding:40px; text-align:center;"><i data-lucide="loader-2" class="spin"></i> Loading structure...</div>';
  lcIcons(container);

  return fetch('api/companyprofile/fetch_org_chart.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      
      // Cache the data
      orgChartCache = res.data;
      cacheTimestamp = Date.now();
      
      renderOrgChartFromData(res.data);
      return res.data;
    })
    .catch(err => {
      container.innerHTML = `<div style="padding:40px; text-align:center; color:var(--danger);">Error: ${err.message}</div>`;
      throw err;
    });
}

// Clear cache when needed (call this after updating departments/positions)
function clearOrgChartCache() {
  orgChartCache = null;
  cacheTimestamp = 0;
}

function renderOrgChartFromData(data) {
  const container = document.getElementById('dept-tree-container');
  const departments = data.departments;

  // Build all HTML strings first to minimize DOM operations
  const departmentHtml = departments.map(dept => {
    let submenuHtml = '';
    if (dept.jobs && dept.jobs.length > 0) {
      const jobsHtml = dept.jobs.map(job => 
        `<li>
          <div class="oc-node oc-staff" style="width:160px; border-top:2px solid var(--primary-light);">
            <div class="oc-node-body" style="padding:12px; text-align:center; flex-direction:column;">
              <div class="oc-node-name" style="font-size:.75rem; font-weight:700;">${job.title}</div>
              <div class="oc-node-role" style="font-size:.6rem; margin-top:4px;">${job.count} employees</div>
            </div>
          </div>
        </li>`
      ).join('');
      submenuHtml = `<ul class="submenu-jobs" style="padding-top:20px;">${jobsHtml}</ul>`;
    }

    return `<li>
      <div class="oc-node oc-mgr">
        <div class="oc-node-header">
          <span class="oc-id">${dept.name.toUpperCase()}</span>
          <span class="badge badge-primary" style="font-size:8px;">${dept.headcount} EMP</span>
        </div>
        <div class="oc-node-body">
          <div class="oc-node-avatar" style="background:var(--primary-light);color:var(--primary);">
            <i data-lucide="users" size="20"></i>
          </div>
          <div class="oc-node-info">
            <div class="oc-node-name">${dept.name}</div>
            <div class="oc-node-role">Department</div>
          </div>
        </div>
        <div class="oc-node-footer">${dept.position_count} Positions</div>
      </div>
      ${submenuHtml}
    </li>`;
  }).join('');

  // Single DOM update
  container.innerHTML = `<ul>${departmentHtml}</ul>`;
  
  // Update badges in batch
  const totalBadge = document.getElementById('oc-total-badge');
  const deptCountFooter = document.getElementById('oc-dept-count');
  if (totalBadge) totalBadge.textContent = `${data.total} TOTAL`;
  if (deptCountFooter) deptCountFooter.textContent = `${departments.length} Departments`;
  
  lcIcons(container);
}

function initPage(id) {
  if (id === 'transfers') {
    renderDepartmentTransfers();
    return;
  }
  if (id === 'leave-analytics') {
    initLeaveAnalytics();
    return;
  }
  if (inited.has(id)) return;
  inited.add(id);

  switch (id) {
    case 'attendance-reports':
      // Initially empty or can call generateAttendanceReport() if you want it pre-loaded
      break;
    case 'shift-management':
      renderShifts(); // Default view
      break;
    case 'dashboard': initDashboard(); break;
    case 'org-chart': initOrgChart(); break;
    case 'departments':
      initServerPaginatedTable('tbl-departments', 'api/companyprofile/fetch_departments.php', {
        columns: [
          { key: 'name', label: 'Department Name' },
          { key: 'head', label: 'Head of Department' },
          { key: 'emp', label: 'Employees' },
          { key: 'status', label: 'Status' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: (v, row) => `
              <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                <button class="btn btn-xs btn-secondary" onclick="openEditDeptModal('${row.name.replace(/'/g, "\\'")}', '${row.head || ''}', ${row.id || 0})" title="Edit"><i data-lucide="edit" size="12"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;" title="Delete"><i data-lucide="trash-2" size="12"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search department or head...'
      });
      break;
    case 'job-positions':
      initServerPaginatedTable('tbl-job-positions', 'api/companyprofile/fetch_jobpositions.php', {
        columns: [
          { key: 'title', label: 'Job Title' },
          { key: 'dept', label: 'Department' },
          { key: 'count', label: 'Employees' },
          { key: 'status', label: 'Status' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: (v, row) => `
              <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                <button class="btn btn-xs btn-secondary" onclick="openEditJobModal('${row.title.replace(/'/g, "\\'")}', '${row.dept}', ${row.id || 0})" title="Edit"><i data-lucide="edit" size="12"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;" title="Delete"><i data-lucide="trash-2" size="12"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search job title or department...'
      });
      break;
    case 'branch-offices':
      initServerPaginatedTable('tbl-branch-offices', 'api/companyprofile/fetch_branchoffices.php', {
        columns: [
          { key: 'name', label: 'Branch Name' },
          { key: 'manager', label: 'Branch Manager' },
          { key: 'phone', label: 'Phone' },
          { key: 'email', label: 'Email' },
          { key: 'location', label: 'Location' },
          { key: 'emp', label: 'Staff' },
          {
            key: 'status', label: 'Status',
            render: (v) => v === 'Active' ? statusBadge.active : statusBadge.inactive
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: (v, row) => `
              <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                <button class="btn btn-xs btn-secondary" onclick="openEditBranchModal('${row.name.replace(/'/g, "\\'")}', '${row.manager || ''}', '${row.phone || ''}', '${row.email || ''}', '${row.location || ''}', '${row.address || ''}', '${row.status}', ${row.id || 0})" title="Edit"><i data-lucide="edit" size="12"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;" title="Delete"><i data-lucide="trash-2" size="12"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search branch name or manager...'
      });
      break;
    case 'employee-directory':
      initServerPaginatedTable('tbl-employees', 'api/employees/fetch_empprofiles.php', {
        columns: [
          { key: 'id', label: 'Emp ID' },
          { key: 'fname', label: 'First Name' },
          { key: 'mname', label: 'Middle Name' },
          { key: 'lname', label: 'Last Name' },
          { key: 'uname', label: 'Username' },
          { key: 'gender', label: 'Gender' },
          { key: 'dob', label: 'Date of Birth' },
          { key: 'hire', label: 'Hire Date' },
          {
            key: 'status', label: 'Status',
            render: (v) => {
              const s = v.toLowerCase();
              if (s === 'active') return statusBadge.active;
              if (s === 'inactive' || s === 'terminated') return statusBadge.inactive;
              return b('warning', v);
            }
          },
          { key: 'marital', label: 'Marital Status' },
          { key: 'phone', label: 'Phone' },
          { key: 'email', label: 'Email' },
          { key: 'dept', label: 'Department' },
          { key: 'position', label: 'Job Position' },
          { key: 'branch', label: 'Branch name' },
          { key: 'type', label: 'Emp Type' },
          { key: 'bankname', label: 'Bank name' },
          { key: 'bankacc', label: 'Bank Account' },
          { key: 'tin', label: 'Tin number' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: 'created', label: 'Created At' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row">
                <button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;"><i data-lucide="trash-2" size="10"></i></button>
              </div>`
          }
        ],
        searchPlaceholder: 'Search full name, job title, department...', perPage: 15
      });
      break;
    case 'add-employee':
      currentObStep = 1;
      jumpToStep(1);
      ['o-dob', 'o-email', 'o-sal', 'o-tin', 'o-acc', 'o-ephone'].forEach(id => {
        const field = document.getElementById(id);
        if (field) field.addEventListener('blur', function () { validateField(this.id); });
      });
      const pAddEmp = document.getElementById('p-add-employee');
      if (pAddEmp) {
        pAddEmp.addEventListener('blur', function (e) {
          const target = e.target;
          if (target.id === 'o-hire' || target.id === 'o-end-date' || target.id === 'o-hours') {
            validateField(target.id);
          }
        }, true);
      }
      ['o-dob', 'o-hire', 'o-end-date'].forEach(id => {
        const field = document.getElementById(id);
        if (field) {
          field.removeAttribute('readonly');
          field.addEventListener('keydown', (e) => e.stopPropagation());
        }
      });
      setTimeout(() => validateMasterRecord(), 50);
      break;
    case 'employment-types': initEmploymentTypesCards(); break;
    case 'probation-tracker':
      initServerPaginatedTable('tbl-probation', 'api/employees/fetch_probation.php', {
        columns: [
          { key: 'name', label: 'Employee' },
          { key: 'dept', label: 'Department' },
          { key: 'start', label: 'Probation Start' },
          { key: 'end', label: 'Probation End' },
          {
            key: 'days', label: 'Days Left',
            render: (v) => {
              const days = parseInt(v);
              if (days < 0) return `<span style="color:var(--danger); font-weight:bold;">Overdue (${Math.abs(days)})</span>`;
              if (days <= 30) return `<span style="color:var(--warning); font-weight:bold;">${days} Days</span>`;
              return `${days} Days`;
            }
          },
          {
            key: 'status', label: 'Probation Status',
            render: (v, row) => {
              if (v === 'Completed') return b('success', 'Completed');
              if (v === 'Failed') return b('danger', 'Failed');
              if (v === 'Extended') return b('warning', 'Extended');
              const days = parseInt(row.days);
              if (days < 0) return b('danger', 'Overdue');
              if (days <= 14) return b('warning', 'Ending Soon');
              return b('info', 'Active');
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: (v, row) => `
              <div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                <button class="btn btn-xs" style="background: #f1f5f9; color: var(--primary); border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center; padding: 5px; border-radius: 6px;" title="Evaluate Employee" onclick="openProbationEvalModal('${row.employee_id}', '${row.name.replace(/'/g, "\\'")}')"><i data-lucide="clipboard-check" size="13"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;display:inline-flex;align-items:center;justify-content:center;padding: 5px; border-radius: 6px; cursor:pointer;"><i data-lucide="trash-2" size="13"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search employee name or department...'
      });
      break;
    case 'contract-renewals':
      initServerPaginatedTable('tbl-contract-renewals', 'api/employees/fetch_contracts.php', {
        columns: [
          { key: 'name', label: 'Employee' },
          { key: 'dept', label: 'Department' },
          { key: 'start', label: 'Start Date' },
          {
            key: 'expiry', label: 'Contract Expiry Date',
            render: (v) => {
              if (v === 'Permanent') return '<span style="color:var(--success); font-weight:600;">Permanent</span>';
              const d = new Date(v);
              if (isNaN(d.getTime())) return v;
              return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            }
          },
          {
            key: 'days', label: 'Status',
            render: (v, row) => {
              if (row.expiry === 'Permanent') return b('success', 'Permanent');
              const days = parseInt(v);
              if (days < 0) return b('danger', 'Expired');
              if (days <= 15) return b('danger', 'Critical');
              if (days <= 30) return b('warning', 'Due Soon');
              return b('success', 'Active');
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row">
                <button class="btn btn-xs btn-secondary" title="Renew Contract"><i data-lucide="refresh-cw" size="10"></i></button>
                <button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search employee name or department...'
      });
      break;
    case 'retirement-planner':
      initServerPaginatedTable('tbl-retirement', 'api/employees/fetch_retirement.php', {
        columns: [
          { key: 'name', label: 'Employee' },
          { key: 'dept', label: 'Department' },
          { key: 'age', label: 'Age' },
          { key: 'tenure', label: 'Service Period' },
          { key: 'date', label: 'Retirement Date' },
          {
            key: 'days', label: 'Status',
            render: (v) => {
              const d = parseInt(v);
              return d < 0 ? b('neutral', 'Retired') : d <= 90 ? b('danger', `Upcoming (${d}D)`) : d <= 365 ? b('warning', 'Within Year') : b('info', 'Active');
            }
          },
          { key: 'pension', label: 'Pension Status', render: () => b('warning', 'In Progress') },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Succession Plan"><i data-lucide="user-plus" size="10"></i></button><button class="btn btn-xs btn-primary" title="Clearance"><i data-lucide="clipboard-check" size="10"></i></button></div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search retirement forecast...'
      });
      break;
    case 'former-employees':
      initServerPaginatedTable('tbl-former-employees', 'api/employees/fetch_former_employees.php', {
        columns: [
          { key: 'name', label: 'Employee' },
          { key: 'dept', label: 'Last Department' },
          { key: 'role', label: 'Last Role' },
          { key: 'exitDate', label: 'Exit Date' },
          {
            key: 'type', label: 'Reason',
            render: (v) => v === 'Terminated' ? b('danger', v) : b('neutral', v)
          },
          { key: 'duration', label: 'Duration' },
          {
            key: 'rehire', label: 'Rehire possibility',
            render: (v) => v === 'No' ? b('danger', 'No') : b('success', 'Yes')
          },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row"><button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;"><i data-lucide="trash-2" size="10"></i></button></div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search former employees...'
      });
      break;
    case 'asset-tracking':
      initServerPaginatedTable('tbl-assets', 'api/employees/fetch_assets.php', {
        columns: [
          { key: 'id', label: 'Item Code' },
          { key: 'name', label: 'Asset Name' },
          { key: 'cat', label: 'Category' },
          { key: 'serial', label: 'Serial number' },
          {
            key: 'val', label: 'Asset Value',
            render: (v) => v ? 'ETB ' + parseFloat(v).toLocaleString() : '—'
          },
          { key: 'user_prev', label: 'Previous custodian' },
          { key: 'user', label: 'Current custodian' },
          { key: 'loc', label: 'Location' },
          { key: 'war', label: 'Warranty' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: (v, row) => `<div style="display: flex; gap: 8px; justify-content: center;">
                <button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button>
                <button class="btn btn-xs btn-secondary" onclick="openReassignModal('${row.name}','${row.user}')" title="Reassign"><i data-lucide="shuffle" size="10"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search assets...'
      });
      break;
    case 'document-vault': initVaultMatrix(); break;
    case 'employee-vault':
      if (pendingEmployeeVaultData) {
        const { name, id } = pendingEmployeeVaultData;
        document.getElementById('v-emp-name').textContent = name;
        document.getElementById('v-emp-id').textContent = id + " • Personnel Archive";
        const listContainer = document.getElementById('vault-docs-list');
        listContainer.innerHTML = '';
        let uploadCount = 0;

        VAULT_SCHEMA.forEach(doc => {
          const isUploaded = Math.random() > 0.4;
          if (isUploaded) uploadCount++;

          const row = document.createElement('div');
          row.className = 'doc-row';
          row.innerHTML = `
            <div class="doc-icon-box ${isUploaded ? 'uploaded' : 'missing'}">
              <i data-lucide="${isUploaded ? 'file-check' : 'file-question-mark'}" size="18"></i>
            </div>
            <div class="doc-meta">
              <div class="doc-name">${doc.name}</div>
              <div class="doc-cat">${doc.cat}</div>
            </div>
            <div class="doc-status">${isUploaded ? '<span class="badge badge-success" style="font-size:10px">Verified</span>' : '<span class="badge badge-neutral" style="font-size:10px; opacity:0.7;">Pending</span>'}</div>
            <div class="doc-actions">${isUploaded ? `
              <button class="btn btn-secondary btn-xs" title="View" onclick="showNotification('Vault','Opening file...','info')"><i data-lucide="eye" size="14"></i> View</button>
              <button class="btn btn-secondary btn-xs" title="Update" style="min-width:34px;"><i data-lucide="refresh-cw" size="14"></i></button>` : `
              <button class="btn btn-primary btn-xs btn-upload-pro" onclick="showNotification('Vault','Ready for upload','info')"><i data-lucide="plus" size="14"></i> Add Document</button>`}
            </div>`;
          listContainer.appendChild(row);
        });

        const total = VAULT_SCHEMA.length;
        document.getElementById('v-count-upload').textContent = uploadCount;
        document.getElementById('v-count-missing').textContent = total - uploadCount;
        document.getElementById('v-compliance-percent').textContent = Math.round((uploadCount / total) * 100) + "%";
        lcIcons(listContainer);
        pendingEmployeeVaultData = null;
      }
      break; 
case 'job-vacancies':
    renderJobVacancies();
    break;
    case 'candidates':
    renderApplicantList();
    break;
      case 'interview-tracker':
      initServerPaginatedTable('tbl-interviews', 'api/talent/fetch_interviews.php', {
        columns: [
          { key: 'candidate', label: 'Candidate' },
          { key: 'position', label: 'Position' },
          { key: 'interviewer', label: 'Interviewer' },
          { key: 'date', label: 'Date' },
          { key: 'time', label: 'Time' },
          { key: 'mode', label: 'Mode' },
          {
            key: 'result', label: 'Result',
            render: (v) => {
              const s = v.toLowerCase();
              return s === 'passed' ? b('success', 'Passed') : s === 'failed' ? b('danger', 'Failed') : s === 'scheduled' ? b('info', 'Scheduled') : s === 'on hold' ? b('warning', 'On Hold') : s === 'no show' ? b('neutral', 'No Show') : b('primary', v);
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Edit Interview"><i data-lucide="edit-3" size="10"></i></button></div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search interviews...'
      });
      break;
    
    case 'internship':
    renderInternshipManagement();
    break;    
      case 'daily-attendance':
      initServerPaginatedTable('tbl-attendance', 'api/attendance/fetch_daily.php', {
        columns: [
          { key: 'name', label: 'Employee' },
          { key: 'dept', label: 'Dept' },
          { key: 'shift', label: 'Shift' },
          {
            key: 'checkin', label: 'Check In',
            render: (v) => v ? v.substring(0, 5) : '—'
          },
          {
            key: 'checkout', label: 'Check Out',
            render: (v) => v ? v.substring(0, 5) : '—'
          },
          { key: 'hours', label: 'Hours' },
          { key: 'ot', label: 'OT' },
          {
            key: 'status', label: 'Status',
            render: (v, row) => {
              if (row.is_late == 1) return b('warning', 'Late');
              if (v === 'P') return b('success', 'Present');
              if (v === 'A') return b('danger', 'Absent');
              if (v === 'L') return b('info', 'On Leave');
              if (v === 'H') return b('neutral', 'Half Day');
              if (v === 'O') return b('neutral', 'Off');
              return b('neutral', v);
            }
          },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View Logs"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs btn-secondary" title="Edit Entry"><i data-lucide="edit-2" size="10"></i></button></div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search attendance...'
      });
      break;
    case 'overtime-requests':
      initServerPaginatedTable('tbl-overtime', 'api/benefits/fetch_overtime.php', {
        columns: [
          { key: 'emp', label: 'Employee' },
          { key: 'dept', label: 'Dept' },
          { key: 'date', label: 'Date' },
          { key: 'hours', label: 'OT Hours', render: (v) => `<b>${v} hrs</b>` },
          { key: 'reason', label: 'Reason', render: (v) => `<span title="${v}" style="display:block; max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v}</span>` },
          { key: 'submitted', label: 'Submitted' },
          {
            key: 'status', label: 'Status',
            render: (v) => {
              if (v === 'Approved') return b('success', 'Approved');
              if (v === 'Rejected') return b('danger', 'Rejected');
              if (v === 'Pending') return b('warning', 'Pending');
              return b('neutral', v);
            }
          },
          {
            key: '_', label: 'Actions',
            render: (v, row) => `<div class="flex-row">${row.status === 'Pending' ? `<button class="btn btn-xs btn-primary" title="Approve/Reject"><i data-lucide="check-circle" size="10"></i> Process</button>` : `<button class="btn btn-xs btn-secondary" title="View Detail"><i data-lucide="eye" size="10"></i></button>`}</div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search overtime requests...'
      });
      break;
    case 'attendance-reports':
      initServerPaginatedTable('tbl-attendance-reports', 'api/attendance/fetch_reports.php', {
        columns: [
          { key: 'dept', label: 'Department' },
          { key: 'total', label: 'Total Emp' },
          { key: 'absent', label: 'Absent Days' },
          { key: 'leave_days', label: 'Leave Days' },
          { key: 'late', label: 'Late Arrivals' },
          { key: 'ot', label: 'Total OT Hrs', render: (v) => v ? parseFloat(v).toFixed(1) : '0.0' },
          {
            key: 'rate', label: 'Attendance Rate',
            render: (v) => {
              const val = parseFloat(v);
              let color = 'var(--success)';
              if (val < 90) color = 'var(--warning)';
              if (val < 75) color = 'var(--danger)';
              return `<b style="color:${color}">${val}%</b>`;
            }
          }
        ],
        perPage: 15, searchPlaceholder: 'Search reports by department...'
      });
      break;
    case 'leave-policy':
      renderLeaveTypes();
      break;
    case 'leave-types': initLeaveTypesCards(); break;
    case 'leave-requests':
      renderLeaveRequests('All');
      break;
    case 'leave-entitlement':
      renderLeaveEntitlements();
      break;
    case 'leave-calendar':
      document.getElementById('cal-splash-screen').style.display = 'block';
      document.getElementById('cal-real-content').style.display = 'none';
      document.getElementById('calendar-main-card').style.alignItems = 'center';
      lcIcons(document.getElementById('cal-splash-screen'));
      break;
    case 'medical-claims':
      initServerPaginatedTable('tbl-medical', 'api/benefits/fetch_medical_claims.php', {
        columns: [
          { key: 'id', label: 'Claim ID' },
          { key: 'emp', label: 'Employee' },
          { key: 'dept', label: 'Department' },
          { key: 'category', label: 'Category' },
          { key: 'amount', label: 'Amount', render: (v) => v ? 'ETB ' + parseFloat(v).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00' },
          { key: 'submitted', label: 'Submitted' },
          { key: 'receipt', label: 'Receipt', render: (v) => v == 1 ? b('success', 'Attached') : b('neutral', 'None') },
          {
            key: 'status', label: 'Status',
            render: (v) => {
              if (v === 'Approved') return b('success', 'Approved');
              if (v === 'Rejected') return b('danger', 'Rejected');
              if (v === 'Pending') return b('warning', 'Pending');
              return b('neutral', v);
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: (v, row) => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View Receipt"><i data-lucide="file-text" size="10"></i></button>${row.status === 'Pending' ? `<button class="btn btn-xs btn-primary" title="Process"><i data-lucide="check-circle" size="10"></i></button>` : ''}</div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search medical claims...'
      });
      break;
    case 'training-needs':
      initServerPaginatedTable('tbl-training-needs', 'api/training/fetch_training_needs.php', {
        columns: [
          { key: 'dept', label: 'Department' },
          { key: 'skill', label: 'Skill Gap' },
          {
            key: 'priority', label: 'Priority',
            render: (v) => {
              if (v === 'High') return b('danger', 'High');
              if (v === 'Medium') return b('warning', 'Medium');
              return b('neutral', 'Low');
            }
          },
          { key: 'emp_count', label: 'Affected', render: (v) => `<b>${v} Employees</b>` },
          { key: 'proposed', label: 'Proposed Training' },
          {
            key: 'status', label: 'Status',
            render: (v) => {
              if (v === 'Approved') return b('success', 'Approved');
              if (v === 'Ongoing') return b('primary', 'Ongoing');
              if (v === 'Pending') return b('warning', 'Pending');
              return b('neutral', v);
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs btn-primary" title="Schedule Training"><i data-lucide="calendar-plus" size="10"></i></button></div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search training needs...'
      });
      break;
    case 'training-schedule':
      initServerPaginatedTable('tbl-training-schedule', 'api/training/fetch_training_schedule.php', {
        columns: [
          { key: 'course', label: 'Course' },
          { key: 'dept', label: 'Department' },
          { key: 'trainer', label: 'Trainer' },
          { key: 'date', label: 'Date' },
          { key: 'time', label: 'Time', render: (v) => v ? v.substring(0, 5) : '—' },
          { key: 'venue', label: 'Venue' },
          {
            key: 'seats', label: 'Enrolled/Seats',
            render: (v, row) => {
              const total = row.total_seats || 0;
              const enrolled = row.enrolled_seats || 0;
              const pct = total > 0 ? (enrolled / total) * 100 : 0;
              let color = pct >= 100 ? 'var(--danger)' : 'var(--primary)';
              return `<span style="font-weight:700; color:${color}">${enrolled}</span> / ${total}`;
            }
          },
          {
            key: 'status', label: 'Status',
            render: (v) => {
              if (v === 'Confirmed') return b('success', 'Confirmed');
              if (v === 'Open') return b('warning', 'Open');
              if (v === 'Cancelled') return b('danger', 'Cancelled');
              return b('neutral', v);
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row">
                <button class="btn btn-xs btn-secondary" title="View Roster"><i data-lucide="users" size="10"></i></button>
                <button class="btn btn-xs btn-primary" title="Edit Session"><i data-lucide="edit-3" size="10"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search schedule...'
      });
      break;
    case 'performance-reviews':
      initServerPaginatedTable('tbl-reviews', 'api/performance/fetch_reviews.php', {
        columns: [
          { key: 'emp', label: 'Employee' },
          { key: 'dept', label: 'Department' },
          { key: 'reviewer', label: 'Reviewer' },
          { key: 'period', label: 'Period' },
          { key: 'score', label: 'Overall Score', render: (v) => v ? `<b>${parseFloat(v).toFixed(1)}</b> / 10` : '—' },
          {
            key: 'rank', label: 'Rating',
            render: (v) => {
              if (v === 'Exceptional') return b('success', v);
              if (v === 'Exceeds') return b('primary', 'Exceeds');
              if (v === 'Meets') return b('neutral', 'Meets');
              if (v === 'Below') return b('danger', 'Below Expectation');
              return b('neutral', v);
            }
          },
          {
            key: 'status', label: 'Status',
            render: (v) => {
              if (v === 'Submitted') return b('success', 'Submitted');
              if (v === 'Pending') return b('warning', 'Pending');
              if (v === 'Acknowledged') return b('info', 'Acknowledged');
              return b('neutral', v);
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row">
                <button class="btn btn-xs btn-secondary" title="View Review"><i data-lucide="eye" size="10"></i></button>
                <button class="btn btn-xs btn-primary" title="Print PDF"><i data-lucide="printer" size="10"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search reviews...'
      });
      break;
    case '360-feedback':
      initServerPaginatedTable('tbl-360', 'api/performance/fetch_360.php', {
        columns: [
          { key: 'subject', label: 'Subject' },
          { key: 'dept', label: 'Department' },
          { key: 'total', label: 'Total Respondents' },
          {
            key: 'complete', label: 'Completed',
            render: (v, row) => {
              const total = row.total || 1;
              const pct = Math.round((v / total) * 100);
              return `<b>${v}</b> <small style="color:var(--muted)">(${pct}%)</small>`;
            }
          },
          { key: 'avg', label: 'Avg Score', render: (v) => v ? `<b>${parseFloat(v).toFixed(1)}</b> / 10` : '<span style="color:var(--muted)">TBD</span>' },
          {
            key: 'status', label: 'Status',
            render: (v) => {
              if (v === 'Closed') return b('success', 'Closed');
              if (v === 'Open') return b('primary', 'Open');
              if (v === 'In Progress') return b('warning', 'In Progress');
              return b('neutral', v);
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row">
                <button class="btn btn-xs btn-secondary" title="View Individual Feedback"><i data-lucide="users" size="10"></i></button>
                <button class="btn btn-xs btn-primary" title="Generate Report"><i data-lucide="file-bar-chart" size="10"></i></button>
              </div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search feedback subjects...'
      });
      break;
    case 'Promote/Demote':
      renderPromoteDemoteTable();
      break;
    case 'transfers':
      renderDepartmentTransfers();
      break;
    case 'attendance': buildMatrix(); break;
    case 'disciplinary-actions':
      renderDisciplinaryTable();
      break;
    case 'resignations':
      renderResignations('All');  
      break; 
case 'termination':
    renderSeparations();
    break;
      case 'roles-permissions': initRoles(); break;
    case 'exit-clearance':
      const chk = v => v == 1 ? b('success', '✓') : '<span style="color:var(--muted)">—</span>';
      initServerPaginatedTable('tbl-clearance', 'api/compliance/fetch_clearance.php', {
        columns: [
          { key: 'emp', label: 'Employee' },
          { key: 'dept', label: 'Department' },
          { key: 'it', label: 'IT', render: chk },
          { key: 'finance', label: 'Finance', render: chk },
          { key: 'hr', label: 'HR', render: chk },
          { key: 'admin', label: 'Admin', render: chk },
          { key: 'assets', label: 'Assets', render: chk },
          {
            key: 'overall', label: 'Overall',
            render: (v) => {
              if (v === 'Cleared') return b('success', 'Cleared');
              if (v === 'In Progress') return b('info', 'In Progress');
              return b('warning', 'Pending');
            }
          },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row"><button class="btn btn-xs btn-primary" title="Update Status"><i data-lucide="check-square" size="10"></i> Sign-off</button></div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search clearance status...'
      });
      break;
    case 'user-management':
      initServerPaginatedTable('tbl-users', 'api/system/fetch_users.php', {
        columns: [
          { key: 'id', label: 'User ID', render: (v) => `<span style="font-family:'JetBrains Mono'; font-size:10px;">USR-${String(v).padStart(3, '0')}</span>` },
          { key: 'name', label: 'Full Name' },
          { key: 'email', label: 'Email Address' },
          { key: 'role', label: 'Role', render: (v) => b('primary', v) },
          { key: 'dept', label: 'Dept' },
          { key: 'last_login', label: 'Last Login', render: (v) => v ? v : '<span style="color:var(--muted)">Never</span>' },
          { key: 'status', label: 'Status', render: (v) => v === 'Active' ? statusBadge.active : statusBadge.inactive },
          { key: 'updated_by_name', label: 'Last Updated By' },
          {
            key: '_', label: 'Actions',
            render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Edit Permissions"><i data-lucide="shield-check" size="10"></i></button><button class="btn btn-xs btn-secondary" title="Reset Password"><i data-lucide="key" size="10"></i></button></div>`
          }
        ],
        perPage: 15, searchPlaceholder: 'Search users...'
      });
      break;
    case 'audit-logs':
      initServerPaginatedTable('tbl-audit', 'api/system/fetch_audit.php', {
        columns: [
          { key: 'user', label: 'User', render: (v) => `<span style="font-weight:700;">${v}</span>` },
          {
            key: 'action', label: 'Action',
            render: (v) => {
              if (v === 'DELETE') return b('danger', v);
              if (v === 'UPDATE') return b('warning', v);
              if (v === 'CREATE') return b('success', v);
              if (v === 'LOGIN') return b('primary', v);
              return b('neutral', v);
            }
          },
          { key: 'module', label: 'Module' },
          { key: 'record', label: 'Record', render: (v) => `<code style="background:#f1f5f9; padding:2px 4px; border-radius:4px; font-size:10px;">${v}</code>` },
          { key: 'ip', label: 'IP Address', render: (v) => `<span style="font-family:'JetBrains Mono'; font-size:10px; color:var(--muted);">${v}</span>` },
          {
            key: 'ts', label: 'Timestamp',
            render: (v) => {
              const d = new Date(v);
              return d.toLocaleString('en-GB', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
            }
          },
          { key: '_', label: 'Details', render: () => `<button class="btn btn-xs btn-secondary">View Changes</button>` }
        ],
        perPage: 15, searchPlaceholder: 'Search audit logs...'
      });
      break;
    case 'hr-analytics': initAnalytics(); break;
  }
}

// Employment Types Cards
function initEmploymentTypesCards() {
  const container = document.getElementById('tbl-employment-types');
  if (!container) return;

  container.innerHTML = '<div class="etype-ledger" id="etype-ledger-stack"></div>';
  const stack = document.getElementById('etype-ledger-stack');
  const accentPalette = ['#15b201', '#3b82f6', '#a855f7', '#f59e0b', '#ef4444', '#06b6d4', '#22c55e', '#e11d48', '#8b5cf6', '#14b8a6'];

  stack.innerHTML = '<div style="padding:100px; text-align:center;"><i data-lucide="loader-2" class="spin" style="color:var(--primary); opacity:0.3;"></i></div>';
  lcIcons(stack);

  fetch('api/employees/fetch_emptypes.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      stack.innerHTML = '';

      const totalCount = res.data.reduce((sum, item) => sum + parseInt(item.count), 0) || 1;

      res.data.forEach((item, index) => {
        const name = (item.name || '').toLowerCase();
        let icon = 'briefcase';
        let colorClass = 'cat-perm';

        if (name.includes('permanent')) { icon = 'shield-check'; colorClass = 'cat-perm'; }
        else if (name.includes('contract')) { icon = 'file-text'; colorClass = 'cat-cont'; }
        else if (name.includes('part')) { icon = 'file-check'; colorClass = 'cat-part'; }
        else if (name.includes('intern')) { icon = 'graduation-cap'; colorClass = 'cat-intn'; }
        else if (name.includes('temp')) { icon = 'clock'; colorClass = 'cat-temp'; }

        const percentage = Math.max(5, (parseInt(item.count) / totalCount) * 100);
        const accentColor = accentPalette[index % accentPalette.length];

        const row = document.createElement('div');
        row.className = `etype-master-row ${colorClass}`;
        row.style.setProperty('--accent-color', accentColor);
        row.innerHTML = `
          <div class="etype-visual"><div class="etype-icon-box"><i data-lucide="${icon}" size="20"></i></div></div>
          <div class="etype-identity">
            <span class="etype-label">${item.name}</span>
            <span class="etype-sub">${item.desc || 'Active organizational policy for ' + item.name + ' staff.'}</span>
          </div>
          <div class="etype-distribution">
            <span class="dist-label">Workforce Distribution</span>
            <div class="dist-track"><div class="dist-fill" style="width: 0%" data-pct="${percentage}"></div></div>
          </div>
          <div class="etype-data">
            <span class="data-val">${item.count}</span>
            <span class="data-unit">Emp</span>
          </div>`;
        stack.appendChild(row);
      });

      setTimeout(() => {
        stack.querySelectorAll('.dist-fill').forEach(bar => bar.style.width = bar.dataset.pct + '%');
      }, 100);

      lcIcons(stack);
    })
    .catch(err => {
      stack.innerHTML = `<div style="padding:40px; text-align:center; color:var(--danger); font-weight:800;">${err.message}</div>`;
    });
}

function initLeaveTypesCards() {
  const container = document.getElementById('tbl-leave-types');
  if (!container) return;

  container.innerHTML = '<div class="leave-type-viewport"><div class="etype-ledger" id="leave-type-ledger-stack"></div></div>';
  const stack = document.getElementById('leave-type-ledger-stack');
  const accentPalette = ['var(--primary)', 'var(--info)', 'var(--success)', 'var(--warning)', 'var(--danger)'];

  stack.innerHTML = '<div style="padding:100px; text-align:center;"><i data-lucide="loader-2" class="spin" style="color:var(--primary); opacity:0.3;"></i></div>';
  lcIcons(stack);

  fetch('api/leave/fetch_leave_types.php?limit=1000')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      stack.innerHTML = '';

      res.data.forEach((item, index) => {
        const name = (item.name || '').toLowerCase();
        let icon = 'calendar';
        if (name.includes('annual')) icon = 'sun';
        else if (name.includes('sick')) icon = 'stethoscope';
        else if (name.includes('maternity') || name.includes('paternity')) icon = 'baby';
        else if (name.includes('compassionate')) icon = 'heart-handshake';
        else if (name.includes('study')) icon = 'book-open';

        const days = parseInt(item.days, 10) || 0;
        const carry = parseInt(item.carry, 10) || 0;
        const accentColor = accentPalette[index % accentPalette.length];
        const isPaid = String(item.paid) === 'Yes';
        const needsApproval = String(item.approval) === '1';

        const row = document.createElement('div');
        row.className = 'etype-master-row leave-type-row';
        row.style.setProperty('--accent-color', accentColor);
        row.innerHTML = `
          <div class="etype-visual leave-type-visual"><div class="etype-icon-box leave-type-icon"><i data-lucide="${icon}" size="18"></i></div></div>
          <div class="etype-identity">
            <div class="leave-type-head"><span class="etype-label">${item.name}</span></div>
            <div class="leave-type-meta">
              <span class="badge ${isPaid ? 'badge-success' : 'badge-warning'}">${isPaid ? 'Paid' : 'Unpaid'}</span>
              <span class="badge ${needsApproval ? 'badge-info' : 'badge-neutral'}">${needsApproval ? 'Approval Required' : 'No Approval'}</span>
              <span class="badge badge-neutral">Carryover ${carry} days</span>
            </div>
            <div class="leave-type-foot"><span class="leave-foot-item"><i data-lucide="calendar-days" size="13"></i>Policy cycle: <b>Yearly</b></span></div>
          </div>
          <div class="etype-data leave-type-data">
            <span class="data-val">${days}</span>
            <span class="data-unit">Days / Year</span>
          </div>`;
        stack.appendChild(row);
      });

      lcIcons(stack);
    })
    .catch(err => {
      stack.innerHTML = `<div style="padding:40px; text-align:center; color:var(--danger); font-weight:800;">${err.message}</div>`;
    });
}

// Probation Evaluation
let pendingEvalData = null;
function openProbationEvalModal(empId, empName) {
  document.getElementById('eval-emp-id').value = empId;
  document.getElementById('eval-modal-title').textContent = `Evaluate ${empName}`;
  document.getElementById('eval-notes').value = '';
  openModal('modal-probation-eval');
}

function submitProbationEval(decision) {
  const empId = document.getElementById('eval-emp-id').value;
  const notes = document.getElementById('eval-notes').value.trim();
  const csrfToken = document.getElementById('probation_eval_csrf_token')?.value || '';
  const empName = document.getElementById('eval-modal-title')?.textContent.replace('Evaluate ', '') || 'Employee';

  if (!empId) {
    showNotification('Error', 'Employee ID is missing.', 'error');
    return;
  }

  closeModal('modal-probation-eval');
  pendingEvalData = { empId, empName, csrfToken, notes, decision };

  if (decision === 'Hire') openConfirm('Confirm Hire', 'Are you sure you want to confirm this employee as permanent?', 'Yes, Confirm Hire', 'success');
  else if (decision === 'Reject') openConfirm('Confirm Termination', 'Are you sure you want to terminate this employee? This action cannot be undone.', 'Yes, Terminate', 'danger');
  else if (decision === 'Extend') openConfirm('Extend Probation', 'Are you sure you want to extend the probation period? You will be asked to select a new end date.', 'Yes, Extend', 'warning');

  document.getElementById('confirm-btn-yes').onclick = function () {
    closeConfirm();
    if (!pendingEvalData) return;
    if (pendingEvalData.decision === 'Hire') executeProbationDecision(pendingEvalData.empId, 'Hire', pendingEvalData.notes, pendingEvalData.csrfToken);
    else if (pendingEvalData.decision === 'Reject') executeProbationDecision(pendingEvalData.empId, 'Reject', pendingEvalData.notes, pendingEvalData.csrfToken);
    else if (pendingEvalData.decision === 'Extend') openExtendProbationModal(pendingEvalData.empId, pendingEvalData.empName, pendingEvalData.csrfToken, pendingEvalData.notes);
    pendingEvalData = null;
  };
}

function executeProbationDecision(empId, decision, notes, csrfToken) {
  const btn = document.querySelector('#confirm-btn-yes');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="14"></i> Processing...`;
  lcIcons(btn);

  const data = { employee_id: empId, decision, notes, csrf_token: csrfToken };
  fetch('api/employees/submit_probation_eval.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        showNotification('Success', result.message, 'success');
        refreshProbationTable();
      } else {
        showNotification('Error', result.message, 'error');
      }
    })
    .catch(error => showNotification('Network Error', error.message, 'error'))
    .finally(() => {
      if (btn && btn.isConnected) {
        btn.disabled = false;
        btn.innerHTML = originalText;
        lcIcons(btn);
      }
    });
}

let pendingExtendNotes = '';
function openExtendProbationModal(empId, empName, csrfToken, existingNotes) {
  document.getElementById('extend-emp-id').value = empId;
  document.getElementById('extend-csrf-token').value = csrfToken;
  pendingExtendNotes = existingNotes || '';

  fetch(`api/employees/get_probation_end_date.php?employee_id=${empId}`)
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const endDate = res.data.end_date;
        document.getElementById('current-end-date').textContent = endDate;
        document.getElementById('new-end-date').value = endDate;
        document.getElementById('new-end-date').min = endDate;
      } else {
        showNotification('Error', 'Could not fetch probation end date.', 'error');
      }
    })
    .catch(err => showNotification('Error', 'Network error: ' + err.message, 'error'));

  openModal('modal-extend-probation');
}

function submitExtendProbation() {
  const empId = document.getElementById('extend-emp-id').value;
  const csrfToken = document.getElementById('extend-csrf-token').value;
  const newEndDate = document.getElementById('new-end-date').value;
  const currentEnd = document.getElementById('current-end-date').textContent;

  if (!newEndDate) {
    showNotification('Required', 'Please select a new end date.', 'warning');
    return;
  }
  if (newEndDate < currentEnd) {
    showNotification('Invalid Date', 'New end date must be on or after the current end date.', 'warning');
    return;
  }

  const btn = document.querySelector('#modal-extend-probation .btn-primary');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="14"></i> Extending...`;
  lcIcons(btn);

  const data = { employee_id: empId, decision: 'Extend', new_end_date: newEndDate, notes: pendingExtendNotes, csrf_token: csrfToken };
  fetch('api/employees/submit_probation_eval.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        showNotification('Success', result.message, 'success');
        closeModal('modal-extend-probation');
        refreshProbationTable();
      } else {
        showNotification('Error', result.message, 'error');
      }
    })
    .catch(error => showNotification('Network Error', error.message, 'error'))
    .finally(() => {
      if (btn && btn.isConnected && btn.disabled) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);
      }
    });
}

function refreshProbationTable() {
  const container = document.getElementById('tbl-probation');
  if (container && typeof container._fetchData === 'function') {
    container._fetchData();
  } else {
    if (typeof inited !== 'undefined') inited.delete('probation-tracker');
    if (typeof initPage === 'function') initPage('probation-tracker');
    else location.reload();
  }
}

// Attendance Matrix
const ATT_CODES = ['P', 'H', 'A', 'L', 'O'];

// Mock data for attendance matrix
const names = [
  'John Smith', 'Emily Johnson', 'Michael Williams', 'Sarah Brown', 'David Jones',
  'Lisa Garcia', 'Robert Miller', 'Maria Davis', 'James Rodriguez', 'Jennifer Martinez',
  'William Hernandez', 'Linda Lopez', 'Richard Gonzalez', 'Patricia Wilson', 'Charles Anderson',
  'Barbara Thomas', 'Joseph Taylor', 'Nancy Moore', 'Thomas Jackson', 'Betty Martin',
  'Christopher Lee', 'Helen Perez', 'Daniel Thompson', 'Sandra White', 'Matthew Harris',
  'Donna Clark', 'Anthony Lewis', 'Carol Robinson', 'Mark Walker', 'Michelle Hall',
  'Steven Allen', 'Laura Young', 'Paul King', 'Sharon Wright', 'Andrew Scott',
  'Kimberly Green', 'Joshua Baker', 'Deborah Adams', 'Kevin Nelson', 'Dorothy Carter'
];

const depts = [
  'Engineering', 'Sales', 'HR', 'Finance', 'Marketing', 'Operations'
];

function buildMatrix() {
  const m = document.getElementById('att-m-select').value;
  const y = document.getElementById('att-y-select').value;
  const deptFilter = document.getElementById('att-dept-select').value;
  const nameInput = document.getElementById('as-input-att-name').value.toLowerCase().trim();

  if (m === "" || y === "") {
    showNotification("Input Required", "Please select both a target Month and Fiscal Year to generate the registry.", "warning");
    return;
  }

  const month = parseInt(m);
  const year = parseInt(y);
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const now = new Date();

  let headHtml = `<tr><th class="sticky-emp sticky-head"><div class="id-label-theme">Employee Identity</div></th>`;
  for (let d = 1; d <= daysInMonth; d++) {
    const dObj = new Date(year, month, d);
    const dayName = dObj.toLocaleDateString('en-US', { weekday: 'short' });
    const isToday = (d === now.getDate() && month === now.getMonth() && year === now.getFullYear());
    headHtml += `<th class="sticky-head att-day-col ${isToday ? 'is-today' : ''}"><span class="ledger-d-num">${d}</span><span class="ledger-d-day">${dayName}</span></th>`;
  }
  headHtml += `</tr>`;
  document.getElementById('ledger-head').innerHTML = headHtml;

  let filteredList = names.map((name, idx) => ({
    name,
    dept: depts[idx % depts.length],
    id: `EMP-10${idx + 100}`
  })).filter(emp => {
    const matchesDept = (deptFilter === 'All' || emp.dept === deptFilter);
    const matchesName = nameInput === "" || emp.name.toLowerCase().includes(nameInput);
    return matchesDept && matchesName;
  });

  let bodyHtml = '';
  if (filteredList.length === 0) {
    bodyHtml = `<tr><td colspan="${daysInMonth + 1}" style="text-align:center; padding: 60px; color: var(--muted);">No matching records found.</td></tr>`;
  } else {
    filteredList.slice(0, 40).forEach((emp) => {
      const initials = emp.name.split(' ').map(n => n[0]).join('');
      bodyHtml += `<tr>
        <td class="sticky-emp">
          <div class="flex-row" style="gap:10px">
            <div class="avatar avatar-sm">${initials}</div>
            <div style="line-height:1.2">
              <div style="font-size:11px; font-weight:800;">${emp.name}</div>
              <div style="font-size:9px; color:var(--muted); font-family:'JetBrains Mono'">${emp.id} | ${emp.dept}</div>
            </div>
          </div>
        </td>`;
      for (let d = 1; d <= daysInMonth; d++) {
        const dObj = new Date(year, month, d);
        const isSat = dObj.getDay() === 6;
        const isSun = dObj.getDay() === 0;
        const isFuture = dObj > now;
        let status = isSat ? 'H' : (isSun ? 'O' : 'P');
        bodyHtml += `<td class="att-cell st-${status}">
          <div class="status-pill ${isFuture ? 'future' : ''}" onclick="${isFuture ? '' : 'cycleStatus(this)'}">${isFuture ? '' : status}</div>
        </td>`;
      }
      bodyHtml += `</tr>`;
    });
  }

  document.getElementById('ledger-body').innerHTML = bodyHtml;
  document.getElementById('ledger-container').style.display = 'block';
  document.getElementById('att-meta-header').style.display = 'flex';
  document.getElementById('ledger-empty').style.display = 'none';
  lcIcons(document.getElementById('p-attendance'));
}

function filterAttMatrix(val) {
  const q = val.toLowerCase();
  document.querySelectorAll('#ledger-body tr').forEach(row => {
    const name = row.querySelector('.sticky-emp').textContent.toLowerCase();
    row.style.display = name.includes(q) ? '' : 'none';
  });
}

function cycleStatus(el) {
  let current = el.textContent.trim();
  let nextIdx = (ATT_CODES.indexOf(current) + 1) % ATT_CODES.length;
  let next = ATT_CODES[nextIdx];

  const parent = el.parentElement;
  ATT_CODES.forEach(code => parent.classList.remove('st-' + code));
  parent.classList.add('st-' + next);
  el.textContent = next;
}

// Document Vault
const VAULT_SCHEMA = [
  { id: 'contract', name: 'Signed Employment Contract', cat: 'Legal' },
  { id: 'cv', name: 'Curriculum Vitae (CV)', cat: 'Identity' },
  { id: 'academic', name: 'Academic Credentials', cat: 'Education' },
  { id: 'clearance', name: 'Clearance / Release Letter', cat: 'History' },
  { id: 'experience', name: 'Experience Letters', cat: 'History' },
  { id: 'coc', name: 'Certificate of Competence (COC)', cat: 'Professional' },
  { id: 'guarantor', name: 'Guarantor Form & ID', cat: 'Legal' },
  { id: 'nda', name: 'Confidentiality / NDA Agreement', cat: 'Compliance' },
  { id: 'handbook', name: 'Acknowledgments', cat: 'Compliance' },
  { id: 'national_id', name: 'National ID / Passport Copy', cat: 'Identity' },
  { id: 'tin', name: 'TIN Certification Document', cat: 'Tax' },
  { id: 'medical', name: 'Health & Fitness Clearance', cat: 'Compliance' }
];

function openEmployeeVault(name, id) {
  pendingEmployeeVaultData = { name, id };
  goPage('employee-vault');
}

function updateEmploymentFields(type) {
  const container = document.getElementById('dynamic-employment-fields');
  if (!container) return;

  if (!type) {
    container.style.display = 'none';
    container.innerHTML = '';
    return;
  }

  container.style.display = 'grid';
  let html = '';

  const probationHtml = `
    <div class="form-group">
      <label>Probation Duration (Days) *</label>
      <div style="display: flex; gap: 10px; align-items: center;">
        <input type="number" id="o-probation_val" class="form-ctrl master-req" placeholder="e.g. 90" value="60" style="width: 100px;">
        <span style="font-size: 0.75rem; color: var(--muted);">Total days from hire date</span>
        <input type="hidden" id="o-probation" name="probation_period" value="">
      </div>
    </div>`;

  const reportingHtml = `
    <div class="form-group" style="grid-column: span 2;">
      <label>Reporting To</label>
      <div class="as-combo-container">
        <input type="text" id="o-reports" class="form-ctrl" data-dropdown-type="employees"
          placeholder="Search manager..." onfocus="showAsDrop('as-drop-reports')"
          oninput="filterAsDrop('o-reports','as-drop-reports')" autocomplete="off">
        <div class="as-combo-results" id="as-drop-reports"></div>
      </div>
    </div>`;

  switch (type) {
    case 'full-time':
      html = `
        <div class="form-group"><label>Hiring Date *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
        ${probationHtml}
        ${reportingHtml}`;
      break;
    case 'contract':
      html = `
        <div class="form-group"><label>Contract Start *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
        <div class="form-group"><label>Contract End *</label><input type="date" class="form-ctrl master-req" id="o-end-date"></div>
        ${probationHtml}
        ${reportingHtml}`;
      break;
    case 'part-time':
      html = `
        <div class="form-group"><label>Hiring Date *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
        <div class="form-group"><label>Hours Per Week</label><input type="number" class="form-ctrl" id="o-hours" placeholder="e.g. 20"></div>
        ${probationHtml}
        ${reportingHtml}`;
      break;
    case 'internship':
      html = `
        <div class="form-group"><label>Internship Start *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
        <div class="form-group"><label>Internship End *</label><input type="date" class="form-ctrl master-req" id="o-end-date"></div>
        <div class="form-group" style="grid-column: span 1;"><label>Academic Institution</label><input type="text" class="form-ctrl" id="o-institution" placeholder="Ex: Addis Ababa University" maxlength="200"></div>
        ${reportingHtml.replace('Reporting To', 'Assigned Mentor')}`;
      break;
    case 'temporary':
      html = `
        <div class="form-group"><label>Project Name *</label><input type="text" class="form-ctrl master-req" id="o-project" placeholder="e.g. Infrastructure Audit" maxlength="200"></div>
        <div class="form-group"><label>Assignment Start *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
        ${reportingHtml.replace('Reporting To', 'Project Supervisor')}`;
      break;
    default:
      html = `<p style="color:var(--muted); grid-column:span 3;">No additional fields required for this employment type.</p>`;
  }

  container.innerHTML = html;
  lcIcons(container);

  container.querySelectorAll('input, select').forEach(input => input.addEventListener('input', validateMasterRecord));
  enforceDropdownOnBlur('o-reports');

  const pVal = document.getElementById('o-probation_val');
  const pHidden = document.getElementById('o-probation');
  if (pVal && pHidden) {
    const updateHidden = () => {
      const days = pVal.value.trim();
      if (days === '0' || days === '') pHidden.value = 'No probation';
      else pHidden.value = days + ' Days';
      validateMasterRecord();
    };
    pVal.addEventListener('input', updateHidden);
    updateHidden();
  }

  validateMasterRecord();
}

// Notifications
function showNotification(title, message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `ydy-toast toast-${type}`;

  const icons = { success: 'check-circle', error: 'alert-circle', warning: 'alert-triangle', info: 'info' };

  toast.innerHTML = `
    <div class="toast-icon"><i data-lucide="${icons[type]}" size="18"></i></div>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      <div class="toast-msg">${message}</div>
    </div>
    <button class="toast-close" onclick="this.closest('.ydy-toast').remove()" title="Dismiss"><i data-lucide="x" size="16"></i></button>
    <div class="toast-progress"><div class="toast-progress-fill"></div></div>`;

  container.appendChild(toast);
  lucide.createIcons({ nodes: [toast] });

  const fill = toast.querySelector('.toast-progress-fill');
  fill.style.transition = 'transform 4s linear';
  fill.style.transform = 'scaleX(0)';

  const timeoutId = setTimeout(() => {
    toast.classList.add('exit');
    setTimeout(() => toast.remove(), 300);
  }, 4000);

  toast._timeoutId = timeoutId;
  const originalRemove = toast.remove;
  toast.remove = function () {
    if (this._timeoutId) clearTimeout(this._timeoutId);
    originalRemove.call(this);
  };
}

// Confirm Modal
function openConfirm(title, message, btnText = "Confirm", type = "default") {
  const modal = document.getElementById('confirm-modal');
  document.getElementById('confirm-title').innerText = title;
  document.getElementById('confirm-body').innerText = message;
  const yesBtn = document.getElementById('confirm-btn-yes');
  yesBtn.innerText = btnText;

  const iconEl = document.getElementById('confirm-icon');
  const iconContainer = document.getElementById('confirm-icon-container');

  yesBtn.className = 'btn';
  iconContainer.className = '';
  iconContainer.style.background = '';
  iconContainer.style.color = '';

  switch (type) {
    case 'danger':
      yesBtn.classList.add('btn-danger');
      iconEl.setAttribute('data-lucide', 'alert-triangle');
      iconContainer.style.background = 'var(--danger-bg)';
      iconContainer.style.color = 'var(--danger)';
      break;
    case 'success':
      yesBtn.classList.add('btn-success');
      iconEl.setAttribute('data-lucide', 'check-circle');
      iconContainer.style.background = 'var(--success-bg)';
      iconContainer.style.color = 'var(--success)';
      break;
    case 'warning':
      yesBtn.classList.add('btn-warning');
      iconEl.setAttribute('data-lucide', 'alert-circle');
      iconContainer.style.background = 'var(--warning-bg)';
      iconContainer.style.color = 'var(--warning)';
      break;
    default:
      yesBtn.classList.add('btn-primary');
      iconEl.setAttribute('data-lucide', 'help-circle');
      iconContainer.style.background = 'var(--primary-light)';
      iconContainer.style.color = 'var(--primary)';
  }

  lucide.createIcons({ icons: { [iconEl.getAttribute('data-lucide')]: iconEl } });
  modal.classList.add('open');
}

function closeConfirm() {
  document.getElementById('confirm-modal').classList.remove('open');
}

function handleLogout() {
  openConfirm("Logout Session", "Are you sure you want to Logout now?", "Yes, Logout");
  document.getElementById('confirm-btn-yes').onclick = function () {
    showNotification("Security", "Ending secure session. Redirecting...", "danger");
    setTimeout(() => { window.location.href = 'login/logout.php'; }, 1500);
    closeConfirm();
  };
}

// Avatar Handling
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const maxSize = 5 * 1024 * 1024;

    if (file.size > maxSize) {
      showNotification("File Too Large", "The image exceeds the 5MB limit. Please upload a smaller photo.", "error");
      input.value = "";
      return;
    }
    if (!file.type.startsWith('image/')) {
      showNotification("Invalid File", "Please select a valid image file (JPG, PNG, WebP).", "error");
      input.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = e => {
      const preview = document.getElementById('avatar-img-output');
      const icon = document.getElementById('placeholder-icon');
      const box = document.getElementById('avatar-preview-box');
      const removeBtn = document.getElementById('avatar-remove-btn');

      preview.src = e.target.result;
      preview.style.display = 'block';
      icon.style.display = 'none';
      if (removeBtn) removeBtn.style.display = 'flex';
      box.style.borderStyle = 'solid';
      box.style.borderColor = 'var(--primary)';
    };
    reader.readAsDataURL(file);
  }
}

function cancelAvatar(e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  const input = document.getElementById('avatar-upload');
  const preview = document.getElementById('avatar-img-output');
  const icon = document.getElementById('placeholder-icon');
  const box = document.getElementById('avatar-preview-box');
  const removeBtn = document.getElementById('avatar-remove-btn');

  if (input) input.value = "";
  if (preview) { preview.src = ""; preview.style.display = 'none'; }
  if (icon) icon.style.display = 'block';
  if (removeBtn) removeBtn.style.display = 'none';
  if (box) { box.style.borderStyle = 'dashed'; box.style.borderColor = '#cbd5e1'; }
}

// ── DROPDOWN SYSTEM ──
const dropdownCache = {};

function clearDropdownCache(type) {
  Object.keys(dropdownCache).forEach(key => {
    if (key.startsWith(type + ':')) delete dropdownCache[key];
  });
}

// Global delegation for dropdown item clicks
document.addEventListener('click', function (e) {
  const item = e.target.closest('.as-res-item');
  if (!item) return;
  const container = item.closest('.as-combo-results');
  if (!container) return;
  e.stopPropagation();

  let inputId = container.id.replace('as-drop-', 'as-input-');
  let inputEl = document.getElementById(inputId);
  if (!inputEl) {
    inputEl = container.closest('.as-combo-container')?.querySelector('input');
    if (inputEl) inputId = inputEl.id;
  }
  if (!inputEl) return;

  const value = item.dataset.value;
  const displayText = item.textContent.trim();
  if (value) selectAsItemWithValue(inputId, container.id, displayText, value);
});

// Close dropdowns on outside click
document.addEventListener('mousedown', function (e) {
  if (!e.target.closest('.as-combo-container')) {
    document.querySelectorAll('.as-combo-results').forEach(d => d.classList.remove('active'));
  }
}, { passive: true });

function filterAsDrop(inputId, dropId) {
  const inputEl = document.getElementById(inputId);
  if (!inputEl) return;
  const searchTerm = inputEl.value.toLowerCase();

  let hiddenId = inputId + '_id';
  let hiddenEl = document.getElementById(hiddenId);
  if (hiddenEl) hiddenEl.value = '';

  const container = document.getElementById(dropId);
  if (!container) return;

  const items = container.querySelectorAll('.as-res-item');
  let hasVisible = false;
  items.forEach(item => {
    const text = item.textContent.toLowerCase();
    if (text.includes(searchTerm)) { item.style.display = 'block'; hasVisible = true; }
    else item.style.display = 'none';
  });

  const existingMsg = container.querySelector('.no-result-msg');
  if (existingMsg) existingMsg.remove();

  if (!hasVisible) {
    const msg = document.createElement('div');
    msg.className = 'as-res-item no-result-msg';
    msg.textContent = 'No matching results';
    msg.style.color = 'var(--muted)';
    container.appendChild(msg);
  }

  container.classList.add('active');
}

window.selectAsItemWithValue = function (inputId, dropId, displayText, value) {
  const inputEl = document.getElementById(inputId);
  if (!inputEl) return;

  inputEl.value = displayText;
  inputEl.classList.remove('field-error');

  let hiddenId = inputId + '_id';
  let hiddenEl = document.getElementById(hiddenId);
if (!hiddenEl) {
    hiddenEl = document.createElement('input');
    hiddenEl.type = 'hidden';
    hiddenEl.id = hiddenId;
    hiddenEl.name = hiddenId;    // ADD THIS LINE
    inputEl.parentNode.appendChild(hiddenEl);
}
hiddenEl.value = value;

  const dropContainer = document.getElementById(dropId);
  if (dropContainer) dropContainer.classList.remove('active');

  if (typeof validateMasterRecord === 'function') validateMasterRecord();

  if (inputId === 'o-dept') {
    const posInput = document.getElementById('o-pos');
    if (posInput) {
      posInput.value = '';
      posInput.placeholder = 'Loading positions...';
      posInput.disabled = false;
      posInput.dataset.departmentId = value;
      const posHidden = document.getElementById('o-pos_id');
      if (posHidden) posHidden.value = '';
    }
    reloadJobPositionsDropdown(value);
  }
  if (inputId === 'o-etype') {
    const rawText = displayText.trim().toLowerCase().replace(/[-\s]+/g, '');
    let normalized = null;
    if (rawText.includes('fulltime') || rawText.includes('permanent')) normalized = 'full-time';
    else if (rawText.includes('contract')) normalized = 'contract';
    else if (rawText.includes('parttime')) normalized = 'part-time';
    else if (rawText.includes('intern')) normalized = 'internship';
    else if (rawText.includes('temp')) normalized = 'temporary';
    if (typeof updateEmploymentFields === 'function') updateEmploymentFields(normalized);
  }
};

function showAsDrop(dropdownId) {
  const dropContainer = document.getElementById(dropdownId);
  let inputId = dropdownId.replace('as-drop-', 'as-input-');
  let inputEl = document.getElementById(inputId);

  if (!inputEl && dropContainer) {
    inputEl = dropContainer.closest('.as-combo-container')?.querySelector('input');
    if (inputEl) inputId = inputEl.id;
  }
  if (!inputEl || !dropContainer) return;

  document.querySelectorAll('.as-combo-results').forEach(d => {
    if (d.id !== dropdownId) d.classList.remove('active');
  });

  const existingItems = dropContainer.querySelectorAll('.as-res-item:not(.no-result-msg)');
  if (existingItems.length > 0) {
    dropContainer.classList.add('active');
    highlightSelectedInDropdown(dropContainer, inputEl.value);
    return;
  }

  let type = inputEl?.getAttribute('data-dropdown-type');
  if (!type) {
    if (dropdownId.includes('branch')) type = 'branches';
    else if (dropdownId.includes('pos') || dropdownId.includes('job')) type = 'job_positions';
    else if (dropdownId.includes('emp') || dropdownId.includes('custodian') || dropdownId.includes('manager')) type = 'employees';
    else if (dropdownId.includes('employment') || dropdownId.includes('etype')) type = 'employment_types';
    else type = 'employees';
  }

  if (dropdownId === 'as-drop-dept' || dropdownId === 'as-drop-branch') {
    Object.keys(dropdownCache).forEach(key => {
      if (key.startsWith(type + ':')) delete dropdownCache[key];
    });
    populateAsDrop(dropdownId, type, '', inputEl.value);
    return;
  }

  if (dropdownId === 'as-drop-pos') {
    const posInput = document.getElementById('o-pos');
    const deptId = posInput?.dataset.departmentId || document.getElementById('o-dept_id')?.value;
    if (!deptId) {
      dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--warning);">Please select a department first</div>';
      dropContainer.classList.add('active');
      return;
    }
    reloadJobPositionsDropdown(deptId);
    return;
  }

  const searchTerm = inputEl?.value || '';
  populateAsDrop(dropdownId, type, searchTerm, inputEl.value);
}

function highlightSelectedInDropdown(container, selectedText) {
  if (!selectedText) return;
  const items = container.querySelectorAll('.as-res-item');
  items.forEach(item => {
    item.classList.remove('selected');
    if (item.textContent.trim() === selectedText) {
      item.classList.add('selected');
      setTimeout(() => item.scrollIntoView({ block: 'nearest' }), 10);
    }
  });
}

async function populateAsDrop(dropdownId, type, searchTerm = '', selectedValue = '') {
  const dropContainer = document.getElementById(dropdownId);
  if (!dropContainer) return;

  dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">Loading...</div>';
  dropContainer.classList.add('active');

  const cacheKey = `${type}:${searchTerm}`;
  if (dropdownCache[cacheKey]) {
    renderDropdownItems(dropContainer, dropdownCache[cacheKey], selectedValue);
    return;
  }

  try {
    const url = `api/1common/fetch_dropdown.php?type=${encodeURIComponent(type)}&search=${encodeURIComponent(searchTerm)}`;
    const response = await fetch(url);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const result = await response.json();

    if (result.success && result.data) {
      dropdownCache[cacheKey] = result.data;
      renderDropdownItems(dropContainer, result.data, selectedValue);
    } else {
      dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">No results found</div>';
    }
  } catch (err) {
    dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--danger);">Error loading data</div>';
  }
}

function renderDropdownItems(container, items, selectedValue = '') {
  container.innerHTML = '';
  items.forEach(item => {
    const div = document.createElement('div');
    div.className = 'as-res-item';
    div.textContent = item.label;
    div.dataset.value = item.value;
    if (selectedValue && item.label === selectedValue) {
      div.classList.add('selected');
      setTimeout(() => div.scrollIntoView({ block: 'nearest' }), 10);
    }
    container.appendChild(div);
  });
}

function toggleStaticDrop(dropId) {
  const drop = document.getElementById(dropId);
  if (!drop) return;
  document.querySelectorAll('.as-combo-results').forEach(d => {
    if (d.id !== dropId) d.classList.remove('active');
  });
  drop.classList.toggle('active');
}

window.selectAsItem = function (inputId, dropId, name) {
  const el = document.getElementById(inputId);
  if (el) { el.value = name; el.classList.remove('field-error'); }
  const drop = document.getElementById(dropId);
  if (drop) drop.classList.remove('active');
  if (typeof validateMasterRecord === 'function') validateMasterRecord();
};

window.selectMonth = function (name, val) {
  document.getElementById('att-m-display').value = name;
  document.getElementById('att-m-select').value = val;
  document.getElementById('as-drop-month').classList.remove('active');
};
window.selectYear = function (val) {
  document.getElementById('att-y-display').value = val;
  document.getElementById('att-y-select').value = val;
  document.getElementById('as-drop-year').classList.remove('active');
};
window.selectAttDept = function (name, val) {
  document.getElementById('att-dept-display').value = name;
  document.getElementById('att-dept-select').value = val;
  document.getElementById('as-drop-att-dept').classList.remove('active');
};

function reloadJobPositionsDropdown(departmentId) {
  const dropContainer = document.getElementById('as-drop-pos');
  const posInput = document.getElementById('o-pos');
  if (!dropContainer) return;

  Object.keys(dropdownCache).forEach(key => {
    if (key.startsWith('job_positions:')) delete dropdownCache[key];
  });

  dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">Loading...</div>';
  dropContainer.classList.add('active');

  fetch(`api/1common/fetch_dropdown.php?type=job_positions&department_id=${departmentId}`)
    .then(r => r.json())
    .then(result => {
      if (result.success && result.data && result.data.length > 0) {
        renderDropdownItems(dropContainer, result.data);
        if (posInput) {
          posInput.disabled = false;
          posInput.placeholder = 'Select Job Position...';
        }
      } else {
        dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">No positions found for this department</div>';
        if (posInput) {
          posInput.disabled = true;
          posInput.placeholder = 'No job positions available';
          posInput.value = '';
          const posHidden = document.getElementById('o-pos_id');
          if (posHidden) posHidden.value = '';
        }
      }
    })
    .catch(err => {
      dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--danger);">Error loading data</div>';
      if (posInput) {
        posInput.disabled = true;
        posInput.placeholder = 'Error loading positions';
      }
    });
}

// ── SERVER PAGINATED TABLE FACTORY ──
function initServerPaginatedTable(containerId, apiUrl, { columns, perPage = 15, searchPlaceholder = 'Search...' }) {
  const container = document.getElementById(containerId);
  if (!container) return;
  delete container.dataset.built;

  let currentPage = 1, totalPages = 1, totalRecords = 0, searchTerm = '', searchTimeout = null, currentRows = [];

  const buildBaseStructure = () => {
    container.innerHTML = `
      <div class="filter-bar">
        <div class="search-container"><div class="search-inner">
          <i data-lucide="search" class="search-lead-icon"></i>
          <input type="text" placeholder="${searchPlaceholder}" id="${containerId}-search">
          <button class="btn-search-ghost" id="${containerId}-clear-search" style="display:none;"><i data-lucide="x" size="14"></i></button>
        </div></div>
      </div>
      <div class="table-wrap">
        <table class="tbl">
          <thead id="${containerId}-thead"></thead>
          <tbody id="${containerId}-tbody"></tbody>
        </table>
      </div>
      <div class="pagination">
        <span class="pagination-info" id="${containerId}-info"></span>
        <div class="pagination-btns" id="${containerId}-pagination"></div>
      </div>`;
    lcIcons(container);

    document.getElementById(`${containerId}-thead`).innerHTML = `<tr>${columns.map(c => `<th>${c.label}</th>`).join('')}</tr>`;
  };

  buildBaseStructure();

  const updateDisplay = () => {
    const tbody = document.getElementById(`${containerId}-tbody`);
    const infoSpan = document.getElementById(`${containerId}-info`);
    const paginationDiv = document.getElementById(`${containerId}-pagination`);
    const searchInput = document.getElementById(`${containerId}-search`);
    const clearBtn = document.getElementById(`${containerId}-clear-search`);

    if (searchInput) searchInput.value = searchTerm;
    if (clearBtn) clearBtn.style.display = searchTerm ? 'block' : 'none';

    if (currentRows.length > 0) {
      tbody.innerHTML = currentRows.map(row =>
        `<tr>${columns.map(c => {
          let v = row[c.key] !== undefined ? row[c.key] : '—';
          if (c.render) v = c.render(v, row);
          return `<td>${v}</td>`;
        }).join('')}</tr>`
      ).join('');
    } else {
      let emptyRow = `<tr>${columns.map((col, idx) => idx === 0 ? `<td style="text-align:left; padding:16px 20px; color:var(--muted); font-weight:500;">No records found</td>` : `<td></td>`).join('')}</tr>`;
      tbody.innerHTML = emptyRow;
    }

    const start = totalRecords ? (currentPage - 1) * perPage + 1 : 0;
    const end = Math.min(currentPage * perPage, totalRecords);
    infoSpan.textContent = `Showing ${totalRecords ? start : 0}–${end} of ${totalRecords}`;

    let pgBtns = `<button class="pg-btn" onclick="changeServerPage('${containerId}', -1)" ${currentPage <= 1 ? 'disabled' : ''}>‹</button>`;
    for (let i = 1; i <= totalPages; i++) {
      if (totalPages <= 7 || i === 1 || i === totalPages || Math.abs(i - currentPage) <= 1) {
        pgBtns += `<button class="pg-btn ${i === currentPage ? 'active' : ''}" onclick="goToServerPage('${containerId}', ${i})">${i}</button>`;
      } else if (Math.abs(i - currentPage) === 2) {
        pgBtns += `<button class="pg-btn" disabled>…</button>`;
      }
    }
    pgBtns += `<button class="pg-btn" onclick="changeServerPage('${containerId}', 1)" ${currentPage >= totalPages ? 'disabled' : ''}>›</button>`;
    paginationDiv.innerHTML = pgBtns;
    lcIcons(tbody);
  };

  const fetchData = () => {
    container.style.opacity = '0.5';
    const url = `${apiUrl}?page=${currentPage}&limit=${perPage}&search=${encodeURIComponent(searchTerm)}`;
    fetch(url)
      .then(r => r.json())
      .then(res => {
        if (!res.success) throw new Error(res.message);
        currentRows = res.data || [];
        totalRecords = res.pagination?.total || 0;
        totalPages = res.pagination?.totalPages || 1;
        updateDisplay();
        container.style.opacity = '1';
      })
      .catch(err => {
        document.getElementById(`${containerId}-tbody`).innerHTML = `<tr><td colspan="${columns.length}" style="padding:20px;color:#dc2626;">Error: ${err.message}</td></tr>`;
        container.style.opacity = '1';
      });
  };

  container._fetchData = fetchData;

  const searchInput = document.getElementById(`${containerId}-search`);
  const clearBtn = document.getElementById(`${containerId}-clear-search`);
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        searchTerm = e.target.value;
        currentPage = 1;
        fetchData();
      }, 300);
    });
  }
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      searchTerm = '';
      if (searchInput) searchInput.value = '';
      currentPage = 1;
      fetchData();
    });
  }

  window[`changeServerPage_${containerId}`] = (dir) => {
    const newPage = currentPage + dir;
    if (newPage >= 1 && newPage <= totalPages) {
      currentPage = newPage;
      fetchData();
    }
  };
  window[`goToServerPage_${containerId}`] = (page) => {
    if (page >= 1 && page <= totalPages) {
      currentPage = page;
      fetchData();
    }
  };

  fetchData();
}

function changeServerPage(containerId, dir) {
  const fn = window[`changeServerPage_${containerId}`];
  if (fn) fn(dir);
}
function goToServerPage(containerId, page) {
  const fn = window[`goToServerPage_${containerId}`];
  if (fn) fn(page);
}

// ── ROLES & PERMISSIONS ──
const mods = [
  { id: 'm-org', n: 'Company & Structure', i: 'building-2', subs: ['Company Profile', 'Organization Chart', 'Departments', 'Job Positions', 'Branch Offices'] },
  { id: 'm-emp', n: 'Employees', i: 'users', subs: ['Employee Profile', 'Employment Types', 'Probation Tracker', 'Contract Renewals', 'Former employees', 'Attachment Vault', 'Asset Tracking'] },
  { id: 'm-rec', n: 'Talent Acquisition', i: 'user-plus', subs: ['Add Job Vacancies', 'Job Applicant\'s List', 'Interview Tracker', 'Internship Management'] },
  { id: 'm-move', n: 'Employee Movement', i: 'arrow-right-left', subs: ['Promote/Demote', 'Department Transfers'] },
  { id: 'm-att', n: 'Attendance', i: 'clock', subs: ['Record attendance', 'Daily Attendance', 'Attendance Reports'] },
  { id: 'm-leave', n: 'Leave Management', i: 'calendar-days', subs: ['Leave Types', 'Leave Requests', 'Leave Entitlement'] },
  { id: 'm-ben', n: 'Benefits', i: 'heart-pulse', subs: ['Medical Claims', 'Overtime Requests'] },
  { id: 'm-comp', n: 'Compliance & Exit', i: 'shield-alert', subs: ['Disciplinary Actions', 'Resignations', 'Separation & Exit', 'Exit Clearance'] },
  { id: 'm-train', n: 'Training & Dev', i: 'graduation-cap', subs: ['Training Needs Analysis', 'Training Schedule'] },
  { id: 'm-perf', n: 'Performance', i: 'trending-up', subs: ['Performance Reviews', '360° Feedback'] },
  { id: 'm-sys', n: 'System Admin', i: 'settings-2', subs: ['User Management', 'Roles & Permissions', 'Audit Logs'] }
];

let currentAccessMode = 'role';

function initRoles() {
  selRole(document.querySelector('.role-pill-v2'), 'Super Admin');
}

function switchAccessMode(mode) {
  currentAccessMode = mode;
  const btnRole = document.getElementById('btn-mode-role');
  const btnUser = document.getElementById('btn-mode-user');
  const sideRole = document.getElementById('side-role-list');
  const sideUser = document.getElementById('side-user-search');
  const targetLabel = document.getElementById('perm-target-label');
  const warning = document.getElementById('override-warning');

  if (mode === 'role') {
    btnRole.style.background = 'var(--primary-light)'; btnRole.style.color = 'var(--primary)';
    btnUser.style.background = 'transparent'; btnUser.style.color = 'var(--muted)';
    sideRole.style.display = 'block';
    sideUser.style.display = 'none';
    targetLabel.textContent = "Standard Role:";
    warning.style.display = 'none';
    selRole(document.querySelector('#side-role-list .role-pill-v2'), 'Super Admin');
  } else {
    btnUser.style.background = 'var(--primary-light)'; btnUser.style.color = 'var(--primary)';
    btnRole.style.background = 'transparent'; btnRole.style.color = 'var(--muted)';
    sideRole.style.display = 'none';
    sideUser.style.display = 'block';
    targetLabel.textContent = "Individual Override:";
    warning.style.display = 'inline-flex';
    document.getElementById('active-role-name').textContent = "No User Selected";
    document.getElementById('perm-grid').innerHTML = `<tr><td colspan="4" style="text-align:center; padding:40px; color:var(--muted)">Search and select a user to define individual permissions.</td></tr>`;
    document.getElementById('selected-user-card').style.display = 'none';
  }
  lcIcons();
}

function selectUserForPerms(name) {
  document.getElementById('as-input-perm-user').value = name;
  document.getElementById('as-drop-perm-user').classList.remove('active');
  document.getElementById('selected-user-card').style.display = 'block';
  document.getElementById('perm-user-name').textContent = name;
  document.getElementById('perm-user-id').textContent = "E-" + Math.floor(1000 + Math.random() * 9000);
  document.getElementById('perm-user-avatar').textContent = name.split(' ').map(n => n[0]).join('');
  document.getElementById('active-role-name').textContent = name;
  renderPermissionGrid(false);
}

function renderPermissionGrid(isSuperAdmin) {
  const grid = document.getElementById('perm-grid');
  let html = '';

  mods.forEach(m => {
    html += `<tr style="background: #f8fafc; border-bottom: 2px solid var(--border)">
      <td style="text-align:center; color:var(--primary)"><i data-lucide="${m.i}" size="14"></i></td>
      <td><b style="font-size:.75rem; text-transform:uppercase; letter-spacing:0.05em;">${m.n}</b></td>
      <td style="font-size:.65rem; color:var(--muted)">Enable/Disable entire sidebar category.</td>
      <td style="text-align:center">
        <label class="switch">
          <input type="checkbox" class="parent-check" data-module-id="${m.id}" onchange="toggleModuleGroup('${m.id}', this.checked)" ${isSuperAdmin ? 'checked disabled' : 'checked'}>
          <span class="slider"></span>
        </label>
      </td>
    </tr>`;

    m.subs.forEach(sub => {
      html += `<tr class="child-row-${m.id}">
        <td></td>
        <td style="padding-left: 30px;">
          <div style="display:flex; align-items:center; gap:8px;">
            <span style="width:6px; height:6px; border-radius:50%; background:var(--primary);"></span>
            <span style="font-size:.75rem; font-weight:600;">${sub}</span>
          </div>
        </td>
        <td style="font-size:.65rem; color:var(--muted)">Individual access to the ${sub} page.</td>
        <td style="text-align:center">
          <label class="switch">
            <input type="checkbox" class="child-check" data-parent-ref="${m.id}" onchange="checkParentStatus('${m.id}')" ${isSuperAdmin ? 'checked disabled' : 'checked'}>
            <span class="slider"></span>
          </label>
        </td>
      </tr>`;
    });
  });

  grid.innerHTML = html;
  lcIcons(grid);
}

function toggleModuleGroup(moduleId, isChecked) {
  document.querySelectorAll(`input[data-parent-ref="${moduleId}"]`).forEach(child => {
    child.checked = isChecked;
    child.closest('tr').style.opacity = isChecked ? "1" : "0.5";
  });
}

function checkParentStatus(moduleId) {
  const parent = document.querySelector(`input[data-module-id="${moduleId}"]`);
  const children = document.querySelectorAll(`input[data-parent-ref="${moduleId}"]`);
  const anyChecked = Array.from(children).some(c => c.checked);
  if (anyChecked && parent && !parent.checked) parent.checked = true;
}

function selRole(el, name) {
  document.querySelectorAll('#side-role-list .role-pill-v2').forEach(p => p.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('active-role-name').textContent = name;
  renderPermissionGrid(name === 'Super Admin');
}

function savePermissionChanges() {
  const target = document.getElementById('active-role-name').textContent;
  const btn = document.querySelector('#p-roles-permissions .btn-primary');
  const indicator = document.getElementById('save-status-indicator');

  if (target === "No User Selected") {
    showNotification("Action Denied", "Please select a role or user first.", "error");
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="14"></i> Syncing...`;
  lcIcons(btn);

  setTimeout(() => {
    btn.innerHTML = originalHtml;
    lcIcons(btn);
    indicator.style.display = 'flex';
    showNotification("Role Updated", `Access schema for ${target} has been updated.`, "success");
    setTimeout(() => { indicator.style.display = 'none'; }, 3000);
  }, 1000);
}

// Hash-based navigation
window.addEventListener('hashchange', () => {
  const currentHash = window.location.hash.replace('#', '');
  if (currentHash) goPage(currentHash);
});
   
// --- SHIFT DEFINITIONS (Instant) ---
function renderShifts() {
    const container = document.getElementById('shift-dynamic-container');
    if (!container) return;
    
    container.className = "bento-grid"; // 12-column layout
    
    // Update Tab UI
    document.getElementById('btn-tab-def').className = 'btn btn-primary btn-sm';
    document.getElementById('btn-tab-assign').className = 'btn btn-secondary btn-sm';
    document.getElementById('btn-tab-assign').style.border = 'none';

    const mockShifts = [
        { name: 'Morning Shift', start: '08:00', end: '17:00', dur: '9h/day', grace: '15min grace', emp: '12 employees' },
        { name: 'Afternoon Shift', start: '13:00', end: '22:00', dur: '9h/day', grace: '15min grace', emp: '8 employees' },
        { name: 'Night Shift', start: '22:00', end: '07:00', dur: '9h/day', grace: '15min grace', emp: '5 employees' }
    ];

    container.innerHTML = mockShifts.map(s => `
        <div class="card" style="grid-column: span 4; border-top: 4px solid var(--primary); display: flex; flex-direction: column;">
            <div class="card-body">
                <h3 class="card-title" style="margin-bottom: 4px;">${s.name}</h3>
                <p style="font-family: 'JetBrains Mono'; color: var(--muted); font-size: 0.85rem; margin-bottom: 16px;">${s.start} &rarr; ${s.end}</p>
                <div class="flex-row" style="gap: 8px; flex-wrap: wrap;">
                    <span class="badge badge-primary" style="background: var(--primary-light); color: var(--primary);">${s.dur}</span>
                    <span class="badge badge-primary" style="background: var(--primary-light); color: var(--primary);">${s.grace}</span>
                    <span class="badge badge-primary" style="background: var(--primary-light); color: var(--primary);">${s.emp}</span>
                </div>
            </div>
            <div class="card-footer" style="padding: 12px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 8px; background: #fafafa; border-radius: 0 0 12px 12px;">
                <button class="btn btn-secondary btn-xs" style="background:#fff">Edit</button>
                <button class="btn btn-primary btn-xs">Assign</button>
            </div>
        </div>
    `).join('');
    
    lcIcons(container);
}

// --- EMPLOYEE ASSIGNMENTS (Instant) ---
function renderShiftAssignments() {
    const container = document.getElementById('shift-dynamic-container');
    if (!container) return;
    
    container.className = ""; // Table layout
    
    document.getElementById('btn-tab-assign').className = 'btn btn-primary btn-sm';
    document.getElementById('btn-tab-def').className = 'btn btn-secondary btn-sm';
    document.getElementById('btn-tab-def').style.border = 'none';

    const mockData = [
        { code: '—', name: 'Abebe Kebede', dept: '—', shift: 'Morning Shift', start: '08:00', end: '17:00', date: '2026-01-01' },
        { code: '—', name: 'Tigist Haile', dept: '—', shift: 'Afternoon Shift', start: '13:00', end: '22:00', date: '2026-02-15' }
    ];

    container.innerHTML = `
        <div class="card mb-3" style="border-radius: 12px; overflow: visible;">
            <div class="card-body" style="padding: 16px 24px;">
                <div class="flex-row" style="gap: 16px; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">DEPARTMENT</label>
                        <div class="as-combo-container">
                            <input type="text" id="shift-dept-filter" class="sel" style="width: 100%;" value="All Departments" onfocus="showAsDrop('as-drop-shift-dept')" readonly>
                            <div class="as-combo-results" id="as-drop-shift-dept">
                                <div class="as-res-item selected" onclick="selectThemedItem(this, 'shift-dept-filter')">All Departments</div>
                                <div class="as-res-item" onclick="selectThemedItem(this, 'shift-dept-filter')">Engineering</div>
                                <div class="as-res-item" onclick="selectThemedItem(this, 'shift-dept-filter')">Operations</div>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">SHIFT FILTER</label>
                        <div class="as-combo-container">
                            <input type="text" id="shift-type-filter" class="sel" style="width: 100%;" value="All Shifts" onfocus="showAsDrop('as-drop-shift-type')" readonly>
                            <div class="as-combo-results" id="as-drop-shift-type">
                                <div class="as-res-item selected" onclick="selectThemedItem(this, 'shift-type-filter')">All Shifts</div>
                                <div class="as-res-item" onclick="selectThemedItem(this, 'shift-type-filter')">Morning Shift</div>
                                <div class="as-res-item" onclick="selectThemedItem(this, 'shift-type-filter')">Afternoon Shift</div>
                            </div>
                        </div>
                    </div>
                    <div style="flex: 1.5;">
                        <label style="font-size: 10px; font-weight: 800; color: var(--muted); margin-bottom: 6px; display: block;">SEARCH EMPLOYEE</label>
                        <div class="search-inner" style="height: 38px; box-shadow: none; border: 1px solid var(--border);">
                            <i data-lucide="search" size="14" style="color: var(--muted)"></i>
                            <input type="text" placeholder="Type name or code..." style="font-size: 13px;">
                        </div>
                    </div>
                    <button class="btn btn-secondary" style="height: 38px; background: #eff4f9; border: none;" onclick="renderShiftAssignments()">
                        <i data-lucide="refresh-cw" size="14"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Employee Shift Assignments</span>
                <button class="btn btn-secondary btn-sm"><i data-lucide="layers" size="14"></i> Bulk Assign</button>
            </div>
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>EMP. CODE</th><th>NAME</th><th>DEPARTMENT</th><th>CURRENT SHIFT</th><th>START TIME</th><th>END TIME</th><th>EFFECTIVE FROM</th><th style="text-align:center">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${mockData.map(row => `
                            <tr>
                                <td style="font-family: 'JetBrains Mono'; font-size: 11px; color: var(--muted);">${row.code}</td>
                                <td style="font-weight: 700;">${row.name}</td>
                                <td style="color: var(--muted);">${row.dept}</td>
                                <td style="font-weight: 600;">${row.shift}</td>
                                <td>${row.start}</td><td>${row.end}</td><td>${row.date}</td>
                                <td>
                                    <div class="flex-row" style="gap:4px; justify-content: center;">
                                        <button class="btn btn-xs btn-secondary" style="font-size: 10px; padding: 2px 8px; background: #fff;">Edit</button>
                                        <button class="btn btn-xs btn-danger" style="font-size: 10px; padding: 2px 8px;">End</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    lcIcons(container);
}

// --- SAVE SHIFT (Instant) ---
function saveNewShift() {
    // Perform instant reset
    showNotification('Shift Created', 'The new shift schedule has been defined successfully.', 'success');
    closeModal('modal-add-shift');
    renderShifts(); // Re-render instantly
}

function generateAttendanceReport() {
    const container = document.getElementById('report-results-container');
    const reportType = document.getElementById('rep-type').value;
    
    let html = '';

    if (reportType === 'Late Comers') {
        // --- LATE COMERS REPORT DATA ---
        const lateData = [
            { code: 'EMP-101', name: 'Abebe Kebede', dept: 'Engineering', date: '2026-03-05', in: '08:25', start: '08:00', late: 25 },
            { code: 'EMP-102', name: 'Tigist Haile', dept: 'HR', date: '2026-03-05', in: '13:40', start: '13:00', late: 40 },
            { code: 'EMP-104', name: 'Selam Tesfaye', dept: 'Engineering', date: '2026-03-07', in: '08:15', start: '08:00', late: 15 },
            { code: 'EMP-103', name: 'Dawit Mengistu', dept: 'Operations', date: '2026-03-10', in: '08:10', start: '08:00', late: 10 },
            { code: 'EMP-101', name: 'Abebe Kebede', dept: 'Engineering', date: '2026-03-12', in: '09:05', start: '08:00', late: 65 }
        ];

        html = `
            <div class="card" style="animation: modalIn 0.3s ease;">
                <div class="card-header">
                    <span class="card-title">Monthly Attendance Summary</span>
                    <span style="font-size: 11px; color: var(--muted); font-weight: 600;">5 records</span>
                </div>
                <div class="table-wrap">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>EMP. CODE</th>
                                <th>NAME</th>
                                <th>DEPARTMENT</th>
                                <th>DATE</th>
                                <th>CHECK-IN</th>
                                <th>SHIFT START</th>
                                <th style="text-align:right">MINUTES LATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${lateData.map(row => `
                                <tr>
                                    <td style="font-family: 'JetBrains Mono'; font-size: 11px; color: var(--muted);">${row.code}</td>
                                    <td style="font-weight: 700;">${row.name}</td>
                                    <td>${row.dept}</td>
                                    <td>${row.date}</td>
                                    <td>${row.in}</td>
                                    <td>${row.start}</td>
                                    <td style="text-align:right; font-weight: 700;">${row.late}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>`;
    } else {
        // --- SUMMARY REPORT DATA (Original) ---
        const summaryData = [
            { dept: 'Engineering', total: 45, absent: 8, leave: 12, late: 15, ot: 32.5, rate: 92.5 },
            { dept: 'HR', total: 12, absent: 1, leave: 3, late: 2, ot: 5, rate: 97.2 },
            { dept: 'Operations', total: 28, absent: 5, leave: 6, late: 8, ot: 18, rate: 89.3 },
            { dept: 'Finance', total: 8, absent: 0, leave: 1, late: 1, ot: 2.5, rate: 98.8 },
            { dept: 'Marketing', total: 10, absent: 2, leave: 2, late: 3, ot: 6, rate: 90.0 }
        ];

        html = `
            <div class="card" style="animation: modalIn 0.3s ease;">
                <div class="card-header">
                    <span class="card-title">Monthly Attendance Summary</span>
                    <span style="font-size: 11px; color: var(--muted); font-weight: 600;">5 records</span>
                </div>
                <div class="table-wrap">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>DEPARTMENT</th><th>TOTAL EMP</th><th>ABSENT DAYS</th><th>LEAVE DAYS</th><th>LATE ARRIVALS</th><th>TOTAL OT HRS</th><th style="text-align:right">ATTENDANCE RATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${summaryData.map(row => {
                                let rateColor = row.rate < 90 ? 'var(--danger)' : (row.rate <= 95 ? 'var(--warning)' : 'var(--success)');
                                return `
                                <tr>
                                    <td>${row.dept}</td><td>${row.total}</td><td>${row.absent}</td><td>${row.leave}</td><td>${row.late}</td><td>${row.ot}</td>
                                    <td style="text-align:right; font-weight: 800; color: ${rateColor};">${row.rate}%</td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            </div>`;
    }

    container.innerHTML = html;
    lcIcons(container);
}
/**
 * Generates the specific report based on the selected 'Report Type' filter.
 * Executed instantly on button click.
 */
function runInstantReport() {
    const target = document.getElementById('report-results-target');
    const reportType = document.getElementById('rep-type-val').value.trim();
    
    // Check if target exists
    if (!target) return;

    let tableHtml = '';
    let recordCount = 0;
    let title = 'Monthly Attendance Summary';

    // Switch data and columns based on the filter
    switch (reportType) {
        case 'Late Comers':
            const lateData = [
                { c: 'EMP-101', n: 'Abebe Kebede', d: 'Engineering', dt: '2026-03-05', in: '08:25', s: '08:00', m: 25 },
                { c: 'EMP-102', n: 'Tigist Haile', d: 'HR', dt: '2026-03-05', in: '13:40', s: '13:00', m: 40 },
                { c: 'EMP-104', n: 'Selam Tesfaye', d: 'Engineering', dt: '2026-03-07', in: '08:15', s: '08:00', m: 15 },
                { c: 'EMP-103', n: 'Dawit Mengistu', d: 'Operations', dt: '2026-03-10', in: '08:10', s: '08:00', m: 10 },
                { c: 'EMP-101', n: 'Abebe Kebede', d: 'Engineering', dt: '2026-03-12', in: '09:05', s: '08:00', m: 65 }
            ];
            recordCount = lateData.length;
            tableHtml = `
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>EMP. CODE</th><th>NAME</th><th>DEPARTMENT</th><th>DATE</th><th>CHECK-IN</th><th>SHIFT START</th><th style="text-align:right">MINUTES LATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${lateData.map(i => `
                            <tr>
                                <td style="font-family:'JetBrains Mono'; font-size:11px">${i.c}</td>
                                <td style="font-weight:700">${i.n}</td>
                                <td>${i.d}</td><td>${i.dt}</td><td>${i.in}</td><td>${i.s}</td>
                                <td style="text-align:right; font-weight:700">${i.m}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>`;
            break;

        case 'Absentee List':
            const absenteeData = [
                { c: 'EMP-101', n: 'Abebe Kebede', d: 'Engineering', days: 3, dates: '5, 12, 19' },
                { c: 'EMP-105', n: 'Henok Assefa', d: 'Finance', days: 2, dates: '8, 22' },
                { c: 'EMP-103', n: 'Dawit Mengistu', d: 'Operations', days: 1, dates: '15' },
                { c: 'EMP-106', n: 'Meron Alemu', d: 'HR', days: 4, dates: '2, 3, 10, 28' }
            ];
            recordCount = absenteeData.length;
            tableHtml = `
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>EMP. CODE</th><th>NAME</th><th>DEPARTMENT</th><th>ABSENT DAYS</th><th style="text-align:right">ABSENT DATES</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${absenteeData.map(i => `
                            <tr>
                                <td style="font-family:'JetBrains Mono'; font-size:11px">${i.c}</td>
                                <td style="font-weight:700">${i.n}</td>
                                <td>${i.d}</td><td>${i.days}</td>
                                <td style="text-align:right; color:var(--muted); font-size:11px">${i.dates}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>`;
            break;

        default: // Summary
            const summaryData = [
                { d: 'Engineering', e: 45, a: 8, l: 12, arr: 15, ot: 32.5, r: 92.5 },
                { d: 'HR', e: 12, a: 1, l: 3, arr: 2, ot: 5, r: 97.2 },
                { d: 'Operations', e: 28, a: 5, l: 6, arr: 8, ot: 18, r: 89.3 },
                { d: 'Finance', e: 8, a: 0, l: 1, arr: 1, ot: 2.5, r: 98.8 },
                { d: 'Marketing', e: 10, a: 2, l: 2, arr: 3, ot: 6, r: 90.0 }
            ];
            recordCount = summaryData.length;
            tableHtml = `
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>DEPARTMENT</th><th>TOTAL EMP</th><th>ABSENT DAYS</th><th>LEAVE DAYS</th><th>LATE ARRIVALS</th><th>TOTAL OT HRS</th><th style="text-align:right">ATTENDANCE RATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${summaryData.map(i => {
                            let col = i.r < 90 ? 'var(--danger)' : (i.r <= 95 ? 'var(--warning)' : 'var(--success)');
                            return `
                            <tr>
                                <td>${i.d}</td><td>${i.e}</td><td>${i.a}</td><td>${i.l}</td><td>${i.arr}</td><td>${i.ot}</td>
                                <td style="text-align:right; font-weight:800; color:${col}">${i.r}%</td>
                            </tr>`}).join('')}
                    </tbody>
                </table>`;
            break;
    }

    // Inject the Card and Table instantly
    target.innerHTML = `
        <div class="card" style="animation: modalIn 0.3s ease;">
            <div class="card-header">
                <span class="card-title">${title}</span>
                <span style="font-size:11px; font-weight:600; color:var(--muted)">${recordCount} records</span>
            </div>
            <div class="table-wrap">
                ${tableHtml}
            </div>
        </div>`;
    
    // Re-run Lucide icons if any were added
    if (typeof lcIcons === 'function') lcIcons(target);
}
//import selection
/**
 * Visual feedback when dragging over
 */
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    const zone = document.getElementById('drop-zone');
    zone.style.borderColor = 'var(--primary)';
    zone.style.background = '#f1fcf0';
    document.getElementById('drop-zone-icon').style.transform = 'scale(1.1)';
}

/**
 * Reset visual feedback when leaving zone
 */
function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    const zone = document.getElementById('drop-zone');
    zone.style.borderColor = '#e2e8f0';
    zone.style.background = '#fafafa';
    document.getElementById('drop-zone-icon').style.transform = 'scale(1)';
}

/**
 * Handle the dropped file
 */
function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    handleDragLeave(e); // Reset styles

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        processImportFile(files[0]);
    }
}

/**
 * Process and display preview instantly
 */
function processImportFile(file) {
    if (!file) return;
    
    // Check extension
    const ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'csv' && ext !== 'xlsx') {
        showNotification('Invalid File', 'Please upload a CSV or Excel file.', 'error');
        return;
    }

    const uploadArea = document.getElementById('attendance-upload-container');
    const previewArea = document.getElementById('attendance-preview-container');
    const tbody = document.getElementById('preview-tbody');

    // Instant switch to preview
    uploadArea.style.display = 'none';
    previewArea.style.display = 'block';

    // Mock data for instant preview
    const mockData = [
        { code: 'EMP-101', date: '2026-03-25', status: 'P', in: '08:05', out: '17:10', valid: true },
        { code: 'EMP-102', date: '2026-03-25', status: 'P', in: '07:55', out: '17:00', valid: true },
        { code: 'EMP-999', date: '2026-03-25', status: 'P', in: '09:00', out: '18:00', valid: false, err: 'User Not Found' }
    ];

    tbody.innerHTML = mockData.map(row => `
        <tr>
            <td style="font-family: 'JetBrains Mono'; font-size: 11px;">${row.code}</td>
            <td style="font-weight: 600;">${row.date}</td>
            <td><span class="badge ${row.status === 'P' ? 'badge-success' : 'badge-danger'}">${row.status}</span></td>
            <td>${row.in}</td>
            <td>${row.out}</td>
            <td style="text-align:right">
                ${row.valid 
                    ? '<span class="badge badge-primary" style="background:#e0f2fe; color:#0369a1">Ready</span>' 
                    : `<span class="badge badge-danger" title="${row.err}">Error</span>`}
            </td>
        </tr>
    `).join('');

    lcIcons(previewArea);
    showNotification('Success', `${file.name} uploaded and validated.`, 'success');
}

/**
 * Finalize the import
 */
function finalizeImport(btn) {
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="14"></i> Syncing...`;
    lcIcons(btn);

    setTimeout(() => {
        showNotification('Import Complete', 'Records have been added to the logs.', 'success');
        goPage('daily-attendance');
    }, 1000);
}

// leavepolicy 
function renderLeaveTypes() {
    const stack = document.getElementById('leave-policy-ledger-stack');
    if (!stack) return;

    // 1. Update Tabs Visuals
    document.querySelectorAll('.policy-tab').forEach(btn => {
        btn.className = 'btn btn-secondary btn-sm policy-tab';
        btn.style.background = 'transparent';
        btn.style.border = 'none';
    });
    const activeBtn = document.getElementById('tab-lt');
    activeBtn.className = 'btn btn-primary btn-sm policy-tab';
    activeBtn.style.background = ''; activeBtn.style.border = '1.5px solid var(--primary)';

    // 2. Updated Data including new types from screenshot
    const leaveData = [
        { name: 'Annual Leave', type: 'Paid', target: 'Approval Required', days: 20, carry: 5, color: '#15b201', icon: 'sun', active: true },
        { name: 'Sick Leave', type: 'Paid', target: 'No Approval', days: 10, carry: 0, color: '#0891b2', icon: 'stethoscope', active: true },
        { name: 'Maternity Leave', type: 'Paid', target: 'Approval Required', days: 90, carry: 0, color: '#8b5cf6', icon: 'baby', active: true },
        { name: 'Paternity Leave', type: 'Paid', target: 'Approval Required', days: 14, carry: 0, color: '#f59e0b', icon: 'heart-handshake', active: true },
        { name: 'Bereavement Leave', type: 'Paid', target: 'Approval Required', days: 5, carry: 0, color: '#ef4444', icon: 'calendar', active: true },
        { name: 'Unpaid Leave', type: 'Unpaid', target: 'Approval Required', days: 0, carry: 0, color: '#94a3b8', icon: 'calendar-days', active: true },
        { name: 'Study / Exam Leave', type: 'Unpaid', target: 'Approval Required', days: 5, carry: 0, color: '#0ea5e9', icon: 'book-open', active: true }
    ];

    // 3. Render with refined spacing
stack.innerHTML = leaveData.map(l => `
    <div class="etype-master-row" style="--accent-color: ${l.color}; height: 88px; padding: 0; margin-bottom: 12px; grid-template-columns: 80px 1.8fr 2fr 140px 200px; box-shadow: var(--shadow); border: 1px solid #f1f5f9;">
        
        <!-- 1. Visual Icon -->
        <div class="etype-visual">
            <div class="etype-icon-box" style="width: 44px; height: 44px; background: color-mix(in srgb, ${l.color} 8%, #fff);">
                <i data-lucide="${l.icon}" size="20" style="color: ${l.color}"></i>
            </div>
        </div>

        <!-- 2. Identity -->
        <div class="etype-identity">
            <span class="etype-label" style="font-size: 0.95rem; font-weight: 800; color: #1e293b;">${l.name}</span>
            <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; color: #94a3b8;">
                <i data-lucide="calendar-days" size="12"></i>
                <span style="font-size: 0.72rem; font-weight: 600;">Policy cycle: <b>Yearly</b></span>
            </div>
        </div>

        <!-- 3. Policy Meta Badges (Refined Design) -->
        <div style="display: flex; align-items: center; gap: 8px;">
            
            <!-- PAID / UNPAID TAG -->
            <span style="
                background: ${l.type === 'Paid' ? '#f0fdf4' : '#fff7ed'}; 
                color: ${l.type === 'Paid' ? '#15b201' : '#f97316'}; 
                border: 1px solid ${l.type === 'Paid' ? '#dcfce7' : '#ffedd5'};
                padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; letter-spacing: 0.02em;
            ">
                ${l.type.toUpperCase()}
            </span>

            <!-- APPROVAL TAG -->
            <span style="
                background: #eff6ff; 
                color: #3b82f6; 
                border: 1px solid #dbeafe;
                padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; letter-spacing: 0.02em;
            ">
                ${l.target.toUpperCase()}
            </span>

            <!-- CARRYOVER TAG -->
            <span style="
                background: #f8fafc; 
                color: #64748b; 
                border: 1px solid #e2e8f0;
                padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; letter-spacing: 0.02em;
            ">
                CARRYOVER ${l.carry} DAYS
            </span>

        </div>

        <!-- 4. Allocation Metrics (Simplified) -->
        <div class="etype-data" style="display: flex; flex-direction: column; justify-content: center; text-align: right; padding-right: 20px;">
            <div style="font-size: 1.2rem; color: #15b201; font-weight: 800; line-height: 1;">
                ${l.days}
            </div>
            <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">
                Days / Year
            </div>
        </div>

        <!-- 5. Actions -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px; padding-right: 24px;">
            <button class="btn btn-secondary btn-xs" style="background: #fff; font-weight: 700; border-radius: 6px;">Edit</button>
            <button class="btn btn-xs ${l.active ? 'btn-danger' : 'btn-success'}" style="font-weight: 700; border-radius: 6px; padding: 4px 14px; min-width: 95px; justify-content: center;">
                ${l.active ? 'Deactivate' : 'Activate'}
            </button>
        </div>
    </div>
`).join('');

    lcIcons(stack);
}
function switchPolicyTab(type) {
    const target = document.getElementById('leave-policy-dynamic-area');
    // Visual Tab Switch
    document.querySelectorAll('.policy-tab').forEach(btn => {
        btn.className = 'btn btn-secondary btn-sm policy-tab';
        btn.style.background = 'transparent';
    });
    event.currentTarget.className = 'btn btn-primary btn-sm policy-tab';
    event.currentTarget.style.background = '';

    target.innerHTML = `<div style="grid-column: 1/-1; padding: 80px; text-align: center;">
        <i data-lucide="construction" size="48" style="color: var(--muted); opacity: 0.2; margin-bottom: 16px;"></i>
        <p style="color: var(--muted); font-weight: 600;">Module coming soon.</p>
    </div>`;
    lcIcons(target);
}
/**
 * Renders the Public Holidays table view matching the screenshot
 */
function renderPublicHolidays() {
    const stack = document.getElementById('leave-policy-ledger-stack');
    if (!stack) return;

    // 1. Update Tabs Visuals
    document.querySelectorAll('.policy-tab').forEach(btn => {
        btn.className = 'btn btn-secondary btn-sm policy-tab';
        btn.style.background = 'transparent';
        btn.style.border = 'none';
    });
    const activeBtn = document.getElementById('tab-ph');
    activeBtn.className = 'btn btn-primary btn-sm policy-tab';
    activeBtn.style.background = ''; activeBtn.style.border = '1.5px solid var(--primary)';

    // 2. Mock Holiday Data
    const holidayData = [
        { name: "New Year's Day", date: '2025-01-01', day: 'Wed', recurring: 'Yes' },
        { name: "Ethiopian Epiphany", date: '2025-01-19', day: 'Sun', recurring: 'Yes' },
        { name: "Adwa Victory Day", date: '2025-03-02', day: 'Sun', recurring: 'Yes' },
        { name: "Labour Day", date: '2025-05-01', day: 'Thu', recurring: 'Yes' }
    ];

    // 3. Render Table Structure and Add Button
    stack.innerHTML = `
        <div style="display: flex; justify-content: flex-end; margin-bottom: 12px; animation: modalIn 0.3s ease;">
            <button class="btn btn-primary btn-sm" style="background: #15b201; border-radius: 8px; padding: 8px 16px;">
                <i data-lucide="plus" size="14" style="margin-right:6px"></i> Add Holiday
            </button>
        </div>

        <div class="card" style="animation: modalIn 0.4s ease; border-radius: 12px; overflow: hidden;">
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>HOLIDAY NAME</th>
                            <th>DATE</th>
                            <th>DAY</th>
                            <th>RECURRING ANNUALLY</th>
                            <th style="text-align:right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${holidayData.map(h => `
                            <tr>
                                <td style="font-weight: 700; color: #1e293b;">${h.name}</td>
                                <td style="color: #64748b; font-family: 'JetBrains Mono'; font-size: 12px;">${h.date}</td>
                                <td style="color: #64748b;">${h.day}</td>
                                <td>
                                    <span class="badge badge-success" style="background: #f0fdf4; color: #15b201; font-size: 9px; font-weight: 800; padding: 2px 8px;">${h.recurring.toUpperCase()}</span>
                                </td>
                                <td style="text-align:right">
                                    <button class="btn btn-xs btn-danger" style="font-size: 10px; font-weight: 700; padding: 3px 12px; border-radius: 6px;">Remove</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    lcIcons(stack);
} 

function renderLeaveEntitlements() {
    const target = document.getElementById('entitlement-results-target');
    if (!target) return;

    const mockData = [
        { code: 'EMP-2025-0001', name: 'Abebe Kebede', dept: 'Engineering', type: 'Annual Leave', typeCol: '#15b201', alloc: 20, carry: 5, used: 8, pend: 2, bal: 15 },
        { code: 'EMP-2025-0001', name: 'Abebe Kebede', dept: 'Engineering', type: 'Sick Leave', typeCol: '#0ea5e9', alloc: 10, carry: 0, used: 3, pend: 0, bal: 7 },
        { code: 'EMP-2025-0010', name: 'Tigist Haile', dept: 'Finance', type: 'Annual Leave', typeCol: '#15b201', alloc: 20, carry: 0, used: 15, pend: 1, bal: 4 },
        { code: 'EMP-2025-0010', name: 'Tigist Haile', dept: 'Finance', type: 'Sick Leave', typeCol: '#0ea5e9', alloc: 10, carry: 0, used: 0, pend: 0, bal: 10 },
        { code: 'EMP-2025-0022', name: 'Yonas Tadesse', dept: 'Marketing', type: 'Maternity Leave', typeCol: '#8b5cf6', alloc: 90, carry: 0, used: 90, pend: 0, bal: 0 }
    ];

    target.innerHTML = `
        <div class="card" style="animation: modalIn 0.3s ease;">
            <div class="card-header">
                <span class="card-title">Leave Entitlements</span>
                <span style="font-size: 11px; font-weight: 600; color: var(--muted)">5 records</span>
            </div>
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>EMP. CODE</th><th>EMPLOYEE NAME</th><th>DEPARTMENT</th><th>LEAVE TYPE</th>
                            <th>ALLOCATED</th><th>CARRIED OVER</th><th>USED</th><th>PENDING</th><th>BALANCE</th>
                            <th style="text-align:center">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${mockData.map(r => {
                            // Calculate Bar %
                            const total = r.alloc + r.carry;
                            const pct = total > 0 ? (r.bal / total) * 100 : 0;
                            let balCol = 'var(--primary)';
                            if(r.bal === 0) balCol = 'var(--danger)';
                            else if(r.bal < 5) balCol = 'var(--warning)';

                            return `
                            <tr>
                                <td style="font-family: 'JetBrains Mono'; font-size: 11px; color: var(--muted);">${r.code}</td>
                                <td style="font-weight: 700;">${r.name}</td>
                                <td>${r.dept}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="width:8px; height:8px; border-radius:50%; background:${r.typeCol}"></span>
                                        ${r.type}
                                    </div>
                                </td>
                                <td style="font-weight:700">${r.alloc}</td>
                                <td style="color:var(--muted)">${r.carry}</td>
                                <td style="color:${r.used > 0 ? 'var(--danger)' : 'inherit'}">${r.used}</td>
                                <td style="color:${r.pend > 0 ? 'var(--warning)' : 'inherit'}">${r.pend}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <b style="color:${balCol}; min-width:15px">${r.bal}</b>
                                        <div style="flex:1; height:4px; background:#f1f5f9; border-radius:10px; width:40px; overflow:hidden;">
                                            <div style="width:${pct}%; height:100%; background:${balCol}; border-radius:10px;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <button class="btn btn-xs btn-secondary" style="background:#fff; font-size:10px; padding:2px 10px;">Edit</button>
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    lcIcons(target);
}

/**
 * Processes the bulk assignment instantly.
 */
function processBulkAssign(btn) {
    const originalHtml = btn.innerHTML;
    
    // UI Loading feedback
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Assigning...`;
    lcIcons(btn);

    // Instant success simulation
    setTimeout(() => {
        showNotification('Bulk Assignment Success', 'Entitlements have been created for the selected department.', 'success');
        closeModal('modal-bulk-assign');
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);

        // Refresh the results table
        if (typeof renderLeaveEntitlements === 'function') {
            renderLeaveEntitlements();
        }
    }, 1000);
}
/**
 * Saves an individual entitlement record instantly.
 */
function saveIndividualEntitlement(btn) {
    const originalHtml = btn.innerHTML;
    
    // Immediate visual feedback
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Saving...`;
    lcIcons(btn);

    // Simulated instant success
    setTimeout(() => {
        showNotification('Allocation Saved', 'The employee leave balance has been updated.', 'success');
        closeModal('modal-add-entitlement');
        
        // Reset button state
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);

        // Refresh the table view instantly
        if (typeof renderLeaveEntitlements === 'function') {
            renderLeaveEntitlements();
        }
    }, 800);
}


function renderLeaveRequests(filter) {
    const target = document.getElementById('leave-requests-target');
    if (!target) return;

    // --- INSTANT TAB SWITCH LOGIC ---
    document.querySelectorAll('.lr-tab').forEach(t => {
        // Reset all to secondary/transparent
        t.className = 'btn btn-secondary btn-sm lr-tab';
        t.style.background = 'transparent';
        t.style.border = 'none';
        t.style.color = '#64748b'; // Muted text for inactive
    });

    const activeBtn = document.getElementById(`lr-tab-${filter.toLowerCase()}`);
    if(activeBtn) {
        // Set active to primary green
        activeBtn.className = 'btn btn-primary btn-sm lr-tab';
        activeBtn.style.background = '#15b201';
        activeBtn.style.border = ''; // Restore default primary border
        activeBtn.style.color = '#fff';
    }

    // --- DATA HANDLING ---
    const mockData = [
        { ref: '#0001', name: 'Abebe Kebede', eid: 'EMP-2025-0001', dept: 'Engineering', type: 'Annual Leave', dot: '#15b201', from: '2026-04-15', to: '2026-04-20', days: 4, reason: 'Family vacation', sub: '2026-04-01', status: 'Approved' },
        { ref: '#0002', name: 'Tigist Haile', eid: 'EMP-2025-0010', dept: 'Finance', type: 'Sick Leave', dot: '#0ea5e9', from: '2026-04-18', to: '2026-04-19', days: 2, reason: 'Medical appointment', sub: '2026-04-05', status: 'Pending' }
    ];

    const data = filter === 'All' ? mockData : mockData.filter(d => d.status === filter);

    // --- RENDER TABLE ---
    target.innerHTML = `
        <div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: var(--shadow);">
            <div class="table-wrap">
                <table class="tbl">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 18px 24px;">REF #</th><th>EMPLOYEE</th><th>DEPARTMENT</th><th>LEAVE TYPE</th>
                            <th>FROM</th><th>TO</th><th style="text-align:center">DAYS</th>
                            <th>REASON</th><th>SUBMITTED</th><th>STATUS</th><th style="text-align:center">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.length > 0 ? data.map(r => `
                        <tr>
                            <td style="padding: 18px 24px; color: #94a3b8; font-size: 11px; font-family: 'JetBrains Mono';">${r.ref}</td>
                            <td>
                                <div style="line-height: 1.4">
                                    <div style="font-weight: 800; color: #1e293b;">${r.name}</div>
                                    <div style="font-size: 10px; color: #94a3b8; font-family: 'JetBrains Mono';">${r.eid}</div>
                                </div>
                            </td>
                            <td style="color: #64748b; font-weight: 500;">${r.dept}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="width:7px; height:7px; border-radius:50%; background:${r.dot}"></span>
                                    <span style="font-weight:700; font-size: 13px; color: #334155;">${r.type}</span>
                                </div>
                            </td>
                            <td style="color: #475569; font-weight: 600;">${r.from}</td>
                            <td style="color: #475569; font-weight: 600;">${r.to}</td>
                            <td style="text-align:center"><b style="font-size: 15px; color: #1e293b;">${r.days}</b></td>
                            <td style="color: #94a3b8; font-size: 12px; font-style: italic;">${r.reason}</td>
                            <td style="color: #64748b;">${r.sub}</td>
                            <td>
                                <span style="
                                    background: ${r.status === 'Approved' ? '#f0fdf4' : '#fffbeb'}; 
                                    color: ${r.status === 'Approved' ? '#15b201' : '#f59e0b'};
                                    border: 1px solid ${r.status === 'Approved' ? '#dcfce7' : '#fef3c7'};
                                    padding: 4px 14px; border-radius: 20px; font-size: 10px; font-weight: 800;
                                ">${r.status}</span>
                            </td>
                            <td style="text-align:right; padding-right: 24px;">
                                ${r.status === 'Approved' ? `
                                    <button class="btn btn-xs btn-secondary" style="background:#f8fafc; border: 1px solid #e2e8f0; padding:4px 16px; border-radius:8px; font-weight:700">Cancel</button>
                                ` : `
                                    <div class="flex-row" style="gap:6px; justify-content: flex-end;">
                                        <button class="btn btn-xs btn-success" style="background:#15b201; border-radius:8px; padding:4px 16px; font-weight:700">Approve</button>
                                        <button class="btn btn-xs btn-danger" style="background:#ef4444; border-radius:8px; padding:4px 16px; font-weight:700">Reject</button>
                                    </div>
                                `}
                            </td>
                        </tr>`).join('') : `<tr><td colspan="11" style="text-align:center; padding: 60px; color: var(--muted);">No matching leave requests found in this category.</td></tr>`}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    lcIcons(target);
}
/**
 * Processes the new leave request submission instantly.
 */
function submitNewLeaveRequest(btn) {
    const originalHtml = btn.innerHTML;
    
    // Immediate loading state
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Submitting...`;
    lcIcons(btn);

    // Instant success simulation (remove artificial delays)
    setTimeout(() => {
        showNotification('Request Submitted', 'Your leave application has been sent for approval.', 'success');
        closeModal('modal-new-leave-request');
        
        // Reset button for next time
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);

        // Instant table refresh
        if (typeof renderLeaveRequests === 'function') {
            renderLeaveRequests('All');
        }
    }, 800);
}
/**
 * Calculates the difference between two dates and updates the UI instantly.
 */
function calculateLeaveDays() {
    const fromVal = document.getElementById('lr-date-from').value;
    const toVal = document.getElementById('lr-date-to').value;
    const isHalfDay = document.getElementById('lr-half-day-toggle').checked;
    
    const box = document.getElementById('lr-calc-box');
    const text = document.getElementById('lr-calc-text');
    const iconDiv = document.getElementById('lr-calc-icon');

    if (fromVal && toVal) {
        const start = new Date(fromVal);
        const end = new Date(toVal);

        // Date Validation
        if (end < start) {
            box.style.background = "#fef2f2";
            box.style.borderColor = "#fecaca";
            text.style.color = "#ef4444";
            text.innerHTML = "Error: End date is before start date";
            iconDiv.style.color = "#ef4444";
            iconDiv.innerHTML = '<i data-lucide="alert-circle" size="20"></i>';
            lcIcons(iconDiv);
            return;
        }

        // Calculate Difference (Inclusive of both days)
        const diffTime = Math.abs(end - start);
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        // Apply Half Day logic
        if (isHalfDay) {
            diffDays = diffDays > 1 ? diffDays - 0.5 : 0.5;
        }

        // Success Styling
        box.style.background = "#f0fdf4";
        box.style.borderColor = "#bbf7d0";
        text.style.color = "#15b201";
        text.innerHTML = `Total Duration: <b style="font-size: 1rem; margin-left: 4px;">${diffDays} Working Day${diffDays !== 1 ? 's' : ''}</b>`;
        iconDiv.style.color = "#15b201";
        iconDiv.innerHTML = '<i data-lucide="calendar-check" size="20"></i>';
        lcIcons(iconDiv);

    } else {
        // Reset to default state
        box.style.background = "#f8fafc";
        box.style.borderColor = "#e2e8f0";
        text.style.color = "#64748b";
        text.innerHTML = "Select dates to calculate duration";
        iconDiv.style.color = "#94a3b8";
        iconDiv.innerHTML = '<i data-lucide="calculator" size="20"></i>';
        lcIcons(iconDiv);
    }
}
 // --- LEAVE CALENDAR STATE ---
let calDate = new Date(); 

// Mock Data Store
const leaveStore = {
    '2026-03-05': [{name: 'J. Smith', type: 'Annual Leave'}],
    '2026-03-08': [{name: 'A. Kim', type: 'Sick Leave'}],
    '2026-03-12': [{name: 'S. Lee', type: 'Annual Leave'}],
    '2026-03-19': [{name: 'E. Wang', type: 'Maternity Leave'}],
    '2026-04-25': [{name: 'Meron Assefa', type: 'Annual Leave'}]
};

/**
 * Main function to generate the grid.  
 */
function renderCalendar() {
    const grid = document.getElementById('calendar-body');
    const label = document.getElementById('cal-month-display');
    if (!grid) return;

    const year = calDate.getFullYear();
    const month = calDate.getMonth();
    label.textContent = calDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    const todayKey = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    
    let html = '';
    // 1. Empty padding for start of month
    for (let i = 0; i < firstDay; i++) {
        html += `<div style="min-height:72px; opacity:0.1; border: 1px dashed #e2e8f0; border-radius: 10px;"></div>`;
    }

    // 2. Map actual days
    for (let d = 1; d <= daysInMonth; d++) {
        const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const hasLeave = leaveStore[dateKey] || null;
        const isToday = (dateKey === todayKey);
        
        html += `
            <div class="cal-day-cell" data-date="${dateKey}" onclick="updateCalendarSidebar('${dateKey}')" style="
                min-height: 72px;
                background: ${hasLeave ? '#f1fcf0' : '#fff'};
                border: 1px solid ${isToday ? '#3b82f6' : (hasLeave ? '#dcfce7' : '#f1f5f9')};
                border-radius: 10px; padding: 10px; cursor: pointer; position: relative; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                ${isToday ? 'box-shadow: inset 0 0 0 1px #3b82f6, 0 4px 12px rgba(59, 130, 246, 0.1);' : ''}
            ">
                <span style="font-size: 0.85rem; font-weight: 700; color: ${isToday ? '#2563eb' : '#1e293b'};">${d}</span>
                ${hasLeave ? `<div style="position:absolute; bottom:10px; left:10px; width:6px; height:6px; background:#15b201; border-radius:50%;"></div>` : ''}
            </div>`;
    }
    grid.innerHTML = html;
    lcIcons(grid);

    // Default sidebar to today (if in current month) or first of month
    const defaultKey = (month === today.getMonth() && year === today.getFullYear()) ? todayKey : `${year}-${String(month + 1).padStart(2, '0')}-01`;
    updateCalendarSidebar(defaultKey);
}

/**
 * Updates the Right Sidebar with Employee Cards
 */
function updateCalendarSidebar(dateStr) {
    // 1. Manage Visual "Active" selection in Grid
    document.querySelectorAll('.cal-day-cell').forEach(c => {
        c.style.transform = "scale(1)";
        c.style.zIndex = "1";
        c.style.boxShadow = c.dataset.date.includes('-' + (new Date().getDate())) ? 'inset 0 0 0 1px #3b82f6' : 'none';
    });

    const clicked = document.querySelector(`[data-date="${dateStr}"]`);
    if(clicked) {
        clicked.style.borderColor = 'var(--primary)';
        clicked.style.transform = "scale(1.08)";
        clicked.style.zIndex = "10";
        clicked.style.boxShadow = "0 10px 15px -3px rgba(0, 0, 0, 0.05)";
    }

    // 2. Update Content Area
    const label = document.getElementById('detail-date-label');
    const listArea = document.getElementById('detail-list-area');
    if (!label || !listArea) return;

    const data = leaveStore[dateStr] || [];
    const dObj = new Date(dateStr);
    label.textContent = dObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

    if (data.length > 0) {
        listArea.innerHTML = data.map(p => `
            <div style="background:#fff; border:1px solid #f1f5f9; padding:14px; border-radius:14px; margin-bottom:12px; display:flex; align-items:center; gap:14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="width:36px; height:36px; border-radius:10px; background:#f0fdf4; color:#15b201; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; border: 1px solid #dcfce7;">
                    ${p.name.charAt(0)}
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 0.9rem; color: #1e293b;">${p.name}</div>
                    <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 800; text-transform:uppercase; letter-spacing:0.02em; margin-top:2px;">${p.type}</div>
                </div>
            </div>`).join('');
    } else {
        listArea.innerHTML = `
            <div style="text-align:center; padding: 60px 20px; opacity:0.4;">
                <i data-lucide="calendar-check" size="40" style="margin-bottom:12px; color: var(--muted);"></i>
                <p style="font-size:0.8rem; font-weight:700; color: var(--muted);">Full attendance today</p>
            </div>`;
    }
    lcIcons(listArea);
}

// --- HELPER NAVIGATION FUNCTIONS ---

function changeMonth(delta) {
    calDate.setMonth(calDate.getMonth() + delta);
    renderCalendar();
}

function resetToToday() {
    calDate = new Date();
    renderCalendar();
}
 function generateVisualCalendar() {
    const splash = document.getElementById('cal-splash-screen');
    const content = document.getElementById('cal-real-content');
    const card = document.getElementById('calendar-main-card');

    if (!splash || !content) return;

    // 1. Swap visibility instantly
    splash.style.display = 'none';
    content.style.display = 'block';
    
    // 2. Reset card alignment for the split view
    card.style.alignItems = 'stretch';

    // 3. Trigger the actual render logic
    renderCalendar();
    
    showNotification('Intelligence', 'Visual overview generated.', 'success');
}
// Global tracker to store Chart.js instances
let analyticsCharts = {};

function initLeaveAnalytics() {
    // 1. Clean up existing charts before re-drawing
    Object.values(analyticsCharts).forEach(chart => chart.destroy());
    analyticsCharts = {};

    const commonOpts = { 
        maintainAspectRatio: false, 
        responsive: true,
        plugins: { legend: { display: false } },
        animation: { duration: 600 }
    };

    // 2. Bar Chart: Leave Days by Dept
    const ctxBar = document.getElementById('chart-leave-dept');
    if (ctxBar) {
        analyticsCharts.bar = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Engineering', 'Finance', 'Marketing', 'Sales', 'HR', 'IT'],
                datasets: [{
                    data: [85, 42, 38, 35, 28, 20],
                    backgroundColor: '#f1fcf0',
                    borderColor: '#15b201',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: { ...commonOpts, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });
    }

    // 3. Doughnut Chart: Distribution
    const ctxPie = document.getElementById('chart-leave-type');
    if (ctxPie) {
        analyticsCharts.pie = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Annual', 'Sick', 'Maternity', 'Paternity', 'Unpaid'],
                datasets: [{
                    data: [45, 25, 15, 10, 5],
                    backgroundColor: ['#15b201', '#0ea5e9', '#8b5cf6', '#f59e0b', '#ef4444'],
                    borderWidth: 3, borderColor: '#fff', cutout: '70%'
                }]
            },
            options: { ...commonOpts, plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 8, padding: 15, font: { size: 10, weight: '700' } } } } }
        });
    }

    // 4. Line Chart: Trend
    const ctxLine = document.getElementById('chart-leave-trend');
    if (ctxLine) {
        analyticsCharts.line = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    data: [18, 22, 35, 28, 30, 25, 20, 15, 10, 18, 12, 15],
                    borderColor: '#15b201', backgroundColor: 'rgba(21, 178, 1, 0.05)', fill: true, tension: 0.4, pointRadius: 3
                }]
            },
            options: { ...commonOpts, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });
    }

    // 5. Populate Leaderboard & Icons
    renderLeaveLeaderboard();
    lcIcons(); 
}
/**
 * Renders the leaderboard for top leave takers with premium styling.
 */
function renderLeaveLeaderboard() {
    const leaderboard = document.getElementById('leaderboard-area');
    if (!leaderboard) return;

    const topTakers = [
        { n: 'Abebe Kebede', d: 'Engineering', v: '28d' },
        { n: 'Tigist Haile', d: 'Finance', v: '24d' },
        { n: 'Yonas Tadesse', d: 'Marketing', v: '20d' },
        { n: 'Meron Assefa', d: 'Sales', v: '18d' },
        { n: 'Dawit Mengistu', d: 'HR', v: '15d' }
    ];

    leaderboard.innerHTML = topTakers.map((p, i) => `
        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 24px; border-bottom:1px solid #f1f5f9; transition: background 0.2s;" 
             onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
            
            <div style="display:flex; align-items:center; gap:16px;">
                <!-- Rank Number -->
                <span style="font-size:11px; font-weight:800; color:#94a3b8; width:15px; font-family:'JetBrains Mono'">${i + 1}</span>
                
                <!-- Identity -->
                <div>
                    <div style="font-size:13px; font-weight:800; color:#1e293b;">${p.n}</div>
                    <div style="font-size:11px; font-weight:600; color:#94a3b8; text-transform: uppercase; letter-spacing:0.02em;">${p.d}</div>
                </div>
            </div>

            <!-- Value Badge -->
            <div style="font-size:15px; font-weight:800; color:#15b201; background: #f0fdf4; padding: 4px 12px; border-radius: 8px; border: 1px solid #dcfce7;">
                ${p.v}
            </div>
        </div>
    `).join('');
}
 /**
 * Renders the Promote/Demote ledger with specific mock data instances.
 */
function renderPromoteDemoteTable() {
    const container = document.getElementById('tbl-Promote/Demote');
    if (!container) return;

    // 1. Specific Mock Data representing all instances
    const mockMovementData = [
        { ref: '501', name: 'Abebe Bekele', type: 'Promotion', from: 'Mid Software Eng', to: 'Senior Software Eng', dept: 'Engineering', change: '7,000', pct: '25', effective: '2026-05-01', status: 'Processing' },
        { ref: '502', name: 'Tigist Alemu', type: 'Promotion', from: 'HR Coordinator', to: 'HR Business Partner', dept: 'HR', change: '6,000', pct: '27', effective: '2026-04-15', status: 'Approved' },
        { ref: '503', name: 'Samuel Girma', type: 'Promotion', from: 'Sales Rep', to: 'Senior Sales Exec', dept: 'Sales', change: '6,000', pct: '33', effective: '2026-04-01', status: 'Processing' },
        { ref: '504', name: 'Hana Tesfaye', type: 'Demotion', from: 'Marketing Manager', to: 'Marketing Specialist', dept: 'Marketing', change: '8,000', pct: '25', effective: '2026-03-15', status: 'Approved' },
        { ref: '505', name: 'Dawit Haile', type: 'Promotion', from: 'Operations Analyst', to: 'Operations Manager', dept: 'Operations', change: '10,000', pct: '50', effective: '2026-05-15', status: 'Pending' },
        { ref: '506', name: 'Liya Worku', type: 'Promotion', from: 'Junior Data Analyst', to: 'Data Analyst', dept: 'IT', change: '5,000', pct: '31', effective: '2026-04-01', status: 'Approved' },
        { ref: '507', name: 'Biniam Negash', type: 'Demotion', from: 'Senior IT Manager', to: 'IT Specialist', dept: 'IT', change: '10,000', pct: '26', effective: '2026-03-01', status: 'Approved' },
        { ref: '508', name: 'Yared Mengistu', type: 'Promotion', from: 'Finance Analyst', to: 'Finance Manager', dept: 'Finance', change: '9,000', pct: '36', effective: '2026-06-01', status: 'Pending' }
    ];

    // 2. Build Table UI
    container.innerHTML = `
        <div class="card" style="animation: modalIn 0.3s ease; border-radius: 16px; border: 1px solid #f1f5f9;">
            <div class="table-wrap">
                <table class="tbl">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 18px 24px;">REF</th>
                            <th>EMPLOYEE</th>
                            <th>TYPE</th>
                            <th>FROM POSITION</th>
                            <th>TO POSITION</th>
                            <th>DEPARTMENT</th>
                            <th>SALARY CHANGE</th>
                            <th>EFFECTIVE</th>
                            <th>STATUS</th>
                            <th style="text-align:right; padding-right: 24px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${mockMovementData.map(r => {
                            const isPromo = r.type === 'Promotion';
                            const statusCol = r.status === 'Approved' ? '#15b201' : (r.status === 'Processing' ? '#0ea5e9' : '#f59e0b');
                            const statusBg = r.status === 'Approved' ? '#f0fdf4' : (r.status === 'Processing' ? '#f0f9ff' : '#fffbeb');
                            const changeCol = isPromo ? '#15b201' : '#ef4444';
                            
                            return `
                            <tr>
                                <td style="padding: 18px 24px; color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">#${r.ref}</td>
                                <td><b style="color: ${r.name === 'Biniam Negash' ? '#15b201' : '#1e293b'}; font-weight: 800;">${r.name}</b></td>
                                <td>
                                    <span style="background: ${isPromo ? '#f0fdf4' : '#fef2f2'}; color: ${isPromo ? '#15b201' : '#ef4444'}; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; border: 1px solid ${isPromo ? '#dcfce7' : '#fee2e2'};">
                                        ${r.type}
                                    </span>
                                </td>
                                <td style="color: #64748b; font-size: 13px;">${r.from}</td>
                                <td style="color: ${r.name === 'Biniam Negash' ? '#15b201' : '#1e293b'}; font-weight: 700; font-size: 13px;">${r.to}</td>
                                <td style="color: ${r.name === 'Biniam Negash' ? '#15b201' : '#64748b'}; font-weight: 500;">${r.dept}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:6px; color: ${changeCol}; font-weight: 800; font-size: 11px;">
                                        <span>${isPromo ? '▲' : '▼'}</span>
                                        <span>ETB ${r.change}</span>
                                        <small style="opacity:0.6; font-weight: 600;">(${r.pct}%)</small>
                                    </div>
                                </td>
                                <td style="color: #475569; font-weight: 600; font-size: 13px;">${r.effective}</td>
                                <td>
                                    <span style="background: ${statusBg}; color: ${statusCol}; border: 1px solid ${statusBg}; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 800;">
                                        ${r.status}
                                    </span>
                                </td>
                                <td style="text-align:right; padding-right: 24px;">
                                    ${r.status === 'Pending' ? `
                                        <div class="flex-row" style="gap:6px; justify-content: flex-end;">
                                            <button class="btn btn-xs btn-success" style="background:#15b201; border-radius:8px; padding:4px 16px; font-weight:700" onclick="showNotification('System','Change Approved','success')">Approve</button>
                                            <button class="btn btn-xs btn-danger" style="background:#ef4444; border-radius:8px; padding:4px 16px; font-weight:700" onclick="showNotification('System','Change Rejected','error')">Reject</button>
                                        </div>
                                    ` : `
                                       <button class="btn btn-xs btn-secondary" onclick="viewMovementDetail(${mockMovementData.indexOf(r)})" style="background:#f8fafc; border: 1px solid #e2e8f0; padding:4px 16px; border-radius:8px; font-weight:700">View</button>
                                    `}
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="padding: 12px 24px; background: #fff; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; border-radius: 0 0 16px 16px;">
                 <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">${mockMovementData.length} records found</span>
            </div>
        </div>
    `;

    lcIcons(container);
}

/**
 * Populates and opens the Movement Detail modal instantly
 */
function viewMovementDetail(index) {
    // 1. Data Store (Include extra fields like salaries and reasons)
    const store = [
        { name: 'Abebe Bekele', type: 'Promotion', from: 'Mid Software Eng', to: 'Senior Software Eng', dept: 'Engineering', old: 'ETB 45,000', new: 'ETB 52,000', date: '2026-05-01', status: 'Processing', reason: 'Consistent high performance and leadership in the backend refactoring project.' },
        { name: 'Tigist Alemu', type: 'Promotion', from: 'HR Coordinator', to: 'HR Business Partner', dept: 'HR', old: 'ETB 22,000', new: 'ETB 28,000', date: '2026-04-15', status: 'Approved', reason: 'Consistent high performance reviews. Ready for strategic HR role.' },
        { name: 'Samuel Girma', type: 'Promotion', from: 'Sales Rep', to: 'Senior Sales Exec', dept: 'Sales', old: 'ETB 18,000', new: 'ETB 24,000', date: '2026-04-01', status: 'Processing', reason: 'Top performing sales agent for three consecutive quarters.' },
        { name: 'Hana Tesfaye', type: 'Demotion', from: 'Marketing Manager', to: 'Marketing Specialist', dept: 'Marketing', old: 'ETB 32,000', new: 'ETB 24,000', date: '2026-03-15', status: 'Approved', reason: 'Role adjustment requested due to personal scheduling requirements.' }
    ];

    const data = store[index] || store[0]; // Fallback to first if index missing

    // 2. Populate Modal Fields
    document.getElementById('mv-modal-title').textContent = `${data.type} — ${data.name}`;
    document.getElementById('mv-modal-sub').textContent = `${data.dept} · Effective ${data.date}`;
    document.getElementById('mv-modal-type').textContent = data.type;
    document.getElementById('mv-modal-status').textContent = data.status;
    document.getElementById('mv-modal-from').textContent = data.from;
    document.getElementById('mv-modal-to').textContent = data.to;
    document.getElementById('mv-modal-sal-old').textContent = data.old;
    document.getElementById('mv-modal-sal-new').textContent = data.new;
    document.getElementById('mv-modal-date').textContent = data.date;
    document.getElementById('mv-modal-dept').textContent = data.dept;
    document.getElementById('mv-modal-reason').textContent = data.reason;

    // 3. Open Modal
    openModal('modal-view-movement');
}
function selectThemedItem(el, inputId) {
    const val = el.textContent.trim();
    const input = document.getElementById(inputId);
    const container = el.closest('.as-combo-results');

    // 1. Update the visible text in the filter box
    if (input) {
        input.value = val;
    }

    // 2. Remove the green highlight ('selected' class) from all items in this list
    container.querySelectorAll('.as-res-item').forEach(item => {
        item.classList.remove('selected');
    });

    // 3. Add the green highlight ('selected' class) to the one you just clicked
    el.classList.add('selected');

    // 4. Close the dropdown menu
    container.classList.remove('active');
}

/**
 * Switches between Step 1 and Step 2 in the Movement Wizard instantly
 */
function switchMvStep(step) {
    const s1 = document.getElementById('mv-step-1-content');
    const s2 = document.getElementById('mv-step-2-content');
    const f1 = document.getElementById('mv-footer-1');
    const f2 = document.getElementById('mv-footer-2');
    const progressLine = document.getElementById('mv-progress-line');
    const ind2 = document.getElementById('mv-ind-2');

    if (step === 2) {
        s1.style.display = 'none';
        f1.style.display = 'none';
        s2.style.display = 'block';
        f2.style.display = 'flex';
        progressLine.style.width = '100%';
        
        // Update Indicator 2
        const num = ind2.querySelector('.mv-step-num');
        num.style.background = '#15b201';
        num.style.color = '#fff';
        num.style.borderColor = '#15b201';
        ind2.querySelector('div:last-child').style.color = '#15b201';
    } else {
        s1.style.display = 'block';
        f1.style.display = 'flex';
        s2.style.display = 'none';
        f2.style.display = 'none';
        progressLine.style.width = '0%';
        
        // Reset Indicator 2
        const num = ind2.querySelector('.mv-step-num');
        num.style.background = '#f1f5f9';
        num.style.color = '#94a3b8';
        num.style.borderColor = '#e2e8f0';
        ind2.querySelector('div:last-child').style.color = '#94a3b8';
    }
    lcIcons();
}

/**
 * Handles the final submission of the wizard
 */
function submitMovementRequest(btn) {
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Submitting...`;
    lcIcons(btn);

    setTimeout(() => {
        showNotification('Request Initiated', 'The position change has been sent for approval.', 'success');
        closeModal('modal-initiate-movement');
        
        // Reset Wizard for next time
        switchMvStep(1);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);
        
        if (typeof renderPromoteDemoteTable === 'function') renderPromoteDemoteTable();
    }, 1000);
}


/**
 * Renders the Department Transfers  instantly
 */ 
function renderDepartmentTransfers() {
    const target = document.getElementById('transfer-table-target');
    if (!target) return;

    // Full Dataset from the Image
    const mockTransfers = [
        { ref: '601', name: 'Abebe Bekele', from_d: 'Engineering', to_d: 'Product', from_b: 'HQ', to_b: 'HQ', req: '2026-04-01', eff: '2026-05-01', status: 'Pending' },
        { ref: '602', name: 'Meron Alemu', from_d: 'Marketing', to_d: 'Sales', from_b: 'HQ', to_b: 'Adama', req: '2026-03-20', eff: '2026-04-15', status: 'Approved' },
        { ref: '603', name: 'Kiros Tesfamichael', from_d: 'Operations', to_d: 'HR', from_b: 'HQ', to_b: 'HQ', req: '2026-04-05', eff: '2026-05-01', status: 'Pending' },
        { ref: '604', name: 'Frehiwot Bekele', from_d: 'Finance', to_d: 'Operations', from_b: 'HQ', to_b: 'Dire Dawa', req: '2026-02-10', eff: '2026-03-01', status: 'Approved' },
        { ref: '605', name: 'Naol Girma', from_d: 'IT', to_d: 'Engineering', from_b: 'HQ', to_b: 'HQ', req: '2026-04-10', eff: '2026-05-15', status: 'Pending' },
        { ref: '606', name: 'Suleiman Ahmed', from_d: 'Sales', to_d: 'Customer Success', from_b: 'Adama', to_b: 'HQ', req: '2026-03-05', eff: '2026-03-20', status: 'Rejected' },
        { ref: '607', name: 'Tigist Alemu', from_d: 'HR', to_d: 'HR', from_b: 'HQ', to_b: 'Hawassa', req: '2026-04-15', eff: '2026-06-01', status: 'Pending' },
        { ref: '608', name: 'Rahel Demeke', from_d: 'Engineering', to_d: 'IT', from_b: 'HQ', to_b: 'HQ', req: '2026-03-01', eff: '2026-03-15', status: 'Approved' }
    ];

    target.innerHTML = `
        <div class="card" style="border-radius: 12px; border: 1px solid #f1f5f9; box-shadow: var(--shadow);">
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="padding: 16px 24px;">REF</th>
                            <th>EMPLOYEE</th>
                            <th>MOVEMENT</th>
                            <th>BRANCH CHANGE</th>
                            <th>REQUESTED</th>
                            <th>EFFECTIVE</th>
                            <th>STATUS</th>
                            <th style="text-align:center; padding-right: 24px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${mockTransfers.map(r => {
                            const statusClass = r.status === 'Approved' ? 'badge-success' : (r.status === 'Rejected' ? 'badge-danger' : 'badge-warning');
                            return `
                            <tr>
                                <td style="padding: 16px 24px; color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">#${r.ref}</td>
                                <td><b style="color: ${r.status === 'Pending' ? '#1e293b' : (r.status === 'Approved' ? '#15b201' : '#1e293b')}; font-weight: 800;">${r.name}</b></td>
                                <td>
                                    <div class="flex-row" style="gap: 8px;">
                                        <span style="font-size: 10px; color: #94a3b8; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 10px; border-radius: 8px; font-weight: 700;">${r.from_d}</span>
                                        <i data-lucide="move-right" size="12" style="color: #cbd5e1;"></i>
                                        <span style="font-size: 10px; color: #15b201; background: #f1fcf0; border: 1.5px solid #15b201; padding: 2px 10px; border-radius: 8px; font-weight: 800;">${r.to_d}</span>
                                    </div>
                                </td>
                                <td style="font-size: 12px; color: #64748b; font-weight: 500;">
                                    ${r.from_b === r.to_b ? 'Same branch' : `<span style="color:#1e293b; font-weight:700">${r.from_b}</span> → <span style="color:#15b201; font-weight:700">${r.to_b}</span>`}
                                </td>
                                <td style="color: #94a3b8; font-size: 12px;">${r.req}</td>
                                <td style="font-weight: 700; color: ${r.status === 'Approved' ? '#15b201' : '#475569'};">${r.eff}</td>
                                <td><span class="badge ${statusClass}" style="padding: 4px 12px; border-radius: 12px; font-weight: 800;">${r.status}</span></td>
                                <td style="text-align:right; padding-right: 24px;">
                                    ${r.status === 'Pending' ? `
                                        <div class="flex-row" style="gap:6px; justify-content: flex-end;">
                                            <button class="btn btn-xs btn-success" style="background:#15b201; padding:5px 12px; border-radius:8px; font-weight:700">Approve</button>
                                            <button class="btn btn-xs btn-danger" style="background:#ef4444; padding:5px 12px; border-radius:8px; font-weight:700">Reject</button>
                                        </div>
                                    ` : `<button class="btn btn-xs btn-secondary" style="background:#f8fafc; border: 1px solid #e2e8f0; padding:5px 15px; border-radius:8px; font-weight:700; color:#64748b;">View</button>`}
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        </div>`;
    lcIcons(target);
}

// Simple filter logic for the search box
function filterTransferTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#transfer-table-target tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
/**
 * RENDERS THE PROMOTE / DEMOTE TABLE
 */
function renderPromoteDemoteTable() {
    const container = document.getElementById('tbl-Promote/Demote');
    if (!container) return;

    const mockData = [
        { ref: '501', name: 'Abebe Bekele', type: 'Promotion', from: 'Mid Software Eng', to: 'Senior Software Eng', change: '7,000', pct: '25', effective: '2026-05-01', status: 'Processing' },
        { ref: '502', name: 'Tigist Alemu', type: 'Promotion', from: 'HR Coordinator', to: 'HR Business Partner', change: '6,000', pct: '27', effective: '2026-04-15', status: 'Approved' }
    ];

    container.innerHTML = `
        <div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div class="table-wrap">
                <table class="tbl">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 18px 24px;">REF</th>
                            <th>EMPLOYEE</th>
                            <th>TYPE</th>
                            <th>TO POSITION</th>
                            <th>SALARY CHANGE</th>
                            <th>STATUS</th>
                            <th style="text-align:right; padding-right: 24px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${mockData.map(r => `
                            <tr>
                                <td style="padding: 18px 24px; color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">#${r.ref}</td>
                                <td><b style="color: #1e293b; font-weight: 800;">${r.name}</b></td>
                                <td><span class="badge ${r.type === 'Promotion' ? 'badge-success' : 'badge-danger'}">${r.type}</span></td>
                                <td>${r.to}</td>
                                <td style="color:#15b201; font-weight:800;">▲ ETB ${r.change}</td>
                                <td><span class="badge badge-info">${r.status}</span></td>
                                <td style="text-align:right; padding-right: 24px;">
                                    <button class="btn btn-xs btn-secondary" onclick="viewMovementDetail(${mockData.indexOf(r)})">View</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>`;
    lcIcons(container);
}
/**
 * Switches between Step 1 and Step 2 in the Transfer Wizard
 */
function switchTrStep(step) {
    const s1 = document.getElementById('tr-step-1-content');
    const s2 = document.getElementById('tr-step-2-content');
    const f1 = document.getElementById('tr-footer-1');
    const f2 = document.getElementById('tr-footer-2');
    const progressLine = document.getElementById('tr-progress-line');
    const ind2 = document.getElementById('tr-ind-2');

    if (step === 2) {
        s1.style.display = 'none'; f1.style.display = 'none';
        s2.style.display = 'block'; f2.style.display = 'flex';
        progressLine.style.width = '100%';
        
        const num = ind2.querySelector('.tr-step-num');
        num.style.background = '#15b201'; num.style.color = '#fff'; num.style.borderColor = '#15b201';
        ind2.querySelector('div:last-child').style.color = '#15b201';
    } else {
        s1.style.display = 'block'; f1.style.display = 'flex';
        s2.style.display = 'none'; f2.style.display = 'none';
        progressLine.style.width = '0%';
        
        const num = ind2.querySelector('.tr-step-num');
        num.style.background = '#f1f5f9'; num.style.color = '#94a3b8'; num.style.borderColor = '#e2e8f0';
        ind2.querySelector('div:last-child').style.color = '#94a3b8';
    }
    lcIcons();
}

/**
 * Handles the final submission for Department Transfer
 */
function submitTransferRequest(btn) {
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Processing...`;
    lcIcons(btn);

    setTimeout(() => {
        showNotification('Transfer Initiated', 'The departmental movement request has been filed.', 'success');
        closeModal('modal-initiate-transfer');
        
        switchTrStep(1); // Reset wizard
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);
        
        if (typeof renderDepartmentTransfers === 'function') renderDepartmentTransfers();
    }, 1000);
}
// Add or Update this specific logic in core.js
function handleTransferEmployeeSelect(name, id) {
    // Mock data simulation - in production, this would be a fetch call based on ID
    const mockDetails = {
        'Abebe Bekele': { dept: 'Engineering', branch: 'HQ' },
        'Meron Alemu': { dept: 'Marketing', branch: 'HQ' },
        'Kiros Tesfamichael': { dept: 'Operations', branch: 'HQ' }
    };

    const details = mockDetails[name] || { dept: 'Unknown', branch: 'Unknown' };
    
    document.getElementById('tr-curr-dept').value = details.dept;
    document.getElementById('tr-curr-branch').value = details.branch;
}

// Ensure the dropdown item click triggers the detail update
// You might need to modify your selectAsItemWithValue logic if it's shared 
if (inputId === 'tr-input-emp') {
    handleTransferEmployeeSelect(displayText, value);
} 

function submitTransferRequest(btn) {
    const originalHtml = btn.innerHTML;
    
    // Simple Validation check
    const requiredFields = ['tr-input-emp', 'tr-input-dept', 'tr-input-branch', 'tr-eff-date', 'tr-reason'];
    let valid = true;
    requiredFields.forEach(id => {
        const el = document.getElementById(id);
        if(!el.value.trim()) {
            el.classList.add('field-error');
            valid = false;
        } else {
            el.classList.remove('field-error');
        }
    });

    if(!valid) {
        showNotification('Required Fields', 'Please fill in all mandatory fields.', 'warning');
        return;
    }

    // Processing UI
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Submitting...`;
    lcIcons(btn);

    setTimeout(() => {
        showNotification('Transfer Initiated', 'The transfer request has been submitted successfully.', 'success');
        closeModal('modal-initiate-transfer');
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);

        // Refresh table if the function exists
        if(typeof renderDepartmentTransfers === 'function') renderDepartmentTransfers();
    }, 1500);
}
function renderDisciplinaryTable() {
    const target = document.getElementById('disciplinary-table-target');
    if (!target) return;

    const data = [
        { ref: '1001', name: 'Abebe Bekele', dept: 'Engineering', type: 'Written Warning', incident: '2026-02-10', issued: '2026-02-12', issuer: 'Meseret Tadesse', level: 2 },
        { ref: '1002', name: 'Tigist Alemu', dept: 'Finance', type: 'Verbal Warning', incident: '2026-03-05', issued: '2026-03-06', issuer: 'Yonas Mekasha', level: 1 },
        { ref: '1003', name: 'Samuel Girma', dept: 'Sales', type: 'Final Warning', incident: '2026-01-18', issued: '2026-01-20', issuer: 'Meseret Tadesse', level: 3 },
        { ref: '1004', name: 'Hana Tesfaye', dept: 'Marketing', type: 'Suspension', incident: '2026-03-22', issued: '2026-03-24', issuer: 'Meseret Tadesse', level: 4 },
        { ref: '1005', name: 'Dawit Haile', dept: 'Operations', type: 'Verbal Warning', incident: '2026-03-30', issued: '2026-04-01', issuer: 'Yonas Mekasha', level: 1 },
        { ref: '1006', name: 'Abebe Bekele', dept: 'Engineering', type: 'Final Warning', incident: '2026-03-15', issued: '2026-03-17', issuer: 'Meseret Tadesse', level: 3 },
        { ref: '1007', name: 'Liya Worku', dept: 'HR', type: 'Written Warning', incident: '2026-04-02', issued: '2026-04-03', issuer: 'Meseret Tadesse', level: 2 }
    ];

    target.innerHTML = `
        <div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: var(--shadow);">
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="padding: 18px 24px;">CASE #</th>
                            <th>EMPLOYEE</th>
                            <th>DEPARTMENT</th>
                            <th>ACTION TYPE</th>
                            <th>INCIDENT DATE</th>
                            <th>ISSUED DATE</th>
                            <th>ISSUED BY</th>
                            <th>SEVERITY</th>
                            <th style="text-align:right; padding-right: 24px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(r => {
                            // Define Level Colors and Widths
                            const levels = {
                                1: { color: '#f59e0b', width: '25%', bg: '#fffbeb' }, // Yellow
                                2: { color: '#f97316', width: '50%', bg: '#fff7ed' }, // Orange
                                3: { color: '#ef4444', width: '75%', bg: '#fef2f2' }, // Red
                                4: { color: '#8b5cf6', width: '100%', bg: '#f5f3ff' } // Purple
                            };
                            const cfg = levels[r.level];

                            return `
                            <tr>
                                <td style="padding: 18px 24px; color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">#${r.ref}</td>
                                <td><b style="color: #1e293b; font-weight: 800;">${r.name}</b></td>
                                <td style="color: #64748b; font-weight: 500;">${r.dept}</td>
                                <td>
                                    <span style="background: ${cfg.bg}; color: ${cfg.color}; border: 1px solid ${cfg.bg}; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 800; display: inline-block;">
                                        ${r.type}
                                    </span>
                                </td>
                                <td style="color: #64748b; font-size: 13px;">${r.incident}</td>
                                <td style="color: #475569; font-weight: 600; font-size: 13px;">${r.issued}</td>
                                <td style="color: #64748b;">${r.issuer}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="width: 50px; height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                                            <div style="width: ${cfg.width}; height: 100%; background: ${cfg.color}; border-radius: 10px;"></div>
                                        </div>
                                        <span style="font-size: 10px; font-weight: 800; color: ${cfg.color};">Level ${r.level}</span>
                                    </div>
                                </td>
                                <td style="text-align:right; padding-right: 24px;">
                                    <button class="btn btn-xs btn-secondary" style="background:#f8fafc; border: 1px solid #e2e8f0; padding:4px 16px; border-radius:8px; font-weight:700">View</button>
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        </div>`;
    lcIcons(target);
}

function filterDisciplinaryTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#disciplinary-table-target tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function submitDisciplinaryAction(btn) {
    const originalHtml = btn.innerHTML;
    
    // 1. Mandatory Fields Validation
    const fields = [
        { id: 'disc-input-emp', label: 'Employee' },
        { id: 'disc-input-type', label: 'Action Type' },
        { id: 'disc-incident-date', label: 'Incident Date' },
        { id: 'disc-description', label: 'Incident Description' }
    ];

    let isValid = true;
    fields.forEach(f => {
        const el = document.getElementById(f.id);
        if (!el.value.trim()) {
            el.classList.add('field-error');
            isValid = false;
        } else {
            el.classList.remove('field-error');
        }
    });

    if (!isValid) {
        showNotification('Validation Error', 'Please complete all required fields (*).', 'warning');
        return;
    }

    // 2. Loading State UI
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Processing...`;
    lcIcons(btn);

    // 3. Simulated Success
    setTimeout(() => {
        showNotification('Success', 'The disciplinary action has been recorded and filed.', 'success');
        closeModal('modal-record-disciplinary');
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);

        // Refresh the table instantly
        if (typeof renderDisciplinaryTable === 'function') {
            renderDisciplinaryTable();
        }
    }, 1200);
}
function renderResignations(filter, el = null) {
    const target = document.getElementById('resignation-table-target');
    if (!target) return;

    // 1. Handle Tab Highlighting safely
    document.querySelectorAll('.res-tab').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = 'transparent';
        btn.style.color = '#64748b';
    });

    // If 'el' is passed (clicked), use it. Otherwise, find the 'All' button by ID.
    const activeTab = el || document.getElementById('tab-all');
    if (activeTab) {
        activeTab.classList.add('active');
        activeTab.style.background = '#15b201';
        activeTab.style.color = '#fff';
    }

    const rawData = [
        { ref: '2001', name: 'Samuel Girma', dept: 'Sales', cat: 'Resignation', type: 'Personal', date: '2026-03-28', assigned: 'Meseret Tadesse', priority: 'High', status: 'Under Review' },
        { ref: '2002', name: 'Liya Worku', dept: 'HR', cat: 'Grievance', type: 'Harassment', date: '2026-03-15', assigned: 'Yonas Mekasha', priority: 'High', status: 'Pending' },
        { ref: '2003', name: 'Biniam Negash', dept: 'IT', cat: 'Grievance', type: 'Unfair Treatment', date: '2026-02-20', assigned: 'Meseret Tadesse', priority: 'Medium', status: 'Resolved' },
        { ref: '2004', name: 'Tigist Alemu', dept: 'Finance', cat: 'Resignation', type: 'Pay Dispute', date: '2026-04-05', assigned: 'Meseret Tadesse', priority: 'High', status: 'Pending' },
        { ref: '2005', name: 'Amir Hassan', dept: 'Operations', cat: 'Grievance', type: 'Work Conditions', date: '2026-04-08', assigned: 'Yonas Mekasha', priority: 'Low', status: 'Pending' }
    ];

    const data = filter === 'All' ? rawData : rawData.filter(r => r.status === filter);

    target.innerHTML = `
        <div class="card" style="border-radius: 12px; border: 1px solid #f1f5f9; box-shadow: var(--shadow);">
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="padding: 16px 24px;">REF #</th>
                            <th>EMPLOYEE</th>
                            <th>DEPARTMENT</th>
                            <th>TYPE</th>
                            <th>FILED DATE</th>
                            <th>ASSIGNED TO</th>
                            <th>PRIORITY</th>
                            <th>STATUS</th>
                            <th style="text-align:right; padding-right: 24px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(r => {
                            const priorityCol = r.priority === 'High' ? '#ef4444' : (r.priority === 'Medium' ? '#f59e0b' : '#15b201');
                            const priorityBg = r.priority === 'High' ? '#fee2e2' : (r.priority === 'Medium' ? '#fffbeb' : '#f1fcf0');
                            const statusCol = r.status === 'Under Review' ? '#0ea5e9' : (r.status === 'Resolved' ? '#15b201' : '#f59e0b');
                            const statusBg = r.status === 'Under Review' ? '#f0f9ff' : (r.status === 'Resolved' ? '#f0fdf4' : '#fffbeb');

                            return `
                            <tr>
                                <td style="padding: 18px 24px; color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">#${r.ref}</td>
                                <td><b style="color: #1e293b; font-weight: 800;">${r.name}</b></td>
                                <td style="color: #64748b; font-weight: 500;">${r.dept}</td>
                                <td>
                                    <div class="flex-row" style="gap: 8px;">
                                        <span style="font-size: 10px; color: #94a3b8; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 6px;">${r.cat}</span>
                                        <span style="font-size: 13px; color: #1e293b; font-weight: 600;">${r.type}</span>
                                    </div>
                                </td>
                                <td style="color: #64748b; font-size: 13px;">${r.date}</td>
                                <td style="color: #475569; font-weight: 600;">${r.assigned}</td>
                                <td>
                                    <span style="background: ${priorityBg}; color: ${priorityCol}; padding: 3px 12px; border-radius: 6px; font-size: 10px; font-weight: 800; border: 1px solid ${priorityBg};">
                                        ${r.priority}
                                    </span>
                                </td>
                                <td>
                                    <span style="background: ${statusBg}; color: ${statusCol}; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 800; border: 1px solid ${statusBg};">
                                        ${r.status}
                                    </span>
                                </td>
                                <td style="text-align:right; padding-right: 24px;">
                                    ${r.status !== 'Resolved' ? `
                                        <button class="btn btn-xs btn-success" style="background:#15b201; border-radius:8px; padding:4px 16px; font-weight:700" onclick="showNotification('System','Case marked as resolved','success')">Resolve</button>
                                    ` : `<span style="color: #cbd5e1; font-weight: 800;">—</span>`}
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="padding: 12px 24px; background: #fff; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 12px 12px;">
                 <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">${data.length} records</span>
                 <div class="pagination-btns">
                    <button class="pg-btn" disabled>‹</button>
                    <button class="pg-btn active">1</button>
                    <button class="pg-btn" disabled>›</button>
                 </div>
            </div>
        </div>
    `;
    lcIcons(target);
}

function submitResignationLog(btn) {
    const originalHtml = btn.innerHTML;
    
    // Validation
    const required = ['res-input-emp', 'res-input-cat', 'res-input-reason', 'res-filed-date'];
    let valid = true;
    required.forEach(id => {
        const el = document.getElementById(id);
        if(!el.value.trim()) {
            el.classList.add('field-error');
            valid = false;
        } else {
            el.classList.remove('field-error');
        }
    });

    if(!valid) {
        showNotification('Required Fields', 'Please fill in all mandatory fields (*).', 'warning');
        return;
    }

    // Processing UI
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Saving...`;
    lcIcons(btn);

    setTimeout(() => {
        showNotification('Record Saved', 'The case has been successfully logged and assigned.', 'success');
        closeModal('modal-log-resignation');
        
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);

        // Refresh the table view instantly
        if(typeof renderResignations === 'function') renderResignations('All');
    }, 1200);
}

// Logic to filter the search box on the main page
function filterResignationTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#resignation-table-target tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function renderSeparations() {
    const target = document.getElementById('separation-table-target');
    if (!target) return;

    const data = [
        { ref: '3001', name: 'Samuel Girma', dept: 'Sales', type: 'Resignation', notice: '2026-03-28', last: '2026-04-25', clearance: 'In Progress', settlement: '—', status: 'In Progress' },
        { ref: '3002', name: 'Biniam Negash', dept: 'IT', type: 'Involuntary', notice: '2026-03-02', last: '2026-03-15', clearance: 'Done', settlement: 'ETB 42,000', status: 'Complete' },
        { ref: '3003', name: 'Tigist Alemu', dept: 'Finance', type: 'Resignation', notice: '2026-04-05', last: '2026-05-05', clearance: 'Pending', settlement: '—', status: 'In Progress' },
        { ref: '3004', name: 'Hana Tesfaye', dept: 'Marketing', type: 'End of Contract', notice: '2026-03-01', last: '2026-03-31', clearance: 'Done', settlement: 'ETB 15,000', status: 'Complete' },
        { ref: '3005', name: 'Yared Mengistu', dept: 'Operations', type: 'Retirement', notice: '2026-02-15', last: '2026-04-30', clearance: 'In Progress', settlement: 'ETB 120,000', status: 'In Progress' }
    ];

    target.innerHTML = `
        <div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: var(--shadow);">
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="padding: 18px 24px;">REF #</th>
                            <th>EMPLOYEE</th>
                            <th>DEPARTMENT</th>
                            <th>EXIT TYPE</th>
                            <th>NOTICE DATE</th>
                            <th>LAST WORKING DAY</th>
                            <th>CLEARANCE</th>
                            <th>SETTLEMENT</th>
                            <th>STATUS</th>
                            <th style="text-align:right; padding-right: 24px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(r => {
                            // Define badge colors for Exit Type
                            const typeColors = {
                                'Resignation': { bg: '#fffbeb', text: '#f59e0b', border: '#fef3c7' },
                                'Involuntary': { bg: '#fee2e2', text: '#ef4444', border: '#fecaca' },
                                'End of Contract': { bg: '#fffbeb', text: '#f59e0b', border: '#fef3c7' },
                                'Retirement': { bg: '#ecfeff', text: '#06b6d4', border: '#cffafe' }
                            };
                            const typeCfg = typeColors[r.type];

                            // Define pill colors for Clearance and Status
                            const getPill = (val) => {
                                if (val === 'Done' || val === 'Complete') return { bg: '#f0fdf4', text: '#15b201' };
                                if (val === 'In Progress') return { bg: '#fffbeb', text: '#f59e0b' };
                                if (val === 'Pending') return { bg: '#fffbeb', text: '#f59e0b' };
                                return { bg: '#f1f5f9', text: '#64748b' };
                            };
                            const clPill = getPill(r.clearance);
                            const stPill = getPill(r.status);

                            return `
                            <tr>
                                <td style="padding: 18px 24px; color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">#${r.ref}</td>
                                <td><b style="color: #1e293b; font-weight: 800;">${r.name}</b></td>
                                <td style="color: #64748b; font-weight: 500;">${r.dept}</td>
                                <td>
                                    <span style="background: ${typeCfg.bg}; color: ${typeCfg.text}; border: 1px solid ${typeCfg.border}; padding: 3px 12px; border-radius: 6px; font-size: 10px; font-weight: 800;">
                                        ${r.type}
                                    </span>
                                </td>
                                <td style="color: #64748b; font-size: 13px;">${r.notice}</td>
                                <td style="color: #1e293b; font-weight: 800; font-size: 13px;">${r.last}</td>
                                <td>
                                    <span style="background: ${clPill.bg}; color: ${clPill.text}; padding: 4px 14px; border-radius: 20px; font-size: 10px; font-weight: 800;">
                                        ${r.clearance}
                                    </span>
                                </td>
                                <td style="color: #64748b; font-size: 13px;">${r.settlement}</td>
                                <td>
                                    <span style="background: ${stPill.bg}; color: ${stPill.text}; padding: 4px 14px; border-radius: 20px; font-size: 10px; font-weight: 800;">
                                        ${r.status}
                                    </span>
                                </td>
                                <td style="text-align:right; padding-right: 24px;">
                                    <button class="btn btn-xs btn-secondary" style="background:#f8fafc; border: 1px solid #e2e8f0; padding:4px 16px; border-radius:8px; font-weight:700">View</button>
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="padding: 12px 24px; background: #fff; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 16px 16px;">
                 <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">5 records</span>
                 <div class="pagination-btns">
                    <button class="pg-btn" disabled>‹</button>
                    <button class="pg-btn active">1</button>
                    <button class="pg-btn" disabled>›</button>
                 </div>
            </div>
        </div>
    `;
    lcIcons(target);
}

function filterSeparationTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#separation-table-target tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
/**
 * Switches between wizard steps (1, 2, 3) for Initiate Separation
 */
function switchSepStep(step) {
    // 1. Hide all content and footers
    document.querySelectorAll('.sep-step-content, [id^="sep-footer-"]').forEach(el => el.style.display = 'none');
    
    // 2. Show target step content and footer
    document.getElementById(`sep-step-${step}-content`).style.display = 'block';
    document.getElementById(`sep-footer-${step}`).style.display = 'flex';

    // 3. Update Progress Line
    const progressLine = document.getElementById('sep-progress-line');
    if (step === 1) progressLine.style.width = '0%';
    else if (step === 2) progressLine.style.width = '50%';
    else if (step === 3) progressLine.style.width = '100%';

    // 4. Update Circle Indicators
    for (let i = 1; i <= 3; i++) {
        const ind = document.getElementById(`sep-ind-${i}`);
        const num = ind.querySelector('.sep-step-num');
        const label = ind.querySelector('div:last-child');

        if (i <= step) {
            num.style.background = '#15b201';
            num.style.color = '#fff';
            num.style.borderColor = '#15b201';
            label.style.color = '#15b201';
        } else {
            num.style.background = '#f1f5f9';
            num.style.color = '#94a3b8';
            num.style.borderColor = '#e2e8f0';
            label.style.color = '#94a3b8';
        }
    }
    
    // Refresh Icons for the new step
    lcIcons();
}

/**
 * Handles the final submission of the wizard
 */
function submitInitiateSeparation(btn) {
    const originalHtml = btn.innerHTML;
    
    // Simple Validation
    const emp = document.getElementById('sep-input-emp').value;
    const type = document.getElementById('sep-input-type').value;
    if(!emp || !type) {
        showNotification('Missing Data', 'Please ensure Step 1 is completed.', 'warning');
        switchSepStep(1);
        return;
    }

    // Processing UI
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="18"></i> Processing...`;
    lcIcons(btn);

    setTimeout(() => {
        showNotification('Separation Initiated', 'Workflow and clearance checklist created.', 'success');
        closeModal('modal-initiate-separation');
        
        // Reset wizard for next use
        switchSepStep(1);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);

        // Refresh main table
        if(typeof renderSeparations === 'function') renderSeparations();
    }, 1500);
}

function renderJobVacancies() {
    const target = document.getElementById('vacancies-table-target');
    if (!target) return;

    const data = [
        { id: '01', title: 'Senior Software Engineer', dept: 'Engineering', branch: 'Main Office', type: 'Full-Time', slots: 2, range: '₱80,000 - ₱120,000', deadline: '2025-05-31', status: 'Open' },
        { id: '02', title: 'HR Business Partner', dept: 'Human Resources', branch: 'Main Office', type: 'Full-Time', slots: 1, range: '₱55,000 - ₱75,000', deadline: '2025-04-30', status: 'Open' },
        { id: '03', title: 'Marketing Coordinator', dept: 'Marketing', branch: 'North Branch', type: 'Full-Time', slots: 1, range: '₱35,000 - ₱50,000', deadline: '2025-04-15', status: 'Filled' },
        { id: '04', title: 'Data Analyst', dept: 'Finance', branch: 'Main Office', type: 'Full-Time', slots: 1, range: '₱60,000 - ₱85,000', deadline: '2025-05-15', status: 'Open' },
        { id: '05', title: 'Customer Support Agent', dept: 'Customer Service', branch: 'South Branch', type: 'Part-Time', slots: 3, range: '₱20,000 - ₱28,000', deadline: '2025-04-20', status: 'Open' },
        { id: '06', title: 'UI/UX Designer', dept: 'Product', branch: 'Main Office', type: 'Full-Time', slots: 1, range: '₱55,000 - ₱80,000', deadline: '2025-04-10', status: 'On Hold' }
    ];

    target.innerHTML = `
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>JOB TITLE</th>
                        <th>DEPARTMENT</th>
                        <th>BRANCH</th>
                        <th>TYPE</th>
                        <th style="text-align:center;">SLOTS</th>
                        <th>SALARY RANGE</th>
                        <th>DEADLINE</th>
                        <th>STATUS</th>
                        <th style="text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(r => {
                        const stColors = {
                            'Open': { bg: '#f0fdf4', text: '#15b201' },
                            'Filled': { bg: '#f0f9ff', text: '#0ea5e9' },
                            'On Hold': { bg: '#fffbeb', text: '#f59e0b' }
                        };
                        const s = stColors[r.status] || { bg: '#f1f5f9', text: '#64748b' };

                        return `
                        <tr style="height: 54px;"> <!-- Fixed Row Height -->
                            <td style="color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">${r.id}</td>
                            <td><b style="color: #1e293b; font-weight: 800;">${r.title}</b></td>
                            <td style="color: #475569;">${r.dept}</td>
                            <td style="color: #64748b;">${r.branch}</td>
                            <td>
                                <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; white-space: nowrap;">
                                    ${r.type}
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 700;">${r.slots}</td>
                            <td style="color: #475569; font-family: 'JetBrains Mono'; font-size: 11px;">${r.range}</td>
                            <td style="color: #94a3b8; font-size: 11px;">${r.deadline}</td>
                            <td>
                                <span class="badge" style="background: ${s.bg}; color: ${s.text}; border: 1px solid ${s.bg}; padding: 3px 12px; border-radius: 12px; font-size: 10px;">
                                    ${r.status}
                                </span>
                            </td>
                            <td>
                                <!-- FORCE HORIZONTAL LAYOUT -->
                                <div style="display: flex; gap: 4px; justify-content: flex-end; align-items: center; flex-wrap: nowrap;">
                                    <button class="btn btn-xs btn-secondary" style="padding: 4px; min-width: 30px;"><i data-lucide="eye" size="14"></i></button>
                                    <button class="btn btn-xs btn-secondary" style="padding: 4px; min-width: 30px;"><i data-lucide="edit-2" size="14"></i></button>
                                    <button class="btn btn-xs" style="background:#fee2e2; color:#dc2626; border:none; padding: 4px; min-width: 30px;"><i data-lucide="trash-2" size="14"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <div class="card-footer" style="padding: 12px 20px; background: #fff; border-top: 1px solid #f1f5f9; border-radius: 0 0 16px 16px;">
             <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">${data.length} records found</span>
        </div>
    `;
    lcIcons(target);
}

function filterVacanciesTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#vacancies-table-target tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function renderApplicantList() {
    const target = document.getElementById('applicant-table-target');
    if (!target) return;

    const data = [
        { id: '01', name: 'Maria Santos', email: 'm.santos@email.com', pos: 'Senior Software Engineer', src: 'LinkedIn', date: '2025-03-05', stage: 'Interview', rating: 4 },
        { id: '02', name: 'Jose Reyes', email: 'j.reyes@email.com', pos: 'Data Analyst', src: 'Indeed', date: '2025-03-08', stage: 'Screening', rating: 4 },
        { id: '03', name: 'Ana Cruz', email: 'a.cruz@email.com', pos: 'HR Business Partner', src: 'Referral', date: '2025-03-10', stage: 'Offer', rating: 5 },
        { id: '04', name: 'Mark Dela Torre', email: 'm.delatorre@email.com', pos: 'Sales Executive', src: 'Company Website', date: '2025-03-12', stage: 'Applied', rating: 0 },
        { id: '05', name: 'Sofia Lim', email: 's.lim@email.com', pos: 'UI/UX Designer', src: 'LinkedIn', date: '2025-03-01', stage: 'Assessment', rating: 4 },
        { id: '06', name: 'Carlos Garcia', email: 'c.garcia@email.com', pos: 'DevOps Engineer', src: 'Job Fair', date: '2025-02-28', stage: 'Rejected', rating: 3 },
        { id: '07', name: 'Liza Mendoza', email: 'l.mendoza@email.com', pos: 'Junior Accountant', src: 'Indeed', date: '2025-03-14', stage: 'Interview', rating: 3 }
    ];

    target.innerHTML = `
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>NAME</th>
                        <th>APPLIED POSITION</th>
                        <th>SOURCE</th>
                        <th>APPLIED DATE</th>
                        <th>STAGE</th>
                        <th>RATING</th>
                        <th style="text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(r => {
                        // Stage logic
                        const stMap = {
                            'Interview': { bg: '#fffbeb', text: '#f59e0b' },
                            'Screening': { bg: '#ecfeff', text: '#0ea5e9' },
                            'Offer': { bg: '#f0fdf4', text: '#15b201' },
                            'Applied': { bg: '#f5f3ff', text: '#8b5cf6' },
                            'Assessment': { bg: '#eff6ff', text: '#3b82f6' },
                            'Rejected': { bg: '#fee2e2', text: '#ef4444' }
                        };
                        const s = stMap[r.stage] || { bg: '#f1f5f9', text: '#64748b' };
                        
                        // Star logic
                        let stars = '';
                        if(r.rating === 0) {
                            stars = '<span style="color:#cbd5e1; font-size:11px;">—</span>';
                        } else {
                            for(let i=1; i<=5; i++) {
                                stars += `<i data-lucide="star" size="12" style="fill:${i <= r.rating ? '#f59e0b' : 'none'}; color:#f59e0b; margin-right:2px;"></i>`;
                            }
                        }

                        return `
                        <tr>
                            <td style="color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">${r.id}</td>
                            <td>
                                <div style="line-height: 1.3">
                                    <div style="font-weight: 800; color: #1e293b; font-size: 13px;">${r.name}</div>
                                    <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">${r.email}</div>
                                </div>
                            </td>
                            <td style="color: #475569; font-weight: 500;">${r.pos}</td>
                            <td>
                                <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700;">
                                    ${r.src}
                                </span>
                            </td>
                            <td style="color: #64748b; font-size: 12px;">${r.date}</td>
                            <td>
                                <span style="background: ${s.bg}; color: ${s.text}; padding: 4px 14px; border-radius: 20px; font-size: 10px; font-weight: 800;">
                                    ${r.stage}
                                </span>
                            </td>
                            <td><div class="flex-row" style="gap:0">${stars}</div></td>
                            <td>
                                <div style="display: flex; gap: 4px; justify-content: flex-end; align-items: center; border-left: 1px solid #f1f5f9; padding-left: 15px;">
                                    <button class="btn btn-xs btn-secondary" style="padding: 4px; min-width: 34px; background:#fff;"><i data-lucide="edit-3" size="14"></i></button>
                                    <button class="btn btn-xs" style="background:#f1fcf0; color:#15b201; border:1px solid #dcfce7; padding: 4px; min-width: 34px;"><i data-lucide="move-right" size="14"></i></button>
                                    <button class="btn btn-xs" style="background:#fee2e2; color:#dc2626; border:none; padding: 4px; min-width: 34px;"><i data-lucide="trash-2" size="14"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <div class="card-footer" style="padding: 12px 24px; background: #fff; border-top: 1px solid #f1f5f9; border-radius: 0 0 16px 16px; display: flex; justify-content: space-between; align-items: center;">
             <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">${data.length} records</span>
             <div class="pagination-btns">
                <button class="pg-btn" disabled>‹</button>
                <button class="pg-btn active">1</button>
                <button class="pg-btn" disabled>›</button>
             </div>
        </div>
    `;
    lcIcons(target);
}

function filterApplicantTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#applicant-table-target tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function renderInterviewTracker() {
    const target = document.getElementById('interview-table-target');
    if (!target) return;

    const data = [
        { id: '01', name: 'Maria Santos', pos: 'Senior Software Engineer', date: '2025-03-25', time: '10:00', mode: 'In-Person', interviewer: 'Robert Cruz', round: '1st Round', score: '8/10', result: 'Passed' },
        { id: '02', name: 'Jose Reyes', pos: 'Data Analyst', date: '2025-03-26', time: '14:00', mode: 'Video Call', interviewer: 'Diana Soriano', round: 'HR Interview', score: '—', result: 'Pending' },
        { id: '03', name: 'Ana Cruz', pos: 'HR Business Partner', date: '2025-03-20', time: '09:00', mode: 'In-Person', interviewer: 'Ben Santos', round: 'Final Round', score: '9/10', result: 'Passed' },
        { id: '04', name: 'Sofia Lim', pos: 'UI/UX Designer', date: '2025-03-27', time: '11:00', mode: 'Video Call', interviewer: 'Mark Tan', round: '1st Round', score: '7/10', result: 'Passed' },
        { id: '05', name: 'Carlos Garcia', pos: 'DevOps Engineer', date: '2025-03-15', time: '10:00', mode: 'In-Person', interviewer: 'Robert Cruz', round: 'Technical Test', score: '4/10', result: 'Failed' },
        { id: '06', name: 'Liza Mendoza', pos: 'Junior Accountant', date: '2025-03-28', time: '13:00', mode: 'Phone Call', interviewer: 'Diana Soriano', round: '1st Round', score: '—', result: 'Pending' },
        { id: '07', name: 'Donna Pascual', pos: 'Legal Counsel', date: '2025-03-22', time: '15:00', mode: 'In-Person', interviewer: 'CEO Office', round: 'Final Round', score: '10/10', result: 'Passed' }
    ];

    target.innerHTML = `
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>CANDIDATE</th>
                        <th>POSITION</th>
                        <th>DATE & TIME</th>
                        <th>MODE</th>
                        <th>INTERVIEWER</th>
                        <th>ROUND</th>
                        <th>SCORE</th>
                        <th>RESULT</th>
                        <th style="text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(r => {
                        // Result logic
                        const resMap = {
                            'Passed': { bg: '#f0fdf4', text: '#15b201' },
                            'Failed': { bg: '#fee2e2', text: '#ef4444' },
                            'Pending': { bg: '#fffbeb', text: '#f59e0b' }
                        };
                        const s = resMap[r.result] || { bg: '#f1f5f9', text: '#64748b' };

                        return `
                        <tr>
                            <td style="color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">${r.id}</td>
                            <td><b style="color: #1e293b; font-weight: 800;">${r.name}</b></td>
                            <td style="color: #475569; font-weight: 500;">${r.pos}</td>
                            <td>
                                <div style="line-height: 1.4">
                                    <div style="font-weight: 800; color: #1e293b; font-size: 13px;">${r.date}</div>
                                    <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">${r.time}</div>
                                </div>
                            </td>
                            <td>
                                <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700;">
                                    ${r.mode}
                                </span>
                            </td>
                            <td style="color: #475569;">${r.interviewer}</td>
                            <td style="color: #94a3b8; font-size: 12px;">${r.round}</td>
                            <td><b style="color: #1e293b; font-size: 14px;">${r.score}</b></td>
                            <td>
                                <span style="background: ${s.bg}; color: ${s.text}; padding: 4px 14px; border-radius: 20px; font-size: 10px; font-weight: 800; border: 1px solid ${s.bg};">
                                    ${r.result}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button class="icon-btn" style="width:34px; height:34px; background:#fff;"><i data-lucide="edit-3" size="14"></i></button>
                                    <button class="icon-btn" style="width:34px; height:34px; color: #ef4444; background: #fee2e2; border:none;"><i data-lucide="trash-2" size="14"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <div class="card-footer" style="padding: 12px 24px; background: #fff; border-top: 1px solid #f1f5f9; border-radius: 0 0 16px 16px;">
             <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">${data.length} records found</span>
        </div>
    `;
    lcIcons(target);
}

function filterInterviewTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#interview-table-target tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function renderInternshipManagement() {
    const target = document.getElementById('internship-table-target');
    if (!target) return;

    const data = [
        { code: 'INT-2025-001', name: 'Kevin Delos Santos', school: 'De La Salle University', course: 'BS Computer Science', dept: 'Engineering', mentor: 'Robert Cruz', start: '2025-01-13', end: '2025-04-13', score: 88, status: 'Active' },
        { code: 'INT-2025-002', name: 'Patricia Gomez', school: 'Ateneo de Manila University', course: 'BS Psychology', dept: 'Human Resources', mentor: 'Diana Soriano', start: '2025-01-13', end: '2025-04-13', score: 72, status: 'Active' },
        { code: 'INT-2025-003', name: 'Andrei Flores', school: 'University of the Philippines', course: 'BS Accountancy', dept: 'Finance', mentor: 'Ben Santos', start: '2025-01-06', end: '2025-04-06', score: 91, status: 'Active' },
        { code: 'INT-2025-004', name: 'Jasmine Uy', school: 'Far Eastern University', course: 'BS Marketing Management', dept: 'Marketing', mentor: 'Mark Tan', start: '2024-10-07', end: '2025-01-07', score: 85, status: 'Completed' },
        { code: 'INT-2025-005', name: 'Lance Domingo', school: 'Mapua University', course: 'BS Information Technology', dept: 'Engineering', mentor: 'Robert Cruz', start: '2025-01-13', end: '2025-04-13', score: null, status: 'Active' },
        { code: 'INT-2025-006', name: 'Ella Tan', school: 'University of Santo Tomas', course: 'BS Commerce', dept: 'Sales', mentor: 'Lisa Ramos', start: '2025-01-20', end: '2025-04-20', score: null, status: 'Active' },
        { code: 'INT-2025-007', name: 'Renz Panganiban', school: 'Polytechnic University', course: 'BS Industrial Engineering', dept: 'Operations', mentor: 'Carlos Lim', start: '2024-11-04', end: '2025-02-04', score: 78, status: 'Completed' }
    ];

    target.innerHTML = `
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width: 120px;">CODE</th>
                        <th>NAME</th>
                        <th>INSTITUTION</th>
                        <th>COURSE</th>
                        <th>DEPARTMENT</th>
                        <th>MENTOR</th>
                        <th>PERIOD</th>
                        <th>EVAL SCORE</th>
                        <th>STATUS</th>
                        <th style="text-align:right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(r => {
                        const statusCol = r.status === 'Active' ? { bg: '#f0fdf4', text: '#15b201' } : { bg: '#f0f9ff', text: '#0ea5e9' };
                        
                        // Score Bar logic
                        let scoreHtml = '<span style="color:#94a3b8; font-size:11px;">Pending</span>';
                        if (r.score) {
                            const barColor = r.score >= 85 ? '#15b201' : '#f59e0b';
                            scoreHtml = `
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width: 40px; height: 5px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                                        <div style="width: ${r.score}%; height: 100%; background: ${barColor}; border-radius: 10px;"></div>
                                    </div>
                                    <b style="color: ${barColor}; font-size: 12px;">${r.score}</b>
                                </div>`;
                        }

                        return `
                        <tr style="height: 60px;">
                            <td style="color: #94a3b8; font-family: 'JetBrains Mono'; font-size: 11px;">${r.code}</td>
                            <td><b style="color: #1e293b; font-weight: 800; font-size: 13px;">${r.name}</b></td>
                            <td style="color: #475569; font-size: 12px; max-width: 160px;">${r.school}</td>
                            <td style="color: #64748b; font-size: 12px;">${r.course}</td>
                            <td style="color: #475569;">${r.dept}</td>
                            <td style="color: #475569;">${r.mentor}</td>
                            <td>
                                <div style="line-height: 1.3">
                                    <div style="font-weight: 700; color: #64748b; font-size: 11px;">${r.start}</div>
                                    <div style="font-size: 10px; color: #94a3b8;">to ${r.end}</div>
                                </div>
                            </td>
                            <td>${scoreHtml}</td>
                            <td>
                                <span class="badge" style="background: ${statusCol.bg}; color: ${statusCol.text}; border: 1px solid ${statusCol.bg}; padding: 4px 12px; border-radius: 10px; font-size: 10px; font-weight: 800;">
                                    ${r.status}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button class="btn btn-xs btn-secondary" style="padding: 4px; min-width: 34px; background:#fff;"><i data-lucide="edit-3" size="14"></i></button>
                                    <button class="btn btn-xs" style="background:#fee2e2; color:#dc2626; border:none; padding: 4px; min-width: 34px;"><i data-lucide="trash-2" size="14"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <div class="card-footer" style="padding: 14px 24px; background: #fff; border-top: 1px solid #f1f5f9; border-radius: 0 0 16px 16px;">
             <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">Showing ${data.length} interns</span>
        </div>
    `;
    lcIcons(target);
}

function filterInternTable(val) {
    const q = val.toLowerCase();
    document.querySelectorAll('#internship-table-target tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}