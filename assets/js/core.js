// ═══════════════════════════════════════════════════════════════════════
// CORE.JS — YDY Universal JavaScript Library
// Optimized: duplicates removed, structure consolidated, performance improved
// ═══════════════════════════════════════════════════════════════════════

// ── ICON HELPER ─────────────────────────────────────────────────────────
// Wrapper around lucide icon rendering; pass an element to scope the scan
const lcIcons = (el) => el ? lucide.createIcons({ nodes: [el] }) : lucide.createIcons();

// ── BADGE FACTORY ────────────────────────────────────────────────────────
// Generates a styled badge span; used throughout table renderers
const b = (cls, txt) => `<span class="badge badge-${cls}">${txt}</span>`;

// Pre-built status badges for the most common states
const statusBadge = {
  active:   b('success', 'Active'),
  inactive: b('neutral', 'Inactive'),
  pending:  b('warning', 'Pending'),
  approved: b('success', 'Approved'),
  rejected: b('danger',  'Rejected')
};

// ── FIELD VALIDATORS ─────────────────────────────────────────────────────
// Each key matches a DOM input id; called on blur and on step-advance
const VALIDATORS = {
  'o-dob': (val) => {
    if (!val) return { valid: false, error: 'Date of Birth is required.' };
    const dob = new Date(val);
    if (isNaN(dob.getTime())) return { valid: false, error: 'Invalid date format.' };
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    if (age < 16)  return { valid: false, error: 'Employee must be at least 16 years old.' };
    if (age > 100) return { valid: false, error: 'Age cannot exceed 100 years.' };
    return { valid: true };
  },
  'o-email': (val) => {
    if (!val) return { valid: true }; // optional field
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)
      ? { valid: true }
      : { valid: false, error: 'Invalid email address.' };
  },
  'o-sal': (val) => {
    if (!val) return { valid: true };
    const num = parseFloat(val);
    return (!isNaN(num) && num > 0)
      ? { valid: true }
      : { valid: false, error: 'Salary must be a positive number.' };
  },
  'o-end-date': (val) => {
    if (!val) return { valid: true };
    const endDate = new Date(val);
    if (isNaN(endDate.getTime())) return { valid: false, error: 'Invalid end date.' };
    const hireField = document.getElementById('o-hire');
    if (hireField?.value && endDate <= new Date(hireField.value))
      return { valid: false, error: 'End date must be after start date.' };
    return { valid: true };
  }
};

// Runs a single field's validator and toggles the error class
function validateField(fieldId) {
  const field = document.getElementById(fieldId);
  if (!field) return { valid: true };
  const validator = VALIDATORS[fieldId];
  if (!validator) return { valid: true };
  const result = validator(field.value.trim());
  field.classList.toggle('field-error', !result.valid);
  return result;
}

// ═══════════════════════════════════════════════════════════════════════
// SIDEBAR SYSTEM
// ═══════════════════════════════════════════════════════════════════════

let sidebarCollapsed = false;
const isMobile = () => window.innerWidth <= 768;

// Cache sidebar DOM references once (they never change)
const sidebar     = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');
const overlay     = document.getElementById('sidebar-overlay');

function toggleSidebar() {
  if (isMobile()) {
    const isOpen = sidebar.classList.contains('mobile-open');
    sidebar.classList.toggle('mobile-open', !isOpen);
    overlay.classList.toggle('visible', !isOpen);
  } else {
    sidebarCollapsed = !sidebarCollapsed;
    sidebar.classList.toggle('collapsed', sidebarCollapsed);
    mainContent.classList.toggle('sidebar-collapsed', sidebarCollapsed);
    const icon = document.getElementById('toggle-icon');
    icon.setAttribute('data-lucide', sidebarCollapsed ? 'panel-left-open' : 'menu');
    // Collapse open submenus when sidebar collapses
    if (sidebarCollapsed)
      document.querySelectorAll('.submenu, .nav-trigger').forEach(s => s.classList.remove('open'));
    lcIcons(document.getElementById('sidebar-toggle-btn'));
  }
}

function closeMobileSidebar() {
  sidebar.classList.remove('mobile-open');
  overlay.classList.remove('visible');
}

// Highlights the correct nav link for the current page
function syncSidebarWithPage(pageId) {
  const pageParentMap = { 'add-employee': 'employee-directory', 'employee-vault': 'document-vault' };
  const targetPage = pageParentMap[pageId] || pageId;
  const activeLink = document.querySelector(`.sub-link[onclick*="'${targetPage}'"], .dash-link[onclick*="'${targetPage}'"]`);
  if (!activeLink) return;
  document.querySelectorAll('.sub-link, .dash-link').forEach(l => l.classList.remove('active'));
  activeLink.classList.add('active');
  const submenu = activeLink.closest('.submenu');
  if (submenu) {
    submenu.classList.add('open');
    submenu.closest('.nav-group')?.querySelector('.nav-trigger')?.classList.add('open');
  }
}

// Remove mobile sidebar state on viewport resize (passive for performance)
window.addEventListener('resize', () => {
  if (!isMobile()) {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('visible');
  }
}, { passive: true });

// ── COLLAPSED SIDEBAR FLYOUT ─────────────────────────────────────────────
// Shows a floating submenu when hovering a collapsed nav group
let _flyoutTimer = null, _activeGroup = null;

function showFlyout(group) {
  clearTimeout(_flyoutTimer);
  if (_activeGroup && _activeGroup !== group) _activeGroup.classList.remove('flyout-active');
  const rect   = group.getBoundingClientRect();
  const sub    = group.querySelector('.submenu');
  const label  = group.querySelector('.nav-trigger-left span');
  if (sub)   sub.style.top   = rect.top + 'px';
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

// Attach flyout events to all nav groups (runs once at script load)
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

// ── NAVIGATION ───────────────────────────────────────────────────────────
function toggleNav(el, id) {
  // If sidebar is collapsed on desktop, expand it first
  if (sidebarCollapsed && !isMobile()) { toggleSidebar(); return; }
  const sub    = document.getElementById(id);
  const isOpen = sub.classList.contains('open');
  // Close all other open menus
  document.querySelectorAll('.nav-trigger').forEach(t => { if (t !== el) t.classList.remove('open'); });
  document.querySelectorAll('.submenu').forEach(s => { if (s !== sub) s.classList.remove('open'); });
  el.classList.toggle('open', !isOpen);
  sub.classList.toggle('open', !isOpen);
}

// ── PAGE ROUTER ───────────────────────────────────────────────────────────
// Tracks which pages have already been initialised to avoid re-running init logic
const inited = new Set();
let pendingEmployeeVaultData = null;

function goPage(id, el) {
  document.querySelectorAll('.sub-link, .dash-link').forEach(l => l.classList.remove('active'));
  if (el) el.classList.add('active');

  const url = new URL(window.location.href);
  url.searchParams.set('page', id);
  history.pushState({ page: id }, '', url.toString());

  const contentArea = document.getElementById('content-area');
  contentArea.style.opacity = '0.4';

  // Clear init flags so the new page re-initialises
  inited.delete(id);
  inited.delete('dashboard-main-chart');
  inited.delete('analytics');

  fetch('dashboard.php?page=' + encodeURIComponent(id) + '&ajax=1')
    .then(r => r.text())
    .then(html => {
      contentArea.innerHTML  = html;
      contentArea.style.opacity = '1';
      lcIcons(contentArea);
      syncSidebarWithPage(id);
      // Remove any leftover "built" markers from previous renders
      contentArea.querySelectorAll('[data-built]').forEach(el => delete el.dataset.built);

      // Update page title from nav link text
      const titleEl = document.getElementById('page-title');
      if (titleEl) {
        const rawText = el ? el.textContent.trim() : id.replace(/-/g, ' ');
        titleEl.textContent = rawText.charAt(0).toUpperCase() + rawText.slice(1);
      }

      // Re-execute inline <script> tags injected by the server
      contentArea.querySelectorAll('script').forEach(old => {
        const s = document.createElement('script');
        [...old.attributes].forEach(a => s.setAttribute(a.name, a.value));
        s.textContent = old.textContent;
        old.parentNode.replaceChild(s, old);
      });

      if (typeof initPage === 'function') initPage(id);
    })
    .catch(() => { contentArea.style.opacity = '1'; });
}

// Browser back/forward navigation support
window.addEventListener('popstate', (e) => {
  const page = e.state?.page || 'dashboard';
  const link = document.querySelector(`.sub-link[onclick*="'${page}'"], .dash-link[onclick*="'${page}'"]`);
  goPage(page, link);
});

// Hash-based navigation support (e.g. #some-page)
window.addEventListener('hashchange', () => {
  const hash = window.location.hash.replace('#', '');
  if (hash) goPage(hash);
});

// ── INITIAL PAGE LOAD ────────────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
  lcIcons();
  const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
  document.querySelectorAll('.sub-link, .dash-link').forEach(l => l.classList.remove('active'));
  const activeLink = document.querySelector(`.sub-link[onclick*="'${currentPage}'"], .dash-link[onclick*="'${currentPage}'"]`);
  if (activeLink) activeLink.classList.add('active');

  const titleEl = document.getElementById('page-title');
  if (titleEl) titleEl.textContent = activeLink ? activeLink.textContent.trim() : currentPage.replace(/-/g, ' ');

  document.getElementById('content-area')?.querySelector('.page')?.classList.add('active');

  if (typeof initPage === 'function') initPage(currentPage);
  setTimeout(() => syncSidebarWithPage(currentPage), 20);
});

// ═══════════════════════════════════════════════════════════════════════
// MODAL HELPERS
// ═══════════════════════════════════════════════════════════════════════

function openModal(id) {
  const el = document.getElementById(id);
  el.classList.add('open');
  lcIcons(el);
}

// Closes only when clicking the overlay backdrop (not inside the modal)
function closeModal(id, e) {
  if (!e || e.target === e.currentTarget)
    document.getElementById(id).classList.remove('open');
}

// ═══════════════════════════════════════════════════════════════════════
// TOAST NOTIFICATION SYSTEM
// ═══════════════════════════════════════════════════════════════════════

const _toastIcons = { success: 'check-circle', error: 'alert-circle', warning: 'alert-triangle', info: 'info' };

function showNotification(title, message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `ydy-toast toast-${type}`;
  toast.innerHTML = `
    <div class="toast-icon"><i data-lucide="${_toastIcons[type]}" size="18"></i></div>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      <div class="toast-msg">${message}</div>
    </div>
    <button class="toast-close" onclick="this.closest('.ydy-toast').remove()" title="Dismiss">
      <i data-lucide="x" size="16"></i>
    </button>
    <div class="toast-progress"><div class="toast-progress-fill"></div></div>`;

  container.appendChild(toast);
  lucide.createIcons({ nodes: [toast] });

  // Animate the progress bar as a countdown visual
  const fill = toast.querySelector('.toast-progress-fill');
  fill.style.transition = 'transform 4s linear';
  fill.style.transform  = 'scaleX(0)';

  // Auto-dismiss after 4 s; clear timeout if user closes manually
  const tid = setTimeout(() => {
    toast.classList.add('exit');
    setTimeout(() => toast.remove(), 300);
  }, 4000);

  toast._timeoutId = tid;
  const _orig = toast.remove.bind(toast);
  toast.remove = function () { clearTimeout(this._timeoutId); _orig(); };
}

// ═══════════════════════════════════════════════════════════════════════
// CONFIRM DIALOG
// ═══════════════════════════════════════════════════════════════════════

// Maps confirm type → [btn class, icon, bg var, color var]
const _confirmStyles = {
  danger:  ['btn-danger',  'alert-triangle', 'var(--danger-bg)',  'var(--danger)'],
  success: ['btn-success', 'check-circle',   'var(--success-bg)', 'var(--success)'],
  warning: ['btn-warning', 'alert-circle',   'var(--warning-bg)', 'var(--warning)'],
  default: ['btn-primary', 'help-circle',    'var(--primary-light)', 'var(--primary)']
};

function openConfirm(title, message, btnText = 'Confirm', type = 'default') {
  document.getElementById('confirm-title').innerText = title;
  document.getElementById('confirm-body').innerText  = message;
  const yesBtn         = document.getElementById('confirm-btn-yes');
  const iconEl         = document.getElementById('confirm-icon');
  const iconContainer  = document.getElementById('confirm-icon-container');

  yesBtn.className      = 'btn';
  iconContainer.className = '';
  iconContainer.style.background = '';
  iconContainer.style.color      = '';

  const [btnCls, icon, bg, color] = _confirmStyles[type] || _confirmStyles.default;
  yesBtn.classList.add(btnCls);
  yesBtn.innerText = btnText;
  iconEl.setAttribute('data-lucide', icon);
  iconContainer.style.background = bg;
  iconContainer.style.color      = color;

  lucide.createIcons({ icons: { [icon]: iconEl } });
  document.getElementById('confirm-modal').classList.add('open');
}

function closeConfirm() {
  document.getElementById('confirm-modal').classList.remove('open');
}

// ═══════════════════════════════════════════════════════════════════════
// AVATAR PREVIEW
// ═══════════════════════════════════════════════════════════════════════

function previewAvatar(input) {
  if (!input.files?.[0]) return;
  const file = input.files[0];
  if (file.size > 5 * 1024 * 1024) {
    showNotification('File Too Large', 'The image exceeds the 5MB limit.', 'error');
    input.value = '';
    return;
  }
  if (!file.type.startsWith('image/')) {
    showNotification('Invalid File', 'Please select a valid image file (JPG, PNG, WebP).', 'error');
    input.value = '';
    return;
  }
  const reader = new FileReader();
  reader.onload = e => {
    const preview   = document.getElementById('avatar-img-output');
    const icon      = document.getElementById('placeholder-icon');
    const box       = document.getElementById('avatar-preview-box');
    const removeBtn = document.getElementById('avatar-remove-btn');
    preview.src            = e.target.result;
    preview.style.display  = 'block';
    icon.style.display     = 'none';
    if (removeBtn) removeBtn.style.display = 'flex';
    box.style.borderStyle  = 'solid';
    box.style.borderColor  = 'var(--primary)';
  };
  reader.readAsDataURL(file);
}

function cancelAvatar(e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  const input     = document.getElementById('avatar-upload');
  const preview   = document.getElementById('avatar-img-output');
  const icon      = document.getElementById('placeholder-icon');
  const box       = document.getElementById('avatar-preview-box');
  const removeBtn = document.getElementById('avatar-remove-btn');
  if (input)   input.value = '';
  if (preview) { preview.src = ''; preview.style.display = 'none'; }
  if (icon)    icon.style.display = 'block';
  if (removeBtn) removeBtn.style.display = 'none';
  if (box) { box.style.borderStyle = 'dashed'; box.style.borderColor = '#cbd5e1'; }
}

// ── BUTTON STATE HELPER ──────────────────────────────────────────────────
// Restores a save/submit button after an async operation
function restoreButton(btn, originalHtml) {
  if (btn?.isConnected) {
    btn.disabled  = false;
    btn.innerHTML = originalHtml;
    lcIcons(btn);
  }
}

// ═══════════════════════════════════════════════════════════════════════
// COMPANY PROFILE
// ═══════════════════════════════════════════════════════════════════════

// Reads a value from the company profile page by its label text
function _getCompanyValue(labelText) {
  for (const entry of document.querySelectorAll('.data-entry')) {
    if (entry.querySelector('.de-label')?.textContent.trim() === labelText)
      return entry.querySelector('.de-value')?.textContent.trim() || '';
  }
  return '';
}

