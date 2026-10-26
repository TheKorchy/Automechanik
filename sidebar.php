<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user']['role'] ?? 'uzivatel';

// Pomocná funkcia pre zobrazenie štítku role
function getRoleBadge($role) {
    if ($role === 'admin') return '<small style="background:#ef4444;">ADMIN</small>';
    if ($role === 'mechanik') return '<small style="background:#f59e0b;">STAFF</small>';
    return '<small style="background:#10b981;">KLIENT</small>';
}
?>

<style>
    /* ... (tvoje pôvodné štýly ostávajú) ... */
    .sidebar { width: 260px; height: 100vh; position: fixed; background: #111827; color: white; padding: 25px 15px; z-index: 1001; top: 0; left: 0; display: flex; flex-direction: column; }
    .sidebar h4 { font-weight: 800; letter-spacing: -1px; color: white; margin-bottom: 35px; text-transform: uppercase; padding-left: 12px; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 10px; }
    .sidebar h4 small { font-size: 0.5rem; color: white; padding: 2px 6px; border-radius: 4px; }
    .sidebar a { display: flex; align-items: center; color: #adb5bd; text-decoration: none; padding: 12px 15px; border-radius: 12px; margin-bottom: 8px; transition: 0.3s; font-family: 'Inter', sans-serif; }
    .sidebar a i { margin-right: 12px; width: 20px; text-align: center; }
    .sidebar a:hover { background: #1f2937; color: #3b82f6; }
    .sidebar .active { background: #1f2937; color: white; border-left: 4px solid #3b82f6; font-weight: 600; }
    .text-danger-custom { color: #ef4444 !important; }
    .section-label { font-size: 0.7rem; text-transform: uppercase; color: #4b5563; margin: 20px 0 10px 15px; font-weight: 700; }
    .sidebar-footer { margin-top: auto; }
</style>

<div class="sidebar">
    <h4>TORYSA <?= getRoleBadge($user_role) ?></h4>

    <?php if ($user_role === 'admin' || $user_role === 'mechanik'): ?>
        <div class="section-label">Interný Panel</div>
        
        <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Prehľad
        </a>

        <a href="objednavky.php" class="<?= ($current_page == 'objednavky.php') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> Objednávky
        </a>

        <a href="mechanik_panel.php" class="<?= ($current_page == 'mechanik_panel.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Mechanik Panel
        </a>

        <a href="kalendar.php" class="<?= ($current_page == 'kalendar.php') ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i> Kalendár
        </a>

        <?php if ($user_role === 'admin'): ?>
            <a href="faktury.php" class="<?= ($current_page == 'faktury.php') ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Faktúry
            </a>
        <?php endif; ?>

        <a href="vin_decoder.php" class="<?= ($current_page == 'vin_decoder.php') ? 'active' : '' ?>">
            <i class="fas fa-car"></i> VIN Decoder
        </a>
    <?php endif; ?>

    <?php if ($user_role === 'uzivatel'): ?>
        <div class="section-label">Môj Účet</div>
        
        <a href="vsetky_notifikacie.php" class="<?= ($current_page == 'vsetky_notifikacie.php') ? 'active' : '' ?>">
            <i class="fas fa-bell"></i> Moje správy
        </a>
        
        <a href="moje_objednavky.php" class="<?= ($current_page == 'moje_objednavky.php') ? 'active' : '' ?>">
            <i class="fas fa-history"></i> Moje objednávky
        </a>

        <a href="index.php#rezervacia" class="">
            <i class="fas fa-plus-circle"></i> Nová rezervácia
        </a>
    <?php endif; ?>

    <div class="sidebar-footer">
        <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
        <a href="index.php"><i class="fas fa-external-link-alt"></i> Zobraziť web</a>
        <a href="logout.php" class="text-danger-custom"><i class="fas fa-sign-out-alt"></i> Odhlásiť</a>
    </div>
</div>