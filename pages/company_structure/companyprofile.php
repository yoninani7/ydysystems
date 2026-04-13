<?php  
include '../config.php'; 
 
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
      <button class="btn-glass-pro-slim"><i data-lucide="edit" size="14"></i><span>Update records</span></button>
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
</div>