function openCompanyEditModal() {
  const g = _getCompanyValue;
  document.getElementById('edit_legal_name').value          = g('Legal Name');
  document.getElementById('edit_trading_name').value        = g('Trading Name');
  document.getElementById('edit_ceo_name').value            = g('CEO');
  document.getElementById('edit_head_office').value         = g('Head office');
  document.getElementById('edit_entity_type').value         = g('Entity Type');
  const estText = g('Establishment');
  if (estText && estText !== '-' && estText !== '—') {
    const d = new Date(estText);
    if (!isNaN(d)) document.getElementById('edit_establishment_date').value = d.toISOString().split('T')[0];
  }
  document.getElementById('edit_registration_no').value     = g('Registration No.');
  document.getElementById('edit_tin').value                 = g('Tax ID (TIN)');
  document.getElementById('edit_vat_reg_number').value      = g('VAT Reg Number');
  document.getElementById('edit_trade_license_no').value    = g('Trade License No.');
  document.getElementById('edit_work_week_desc').value      = g('Standard Work Week');
  document.getElementById('edit_probation_days').value      = g('Probation Period');
  document.getElementById('edit_retirement_age').value      = g('Retirement Policy');
  document.getElementById('edit_main_bank').value           = g('Main Bank');
  document.getElementById('edit_bank_account_primary').value = g('Account (Primary)');
  document.getElementById('edit_base_currency').value       = g('Base Currency');
  document.getElementById('edit_fiscal_start').value        = g('Fiscal Start');
  document.getElementById('edit_website').value             = g('Official Website');
  document.getElementById('edit_corporate_email').value     = g('Corporate Email');
  document.getElementById('edit_corporate_phone').value     = g('Corporate Phone');
  document.getElementById('edit_telegram').value            = g('Telegram:') || g('Telegram');
  document.getElementById('edit_whatsapp').value            = g('WhatsApp:') || g('WhatsApp');
  document.getElementById('edit_linkedin').value            = g('LinkedIn:') || g('LinkedIn');

  if (typeof populateCompanyFields === 'function') populateCompanyFields();
  disableCompanyEdit();
  document.getElementById('btn-enable-edit').style.display = 'inline-flex';
  document.getElementById('btn-save-company').style.display = 'none';
  openModal('modal-edit-company');
}

function enableCompanyEdit() {
  document.getElementById('company-profile-form').querySelectorAll('input, select, textarea').forEach(i => i.disabled = false);
  document.getElementById('btn-enable-edit').style.display = 'none';
  document.getElementById('btn-save-company').style.display = 'inline-flex';
}

function disableCompanyEdit() {
  document.getElementById('company-profile-form').querySelectorAll('input, select, textarea').forEach(i => i.disabled = true);
}

function saveCompanyProfile() {
  const btn = document.getElementById('btn-save-company');
  const originalHtml = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="14"></i> Saving...`;
  lcIcons(btn);
  const formData = new FormData(document.getElementById('company-profile-form'));
  if (!formData.get('legal_name')) {
    showNotification('Validation Error', 'Legal Name is required.', 'error');
    restoreButton(btn, originalHtml);
    return;
  }
  fetch('api/companyprofile/update_company_profile.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        showNotification('Success', result.message || 'Company profile updated.', 'success');
        closeCompanyModal();
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification('Error', result.message || 'Update failed.', 'error');
      }
    })
    .catch(err => showNotification('Network Error', err.message, 'error'))
    .finally(() => {
      restoreButton(btn, originalHtml);
      disableCompanyEdit();
      document.getElementById('btn-enable-edit').style.display = 'inline-flex';
      document.getElementById('btn-save-company').style.display = 'none';
    });
}

function closeCompanyModal(e) { if (!e || e.target.classList.contains('modal-overlay')) closeModal('modal-edit-company'); }

// Attach "Update Company" button click handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('#p-company-profile .btn-glass-pro-slim')
    ?.addEventListener('click', openCompanyEditModal);
});

// ═══════════════════════════════════════════════════════════════════════
// DEPARTMENT MANAGEMENT
// ═══════════════════════════════════════════════════════════════════════

function openDeptModal() {
  openModal('modal-add-dept');
  const headInput = document.getElementById('as-input-dept-head');
  // Create the hidden ID field if it doesn't exist yet
  let hidden = document.getElementById('as-input-dept-head_id');
  if (!hidden) {
    hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.id   = 'as-input-dept-head_id';
    headInput.parentNode.appendChild(hidden);
  }
  document.getElementById('dept-name').value = '';
  headInput.value  = '';
  hidden.value     = '';
  document.getElementById('dept-name').classList.remove('field-error');
  headInput.classList.remove('field-error');
  enforceDropdownOnBlur('as-input-dept-head');
}

function closeDeptModal(e) { closeModal('modal-add-dept', e); }

function saveDepartment() {
  const deptName  = document.getElementById('dept-name');
  const headInput = document.getElementById('as-input-dept-head');
  const headId    = document.getElementById('as-input-dept-head_id')?.value || '';
  const status    = document.getElementById('dept-status').value || 'Active';
  const csrf      = document.getElementById('dept_csrf_token')?.value || '';

  deptName.classList.remove('field-error');
  headInput.classList.remove('field-error');

  if (!deptName.value.trim()) {
    deptName.classList.add('field-error');
    showNotification('Required', 'Department name is mandatory.', 'warning');
    return;
  }
  if (headInput.value.trim() !== '' && headId === '') {
    headInput.classList.add('field-error');
    showNotification('Selection Required', 'Please select an employee from the dropdown list or leave the field empty.', 'warning');
    headInput.focus();
    return;
  }

  const btn = document.querySelector('#modal-add-dept .btn-primary');
  const originalHtml = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  fetch('api/companyprofile/add_department.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ dept_name: deptName.value.trim(), dept_head_id: headId, dept_status: status, csrf_token: csrf })
  })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('departments');
        showNotification('Success', result.message, 'success');
        closeDeptModal();
        inited.delete('departments');
        goPage('departments');
      } else {
        showNotification('Error', result.message, 'error');
      }
    })
    .catch(err => showNotification('Error', 'Network error: ' + err.message, 'error'))
    .finally(() => restoreButton(btn, originalHtml));
}

async function openEditDeptModal(deptName, headName, deptId) {
  document.getElementById('edit_dept_id').value    = deptId;
  document.getElementById('edit-dept-name').value  = deptName;
  document.getElementById('edit-dept-status').value = 'Active';
  const headInput = document.getElementById('as-input-edit-dept-head');
  headInput.value = headName === '—' ? '' : headName;
  headInput.classList.remove('field-error');
  document.getElementById('as-input-edit-dept-head_id')?.remove();

  // Preload or use cached employee list for the dropdown
  let employees = dropdownCache['employees:'];
  if (!employees) {
    try {
      const res = await fetch('api/1common/fetch_dropdown.php?type=employees').then(r => r.json());
      if (res.success && res.data) dropdownCache['employees:'] = employees = res.data;
    } catch (err) { console.warn('Failed to preload employees:', err); }
  }
  if (employees && headName && headName !== '—') {
    const emp = employees.find(e => e.label.includes(headName));
    if (emp) {
      const hidden = document.createElement('input');
      hidden.type  = 'hidden';
      hidden.id    = 'as-input-edit-dept-head_id';
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
  const deptId    = document.getElementById('edit_dept_id').value;
  const deptName  = document.getElementById('edit-dept-name');
  const headInput = document.getElementById('as-input-edit-dept-head');
  const headId    = document.getElementById('as-input-edit-dept-head_id')?.value || '';
  const status    = document.getElementById('edit-dept-status').value;
  const csrf      = document.getElementById('edit_dept_csrf_token').value;
  const btn       = document.getElementById('btn-update-dept');

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
  btn.disabled  = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  fetch('api/companyprofile/update_department.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ dept_id: deptId, dept_name: deptName.value.trim(), dept_head_id: headId, dept_status: status, csrf_token: csrf })
  })
    .then(r => r.json())
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
    .catch(err => showNotification('Error', 'Network error: ' + err.message, 'error'))
    .finally(() => restoreButton(btn, originalHtml));
}

// ═══════════════════════════════════════════════════════════════════════
// JOB POSITION MANAGEMENT
// ═══════════════════════════════════════════════════════════════════════

function openJobModal() {
  openModal('modal-add-job-position');
  document.getElementById('job-title').value = '';
  document.getElementById('as-input-job-dept').value = '';
  document.getElementById('as-input-job-dept_id')?.remove();
  document.getElementById('job-status').value = 'Active';
  enforceDropdownOnBlur('as-input-job-dept');
}

function closeJobModal(e) { closeModal('modal-add-job-position', e); }

function saveJobPosition() {
  const titleEl  = document.getElementById('job-title');
  const deptId   = document.getElementById('as-input-job-dept_id')?.value || '';
  const deptInput = document.getElementById('as-input-job-dept');
  const status   = document.getElementById('job-status').value || 'Active';
  const csrf     = document.getElementById('job_csrf_token')?.value || '';
  const btn      = document.getElementById('btn-save-job');

  titleEl.classList.remove('field-error');
  deptInput.classList.remove('field-error');

  if (!titleEl.value.trim()) {
    titleEl.classList.add('field-error');
    showNotification('Required Fields', 'Job Title is required.', 'warning');
    return;
  }
  if (!deptId) {
    deptInput.classList.add('field-error');
    showNotification('Required Fields', 'Please select a Department.', 'warning');
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Creating...`;
  lcIcons(btn);

  fetch('api/companyprofile/add_job_position.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ job_title: titleEl.value.trim(), job_dept_id: deptId, job_status: status, csrf_token: csrf })
  })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('job_positions');
        showNotification('Success', result.message, 'success');
        closeJobModal();
        inited.delete('job-positions');
        goPage('job-positions');
      } else {
        showNotification('Error', result.message, 'error');
      }
    })
    .catch(err => showNotification('Error', 'Network error: ' + err.message, 'error'))
    .finally(() => restoreButton(btn, originalHtml));
}

async function openEditJobModal(title, deptName, jobId) {
  document.getElementById('edit_job_id').value      = jobId;
  document.getElementById('edit-job-title').value   = title;
  const deptInput = document.getElementById('as-input-edit-job-dept');
  deptInput.value = deptName;
  deptInput.classList.remove('field-error');

  let hidden = document.getElementById('as-input-edit-job-dept_id');
  if (!hidden) {
    hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.id   = 'as-input-edit-job-dept_id';
    deptInput.parentNode.appendChild(hidden);
  }

  let departments = dropdownCache['departments:'];
  if (!departments) {
    try {
      const res = await fetch('api/1common/fetch_dropdown.php?type=departments').then(r => r.json());
      if (res.success && res.data) dropdownCache['departments:'] = departments = res.data;
    } catch (err) { console.warn('Failed to preload departments:', err); }
  }
  if (departments) {
    hidden.value = departments.find(d => d.label === deptName)?.value || '';
    const dropContainer = document.getElementById('as-drop-edit-job-dept');
    if (dropContainer) renderDropdownItems(dropContainer, departments, deptName);
  }
  enforceDropdownOnBlur('as-input-edit-job-dept');
  openModal('modal-edit-job-position');
}

function closeEditJobModal(e) { closeModal('modal-edit-job-position', e); }

function updateJobPosition() {
  const jobId     = document.getElementById('edit_job_id').value;
  const titleEl   = document.getElementById('edit-job-title');
  const deptInput = document.getElementById('as-input-edit-job-dept');
  const deptId    = document.getElementById('as-input-edit-job-dept_id')?.value || '';
  const btn       = document.getElementById('btn-update-job');

  titleEl.classList.remove('field-error');
  deptInput.classList.remove('field-error');

  if (!titleEl.value.trim()) {
    titleEl.classList.add('field-error');
    showNotification('Required', 'Job Title is required.', 'warning');
    return;
  }
  if (!deptInput.value.trim()) {
    deptInput.classList.add('field-error');
    showNotification('Required', 'Please select a department.', 'warning');
    return;
  }
  if (deptInput.value.trim() && !deptId) {
    deptInput.classList.add('field-error');
    showNotification('Invalid Selection', 'Please select a valid department from the dropdown list.', 'warning');
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  fetch('api/companyprofile/update_job_position.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      job_id: jobId, job_title: titleEl.value.trim(), job_dept_id: deptId,
      job_status: document.getElementById('edit-job-status').value,
      csrf_token: document.getElementById('edit_job_csrf_token').value
    })
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
    .finally(() => restoreButton(btn, originalHtml));
}

// ═══════════════════════════════════════════════════════════════════════
// BRANCH MANAGEMENT
// ═══════════════════════════════════════════════════════════════════════

function openBranchModal() {
  openModal('modal-add-branch');
  const fields = ['branch-name', 'branch-phone', 'branch-email', 'branch-city', 'branch-address'];
  fields.forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.value = ''; el.classList.remove('field-error'); }
  });
  document.getElementById('as-input-branch-mgr').value = '';
  document.getElementById('as-input-branch-mgr').classList.remove('field-error');
  document.getElementById('branch-status').value = 'Active';
  document.getElementById('as-input-branch-mgr_id')?.remove();
  enforceDropdownOnBlur('as-input-branch-mgr');
}

function closeBranchModal(e) { closeModal('modal-add-branch', e); }

function saveBranch() {
  const branchName   = document.getElementById('branch-name');
  const managerInput = document.getElementById('as-input-branch-mgr');
  const managerId    = document.getElementById('as-input-branch-mgr_id')?.value || '';
  const status       = document.getElementById('branch-status').value || 'Active';
  const csrf         = document.getElementById('branch_csrf_token')?.value || '';
  const btn          = document.getElementById('btn-save-branch');

  branchName.classList.remove('field-error');
  managerInput.classList.remove('field-error');

  if (!branchName.value.trim()) {
    branchName.classList.add('field-error');
    showNotification('Required', 'Branch Name is mandatory.', 'warning');
    return;
  }
  if (managerInput.value.trim() && !managerId) {
    managerInput.classList.add('field-error');
    showNotification('Invalid Manager', 'Please select a manager from the dropdown list or leave the field empty.', 'warning');
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  fetch('api/companyprofile/add_branch.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      branch_name: branchName.value.trim(), branch_manager_id: managerId, branch_status: status,
      branch_phone:   document.getElementById('branch-phone').value.trim(),
      branch_email:   document.getElementById('branch-email').value.trim(),
      branch_city:    document.getElementById('branch-city').value.trim(),
      branch_address: document.getElementById('branch-address').value.trim(),
      csrf_token: csrf
    })
  })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('branches');
        showNotification('Success', result.message, 'success');
        closeBranchModal();
        inited.delete('branch-offices');
        goPage('branch-offices');
      } else {
        showNotification('Error', result.message, 'error');
      }
    })
    .catch(err => showNotification('Error', 'Network error: ' + err.message, 'error'))
    .finally(() => restoreButton(btn, originalHtml));
}

async function openEditBranchModal(name, manager, phone, email, city, address, status, branchId) {
  const dash = v => (v === '—' ? '' : v);
  document.getElementById('edit_branch_id').value      = branchId;
  document.getElementById('edit-branch-name').value    = name;
  document.getElementById('edit-branch-phone').value   = dash(phone);
  document.getElementById('edit-branch-email').value   = dash(email);
  document.getElementById('edit-branch-city').value    = dash(city);
  document.getElementById('edit-branch-address').value = dash(address);
  document.getElementById('edit-branch-status').value  = status;
  const mgrInput = document.getElementById('as-input-edit-branch-mgr');
  mgrInput.value = dash(manager);
  mgrInput.classList.remove('field-error');
  document.getElementById('as-input-edit-branch-mgr_id')?.remove();

  let employees = dropdownCache['employees:'];
  if (!employees) {
    try {
      const res = await fetch('api/1common/fetch_dropdown.php?type=employees').then(r => r.json());
      if (res.success && res.data) dropdownCache['employees:'] = employees = res.data;
    } catch (err) { console.warn('Failed to preload employees:', err); }
  }
  if (employees && manager && manager !== '—') {
    const emp = employees.find(e => e.label.includes(manager));
    if (emp) {
      const hidden = document.createElement('input');
      hidden.type  = 'hidden';
      hidden.id    = 'as-input-edit-branch-mgr_id';
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
  const branchId     = document.getElementById('edit_branch_id').value;
  const branchName   = document.getElementById('edit-branch-name');
  const managerInput = document.getElementById('as-input-edit-branch-mgr');
  const managerId    = document.getElementById('as-input-edit-branch-mgr_id')?.value || '';
  const status       = document.getElementById('edit-branch-status').value;
  const csrf         = document.getElementById('edit_branch_csrf_token').value;
  const btn          = document.getElementById('btn-update-branch');

  branchName.classList.remove('field-error');
  managerInput.classList.remove('field-error');

  if (!branchName.value.trim()) {
    branchName.classList.add('field-error');
    showNotification('Required', 'Branch Name is mandatory.', 'warning');
    return;
  }
  if (managerInput.value.trim() && !managerId) {
    managerInput.classList.add('field-error');
    showNotification('Invalid Selection', 'Please select a valid manager from the dropdown, or leave the field empty.', 'warning');
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = `<i data-lucide="loader-2" class="spin" size="13"></i> Saving...`;
  lcIcons(btn);

  fetch('api/companyprofile/update_branch.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      branch_id: branchId, branch_name: branchName.value.trim(), branch_manager_id: managerId,
      branch_status: status,
      branch_phone:   document.getElementById('edit-branch-phone').value.trim(),
      branch_email:   document.getElementById('edit-branch-email').value.trim(),
      branch_city:    document.getElementById('edit-branch-city').value.trim(),
      branch_address: document.getElementById('edit-branch-address').value.trim(),
      csrf_token: csrf
    })
  })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        clearDropdownCache('branches');
        showNotification('Success', result.message, 'success');
        closeEditBranchModal();
        inited.delete('branch-offices');
        goPage('branch-offices');
      } else {
        showNotification('Error', result.message, 'error');
        restoreButton(btn, originalHtml);
      }
    })
    .catch(err => { showNotification('Error', 'Network error: ' + err.message, 'error'); restoreButton(btn, originalHtml); })
    .finally(() => restoreButton(btn, originalHtml));
}

