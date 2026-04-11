<!-- ADD NEW EMPLOYEE -->
<div class="page" id="p-add-employee">
    <div class="page-header" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:16px;">
            <button class="sidebar-toggle" onclick="goPage('employee-directory')">
                <i data-lucide="arrow-left" size="16"></i>
            </button>
            <div>
                <div class="page-title" style="font-size:1.4rem;letter-spacing:-0.02em;">
                    Onboard Personnel <span style="color:var(--muted);font-weight:400;">/ Master Data</span>
                </div>
                <p class="page-sub">Establish formal organizational records and legal compliance identities.</p>
            </div>
        </div>
        <div class="flex-row">
            <button class="btn btn-secondary" onclick="goPage('employee-directory')">Discard Draft</button>
            <button class="btn btn-primary" id="btn-save-master" style="padding:10px 28px;opacity:0.4;cursor:not-allowed;" disabled onclick="saveNewEmployee()">
                <i data-lucide="shield-check" size="16"></i> Commit Record
            </button>
        </div>
        </div>

        <div class="onboard-grid">
            <aside class="onboard-sidebar">
            <div style="margin-bottom:32px;">
                <div style="font-size:.6rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;">
                    Master Record Progress
                </div>
                <div style="height:4px;background:#e2e8f0;border-radius:10px;overflow:hidden;">
                    <div id="master-progress-line" style="width:20%;height:100%;background:var(--primary);transition:width .4s ease;"></div>
                </div>
            </div>
            <nav id="onboard-nav-list">
                <div class="step-pro active" data-step="1" onclick="jumpToStep(1)">
                    <div class="step-idx">1</div><span>Identity</span>
                </div>
                <div class="step-pro" data-step="2" onclick="jumpToStep(2)">
                    <div class="step-idx">2</div><span>Contact</span>
                </div>
                <div class="step-pro" data-step="3" onclick="jumpToStep(3)">
                    <div class="step-idx">3</div><span>Employment</span>
                </div>
                <div class="step-pro" data-step="4" onclick="jumpToStep(4)">
                    <div class="step-idx">4</div><span>Finance</span>
                </div>
                <div class="step-pro" data-step="5" onclick="jumpToStep(5)">
                    <div class="step-idx">5</div><span>Compliance</span>
                </div>
                <div class="step-pro" data-step="6" onclick="jumpToStep(6)">
                    <div class="step-idx">6</div><span>Review</span>
                </div>
                <div style="margin-top:auto;padding:20px;background:#fff;border:1px solid var(--border);border-radius:16px;">
                    <div style="font-size:.75rem;font-weight:800;color:var(--text);">Integrity Check</div>
                    <div id="master-val-text" style="font-size:.65rem;color:var(--danger);margin-top:6px;font-weight:600;">
                        * Missing Required Fields
                    </div>
                </div>
            </nav>
        </aside>

        <main class="onboard-content">
            <section id="ob-step-1" class="form-section-content active">
                <div class="form-header-pro">
                    <div class="input-group-label">Personal Identity</div>
                    <p class="input-group-sub">Establish the primary legal identity for the master record.</p>
                </div>
                <div class="identity-master-wrapper">
                    <div class="avatar-upload-zone">
                        <!-- We put the label around everything so the whole area is clickable -->
                        <label for="avatar-upload" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                            <div class="avatar-frame" id="avatar-preview-box">
                                <i data-lucide="user" size="40" style="color:#cbd5e1;" id="placeholder-icon"></i>
                                <img id="avatar-img-output" src="" style="width:100%;height:100%;object-fit:cover;display:none;">
                            </div>
                            <div class="btn btn-secondary btn-xs" style="border-radius:20px; padding:5px 15px;">
                                <i data-lucide="camera" size="12"></i> Upload Photo
                            </div>
                        </label>
                        <!-- This is the hidden actual file picker -->
                        <input type="file" id="avatar-upload" hidden accept="image/*" onchange="previewAvatar(this)">
                    </div>

                    <div class="identity-fields-container">
                        <div class="name-grid-row">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" class="form-ctrl master-req" id="o-fname" placeholder="Ex: Abebe">
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" class="form-ctrl" id="o-mname" placeholder="Ex: Bikila">
                            </div>
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" class="form-ctrl master-req" id="o-lname" placeholder="Ex: Gebre">
                            </div>
                        </div>
                        <div class="identity-divider"></div>
                        <div class="demo-grid-row">
                            <div class="form-group">
                                <label>Date of Birth *</label>
                                <input type="date" class="form-ctrl master-req" id="o-dob">
                            </div>
                            <div class="form-group">
                                <label>Gender *</label>
                                <div class="as-combo-container">
                                    <input type="text" id="o-gender" class="form-ctrl master-req" placeholder="Select Gender..." onfocus="showAsDrop('as-drop-gender')" readonly>
                                    <div class="as-combo-results" id="as-drop-gender">
                                        <div class="as-res-item" onclick="selectAsItem('o-gender','as-drop-gender','Male')">Male</div>
                                        <div class="as-res-item" onclick="selectAsItem('o-gender','as-drop-gender','Female')">Female</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nationality *</label>
                                <input type="text" class="form-ctrl master-req" id="o-nat" value="Ethiopian" disabled>
                            </div>
                        </div>
                        <div class="demo-grid-row">
                            <div class="form-group">
                                <label>Marital Status</label>
                                <div class="as-combo-container">
                                    <input type="text" id="o-marital" class="form-ctrl" placeholder="Select Marital Status..." onfocus="showAsDrop('as-drop-marital')" readonly>
                                    <div class="as-combo-results" id="as-drop-marital">
                                        <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Single')">Single</div>
                                        <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Married')">Married</div>
                                        <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Divorced')">Divorced</div>
                                        <div class="as-res-item" onclick="selectAsItem('o-marital','as-drop-marital','Widowed')">Widowed</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="grid-column:span 2;">
                                <label>Place of Birth (Region)</label>
                                <input type="text" class="form-ctrl" id="o-pob" placeholder="Enter region">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="ob-step-2" class="form-section-content">
                <div class="input-group-label">Contact Channels</div>
                <p class="input-group-sub">Employee reachability and residential records.</p>
                <div class="form-grid fg-2">
                    <div class="form-group">
                        <label>Personal Phone *</label>
                        <input type="tel" class="form-ctrl master-req" id="o-phone">
                    </div>
                    <div class="form-group">
                        <label>Personal Email *</label>
                        <input type="email" class="form-ctrl master-req" id="o-email">
                    </div>
                    <div class="form-group" style="grid-column:span 2;">
                        <label>Permanent Address *</label>
                        <textarea class="form-ctrl master-req" id="o-addr" style="min-height:80px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" class="form-ctrl master-req" id="o-city" value="Addis Ababa">
                    </div>
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" class="form-ctrl" id="o-zip" value="1000">
                    </div>
                </div>
            </section>

            <!-- EMPLOYMENT PLACEMENT (DYNAMIC VERSION) -->
            <section id="ob-step-3" class="form-section-content">
                <div class="form-header-pro">
                    <div class="input-group-label">Employment Placement</div>
                    <p class="input-group-sub">Mapping the position within the company structure based on employment type.</p>
                </div>

                <div class="form-grid fg-3">
                    <div class="form-group">
                        <label>Department *</label>
                        <div class="as-combo-container">
                            <input type="text" id="o-dept" class="form-ctrl master-req" placeholder="Select Department..." onfocus="showAsDrop('as-drop-dept')" readonly>
                            <div class="as-combo-results" id="as-drop-dept">
                                <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Engineering')">Engineering</div>
                                <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Finance')">Finance</div>
                                <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','HR')">HR</div>
                                <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Sales')">Sales</div>
                                <div class="as-res-item" onclick="selectAsItem('o-dept','as-drop-dept','Marketing')">Marketing</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Job Position *</label>
                        <div class="as-combo-container">
                            <input type="text" id="o-pos" class="form-ctrl master-req" placeholder="Select Position..." onfocus="showAsDrop('as-drop-pos')" readonly>
                            <div class="as-combo-results" id="as-drop-pos">
                                <div class="as-res-item" onclick="selectAsItem('o-pos','as-drop-pos','Senior Dev')">Senior Dev</div>
                                <div class="as-res-item" onclick="selectAsItem('o-pos','as-drop-pos','Analyst')">Analyst</div>
                                <div class="as-res-item" onclick="selectAsItem('o-pos','as-drop-pos','Marketing Specialist')">Marketing Specialist</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Employment Type *</label>
                        <div class="as-combo-container">
                            <input type="text" id="o-etype" class="form-ctrl master-req" placeholder="Select Employment Type..." onfocus="showAsDrop('as-drop-etype')" readonly>
                            <div class="as-combo-results" id="as-drop-etype">
                                <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Permanent / Full-Time'); updateEmploymentFields('full-time')">Permanent / Full-Time</div>
                                <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Fixed-Term Contract'); updateEmploymentFields('contract')">Fixed-Term Contract</div>
                                <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Part-Time'); updateEmploymentFields('part-time')">Part-Time</div>
                                <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Internship'); updateEmploymentFields('internship')">Internship</div>
                                <div class="as-res-item" onclick="selectAsItem('o-etype','as-drop-etype','Temporary / Casual'); updateEmploymentFields('temporary')">Temporary / Casual</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Container -->
                <div id="dynamic-employment-fields" class="form-grid fg-3 mt-4" style="border-top: 1px dashed var(--border); padding-top: 20px; display: none;">
                    <!-- Content injected via JS -->
                </div>
            </section>

            <section id="ob-step-4" class="form-section-content">
                <div class="input-group-label">Financial & Treasury</div>
                <p class="input-group-sub">Payroll disbursement and statutory tax records.</p>
                <div class="form-grid fg-2">
                    <div class="form-group">
                        <label>Gross Salary (ETB) *</label>
                        <input type="number" class="form-ctrl master-req" id="o-sal">
                    </div>
                    <div class="form-group">
                        <label>Tax ID (TIN) *</label>
                        <input type="text" class="form-ctrl master-req" id="o-tin">
                    </div>
                    <div class="form-group">
                        <label>Bank Name *</label>
                        <div class="as-combo-container">
                            <input type="text" id="o-bank" class="form-ctrl master-req" placeholder="Select Bank..." onfocus="showAsDrop('as-drop-bank')" readonly>
                            <div class="as-combo-results" id="as-drop-bank">
                                <div class="as-res-item" onclick="selectAsItem('o-bank','as-drop-bank','CBE')">CBE</div>
                                <div class="as-res-item" onclick="selectAsItem('o-bank','as-drop-bank','Awash')">Awash</div>
                                <div class="as-res-item" onclick="selectAsItem('o-bank','as-drop-bank','Abyssinia')">Abyssinia</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Account Number *</label>
                        <input type="text" class="form-ctrl master-req" id="o-acc">
                    </div>
                </div>
            </section>

            <section id="ob-step-5" class="form-section-content">
                <div class="input-group-label">Compliance & Legal</div>
                <p class="input-group-sub">Final verification and emergency contact data.</p>
                <div class="form-grid fg-2">
                    <div class="form-group">
                        <label>Emergency Contact *</label>
                        <input type="text" class="form-ctrl master-req" id="o-ename">
                    </div>
                    <div class="form-group">
                        <label>Emergency Phone *</label>
                        <input type="tel" class="form-ctrl master-req" id="o-ephone">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Emergency Relation to employee *</label>
                        <input type="text" class="form-ctrl master-req" id="o-idno">
                    </div>
                </div>
            </section>

