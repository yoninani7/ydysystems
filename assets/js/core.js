// ── ICON HELPER: targeted scan when possible, full scan as fallback ──
const lcIcons = (el) => el ? lucide.createIcons({nodes:[el]}) : lucide.createIcons();

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

window.addEventListener('resize', () => {
  if (!isMobile()) {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('visible');
  }
}, {passive:true});

// ── COLLAPSED SIDEBAR FLYOUT (with delay fix) ──
let _flyoutTimer = null;
let _activeGroup = null;

function showFlyout(group) {
  clearTimeout(_flyoutTimer);
  if (_activeGroup && _activeGroup !== group) {
    _activeGroup.classList.remove('flyout-active');
  }
  
  // Dynamically calculate position
  const rect = group.getBoundingClientRect();
  const submenu = group.querySelector('.submenu');
  const label = group.querySelector('.nav-trigger-left span');
  
  if (submenu) {
    submenu.style.top = rect.top + 'px';
  }
  if (label) {
    label.style.top = rect.top + 'px';
  }

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

  // Update the browser URL without reloading
  const url = new URL(window.location.href);
  url.searchParams.set('page', id);
  history.pushState({ page: id }, '', url.toString());

  // Load only the inner content via AJAX
  const contentArea = document.getElementById('content-area');
  contentArea.style.opacity = '0.4';

fetch('dashboard.php?page=' + encodeURIComponent(id) + '&ajax=1')
    .then(r => r.text())
    .then(html => {
      contentArea.innerHTML = html;
      contentArea.style.opacity = '1';
      lcIcons(contentArea);

      // Update page title
      const titleEl = document.getElementById('page-title'); 
      if (titleEl) { 
        let rawText = el ? el.textContent.trim() : id.replace(/-/g, ' '); 
        const capitalizedText = rawText.charAt(0).toUpperCase() + rawText.slice(1); 
        titleEl.textContent = capitalizedText;
      }
      // ── Re-execute any <script> tags injected via innerHTML ──
      contentArea.querySelectorAll('script').forEach(oldScript => {
        const newScript = document.createElement('script');
        [...oldScript.attributes].forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.textContent = oldScript.textContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
      });

      // Clear the init guard so the page re-initializes with the fresh DOM
      inited.delete(id);
      inited.delete('dashboard-main-chart');
      inited.delete('analytics');

      if (typeof initPage === 'function') initPage(id);
    })
    .catch(() => { contentArea.style.opacity = '1'; });
}

// Handle browser back/forward buttons
window.addEventListener('popstate', (e) => {
  const page = (e.state && e.state.page) || 'dashboard';
  const link = document.querySelector(`.sub-link[onclick*="'${page}'"], .dash-link[onclick*="'${page}'"]`);
  goPage(page, link);
});

window.addEventListener('DOMContentLoaded', () => {
  lcIcons();
  
  const urlParams = new URLSearchParams(window.location.search);
  const currentPage = urlParams.get('page') || 'dashboard';
  
  // Highlight active sidebar link
  document.querySelectorAll('.sub-link, .dash-link').forEach(l => l.classList.remove('active'));
  const activeLink = document.querySelector(`.sub-link[onclick*="'${currentPage}'"], .dash-link[onclick*="'${currentPage}'"]`);
  if (activeLink) activeLink.classList.add('active');
  
  // Set page title
  const titleEl = document.getElementById('page-title');
  if (titleEl) {
    titleEl.textContent = activeLink ? activeLink.textContent.trim() : currentPage.replace(/-/g, ' ');
  }
  
  // Make the loaded page visible (add 'active' class to its .page div)
  const contentArea = document.getElementById('content-area');
  if (contentArea) {
    const pageDiv = contentArea.querySelector('.page');
    if (pageDiv) pageDiv.classList.add('active');
  }
  
  // Initialize the page (tables, charts, etc.)
  if (typeof initPage === 'function') {
    initPage(currentPage);
  }
});

// ── PAGINATED TABLE BUILDER ──
window.changePg=(id,dir)=>{const c=document.getElementById(id);const np=c._page()+dir;const tp=Math.ceil(c._rows.length/c._perPage);if(np>=1&&np<=tp)c._setPage(np);};
window.setPg=(id,p)=>document.getElementById(id)._setPage(p);

// ── MOCK DATA ──
const depts=['Engineering','Sales','HR','Finance','Marketing','Operations','Legal','IT','Product','Customer Success'];
const names=['John Smith','Jane Doe','Carlos Martinez','Alice Kim','Michael Brown','Sarah Lee','David Park','Emily Wang','Robert Johnson','Lisa Chen','Tom Wilson','Maya Singh','James White','Anna Taylor','Kevin Moore','Sophie Turner','Chris Davis','Rachel Green','Daniel Hill','Olivia Harris'];
const gen=(n,fn)=>Array.from({length:n},(_,i)=>fn(i));
const rand=arr=>arr[Math.floor(Math.random()*arr.length)];
const randInt=(a,b)=>Math.floor(Math.random()*(b-a+1))+a;
const fmtMoney=n=>'$'+n.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
const b=(cls,txt)=>`<span class="badge badge-${cls}">${txt}</span>`;
const actions=`<div class="flex-row"><button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button><button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;"><i data-lucide="trash-2" size="10"></i></button></div>`;
const ac={key:'_',label:'Actions',render:()=>actions};
const statusBadge={active:b('success','Active'),inactive:b('neutral','Inactive'),pending:b('warning','Pending'),approved:b('success','Approved'),rejected:b('danger','Rejected')};

// ── ORG CHART ──
let ocZoom=1.0;
function zoomOC(delta){const n=ocZoom+delta;if(n>=0.3&&n<=1.5){ocZoom=n;applyOCZoom();}}
function resetOC(){ocZoom=1.0;applyOCZoom();const bp=document.getElementById('oc-blueprint-area');if(bp)bp.scrollLeft=(bp.scrollWidth-bp.clientWidth)/2;}
function applyOCZoom(){const c=document.getElementById('oc-zoom-container'),l=document.getElementById('oc-zoom-label');if(c)c.style.transform=`scale(${ocZoom})`;if(l)l.textContent=Math.round(ocZoom*100)+'%';}
function initOrgChart(){applyOCZoom();initOrgChartDrag();  renderOrgChartDepartments();}
function initOrgChartDrag(){
  const slider=document.getElementById('oc-blueprint-area');
  if(!slider||slider.dataset.drag)return; slider.dataset.drag='1';
  let down=false,sx,sy,sl,st;
  slider.addEventListener('mousedown',e=>{down=true;sx=e.pageX-slider.offsetLeft;sy=e.pageY-slider.offsetTop;sl=slider.scrollLeft;st=slider.scrollTop;});
  slider.addEventListener('mouseleave',()=>down=false);
  slider.addEventListener('mouseup',()=>down=false);
  slider.addEventListener('mousemove',e=>{if(!down)return;e.preventDefault();slider.scrollLeft=sl-(e.pageX-slider.offsetLeft-sx)*2;slider.scrollTop=st-(e.pageY-slider.offsetTop-sy)*2;});
}
 
// ── VAULT MATRIX ──
const companyRequiredDocs=[{id:'id_card',short:'ID',name:'National ID/Passport'},{id:'contract',short:'CTR',name:'Employment Contract'},{id:'nda',short:'NDA',name:'Non-Disclosure Agreement'},{id:'tax',short:'TAX',name:'Tax Filing (TIN)'},{id:'edu',short:'EDU',name:'Degree/Certificates'},{id:'photo',short:'IMG',name:'Profile Photo'}];
function initVaultMatrix(){
  const container=document.getElementById('vault-matrix-container');
  if(!container||container.dataset.built)return;
  container.dataset.built='1';
  const cols=[{key:'emp',label:'Employee'}];
  companyRequiredDocs.forEach(doc=>cols.push({key:doc.id,label:doc.short,render:val=>val==='filled'?`<div class="vault-slot filled" title="Uploaded"><i data-lucide="check" size="10"></i></div>`:`<div class="vault-slot expired" title="Expired"><i data-lucide="alert-circle" size="10"></i></div>`}));
  cols.push({key:'progress',label:'Fulfillment',render:v=>`<span style="font-size:.7rem;font-weight:800;color:var(--primary);">${v}%</span>`});
  // Inside initVaultMatrix function:
  cols.push({
  key: '_',
  label: 'Actions',
  render: (v, row) => {
    // This extracts the clean name and ID from the HTML string
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = row.emp;
    const cleanName = tempDiv.querySelector('b').innerText;
    const cleanId = tempDiv.querySelector('small').innerText;
    
    return `<button class="btn btn-xs btn-secondary" onclick="openEmployeeVault('${cleanName}', '${cleanId}')">Open Folder</button>`;
  }
});
  const rows=gen(25,i=>{const row={emp:`<b>${names[i%names.length]}</b><br><small style="color:var(--muted)">E-${1000+i}</small>`,progress:randInt(40,100)};companyRequiredDocs.forEach(doc=>{row[doc.id]=Math.random()>0.4?'filled':'expired';});return row;});
  const wrapper=document.createElement('div');
  wrapper.id='tbl-vault-matrix-final';
  container.appendChild(wrapper);
  buildTable('tbl-vault-matrix-final',{columns:cols,rows,perPage:12});
}

// ── DASHBOARD CHART  ── 
function initDashboard() {
  // Use a unique key to prevent re-initialization
  if (inited.has('dashboard-main-chart')) return;
  inited.add('dashboard-main-chart');

  const canvas = document.getElementById('chart-headcount-dept');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  // Create the Premium Vertical Gradient (Green to Lime)
  const gradient = ctx.createLinearGradient(0, 300, 0, 0);
  gradient.addColorStop(0, '#44c100'); // Deep Green
  gradient.addColorStop(1, '#d4fc79'); // Bright Lime

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
function initAnalytics(){
  if(inited.has('analytics'))return;
  inited.add('analytics');
  requestAnimationFrame(()=>{
    const gridCfg={color:'#f1f5f9'};
    const legOpts={position:'bottom',labels:{boxWidth:10,font:{size:10}}};
    const c4=document.getElementById('chart-hire-attrition');
    if(c4)new Chart(c4,{type:'bar',data:{labels:['Jan','Feb','Mar','Apr','May','Jun'],datasets:[{label:'New Hires',data:[15,8,12,0,0,0],backgroundColor:'#16a34a',borderRadius:4},{label:'Attrition',data:[5,4,7,0,0,0],backgroundColor:'#dc2626',borderRadius:4}]},options:{maintainAspectRatio:false,plugins:{legend:legOpts},scales:{y:{beginAtZero:true,grid:gridCfg},x:{grid:{display:false}}}}});
    const c5=document.getElementById('chart-age');
    if(c5)new Chart(c5,{type:'bar',data:{labels:['18–25','26–35','36–45','46–55','55+'],datasets:[{label:'Employees',data:[180,420,380,200,68],backgroundColor:['#15b201','#16a34a','#d97706','#dc2626','#64748b'],borderRadius:4}]},options:{maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:gridCfg},x:{grid:{display:false}}}}});
  });
}

// ── ROLES ──
function selectRole(el,name){document.querySelectorAll('.role-pill').forEach(p=>p.classList.remove('active'));el.classList.add('active');document.getElementById('active-role-name').textContent=name;}

// ── MODALS ──
function openModal(id){document.getElementById(id).classList.add('open');lcIcons(document.getElementById(id));}
function closeModal(id,e){if(!e||e.target===e.currentTarget)document.getElementById(id).classList.remove('open');}
function openDeptModal() {
    openModal('modal-add-dept');
    document.getElementById('dept-name').value = '';
    document.getElementById('dept-name').classList.remove('field-error'); // <-- clear error
    document.getElementById('as-input-dept-head').value = '';
    const existingHidden = document.getElementById('as-input-dept-head_id');
    if (existingHidden) existingHidden.remove();
    document.getElementById('dept-status').value = 'Active';
    enforceDropdownOnBlur('as-input-dept-head');
}

