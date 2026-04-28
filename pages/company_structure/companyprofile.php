<?php  
require_once __DIR__ . '/../../config.php'; 
 
$stmt = $pdo->query("SELECT * FROM company_profile LIMIT 1");
$cp = $stmt->fetch();

// 3. Helper function to handle nulls/empty strings
function check($val) {
    return (!isset($val) || trim($val) === "") ? "-" : htmlspecialchars($val);
}
?>

<!-- COMPANY PROFILE PAGE --> 
<div class="page" id="p-company-profile">
  <div class="master-profile-wrapper">
    
    <!-- HERO BANNER -->
    <header class="db-hero-banner" style="padding:16px 28px; min-height:auto; position:relative; margin-bottom:20px;">
      <div class="db-hero-content">
        <h1 class="db-hero-title" style="font-size:1.5rem;"><?php echo check($cp['legal_name']); ?></h1>
        <p class="db-hero-sub">Corporate Registry</p>
      </div>
      <img src="assets/img/bgt.png" class="db-hero-center-img" alt="Banner">
      <button class="btn-glass-pro-slim" onclick="openCompanyEditModal()"><i data-lucide="edit" size="14"></i><span>Update records</span></button>
    </header>

    <div class="profile-main-grid" style="grid-template-columns: repeat(3, 1fr); gap:16px;"> 
      
      <!-- COLUMN 1: LEGAL -->
      <div class="data-card span-v-2">
        <div class="card-label-strip">
          <i data-lucide="file-text" size="14"></i>
          <span>Legal & Incorporation</span>
        </div>
        <div class="card-content">
          <div class="data-entry"><span class="de-label">CEO</span><span class="de-value"><?php echo check($cp['ceo_name']); ?></span></div>
          <div class="data-entry"><span class="de-label">Legal Name</span><span class="de-value"><?php echo check($cp['legal_name']); ?></span></div>
          <div class="data-entry"><span class="de-label">Trading Name</span><span class="de-value"><?php echo check($cp['trading_name']); ?></span></div>
          <div class="data-entry"><span class="de-label">Head office</span><span class="de-value"><?php echo check($cp['head_office']); ?></span></div>
          <div class="data-entry"><span class="de-label">Entity Type</span><span class="de-value"><?php echo check($cp['entity_type']); ?></span></div>
          <div class="data-entry"><span class="de-label">Establishment</span><span class="de-value"><?php echo ($cp['establishment_date'] != "-") ? date("M d, Y", strtotime($cp['establishment_date'])) : "-"; ?></span></div>
          <div class="data-entry"><span class="de-label">Registration No.</span><span class="de-value mono"><?php echo check($cp['registration_no']); ?></span></div>
          <div class="data-entry"><span class="de-label">Tax ID (TIN)</span><span class="de-value mono"><?php echo check($cp['tin']); ?></span></div>
          <div class="data-entry"><span class="de-label">VAT Reg Number</span><span class="de-value mono"><?php echo check($cp['vat_reg_number']); ?></span></div>
          <div class="data-entry"><span class="de-label">Trade License No.</span><span class="de-value"><?php echo check($cp['trade_license_no']); ?></span></div>
        </div>
      </div>

      <!-- COLUMN 2: OPERATIONS -->
      <div class="data-card">
        <div class="card-label-strip">
          <i data-lucide="gavel" size="14"></i>
          <span>Operational Policies</span>
        </div>
        <div class="card-content">
          <div class="data-entry">
            <span class="de-label">Standard Work Week</span>
            <span class="badge badge-primary"><?php echo check($cp['work_week_desc']); ?></span>
          </div>
          <div class="data-entry">
            <span class="de-label">Probation Period</span>
            <span class="de-value"><?php echo check($cp['probation_days']); ?></span>
          </div>
          <div class="data-entry">
            <span class="de-label">Retirement Policy</span>
            <span class="de-value"><?php echo check($cp['retirement_age']); ?></span>
          </div>
        </div>
      </div>

      <!-- COLUMN 2: TREASURY -->
      <div class="data-card">
        <div class="card-label-strip">
          <i data-lucide="landmark" size="14"></i>
          <span>System & Treasury</span>
        </div>
        <div class="card-content">
          <div class="data-entry"><span class="de-label">Main Bank</span><span class="de-value"><?php echo check($cp['main_bank']); ?></span></div>
          <div class="data-entry"><span class="de-label">Account (Primary)</span><span class="de-value mono"><?php echo check($cp['bank_account_primary']); ?></span></div>
          <div class="data-entry"><span class="de-label">Base Currency</span><span class="de-value"><?php echo check($cp['base_currency']); ?></span></div>
          <div class="data-entry"><span class="de-label">Fiscal Start</span><span class="de-value"><?php echo check($cp['fiscal_start']); ?></span></div>
        </div>
      </div>

      <!-- COLUMN 3: DIGITAL -->
      <div class="data-card">
        <div class="card-label-strip">
          <i data-lucide="globe" size="14"></i>
          <span>Digital Identity</span>
        </div>
        <div class="card-content">
          <div class="data-entry">
            <span class="de-label">Official Website</span>
            <span class="de-value"><?php echo check($cp['website']); ?></span>
          </div>
          <div class="data-entry">
            <span class="de-label">Corporate Email</span>
            <span class="de-value"><?php echo check($cp['corporate_email']); ?></span>
          </div>
          <div class="data-entry">
            <span class="de-label">Corporate Phone</span>
            <span class="de-value"><?php echo check($cp['corporate_phone']); ?></span>
          </div>
          <div class="data-entry" style="background:var(--primary-light); margin:12px; border-radius:10px; border:1px solid var(--primary);">
            <span class="de-label" style="color:var(--primary-dark); font-weight:800;">Software Version</span>
            <span class="de-value mono" style="font-weight:800;">v1.0.0-YDY</span>
          </div>
        </div>
      </div>

      <!-- COLUMN 3: SOCIAL MEDIA -->
      <div class="data-card">
        <div class="card-label-strip">
          <i data-lucide="share-2" size="14"></i>
          <span>Social Media Handles</span>
        </div>
        <div class="card-content" style="padding:12px 16px;">
          <div class="flex-col" style="gap:10px;">
            <div class="data-entry" style="border:none; padding:0;"><span class="de-label">Telegram:</span> <span class="de-value"><?php echo check($cp['telegram']); ?></span></div>
            <div class="data-entry" style="border:none; padding:0;"><span class="de-label">WhatsApp:</span> <span class="de-value"><?php echo check($cp['whatsapp']); ?></span></div>
            <div class="data-entry" style="border:none; padding:0;"><span class="de-label">LinkedIn:</span> <span class="de-value"><?php echo check($cp['linkedin']); ?></span></div>
          </div>
        </div>
      </div>

    </div>
  </div> 
  <!-- COMPANY PROFILE UPDATE MODAL (RESPONSIVE & THEME-ONLY) -->