<section id="ob-step-6" class="form-section-content">
    <div class="form-header-pro">
        <div class="input-group-label">Final Record Verification</div>
        <p class="input-group-sub">Audit the legal identity and placement details before committing to the organization database.</p>
    </div>

    <div id="summary-render-area" class="review-container">
        <!-- TOP IDENTITY BAR -->
        <div class="review-identity-card">
            <img id="rev-img" src="placeholder-user.png" class="review-avatar-preview" alt="Employee Photo">
            <div>
                <h3 id="rev-full-name" style="font-size: 1.2rem; font-weight: 800; color: var(--text); margin-bottom: 4px;">--</h3>
                <div style="display: flex; gap: 8px;">
                    <span class="badge badge-primary" id="rev-badge-dept">--</span>
                    <span class="badge badge-neutral" id="rev-badge-type">--</span>
                </div>
            </div>
        </div>

        <!-- CONTACT & PERSONAL -->
        <div class="review-sec">
            <div class="review-sec-title">
                <i data-lucide="user" size="14"></i> Personal & Contact
            </div>
            <div class="review-row"><span class="rev-label">Date of Birth</span><span class="rev-val" id="rev-dob">--</span></div>
            <div class="review-row"><span class="rev-label">Gender</span><span class="rev-val" id="rev-gender">--</span></div>
            <div class="review-row"><span class="rev-label">Phone</span><span class="rev-val" id="rev-phone">--</span></div>
            <div class="review-row"><span class="rev-label">Email</span><span class="rev-val" id="rev-email">--</span></div>
        </div>

        <!-- EMPLOYMENT PLACEMENT -->
        <div class="review-sec">
            <div class="review-sec-title">
                <i data-lucide="briefcase" size="14"></i> Placement Details
            </div>
            <div class="review-row"><span class="rev-label">Job Position</span><span class="rev-val" id="rev-pos">--</span></div>
            <div class="review-row"><span class="rev-label">Department</span><span class="rev-val" id="rev-dept">--</span></div>
            <div class="review-row"><span class="rev-label">Employment Type</span><span class="rev-val" id="rev-etype">--</span></div>
            <div class="review-row"><span class="rev-label">Location</span><span class="rev-val" id="rev-city">Addis Ababa</span></div>
        </div>

        <!-- FINANCE & TAX -->
        <div class="review-sec">
            <div class="review-sec-title">
                <i data-lucide="landmark" size="14"></i> Finance & Treasury
            </div>
            <div class="review-row"><span class="rev-label">Gross Salary</span><span class="rev-val" id="rev-sal">--</span></div>
            <div class="review-row"><span class="rev-label">Bank Name</span><span class="rev-val" id="rev-bank">--</span></div>
            <div class="review-row"><span class="rev-label">Account No.</span><span class="rev-val mono" id="rev-acc">--</span></div>
            <div class="review-row"><span class="rev-label">Tax ID (TIN)</span><span class="rev-val mono" id="rev-tin">--</span></div>
        </div>

        <!-- COMPLIANCE -->
        <div class="review-sec">
            <div class="review-sec-title">
                <i data-lucide="shield-check" size="14"></i> Emergency & Legal
            </div>
            <div class="review-row"><span class="rev-label">Emergency Contact</span><span class="rev-val" id="rev-ename">--</span></div>
            <div class="review-row"><span class="rev-label">Relationship</span><span class="rev-val" id="rev-relation">--</span></div>
            <div class="review-row"><span class="rev-label">Emergency Phone</span><span class="rev-val" id="rev-ephone">--</span></div>
        </div>
    </div>
</section>

            <div style="margin-top:60px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);padding-top:20px;">
                <button class="btn btn-secondary" id="ob-prev" onclick="moveOnboarding(-1)" style="visibility:hidden;">
                    <i data-lucide="chevron-left" size="14"></i> Previous
                </button>

                <!-- This area swaps between dots and the button -->
                <div id="ob-step-nav-center">
                    <div class="dot-progress" id="ob-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                    <button class="btn btn-success" id="btn-save-master-bottom" style="display:none; padding:10px 30px;" onclick="saveNewEmployee()">
                        <i data-lucide="user-plus" size="16"></i> Add Employee
                    </button>
                </div>

                <button class="btn btn-primary" id="ob-next" onclick="moveOnboarding(1)" style="padding:10px 24px;">
                    Next Step <i data-lucide="chevron-right" size="14"></i>
                </button>
            </div>
        </main>
    </div>
</div>