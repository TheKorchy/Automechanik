<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "connect_web.php";

$user = $_SESSION['user'] ?? null;
$avatarFileName = $user['avatar'] ?? 'default.png';
$avatarPath = "uploads/avatars/" . $avatarFileName;

if (!file_exists($avatarPath) || empty($avatarFileName)) {
    $avatarPath = "uploads/avatars/default.png";
}

$userRole = $user['role'] ?? 'user';
$unread_count = 0;

if ($user) {
    $u_id = $user['id'];
    // Získame počet neprečítaných správ pre badge
    $stmt_n = $conn->prepare("SELECT COUNT(*) as pocet FROM notifikacie WHERE uzivatel_id = ? AND precitane = 0");
    $stmt_n->bind_param("i", $u_id);
    $stmt_n->execute();
    $unread_count = $stmt_n->get_result()->fetch_assoc()['pocet'];
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .navbar {
        background: #0f172a !important;
        backdrop-filter: blur(12px);
        padding: 0.7rem 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        /* Fix pre desktop sidebar */
        margin-left: 260px;
        width: calc(100% - 260px);
        position: fixed;
        top: 0; right: 0; z-index: 1000;
        transition: all 0.3s ease;
    }

    @media (max-width: 992px) {
        .navbar { margin-left: 0; width: 100%; padding: 0.5rem 1rem; }
    }

    /* ŠTÝL NOTIFIKÁCIÍ (ZVONČEK) */
    .notif-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 42px; height: 42px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        color: #94a3b8; transition: 0.3s;
        position: relative; margin-right: 15px;
    }

    .notif-btn:hover {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
        border-color: #3b82f6;
        transform: translateY(-2px);
    }

    .notif-badge-admin {
        position: absolute; top: -5px; right: -5px;
        background: #ef4444; color: white; font-size: 0.65rem;
        min-width: 20px; height: 20px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; border: 2px solid #0f172a;
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.4);
    }

    /* DROPDOWN OKNO NOTIFIKÁCIÍ */
    .admin-notif-dropdown {
        width: 350px;
        border-radius: 24px;
        padding: 20px;
        background: #ffffff !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        margin-top: 15px !important;
    }

    .notif-scroll {
        max-height: 380px;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* Tenký scrollbar pre notifikácie */
    .notif-scroll::-webkit-scrollbar { width: 4px; }
    .notif-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .admin-notif-item {
        padding: 14px; border-radius: 16px; margin-bottom: 8px;
        background: #f8fafc; transition: 0.2s; border-left: 4px solid transparent;
        text-decoration: none; display: block;
    }

    .admin-notif-item.unread {
        background: #eff6ff;
        border-left-color: #3b82f6;
    }

    .admin-notif-item:hover {
        background: #f1f5f9;
        transform: translateX(4px);
    }

    /* PROFILOVÝ PILL */
    .user-nav-pill {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 5px 15px 5px 5px;
        border-radius: 50px;
        display: flex; align-items: center;
        transition: 0.3s;
    }

    .user-nav-pill:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #3b82f6;
    }

    .dropdown-user-info {
        background: #f8fafc;
        border-radius: 18px;
        padding: 15px;
        margin: 5px 5px 12px 5px;
    }

    .dropdown-item {
        border-radius: 12px;
        padding: 10px 15px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: 0.2s;
    }

    .dropdown-item i {
        width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        background: #f1f5f9; border-radius: 10px; color: #64748b;
    }

    .dropdown-item:hover {
        background: #eff6ff;
        color: #3b82f6;
    }

    .dropdown-item:hover i {
        background: #3b82f6;
        color: #fff;
    }
</style>

<nav class="navbar navbar-expand navbar-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center px-lg-4">

        <div class="d-lg-none">
            <button class="btn text-white p-0" id="sidebarToggle">
                <i class="fas fa-bars fa-lg"></i>
            </button>
        </div>

        <div class="ms-auto d-flex align-items-center">

            <?php if ($user): ?>

                <div class="dropdown">
                    <a href="#" class="notif-btn text-decoration-none shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-regular fa-bell fa-lg"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="notif-badge-admin"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end admin-notif-dropdown border-0 shadow-lg">
                        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                            <div>
                                <h6 class="m-0 fw-800 text-dark">Upozornenia</h6>
                                <p class="m-0 text-muted" style="font-size: 0.65rem;">Máte <?= $unread_count ?> neprečítaných správ</p>
                            </div>
                            <a href="oznac_precitane.php" class="btn btn-sm btn-light rounded-pill fw-bold" style="font-size: 0.65rem;">
                                <i class="fas fa-check-double me-1 text-primary"></i> Všetko prečítané
                            </a>
                        </div>

                        <div class="notif-scroll">
                            <?php
                            // Načítame posledných 6 notifikácií pre prihláseného Admina/Mechanika/Usera
                            $notif_res = $conn->query("SELECT * FROM notifikacie WHERE uzivatel_id = $u_id ORDER BY id DESC LIMIT 6");

                            if ($notif_res && $notif_res->num_rows > 0):
                                while($n = $notif_res->fetch_assoc()): ?>
                                    <a href="vsetky_notifikacie.php?read=<?= $n['id'] ?>" class="admin-notif-item <?= !$n['precitane'] ? 'unread' : '' ?>">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-bold text-dark" style="font-size: 0.75rem;">Systémové hlásenie</span>
                                            <span class="text-muted" style="font-size: 0.6rem;">
                                                <?= date("H:i", strtotime($n['datum'])) ?>
                                            </span>
                                        </div>
                                        <p class="m-0 text-secondary small" style="line-height: 1.4; font-size: 0.8rem;">
                                            <?= htmlspecialchars($n['sprava']) ?>
                                        </p>
                                    </a>
                                <?php endwhile;
                            else: ?>
                                <div class="text-center py-5">
                                    <i class="fa-solid fa-envelope-open text-light mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted small">Žiadne nové notifikácie</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-center mt-3 border-top pt-3">
                            <a href="vsetky_notifikacie.php" class="text-primary small fw-800 text-decoration-none">
                                Zobraziť všetky správy <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none user-nav-pill dropdown-toggle shadow-none" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $avatarPath ?>?t=<?= time() ?>" width="34" height="34" class="rounded-circle" style="object-fit: cover; border: 2px solid rgba(59, 130, 246, 0.5);">
                        <span class="ms-2 text-white small fw-bold d-none d-sm-inline">
                            <?= htmlspecialchars($user['username'] ?? 'Môj Účet') ?>
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3" aria-labelledby="userMenu">
                        <div class="dropdown-user-info">
                            <div class="small text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Rola: <?= htmlspecialchars($userRole) ?></div>
                            <div class="fw-800 text-dark fs-5"><?= htmlspecialchars($user['username']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                        </div>

                        <li><a class="dropdown-item" href="profil.php?tab=nastavenia"><i class="fas fa-user-gear"></i> Nastavenia profilu</a></li>
                        <li><a class="dropdown-item" href="vsetky_notifikacie.php"><i class="fas fa-bell"></i> Moje správy</a></li>
                        <li><a class="dropdown-item" href="index.php"><i class="fas fa-globe"></i> Návrat na web</a></li>

                        <div class="dropdown-divider mx-2 my-2"></div>

                        <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="fas fa-arrow-right-from-bracket"></i> Odhlásiť sa</a></li>
                    </ul>
                </div>

            <?php endif; ?>

        </div>
    </div>
</nav>