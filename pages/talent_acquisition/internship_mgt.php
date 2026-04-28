<div class="page-header">
    <div>
        <h1 class="page-title">Internship Management</h1>
        <p class="page-sub">Academic partnerships and student trainees</p>
    </div>
    <div class="flex-row">
        <button class="btn btn-secondary" style="background: #fff; border-radius: 10px; padding: 10px 20px;">
            <i data-lucide="star" size="18"></i> Potential Hires
        </button>
        <button class="btn btn-primary" style="background: #15b201; padding: 10px 22px; border-radius: 10px;" onclick="openModal('modal-add-intern')">
            <i data-lucide="plus" size="18"></i> Add Intern
        </button>
    </div>
</div>

<!-- Statistics Row -->
<div class="stats-row stats-4">
    <div class="stat-card" style="padding: 20px;">
        <div class="art-label" style="font-size: 10px;">Active Interns</div>
        <div class="art-value" style="font-size: 2rem; margin-top: 8px;">6</div>
    </div>
    <div class="stat-card" style="padding: 20px;">
        <div class="art-label" style="font-size: 10px;">Evaluations Due</div>
        <div class="art-value" style="font-size: 2rem; margin-top: 8px; color: #f59e0b;">3</div>
    </div>
    <div class="stat-card" style="padding: 20px;">
        <div class="art-label" style="font-size: 10px;">Completed</div>
        <div class="art-value" style="font-size: 2rem; margin-top: 8px; color: #15b201;">2</div>
    </div>
    <div class="stat-card" style="padding: 20px;">
        <div class="art-label" style="font-size: 10px;">Avg. Eval Score</div>
        <div class="art-value" style="font-size: 2rem; margin-top: 8px; color: #0ea5e9;">83%</div>
    </div>
</div>

<!-- Table Card -->
<div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: var(--shadow); margin-top: 20px;">
    <div class="card-header" style="background: #fff; padding: 18px 24px; border-bottom: 1px solid #f1f5f9;">
        <span class="card-title" style="font-size: 1rem;">Interns List</span>
        <div class="flex-row" style="gap: 12px;">
            <div class="as-combo-container" style="width: 140px;">
                <input type="text" class="sel" value="All Status" readonly style="background: #f8fafc; width: 100%;" onclick="toggleStaticDrop('as-drop-intern-status')">
                <div class="as-combo-results" id="as-drop-intern-status">
                    <div class="as-res-item">All Status</div>
                    <div class="as-res-item">Active</div>
                    <div class="as-res-item">Completed</div>
                </div>
            </div>
            <div class="search-inner" style="width: 280px; background: #f8fafc;">
                <i data-lucide="search" size="16"></i>
                <input type="text" placeholder="Search name, school..." oninput="filterInternTable(this.value)">
            </div>
        </div>
    </div>
    <div id="internship-table-target"></div>
</div>

<script>
    setTimeout(() => {
        if(typeof renderInternshipManagement === 'function') renderInternshipManagement();
    }, 50);
</script>