<div class="modal-overlay" id="modal-edit-company" onclick="closeCompanyModal(event)">
  <div class="modal-box" style="max-width: 1400px; width: 98%; border-radius: 16px; overflow: hidden;">
    <div class="modal-header">
      <div>
        <div style="font-size:1.1rem; font-weight:800; letter-spacing: -0.02em;">Update Company Profile</div>
        <div style="font-size:0.75rem; color: var(--muted); margin-top: 4px;">Edit legal, operational, and digital records</div>
      </div>
      <button class="icon-btn" onclick="closeCompanyModal()"><i data-lucide="x" size="16"></i></button>
    </div>
    
    <div class="modal-body" style="padding: 24px;">
      <form id="company-profile-form">
        <input type="hidden" id="edit_csrf_token" value="<?php echo csrf_token(); ?>">
        
        <!-- RESPONSIVE GRID: 3 columns on desktop, 1 column on mobile -->
        <div class="company-profile-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
          
          <!-- COLUMN 1: LEGAL & INCORPORATION -->
          <div class="profile-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
              <div style="width: 32px; height: 32px; background: var(--primary-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <i data-lucide="file-text" size="16"></i>
              </div>
              <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text);">Legal & Incorporation</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 14px;">
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">LEGAL NAME *</label>
                <input id="edit_legal_name" class="form-ctrl" placeholder="e.g. YDY Systems PLC">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">TRADING NAME</label>
                <input id="edit_trading_name" class="form-ctrl" placeholder="e.g. YDY">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">CEO / GENERAL MANAGER</label>
                <input id="edit_ceo_name" class="form-ctrl" placeholder="Full name">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">HEAD OFFICE</label>
                <input id="edit_head_office" class="form-ctrl" placeholder="Full address">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">ENTITY TYPE</label>
                <div class="as-combo-container">
                  <input type="text" id="edit_entity_type" class="form-ctrl" placeholder="Select..." onfocus="toggleStaticDrop('as-drop-entity')" readonly>
                  <div class="as-combo-results" id="as-drop-entity">
                    <div class="as-res-item" onclick="selectAsItem('edit_entity_type','as-drop-entity','Private Limited Company')">Private Limited Company</div>
                    <div class="as-res-item" onclick="selectAsItem('edit_entity_type','as-drop-entity','PLC (Share Company)')">PLC (Share Company)</div>
                    <div class="as-res-item" onclick="selectAsItem('edit_entity_type','as-drop-entity','Sole Proprietorship')">Sole Proprietorship</div>
                    <div class="as-res-item" onclick="selectAsItem('edit_entity_type','as-drop-entity','NGO')">NGO</div>
                  </div>
                </div>
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">ESTABLISHMENT DATE</label>
                <input type="date" id="edit_establishment_date" class="form-ctrl">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">REGISTRATION NUMBER</label>
                <input id="edit_registration_no" class="form-ctrl" placeholder="e.g. MT/AA/1/0012345/2007">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">TAX ID (TIN)</label>
                <input id="edit_tin" class="form-ctrl" placeholder="10 digits">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">VAT REGISTRATION NUMBER</label>
                <input id="edit_vat_reg_number" class="form-ctrl" placeholder="VAT number">
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">TRADE LICENSE NUMBER</label>
                <input id="edit_trade_license_no" class="form-ctrl" placeholder="License number">
              </div>
            </div>
          </div>
          
          <!-- COLUMN 2: OPERATIONS & TREASURY -->
          <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Operations Card -->
            <div class="profile-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
                <div style="width: 32px; height: 32px; background: var(--primary-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                  <i data-lucide="gavel" size="16"></i>
                </div>
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text);">Operational Policies</span>
              </div>
              <div style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">STANDARD WORK WEEK</label>
                  <input id="edit_work_week_desc" class="form-ctrl" placeholder="e.g. Mon-Fri (40 hrs) + Sat Half-day">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">PROBATION PERIOD (DAYS)</label>
                  <input type="number" id="edit_probation_days" class="form-ctrl" placeholder="e.g. 60">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">RETIREMENT AGE</label>
                  <input type="number" id="edit_retirement_age" class="form-ctrl" placeholder="e.g. 60">
                </div>
              </div>
            </div>
            
            <!-- Treasury Card -->
            <div class="profile-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
                <div style="width: 32px; height: 32px; background: var(--primary-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                  <i data-lucide="landmark" size="16"></i>
                </div>
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text);">Treasury & Finance</span>
              </div>
              <div style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">MAIN BANK</label>
                  <input id="edit_main_bank" class="form-ctrl" placeholder="e.g. Commercial Bank of Ethiopia">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">PRIMARY ACCOUNT</label>
                  <input id="edit_bank_account_primary" class="form-ctrl" placeholder="Account number">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">BASE CURRENCY</label>
                  <div class="as-combo-container">
                    <input type="text" id="edit_base_currency" class="form-ctrl" placeholder="Select..." onfocus="toggleStaticDrop('as-drop-currency')" readonly>
                    <div class="as-combo-results" id="as-drop-currency">
                      <div class="as-res-item" onclick="selectAsItem('edit_base_currency','as-drop-currency','ETB')">ETB (Ethiopian Birr)</div>
                      <div class="as-res-item" onclick="selectAsItem('edit_base_currency','as-drop-currency','USD')">USD</div>
                    </div>
                  </div>
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">FISCAL YEAR START</label>
                  <input id="edit_fiscal_start" class="form-ctrl" placeholder="e.g. Hamle 01 (July 08)">
                </div>
              </div>
            </div>
          </div>
          
          <!-- COLUMN 3: DIGITAL & SOCIAL -->
          <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Digital Card -->
            <div class="profile-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
                <div style="width: 32px; height: 32px; background: var(--primary-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                  <i data-lucide="globe" size="16"></i>
                </div>
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text);">Digital Identity</span>
              </div>
              <div style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">OFFICIAL WEBSITE</label>
                  <input id="edit_website" class="form-ctrl" placeholder="https://">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">CORPORATE EMAIL</label>
                  <input type="email" id="edit_corporate_email" class="form-ctrl" placeholder="info@company.com">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">CORPORATE PHONE</label>
                  <input id="edit_corporate_phone" class="form-ctrl" placeholder="+251...">
                </div>
              </div>
            </div>
            
            <!-- Social Media Card -->
            <div class="profile-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 18px;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
                <div style="width: 32px; height: 32px; background: var(--primary-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                  <i data-lucide="share-2" size="16"></i>
                </div>
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text);">Social Media</span>
              </div>
              <div style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">TELEGRAM</label>
                  <input id="edit_telegram" class="form-ctrl" placeholder="@handle">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">WHATSAPP</label>
                  <input id="edit_whatsapp" class="form-ctrl" placeholder="+251...">
                </div>
                <div class="form-group" style="margin:0;">
                  <label style="font-size:0.65rem; font-weight:700; color: var(--muted); margin-bottom:4px;">LINKEDIN</label>
                  <input id="edit_linkedin" class="form-ctrl" placeholder="Company page URL">
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    
   <div class="modal-footer">
  <button class="btn btn-secondary" onclick="closeCompanyModal()">Cancel</button>
  <button class="btn btn-primary" id="btn-enable-edit" onclick="enableCompanyEdit()">
    <i data-lucide="edit" size="14"></i> Update
  </button>
  <button class="btn btn-primary" id="btn-save-company" onclick="saveCompanyProfile()" style="display: none;">
    <i data-lucide="save" size="14"></i> Save Changes
  </button>
</div>
  </div>
</div>
</div>