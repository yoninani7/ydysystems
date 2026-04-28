<div class="page-header">
    <div>
        <h1 class="page-title">Job Vacancies</h1>
        <p class="page-sub">Manage open positions across all departments</p>
    </div>
    <div class="flex-row">
        <div class="as-combo-container" style="width: 180px;">
            <input type="text" class="sel" value="All Statuses" readonly style="background: #fff; width: 100%;" onclick="toggleStaticDrop('as-drop-vac-status')">
            <div class="as-combo-results" id="as-drop-vac-status">
                <div class="as-res-item selected">All Statuses</div>
                <div class="as-res-item">Open</div>
                <div class="as-res-item">On Hold</div>
                <div class="as-res-item">Filled</div>
            </div>
        </div>
        <button class="btn btn-primary" style="background: #15b201; padding: 10px 22px; border-radius: 10px;" onclick="openModal('modal-post-vacancy')">
            <i data-lucide="plus" size="18"></i> Post Vacancy
        </button>
    </div>
</div>

<!-- Statistics Grid -->
<div class="stats-row stats-4">
    <div class="stat-card art-card">
        <div class="art-label">Total Vacancies</div>
        <div class="art-main-row">
            <div class="art-value">12</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="color: #10b981;">
                    <i data-lucide="trending-up" size="14"></i> 3
                </div>
                <div class="art-subtext">Vs Last Month</div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-headcount">
        <div class="art-label">Open</div>
        <div class="art-main-row">
            <div class="art-value">7</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="color: #10b981;">Active</div>
                <div class="art-subtext">Accepting Apps</div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-leave">
        <div class="art-label">On Hold</div>
        <div class="art-main-row">
            <div class="art-value">2</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="color: #f59e0b;">Paused</div>
                <div class="art-subtext">Pending Review</div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-units" style="--accent-color: #06b6d4;">
        <div class="art-label">Filled</div>
        <div class="art-main-row">
            <div class="art-value">2</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="color: #06b6d4;">Closed</div>
                <div class="art-subtext">This Cycle</div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: var(--shadow);">
    <div class="card-header" style="background: #fff; padding: 18px 24px;">
        <span class="card-title" style="font-size: 1rem;">All Vacancies</span>
        <div class="search-inner" style="width: 320px; background: #f8fafc;">
            <i data-lucide="search" size="16"></i>
            <input type="text" id="vac-search-input" placeholder="Search title, dept..." oninput="filterVacanciesTable(this.value)">
        </div>
    </div>
    <div id="vacancies-table-target"></div>
</div>

<script>
    setTimeout(() => {
        if(typeof renderJobVacancies === 'function') renderJobVacancies();
    }, 50);
</script>