function closeDeptModal(e){closeModal('modal-add-dept',e);} 
function saveDepartment() {
    const deptName = document.getElementById('dept-name');
    const headInput = document.getElementById('as-input-dept-head');
    const headId = document.getElementById('as-input-dept-head_id')?.value || '';
    const status = document.getElementById('dept-status').value || 'Active';
    const csrfToken = document.getElementById('dept_csrf_token')?.value || '';

    // Reset errors
    deptName.classList.remove('field-error');

    let isValid = true;
    if (!deptName.value.trim()) {
        deptName.classList.add('field-error');
        isValid = false;
        showNotification("Required", "Department name is mandatory.", "warning");
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
            showNotification("Success", result.message, "success");
            closeDeptModal();
            inited.delete('departments');
            goPage('departments');
        } else {
            showNotification("Error", result.message, "error");
        }
    })
    .catch(error => {
        showNotification("Error", "Network error: " + error.message, "error");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);
    });
}
function openAssetModal(){openModal('modal-add-asset');}
function closeAssetModal(e){closeModal('modal-add-asset',e);}
function saveNewAsset(){const name=document.getElementById('as-new-name').value;if(!name){alert('Asset Name is required.');return;}alert(`Asset "${name}" registered successfully.`);closeAssetModal();}
function openReassignModal(assetName,currentCustodian){document.getElementById('reassign-display-name').textContent=assetName;document.getElementById('reassign-display-curr').textContent=currentCustodian;document.getElementById('as-input-reassign').value='';openModal('modal-reassign-asset');}
function closeReassignModal(e){closeModal('modal-reassign-asset',e);}
function saveReassignment(){const o=document.getElementById('as-input-reassign').value,a=document.getElementById('reassign-display-name').textContent;if(!o){alert('Please select a new custodian.');return;}alert(`Reassignment Successful: ${a} has been transferred to ${o}.`);closeReassignModal();}
 function closeJobModal(e){closeModal('modal-add-job-position',e);} 
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

    // Reset visual errors
    titleEl.classList.remove('field-error');
    const deptInput = document.getElementById('as-input-job-dept');
    deptInput.classList.remove('field-error');

    let isValid = true;
    let errorMsg = "";

    if (!titleEl.value.trim()) {
        titleEl.classList.add('field-error');
        isValid = false;
        errorMsg = "Job Title is required.";
    }
    
    if (!deptId) {
        deptInput.classList.add('field-error');
        isValid = false;
        if (!errorMsg) errorMsg = "Please select a Department.";
    }

    if (!isValid) {
        showNotification("Required Fields", errorMsg, "warning");
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
            showNotification("Success", result.message, "success");
            closeJobModal();
            inited.delete('job-positions');
            goPage('job-positions');
        } else {
            showNotification("Error", result.message, "error");
        }
    })
    .catch(error => {
        showNotification("Error", "Network error: " + error.message, "error");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);
    });
}


function openBranchModal() {
    openModal('modal-add-branch');
    document.getElementById('branch-name').value = '';
    document.getElementById('branch-name').classList.remove('field-error'); // <-- clear error
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
function closeBranchModal(e){closeModal('modal-add-branch',e);}
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

    // Reset errors
    branchName.classList.remove('field-error');
    managerInput.classList.remove('field-error');

    let isValid = true;

    if (!branchName.value.trim()) {
        branchName.classList.add('field-error');
        showNotification("Required", "Branch Name is mandatory.", "warning");
        return;
    }

    // If user typed something in manager field but no hidden ID exists, it's invalid
    if (managerText !== '' && managerId === '') {
        managerInput.classList.add('field-error');
        showNotification("Invalid Manager", "Please select a manager from the dropdown list.", "warning");
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
            showNotification("Success", result.message, "success");
            closeBranchModal();
            inited.delete('branch-offices');
            goPage('branch-offices');
        } else {
            showNotification("Error", result.message, "error");
        }
    })
    .catch(error => {
        showNotification("Error", "Network error: " + error.message, "error");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        lcIcons(btn);
    });
}
// ── ASSET DROPDOWNS ──

function showAsDrop(dropdownId) {
    const dropContainer = document.getElementById(dropdownId);
    const inputId = dropdownId.replace('as-drop-', 'as-input-');
    const inputEl = document.getElementById(inputId);
    
    const type = inputEl?.getAttribute('data-dropdown-type');
    if (!type) {
        toggleStaticDrop(dropdownId);
        return;
    }
    
    const searchTerm = inputEl?.value || '';
    populateAsDrop(dropdownId, type, searchTerm);
}
function enforceDropdownOnBlur(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    // Remove previous listener to avoid duplicates, then add new one
    const handler = function() {
        const hidden = document.getElementById(this.id + '_id');
        if (hidden && hidden.value === '' && this.value.trim() !== '') {
            this.value = '';
            showNotification("Invalid Selection", "Please select an option from the dropdown list.", "warning");
            this.classList.add('field-error');
        } else {
            this.classList.remove('field-error');
        }
    };
    input.removeEventListener('blur', handler);
    input.addEventListener('blur', handler);
}
function filterAsDrop(inputId,dropId){const val=document.getElementById(inputId).value.toLowerCase(),d=document.getElementById(dropId);if(!d.innerHTML.trim())populateAsDrop(dropId);d.querySelectorAll('.as-res-item').forEach(item=>{item.style.display=item.textContent.toLowerCase().includes(val)?'block':'none';});d.classList.add('active');}
function selectAsItem(inputId,dropId,name){document.getElementById(inputId).value=name;document.getElementById(dropId).classList.remove('active');}
function selectMonth(name, val) {
  document.getElementById('att-m-display').value = name;
  document.getElementById('att-m-select').value = val;
  document.getElementById('as-drop-month').classList.remove('active');
}
function selectYear(val) {
  document.getElementById('att-y-display').value = val;
  document.getElementById('att-y-select').value = val;
  document.getElementById('as-drop-year').classList.remove('active');
}
function selectAttDept(name, val) {
  document.getElementById('att-dept-display').value = name;
  document.getElementById('att-dept-select').value = val;
  document.getElementById('as-drop-att-dept').classList.remove('active');
}
window.addEventListener('mousedown',e=>{if(!e.target.closest('.as-combo-container'))document.querySelectorAll('.as-combo-results').forEach(d=>d.classList.remove('active'));},{passive:true});

// ── ONBOARDING WIZARD ──
let currentObStep=1;
const totalObSteps=6;
// 1. Blocks Next Button + Highlights Missing
// 1. Blocks 'Next' & toggles the red highlight class
function moveOnboarding(dir){
  if(dir>0){
    const cur=[...document.querySelectorAll(`#ob-step-${currentObStep} .master-req`)];
    let ok=true; 
    cur.forEach(i=>{ 
      const isBad = !i.value.trim();
      i.classList.toggle('field-error', isBad); 
      if(isBad) ok=false;
    });
    if(!ok) return; 
  }
  const t=currentObStep+dir; if(t>=1&&t<=totalObSteps) jumpToStep(t);
}

// 2. Blocks side-nav jumps if current section is invalid
function jumpToStep(step){
  if(step > currentObStep) {
    const cur=[...document.querySelectorAll(`#ob-step-${currentObStep} .master-req`)];
    if(cur.some(i=>!i.value.trim())) { moveOnboarding(1); return; }
  }
  
  currentObStep=step;
  if(step === 6) renderSummary();

  document.querySelectorAll('#p-add-employee .form-section-content').forEach(s=>s.classList.remove('active'));
  document.getElementById(`ob-step-${step}`).classList.add('active');
  
  // Left Sidebar Nav Updates
  document.querySelectorAll('.step-pro').forEach((item,idx)=>{
    const sNum=idx+1;
    item.classList.toggle('active',sNum===step);
    item.classList.toggle('done',sNum<step);
    item.querySelector('.step-idx').innerHTML=sNum<step?'<i data-lucide="check" size="14"></i>':sNum;
  });

  // Bottom Nav Controls
  const dots = document.getElementById('ob-dots'), 
        next = document.getElementById('ob-next'), 
        bottomCommit = document.getElementById('btn-save-master-bottom'),
        prev = document.getElementById('ob-prev');

  prev.style.visibility = step === 1 ? 'hidden' : 'visible';
  
  // THE SWAP: If step 6, hide dots/next and show large Add button
  dots.style.display = step === 6 ? 'none' : 'flex';
  next.style.display = step === 6 ? 'none' : 'flex';
  bottomCommit.style.display = step === 6 ? 'flex' : 'none';
  
  next.innerHTML = step === 5 ? 'Review All Steps' : 'Next Step <i data-lucide="chevron-right" size="14"></i>';
  
  document.querySelectorAll('.dot').forEach((d,i)=>d.classList.toggle('active',(i+1)===step));
  document.getElementById('master-progress-line').style.width=(step/totalObSteps*100)+'%';
  
  validateMasterRecord();
  lcIcons(document.getElementById('p-add-employee'));
}

function renderSummary() {
  const area = document.getElementById('summary-render-area');
  if (!area) return;

  // Helper to get value or a dash if empty
  const getV = (id) => {
    const el = document.getElementById(id);
    return el && el.value.trim() !== "" ? el.value.trim() : '<span style="color:var(--muted); font-weight:400;">—</span>';
  };

  // 1. Update Header Identity
  const fullName = `${getV('o-fname')} ${document.getElementById('o-mname').value} ${getV('o-lname')}`.replace(/\s+/g, ' ');
  document.getElementById('rev-full-name').innerHTML = fullName;
  document.getElementById('rev-badge-dept').textContent = getV('o-dept');
  document.getElementById('rev-badge-type').textContent = getV('o-etype');

  // 2. Sync Avatar
  const sourceImg = document.getElementById('avatar-img-output');
  const targetImg = document.getElementById('rev-img');
  if (sourceImg && sourceImg.style.display !== 'none') {
    targetImg.src = sourceImg.src;
    targetImg.style.opacity = "1";
  } else {
    targetImg.src = 'assets/img/bgwhitel.png'; // Fallback
    targetImg.style.opacity = "0.3";
  }

  // 3. Static Sections
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

  // 4. DYNAMIC EMPLOYMENT FIELDS HANDLER
  // This looks at all inputs inside the dynamic container from Step 3
  const dynamicContainer = document.getElementById('dynamic-employment-fields');
  const dynamicSummaryArea = document.getElementById('rev-dynamic-fields-area');
  dynamicSummaryArea.innerHTML = ''; // Clear previous

  if (dynamicContainer) {
    const inputs = dynamicContainer.querySelectorAll('input');
    inputs.forEach(input => {
      // Find the label text for this input
      const labelText = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || 'Detail';
      const val = input.value.trim() || '—';
      
      // Append a new row to the summary for each dynamic field
      const row = document.createElement('div');
      row.className = 'review-row';
      row.innerHTML = `<span class="rev-label">${labelText}</span><span class="rev-val">${val}</span>`;
      dynamicSummaryArea.appendChild(row);
    });
  }
}
// 3. Real-time Button States & auto-clears red highlights
function validateMasterRecord(){
  const all=[...document.querySelectorAll('#p-add-employee .master-req')], cur=[...document.querySelectorAll(`#ob-step-${currentObStep} .master-req`)];
  const allOk=all.every(i=>i.value.trim()), stepOk=cur.every(i=>i.value.trim());
  
  const commitTop = document.getElementById('btn-save-master'),
        commitBtn = document.getElementById('btn-save-master-bottom'), 
        next = document.getElementById('ob-next'), 
        val = document.getElementById('master-val-text');
  
  // Handle Button Opacity/State
  if(next){ next.style.opacity=stepOk?'1':'0.4'; next.style.cursor=stepOk?'pointer':'not-allowed'; }
  
  [commitTop, commitBtn].forEach(btn => {
    if(btn) {
      btn.disabled = !allOk;
      btn.style.opacity = allOk ? '1' : '0.4';
      btn.style.cursor = allOk ? 'pointer' : 'not-allowed';
    }
  });
  
  val.innerHTML=allOk?'<i data-lucide="check-circle" size="12"></i> Verified':`* Required fields missing`;
  val.style.color=allOk?'var(--success)':'var(--danger)';
  
  all.forEach(i=>{ if(i.value.trim()) i.classList.remove('field-error'); });
  lcIcons(val);
}document.querySelectorAll('#p-add-employee input, #p-add-employee select, #p-add-employee textarea').forEach(input=>input.addEventListener('input',validateMasterRecord));

