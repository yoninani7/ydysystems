<div class="page-header">
    <div>
        <h1 class="page-title">Interview Tracker</h1>
        <p class="page-sub">Schedule and track interview outcomes</p>
    </div>
    <div class="flex-row">
        <div class="as-combo-container" style="width: 160px;">
            <input type="text" class="sel" value="All Results" readonly style="background: #fff; width: 100%;" onclick="toggleStaticDrop('as-drop-int-results')">
            <div class="as-combo-results" id="as-drop-int-results">
                <div class="as-res-item selected">All Results</div>
                <div class="as-res-item">Passed</div>
                <div class="as-res-item">Failed</div>
                <div class="as-res-item">Pending</div>
            </div>
        </div>
        <button class="btn btn-primary" style="background: #15b201; padding: 10px 22px; border-radius: 10px;" onclick="openModal('modal-schedule-interview')">
            <i data-lucide="calendar-plus" size="18"></i> Schedule Interview
        </button>
    </div>
</div>

<!-- Statistics Row -->
<div class="stats-row stats-4">
    <div class="stat-card" style="padding: 24px;">
        <div class="art-label" style="margin-bottom: 12px;">Total Interviews</div>
        <div class="art-value" style="font-size: 2.2rem;">12</div>
    </div>
    <div class="stat-card" style="padding: 24px;">
        <div class="art-label" style="margin-bottom: 12px;">Pending</div>
        <div class="art-value" style="font-size: 2.2rem;">6</div>
    </div>
    <div class="stat-card" style="padding: 24px;">
        <div class="art-label" style="margin-bottom: 12px;">Passed</div>
        <div class="art-value" style="font-size: 2.2rem;">4</div>
    </div>
    <div class="stat-card" style="padding: 24px;">
        <div class="art-label" style="margin-bottom: 12px;">Failed / No-Show</div>
        <div class="art-value" style="font-size: 2.2rem;">2</div>
    </div>
</div>

<!-- Table Card -->
<div class="card" style="border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: var(--shadow); margin-top: 20px;">
    <div class="card-header" style="background: #fff; padding: 18px 24px; border-bottom: 1px solid #f1f5f9;">
        <span class="card-title" style="font-size: 1rem;">Interview Schedule</span>
        <div class="search-inner" style="width: 320px; background: #f8fafc;">
            <i data-lucide="search" size="16"></i>
            <input type="text" placeholder="Search candidate, position..." oninput="filterInterviewTable(this.value)">
        </div>
    </div>
    <div id="interview-table-target"></div>
</div>

<script>
    setTimeout(() => {
        if(typeof renderInterviewTracker === 'function') renderInterviewTracker();
    }, 50);
</script>