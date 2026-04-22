<!-- PROBATION TRACKER -->
<div class="page" id="p-probation-tracker">
  <div class="page-header"><div><div class="page-title">Probation Tracker</div><div class="page-sub">Monitor and confirm probationary employees</div></div><button class="btn btn-primary"><i data-lucide="check-circle" size="13"></i>Confirm Selected</button></div>
  <div id="tbl-probation"></div>
</div>

<!-- Evaluation Modal -->
<div class="modal-overlay" id="modal-probation-eval" onclick="closeModal('modal-probation-eval', event)">
  <div class="modal-box" style="max-width: 500px;">
    <div class="modal-header">
      <div>
        <div class="modal-title" id="eval-modal-title" style="font-size:1.1rem; font-weight:800;">Evaluate Employee</div>
        <div style="font-size:.75rem; color:var(--muted); margin-top:3px;">Submit your evaluation decision for this probation period</div>
      </div>
      <button class="icon-btn" onclick="closeModal('modal-probation-eval')"><i data-lucide="x" size="18"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="eval-emp-id">
      <input type="hidden" id="probation_eval_csrf_token" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="form-group">
        <label style="font-weight: 700; font-size: 0.8rem; margin-bottom: 8px; display: block;">Evaluation Notes / Feedback</label>
        <textarea id="eval-notes" class="form-ctrl" rows="4" placeholder="Enter evaluation notes here..." style="width: 100%; border-radius: 8px; padding: 12px; font-size: 0.85rem; resize:none;" ></textarea>
      </div>
      
      <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
        <div style="font-size: 0.75rem; font-weight: 800; color: var(--muted); margin-bottom: 12px; letter-spacing: 0.05em; text-transform: uppercase;">Final Decision</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
          <button class="btn" style="background: #ecfdf5; color: #059669; border: 1px solid #10b98120; padding: 15px 10px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; transition: all 0.2s;" onclick="submitProbationEval('Hire')">
            <div style="background: #10b981; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="user-check" size="16"></i>
            </div>
            <span style="font-size: 0.75rem; font-weight: 700;">Confirm Hire</span>
          </button>
          <button class="btn" style="background: #fffbeb; color: #d97706; border: 1px solid #f59e0b20; padding: 15px 10px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; transition: all 0.2s;" onclick="submitProbationEval('Extend')">
            <div style="background: #f59e0b; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="clock" size="16"></i>
            </div>
            <span style="font-size: 0.75rem; font-weight: 700;">Extend</span>
          </button>
          <button class="btn" style="background: #fef2f2; color: #dc2626; border: 1px solid #ef444420; padding: 15px 10px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; transition: all 0.2s;" onclick="submitProbationEval('Reject')">
            <div style="background: #ef4444; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="user-minus" size="16"></i>
            </div>
            <span style="font-size: 0.75rem; font-weight: 700;">Terminate</span>
          </button>
        </div>
      </div>
    </div>
    <div class="modal-footer" style="background: #f8fafc; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
      <button class="btn btn-secondary" onclick="closeModal('modal-probation-eval')" style="font-size: 0.8rem; padding: 8px 20px;">Cancel</button>
    </div>
  </div>
</div>