function saveNewEmployee() {
  const btnTop = document.getElementById('btn-save-master');
  const btnBottom = document.getElementById('btn-save-master-bottom');
  const branchId = document.getElementById('o-branch_id')?.value || '';
  const allBtns = [btnTop, btnBottom];

  allBtns.forEach(btn => {
    if (btn) {
      btn.disabled = true;
      // We insert the <i> tag with the data-lucide attribute
      btn.innerHTML = `<i data-lucide="loader-2" class="spin"></i> Finalizing Account...`;
      
      // IMPORTANT: Tell Lucide to look at THIS specific button and turn the <i> into an SVG
      if (typeof lucide !== 'undefined') {
        lucide.createIcons({
          nodes: [btn]
        });
      }
    }
  });

  // Simulate API call
  setTimeout(() => {
    showNotification("Success", "Employee profile has been created.", "success");
    goPage('employee-directory');
    
    // Reset buttons after transition
    setTimeout(() => {
        btnTop.innerHTML = `<i data-lucide="shield-check"></i> Commit Record`;
        btnBottom.innerHTML = `<i data-lucide="user-plus"></i> Add Employee`;
        allBtns.forEach(b => { if(b) b.disabled = false; });
        lucide.createIcons(); // Final refresh for all icons
    }, 1000);
  }, 2000);
}