// ═══════════════════════════════════════════════════════════════════════
// ASSET MANAGEMENT (placeholders — functionality pending backend)
// ═══════════════════════════════════════════════════════════════════════

function openAssetModal()  { openModal('modal-add-asset'); }
function closeAssetModal(e){ closeModal('modal-add-asset', e); }

function saveNewAsset() {
  const name = document.getElementById('as-new-name').value;
  if (!name) { alert('Asset Name is required.'); return; }
  alert(`Asset "${name}" registered successfully.`);
  closeAssetModal();
}

function openReassignModal(assetName, currentCustodian) {
  document.getElementById('reassign-display-name').textContent = assetName;
  document.getElementById('reassign-display-curr').textContent = currentCustodian;
  document.getElementById('as-input-reassign').value = '';
  openModal('modal-reassign-asset');
}

function closeReassignModal(e) { closeModal('modal-reassign-asset', e); }

function saveReassignment() {
  const assignee  = document.getElementById('as-input-reassign').value;
  const assetName = document.getElementById('reassign-display-name').textContent;
  if (!assignee) { alert('Please select a new custodian.'); return; }
  alert(`Reassignment Successful: ${assetName} has been transferred to ${assignee}.`);
  closeReassignModal();
}

// ═══════════════════════════════════════════════════════════════════════
// ORG CHART
// ═══════════════════════════════════════════════════════════════════════

function fetchOrgChartData() {
  const container = document.getElementById('dept-tree-container');
  if (!container) return Promise.reject('Container not found');
  container.innerHTML = '<div style="padding:40px;text-align:center;"><i data-lucide="loader-2" class="spin"></i> Loading structure...</div>';
  lcIcons(container);
  return fetch('api/companyprofile/fetch_org_chart.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      renderOrgChartFromData(res.data);
    })
    .catch(err => {
      container.innerHTML = `<div style="padding:40px;text-align:center;color:var(--danger);">Error: ${err.message}</div>`;
      throw err;
    });
}

function renderOrgChartFromData(data) {
  const container = document.getElementById('dept-tree-container');
  // Build markup using array join instead of string concatenation in a loop
  const html = data.departments.map(dept => {
    const jobsHtml = dept.jobs?.length
      ? '<ul class="submenu-jobs" style="padding-top:20px;">' +
        dept.jobs.map(job => `
          <li><div class="oc-node oc-staff" style="width:160px;border-top:2px solid var(--primary-light);">
            <div class="oc-node-body" style="padding:12px;text-align:center;flex-direction:column;">
              <div class="oc-node-name" style="font-size:.75rem;font-weight:700;">${job.title}</div>
              <div class="oc-node-role" style="font-size:.6rem;margin-top:4px;">${job.count} employees</div>
            </div></div></li>`).join('') + '</ul>'
      : '';
    return `
      <li>
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
        ${jobsHtml}
      </li>`;
  }).join('');

  container.innerHTML = html;
  document.getElementById('oc-total-badge')?.textContent && (document.getElementById('oc-total-badge').textContent = `${data.total} TOTAL`);
  const deptFooter = document.getElementById('oc-dept-count');
  if (deptFooter) deptFooter.textContent = `${data.departments.length} Departments`;
  lcIcons(container);
}

// ═══════════════════════════════════════════════════════════════════════
// EMPLOYEE ONBOARDING WIZARD
// ═══════════════════════════════════════════════════════════════════════

let currentObStep  = 1;
const totalObSteps = 6;

// Step field map: which field IDs to run special validators on per step
const _stepFieldMap = {
  1: ['o-dob'],
  2: ['o-email'],
  3: [], // dynamic fields handled separately
  4: ['o-sal', 'o-tin', 'o-acc'],
  5: ['o-ephone']
};

function moveOnboarding(dir) {
  if (dir > 0 && !validateStep(currentObStep)) return;
  const t = currentObStep + dir;
  if (t >= 1 && t <= totalObSteps) jumpToStep(t);
}

function validateStep(step) {
  // 1. Check all required fields are filled
  const stepFields = [...document.querySelectorAll(`#ob-step-${step} .master-req`)];
  let allValid = true;
  stepFields.forEach(f => {
    const empty = !f.value.trim();
    f.classList.toggle('field-error', empty);
    if (empty) allValid = false;
  });
  if (!allValid) {
    showNotification('Missing Required Fields', 'Please fill in all required fields before proceeding.', 'warning');
    return false;
  }
  // 2. Run type-specific validators for this step's fields
  const ids = [...(_stepFieldMap[step] || [])];
  if (step === 3) {
    ['o-hire', 'o-end-date', 'o-hours'].forEach(id => { if (document.getElementById(id)) ids.push(id); });
  }
  for (const id of ids) {
    const result = validateField(id);
    if (!result.valid) { showNotification('Invalid Input', result.error, 'warning'); return false; }
  }
  // 3. Cross-field: end date must be after hire date
  if (step === 3) {
    const hireField = document.getElementById('o-hire');
    const endField  = document.getElementById('o-end-date');
    if (hireField?.value && endField?.value && new Date(endField.value) <= new Date(hireField.value)) {
      endField.classList.add('field-error');
      showNotification('Invalid Date Range', 'Contract end date must be after start date.', 'warning');
      return false;
    }
  }
  return true;
}

function jumpToStep(step) {
  // Block forward jumps if current step has unfilled required fields
  if (step > currentObStep) {
    const cur = [...document.querySelectorAll(`#ob-step-${currentObStep} .master-req`)];
    if (cur.some(i => !i.value.trim())) { moveOnboarding(1); return; }
  }
  currentObStep = step;
  if (step === 6) renderSummary();

  // Show only the active step
  document.querySelectorAll('#p-add-employee .form-section-content').forEach(s => s.classList.remove('active'));
  document.getElementById(`ob-step-${step}`).classList.add('active');

  // Update step indicator sidebar
  document.querySelectorAll('.step-pro').forEach((item, idx) => {
    const n = idx + 1;
    item.classList.toggle('active', n === step);
    item.classList.toggle('done', n < step);
    item.querySelector('.step-idx').innerHTML = n < step ? '<i data-lucide="check" size="14"></i>' : n;
  });

  // Bottom navigation controls
  const dots        = document.getElementById('ob-dots');
  const next        = document.getElementById('ob-next');
  const bottomSave  = document.getElementById('btn-save-master-bottom');
  const prev        = document.getElementById('ob-prev');

  prev.style.visibility   = step === 1 ? 'hidden' : 'visible';
  dots.style.display      = step === 6 ? 'none' : 'flex';
  next.style.display      = step === 6 ? 'none' : 'flex';
  bottomSave.style.display = step === 6 ? 'flex' : 'none';
  next.innerHTML = step === 5
    ? 'Review All Steps'
    : 'Next Step <i data-lucide="chevron-right" size="14"></i>';

  document.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('active', i + 1 === step));
  document.getElementById('master-progress-line').style.width = (step / totalObSteps * 100) + '%';

  validateMasterRecord();
  lcIcons(document.getElementById('p-add-employee'));

  // Silently refresh CSRF token on each step change
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

  // Returns the field value or an em-dash placeholder
  const getV = (id) => {
    const el = document.getElementById(id);
    return el?.value.trim() || '<span style="color:var(--muted);font-weight:400;">—</span>';
  };

  const fullName = `${getV('o-fname')} ${document.getElementById('o-mname')?.value || ''} ${getV('o-lname')}`.replace(/\s+/g, ' ');
  document.getElementById('rev-full-name').innerHTML    = fullName;
  document.getElementById('rev-badge-dept').textContent = getV('o-dept');
  document.getElementById('rev-badge-type').textContent = getV('o-etype');

  // Avatar sync
  const srcImg = document.getElementById('avatar-img-output');
  const tgtImg = document.getElementById('rev-img');
  if (srcImg?.style.display !== 'none') { tgtImg.src = srcImg.src; tgtImg.style.opacity = '1'; }
  else { tgtImg.src = 'assets/img/bgwhitel.png'; tgtImg.style.opacity = '0.3'; }

  // Static review fields
  const staticMap = [
    ['rev-dob', 'o-dob'], ['rev-gender', 'o-gender'], ['rev-phone', 'o-phone'],
    ['rev-email', 'o-email'], ['rev-pos', 'o-pos'], ['rev-dept', 'o-dept'],
    ['rev-etype', 'o-etype'], ['rev-bank', 'o-bank'], ['rev-acc', 'o-acc'],
    ['rev-tin', 'o-tin'], ['rev-ename', 'o-ename'], ['rev-relation', 'o-idno'], ['rev-ephone', 'o-ephone']
  ];
  staticMap.forEach(([revId, srcId]) => { document.getElementById(revId).innerHTML = getV(srcId); });

  const rawSal = document.getElementById('o-sal')?.value;
  document.getElementById('rev-sal').innerHTML = rawSal ? 'ETB ' + parseFloat(rawSal).toLocaleString() : '—';

  // Dynamic employment fields (Step 3)
  const dynContainer  = document.getElementById('dynamic-employment-fields');
  const dynSummaryArea = document.getElementById('rev-dynamic-fields-area');
  dynSummaryArea.innerHTML = '';
  if (dynContainer) {
    dynContainer.querySelectorAll('input').forEach(input => {
      if (input.type === 'hidden') return;
      const labelText = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || 'Detail';
      let val = input.value.trim() || '—';
      if (input.id === 'o-probation_val') {
        val = input.value.trim() === '0' ? 'No probation' : (input.value.trim() ? input.value.trim() + ' Days' : '—');
      }
      const row = document.createElement('div');
      row.className = 'review-row';
      row.innerHTML = `<span class="rev-label">${labelText}</span><span class="rev-val">${val}</span>`;
      dynSummaryArea.appendChild(row);
    });
  }
}

// Debounce timer for input-driven validation
let _valDebounce;

// Updates all save/next button states and clears resolved error highlights
function validateMasterRecord() {
  const all  = [...document.querySelectorAll('#p-add-employee .master-req')];
  const cur  = [...document.querySelectorAll(`#ob-step-${currentObStep} .master-req`)];
  const allOk  = all.every(i => i.value.trim());
  const stepOk = cur.every(i => i.value.trim());

  const commitTop = document.getElementById('btn-save-master');
  const commitBtn = document.getElementById('btn-save-master-bottom');
  const next      = document.getElementById('ob-next');
  const valText   = document.getElementById('master-val-text');

  if (next) { next.style.opacity = stepOk ? '1' : '0.4'; next.style.cursor = stepOk ? 'pointer' : 'not-allowed'; }
  [commitTop, commitBtn].forEach(btn => {
    if (!btn) return;
    btn.disabled    = !allOk;
    btn.style.opacity = allOk ? '1' : '0.4';
    btn.style.cursor  = allOk ? 'pointer' : 'not-allowed';
  });

  valText.innerHTML   = allOk ? '<i data-lucide="check-circle" size="12"></i> Verified' : '* Required fields missing';
  valText.style.color = allOk ? 'var(--success)' : 'var(--danger)';

  // Clear error state on fields that now have a valid value
  all.forEach(i => {
    if (!i.value.trim()) return;
    if (VALIDATORS[i.id]) { if (VALIDATORS[i.id](i.value).valid) i.classList.remove('field-error'); }
    else i.classList.remove('field-error');
  });

  lcIcons(valText);
}

