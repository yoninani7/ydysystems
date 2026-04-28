<div class="page-header">
    <div>
        <h1 class="page-title">Job Applicant's List</h1>
        <p class="page-sub">Track applicants through hiring stages</p>
    </div>
    <div class="flex-row">
        <div class="as-combo-container" style="width: 180px;">
            <input type="text" class="sel" value="All Stages" readonly style="background: #fff; width: 100%;" onclick="toggleStaticDrop('as-drop-app-stage')">
            <div class="as-combo-results" id="as-drop-app-stage">
                <div class="as-res-item selected">All Stages</div>
                <div class="as-res-item">Applied</div>
                <div class="as-res-item">Screening</div>
                <div class="as-res-item">Assessment</div>
                <div class="as-res-item">Interview</div>
                <div class="as-res-item">Offer</div>
            </div>
        </div>
        <button class="btn btn-primary" style="background: #15b201; padding: 10px 22px; border-radius: 10px;" onclick="openModal('modal-add-candidate')">
            <i data-lucide="plus" size="18"></i> Add Candidate
        </button>
    </div>
</div>

<!-- Statistics Grid -->
<div class="stats-row stats-4">
    <div class="stat-card art-card">
        <div class="art-label">Total Applicants</div>
        <div class="art-main-row">
            <div class="art-value">18</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f1fcf0; padding: 8px; border-radius: 10px; color: #15b201;">
                    <i data-lucide="users" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-units" style="--accent-color: #0ea5e9;">
        <div class="art-label">In Progress</div>
        <div class="art-main-row">
            <div class="art-value">13</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="color: #0ea5e9; font-weight: 700; border-bottom: 2px solid #0ea5e9; padding-bottom: 2px;">Active Pipeline</div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-headcount">
        <div class="art-label">Hired</div>
        <div class="art-main-row">
            <div class="art-value">3</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #f1fcf0; padding: 8px; border-radius: 10px; color: #15b201;">
                    <i data-lucide="user-check" size="20"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card art-card art-expired">
        <div class="art-label">Rejected</div>
        <div class="art-main-row">
            <div class="art-value">2</div>
            <div class="art-meta-group">
                <div class="art-indicator" style="background: #fee2e2; padding: 8px; border-radius: 10px; color: #ef4444;">
                    <i data-lucide="user-x" size="20"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: var(--shadow); margin-top: 20px;">
    <div class="card-header" style="background: #fff; padding: 18px 24px; border-bottom: 1px solid #f1f5f9;">
        <span class="card-title" style="font-size: 1rem;">All Candidates</span>
        <div class="search-inner" style="width: 320px; background: #f8fafc;">
            <i data-lucide="search" size="16"></i>
            <input type="text" placeholder="Search name, position..." oninput="filterApplicantTable(this.value)">
        </div>
    </div>
    <div id="applicant-table-target"></div>
</div>

<script>
    setTimeout(() => {
        if(typeof renderApplicantList === 'function') renderApplicantList();
    }, 50);
</script>