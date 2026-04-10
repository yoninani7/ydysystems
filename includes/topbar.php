  <div class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" id="sidebar-toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar" aria-label="Toggle Sidebar">
        <i data-lucide="menu" size="16" id="toggle-icon"></i>
      </button>
      <div class="breadcrumb">
        <i data-lucide="home" size="13"></i>
        <span>/</span>
        <span id="page-title">Dashboard</span>
      </div>
    </div>
    <div class="topbar-right">
      <div class="icon-btn"><i data-lucide="bell" size="15"></i><span class="notif-dot"></span></div>
      <div class="icon-btn"><i data-lucide="help-circle" size="15"></i></div>
      <div class="topbar-user">
        <div class="topbar-user-avatar"><?= strtoupper(substr($username, 0, 2)) ?></div>
        <div class="topbar-user-info">
          <div class="u-name"><?php echo $username; ?></div>
          <div class="u-role"><?php echo $userRole; ?></div>
        </div>
      </div>
      <div class="icon-btn btn-logout" onclick="handleLogout()" title="Log out">
        <i data-lucide="log-out" size="15"></i>
      </div>
    </div>
  </div>