// Delegated input listener — validates the wizard form with 150 ms debounce
document.addEventListener('input', (e) => {
  if (e.target.closest('#p-add-employee') && ['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) {
    clearTimeout(_valDebounce);
    _valDebounce = setTimeout(validateMasterRecord, 150);
  }
});

// Final validation before submit: marks all missing required fields
function validateAllSteps() {
  const allReq = [...document.querySelectorAll('#p-add-employee .master-req')];
  let valid = true;
  allReq.forEach(input => {
    const isEmpty = !input.value.trim();
    input.classList.toggle('field-error', isEmpty);
    if (isEmpty) valid = false;
  });
  return valid;
}

function saveNewEmployee() {
  if (!validateAllSteps()) {
    showNotification('Validation Failed', 'Please correct all errors before submitting.', 'error');
    validateMasterRecord();
    return;
  }

  const btnTop    = document.getElementById('btn-save-master');
  const btnBottom = document.getElementById('btn-save-master-bottom');
  [btnTop, btnBottom].filter(Boolean).forEach(btn => {
    btn.disabled  = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="spin"></i> Saving...`;
    lucide.createIcons({ nodes: [btn] });
  });

  const getVal       = id => document.getElementById(id)?.value?.trim() || '';
  const getHiddenVal = id => document.getElementById(id + '_id')?.value || '';

  const fd = new FormData();
  // Personal
  ['fname','mname','lname','dob','gender','marital','nat','pob','phone','email','addr','city','zip']
    .forEach(f => fd.append(f === 'fname' ? 'first_name' : f === 'mname' ? 'middle_name' : f === 'lname' ? 'last_name'
      : f === 'dob' ? 'date_of_birth' : f === 'marital' ? 'marital_status' : f === 'nat' ? 'nationality'
      : f === 'pob' ? 'place_of_birth' : f === 'phone' ? 'personal_phone' : f === 'email' ? 'personal_email'
      : f === 'addr' ? 'address' : f === 'zip' ? 'postal_code' : f, getVal('o-' + f)));
  // Employment IDs (hidden fields)
  ['dept','branch','pos','etype'].forEach(f => fd.append(
    f === 'dept' ? 'department_id' : f === 'branch' ? 'branch_id' : f === 'pos' ? 'job_position_id' : 'employment_type_id',
    getHiddenVal('o-' + f)));
  // Optional dynamic fields
  const optMap = { 'o-hire': 'hire_date', 'o-end-date': 'contract_end_date', 'o-probation': 'probation_period',
    'o-probation_val': 'probation_days', 'o-reports_id': 'reports_to_id', 'o-hours': 'hours_per_week',
    'o-project': 'project_name', 'o-institution': 'institution' };
  Object.entries(optMap).forEach(([id, key]) => {
    const el = document.getElementById(id);
    if (el) fd.append(key, el.value.trim?.() ?? el.value);
  });
  // Finance & Emergency
  ['sal','bank','acc','tin'].forEach(f => fd.append(
    f === 'sal' ? 'salary' : f === 'bank' ? 'bank_name' : f === 'acc' ? 'bank_account' : 'tin', getVal('o-' + f)));
  ['ename','ephone','idno'].forEach(f => fd.append(
    f === 'ename' ? 'emergency_name' : f === 'ephone' ? 'emergency_phone' : 'emergency_relation', getVal('o-' + f)));
  // CSRF
  const csrf = document.getElementById('csrf_token') || document.querySelector('input[name="csrf_token"]');
  fd.append('csrf_token', csrf?.value || '');
  // Avatar
  const avatar = document.getElementById('avatar-upload');
  if (avatar?.files.length) fd.append('avatar', avatar.files[0]);

  const _SERVER_FIELD_MAP = {
    first_name: 'o-fname', middle_name: 'o-mname', last_name: 'o-lname', date_of_birth: 'o-dob',
    gender: 'o-gender', department_id: 'o-dept', branch_id: 'o-branch',
    job_position_id: 'o-pos', employment_type_id: 'o-etype', hire_date: 'o-hire',
    contract_end_date: 'o-end-date', salary: 'o-sal', emergency_phone: 'o-ephone'
  };

  fetch('api/employees/add_employee.php', { method: 'POST', body: fd })
    .then(r => {
      if (!r.ok) return r.text().then(t => { throw new Error(`HTTP ${r.status}: ${t.substring(0, 100)}`); });
      return r.json();
    })
    .then(result => {
      if (result.success) {
        clearDropdownCache('employees');
        showNotification('Success', result.message, 'success');
        setTimeout(() => goPage('employee-directory'), 1500);
      } else if (result.errors) {
        document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
        Object.keys(result.errors).forEach(f => document.getElementById(_SERVER_FIELD_MAP[f] || f)?.classList.add('field-error'));
        showNotification('Validation Failed', result.message || 'Please check highlighted fields.', 'error');
      } else {
        showNotification('Error', result.message || 'Failed to create employee.', 'error');
      }
    })
    .catch(err => showNotification('Network Error', err.message || 'Could not connect to server.', 'error'))
    .finally(() => {
      [document.getElementById('btn-save-master'), document.getElementById('btn-save-master-bottom')]
        .forEach(btn => {
          if (!btn?.isConnected) return;
          btn.disabled  = false;
          btn.innerHTML = btn.id === 'btn-save-master'
            ? `<i data-lucide="shield-check"></i> Commit Record`
            : `<i data-lucide="user-plus"></i> Add Employee`;
          lucide.createIcons({ nodes: [btn] });
        });
    });
}

// ═══════════════════════════════════════════════════════════════════════
// DYNAMIC EMPLOYMENT FIELDS (Step 3 of wizard)
// ═══════════════════════════════════════════════════════════════════════

function updateEmploymentFields(type) {
  const container = document.getElementById('dynamic-employment-fields');
  if (!container) return;
  if (!type) { container.style.display = 'none'; container.innerHTML = ''; return; }

  container.style.display = 'grid';

  const probationHtml = `
    <div class="form-group">
      <label>Probation Duration (Days) *</label>
      <div style="display:flex;gap:10px;align-items:center;">
        <input type="number" id="o-probation_val" class="form-ctrl master-req" placeholder="e.g. 90" value="60" style="width:100px;">
        <span style="font-size:.75rem;color:var(--muted);">Total days from hire date</span>
        <input type="hidden" id="o-probation" value="">
      </div>
    </div>`;

  const reportingHtml = `
    <div class="form-group" style="grid-column:span 2;">
      <label>Reporting To</label>
      <div class="as-combo-container">
        <input type="text" id="o-reports" class="form-ctrl" data-dropdown-type="employees"
               placeholder="Search manager..." onfocus="showAsDrop('as-drop-reports')"
               oninput="filterAsDrop('o-reports','as-drop-reports')" autocomplete="off">
        <div class="as-combo-results" id="as-drop-reports"></div>
      </div>
    </div>`;

  const htmlMap = {
    'full-time':  `<div class="form-group"><label>Hiring Date *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>${probationHtml}${reportingHtml}`,
    'contract':   `<div class="form-group"><label>Contract Start *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
                   <div class="form-group"><label>Contract End *</label><input type="date" class="form-ctrl master-req" id="o-end-date"></div>
                   ${probationHtml}${reportingHtml}`,
    'part-time':  `<div class="form-group"><label>Hiring Date *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
                   <div class="form-group"><label>Hours Per Week</label><input type="number" class="form-ctrl" id="o-hours" placeholder="e.g. 20"></div>
                   ${probationHtml}${reportingHtml}`,
    'internship': `<div class="form-group"><label>Internship Start *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
                   <div class="form-group"><label>Internship End *</label><input type="date" class="form-ctrl master-req" id="o-end-date"></div>
                   <div class="form-group" style="grid-column:span 1;"><label>Academic Institution</label>
                     <input type="text" class="form-ctrl" id="o-institution" placeholder="Ex: Addis Ababa University" maxlength="200"></div>
                   ${reportingHtml.replace('Reporting To', 'Assigned Mentor')}`,
    'temporary':  `<div class="form-group"><label>Project Name *</label><input type="text" class="form-ctrl master-req" id="o-project" placeholder="e.g. Infrastructure Audit" maxlength="200"></div>
                   <div class="form-group"><label>Assignment Start *</label><input type="date" class="form-ctrl master-req" id="o-hire"></div>
                   ${reportingHtml.replace('Reporting To', 'Project Supervisor')}`
  };

  container.innerHTML = htmlMap[type] || `<p style="color:var(--muted);grid-column:span 3;">No additional fields required for this employment type.</p>`;
  lcIcons(container);

  // Attach input listeners for live validation
  container.querySelectorAll('input, select').forEach(i => i.addEventListener('input', validateMasterRecord));
  enforceDropdownOnBlur('o-reports');

  // Keep hidden probation field in sync with displayed days input
  const pVal    = document.getElementById('o-probation_val');
  const pHidden = document.getElementById('o-probation');
  if (pVal && pHidden) {
    const syncProbation = () => {
      const d = pVal.value.trim();
      pHidden.value = d === '0' || d === '' ? 'No probation' : d + ' Days';
      validateMasterRecord();
    };
    pVal.addEventListener('input', syncProbation);
    syncProbation();
  }

  validateMasterRecord();
}

// ── PAGE INIT ROUTER ──────────────────────────────────────────────────────
// Called after each page load; initialises tables and widgets for the given page ID
function initPage(id) {
  if (inited.has(id)) return;
  inited.add(id);

  // Helper for the common table case (DRY)
  const tbl = (containerId, apiUrl, opts) => initServerPaginatedTable(containerId, apiUrl, opts);

  // Action button column templates (reused across many tables)
  const actionEye      = () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View"><i data-lucide="eye" size="10"></i></button></div>`;
  const actionEyeTrash = () => `<div class="flex-row"><button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;"><i data-lucide="trash-2" size="10"></i></button></div>`;
  const trash          = () => `<div class="flex-row"><button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;"><i data-lucide="trash-2" size="10"></i></button></div>`;
  const editTrashBtns  = (editCb) => (v, row) => `
    <div style="display:flex;align-items:center;gap:8px;justify-content:center;">
      <button class="btn btn-xs btn-secondary" onclick="${editCb(v, row)}" title="Edit"><i data-lucide="edit" size="12"></i></button>
      <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;" title="Delete"><i data-lucide="trash-2" size="12"></i></button>
    </div>`;

  switch (id) {
    case 'dashboard': initDashboard(); break;
    case 'org-chart': initOrgChart(); break;

    case 'departments':
      tbl('tbl-departments', 'api/companyprofile/fetch_departments.php', {
        columns: [
          { key: 'name',             label: 'Department Name' },
          { key: 'head',             label: 'Head of Department' },
          { key: 'emp',              label: 'Employees' },
          { key: 'status',           label: 'Status' },
          { key: 'updated_by_name',  label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: editTrashBtns((v, row) => `openEditDeptModal('${row.name.replace(/'/g, "\\'")}', '${row.head || ''}', ${row.id || 0})`) }
        ],
        perPage: 15, searchPlaceholder: 'Search department or head...'
      });
      break;

    case 'job-positions':
      tbl('tbl-job-positions', 'api/companyprofile/fetch_jobpositions.php', {
        columns: [
          { key: 'title',           label: 'Job Title' },
          { key: 'dept',            label: 'Department' },
          { key: 'count',           label: 'Employees' },
          { key: 'status',          label: 'Status' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: editTrashBtns((v, row) => `openEditJobModal('${row.title.replace(/'/g, "\\'")}', '${row.dept}', ${row.id || 0})`) }
        ],
        perPage: 15, searchPlaceholder: 'Search job title or department...'
      });
      break;

    case 'branch-offices':
      tbl('tbl-branch-offices', 'api/companyprofile/fetch_branchoffices.php', {
        columns: [
          { key: 'name',            label: 'Branch Name' },
          { key: 'manager',         label: 'Branch Manager' },
          { key: 'phone',           label: 'Phone' },
          { key: 'email',           label: 'Email' },
          { key: 'location',        label: 'Location' },
          { key: 'emp',             label: 'Staff' },
          { key: 'status',          label: 'Status', render: v => v === 'Active' ? statusBadge.active : statusBadge.inactive },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: editTrashBtns((v, row) => `openEditBranchModal('${row.name.replace(/'/g, "\\'")}','${row.manager || ''}','${row.phone || ''}','${row.email || ''}','${row.location || ''}','${row.address || ''}','${row.status}',${row.id || 0})`) }
        ],
        perPage: 15, searchPlaceholder: 'Search branch name or manager...'
      });
      break;

    case 'employee-directory':
      tbl('tbl-employees', 'api/employees/fetch_empprofiles.php', {
        columns: [
          { key: 'id',              label: 'Emp ID' },
          { key: 'fname',           label: 'First Name' },
          { key: 'mname',           label: 'Middle Name' },
          { key: 'lname',           label: 'Last Name' },
          { key: 'uname',           label: 'Username' },
          { key: 'gender',          label: 'Gender' },
          { key: 'dob',             label: 'Date of Birth' },
          { key: 'hire',            label: 'Hire Date' },
          { key: 'status', label: 'Status', render: v => {
              const s = v.toLowerCase();
              return s === 'active' ? statusBadge.active : (s === 'inactive' || s === 'terminated') ? statusBadge.inactive : b('warning', v);
          }},
          { key: 'marital',         label: 'Marital Status' },
          { key: 'phone',           label: 'Phone' },
          { key: 'email',           label: 'Email' },
          { key: 'dept',            label: 'Department' },
          { key: 'position',        label: 'Job Position' },
          { key: 'branch',          label: 'Branch name' },
          { key: 'type',            label: 'Emp Type' },
          { key: 'bankname',        label: 'Bank name' },
          { key: 'bankacc',         label: 'Bank Account' },
          { key: 'tin',             label: 'Tin number' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: 'created',         label: 'Created At' },
          { key: '_', label: 'Actions', render: actionEyeTrash }
        ],
        searchPlaceholder: 'Search full name, job title, department...', perPage: 15
      });
      break;

    case 'add-employee':
      currentObStep = 1;
      jumpToStep(1);
      // Blur validation for wizard fields with special rules
      ['o-dob', 'o-email', 'o-sal', 'o-tin', 'o-acc', 'o-ephone'].forEach(id => {
        document.getElementById(id)?.addEventListener('blur', function () { validateField(this.id); });
      });
      // Delegated blur for dynamic fields (rendered after page load)
      document.getElementById('p-add-employee')?.addEventListener('blur', e => {
        if (['o-hire', 'o-end-date', 'o-hours'].includes(e.target.id)) validateField(e.target.id);
      }, true);
      // Allow keyboard interaction on date fields
      ['o-dob', 'o-hire', 'o-end-date'].forEach(id => {
        const f = document.getElementById(id);
        if (f) { f.removeAttribute('readonly'); f.addEventListener('keydown', e => e.stopPropagation()); }
      });
      setTimeout(() => validateMasterRecord(), 50);
      break;

    case 'employment-types': initEmploymentTypesCards(); break;

    case 'probation-tracker':
      tbl('tbl-probation', 'api/employees/fetch_probation.php', {
        columns: [
          { key: 'name',  label: 'Employee' },
          { key: 'dept',  label: 'Department' },
          { key: 'start', label: 'Probation Start' },
          { key: 'end',   label: 'Probation End' },
          { key: 'days',  label: 'Days Left', render: v => {
              const d = parseInt(v);
              if (d < 0)   return `<span style="color:var(--danger);font-weight:bold;">Overdue (${Math.abs(d)})</span>`;
              if (d <= 30) return `<span style="color:var(--warning);font-weight:bold;">${d} Days</span>`;
              return `${d} Days`;
          }},
          { key: 'status', label: 'Probation Status', render: (v, row) => {
              if (v === 'Completed') return b('success', 'Completed');
              if (v === 'Failed')    return b('danger',  'Failed');
              if (v === 'Extended')  return b('warning', 'Extended');
              const d = parseInt(row.days);
              if (d < 0)   return b('danger',  'Overdue');
              if (d <= 14) return b('warning', 'Ending Soon');
              return b('info', 'Active');
          }},
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: (v, row) => `
            <div style="display:flex;align-items:center;gap:10px;justify-content:center;">
              <button class="btn btn-xs" style="background:#f1f5f9;color:var(--primary);border:1px solid #e2e8f0;padding:5px;border-radius:6px;" title="Evaluate Employee" onclick="openProbationEvalModal('${row.employee_id}','${row.name.replace(/'/g, "\\'")}')">
                <i data-lucide="clipboard-check" size="13"></i>
              </button>
              <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:5px;border-radius:6px;cursor:pointer;">
                <i data-lucide="trash-2" size="13"></i>
              </button>
            </div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search employee name or department...'
      });
      break;

    case 'contract-renewals':
      tbl('tbl-contract-renewals', 'api/employees/fetch_contracts.php', {
        columns: [
          { key: 'name', label: 'Employee' }, { key: 'dept', label: 'Department' }, { key: 'start', label: 'Start Date' },
          { key: 'expiry', label: 'Contract Expiry Date', render: v => {
              if (v === 'Permanent') return '<span style="color:var(--success);font-weight:600;">Permanent</span>';
              const d = new Date(v);
              return isNaN(d) ? v : d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
          }},
          { key: 'days', label: 'Status', render: (v, row) => {
              if (row.expiry === 'Permanent') return b('success', 'Permanent');
              const d = parseInt(v);
              return d < 0 ? b('danger', 'Expired') : d <= 15 ? b('danger', 'Critical') : d <= 30 ? b('warning', 'Due Soon') : b('success', 'Active');
          }},
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Renew Contract"><i data-lucide="refresh-cw" size="10"></i></button><button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search employee name or department...'
      });
      break;

    case 'retirement-planner':
      tbl('tbl-retirement', 'api/employees/fetch_retirement.php', {
        columns: [
          { key: 'name', label: 'Employee' }, { key: 'dept', label: 'Department' }, { key: 'age', label: 'Age' },
          { key: 'tenure', label: 'Service Period' }, { key: 'date', label: 'Retirement Date' },
          { key: 'days', label: 'Status', render: v => { const d = parseInt(v); return d < 0 ? b('neutral','Retired') : d <= 90 ? b('danger',`Upcoming (${d}D)`) : d <= 365 ? b('warning','Within Year') : b('info','Active'); }},
          { key: 'pension', label: 'Pension Status', render: () => b('warning', 'In Progress') },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Succession Plan"><i data-lucide="user-plus" size="10"></i></button><button class="btn btn-xs btn-primary" title="Clearance"><i data-lucide="clipboard-check" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search retirement forecast...'
      });
      break;

    case 'former-employees':
      tbl('tbl-former-employees', 'api/employees/fetch_former_employees.php', {
        columns: [
          { key: 'name', label: 'Employee' }, { key: 'dept', label: 'Last Department' }, { key: 'role', label: 'Last Role' }, { key: 'exitDate', label: 'Exit Date' },
          { key: 'type', label: 'Reason', render: v => v === 'Terminated' ? b('danger', v) : b('neutral', v) },
          { key: 'duration', label: 'Duration' },
          { key: 'rehire', label: 'Rehire possibility', render: v => v === 'No' ? b('danger', 'No') : b('success', 'Yes') },
          { key: '_', label: 'Actions', render: trash }
        ],
        perPage: 15, searchPlaceholder: 'Search former employees...'
      });
      break;

    case 'asset-tracking':
      tbl('tbl-assets', 'api/employees/fetch_assets.php', {
        columns: [
          { key: 'id', label: 'Item Code' }, { key: 'name', label: 'Asset Name' }, { key: 'cat', label: 'Category' },
          { key: 'serial', label: 'Serial number' },
          { key: 'val', label: 'Asset Value', render: v => v ? 'ETB ' + parseFloat(v).toLocaleString() : '—' },
          { key: 'user_prev', label: 'Previous custodian' }, { key: 'user', label: 'Current custodian' },
          { key: 'loc', label: 'Location' }, { key: 'war', label: 'Warranty' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: (v, row) => `<div style="display:flex;gap:8px;justify-content:center;"><button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs btn-secondary" onclick="openReassignModal('${row.name}','${row.user}')" title="Reassign"><i data-lucide="shuffle" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search assets...'
      });
      break;

    case 'document-vault': initVaultMatrix(); break;

    case 'employee-vault':
      if (pendingEmployeeVaultData) {
        const { name, id: empId } = pendingEmployeeVaultData;
        document.getElementById('v-emp-name').textContent = name;
        document.getElementById('v-emp-id').textContent   = empId + ' • Personnel Archive';
        const listContainer = document.getElementById('vault-docs-list');
        listContainer.innerHTML = '';
        let uploadCount = 0;
        VAULT_SCHEMA.forEach(doc => {
          const isUploaded = Math.random() > 0.4; // TODO: replace with real API call
          if (isUploaded) uploadCount++;
          const row = document.createElement('div');
          row.className = 'doc-row';
          row.innerHTML = `
            <div class="doc-icon-box ${isUploaded ? 'uploaded' : 'missing'}"><i data-lucide="${isUploaded ? 'file-check' : 'file-question-mark'}" size="18"></i></div>
            <div class="doc-meta"><div class="doc-name">${doc.name}</div><div class="doc-cat">${doc.cat}</div></div>
            <div class="doc-status">${isUploaded ? '<span class="badge badge-success" style="font-size:10px">Verified</span>' : '<span class="badge badge-neutral" style="font-size:10px;opacity:.7;">Pending</span>'}</div>
            <div class="doc-actions">
              ${isUploaded
                ? `<button class="btn btn-secondary btn-xs" title="View" onclick="showNotification('Vault','Opening file...','info')"><i data-lucide="eye" size="14"></i> View</button><button class="btn btn-secondary btn-xs" style="min-width:34px;"><i data-lucide="refresh-cw" size="14"></i></button>`
                : `<button class="btn btn-primary btn-xs btn-upload-pro" onclick="showNotification('Vault','Ready for upload','info')"><i data-lucide="plus" size="14"></i> Add Document</button>`}
            </div>`;
          listContainer.appendChild(row);
        });
        const total = VAULT_SCHEMA.length;
        document.getElementById('v-count-upload').textContent      = uploadCount;
        document.getElementById('v-count-missing').textContent     = total - uploadCount;
        document.getElementById('v-compliance-percent').textContent = Math.round((uploadCount / total) * 100) + '%';
        lcIcons(listContainer);
        pendingEmployeeVaultData = null;
      }
      break;

    case 'job-vacancies':
      tbl('tbl-vacancies', 'api/talent/fetch_vacancies.php', {
        columns: [
          { key: 'title', label: 'Position' }, { key: 'dept', label: 'Department' }, { key: 'branch', label: 'Branch' },
          { key: 'type', label: 'Type' }, { key: 'posted', label: 'Posted' }, { key: 'deadline', label: 'Deadline' },
          { key: 'status', label: 'Status', render: v => {
              const s = v.toLowerCase();
              return s === 'open' ? b('success','Open') : s === 'closed' ? b('danger','Closed') : s === 'filled' ? b('neutral','Filled') : s === 'on hold' ? b('warning','On Hold') : b('info', v);
          }},
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: actionEyeTrash }
        ],
        perPage: 15, searchPlaceholder: 'Search positions, departments...'
      });
      break;

    case 'candidates':
      tbl('tbl-candidates', 'api/talent/fetch_candidates.php', {
        columns: [
          { key: 'name', label: 'Candidate' }, { key: 'position', label: 'Applied For' }, { key: 'applied', label: 'Applied Date' },
          { key: 'stage', label: 'Stage', render: v => {
              const s = v.toLowerCase();
              return s === 'hired' ? b('success','Hired') : s === 'rejected' ? b('danger','Rejected') : s === 'interview' ? b('warning','Interview') : s === 'offer' ? b('primary','Offer Made') : s === 'screening' ? b('info','Screening') : b('neutral', v);
          }},
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Download CV"><i data-lucide="download" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search candidates...'
      });
      break;

    case 'interview-tracker':
      tbl('tbl-interviews', 'api/talent/fetch_interviews.php', {
        columns: [
          { key: 'candidate', label: 'Candidate' }, { key: 'position', label: 'Position' },
          { key: 'interviewer', label: 'Interviewer' }, { key: 'date', label: 'Date' },
          { key: 'time', label: 'Time' }, { key: 'mode', label: 'Mode' },
          { key: 'result', label: 'Result', render: v => {
              const s = v.toLowerCase();
              return s === 'passed' ? b('success','Passed') : s === 'failed' ? b('danger','Failed') : s === 'scheduled' ? b('info','Scheduled') : s === 'on hold' ? b('warning','On Hold') : s === 'no show' ? b('neutral','No Show') : b('primary', v);
          }},
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Edit Interview"><i data-lucide="edit-3" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search interviews...'
      });
      break;

    case 'internship':
      tbl('tbl-internship', 'api/talent/fetch_internships.php', {
        columns: [
          { key: 'id_code', label: 'Intern ID' }, { key: 'name', label: 'Full Name' }, { key: 'uni', label: 'Institution' },
          { key: 'dept', label: 'Assigned Dept' }, { key: 'mentor', label: 'Mentor' },
          { key: 'start', label: 'Start Date' }, { key: 'end', label: 'End Date' },
          { key: 'eval', label: 'Evaluation', render: v => (!v || v === '0.00') ? '<span style="color:var(--muted)">Pending</span>' : b('primary', parseFloat(v).toFixed(0) + '%') },
          { key: 'status', label: 'Status', render: v => v === 'Active' ? b('success','Active') : v === 'Completed' ? b('neutral','Completed') : v === 'Terminated' ? b('danger','Terminated') : b('info', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Evaluation Form"><i data-lucide="clipboard-check" size="10"></i></button>${trash()}</div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search interns...'
      });
      break;

    case 'daily-attendance':
      tbl('tbl-attendance', 'api/attendance/fetch_daily.php', {
        columns: [
          { key: 'name', label: 'Employee' }, { key: 'dept', label: 'Dept' }, { key: 'shift', label: 'Shift' },
          { key: 'checkin',  label: 'Check In',  render: v => v ? v.substring(0, 5) : '—' },
          { key: 'checkout', label: 'Check Out', render: v => v ? v.substring(0, 5) : '—' },
          { key: 'hours', label: 'Hours' }, { key: 'ot', label: 'OT' },
          { key: 'status', label: 'Status', render: (v, row) => {
              if (row.is_late == 1) return b('warning', 'Late');
              return v === 'P' ? b('success','Present') : v === 'A' ? b('danger','Absent') : v === 'L' ? b('info','On Leave') : v === 'H' ? b('neutral','Half Day') : v === 'O' ? b('neutral','Off') : b('neutral', v);
          }},
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View Logs"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs btn-secondary" title="Edit Entry"><i data-lucide="edit-2" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search attendance...'
      });
      break;

    case 'overtime-requests':
      tbl('tbl-overtime', 'api/benefits/fetch_overtime.php', {
        columns: [
          { key: 'emp',  label: 'Employee' }, { key: 'dept', label: 'Dept' }, { key: 'date', label: 'Date' },
          { key: 'hours', label: 'OT Hours', render: v => `<b>${v} hrs</b>` },
          { key: 'reason', label: 'Reason', render: v => `<span title="${v}" style="display:block;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${v}</span>` },
          { key: 'submitted', label: 'Submitted' },
          { key: 'status', label: 'Status', render: v => v === 'Approved' ? b('success','Approved') : v === 'Rejected' ? b('danger','Rejected') : v === 'Pending' ? b('warning','Pending') : b('neutral', v) },
          { key: '_', label: 'Actions', render: (v, row) => `<div class="flex-row">${row.status === 'Pending' ? `<button class="btn btn-xs btn-primary" title="Approve/Reject"><i data-lucide="check-circle" size="10"></i> Process</button>` : `<button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>`}</div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search overtime requests...'
      });
      break;

    case 'attendance-reports':
      tbl('tbl-attendance-reports', 'api/attendance/fetch_reports.php', {
        columns: [
          { key: 'dept', label: 'Department' }, { key: 'total', label: 'Total Emp' },
          { key: 'absent', label: 'Absent Days' }, { key: 'leave_days', label: 'Leave Days' }, { key: 'late', label: 'Late Arrivals' },
          { key: 'ot', label: 'Total OT Hrs', render: v => v ? parseFloat(v).toFixed(1) : '0.0' },
          { key: 'rate', label: 'Attendance Rate', render: v => {
              const val = parseFloat(v);
              const color = val < 75 ? 'var(--danger)' : val < 90 ? 'var(--warning)' : 'var(--success)';
              return `<b style="color:${color}">${val}%</b>`;
          }}
        ],
        perPage: 15, searchPlaceholder: 'Search reports by department...'
      });
      break;

    case 'leave-types': initLeaveTypesCards(); break;

    case 'leave-requests':
      tbl('tbl-leave-requests', 'api/leave/fetch_leaverequests.php', {
        columns: [
          { key: 'emp', label: 'Employee' }, { key: 'dept', label: 'Department' }, { key: 'type', label: 'Leave Type' },
          { key: 'approver', label: 'Approver', render: v => v || '<span style="color:var(--muted)">—</span>' },
          { key: 'from', label: 'From' }, { key: 'to', label: 'To' }, { key: 'days', label: 'Days' },
          { key: 'reason', label: 'Reason', render: v => `<span title="${v}" style="display:block;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${v}</span>` },
          { key: 'status', label: 'Status', render: v => v === 'Approved' ? b('success','Approved') : v === 'Rejected' ? b('danger','Rejected') : v === 'Pending' ? b('warning','Pending') : b('neutral', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: (v, row) => `<div class="flex-row">${row.status === 'Pending' ? `<button class="btn btn-xs btn-primary" title="Process Request"><i data-lucide="check-square" size="10"></i> Review</button>` : `<button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>`}</div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search leave requests...'
      });
      break;

    case 'leave-entitlement':
      tbl('tbl-leave-entitlement', 'api/leave/fetch_entitlement.php', {
        columns: [
          { key: 'id', label: 'Emp ID' }, { key: 'name', label: 'Employee' }, { key: 'dept', label: 'Department' },
          { key: 'al_total', label: 'AL Total' }, { key: 'al_used', label: 'AL Used' },
          { key: 'al_bal', label: 'AL Balance', render: v => `<b style="color:var(--primary)">${v}</b>` },
          { key: 'sl_used', label: 'SL Used' }, { key: 'sl_bal', label: 'SL Balance' },
          { key: 'carry', label: 'Carried Over', render: v => v > 0 ? b('info', v + ' Days') : '0' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<button class="btn btn-xs btn-secondary" title="Update Entitlement"><i data-lucide="edit-3" size="10"></i></button>` }
        ],
        perPage: 15, searchPlaceholder: 'Search entitlement...'
      });
      break;

    case 'medical-claims':
      tbl('tbl-medical', 'api/benefits/fetch_medical_claims.php', {
        columns: [
          { key: 'id', label: 'Claim ID' }, { key: 'emp', label: 'Employee' }, { key: 'dept', label: 'Department' }, { key: 'category', label: 'Category' },
          { key: 'amount', label: 'Amount', render: v => v ? 'ETB ' + parseFloat(v).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00' },
          { key: 'submitted', label: 'Submitted' },
          { key: 'receipt', label: 'Receipt', render: v => v == 1 ? b('success','Attached') : b('neutral','None') },
          { key: 'status', label: 'Status', render: v => v === 'Approved' ? b('success','Approved') : v === 'Rejected' ? b('danger','Rejected') : v === 'Pending' ? b('warning','Pending') : b('neutral', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: (v, row) => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View Receipt"><i data-lucide="file-text" size="10"></i></button>${row.status === 'Pending' ? `<button class="btn btn-xs btn-primary" title="Process"><i data-lucide="check-circle" size="10"></i></button>` : ''}</div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search medical claims...'
      });
      break;

    case 'training-needs':
      tbl('tbl-training-needs', 'api/training/fetch_training_needs.php', {
        columns: [
          { key: 'dept', label: 'Department' }, { key: 'skill', label: 'Skill Gap' },
          { key: 'priority', label: 'Priority', render: v => v === 'High' ? b('danger','High') : v === 'Medium' ? b('warning','Medium') : b('neutral','Low') },
          { key: 'emp_count', label: 'Affected', render: v => `<b>${v} Employees</b>` },
          { key: 'proposed', label: 'Proposed Training' },
          { key: 'status', label: 'Status', render: v => v === 'Approved' ? b('success','Approved') : v === 'Ongoing' ? b('primary','Ongoing') : v === 'Pending' ? b('warning','Pending') : b('neutral', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs btn-primary" title="Schedule Training"><i data-lucide="calendar-plus" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search training needs...'
      });
      break;

    case 'training-schedule':
      tbl('tbl-training-schedule', 'api/training/fetch_training_schedule.php', {
        columns: [
          { key: 'course', label: 'Course' }, { key: 'dept', label: 'Department' },
          { key: 'trainer', label: 'Trainer' }, { key: 'date', label: 'Date' },
          { key: 'time', label: 'Time', render: v => v ? v.substring(0, 5) : '—' },
          { key: 'venue', label: 'Venue' },
          { key: 'seats', label: 'Enrolled/Seats', render: (v, row) => {
              const total    = row.total_seats    || 0;
              const enrolled = row.enrolled_seats || 0;
              const pct   = total > 0 ? (enrolled / total) * 100 : 0;
              const color = pct >= 100 ? 'var(--danger)' : 'var(--primary)';
              return `<span style="font-weight:700;color:${color}">${enrolled}</span> / ${total}`;
          }},
          { key: 'status', label: 'Status', render: v => v === 'Confirmed' ? b('success','Confirmed') : v === 'Open' ? b('warning','Open') : v === 'Cancelled' ? b('danger','Cancelled') : b('neutral', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View Roster"><i data-lucide="users" size="10"></i></button><button class="btn btn-xs btn-primary" title="Edit Session"><i data-lucide="edit-3" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search schedule...'
      });
      break;

    case 'performance-reviews':
      tbl('tbl-reviews', 'api/performance/fetch_reviews.php', {
        columns: [
          { key: 'emp', label: 'Employee' }, { key: 'dept', label: 'Department' }, { key: 'reviewer', label: 'Reviewer' }, { key: 'period', label: 'Period' },
          { key: 'score', label: 'Overall Score', render: v => v ? `<b>${parseFloat(v).toFixed(1)}</b> / 10` : '—' },
          { key: 'rank', label: 'Rating', render: v => v === 'Exceptional' ? b('success', v) : v === 'Exceeds' ? b('primary','Exceeds') : v === 'Meets' ? b('neutral','Meets') : v === 'Below' ? b('danger','Below Expectation') : b('neutral', v) },
          { key: 'status', label: 'Status', render: v => v === 'Submitted' ? b('success','Submitted') : v === 'Pending' ? b('warning','Pending') : v === 'Acknowledged' ? b('info','Acknowledged') : b('neutral', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs btn-primary" title="Print PDF"><i data-lucide="printer" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search reviews...'
      });
      break;

    case '360-feedback':
      tbl('tbl-360', 'api/performance/fetch_360.php', {
        columns: [
          { key: 'subject', label: 'Subject' }, { key: 'dept', label: 'Department' }, { key: 'total', label: 'Total Respondents' },
          { key: 'complete', label: 'Completed', render: (v, row) => { const pct = Math.round((v / (row.total || 1)) * 100); return `<b>${v}</b> <small style="color:var(--muted)">(${pct}%)</small>`; }},
          { key: 'avg', label: 'Avg Score', render: v => v ? `<b>${parseFloat(v).toFixed(1)}</b> / 10` : '<span style="color:var(--muted)">TBD</span>' },
          { key: 'status', label: 'Status', render: v => v === 'Closed' ? b('success','Closed') : v === 'Open' ? b('primary','Open') : v === 'In Progress' ? b('warning','In Progress') : b('neutral', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary"><i data-lucide="users" size="10"></i></button><button class="btn btn-xs btn-primary"><i data-lucide="file-bar-chart" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search feedback subjects...'
      });
      break;

    case 'Promote/Demote':
      tbl('tbl-Promote/Demote', 'api/movement/fetch_promotions.php', {
        columns: [
          { key: 'emp', label: 'Employee' },
          { key: 'type', label: 'Type', render: v => v === 'Promotion' ? b('success', v) : b('warning', v) },
          { key: 'from_pos', label: 'Prev Position' }, { key: 'to_pos', label: 'Current Position' }, { key: 'dept', label: 'Dept' },
          { key: 'sal_from', label: 'Old Salary', render: v => v ? 'ETB ' + parseFloat(v).toLocaleString() : '—' },
          { key: 'sal_to',   label: 'New Salary', render: v => v ? 'ETB ' + parseFloat(v).toLocaleString() : '—' },
          { key: 'eff_date', label: 'Effective' },
          { key: 'status', label: 'Status', render: v => statusBadge[v.toLowerCase()] || b('info', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: actionEyeTrash }
        ],
        perPage: 15, searchPlaceholder: 'Search promotions...'
      });
      break;

    case 'transfers':
      tbl('tbl-transfers-dept', 'api/movement/fetch_transfers.php', {
        columns: [
          { key: 'emp', label: 'Employee' }, { key: 'from_dept', label: 'From Department' }, { key: 'to_dept', label: 'To Department' },
          { key: 'from_branch', label: 'From Branch' }, { key: 'to_branch', label: 'To Branch' },
          { key: 'req_date', label: 'Requested' }, { key: 'eff_date', label: 'Effective' },
          { key: 'status', label: 'Status', render: v => statusBadge[v.toLowerCase()] || b('info', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: actionEyeTrash }
        ],
        perPage: 15, searchPlaceholder: 'Search transfers...'
      });
      break;

    case 'attendance': buildMatrix(); break;

    case 'disciplinary-actions':
      tbl('tbl-disciplinary', 'api/compliance/fetch_disciplinary.php', {
        columns: [
          { key: 'emp', label: 'Employee' }, { key: 'dept', label: 'Department' },
          { key: 'type', label: 'Action Type', render: v => {
              return v === 'Verbal Warning' ? b('neutral', v) : v === 'Written Warning' ? b('warning', v)
                : v === 'Final Warning' ? b('danger', v) : v === 'Suspension' ? b('danger', 'Suspended')
                : v === 'Demotion' ? b('primary', v) : b('neutral', v);
          }},
          { key: 'incident', label: 'Incident Date' }, { key: 'issued', label: 'Issued Date' },
          { key: 'issuer_name', label: 'Issued By', render: v => v || '<span style="color:var(--muted)">System</span>' },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: actionEyeTrash }
        ],
        perPage: 15, searchPlaceholder: 'Search disciplinary records...'
      });
      break;

    case 'resignations':
      tbl('tbl-resignations', 'api/compliance/fetch_resignations.php', {
        columns: [
          { key: 'emp', label: 'Employee' }, { key: 'dept', label: 'Department' }, { key: 'type', label: 'Reason' }, { key: 'filed', label: 'Filed' },
          { key: 'assigned', label: 'Assigned To', render: v => v || '<span style="color:var(--muted)">Unassigned</span>' },
          { key: 'priority', label: 'Priority', render: v => v === 'High' ? b('danger','High') : v === 'Medium' ? b('warning','Medium') : b('neutral', v) },
          { key: 'status', label: 'Status', render: v => v === 'Resolved' ? b('success','Resolved') : v === 'Pending' ? b('warning','Pending') : v === 'Under Review' ? b('info','Under Review') : b('neutral', v) },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-primary" title="Process Separation"><i data-lucide="user-minus" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search resignations...'
      });
      break;

    case 'termination':
      tbl('tbl-termination', 'api/compliance/fetch_separations.php', {
        columns: [
          { key: 'emp', label: 'Employee' }, { key: 'dept', label: 'Department' }, { key: 'type', label: 'Separation Type' },
          { key: 'notice', label: 'Notice Date' }, { key: 'last_day', label: 'Last Working Day' },
          { key: 'clearance', label: 'Clearance', render: v => v === 'Done' ? b('success','Done') : b('warning','Pending') },
          { key: 'settlement', label: 'Final Settlement', render: v => v ? 'ETB ' + parseFloat(v).toLocaleString(undefined, { minimumFractionDigits: 2 }) : b('neutral','TBD') },
          { key: 'status', label: 'Status', render: v => v === 'Complete' ? b('success','Complete') : b('warning','In Progress') },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="View Dossier"><i data-lucide="folder" size="10"></i></button><button class="btn btn-xs btn-primary" title="Print Certificate"><i data-lucide="printer" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search terminations...'
      });
      break;

    case 'roles-permissions': initRoles(); break;

    case 'exit-clearance': {
      const chk = v => v == 1 ? b('success','✓') : '<span style="color:var(--muted)">—</span>';
      tbl('tbl-clearance', 'api/compliance/fetch_clearance.php', {
        columns: [
          { key: 'emp', label: 'Employee' }, { key: 'dept', label: 'Department' },
          { key: 'it',      label: 'IT',      render: chk }, { key: 'finance', label: 'Finance', render: chk },
          { key: 'hr',      label: 'HR',      render: chk }, { key: 'admin',   label: 'Admin',   render: chk },
          { key: 'assets',  label: 'Assets',  render: chk },
          { key: 'overall', label: 'Overall', render: v => v === 'Cleared' ? b('success','Cleared') : v === 'In Progress' ? b('info','In Progress') : b('warning','Pending') },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-primary" title="Update Status"><i data-lucide="check-square" size="10"></i> Sign-off</button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search clearance status...'
      });
      break;
    }

    case 'user-management':
      tbl('tbl-users', 'api/system/fetch_users.php', {
        columns: [
          { key: 'id', label: 'User ID', render: v => `<span style="font-family:'JetBrains Mono';font-size:10px;">USR-${String(v).padStart(3,'0')}</span>` },
          { key: 'name', label: 'Full Name' }, { key: 'email', label: 'Email Address' },
          { key: 'role', label: 'Role', render: v => b('primary', v) }, { key: 'dept', label: 'Dept' },
          { key: 'last_login', label: 'Last Login', render: v => v || '<span style="color:var(--muted)">Never</span>' },
          { key: 'status', label: 'Status', render: v => v === 'Active' ? statusBadge.active : statusBadge.inactive },
          { key: 'updated_by_name', label: 'Last Updated By' },
          { key: '_', label: 'Actions', render: () => `<div class="flex-row"><button class="btn btn-xs btn-secondary" title="Edit Permissions"><i data-lucide="shield-check" size="10"></i></button><button class="btn btn-xs btn-secondary" title="Reset Password"><i data-lucide="key" size="10"></i></button></div>` }
        ],
        perPage: 15, searchPlaceholder: 'Search users...'
      });
      break;

    case 'audit-logs':
      tbl('tbl-audit', 'api/system/fetch_audit.php', {
        columns: [
          { key: 'user',   label: 'User',   render: v => `<span style="font-weight:700;">${v}</span>` },
          { key: 'action', label: 'Action', render: v => v === 'DELETE' ? b('danger', v) : v === 'UPDATE' ? b('warning', v) : v === 'CREATE' ? b('success', v) : v === 'LOGIN' ? b('primary', v) : b('neutral', v) },
          { key: 'module', label: 'Module' },
          { key: 'record', label: 'Record', render: v => `<code style="background:#f1f5f9;padding:2px 4px;border-radius:4px;font-size:10px;">${v}</code>` },
          { key: 'ip',     label: 'IP Address', render: v => `<span style="font-family:'JetBrains Mono';font-size:10px;color:var(--muted);">${v}</span>` },
          { key: 'ts',     label: 'Timestamp', render: v => { const d = new Date(v); return d.toLocaleString('en-GB', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' }); }},
          { key: '_',      label: 'Details', render: () => `<button class="btn btn-xs btn-secondary">View Changes</button>` }
        ],
        perPage: 15, searchPlaceholder: 'Search audit logs...'
      });
      break;

    case 'hr-analytics': initAnalytics(); break;
  }
}

// ═══════════════════════════════════════════════════════════════════════
// EMPLOYMENT TYPES CARDS
// ═══════════════════════════════════════════════════════════════════════

const _etypeAccents = ['#15b201','#3b82f6','#a855f7','#f59e0b','#ef4444','#06b6d4','#22c55e','#e11d48','#8b5cf6','#14b8a6'];

function initEmploymentTypesCards() {
  const container = document.getElementById('tbl-employment-types');
  if (!container) return;
  container.innerHTML = '<div class="etype-ledger" id="etype-ledger-stack"></div>';
  const stack = document.getElementById('etype-ledger-stack');
  stack.innerHTML = '<div style="padding:100px;text-align:center;"><i data-lucide="loader-2" class="spin" style="color:var(--primary);opacity:.3;"></i></div>';
  lcIcons(stack);

  fetch('api/employees/fetch_emptypes.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      const totalCount = res.data.reduce((s, i) => s + parseInt(i.count), 0) || 1;
      stack.innerHTML  = '';

      res.data.forEach((item, idx) => {
        const name = (item.name || '').toLowerCase();
        let icon = 'briefcase', colorClass = 'cat-perm';
        if      (name.includes('permanent')) { icon = 'shield-check';    colorClass = 'cat-perm'; }
        else if (name.includes('contract'))  { icon = 'file-text';       colorClass = 'cat-cont'; }
        else if (name.includes('part'))      { icon = 'file-check';      colorClass = 'cat-part'; }
        else if (name.includes('intern'))    { icon = 'graduation-cap';  colorClass = 'cat-intn'; }
        else if (name.includes('temp'))      { icon = 'clock';           colorClass = 'cat-temp'; }

        const pct         = Math.max(5, (parseInt(item.count) / totalCount) * 100);
        const accentColor = _etypeAccents[idx % _etypeAccents.length];
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
            <div class="dist-track"><div class="dist-fill" style="width:0%" data-pct="${pct}"></div></div>
          </div>
          <div class="etype-data"><span class="data-val">${item.count}</span><span class="data-unit">Emp</span></div>`;
        stack.appendChild(row);
      });

      // Animate bars after paint
      setTimeout(() => stack.querySelectorAll('.dist-fill').forEach(bar => bar.style.width = bar.dataset.pct + '%'), 100);
      lcIcons(stack);
    })
    .catch(err => { stack.innerHTML = `<div style="padding:40px;text-align:center;color:var(--danger);font-weight:800;">${err.message}</div>`; });
}

// ═══════════════════════════════════════════════════════════════════════
// LEAVE TYPES CARDS
// ═══════════════════════════════════════════════════════════════════════

const _leaveAccents = ['var(--primary)','var(--info)','var(--success)','var(--warning)','var(--danger)'];

function initLeaveTypesCards() {
  const container = document.getElementById('tbl-leave-types');
  if (!container) return;
  container.innerHTML = '<div class="leave-type-viewport"><div class="etype-ledger" id="leave-type-ledger-stack"></div></div>';
  const stack = document.getElementById('leave-type-ledger-stack');
  stack.innerHTML = '<div style="padding:100px;text-align:center;"><i data-lucide="loader-2" class="spin" style="color:var(--primary);opacity:.3;"></i></div>';
  lcIcons(stack);

  fetch('api/leave/fetch_leave_types.php?limit=1000')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      stack.innerHTML = '';
      res.data.forEach((item, idx) => {
        const name = (item.name || '').toLowerCase();
        let icon = 'calendar';
        if      (name.includes('annual'))                              icon = 'sun';
        else if (name.includes('sick'))                                icon = 'stethoscope';
        else if (name.includes('maternity') || name.includes('paternity')) icon = 'baby';
        else if (name.includes('compassionate'))                       icon = 'heart-handshake';
        else if (name.includes('study'))                               icon = 'book-open';

        const days         = parseInt(item.days, 10) || 0;
        const carry        = parseInt(item.carry, 10) || 0;
        const isPaid       = String(item.paid) === 'Yes';
        const needsApproval = String(item.approval) === '1';
        const accentColor  = _leaveAccents[idx % _leaveAccents.length];

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
            <div class="leave-type-foot">
              <span class="leave-foot-item"><i data-lucide="calendar-days" size="13"></i>Policy cycle: <b>Yearly</b></span>
            </div>
          </div>
          <div class="etype-data leave-type-data"><span class="data-val">${days}</span><span class="data-unit">Days / Year</span></div>`;
        stack.appendChild(row);
      });
      lcIcons(stack);
    })
    .catch(err => { stack.innerHTML = `<div style="padding:40px;text-align:center;color:var(--danger);font-weight:800;">${err.message}</div>`; });
}

// ═══════════════════════════════════════════════════════════════════════
// SERVER-PAGINATED TABLE ENGINE
// ═══════════════════════════════════════════════════════════════════════

function initServerPaginatedTable(containerId, apiUrl, { columns, perPage = 15, searchPlaceholder = 'Search...' }) {
  const container = document.getElementById(containerId);
  if (!container) return;
  delete container.dataset.built;

  let currentPage   = 1;
  let totalPages    = 1;
  let totalRecords  = 0;
  let searchTerm    = '';
  let searchTimeout = null;
  let currentRows   = [];

  // Build the static shell once; only tbody/pagination update on fetches
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

  // Header is static — render once
  document.getElementById(`${containerId}-thead`).innerHTML =
    `<tr>${columns.map(c => `<th>${c.label}</th>`).join('')}</tr>`;

  // Renders tbody, info text, and pagination buttons with current state
  const updateDisplay = () => {
    const tbody      = document.getElementById(`${containerId}-tbody`);
    const infoSpan   = document.getElementById(`${containerId}-info`);
    const pgDiv      = document.getElementById(`${containerId}-pagination`);
    const searchInput = document.getElementById(`${containerId}-search`);
    const clearBtn   = document.getElementById(`${containerId}-clear-search`);

    if (searchInput) searchInput.value = searchTerm;
    if (clearBtn) clearBtn.style.display = searchTerm ? 'block' : 'none';

    // Build all rows as one string for a single innerHTML assignment
    if (currentRows.length) {
      tbody.innerHTML = currentRows.map(row =>
        `<tr>${columns.map(c => {
          const v = row[c.key] !== undefined ? row[c.key] : '—';
          return `<td>${c.render ? c.render(v, row) : v}</td>`;
        }).join('')}</tr>`
      ).join('');
    } else {
      // Empty state: first cell shows message, rest are empty
      tbody.innerHTML = `<tr>` +
        columns.map((col, i) => i === 0
          ? `<td style="text-align:left;padding:16px 20px;color:var(--muted);font-weight:500;">No records found</td>`
          : `<td></td>`).join('') +
        `</tr>`;
    }

    const start = totalRecords ? (currentPage - 1) * perPage + 1 : 0;
    const end   = Math.min(currentPage * perPage, totalRecords);
    infoSpan.textContent = `Showing ${start}–${end} of ${totalRecords}`;

    // Compact pagination with ellipsis (shows at most 7 pages around current)
    let pgHTML = `<button class="pg-btn" onclick="changeServerPage('${containerId}',-1)" ${currentPage <= 1 ? 'disabled' : ''}>‹</button>`;
    for (let i = 1; i <= totalPages; i++) {
      if (totalPages <= 7 || i === 1 || i === totalPages || Math.abs(i - currentPage) <= 1) {
        pgHTML += `<button class="pg-btn ${i === currentPage ? 'active' : ''}" onclick="goToServerPage('${containerId}',${i})">${i}</button>`;
      } else if (Math.abs(i - currentPage) === 2) {
        pgHTML += `<button class="pg-btn" disabled>…</button>`;
      }
    }
    pgHTML += `<button class="pg-btn" onclick="changeServerPage('${containerId}',1)" ${currentPage >= totalPages ? 'disabled' : ''}>›</button>`;
    pgDiv.innerHTML = pgHTML;
    lcIcons(tbody);
  };

  const fetchData = () => {
    container.style.opacity = '0.5';
    fetch(`${apiUrl}?page=${currentPage}&limit=${perPage}&search=${encodeURIComponent(searchTerm)}`)
      .then(r => r.json())
      .then(res => {
        if (!res.success) throw new Error(res.message);
        currentRows   = res.data || [];
        totalRecords  = res.pagination?.total || 0;
        totalPages    = res.pagination?.totalPages || 1;
        updateDisplay();
      })
      .catch(err => {
        document.getElementById(`${containerId}-tbody`).innerHTML =
          `<tr><td colspan="${columns.length}" style="padding:20px;color:#dc2626;">Error: ${err.message}</td></tr>`;
      })
      .finally(() => { container.style.opacity = '1'; });
  };

  // Expose fetchData so external code can refresh the table (e.g. after a save)
  container._fetchData = fetchData;

  // Debounced search
  document.getElementById(`${containerId}-search`)?.addEventListener('input', e => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { searchTerm = e.target.value; currentPage = 1; fetchData(); }, 300);
  });

  document.getElementById(`${containerId}-clear-search`)?.addEventListener('click', () => {
    searchTerm = '';
    document.getElementById(`${containerId}-search`).value = '';
    currentPage = 1;
    fetchData();
  });

  // Per-table pagination handlers stored on window (inline onclick needs a global)
  window[`changeServerPage_${containerId}`] = dir => {
    const p = currentPage + dir;
    if (p >= 1 && p <= totalPages) { currentPage = p; fetchData(); }
  };
  window[`goToServerPage_${containerId}`] = page => {
    if (page >= 1 && page <= totalPages) { currentPage = page; fetchData(); }
  };

  fetchData();
}

// Global pagination delegates called from inline onclick attributes
function changeServerPage(containerId, dir)        { window[`changeServerPage_${containerId}`]?.(dir); }
function goToServerPage(containerId, page)         { window[`goToServerPage_${containerId}`]?.(page); }

// ═══════════════════════════════════════════════════════════════════════
// DOCUMENT VAULT
// ═══════════════════════════════════════════════════════════════════════

const VAULT_SCHEMA = [
  { id: 'contract',    name: 'Signed Employment Contract',    cat: 'Legal' },
  { id: 'cv',          name: 'Curriculum Vitae (CV)',          cat: 'Identity' },
  { id: 'academic',    name: 'Academic Credentials',           cat: 'Education' },
  { id: 'clearance',   name: 'Clearance / Release Letter',     cat: 'History' },
  { id: 'experience',  name: 'Experience Letters',             cat: 'History' },
  { id: 'coc',         name: 'Certificate of Competence (COC)', cat: 'Professional' },
  { id: 'guarantor',   name: 'Guarantor Form & ID',            cat: 'Legal' },
  { id: 'nda',         name: 'Confidentiality / NDA Agreement', cat: 'Compliance' },
  { id: 'handbook',    name: 'Acknowledgments',                cat: 'Compliance' },
  { id: 'national_id', name: 'National ID / Passport Copy',    cat: 'Identity' },
  { id: 'tin',         name: 'TIN Certification Document',     cat: 'Tax' },
  { id: 'medical',     name: 'Health & Fitness Clearance',     cat: 'Compliance' }
];

// Stores the target employee while the page transition loads
function openEmployeeVault(name, id) {
  pendingEmployeeVaultData = { name, id };
  goPage('employee-vault');
}

// ═══════════════════════════════════════════════════════════════════════
// ATTENDANCE MATRIX
// ═══════════════════════════════════════════════════════════════════════

const ATT_CODES = ['P', 'H', 'A', 'L', 'O'];

function buildMatrix() {
  const m          = document.getElementById('att-m-select').value;
  const y          = document.getElementById('att-y-select').value;
  const deptFilter = document.getElementById('att-dept-select').value;
  const nameInput  = document.getElementById('as-input-att-name').value.toLowerCase().trim();

  if (!m || !y) {
    showNotification('Input Required', 'Please select both a target Month and Fiscal Year to generate the registry.', 'warning');
    return;
  }

  const month       = parseInt(m);
  const year        = parseInt(y);
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const now         = new Date();

  // Build header row
  let headHtml = `<tr><th class="sticky-emp sticky-head"><div class="id-label-theme">Employee Identity</div></th>`;
  for (let d = 1; d <= daysInMonth; d++) {
    const dObj    = new Date(year, month, d);
    const dayName = dObj.toLocaleDateString('en-US', { weekday: 'short' });
    const isToday = d === now.getDate() && month === now.getMonth() && year === now.getFullYear();
    headHtml += `<th class="sticky-head att-day-col ${isToday ? 'is-today' : ''}"><span class="ledger-d-num">${d}</span><span class="ledger-d-day">${dayName}</span></th>`;
  }
  headHtml += '</tr>';
  document.getElementById('ledger-head').innerHTML = headHtml;

  // Filter employee list
  const filteredList = names.map((name, idx) => ({
    name, dept: depts[idx % depts.length], id: `EMP-10${idx + 100}`
  })).filter(emp => {
    return (deptFilter === 'All' || emp.dept === deptFilter) &&
           (!nameInput || emp.name.toLowerCase().includes(nameInput));
  });

  let bodyHtml = '';
  if (!filteredList.length) {
    bodyHtml = `<tr><td colspan="${daysInMonth + 1}" style="text-align:center;padding:60px;color:var(--muted);">No matching records found.</td></tr>`;
  } else {
    filteredList.slice(0, 40).forEach(emp => {
      const initials = emp.name.split(' ').map(n => n[0]).join('');
      bodyHtml += `<tr>
        <td class="sticky-emp">
          <div class="flex-row" style="gap:10px">
            <div class="avatar avatar-sm">${initials}</div>
            <div style="line-height:1.2">
              <div style="font-size:11px;font-weight:800;">${emp.name}</div>
              <div style="font-size:9px;color:var(--muted);font-family:'JetBrains Mono'">${emp.id} | ${emp.dept}</div>
            </div>
          </div>
        </td>`;
      for (let d = 1; d <= daysInMonth; d++) {
        const dObj    = new Date(year, month, d);
        const isSat   = dObj.getDay() === 6;
        const isSun   = dObj.getDay() === 0;
        const isFuture = dObj > now;
        const status  = isSat ? 'H' : isSun ? 'O' : 'P';
        bodyHtml += `<td class="att-cell st-${status}">
          <div class="status-pill ${isFuture ? 'future' : ''}" onclick="${isFuture ? '' : 'cycleStatus(this)'}">
            ${isFuture ? '' : status}
          </div>
        </td>`;
      }
      bodyHtml += '</tr>';
    });
  }

  document.getElementById('ledger-body').innerHTML = bodyHtml;
  document.getElementById('ledger-container').style.display = 'block';
  document.getElementById('att-meta-header').style.display  = 'flex';
  document.getElementById('ledger-empty').style.display     = 'none';
  lcIcons(document.getElementById('p-attendance'));
}

// Live name filter on the rendered attendance matrix
function filterAttMatrix(val) {
  const q = val.toLowerCase();
  document.querySelectorAll('#ledger-body tr').forEach(row => {
    const name = row.querySelector('.sticky-emp')?.textContent.toLowerCase() || '';
    row.style.display = name.includes(q) ? '' : 'none';
  });
}

// Cycles the status pill through P→H→A→L→O→P
function cycleStatus(el) {
  const current = el.textContent.trim();
  const next    = ATT_CODES[(ATT_CODES.indexOf(current) + 1) % ATT_CODES.length];
  const parent  = el.parentElement;
  ATT_CODES.forEach(code => parent.classList.remove('st-' + code));
  parent.classList.add('st-' + next);
  el.textContent = next;
}

// ═══════════════════════════════════════════════════════════════════════
// ROLES & PERMISSIONS
// ═══════════════════════════════════════════════════════════════════════

// Module definitions used to build the permissions grid
const mods = [
  { id: 'm-org',   n: 'Company & Structure',  i: 'building-2',    subs: ['Company Profile','Organization Chart','Departments','Job Positions','Branch Offices'] },
  { id: 'm-emp',   n: 'Employees',             i: 'users',         subs: ['Employee Profile','Employment Types','Probation Tracker','Contract Renewals','Former employees','Attachment Vault','Asset Tracking'] },
  { id: 'm-rec',   n: 'Talent Acquisition',    i: 'user-plus',     subs: ['Add Job Vacancies',"Job Applicant's List",'Interview Tracker','Internship Management'] },
  { id: 'm-move',  n: 'Employee Movement',     i: 'arrow-right-left', subs: ['Promote/Demote','Department Transfers'] },
  { id: 'm-att',   n: 'Attendance',            i: 'clock',         subs: ['Record attendance','Daily Attendance','Attendance Reports'] },
  { id: 'm-leave', n: 'Leave Management',      i: 'calendar-days', subs: ['Leave Types','Leave Requests','Leave Entitlement'] },
  { id: 'm-ben',   n: 'Benefits',              i: 'heart-pulse',   subs: ['Medical Claims','Overtime Requests'] },
  { id: 'm-comp',  n: 'Compliance & Exit',     i: 'shield-alert',  subs: ['Disciplinary Actions','Resignations','Separation & Exit','Exit Clearance'] },
  { id: 'm-train', n: 'Training & Dev',        i: 'graduation-cap',subs: ['Training Needs Analysis','Training Schedule'] },
  { id: 'm-perf',  n: 'Performance',           i: 'trending-up',   subs: ['Performance Reviews','360° Feedback'] },
  { id: 'm-sys',   n: 'System Admin',          i: 'settings-2',    subs: ['User Management','Roles & Permissions','Audit Logs'] }
];

function initRoles() { selRole(document.querySelector('.role-pill-v2'), 'Super Admin'); }

let currentAccessMode = 'role';

function switchAccessMode(mode) {
  currentAccessMode = mode;
  const btnRole    = document.getElementById('btn-mode-role');
  const btnUser    = document.getElementById('btn-mode-user');
  const sideRole   = document.getElementById('side-role-list');
  const sideUser   = document.getElementById('side-user-search');
  const label      = document.getElementById('perm-target-label');
  const warning    = document.getElementById('override-warning');

  if (mode === 'role') {
    btnRole.style.background = 'var(--primary-light)'; btnRole.style.color = 'var(--primary)';
    btnUser.style.background = 'transparent';           btnUser.style.color = 'var(--muted)';
    sideRole.style.display   = 'block';
    sideUser.style.display   = 'none';
    label.textContent        = 'Standard Role:';
    warning.style.display    = 'none';
    selRole(document.querySelector('#side-role-list .role-pill-v2'), 'Super Admin');
  } else {
    btnUser.style.background = 'var(--primary-light)'; btnUser.style.color = 'var(--primary)';
    btnRole.style.background = 'transparent';           btnRole.style.color = 'var(--muted)';
    sideRole.style.display   = 'none';
    sideUser.style.display   = 'block';
    label.textContent        = 'Individual Override:';
    warning.style.display    = 'inline-flex';
    document.getElementById('active-role-name').textContent = 'No User Selected';
    document.getElementById('perm-grid').innerHTML = `<tr><td colspan="4" style="text-align:center;padding:40px;color:var(--muted)">Search and select a user to define individual permissions.</td></tr>`;
    document.getElementById('selected-user-card').style.display = 'none';
  }
  lcIcons();
}

function selectUserForPerms(name) {
  document.getElementById('as-input-perm-user').value = name;
  document.getElementById('as-drop-perm-user').classList.remove('active');
  document.getElementById('selected-user-card').style.display = 'block';
  document.getElementById('perm-user-name').textContent   = name;
  document.getElementById('perm-user-id').textContent     = 'E-' + Math.floor(1000 + Math.random() * 9000);
  document.getElementById('perm-user-avatar').textContent = name.split(' ').map(n => n[0]).join('');
  document.getElementById('active-role-name').textContent = name;
  renderPermissionGrid(false);
}

// Generates the permissions table from the mods array
function renderPermissionGrid(isSuperAdmin) {
  const html = mods.map(m => `
    <tr style="background:#f8fafc;border-bottom:2px solid var(--border)">
      <td style="text-align:center;color:var(--primary)"><i data-lucide="${m.i}" size="14"></i></td>
      <td><b style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">${m.n}</b></td>
      <td style="font-size:.65rem;color:var(--muted)">Enable/Disable entire sidebar category.</td>
      <td style="text-align:center">
        <label class="switch">
          <input type="checkbox" class="parent-check" data-module-id="${m.id}"
                 onchange="toggleModuleGroup('${m.id}',this.checked)" ${isSuperAdmin ? 'checked disabled' : 'checked'}>
          <span class="slider"></span>
        </label>
      </td>
    </tr>
    ${m.subs.map(sub => `
    <tr class="child-row-${m.id}">
      <td></td>
      <td style="padding-left:30px;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="width:6px;height:6px;border-radius:50%;background:var(--primary);"></span>
          <span style="font-size:.75rem;font-weight:600;">${sub}</span>
        </div>
      </td>
      <td style="font-size:.65rem;color:var(--muted)">Individual access to the ${sub} page.</td>
      <td style="text-align:center">
        <label class="switch">
          <input type="checkbox" class="child-check" data-parent-ref="${m.id}"
                 onchange="checkParentStatus('${m.id}')" ${isSuperAdmin ? 'checked disabled' : 'checked'}>
          <span class="slider"></span>
        </label>
      </td>
    </tr>`).join('')}`).join('');

  const grid = document.getElementById('perm-grid');
  grid.innerHTML = html;
  lcIcons(grid);
}

function toggleModuleGroup(moduleId, isChecked) {
  document.querySelectorAll(`input[data-parent-ref="${moduleId}"]`).forEach(child => {
    child.checked = isChecked;
    child.closest('tr').style.opacity = isChecked ? '1' : '0.5';
  });
}

// If any child is checked, ensure the parent toggle is also on
function checkParentStatus(moduleId) {
  const parent   = document.querySelector(`input[data-module-id="${moduleId}"]`);
  const children = document.querySelectorAll(`input[data-parent-ref="${moduleId}"]`);
  if (Array.from(children).some(c => c.checked) && !parent.checked) parent.checked = true;
}

function selRole(el, name) {
  document.querySelectorAll('#side-role-list .role-pill-v2').forEach(p => p.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('active-role-name').textContent = name;
  renderPermissionGrid(name === 'Super Admin');
}

function savePermissionChanges() {
  const target = document.getElementById('active-role-name').textContent;
  if (target === 'No User Selected') { showNotification('Action Denied', 'Please select a role or user first.', 'error'); return; }

  const btn       = document.querySelector('#p-roles-permissions .btn-primary');
  const indicator = document.getElementById('save-status-indicator');
  const origHtml  = btn.innerHTML;
  btn.innerHTML   = `<i data-lucide="loader-2" class="spin" size="14"></i> Syncing...`;
  lcIcons(btn);

  // Simulated save (replace with actual API call)
  setTimeout(() => {
    btn.innerHTML = origHtml;
    lcIcons(btn);
    indicator.style.display = 'flex';
    showNotification('Role Updated', `Access schema for ${target} has been updated.`, 'success');
    setTimeout(() => { indicator.style.display = 'none'; }, 3000);
  }, 1000);
}

// ═══════════════════════════════════════════════════════════════════════
// PROBATION EVALUATION
// ═══════════════════════════════════════════════════════════════════════

let pendingEvalData = null;

function openProbationEvalModal(empId, empName) {
  document.getElementById('eval-emp-id').value              = empId;
  document.getElementById('eval-modal-title').textContent   = `Evaluate ${empName}`;
  document.getElementById('eval-notes').value               = '';
  openModal('modal-probation-eval');
}

function submitProbationEval(decision) {
  const empId    = document.getElementById('eval-emp-id').value;
  const notes    = document.getElementById('eval-notes').value.trim();
  const csrf     = document.getElementById('probation_eval_csrf_token')?.value || '';
  const empName  = document.getElementById('eval-modal-title')?.textContent.replace('Evaluate ', '') || 'Employee';

  if (!empId) { showNotification('Error', 'Employee ID is missing.', 'error'); return; }

  closeModal('modal-probation-eval');
  pendingEvalData = { empId, empName, csrf, notes, decision };

  const confirmMap = {
    Hire:   ['Confirm Hire',         'Are you sure you want to confirm this employee as permanent?',                          'Yes, Confirm Hire', 'success'],
    Reject: ['Confirm Termination',  'Are you sure you want to terminate this employee? This action cannot be undone.',        'Yes, Terminate',    'danger'],
    Extend: ['Extend Probation',     'Are you sure you want to extend the probation period? You will be asked for a new date.', 'Yes, Extend',       'warning']
  };
  const [title, msg, btnText, type] = confirmMap[decision];
  openConfirm(title, msg, btnText, type);

  document.getElementById('confirm-btn-yes').onclick = function () {
    closeConfirm();
    if (!pendingEvalData) return;
    const d = pendingEvalData;
    if (d.decision === 'Extend') {
      openExtendProbationModal(d.empId, d.empName, d.csrf, d.notes);
    } else {
      executeProbationDecision(d.empId, d.decision, d.notes, d.csrf);
    }
    pendingEvalData = null;
  };
}

function executeProbationDecision(empId, decision, notes, csrfToken) {
  const btn       = document.getElementById('confirm-btn-yes');
  const origText  = btn.innerHTML;
  btn.disabled    = true;
  btn.innerHTML   = `<i data-lucide="loader-2" class="spin" size="14"></i> Processing...`;
  lcIcons(btn);

  fetch('api/employees/submit_probation_eval.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ employee_id: empId, decision, notes, csrf_token: csrfToken })
  })
    .then(r => r.json())
    .then(result => {
      result.success
        ? (showNotification('Success', result.message, 'success'), refreshProbationTable())
        : showNotification('Error', result.message, 'error');
    })
    .catch(err => showNotification('Network Error', err.message, 'error'))
    .finally(() => { if (btn?.isConnected) { btn.disabled = false; btn.innerHTML = origText; lcIcons(btn); } });
}

// ── EXTEND PROBATION ─────────────────────────────────────────────────────
let pendingExtendNotes = '';

function openExtendProbationModal(empId, empName, csrfToken, existingNotes) {
  document.getElementById('extend-emp-id').value     = empId;
  document.getElementById('extend-csrf-token').value = csrfToken;
  pendingExtendNotes = existingNotes || '';

  fetch(`api/employees/get_probation_end_date.php?employee_id=${empId}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) { showNotification('Error', 'Could not fetch probation end date.', 'error'); return; }
      const end = res.data.end_date;
      document.getElementById('current-end-date').textContent = end;
      document.getElementById('new-end-date').value           = end;
      document.getElementById('new-end-date').min             = end;
    })
    .catch(err => showNotification('Error', 'Network error: ' + err.message, 'error'));

  openModal('modal-extend-probation');
}

function submitExtendProbation() {
  const empId      = document.getElementById('extend-emp-id').value;
  const csrfToken  = document.getElementById('extend-csrf-token').value;
  const newEndDate = document.getElementById('new-end-date').value;
  const currentEnd = document.getElementById('current-end-date').textContent;

  if (!newEndDate) { showNotification('Required', 'Please select a new end date.', 'warning'); return; }
  if (newEndDate < currentEnd) { showNotification('Invalid Date', 'New end date must be on or after the current end date.', 'warning'); return; }

  const btn       = document.querySelector('#modal-extend-probation .btn-primary');
  const origHtml  = btn.innerHTML;
  btn.disabled    = true;
  btn.innerHTML   = `<i data-lucide="loader-2" class="spin" size="14"></i> Extending...`;
  lcIcons(btn);

  fetch('api/employees/submit_probation_eval.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ employee_id: empId, decision: 'Extend', new_end_date: newEndDate, notes: pendingExtendNotes, csrf_token: csrfToken })
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
    .catch(err => showNotification('Network Error', err.message, 'error'))
    .finally(() => { if (btn?.isConnected && btn.disabled) { btn.disabled = false; btn.innerHTML = origHtml; lcIcons(btn); } });
}

// Refreshes the probation table without full page reload
function refreshProbationTable() {
  const container = document.getElementById('tbl-probation');
  if (container?._fetchData) {
    container._fetchData();
  } else {
    inited.delete('probation-tracker');
    if (typeof initPage === 'function') initPage('probation-tracker');
    else location.reload();
  }
}

// ═══════════════════════════════════════════════════════════════════════
// LOGOUT
// ═══════════════════════════════════════════════════════════════════════

function handleLogout() {
  openConfirm('Logout Session', 'Are you sure you want to Logout now?', 'Yes, Logout');
  document.getElementById('confirm-btn-yes').onclick = function () {
    showNotification('Security', 'Ending secure session. Redirecting...', 'danger');
    setTimeout(() => { window.location.href = 'login/logout.php'; }, 1500);
    closeConfirm();
  };
}

// ═══════════════════════════════════════════════════════════════════════
// DROPDOWN SYSTEM (Dynamic + Static autocomplete combos)
// ═══════════════════════════════════════════════════════════════════════

const dropdownCache = {}; // type:searchTerm → array of { label, value }
let activeDropdownId = null;

// Clears all cached entries for a given type (e.g. after a successful save)
function clearDropdownCache(type) {
  Object.keys(dropdownCache).forEach(key => { if (key.startsWith(type + ':')) delete dropdownCache[key]; });
}

// Validates that the user selected from the dropdown and didn't just type a name
function enforceDropdownOnBlur(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const handler = function () {
    const hidden = document.getElementById(this.id + '_id');
    this.classList.toggle('field-error', !!(hidden && !hidden.value && this.value.trim()));
  };
  input.removeEventListener('blur', handler);
  input.addEventListener('blur', handler);
}

// Global click delegation: handles item selection in any open dropdown
document.addEventListener('click', function (e) {
  const item = e.target.closest('.as-res-item');
  if (!item) return;
  const container = item.closest('.as-combo-results');
  if (!container) return;
  e.stopPropagation();

  let inputId = container.id.replace('as-drop-', 'as-input-');
  let inputEl = document.getElementById(inputId);
  // Fallback for wizard inputs whose IDs differ from the naming convention
  if (!inputEl) {
    inputEl = container.closest('.as-combo-container')?.querySelector('input');
    if (inputEl) inputId = inputEl.id;
  }
  if (!inputEl) return;

  const value       = item.dataset.value;
  const displayText = item.textContent.trim();
  if (value) selectAsItemWithValue(inputId, container.id, displayText, value);
});

// Close open dropdowns when clicking outside any combo container
document.addEventListener('mousedown', function (e) {
  if (!e.target.closest('.as-combo-container'))
    document.querySelectorAll('.as-combo-results').forEach(d => d.classList.remove('active'));
}, { passive: true });

// Called on focus; opens the dropdown and fetches data if needed
function showAsDrop(dropdownId) {
  const dropContainer = document.getElementById(dropdownId);
  let inputId         = dropdownId.replace('as-drop-', 'as-input-');
  let inputEl         = document.getElementById(inputId);
  if (!inputEl && dropContainer) {
    inputEl = dropContainer.closest('.as-combo-container')?.querySelector('input');
    if (inputEl) inputId = inputEl.id;
  }
  if (!inputEl || !dropContainer) return;

  // Close all other dropdowns
  document.querySelectorAll('.as-combo-results').forEach(d => { if (d.id !== dropdownId) d.classList.remove('active'); });

  // If already has static items, just show and highlight
  if (dropContainer.querySelectorAll('.as-res-item:not(.no-result-msg)').length > 0) {
    dropContainer.classList.add('active');
    highlightSelectedInDropdown(dropContainer, inputEl.value);
    return;
  }

  // Infer data type from dropdown ID or data attribute
  let type = inputEl.getAttribute('data-dropdown-type');
  if (!type) {
    if      (dropdownId.includes('branch'))                                type = 'branches';
    else if (dropdownId.includes('pos') || dropdownId.includes('job'))     type = 'job_positions';
    else if (dropdownId.includes('emp') || dropdownId.includes('custodian') || dropdownId.includes('manager')) type = 'employees';
    else if (dropdownId.includes('employment') || dropdownId.includes('etype')) type = 'employment_types';
    else type = 'employees';
  }

  // Department and Branch always bypass cache to get fresh data
  if (dropdownId === 'as-drop-dept' || dropdownId === 'as-drop-branch') {
    clearDropdownCache(type);
    populateAsDrop(dropdownId, type, '', inputEl.value);
    return;
  }

  // Job Positions depend on department selection
  if (dropdownId === 'as-drop-pos') {
    const posInput = document.getElementById('o-pos');
    const deptId   = posInput?.dataset.departmentId || document.getElementById('o-dept_id')?.value;
    if (!deptId) {
      dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--warning);">Please select a department first</div>';
      dropContainer.classList.add('active');
      activeDropdownId = dropdownId;
      return;
    }
    reloadJobPositionsDropdown(deptId);
    activeDropdownId = dropdownId;
    return;
  }

  populateAsDrop(dropdownId, type, inputEl.value || '', inputEl.value);
}

// Filters already-rendered items by the typed search term (client-side filter)
function filterAsDrop(inputId, dropId) {
  const inputEl = document.getElementById(inputId);
  if (!inputEl) return;
  const searchTerm = inputEl.value.toLowerCase();

  // Invalidate the hidden ID so partial text doesn't appear selected
  const hiddenEl = document.getElementById(inputId + '_id');
  if (hiddenEl) hiddenEl.value = '';

  const container = document.getElementById(dropId);
  if (!container) return;

  let hasVisible = false;
  container.querySelectorAll('.as-res-item').forEach(item => {
    const visible = item.textContent.toLowerCase().includes(searchTerm);
    item.style.display = visible ? 'block' : 'none';
    if (visible) hasVisible = true;
  });

  container.querySelector('.no-result-msg')?.remove();
  if (!hasVisible) {
    const msg = document.createElement('div');
    msg.className   = 'as-res-item no-result-msg';
    msg.textContent = 'No matching results';
    msg.style.color = 'var(--muted)';
    container.appendChild(msg);
  }
  container.classList.add('active');
}

// Fetches items from the server and renders them in the dropdown
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
    const res = await fetch(`api/1common/fetch_dropdown.php?type=${encodeURIComponent(type)}&search=${encodeURIComponent(searchTerm)}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const result = await res.json();
    if (result.success && result.data) {
      dropdownCache[cacheKey] = result.data;
      renderDropdownItems(dropContainer, result.data, selectedValue);
    } else {
      dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">No results found</div>';
    }
  } catch (err) {
    console.error('Dropdown error:', err);
    dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--danger);">Error loading data</div>';
  }
}

// Renders an array of { label, value } objects as clickable list items
function renderDropdownItems(container, items, selectedValue = '') {
  // Build all items as a DocumentFragment to avoid repeated repaints
  const fragment = document.createDocumentFragment();
  items.forEach(item => {
    const div         = document.createElement('div');
    div.className     = 'as-res-item';
    div.textContent   = item.label;
    div.dataset.value = item.value;
    if (selectedValue && item.label === selectedValue) {
      div.classList.add('selected');
      setTimeout(() => div.scrollIntoView({ block: 'nearest' }), 10);
    }
    fragment.appendChild(div);
  });
  container.innerHTML = '';
  container.appendChild(fragment);
}

// Scrolls to and highlights the currently selected item
function highlightSelectedInDropdown(container, selectedText) {
  if (!selectedText) return;
  container.querySelectorAll('.as-res-item').forEach(item => {
    item.classList.remove('selected');
    if (item.textContent.trim() === selectedText) {
      item.classList.add('selected');
      setTimeout(() => item.scrollIntoView({ block: 'nearest' }), 10);
    }
  });
}

// Handles selection: updates visible input, writes hidden value, triggers side effects
window.selectAsItemWithValue = function (inputId, dropId, displayText, value) {
  const inputEl = document.getElementById(inputId);
  if (!inputEl) return;
  inputEl.value = displayText;
  inputEl.classList.remove('field-error');

  const hiddenId = inputId + '_id';
  let hiddenEl   = document.getElementById(hiddenId);
  if (!hiddenEl) {
    hiddenEl       = document.createElement('input');
    hiddenEl.type  = 'hidden';
    hiddenEl.id    = hiddenId;
    inputEl.parentNode.appendChild(hiddenEl);
  }
  hiddenEl.value = value;

  document.getElementById(dropId)?.classList.remove('active');
  if (typeof validateMasterRecord === 'function') validateMasterRecord();

  // Side effect: selecting a department triggers job positions reload
  if (inputId === 'o-dept') {
    const posInput = document.getElementById('o-pos');
    if (posInput) {
      posInput.value               = '';
      posInput.placeholder         = 'Loading positions...';
      posInput.disabled            = false;
      posInput.dataset.departmentId = value;
      const posHidden = document.getElementById('o-pos_id');
      if (posHidden) posHidden.value = '';
    }
    reloadJobPositionsDropdown(value);
  }

  // Side effect: selecting employment type updates dynamic wizard fields
  if (inputId === 'o-etype') {
    const raw = displayText.trim().toLowerCase().replace(/[-\s]+/g, '');
    let normalized = null;
    if      (raw.includes('fulltime') || raw.includes('permanent')) normalized = 'full-time';
    else if (raw.includes('contract'))  normalized = 'contract';
    else if (raw.includes('parttime'))  normalized = 'part-time';
    else if (raw.includes('intern'))    normalized = 'internship';
    else if (raw.includes('temp'))      normalized = 'temporary';
    if (typeof updateEmploymentFields === 'function') updateEmploymentFields(normalized);
  }
};

// For static dropdowns (gender, marital status) that don't fetch data
function toggleStaticDrop(dropId) {
  const drop = document.getElementById(dropId);
  if (!drop) return;
  document.querySelectorAll('.as-combo-results').forEach(d => { if (d.id !== dropId) d.classList.remove('active'); });
  drop.classList.toggle('active');
}

// Legacy static item selector (kept for backward compatibility)
window.selectAsItem = function (inputId, dropId, name) {
  const el = document.getElementById(inputId);
  if (el) { el.value = name; el.classList.remove('field-error'); }
  document.getElementById(dropId)?.classList.remove('active');
  if (typeof validateMasterRecord === 'function') validateMasterRecord();
};

// Attendance-specific dropdown selectors
window.selectMonth  = (name, val) => { document.getElementById('att-m-display').value = name; document.getElementById('att-m-select').value = val;  document.getElementById('as-drop-month').classList.remove('active'); };
window.selectYear   = (val)       => { document.getElementById('att-y-display').value = val;  document.getElementById('att-y-select').value = val;  document.getElementById('as-drop-year').classList.remove('active'); };
window.selectAttDept= (name, val) => { document.getElementById('att-dept-display').value = name; document.getElementById('att-dept-select').value = val; document.getElementById('as-drop-att-dept').classList.remove('active'); };

// Fetches job positions for a specific department and populates the dropdown
function reloadJobPositionsDropdown(departmentId) {
  const dropContainer = document.getElementById('as-drop-pos');
  const posInput      = document.getElementById('o-pos');
  if (!dropContainer) return;

  // Invalidate any cached positions (they're department-specific)
  clearDropdownCache('job_positions');

  dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">Loading...</div>';
  dropContainer.classList.add('active');

  fetch(`api/1common/fetch_dropdown.php?type=job_positions&department_id=${departmentId}`)
    .then(r => r.json())
    .then(result => {
      if (result.success && result.data?.length) {
        renderDropdownItems(dropContainer, result.data);
        if (posInput) { posInput.disabled = false; posInput.placeholder = 'Select Job Position...'; }
      } else {
        dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">No positions found for this department</div>';
        if (posInput) {
          posInput.disabled     = true;
          posInput.placeholder  = 'No job positions available';
          posInput.value        = '';
          const posHidden = document.getElementById('o-pos_id');
          if (posHidden) posHidden.value = '';
        }
      }
    })
    .catch(err => {
      console.error('[JobPositions] Error:', err);
      dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--danger);">Error loading data</div>';
      if (posInput) { posInput.disabled = true; posInput.placeholder = 'Error loading positions'; }
    });
}