<div class="page-header">
    <div>
        <h1 class="page-title">Leave Calendar</h1>
        <p class="page-sub">Visual team leave overview & schedule monitoring</p>
    </div>
</div>

<!-- Main Card Container -->
<div class="card" id="calendar-main-card" style="border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow); background: #fff; min-height: 580px; display: flex; align-items: center; justify-content: center;">
    
    <!-- LAYER 1: Leave Intelligence Splash (Visible by default) -->
    <div id="cal-splash-screen" style="text-align: center; padding: 60px 40px; animation: modalIn 0.4s ease;">
        <div class="att-empty-icon-stack" style="margin-bottom: 30px;">
            <div class="icon-ring pulse"></div>
            <div class="icon-ring delay-1"></div>
            <div class="icon-main" style="background: #15b201;">
                <i data-lucide="calendar-days" size="32"></i>
            </div>
        </div>

        <h2 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Leave records</h2>
        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.6; margin-bottom: 32px; max-width: 420px; margin-left: auto; margin-right: auto;">
            The leave registry is synchronized. Click below to generate the visual overview for the selected month.
        </p>

        <button class="btn btn-primary" onclick="generateVisualCalendar()" style="background: #15b201; border: none; padding: 14px 32px; font-size: 0.9rem; font-weight: 700; border-radius: 14px; box-shadow: 0 10px 20px rgba(21, 178, 1, 0.2);">
            <i data-lucide="calendar-days" size="18" style="margin-right:10px"></i> View Calendar
        </button>
    </div>

    <!-- LAYER 2: Real Calendar Content (Hidden by default) -->
    <div id="cal-real-content" style="display: none; width: 100%; height: 100%;">
        <div style="display: flex; min-height: 580px; width: 100%;">
            
            <!-- LEFT: Grid Panel -->
            <div style="flex: 1; padding: 24px; border-right: 1px solid #f1f5f9;">
                <div class="flex-row" style="justify-content: space-between; margin-bottom: 24px;">
                    <h2 id="cal-month-display" style="font-size: 1.15rem; font-weight: 800; color: #1e293b;">--</h2>
                    <div class="flex-row" style="background: #f8fafc; padding: 4px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <button class="icon-btn" style="border:none; background:transparent;" onclick="changeMonth(-1)"><i data-lucide="chevron-left" size="18"></i></button>
                        <button class="btn btn-secondary btn-sm" onclick="resetToToday()" style="border:none; background:#fff; font-weight: 800; height: 32px; padding: 0 16px; box-shadow: var(--shadow); border-radius: 8px;">Today</button>
                        <button class="icon-btn" style="border:none; background:transparent;" onclick="changeMonth(1)"><i data-lucide="chevron-right" size="18"></i></button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; margin-bottom: 12px;">
                    <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d) echo "<span style='font-size:0.65rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.12em;'>$d</span>"; ?>
                </div>

                <div id="calendar-body" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; flex: 1;"></div>

                <div class="flex-row mt-4" style="gap: 24px; padding-top: 16px; border-top: 1px solid #f8fafc;">
                    <div class="flex-row" style="gap:8px;"><div style="width:10px; height:10px; border-radius:3px; border:1.5px solid #3b82f6;"></div><span style="font-size:0.72rem; color:#64748b; font-weight:700;">Current Day</span></div>
                    <div class="flex-row" style="gap:8px;"><div style="width:10px; height:10px; border-radius:3px; background:#f1fcf0; border:1px solid #dcfce7;"></div><span style="font-size:0.72rem; color:#64748b; font-weight:700;">Staff on Leave</span></div>
                </div>
            </div>

            <!-- RIGHT: Sidebar Panel -->
            <div style="width: 340px; background: #fcfdfe; padding: 24px; display: flex; flex-direction: column;">
                <div style="margin-bottom: 24px;">
                    <div id="detail-date-label" style="font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em;">Loading...</div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-top: 4px;">Employees on leave</h3>
                </div>
                <div id="detail-list-area" style="flex: 1; overflow-y: auto;"></div>
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                    <button class="btn btn-primary" style="width:100%; justify-content:center; height:44px; border-radius:12px; background:#15b201; font-weight:700;" onclick="goPage('leave-requests')">View All Requests</button>
                </div>
            </div>
        </div>
    </div>
</div>