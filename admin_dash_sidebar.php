<?php
// Predpokladáme, že session už beží z hlavného súboru profilu
$current_page = basename($_SERVER['PHP_SELF']);
$current_tab = $_GET['tab'] ?? '';
$user_role = $_SESSION['user']['role'] ?? 'user';
?>

<style>
    :root {
        --sidebar-width: 260px;
        --accent-blue: #3b82f6;
        --staff-yellow: #facc15; /* Žltá pre mechanikov */
    }

    .sidebar-admin {
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        background: #0f172a;
        color: white;
        padding: 25px 15px;
        z-index: 1100;
        top: 0;
        left: 0;
        border-right: 1px solid rgba(59, 130, 246, 0.2);
        display: flex;
        flex-direction: column;
    }

    /* Logo a Badge v hlavičke */
    .sidebar-admin h4 {
        font-weight: 800;
        color: white;
        margin-bottom: 35px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Bricolage Grotesque', sans-serif;
    }

    .sidebar-admin h4 small {
        font-size: 0.6rem;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 900;
    }

    /* Farby štítkov podľa roly */
    .badge-admin { background: var(--accent-blue); color: white; }
    .badge-staff { background: var(--staff-yellow); color: #000; }
    .badge-client { background: #64748b; color: white; }

    /* Odkazy v menu */
    .sidebar-admin a {
        display: flex;
        align-items: center;
        color: #94a3b8;
        text-decoration: none;
        padding: 12px 15px;
        border-radius: 12px;
        margin-bottom: 5px;
        transition: 0.3s;
        font-weight: 500;
    }

    .sidebar-admin a i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    .sidebar-admin a:hover,
    .sidebar-admin a.active {
        background: var(--accent-blue);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .sidebar-admin .section-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #475569;
        font-weight: 700;
        margin: 20px 0 10px 15px;
        letter-spacing: 1px;
    }

    @media (max-width: 992px) {
        .sidebar-admin { transform: translateX(-100%); }
    }
</style>

<div class="sidebar-admin">
    <h4>
        TORYSA
        <?php if($user_role === 'admin'): ?>
            <small class="badge-admin">ADMIN</small>
        <?php elseif($user_role === 'mechanik'): ?>
            <small class="badge-staff">STAFF</small>
        <?php else: ?>
            <small class="badge-client">KLIENT</small>
        <?php endif; ?>
    </h4>

    <?php if($user_role === 'admin'): ?>
        <div class="section-label">Manažment</div>
        <a href="admin_dashboard.php" class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> Celkový prehľad
        </a>

        <div class="section-label">Správa systému</div>
        <a href="admin_panel.php?tab=uzivatelia" class="<?= ($current_tab == 'uzivatelia') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Používatelia
        </a>
        <a href="admin_panel.php?tab=objednavky" class="<?= ($current_tab == 'objednavky') ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> Všetky objednávky
        </a>
        <a href="admin_panel.php?tab=galeria" class="<?= ($current_tab == 'galeria') ? 'active' : '' ?>">
            <i class="fas fa-images"></i> Galéria / Web
        </a>
    <?php endif; ?>

    <?php if($user_role === 'mechanik'): ?>
        <div class="section-label">Servis</div>
        <a href="objednavky.php" class="<?= ($current_page == 'objednavky.php') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> Moje úlohy
        </a>
        <a href="kalendar.php" class="<?= ($current_page == 'kalendar.php') ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i> Plán prác
        </a>
    <?php endif; ?>

    <div class="section-label">Môj účet</div>
    <a href="profil.php" class="<?= ($current_page == 'profil.php') ? 'active' : '' ?>">
        <i class="fas fa-user-circle"></i> Môj Profil
    </a>
    <a href="vsetky_notifikacie.php" class="<?= ($current_page == 'vsetky_notifikacie.php') ? 'active' : '' ?>">
        <i class="fas fa-bell"></i> Upozornenia
    </a>

    <div class="mt-auto">
        <hr style="border-color: rgba(255,255,255,0.05); margin: 15px 0;">
        <a href="index.php">
            <i class="fas fa-external-link-alt"></i> Späť na web
        </a>
        <a href="logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt"></i> Odhlásiť sa
        </a>
    </div>
</div>