<!-- ASSET TRACKING   -->
<div class="page" id="p-asset-tracking">
  <div class="page-header">
    <div>
      <div class="page-title">Asset Registry</div>
      <div class="page-sub">Comprehensive tracking of corporate inventory, valuation, and protection plans</div>
    </div>
    <div class="flex-row">
      <button class="btn btn-secondary"><i data-lucide="layout-grid" size="13"></i> Category list</button>
      <button class="btn btn-primary" onclick="openAssetModal()">
        <i data-lucide="plus" size="13"></i> Register New Asset
      </button>
    </div>
  </div>

  <!-- Summary Ribbon -->
  <div class="stats-row stats-4 mb-4">
    <div class="stat-card" style="border-left:4px solid var(--primary);"><div class="stat-label">Inventory Value</div><div class="stat-value">ETB 4.2M</div></div>
    <div class="stat-card" style="border-left:4px solid var(--info);"><div class="stat-label">Total Assets</div><div class="stat-value">256</div></div> 
    <div class="stat-card" style="border-left:4px solid var(--danger);"><div class="stat-label">Total custodians</div><div class="stat-value">03</div></div>
  </div>

  <!-- Standard Table Container -->
  <div id="tbl-assets"></div>
</div>