<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "connect_web.php";

$user = $_SESSION['user'] ?? null;
$unread_count = 0;

if ($user) {
    $u_id = $user['id'];
    // Počet neprečítaných správ pre prihláseného užívateľa
    $stmt_n = $conn->prepare("SELECT COUNT(*) as pocet FROM notifikacie WHERE uzivatel_id = ? AND precitane = 0");
    $stmt_n->bind_param("i", $u_id);
    $stmt_n->execute();
    $unread_count = $stmt_n->get_result()->fetch_assoc()['pocet'];
}

$avatarPath = (isset($user['avatar']) && !empty($user['avatar'])) ? "uploads/avatars/" . $user['avatar'] : "uploads/avatars/default.png";
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=Plus+Jakarta+Sans:wght@200..800&display=swap');

    :root {
        --nav-bg: #020617;
        --accent-blue: #3b82f6;
        --text-bright: #f1f5f9;
    }

    .navbar-custom {
        background-color: var(--nav-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 0.7rem 0;
    }

    .navbar-brand {
        font-family: 'Bricolage Grotesque', sans-serif;
        font-weight: 800;
        letter-spacing: -1px;
        color: var(--text-bright) !important;
    }

    .nav-link {
        color: rgba(255,255,255,0.8) !important;
        font-weight: 500;
        transition: 0.3s;
        margin: 0 5px;
    }

    .nav-link:hover { color: var(--accent-blue) !important; }

    .btn-register-custom {
        background-color: var(--accent-blue) !important;
        color: white !important;
        font-weight: 700;
        border-radius: 12px;
        padding: 8px 24px !important;
        transition: 0.3s;
        border: none;
    }

    /* DROPDOWN NOTIFIKÁCIÍ - ELITE DESIGN */
    .notif-dropdown {
        width: 350px;
        background: rgba(15, 23, 42, 0.98) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(59, 130, 246, 0.2) !important;
        border-radius: 20px;
        padding: 15px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        margin-top: 15px !important;
    }

    .notif-item-box {
        padding: 12px;
        border-radius: 14px;
        margin-bottom: 8px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid transparent;
        transition: 0.3s;
    }

    .notif-item-box.new {
        border-left: 3px solid var(--accent-blue);
        background: rgba(59, 130, 246, 0.05);
    }

    .notif-item-box:hover { background: rgba(255, 255, 255, 0.07); transform: translateX(3px); }

    .notif-badge-elite {
        position: absolute; top: -8px; right: -10px;
        background: #ef4444; color: white; font-size: 0.65rem;
        padding: 2px 6px; border-radius: 50px; font-weight: 900;
        border: 2px solid var(--nav-bg);
    }

    .new-dot {
        width: 8px; height: 8px; background: var(--accent-blue); border-radius: 50%;
        display: inline-block; margin-left: 5px; animation: pulse-notif 2s infinite;
    }

    @keyframes pulse-notif { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }

    .user-pill {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50px;
        padding: 4px 12px 4px 5px;
        display: flex; align-items: center; text-decoration: none; transition: 0.3s;
    }

    .user-pill:hover { background: rgba(255, 255, 255, 0.1); }
</style>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top navbar-custom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="obrazky-logo/ChatGPT%20Image%2026.%209.%202025,%2016_07_44.png" height="40" class="me-2 rounded shadow-sm">
            TORYSA <span style="color: var(--accent-blue);">SERVIS</span>
        </a>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Domov</a></li>
                <li class="nav-item"><a class="nav-link" href="O_sluzbe.php">O nás</a></li>
                <li class="nav-item"><a class="nav-link" href="sluzby_opis.php">Služby</a></li>
                <li class="nav-item"><a class="nav-link" href="kontakt.php">Kontakt</a></li>
            </ul>

            <div class="d-flex align-items-center">
                <?php if ($user): ?>
                    <div class="dropdown me-3">
                        <a href="#" class="position-relative text-white" data-bs-toggle="dropdown">
                            <i class="fa-regular fa-bell fa-lg"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="notif-badge-elite"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                            <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                                <h6 class="m-0 text-white fw-bold">Upozornenia</h6>
                                <a href="oznac_precitane.php" class="small text-primary text-decoration-none fw-bold">Označiť všetko</a>
                            </div>

                            <div style="max-height: 320px; overflow-y: auto; padding-right: 5px;">
                                <?php
                                $notif_res = $conn->query("SELECT * FROM notifikacie WHERE uzivatel_id = $u_id ORDER BY id DESC LIMIT 5");
                                if ($notif_res && $notif_res->num_rows > 0):
                                    while($n = $notif_res->fetch_assoc()): ?>
                                        <div class="notif-item-box <?= !$n['precitane'] ? 'new' : '' ?>">
                                            <p class="m-0 text-white-50 small" style="line-height: 1.5;">
                                                <?= nl2br(htmlspecialchars($n['sprava'])) ?>
                                                <?php if(!$n['precitane']) echo '<span class="new-dot"></span>'; ?>
                                            </p>
                                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                                <small class="text-muted" style="font-size: 0.65rem;">
                                                    <i class="fa-regular fa-clock me-1"></i> <?= date("d.m. H:i", strtotime($n['datum'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile;
                                else: ?>
                                    <div class="text-center py-4 text-muted small">Žiadne správy</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a class="user-pill dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <img src="<?= $avatarPath ?>?t=<?= time() ?>" width="32" height="32" class="rounded-circle" style="object-fit: cover;">
                            <span class="ms-2 text-white small fw-bold"><?= htmlspecialchars($user['username']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">

                            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                            <?php if ($user['role'] === 'mechanik'): ?>
                                <li><a class="dropdown-item" href="dashboard.php">Prehľad</a></li>
                            <?php endif; ?>
                            <?php if ($user['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="dashboard.php">Prehľad</a></li>
                                <li><a class="dropdown-item" href="admin_panel.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Odhlásiť</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="nav-link text-white me-3" href="prihlasenie.php">Prihlásenie</a>
                    <a class="btn btn-register-custom" href="registracia.php">Registrácia</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>