function previewAvatar(input){
  if(input.files&&input.files[0]){
    const reader=new FileReader();
    reader.onload=e=>{
      const preview=document.getElementById('avatar-img-output');
      const icon=document.getElementById('placeholder-icon');
      const box=document.getElementById('avatar-preview-box');
      preview.src=e.target.result;preview.style.display='block';icon.style.display='none';
      box.style.borderStyle='solid';box.style.borderColor='var(--primary)';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ── PAGE INIT ROUTER ──
const inited=new Set();
// Department data
const departmentsForChart = [  
  { name: 'Marketing', headcount: 195, manager: 'Helen Gebre' },
  { name: 'Finance', headcount: 110, manager: 'Daniel Smith' },
  { name: 'HR', headcount: 82, manager: 'Sarah Lee' }, 
  { name: 'IT', headcount: 16, manager: 'Kevin Vark' }
];
 
function renderOrgChartDepartments() {
  const container = document.getElementById('dept-tree-container');
  if (!container) return;
  
  // Department data with job titles
  const departmentsForChart = [  
    { 
      name: 'Marketing', 
      headcount: 195,
      jobs: [
        { title: 'Marketing Manager', count: 12 },
        { title: 'Digital Marketing Specialist', count: 48 },
        { title: 'Content Creator', count: 35 },
        { title: 'Social Media Manager', count: 28 },
        { title: 'Brand Strategist', count: 22 },
        { title: 'Market Research Analyst', count: 32 },
        { title: 'PR Specialist', count: 18 }
      ]
    },
    { 
      name: 'Finance', 
      headcount: 110,
      jobs: [
        { title: 'Finance Manager', count: 8 },
        { title: 'Senior Accountant', count: 22 },
        { title: 'Accountant', count: 45 },
        { title: 'Payroll Specialist', count: 15 },
        { title: 'Financial Analyst', count: 12 },
        { title: 'Auditor', count: 8 }
      ]
    },
    { 
      name: 'HR', 
      headcount: 82,
      jobs: [
        { title: 'HR Manager', count: 5 },
        { title: 'HR Generalist', count: 28 },
        { title: 'Recruitment Specialist', count: 18 },
        { title: 'Training Coordinator', count: 12 },
        { title: 'Compensation Analyst', count: 9 },
        { title: 'HR Assistant', count: 10 }
      ]
    }, 
    { 
      name: 'IT', 
      headcount: 16,
      jobs: [
        { title: 'IT Manager', count: 2 },
        { title: 'System Administrator', count: 5 },
        { title: 'Network Engineer', count: 4 },
        { title: 'Help Desk Support', count: 3 },
        { title: 'Security Specialist', count: 2 }
      ]
    }
  ];
  
  let html = '';
  departmentsForChart.forEach(dept => {
    // Build submenu items for job titles
    let submenuHtml = '';
    if (dept.jobs && dept.jobs.length > 0) {
      submenuHtml = '<ul class="submenu-jobs" style="padding-top:20px;">';
      dept.jobs.forEach(job => {
        submenuHtml += `
          <li>
            <div class="oc-node oc-staff" style="width:160px; border-top:2px solid var(--primary-light);">
              <div class="oc-node-body" style="padding:12px; text-align:center; flex-direction:column;">
                <div class="oc-node-name" style="font-size:.75rem; font-weight:700;">${job.title}</div>
                <div class="oc-node-role" style="font-size:.6rem; margin-top:4px;">${job.count} employees</div>
              </div>
            </div>
          </li>
        `;
      });
      submenuHtml += '</ul>';
    }
    
    html += `
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
          <div class="oc-node-footer">
            ${dept.jobs.length} Positions
          </div>
        </div>
        ${submenuHtml}
      </li>
    `;
  });
  container.innerHTML = html;
  
  // Refresh icons
  setTimeout(() => {
    if (typeof lcIcons === 'function') lcIcons(container);
  }, 50);
}
function initPage(id){
  if(inited.has(id))return;
  inited.add(id);
  switch(id){
    case 'dashboard':initDashboard();break;
    case 'org-chart':initOrgChart();break;
    case 'departments':
      fetch('api/companyprofile/fetch_departments.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          // Clear the built guard so buildTable can run
          const el = document.getElementById('tbl-departments');
          if (el) delete el.dataset.built;

          buildTable('tbl-departments', {
            columns: [
              { key: 'name',   label: 'Department Name' },
              { key: 'head',   label: 'Head of Department' },
              { key: 'emp',    label: 'Employees' },
              { key: 'status', label: 'Status' },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                      <i data-lucide="trash-2" size="10"></i>
                    </button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          document.getElementById('tbl-departments').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading departments: ${err.message}</p>`;
        });
      break;
    case 'job-positions':
    fetch('api/companyprofile/fetch_jobpositions.php')
      .then(r => r.json())
      .then(res => {
        if (!res.success) throw new Error(res.message);
        
        const el = document.getElementById('tbl-job-positions');
        if (el) delete el.dataset.built; // Clear built guard to allow re-rendering

        buildTable('tbl-job-positions', {
          columns: [
            { key: 'title', label: 'Job Title' },
            { key: 'dept',  label: 'Department' },
            { key: 'count', label: 'Headcount' },
            { key: 'status', label: 'Status' },
            {
              key: '_',
              label: 'Actions',
              render: () => `
                <div class="flex-row">
                  <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                    <i data-lucide="trash-2" size="10"></i>
                  </button>
                </div>`
            }
          ],
          rows: res.data
        });
      })
      .catch(err => {
        console.error(err);
        document.getElementById('tbl-job-positions').innerHTML =
          `<p style="padding:20px;color:#dc2626;">Error loading job positions: ${err.message}</p>`;
      });
    break;
    case 'branch-offices':
    fetch('api/companyprofile/fetch_branchoffices.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      
      const el = document.getElementById('tbl-branch-offices');
      if (el) delete el.dataset.built;

      buildTable('tbl-branch-offices', {
        columns: [
          { key: 'name',     label: 'Branch Name' },
          { key: 'manager',  label: 'Branch Manager' },
          { key: 'phone',    label: 'Phone' },
          { key: 'email',    label: 'Email' },
          { key: 'location', label: 'Location' },
          { key: 'emp',      label: 'Staff' },
          { 
            key: 'status',   
            label: 'Status',
            render: (v) => v === 'Active' ? statusBadge.active : statusBadge.inactive 
          },
          {
            key: '_',
            label: 'Actions',
            render: () => `
              <div class="flex-row">
                <button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                  <i data-lucide="trash-2" size="10"></i>
                </button>
              </div>`
          }
        ],
        rows: res.data
      });
    })
    .catch(err => {
      console.error(err);
      document.getElementById('tbl-branch-offices').innerHTML =
        `<p style="padding:20px;color:#dc2626;">Error loading branches: ${err.message}</p>`;
    });
  break;
    case 'employee-directory':
      fetch('api/employees/fetch_empprofiles.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      
      const el = document.getElementById('tbl-employees');
      if (el) delete el.dataset.built;

      buildTable('tbl-employees', {
        columns: [
          {key:'id', label:'Emp ID'},
          {key:'fname', label:'First Name'},
          {key:'mname', label:'Middle Name'},
          {key:'lname', label:'Last Name'},
          {key:'uname', label:'Username'},
          {key:'gender', label:'Gender'},
          {key:'dob', label:'Date of Birth'},
          {key:'hire', label:'Hired Date'},
          {
            key:'status', 
            label:'Status',
            render: (v) => {
              const s = v.toLowerCase();
              if (s === 'active') return statusBadge.active;
              if (s === 'inactive' || s === 'terminated') return statusBadge.inactive;
              return b('warning', v); // Fallback for 'On Leave', etc.
            }
          },
          {key:'marital', label:'Marital Status'},
          {key:'phone', label:'Phone'},
          {key:'email', label:'Email'},
          {key:'dept', label:'Department'},
          {key:'position', label:'Job Position'},
          {key:'branch', label:'Branch name'},
          {key:'type', label:'Emp Type'},
          {key:'bankname', label:'Bank name'},
          {key:'bankacc', label:'Bank Account'},
          {key:'tin', label:'Tin number'},
          {key:'created', label:'Created At'},
          {
            key: '_',
            label: 'Actions',
            render: () => `
              <div class="flex-row">
                <button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                  <i data-lucide="trash-2" size="10"></i>
                </button>
              </div>`
          }
        ],
        rows: res.data,
        perPage: 15 // Increased because directory is usually long
      });
    })
    .catch(err => {
      console.error(err);
      document.getElementById('tbl-employees').innerHTML =
        `<p style="padding:20px;color:#dc2626;">Error loading directory: ${err.message}</p>`;
    });
  break;
    case 'employment-types':
        fetch('api/employees/fetch_emptypes.php')
          .then(r => r.json())
          .then(res => {
            if (!res.success) throw new Error(res.message);
            
            const el = document.getElementById('tbl-employment-types');
            if (el) delete el.dataset.built;

            buildTable('tbl-employment-types', {
              columns: [
                { key: 'name', label: 'Type Name' },
                { key: 'desc', label: 'Description' },
                { key: 'count', label: 'Employees' },
                {
                  key: '_',
                  label: 'Actions',
                  render: () => `
                    <div class="flex-row">
                      <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                        <i data-lucide="trash-2" size="10"></i>
                      </button>
                    </div>`
                }
              ],
              rows: res.data
            });
          })
          .catch(err => {
            console.error(err);
            document.getElementById('tbl-employment-types').innerHTML =
              `<p style="padding:20px;color:#dc2626;">Error loading employment types: ${err.message}</p>`;
          });
        break;
    case 'probation-tracker':
      fetch('api/employees/fetch_probation.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-probation');
          if (el) delete el.dataset.built;

          buildTable('tbl-probation', {
            columns: [
              { key: 'name',  label: 'Employee' },
              { key: 'dept',  label: 'Department' },
              { key: 'start', label: 'Probation Start' },
              { key: 'end',   label: 'Probation End' },
              { 
                key: 'days',  
                label: 'Days Left',
                render: (v) => {
                    const days = parseInt(v);
                    if (days < 0) return `<span style="color:var(--danger); font-weight:bold;">Overdue (${Math.abs(days)})</span>`;
                    if (days <= 14) return `<span style="color:var(--warning); font-weight:bold;">${days} Days</span>`;
                    return `${days} Days`;
                }
              },
              { 
                key: 'status', 
                label: 'Probation Status',
                render: (v, row) => {
                    const days = parseInt(row.days);
                    if (v === 'Extended') return b('warning', 'Extended');
                    if (days <= 14) return b('danger', 'Ending Soon');
                    return b('info', 'Active');
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="Evaluate"><i data-lucide="clipboard-check" size="10"></i></button>
                    <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                      <i data-lucide="trash-2" size="10"></i>
                    </button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-probation').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading probation data: ${err.message}</p>`;
        });
      break;
    case 'contract-renewals':
  fetch('api/employees/fetch_contracts.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      
      const el = document.getElementById('tbl-contract-renewals');
      if (el) delete el.dataset.built;

      buildTable('tbl-contract-renewals', {
        columns: [
          { key: 'name',   label: 'Employee' },
          { key: 'dept',   label: 'Department' },
          { key: 'start',  label: 'Start Date' },
          { key: 'expiry', label: 'Expiry Date' },
          { 
            key: 'days',   
            label: 'Days to Expiry',
            render: (v) => {
              const days = parseInt(v);
              if (days < 0) return `<span style="color:var(--danger); font-weight:bold;">Expired (${Math.abs(days)}d ago)</span>`;
              if (days <= 15) return `<span style="color:var(--danger); font-weight:bold;">${days} Days</span>`;
              if (days <= 30) return `<span style="color:var(--warning); font-weight:bold;">${days} Days</span>`;
              return `${days} Days`;
            }
          },
          { 
            key: 'status', 
            label: 'Status',
            render: (v, row) => {
              const days = parseInt(row.days);
              if (days < 0) return b('danger', 'Expired');
              if (days <= 15) return b('danger', 'Critical');
              if (days <= 30) return b('warning', 'Due Soon');
              return b('success', 'Active');
            }
          },
          {
            key: '_',
            label: 'Actions',
            render: () => `
              <div class="flex-row">
                <button class="btn btn-xs btn-secondary" title="Renew Contract"><i data-lucide="refresh-cw" size="10"></i></button>
                <button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>
              </div>`
          }
        ],
        rows: res.data
      });
    })
    .catch(err => {
      console.error(err);
      document.getElementById('tbl-contract-renewals').innerHTML =
        `<p style="padding:20px;color:#dc2626;">Error loading renewals: ${err.message}</p>`;
    });
  break;
    case 'retirement-planner':
  fetch('api/employees/fetch_retirement.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      
      const el = document.getElementById('tbl-retirement');
      if (el) delete el.dataset.built;

      // Update the "Upcoming Retirement" count badge in the UI
      // We count how many people reach retirement in the next 90 days
      const upcomingCount = res.data.filter(emp => emp.days <= 90 && emp.days >= 0).length;
      const countEl = document.getElementById('count-upcoming-ret');
      if (countEl) countEl.textContent = upcomingCount;

      buildTable('tbl-retirement', {
        columns: [
          { key: 'name',   label: 'Employee' },
          { key: 'dept',   label: 'Department' },
          { key: 'age',    label: 'Age' },
          { key: 'tenure', label: 'Service Period' },
          { key: 'date',   label: 'Retirement Date' },
          { 
            key: 'days',   
            label: 'Status',
            render: (v) => {
              const days = parseInt(v);
              if (days < 0) return b('neutral', 'Retired');
              if (days <= 90) return b('danger', `Upcoming (${days}D)`);
              if (days <= 365) return b('warning', 'Within Year');
              return b('info', 'Active');
            }
          },
          { 
            key: 'pension', 
            label: 'Pension Status', 
            render: () => b('warning', 'In Progress') // Defaulting as this is usually manual
          },
          { 
            key: '_', 
            label: 'Actions', 
            render: () => `
              <div class="flex-row">
                <button class="btn btn-xs btn-secondary" title="Succession Plan"><i data-lucide="user-plus" size="10"></i></button>
                <button class="btn btn-xs btn-primary" title="Clearance"><i data-lucide="clipboard-check" size="10"></i></button>
              </div>`
          }
        ],
        rows: res.data,
        perPage: 10
      });
    })
    .catch(err => {
      console.error(err);
      document.getElementById('tbl-retirement').innerHTML =
        `<p style="padding:20px;color:#dc2626;">Error loading retirement data: ${err.message}</p>`;
    });
  break;
    case 'former-employees':
  fetch('api/employees/fetch_former_employees.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      
      const el = document.getElementById('tbl-former-employees');
      if (el) delete el.dataset.built; // Clear built guard

      buildTable('tbl-former-employees', {
        columns: [
          { key: 'name',     label: 'Employee' },
          { key: 'dept',     label: 'Last Department' },
          { key: 'role',     label: 'Last Role' },
          { key: 'exitDate', label: 'Exit Date' },
          { 
            key: 'type',     
            label: 'Reason',
            render: (v) => v === 'Terminated' ? b('danger', v) : b('neutral', v)
          },
          { key: 'duration', label: 'Duration' },
          { 
            key: 'rehire',   
            label: 'Rehire possibility',
            render: (v) => v === 'No' ? b('danger', 'No') : b('success', 'Yes')
          },
          {
            key: '_',
            label: 'Actions',
            render: () => `
              <div class="flex-row">
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                  <i data-lucide="trash-2" size="10"></i>
                </button>
              </div>`
          }
        ],
        rows: res.data
      });
    })
    .catch(err => {
      console.error(err);
      document.getElementById('tbl-former-employees').innerHTML =
        `<p style="padding:20px;color:#dc2626;">Error loading former employees: ${err.message}</p>`;
    });
  break;
    case 'asset-tracking':
      fetch('api/employees/fetch_assets.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-assets');
          if (el) delete el.dataset.built; // Allow re-render

          buildTable('tbl-assets', {
            columns: [
              { key: 'id', label: 'Item Code' },
              { key: 'name', label: 'Asset Name' },
              { key: 'cat', label: 'Category' },
              { key: 'serial', label: 'Serial number' },
              { 
                key: 'val', 
                label: 'Asset Value',
                render: (v) => v ? 'ETB ' + parseFloat(v).toLocaleString() : '—'
              },
              { key: 'user_prev', label: 'Previous custodian' },
              { key: 'user', label: 'Current custodian' },
              { key: 'loc', label: 'Location' }, 
              { key: 'war', label: 'Warranty' }, 
              { 
                key: '_', 
                label: 'Actions', 
                render: (v, row) => `
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    <button class="btn btn-xs btn-secondary" title="View Details">
                      <i data-lucide="eye" size="10"></i>
                    </button>
                    <button class="btn btn-xs btn-secondary" onclick="openReassignModal('${row.name}','${row.user}')" title="Reassign">
                      <i data-lucide="shuffle" size="10"></i>
                    </button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-assets').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading assets: ${err.message}</p>`;
        });
      break;
    case 'document-vault':initVaultMatrix();break;
    case 'job-vacancies':
      fetch('api/talent/fetch_vacancies.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-vacancies');
          if (el) delete el.dataset.built;

          buildTable('tbl-vacancies', {
            columns: [
              { key: 'title', label: 'Position' },
              { key: 'dept',  label: 'Department' },
              { key: 'branch', label: 'Branch' },
              { key: 'type',   label: 'Type' },
              { key: 'posted', label: 'Posted' },
              { key: 'deadline', label: 'Deadline' },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                  const s = v.toLowerCase();
                  if (s === 'open') return b('success', 'Open');
                  if (s === 'closed') return b('danger', 'Closed');
                  if (s === 'filled') return b('neutral', 'Filled');
                  if (s === 'on hold') return b('warning', 'On Hold');
                  return b('info', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button>
                    <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                      <i data-lucide="trash-2" size="10"></i>
                    </button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-vacancies').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading vacancies: ${err.message}</p>`;
        });
      break;
    case 'candidates':
      fetch('api/talent/fetch_candidates.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-candidates');
          if (el) delete el.dataset.built;

          buildTable('tbl-candidates', {
            columns: [
              { key: 'name', label: 'Candidate' },
              { key: 'position', label: 'Applied For' },
              { key: 'applied', label: 'Applied Date' },
              { 
                key: 'stage', 
                label: 'Stage',
                render: (v) => {
                  const s = v.toLowerCase();
                  if (s === 'hired') return b('success', 'Hired');
                  if (s === 'rejected') return b('danger', 'Rejected');
                  if (s === 'interview') return b('warning', 'Interview');
                  if (s === 'offer') return b('primary', 'Offer Made');
                  if (s === 'screening') return b('info', 'Screening');
                  return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                     
                    <button class="btn btn-xs btn-secondary" title="Download CV"><i data-lucide="download" size="10"></i></button>
                     
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-candidates').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading candidates: ${err.message}</p>`;
        });
      break;
    case 'interview-tracker':
      fetch('api/talent/fetch_interviews.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-interviews');
          if (el) delete el.dataset.built;

          buildTable('tbl-interviews', {
            columns: [
              { key: 'candidate', label: 'Candidate' },
              { key: 'position', label: 'Position' },
              { key: 'interviewer', label: 'Interviewer' },
              { key: 'date', label: 'Date' },
              { key: 'time', label: 'Time' },
              { key: 'mode', label: 'Mode' },
              { 
                key: 'result', 
                label: 'Result',
                render: (v) => {
                  const s = v.toLowerCase();
                  if (s === 'passed') return b('success', 'Passed');
                  if (s === 'failed') return b('danger', 'Failed');
                  if (s === 'scheduled') return b('info', 'Scheduled');
                  if (s === 'on hold') return b('warning', 'On Hold');
                  if (s === 'no show') return b('neutral', 'No Show');
                  return b('primary', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="Edit Interview"><i data-lucide="edit-3" size="10"></i></button>                      
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-interviews').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading interviews: ${err.message}</p>`;
        });
      break;
    case 'internship':
      fetch('api/talent/fetch_internships.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-internship');
          if (el) delete el.dataset.built;

          buildTable('tbl-internship', {
            columns: [
              {key:'id_code', label:'Intern ID'},
              {key:'name', label:'Full Name'},
              {key:'uni', label:'Institution'},
              {key:'dept', label:'Assigned Dept'},
              {key:'mentor', label:'Mentor'},
              {key:'start', label:'Start Date'},
              {key:'end', label:'End Date'},
              {
                key:'eval',
                label:'Evaluation',
                render: (v) => {
                    if (!v || v === '0.00') return '<span style="color:var(--muted)">Pending</span>';
                    return b('primary', parseFloat(v).toFixed(0) + '%');
                }
              },
              {
                key:'status',
                label:'Status',
                render: (v) => {
                  if (v === 'Active') return b('success', 'Active');
                  if (v === 'Completed') return b('neutral', 'Completed');
                  if (v === 'Terminated') return b('danger', 'Terminated');
                  return b('info', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="Evaluation Form"><i data-lucide="clipboard-check" size="10"></i></button>
                    <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                      <i data-lucide="trash-2" size="10"></i>
                    </button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-internship').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading internships: ${err.message}</p>`;
        });
      break;
    case 'daily-attendance':
      fetch('api/attendance/fetch_daily.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-attendance');
          if (el) delete el.dataset.built;

          buildTable('tbl-attendance', {
            columns: [
              { key: 'name', label: 'Employee' },
              { key: 'dept', label: 'Dept' },
              { key: 'shift', label: 'Shift' },
              { 
                key: 'checkin', 
                label: 'Check In', 
                render: (v) => v ? v.substring(0, 5) : '—' 
              },
              { 
                key: 'checkout', 
                label: 'Check Out', 
                render: (v) => v ? v.substring(0, 5) : '—' 
              },
              { key: 'hours', label: 'Hours' },
              { key: 'ot', label: 'OT' },
              { 
                key: 'status', 
                label: 'Status',
                render: (v, row) => {
                  // Logic to show "Late" badge if the DB flag is set
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
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Logs"><i data-lucide="eye" size="10"></i></button>
                    <button class="btn btn-xs btn-secondary" title="Edit Entry"><i data-lucide="edit-2" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-attendance').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading attendance: ${err.message}</p>`;
        });
      break;
    case 'overtime-requests':
      fetch('api/benefits/fetch_overtime.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-overtime');
          if (el) delete el.dataset.built;

          buildTable('tbl-overtime', {
            columns: [
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Dept' },
              { key: 'date', label: 'Date' },
              { 
                key: 'hours', 
                label: 'OT Hours',
                render: (v) => `<b>${v} hrs</b>`
              },
              { 
                key: 'reason', 
                label: 'Reason',
                render: (v) => `<span title="${v}" style="display:block; max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v}</span>`
              },
              { key: 'submitted', label: 'Submitted' },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                  if (v === 'Approved') return b('success', 'Approved');
                  if (v === 'Rejected') return b('danger', 'Rejected');
                  if (v === 'Pending') return b('warning', 'Pending');
                  return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: (v, row) => `
                  <div class="flex-row">
                    ${row.status === 'Pending' ? 
                      `<button class="btn btn-xs btn-primary" title="Approve/Reject"><i data-lucide="check-circle" size="10"></i> Process</button>` : 
                      `<button class="btn btn-xs btn-secondary" title="View Detail"><i data-lucide="eye" size="10"></i></button>`
                    }
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-overtime').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading overtime: ${err.message}</p>`;
        });
      break;
    case 'attendance-reports':
      fetch('api/attendance/fetch_reports.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-attendance-reports');
          if (el) delete el.dataset.built;

          buildTable('tbl-attendance-reports', {
            columns: [
              { key: 'dept', label: 'Department' },
              { key: 'total', label: 'Total Emp' },
              { key: 'absent', label: 'Absent Days' },
              { key: 'leave_days', label: 'Leave Days' },
              { key: 'late', label: 'Late Arrivals' },
              { 
                key: 'ot', 
                label: 'Total OT Hrs',
                render: (v) => parseFloat(v).toFixed(1)
              },
              { 
                key: 'rate', 
                label: 'Attendance Rate',
                render: (v) => {
                    const val = parseFloat(v);
                    let color = 'var(--success)';
                    if (val < 90) color = 'var(--warning)';
                    if (val < 75) color = 'var(--danger)';
                    return `<b style="color:${color}">${val}%</b>`;
                }
              } 
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-attendance-reports').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading reports: ${err.message}</p>`;
        });
      break;
    case 'leave-types':
      fetch('api/leave/fetch_leave_types.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-leave-types');
          if (el) delete el.dataset.built;

          buildTable('tbl-leave-types', {
            columns: [
              { key: 'name', label: 'Leave Type' },
              { 
                key: 'days', 
                label: 'Days/Year',
                render: (v) => v ? v : '—' 
              },
              { 
                key: 'carry', 
                label: 'Carryover',
                render: (v) => v ? v + ' days' : '0 days'
              },
              { 
                key: 'paid', 
                label: 'Paid Status',
                render: (v) => v === 'Yes' ? b('success', 'Paid') : (v === 'No' ? b('danger', 'Unpaid') : b('warning', v))
              },
              { 
                key: 'approval', 
                label: 'Needs Approval',
                render: (v) => v == 1 ? 'Yes' : 'No'
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    
                    <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                      <i data-lucide="trash-2" size="10"></i>
                    </button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-leave-types').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading leave types: ${err.message}</p>`;
        });
      break; 
    case 'leave-requests':
      fetch('api/leave/fetch_leaverequests.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-leave-requests');
          if (el) delete el.dataset.built;

          buildTable('tbl-leave-requests', {
            columns: [
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { key: 'type', label: 'Leave Type' },
              { 
                key: 'approver', 
                label: 'Approver',
                render: (v) => v ? v : '<span style="color:var(--muted)">—</span>'
              },
              { key: 'from', label: 'From' },
              { key: 'to', label: 'To' },
              { key: 'days', label: 'Days' },
              { 
                key: 'reason', 
                label: 'Reason',
                render: (v) => `<span title="${v}" style="display:block; max-width:120px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v}</span>`
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                  if (v === 'Approved') return b('success', 'Approved');
                  if (v === 'Rejected') return b('danger', 'Rejected');
                  if (v === 'Pending') return b('warning', 'Pending');
                  return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: (v, row) => `
                  <div class="flex-row">
                    ${row.status === 'Pending' ? 
                      `<button class="btn btn-xs btn-primary" title="Process Request"><i data-lucide="check-square" size="10"></i> Review</button>` : 
                      `<button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button>`
                    }
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-leave-requests').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading leave requests: ${err.message}</p>`;
        });
      break;
    case 'leave-entitlement':
      fetch('api/leave/fetch_entitlement.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-leave-entitlement');
          if (el) delete el.dataset.built;

          buildTable('tbl-leave-entitlement', {
            columns: [
              { key: 'id', label: 'Emp ID' },
              { key: 'name', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { key: 'al_total', label: 'AL Total' },
              { key: 'al_used', label: 'AL Used' },
              { 
                key: 'al_bal', 
                label: 'AL Balance',
                render: (v) => `<b style="color:var(--primary)">${v}</b>`
              },
              { key: 'sl_used', label: 'SL Used' },
              { key: 'sl_bal', label: 'SL Balance' },
              { 
                key: 'carry', 
                label: 'Carried Over',
                render: (v) => v > 0 ? b('info', v + ' Days') : '0'
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <button class="btn btn-xs btn-secondary" title="Update Entitlement">
                    <i data-lucide="edit-3" size="10"></i>
                  </button>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-leave-entitlement').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading entitlements: ${err.message}</p>`;
        });
      break;
    case 'medical-claims':
      fetch('api/benefits/fetch_medical_claims.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-medical');
          if (el) delete el.dataset.built;

          buildTable('tbl-medical', {
            columns: [
              { key: 'id', label: 'Claim ID' },
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { key: 'category', label: 'Category' },
              { 
                key: 'amount', 
                label: 'Amount',
                render: (v) => 'ETB ' + parseFloat(v).toLocaleString(undefined, {minimumFractionDigits: 2})
              },
              { key: 'submitted', label: 'Submitted' },
              { 
                key: 'receipt', 
                label: 'Receipt',
                render: (v) => v == 1 ? b('success', 'Attached') : b('neutral', 'None')
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                  if (v === 'Approved') return b('success', 'Approved');
                  if (v === 'Rejected') return b('danger', 'Rejected');
                  if (v === 'Pending') return b('warning', 'Pending');
                  return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: (v, row) => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Receipt"><i data-lucide="file-text" size="10"></i></button>
                    ${row.status === 'Pending' ? 
                      `<button class="btn btn-xs btn-primary" title="Process"><i data-lucide="check-circle" size="10"></i></button>` : 
                      ''
                    }
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-medical').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading claims: ${err.message}</p>`;
        });
      break;
    case 'training-needs':
      fetch('api/training/fetch_training_needs.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-training-needs');
          if (el) delete el.dataset.built;

          buildTable('tbl-training-needs', {
            columns: [
              { key: 'dept', label: 'Department' },
              { key: 'skill', label: 'Skill Gap' },
              { 
                key: 'priority', 
                label: 'Priority',
                render: (v) => {
                    if (v === 'High') return b('danger', 'High');
                    if (v === 'Medium') return b('warning', 'Medium');
                    return b('neutral', 'Low');
                }
              },
              { 
                key: 'emp_count', 
                label: 'Affected',
                render: (v) => `<b>${v} Employees</b>`
              },
              { key: 'proposed', label: 'Proposed Training' },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                    if (v === 'Approved') return b('success', 'Approved');
                    if (v === 'Ongoing') return b('primary', 'Ongoing');
                    if (v === 'Pending') return b('warning', 'Pending');
                    return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button>
                    <button class="btn btn-xs btn-primary" title="Schedule Training"><i data-lucide="calendar-plus" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-training-needs').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading training needs: ${err.message}</p>`;
        });
      break;
    case 'training-schedule':
      fetch('api/training/fetch_training_schedule.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-training-schedule');
          if (el) delete el.dataset.built;

          buildTable('tbl-training-schedule', {
            columns: [
              { key: 'course', label: 'Course' },
              { key: 'dept', label: 'Department' },
              { key: 'trainer', label: 'Trainer' },
              { key: 'date', label: 'Date' },
              { 
                key: 'time', 
                label: 'Time',
                render: (v) => v ? v.substring(0, 5) : '—'
              },
              { key: 'venue', label: 'Venue' },
              { 
                key: 'seats', 
                label: 'Enrolled/Seats',
                render: (v, row) => {
                    const pct = (row.enrolled_seats / row.total_seats) * 100;
                    let color = pct >= 100 ? 'var(--danger)' : 'var(--primary)';
                    return `<span style="font-weight:700; color:${color}">${row.enrolled_seats}</span> / ${row.total_seats}`;
                }
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                    if (v === 'Confirmed') return b('success', 'Confirmed');
                    if (v === 'Open') return b('warning', 'Open');
                    if (v === 'Cancelled') return b('danger', 'Cancelled');
                    return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Roster"><i data-lucide="users" size="10"></i></button>
                    <button class="btn btn-xs btn-primary" title="Edit Session"><i data-lucide="edit-3" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-training-schedule').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading schedule: ${err.message}</p>`;
        });
      break;
    case 'performance-reviews':
      fetch('api/performance/fetch_reviews.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-reviews');
          if (el) delete el.dataset.built;

          buildTable('tbl-reviews', {
            columns: [
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { key: 'reviewer', label: 'Reviewer' },
              { key: 'period', label: 'Period' },
              { 
                key: 'score', 
                label: 'Overall Score',
                render: (v) => v ? `<b>${parseFloat(v).toFixed(1)}</b> / 10` : '—'
              },
              { 
                key: 'rank', 
                label: 'Rating',
                render: (v) => {
                  if (v === 'Exceptional') return b('success', v);
                  if (v === 'Exceeds') return b('primary', 'Exceeds');
                  if (v === 'Meets') return b('neutral', 'Meets');
                  if (v === 'Below') return b('danger', 'Below Expectation');
                  return b('neutral', v);
                }
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                  if (v === 'Submitted') return b('success', 'Submitted');
                  if (v === 'Pending') return b('warning', 'Pending');
                  if (v === 'Acknowledged') return b('info', 'Acknowledged');
                  return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Review"><i data-lucide="eye" size="10"></i></button>
                    <button class="btn btn-xs btn-primary" title="Print PDF"><i data-lucide="printer" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-reviews').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading reviews: ${err.message}</p>`;
        });
      break;
    case '360-feedback':
      fetch('api/performance/fetch_360.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-360');
          if (el) delete el.dataset.built;

          buildTable('tbl-360', {
            columns: [
              { key: 'subject', label: 'Subject' },
              { key: 'dept', label: 'Department' },
              { key: 'total', label: 'Total Respondents' },
              { 
                key: 'complete', 
                label: 'Completed',
                render: (v, row) => {
                    const pct = Math.round((v / row.total) * 100);
                    return `<b>${v}</b> <small style="color:var(--muted)">(${pct}%)</small>`;
                }
              },
              { 
                key: 'avg', 
                label: 'Avg Score',
                render: (v) => v ? `<b>${parseFloat(v).toFixed(1)}</b> / 10` : '<span style="color:var(--muted)">TBD</span>'
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                  if (v === 'Closed') return b('success', 'Closed');
                  if (v === 'Open') return b('primary', 'Open');
                  if (v === 'In Progress') return b('warning', 'In Progress');
                  return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Individual Feedback"><i data-lucide="users" size="10"></i></button>
                    <button class="btn btn-xs btn-primary" title="Generate Report"><i data-lucide="file-bar-chart" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-360').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading feedback data: ${err.message}</p>`;
        });
      break;
    case 'Promote/Demote':
     fetch('api/movement/fetch_promotions.php')
     .then(r => r.json())
     .then(res => {
      if (!res.success) throw new Error(res.message);
      
      const el = document.getElementById('tbl-Promote/Demote');
      if (el) delete el.dataset.built;

      buildTable('tbl-Promote/Demote', {
        columns: [
          { key: 'emp',      label: 'Employee' },
          { 
            key: 'type',     
            label: 'Type',
            render: (v) => v === 'Promotion' ? b('success', v) : b('warning', v)
          },
          { key: 'from_pos', label: 'Prev Position' },
          { key: 'to_pos',   label: 'Current Position' },
          { key: 'dept',     label: 'Dept' },
          { 
            key: 'sal_from', 
            label: 'Old Salary',
            render: (v) => v ? 'ETB ' + parseFloat(v).toLocaleString() : '—'
          },
          { 
            key: 'sal_to',   
            label: 'New Salary',
            render: (v) => v ? 'ETB ' + parseFloat(v).toLocaleString() : '—'
          },
          { key: 'eff_date', label: 'Effective' },
          { 
            key: 'status',   
            label: 'Status',
            render: (v) => {
                if (v === 'Approved') return statusBadge.approved;
                if (v === 'Pending') return statusBadge.pending;
                if (v === 'Rejected') return statusBadge.rejected;
                return b('info', v);
            }
          },
          {
            key: '_',
            label: 'Actions',
            render: () => `
              <div class="flex-row">
                <button class="btn btn-xs btn-secondary"><i data-lucide="eye" size="10"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                  <i data-lucide="trash-2" size="10"></i>
                </button>
              </div>`
          }
        ],
        rows: res.data
      });
    })
    .catch(err => {
      console.error(err);
      document.getElementById('tbl-Promote/Demote').innerHTML =
        `<p style="padding:20px;color:#dc2626;">Error loading movement data: ${err.message}</p>`;
    });
  break;
    case 'transfers':
     fetch('api/movement/fetch_transfers.php')
    .then(r => r.json())
    .then(res => {
      if (!res.success) throw new Error(res.message);
      
      const el = document.getElementById('tbl-transfers-dept');
      if (el) delete el.dataset.built;

      buildTable('tbl-transfers-dept', {
        columns: [
          { key: 'emp',         label: 'Employee' },
          { key: 'from_dept',   label: 'From Department' },
          { key: 'to_dept',     label: 'To Department' },
          { key: 'from_branch', label: 'From Branch' },
          { key: 'to_branch',   label: 'To Branch' },
          { key: 'req_date',    label: 'Requested' },
          { key: 'eff_date',    label: 'Effective' },
          { 
            key: 'status',      
            label: 'Status',
            render: (v) => {
                if (v === 'Approved') return statusBadge.approved;
                if (v === 'Pending') return statusBadge.pending;
                if (v === 'Rejected') return statusBadge.rejected;
                return b('info', v);
            }
          },
          {
            key: '_',
            label: 'Actions',
            render: () => `
              <div class="flex-row">
                <button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button>
                <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                  <i data-lucide="trash-2" size="10"></i>
                </button>
              </div>`
          }
        ],
        rows: res.data
      });
    })
    .catch(err => {
      console.error(err);
      document.getElementById('tbl-transfers-dept').innerHTML =
        `<p style="padding:20px;color:#dc2626;">Error loading transfer data: ${err.message}</p>`;
    });
  break;
    case 'attendance': initAttendanceGrid(); break;
    case 'disciplinary-actions':
      fetch('api/compliance/fetch_disciplinary.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-disciplinary');
          if (el) delete el.dataset.built;

          buildTable('tbl-disciplinary', {
            columns: [
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { 
                key: 'type', 
                label: 'Action Type',
                render: (v) => {
                  // Color coding based on severity
                  if (v === 'Verbal Warning') return b('neutral', v);
                  if (v === 'Written Warning') return b('warning', v);
                  if (v === 'Final Warning') return b('danger', v);
                  if (v === 'Suspension') return b('danger', 'Suspended');
                  if (v === 'Demotion') return b('primary', v);
                  return b('neutral', v);
                }
              },
              { key: 'incident', label: 'Incident Date' },
              { key: 'issued', label: 'Issued Date' },
              { 
                key: 'issuer_name', 
                label: 'Issued By',
                render: (v) => v ? v : '<span style="color:var(--muted)">System</span>'
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Details"><i data-lucide="eye" size="10"></i></button>
                    <button class="btn btn-xs" style="background:#fee2e2;color:#dc2626;border:none;padding:3px 8px;border-radius:6px;font-size:.68rem;cursor:pointer;">
                      <i data-lucide="trash-2" size="10"></i>
                    </button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-disciplinary').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading disciplinary records: ${err.message}</p>`;
        });
      break;
    case 'resignations':
      fetch('api/compliance/fetch_resignations.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-resignations');
          if (el) delete el.dataset.built;

          buildTable('tbl-resignations', {
            columns: [
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { key: 'type', label: 'Reason' },
              { key: 'filed', label: 'Filed' },
              { 
                key: 'assigned', 
                label: 'Assigned To',
                render: (v) => v ? v : '<span style="color:var(--muted)">Unassigned</span>'
              },
              { 
                key: 'priority', 
                label: 'Priority',
                render: (v) => {
                  if (v === 'High') return b('danger', 'High');
                  if (v === 'Medium') return b('warning', 'Medium');
                  return b('neutral', v);
                }
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => {
                  if (v === 'Resolved') return b('success', 'Resolved');
                  if (v === 'Pending') return b('warning', 'Pending');
                  if (v === 'Under Review') return b('info', 'Under Review');
                  return b('neutral', v);
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                     
                    <button class="btn btn-xs btn-primary" title="Process Separation"><i data-lucide="user-minus" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-resignations').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading resignations: ${err.message}</p>`;
        });
      break;
    case 'termination':
      fetch('api/compliance/fetch_separations.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-termination');
          if (el) delete el.dataset.built;

          buildTable('tbl-termination', {
            columns: [
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { key: 'type', label: 'Separation Type' },
              { key: 'notice', label: 'Notice Date' },
              { key: 'last_day', label: 'Last Working Day' },
              { 
                key: 'clearance', 
                label: 'Clearance',
                render: (v) => v === 'Done' ? b('success', 'Done') : b('warning', 'Pending')
              },
              { 
                key: 'settlement', 
                label: 'Final Settlement',
                render: (v) => v ? 'ETB ' + parseFloat(v).toLocaleString(undefined, {minimumFractionDigits: 2}) : b('neutral', 'TBD')
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => v === 'Complete' ? b('success', 'Complete') : b('warning', 'In Progress')
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="View Dossier"><i data-lucide="folder" size="10"></i></button>
                    <button class="btn btn-xs btn-primary" title="Print Certificate"><i data-lucide="printer" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-termination').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading separation data: ${err.message}</p>`;
        });
      break;
    case 'roles-permissions': initRoles(); break;
    case 'exit-clearance':
      fetch('api/compliance/fetch_clearance.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-clearance');
          if (el) delete el.dataset.built;

          // Helper to render checkmarks or dashes
          const chk = v => v == 1 ? b('success', '✓') : '<span style="color:var(--muted)">—</span>';

          buildTable('tbl-clearance', {
            columns: [
              { key: 'emp', label: 'Employee' },
              { key: 'dept', label: 'Department' },
              { key: 'it', label: 'IT', render: chk },
              { key: 'finance', label: 'Finance', render: chk },
              { key: 'hr', label: 'HR', render: chk },
              { key: 'admin', label: 'Admin', render: chk },
              { key: 'assets', label: 'Assets', render: chk },
              { 
                key: 'overall', 
                label: 'Overall',
                render: (v) => {
                  if (v === 'Cleared') return b('success', 'Cleared');
                  if (v === 'In Progress') return b('info', 'In Progress');
                  return b('warning', 'Pending');
                }
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-primary" title="Update Status"><i data-lucide="check-square" size="10"></i> Sign-off</button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-clearance').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading clearance: ${err.message}</p>`;
        });
      break;
    case 'user-management':
      fetch('api/system/fetch_users.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-users');
          if (el) delete el.dataset.built;

          buildTable('tbl-users', {
            columns: [
              { 
                key: 'id', 
                label: 'User ID',
                render: (v) => `<span style="font-family:'JetBrains Mono'; font-size:10px;">USR-${String(v).padStart(3,'0')}</span>`
              },
              { key: 'name', label: 'Full Name' },
              { key: 'email', label: 'Email Address' },
              { 
                key: 'role', 
                label: 'Role',
                render: (v) => b('primary', v)
              },
              { key: 'dept', label: 'Dept' },
              { 
                key: 'last_login', 
                label: 'Last Login',
                render: (v) => v ? v : '<span style="color:var(--muted)">Never</span>'
              },
              { 
                key: 'status', 
                label: 'Status',
                render: (v) => v === 'Active' ? statusBadge.active : statusBadge.inactive
              },
              {
                key: '_',
                label: 'Actions',
                render: () => `
                  <div class="flex-row">
                    <button class="btn btn-xs btn-secondary" title="Edit Permissions"><i data-lucide="shield-check" size="10"></i></button>
                    <button class="btn btn-xs btn-secondary" title="Reset Password"><i data-lucide="key" size="10"></i></button>
                  </div>`
              }
            ],
            rows: res.data
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-users').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading users: ${err.message}</p>`;
        });
      break;
    case 'audit-logs':
      fetch('api/system/fetch_audit.php')
        .then(r => r.json())
        .then(res => {
          if (!res.success) throw new Error(res.message);
          
          const el = document.getElementById('tbl-audit');
          if (el) delete el.dataset.built;

          buildTable('tbl-audit', {
            columns: [
              { 
                key: 'user', 
                label: 'User',
                render: (v) => `<span style="font-weight:700;">${v}</span>`
              },
              { 
                key: 'action', 
                label: 'Action',
                render: (v) => {
                    // Highlight critical actions
                    if (v === 'DELETE') return b('danger', v);
                    if (v === 'UPDATE') return b('warning', v);
                    if (v === 'CREATE') return b('success', v);
                    if (v === 'LOGIN') return b('primary', v);
                    return b('neutral', v);
                }
              },
              { key: 'module', label: 'Module' },
              { 
                key: 'record', 
                label: 'Record',
                render: (v) => `<code style="background:#f1f5f9; padding:2px 4px; border-radius:4px; font-size:10px;">${v}</code>`
              },
              { 
                key: 'ip', 
                label: 'IP Address',
                render: (v) => `<span style="font-family:'JetBrains Mono'; font-size:10px; color:var(--muted);">${v}</span>`
              },
              { 
                key: 'ts', 
                label: 'Timestamp',
                render: (v) => {
                    const d = new Date(v);
                    return d.toLocaleString('en-GB', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
                }
              },
              {
                key: '_',
                label: 'Details',
                render: () => `<button class="btn btn-xs btn-secondary">View Changes</button>`
              }
            ],
            rows: res.data,
            perPage: 15 // Higher perPage for logs
          });
        })
        .catch(err => {
          console.error(err);
          document.getElementById('tbl-audit').innerHTML =
            `<p style="padding:20px;color:#dc2626;">Error loading audit logs: ${err.message}</p>`;
        });
      break;
    case 'hr-analytics':initAnalytics();break;
  }
}

window.addEventListener('DOMContentLoaded', () => {
  lcIcons();
  // Set page title based on current page parameter
  const urlParams = new URLSearchParams(window.location.search);
  const currentPage = urlParams.get('page') || 'dashboard';
  const titleEl = document.getElementById('page-title');
  if (titleEl) {
    const activeLink = document.querySelector(`.sub-link[onclick*="'${currentPage}'"], .dash-link[onclick*="'${currentPage}'"]`);
    titleEl.textContent = activeLink ? activeLink.textContent.trim() : currentPage.replace(/-/g, ' ');
  }
  // Initialize the current page
  if (typeof initPage === 'function') {
    initPage(currentPage);
  }
});
window.addEventListener('hashchange', () => {
  const currentHash = window.location.hash.replace('#', '');
  if (currentHash) {
    goPage(currentHash);
  }
});
//sorting algorithm for all tables 
function buildTable(containerId, {columns, rows, perPage=8}) {
  const container = document.getElementById(containerId);
  if (!container || container.dataset.built) return;
  container.dataset.built = '1';
  
  container._rows = [...rows]; 
  container._cols = columns; 
  container._perPage = perPage;
  container._currPg = 1;
  container._sort = { key: null, dir: 'desc' };

  container._render = function() {
    const start = (this._currPg - 1) * this._perPage;
    const end = Math.min(start + this._perPage, this._rows.length);
    const slice = this._rows.slice(start, end);
    
    // Header Logic: Check for action key '_'
    const colHeaders = this._cols.map(c => {
      if (c.key === '_') return `<th>${c.label}</th>`; // No sort for Actions
      
      const isSorted = this._sort.key === c.key;
      const arrow = isSorted ? (this._sort.dir === 'asc' ? ' ↑' : ' ↓') : ' ↕';
      return `<th onclick="sortTbl('${containerId}','${c.key}')">${c.label}<span class="sort-indicator">${arrow}</span></th>`;
    }).join('');

    const bodyRows = slice.map(row =>
      `<tr>${this._cols.map(c => {
        let v = row[c.key] !== undefined ? row[c.key] : '—';
        if (c.render) v = c.render(v, row);
        return `<td>${v}</td>`;
      }).join('')}</tr>`
    ).join('');

    const tp = Math.ceil(this._rows.length / this._perPage);
    let pgBtns = `<button class="pg-btn" onclick="changePg('${containerId}',-1)" ${this._currPg<=1?'disabled':''}>‹</button>`;
    for (let i=1; i<=tp; i++) {
      if (tp<=7 || i===1 || i===tp || Math.abs(i-this._currPg)<=1) 
        pgBtns += `<button class="pg-btn ${i===this._currPg?'active':''}" onclick="setPg('${containerId}',${i})">${i}</button>`;
      else if (Math.abs(i-this._currPg)===2) pgBtns += `<button class="pg-btn" disabled>…</button>`;
    }
    pgBtns += `<button class="pg-btn" onclick="changePg('${containerId}',1)" ${this._currPg>=tp?'disabled':''}>›</button>`;

    this.innerHTML = `
      <div class="filter-bar">
        <div class="search-container"><div class="search-inner">
          <i data-lucide="search" class="search-lead-icon"></i>
          <input type="text" placeholder="Search..." oninput="filterTbl('${containerId}', this.value)">
        </div></div>
      </div>
      <div class="table-wrap"><table class="tbl"><thead><tr>${colHeaders}</tr></thead><tbody>${bodyRows}</tbody></table></div>
      <div class="pagination"><span class="pagination-info">Showing ${this._rows.length?start+1:0}–${end} of ${this._rows.length}</span><div class="pagination-btns">${pgBtns}</div></div>`;
    lcIcons(this);
  };

  container._render();
}

// ── GLOBAL SORT HANDLER (DESC-FIRST) ──
window.sortTbl = (id, key) => {
  const c = document.getElementById(id);
  if (c._sort.key === key) {
    c._sort.dir = c._sort.dir === 'asc' ? 'desc' : 'asc';
  } else {
    c._sort.key = key;
    c._sort.dir = 'desc';
  }

  c._rows.sort((a, b) => {
    let vA = a[key], vB = b[key];
    const isNum = !isNaN(parseFloat(vA)) && isFinite(vA) && !isNaN(parseFloat(vB)) && isFinite(vB);
    let res = isNum ? (vA - vB) : String(vA).replace(/<[^>]*>/g, '').localeCompare(String(vB).replace(/<[^>]*>/g, ''), undefined, {numeric: true, sensitivity: 'base'});
    return c._sort.dir === 'asc' ? res : -res;
  });

  c._currPg = 1;
  c._render();
};

window.changePg = (id, dir) => { const c = document.getElementById(id); c._currPg += dir; c._render(); };
window.setPg = (id, p) => { const c = document.getElementById(id); c._currPg = p; c._render(); };
window.filterTbl = (id, val) => {
  const c = document.getElementById(id);
  if (!c._rawRows) c._rawRows = [...c._rows];
  const q = val.toLowerCase();
  c._rows = c._rawRows.filter(r => Object.values(r).some(v => String(v).toLowerCase().includes(q)));
  c._currPg = 1;
  c._render();
};
const mods = [
  
  {
    id: 'm-org', n: 'Company & Structure', i: 'building-2',
    subs: ['Company Profile', 'Organization Chart', 'Departments', 'Job Positions', 'Branch Offices']
  },
  {
    id: 'm-emp', n: 'Employees', i: 'users',
    subs: ['Employee Profile', 'Employment Types', 'Probation Tracker', 'Contract Renewals', 'Former employees', 'Attachment Vault', 'Asset Tracking']
  },
  {
    id: 'm-rec', n: 'Talent Acquisition', i: 'user-plus',
    subs: ['Add Job Vacancies', 'Job Applicant\'s List', 'Interview Tracker', 'Internship Management']
  },
  {
    id: 'm-move', n: 'Employee Movement', i: 'arrow-right-left',
    subs: ['Promote/Demote', 'Department Transfers']
  },
  {
    id: 'm-att', n: 'Attendance', i: 'clock',
    subs: ['Record attendance', 'Daily Attendance', 'Attendance Reports']
  },
  {
    id: 'm-leave', n: 'Leave Management', i: 'calendar-days',
    subs: ['Leave Types', 'Leave Requests', 'Leave Entitlement']
  },
  {
    id: 'm-ben', n: 'Benefits', i: 'heart-pulse',
    subs: ['Medical Claims', 'Overtime Requests']
  },
  {
    id: 'm-comp', n: 'Compliance & Exit', i: 'shield-alert',
    subs: ['Disciplinary Actions', 'Resignations', 'Separation & Exit', 'Exit Clearance']
  },
  {
    id: 'm-train', n: 'Training & Dev', i: 'graduation-cap',
    subs: ['Training Needs Analysis', 'Training Schedule']
  },
  {
    id: 'm-perf', n: 'Performance', i: 'trending-up',
    subs: ['Performance Reviews', '360° Feedback']
  },
  {
    id: 'm-sys', n: 'System Admin', i: 'settings-2',
    subs: ['User Management', 'Roles & Permissions', 'Audit Logs']
  }
];

function initRoles() { 
  selRole(document.querySelector('.role-pill-v2'), 'Super Admin'); 
}
 
let currentAccessMode = 'role';

function switchAccessMode(mode) {
    currentAccessMode = mode;
    const btnRole = document.getElementById('btn-mode-role');
    const btnUser = document.getElementById('btn-mode-user');
    const sideRole = document.getElementById('side-role-list');
    const sideUser = document.getElementById('side-user-search');
    const targetLabel = document.getElementById('perm-target-label');
    const warning = document.getElementById('override-warning');

    if (mode === 'role') {
        // UI Switching
        btnRole.style.background = 'var(--primary-light)'; btnRole.style.color = 'var(--primary)';
        btnUser.style.background = 'transparent'; btnUser.style.color = 'var(--muted)';
        sideRole.style.display = 'block';
        sideUser.style.display = 'none';
        targetLabel.textContent = "Standard Role:";
        warning.style.display = 'none';
        
        // Select first standard role
        selRole(document.querySelector('#side-role-list .role-pill-v2'), 'Super Admin');
    } else {
        // UI Switching
        btnUser.style.background = 'var(--primary-light)'; btnUser.style.color = 'var(--primary)';
        btnRole.style.background = 'transparent'; btnRole.style.color = 'var(--muted)';
        sideRole.style.display = 'none';
        sideUser.style.display = 'block';
        targetLabel.textContent = "Individual Override:";
        warning.style.display = 'inline-flex';
        
        // Clear the table until a user is picked
        document.getElementById('active-role-name').textContent = "No User Selected";
        document.getElementById('perm-grid').innerHTML = `<tr><td colspan="4" style="text-align:center; padding:40px; color:var(--muted)">Search and select a user to define individual permissions.</td></tr>`;
        document.getElementById('selected-user-card').style.display = 'none';
    }
    lcIcons();
}

// Override the existing selectAsItem specifically for Permissions
// Or handle it in a generic way if you prefer:
function selectUserForPerms(name) {
    document.getElementById('as-input-perm-user').value = name;
    document.getElementById('as-drop-perm-user').classList.remove('active');
    
    // Update the Profile Card
    document.getElementById('selected-user-card').style.display = 'block';
    document.getElementById('perm-user-name').textContent = name;
    document.getElementById('perm-user-id').textContent = "E-" + Math.floor(1000 + Math.random() * 9000);
    document.getElementById('perm-user-avatar').textContent = name.split(' ').map(n => n[0]).join('');
    
    // Set the Active Target
    document.getElementById('active-role-name').textContent = name;
    
    // Re-render the grid (Individual mode always starts with some base permissions)
    renderPermissionGrid(false); // Passing 'false' makes checkboxes active/changeable
}

// Ensure the grid reflects the parent-child relationship on load
function renderPermissionGrid(isSuperAdmin) {
    const grid = document.getElementById('perm-grid');
    let html = '';

    mods.forEach(m => {
        // 1. RENDER THE CATEGORY (THE GROUP)
        html += `
          <tr style="background: #f8fafc; border-bottom: 2px solid var(--border)">
            <td style="text-align:center; color:var(--primary)"><i data-lucide="${m.i}" size="14"></i></td>
            <td><b style="font-size:.75rem; text-transform:uppercase; letter-spacing:0.05em;">${m.n}</b></td>
            <td style="font-size:.65rem; color:var(--muted)">Enable/Disable entire sidebar category.</td>
            <td style="text-align:center">
              <label class="switch">
                <input type="checkbox" 
                       class="parent-check" 
                       data-module-id="${m.id}"
                       onchange="toggleModuleGroup('${m.id}', this.checked)"
                       ${isSuperAdmin ? 'checked disabled' : 'checked'}>
                <span class="slider"></span>
              </label>
            </td>
          </tr>
        `;

        // 2. RENDER THE SUB-CATEGORIES (INDIVIDUALS)
        m.subs.forEach(sub => {
            html += `
              <tr class="child-row-${m.id}">
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
                    <input type="checkbox" 
                           class="child-check" 
                           data-parent-ref="${m.id}"
                           onchange="checkParentStatus('${m.id}')"
                           ${isSuperAdmin ? 'checked disabled' : 'checked'}>
                    <span class="slider"></span>
                  </label>
                </td>
              </tr>
            `;
        });
    });

    grid.innerHTML = html;
    lcIcons(grid);
}

// Function to toggle all sub-modules when parent is clicked
function toggleModuleGroup(moduleId, isChecked) {
    // Find all checkboxes that belong to this category
    const children = document.querySelectorAll(`input[data-parent-ref="${moduleId}"]`);
    children.forEach(child => {
        child.checked = isChecked;
        // Optionally: gray out the child rows if parent is unchecked
        child.closest('tr').style.opacity = isChecked ? "1" : "0.5";
    });
}

// Optional: If you click a child ON, ensure the parent is also ON
// If you click all children OFF, the parent stays as is or you can auto-toggle it
function checkParentStatus(moduleId) {
    const parent = document.querySelector(`input[data-module-id="${moduleId}"]`);
    const children = document.querySelectorAll(`input[data-parent-ref="${moduleId}"]`);
    const anyChecked = Array.from(children).some(c => c.checked);
    
    // If at least one child is checked, the parent module should technically be active
    if (anyChecked && !parent.checked) {
        parent.checked = true;
    }
}
// Modify your existing selRole to call the new renderer
function selRole(el, name) {
    document.querySelectorAll('#side-role-list .role-pill-v2').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('active-role-name').textContent = name;
    renderPermissionGrid(name === 'Super Admin');
}

// Custom Save Logic
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

 

const ATT_CODES = ['P', 'H', 'A', 'L', 'O'];
function buildMatrix() {
  const m = document.getElementById('att-m-select').value;
  const y = document.getElementById('att-y-select').value;
  const deptFilter = document.getElementById('att-dept-select').value;
  // Get typed or selected name
  const nameInput = document.getElementById('as-input-att-name').value.toLowerCase().trim();

if (m === "" || y === "") {
    showNotification("Input Required", "Please select both a target Month and Fiscal Year to generate the registry.", "warning");
    return;
}

  const month = parseInt(m);
  const year = parseInt(y);
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const now = new Date();

  // 1. Header
  let headHtml = `<tr><th class="sticky-emp sticky-head"><div class="id-label-theme">Employee Identity</div></th>`;
  for (let d = 1; d <= daysInMonth; d++) {
    const dObj = new Date(year, month, d);
    const dayName = dObj.toLocaleDateString('en-US', { weekday: 'short' });
    const isToday = (d === now.getDate() && month === now.getMonth() && year === now.getFullYear());
    headHtml += `<th class="sticky-head att-day-col ${isToday ? 'is-today' : ''}"><span class="ledger-d-num">${d}</span><span class="ledger-d-day">${dayName}</span></th>`;
  }
  headHtml += `</tr>`;
  document.getElementById('ledger-head').innerHTML = headHtml;

  // 2. Filter logic
  let bodyHtml = '';
  // Combine names with departments to filter them
  const filteredList = names.map((name, idx) => {
    return { name, dept: depts[idx % depts.length], id: `EMP-10${idx + 100}` };
  }).filter(emp => {
    const matchesDept = (deptFilter === 'All' || emp.dept === deptFilter);
    const matchesName = nameInput === "" || emp.name.toLowerCase().includes(nameInput);
    return matchesDept && matchesName;
  });

  if (filteredList.length === 0) {
    bodyHtml = `<tr><td colspan="${daysInMonth + 1}" style="text-align:center; padding: 60px; color: var(--muted);">No matching records found.</td></tr>`;
  } else {
    // Show top 40 matches
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
                      <div class="status-pill ${isFuture ? 'future' : ''}" onclick="${isFuture ? '' : 'cycleStatus(this)'}">
                         ${isFuture ? '' : status}
                      </div>
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

// 1. Define the mandatory document list
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

// 2. Modified open function (Update your initVaultMatrix button to call this)
function openEmployeeVault(name, id) {
    goPage('employee-vault');
    document.getElementById('v-emp-name').textContent = name;
    document.getElementById('v-emp-id').textContent = id + " • Personnel Archive";
    
    const listContainer = document.getElementById('vault-docs-list');
    listContainer.innerHTML = '';

    let uploadCount = 0;

    VAULT_SCHEMA.forEach(doc => {
        const isUploaded = Math.random() > 0.4; // Mocking data
        if(isUploaded) uploadCount++;

        const row = document.createElement('div');
        row.className = 'doc-row';
        
        row.innerHTML = `
            <!-- COLUMN 1: ICON -->
            <div class="doc-icon-box ${isUploaded ? 'uploaded' : 'missing'}">
                <i data-lucide="${isUploaded ? 'file-check' : 'file-question-mark'}" size="18"></i>
            </div>

            <!-- COLUMN 2: TITLE & CATEGORY -->
            <div class="doc-meta">
                <div class="doc-name">${doc.name}</div>
                <div class="doc-cat">${doc.cat}</div>
            </div>

            <!-- COLUMN 3: STATUS BADGE -->
            <div class="doc-status">
                ${isUploaded ? 
                    '<span class="badge badge-success" style="font-size:10px">Verified</span>' : 
                    '<span class="badge badge-neutral" style="font-size:10px; opacity:0.7;">Pending</span>'}
            </div>

            <!-- COLUMN 4: ACTIONS -->
            <div class="doc-actions">
                ${isUploaded ? `
                    <button class="btn btn-secondary btn-xs" title="View" onclick="showNotification('Vault','Opening file...','info')">
                        <i data-lucide="eye" size="14"></i> View
                    </button>
                    <button class="btn btn-secondary btn-xs" title="Update" style="min-width:34px;">
                        <i data-lucide="refresh-cw" size="14"></i>
                    </button>
                ` : `
                    <button class="btn btn-primary btn-xs btn-upload-pro" onclick="showNotification('Vault','Ready for upload','info')">
                        <i data-lucide="plus" size="14"></i> Add Document
                    </button>
                `}
            </div>
        `;
        listContainer.appendChild(row);
    });

    // Update Stats in side-card
    const total = VAULT_SCHEMA.length;
    document.getElementById('v-count-upload').textContent = uploadCount;
    document.getElementById('v-count-missing').textContent = total - uploadCount;
    document.getElementById('v-compliance-percent').textContent = Math.round((uploadCount/total)*100) + "%";

    lcIcons(listContainer);
}
function updateEmploymentFields(type) {
  const container = document.getElementById('dynamic-employment-fields');
  if (!type) {
    container.style.display = 'none';
    return;
  }
  container.style.display = 'grid';
  let html = '';

  // Standard Probation options based on your company profile (90 days standard)
  const probationHtml = ` 
      <div class="form-group">
  <label>Probation Period *</label>
  <div class="as-combo-container">
    <input type="text" id="o-probation" class="form-ctrl master-req" 
           placeholder="Select Period..." value="60 Days (Standard)"
           onfocus="showAsDrop('as-drop-probation')" readonly>
    <div class="as-combo-results" id="as-drop-probation">
      <div class="as-res-item" onclick="selectAsItem('o-probation','as-drop-probation','90 Days')">90 Days</div>
      <div class="as-res-item" onclick="selectAsItem('o-probation','as-drop-probation','60 Days (Standard)')">60 Days (Standard)</div>
      <div class="as-res-item" onclick="selectAsItem('o-probation','as-drop-probation','45 Days')">45 Days</div>
      <div class="as-res-item" onclick="selectAsItem('o-probation','as-drop-probation','No Probation')">No Probation</div>
    </div>
  </div> 
    </div>
  `;

  
  switch (type) {
    case 'full-time':
      html = `
        <div class="form-group"><label>Hiring Date *</label>
             <input type="date" class="form-ctrl master-req" id="o-hire" onclick="this.showPicker()" style="cursor:pointer"></div>
        ${probationHtml}
        <div class="form-group"><label>Reporting To </label><input type="text" class="form-ctrl" id="o-reports" placeholder="Search Manager..."></div>
      `;
      break;

    case 'contract':
      html = `
        <div class="form-group"><label>Contract Start *</label>
             <input type="date" class="form-ctrl master-req" id="o-hire" onclick="this.showPicker()" style="cursor:pointer"></div>
        <div class="form-group"><label>Contract End *</label>
             <input type="date" class="form-ctrl master-req" id="o-end-date" onclick="this.showPicker()" style="cursor:pointer"></div>
        ${probationHtml}
        <div class="form-group" style="grid-column: span 3;"><label>Reporting To </label><input type="text" class="form-ctrl" id="o-reports" placeholder="Search Manager..."></div>
      `;
      break;

    case 'part-time':
      html = `
        <div class="form-group"><label>Hiring Date *</label>
             <input type="date" class="form-ctrl master-req" id="o-hire" onclick="this.showPicker()" style="cursor:pointer"></div>
        <div class="form-group"><label>Hours Per Week *</label><input type="number" class="form-ctrl master-req" id="o-hours" placeholder="e.g. 20"></div>
        ${probationHtml}
        <div class="form-group" style="grid-column: span 3;"><label>Reporting To</label><input type="text" class="form-ctrl" id="o-reports" placeholder="Search Manager..."></div>
      `;
      break;

    case 'internship':
      html = `
        <div class="form-group"><label>Internship Start *</label>
             <input type="date" class="form-ctrl master-req" id="o-hire" onclick="this.showPicker()" style="cursor:pointer"></div>
        <div class="form-group"><label>Internship End *</label>
             <input type="date" class="form-ctrl master-req" id="o-end-date" onclick="this.showPicker()" style="cursor:pointer"></div>
        <div class="form-group"><label>Assigned Mentor</label><input type="text" class="form-ctrl" id="o-reports" placeholder="Full name of Mentor"></div>
      `;
      break;

    case 'temporary':
      html = `
        <div class="form-group"><label>Project Name *</label><input type="text" class="form-ctrl master-req" id="o-project" placeholder="e.g. Infrastructure Audit"></div>
        <div class="form-group"><label>Assignment Start *</label>
             <input type="date" class="form-ctrl master-req" id="o-hire" onclick="this.showPicker()" style="cursor:pointer"></div>
        <div class="form-group"><label>Project Supervisor </label><input type="text" class="form-ctrl" id="o-reports" placeholder="Search Supervisor..."></div>
      `;
      break;
  }

  container.innerHTML = html;
  
  // Re-initialize icons for the new elements
  lcIcons(container);

  // Attach validation listeners to the newly created inputs
  container.querySelectorAll('input, select').forEach(input => {
    input.addEventListener('input', validateMasterRecord);
  });
  
  // Refresh the global validation state
  validateMasterRecord();
}
function showNotification(title, message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `ydy-toast toast-${type}`;
    
    const icons = {
        success: 'check-circle',
        error: 'alert-circle',
        warning: 'alert-triangle',
        info: 'info'
    };

    toast.innerHTML = `
        <div class="toast-icon" ><i data-lucide="${icons[type]}" size="18"></i></div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-msg">${message}</div>
        </div>
        <div class="toast-progress"><div class="toast-progress-fill" id="progress-fill"></div></div>
    `;

    container.appendChild(toast);
    lucide.createIcons({ nodes: [toast] });

    // Animate progress bar
    const fill = toast.querySelector('.toast-progress-fill');
    fill.style.transition = 'transform 4s linear';
    fill.style.transform = 'scaleX(0)';

    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('exit');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
function handleLogout() { 
  openConfirm("Logout Session", "Are you sure you want to Logout now?"); 
  document.getElementById('confirm-btn-yes').onclick = function() {
      // Proceed with logout
      showNotification("Security", "Ending secure session. Redirecting...", "danger");
      setTimeout(() => {
          window.location.href = 'login/logout.php'; 
      }, 1500);
      
      closeConfirm(); // Close after finishing
  };
}
function openConfirm(title, message) {
  document.getElementById('confirm-title').innerText = title;
  document.getElementById('confirm-body').innerText = message;
  document.getElementById('confirm-modal').classList.add('open');
}

function closeConfirm() {
  document.getElementById('confirm-modal').classList.remove('open');
}
  function previewAvatar(input) {
    if (input.files && input.files[0]) {
      const file = input.files[0];
      const maxSize = 5 * 1024 * 1024; // 5MB in bytes

      // 1. Validation: Size Check
      if (file.size > maxSize) {
        showNotification("File Too Large", "The image exceeds the 5MB limit. Please upload a smaller photo.", "error");
        input.value = ""; // Reset the input
        return;
      }

      // 2. Validation: Type Check
      if (!file.type.startsWith('image/')) {
        showNotification("Invalid File", "Please select a valid image file (JPG, PNG, WebP).", "error");
        input.value = "";
        return;
      }

      // 3. Success: Process Preview
      const reader = new FileReader();
      reader.onload = e => {
        const preview = document.getElementById('avatar-img-output');
        const icon = document.getElementById('placeholder-icon');
        const box = document.getElementById('avatar-preview-box');
        
        preview.src = e.target.result;
        preview.style.display = 'block';
        icon.style.display = 'none';
        
        // Visual feedback that the image is accepted
        box.style.borderStyle = 'solid';
        box.style.borderColor = 'var(--primary)';
      };
      reader.readAsDataURL(file);
    }
  }

// Cache for dropdown data to avoid repeated calls
// Cache for dropdown data
const dropdownCache = {};

async function populateAsDrop(dropdownId, type, searchTerm = '') {
    const dropContainer = document.getElementById(dropdownId);
    if (!dropContainer) return;

    dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">Loading...</div>';
    dropContainer.classList.add('active');

    const cacheKey = `${type}:${searchTerm}`;
    if (dropdownCache[cacheKey]) {
        renderDropdownItems(dropContainer, dropdownCache[cacheKey]);
        return;
    }

    try {
        const url = `api/1common/fetch_dropdown.php?type=${encodeURIComponent(type)}&search=${encodeURIComponent(searchTerm)}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            dropdownCache[cacheKey] = result.data;
            renderDropdownItems(dropContainer, result.data);
        } else {
            dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--muted);">No results found</div>';
        }
    } catch (err) {
        console.error('Dropdown error:', err);
        dropContainer.innerHTML = '<div class="as-res-item" style="color:var(--danger);">Error loading data</div>';
    }
}
function toggleStaticDrop(dropId) {
    const drop = document.getElementById(dropId);
    if (!drop) return;
    // Close any other open dropdowns
    document.querySelectorAll('.as-combo-results').forEach(d => {
        if (d.id !== dropId) d.classList.remove('active');
    });
    drop.classList.toggle('active');
}
function renderDropdownItems(container, items) {
    const inputId = container.id.replace('as-drop-', 'as-input-');
    container.innerHTML = '';
    items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'as-res-item';
        div.textContent = item.label;
        div.setAttribute('data-value', item.value);
        div.setAttribute('onclick', `selectAsItemWithValue('${inputId}', '${container.id}', '${item.label.replace(/'/g, "\\'")}', '${item.value}')`);
        container.appendChild(div);
    });
}

function selectAsItemWithValue(inputId, dropId, displayText, value) {
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
        inputEl.parentNode.appendChild(hiddenEl);
    }
    hiddenEl.value = value;
    
    const dropContainer = document.getElementById(dropId);
    if (dropContainer) dropContainer.classList.remove('active');
}

function showAsDrop(dropdownId) {
    const dropContainer = document.getElementById(dropdownId);
    const inputId = dropdownId.replace('as-drop-', 'as-input-');
    const inputEl = document.getElementById(inputId);
    
    let type = inputEl?.getAttribute('data-dropdown-type');
    if (!type) {
        if (dropdownId.includes('dept')) type = 'departments';
        else if (dropdownId.includes('branch')) type = 'branches';
        else if (dropdownId.includes('pos') || dropdownId.includes('job')) type = 'job_positions';
        else if (dropdownId.includes('emp') || dropdownId.includes('custodian') || dropdownId.includes('manager')) type = 'employees';
        else if (dropdownId.includes('employment')) type = 'employment_types';
        else type = 'employees';
    }
    
    const searchTerm = inputEl?.value || '';
    populateAsDrop(dropdownId, type, searchTerm);
}

function filterAsDrop(inputId, dropId) {
    const searchTerm = document.getElementById(inputId).value.toLowerCase();
    const container = document.getElementById(dropId);
    if (!container) return;
    
    const items = container.querySelectorAll('.as-res-item');
    let hasVisible = false;
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
            hasVisible = true;
        } else {
            item.style.display = 'none';
        }
    });
    
    if (!hasVisible && container.innerHTML.trim() !== '') {
        const noResult = container.querySelector('.no-result-msg');
        if (!noResult) {
            const msg = document.createElement('div');
            msg.className = 'as-res-item no-result-msg';
            msg.textContent = 'No matching results';
            msg.style.color = 'var(--muted)';
            container.appendChild(msg);
        }
    } else {
        const noResult = container.querySelector('.no-result-msg');
        if (noResult) noResult.remove();
    }
    
    container.classList.add('active');
}