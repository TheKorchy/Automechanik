<?php
include "connect_web.php";
include 'nav_admin.php';
// nav_admin.php sme odstránili alebo ho nechaj, ak obsahuje dôležité globálne funkcie,
// ale sidebar riešime nižšie dynamicky.

// Ochrana prístupu
if (session_status() === PHP_SESSION_NONE) session_start();

// OPRAVA: Povolený prístup pre admina AJ mechanika
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'mechanik')) {
    header("Location: prihlasenie.php");
    exit();
}

$userRole = $_SESSION['user']['role'];
$activeTab = $_GET['tab'] ?? 'uzivatelia';

/* =========================
   LOGIKA PRE UPLOAD A MAZANIE
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['obrazok'])) {
    $sekcia = $_POST['sekcia'];
    $dir = "uploads/$sekcia/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['obrazok']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','webp'])) {
        $fixne = ['servis-motora','servis-podvozku','servis-prevodoviek','diagnostika','pneuservis'];
        if (in_array($sekcia, $fixne)) {
            $old = $conn->query("SELECT * FROM galeria WHERE sekcia='$sekcia'");
            while ($o = $old->fetch_assoc()) {
                if (file_exists($o['cesta'])) unlink($o['cesta']);
                $conn->query("DELETE FROM galeria WHERE id={$o['id']}");
            }
        }
        $name = uniqid().".".$ext;
        $path = $dir.$name;
        if (move_uploaded_file($_FILES['obrazok']['tmp_name'], $path)) {
            $conn->query("INSERT INTO galeria (sekcia, cesta, poradie) VALUES ('$sekcia','$path',1)");
        }
    }
    header("Location: admin_panel.php?tab=galeria");
    exit;
}

if (isset($_GET['delete_img'])) {
    $id = (int)$_GET['delete_img'];
    $res = $conn->query("SELECT cesta FROM galeria WHERE id=$id");
    if ($res && $row = $res->fetch_assoc()) {
        if (file_exists($row['cesta'])) unlink($row['cesta']);
        $conn->query("DELETE FROM galeria WHERE id=$id");
    }
    header("Location: admin_panel.php?tab=galeria");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Správa systému</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        :root { --primary: #3b82f6; --dark: #0f172a; --bg: #f8fafc; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Main Content - prispôsobené pre sidebar */
        .content { margin-left: 260px; padding: 40px; transition: 0.3s; }
        .card-modern { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: white; }

        /* Tabs */
        .nav-tabs-modern { border-bottom: 2px solid #f1f5f9; gap: 30px; margin-bottom: 30px; }
        .nav-tabs-modern .nav-link { padding: 10px 0; font-weight: 600; color: #64748b; border: none; position: relative; background: transparent; transition: 0.3s; }
        .nav-tabs-modern .nav-link.active { color: var(--primary); }
        .nav-tabs-modern .nav-link.active::after { content: ""; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: var(--primary); }

        /* Tables */
        .table thead th { background: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 15px; border: none; }
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

        /* Flip Cards Galéria */
        .flip-card { background-color: transparent; height: 220px; perspective: 1000px; border-radius: 20px; }
        .flip-card-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); transform-style: preserve-3d; border-radius: 20px; }
        .flip-card:hover .flip-card-inner { transform: rotateY(180deg); }
        .flip-card-front, .flip-card-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 20px; overflow: hidden; }
        .flip-card-front img { width: 100%; height: 100%; object-fit: cover; }
        .flip-card-back { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; transform: rotateY(180deg); display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; }

        .badge-role-overlay { position: absolute; top: 12px; left: 12px; z-index: 5; padding: 4px 10px; border-radius: 8px; font-size: 0.65rem; font-weight: 800; color: white; }

        @media (max-width: 992px) { .content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<?php
// DYNAMICKÉ NAČÍTANIE SIDEBARU
if ($userRole === 'mechanik') {
    include_once 'sidebar.php';
} else {
    include_once 'admin_dash_sidebar.php';
}
?>

<div class="content">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-800 text-dark">Správa systému</h2>
        <span class="badge bg-light text-dark border p-2 px-3 rounded-3">
            Prihlásený ako: <strong class="text-primary"><?= strtoupper($userRole) ?></strong>
        </span>
    </div>

    <div class="card card-modern">
        <div class="card-body p-4">

            <ul class="nav nav-tabs-modern" id="adminTabs" role="tablist">
                <?php if($userRole === 'admin'): ?>
                    <li class="nav-item">
                        <button class="nav-link <?= $activeTab=='uzivatelia'?'active':'' ?>" id="tab-uzivatelia" data-bs-toggle="tab" data-bs-target="#uzivatelia" type="button" role="tab">Používatelia</button>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab=='objednavky'?'active':'' ?>" id="tab-objednavky" data-bs-toggle="tab" data-bs-target="#objednavky" type="button" role="tab">Objednávky</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab=='galeria'?'active':'' ?>" id="tab-galeria" data-bs-toggle="tab" data-bs-target="#galeria" type="button" role="tab">Galéria / Web</button>
                </li>
            </ul>

            <div class="tab-content">

                <?php if($userRole === 'admin'): ?>
                    <div class="tab-pane fade <?= $activeTab=='uzivatelia'?'show active':'' ?>" id="uzivatelia" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Používateľ</th><th>E-mail</th><th>Úroveň prístupu</th></tr></thead>
                                <tbody>
                                <?php
                                $users = $conn->query("SELECT username, email, role FROM uzivatelia");
                                while($u=$users->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($u['username']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><span class="badge bg-<?= $u['role']=='admin'?'danger':($u['role']=='mechanik'?'warning':'primary') ?>"><?= $u['role'] ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="tab-pane fade <?= ($activeTab=='objednavky' || ($userRole == 'mechanik' && $activeTab == 'uzivatelia')) ? 'show active' : '' ?>" id="objednavky" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>Služba</th><th>Zákazník</th><th>Dátum</th><th>Stav</th></tr></thead>
                            <tbody>
                            <?php
                            $orders = $conn->query("SELECT * FROM odoslane ORDER BY id DESC");
                            while($o=$orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($o['service']) ?></td>
                                    <td><?= htmlspecialchars($o['name']) ?></td>
                                    <td class="text-muted"><?= date("d.m.Y H:i", strtotime($o['datum'])) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $o['stav'] ?? 'Čaká' ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade <?= $activeTab=='galeria'?'show active':'' ?>" id="galeria" role="tabpanel">
                    <div class="bg-light p-4 rounded-4 mb-5 border">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Sekcia webu</label>
                                    <select name="sekcia" class="form-select rounded-3" required>
                                        <option value="index-carousel">Hlavný Slider</option>
                                        <option value="servis-motora">Servis motora</option>
                                        <option value="diagnostika">Diagnostika</option>
                                        <option value="pneuservis">Pneuservis</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Obrázok</label>
                                    <input type="file" name="obrazok" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3">Nahrať fotku</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4">
                        <?php
                        $imgs = $conn->query("SELECT * FROM galeria ORDER BY id DESC");
                        while($img = $imgs->fetch_assoc()): ?>
                            <div class="col">
                                <div class="flip-card">
                                    <div class="flip-card-inner shadow-sm">
                                        <div class="flip-card-front">
                                            <div class="badge-role-overlay bg-dark opacity-75"><?= strtoupper($img['sekcia']) ?></div>
                                            <img src="<?= $img['cesta'] ?>" alt="Foto">
                                        </div>
                                        <div class="flip-card-back">
                                            <a href="?delete_img=<?= $img['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-3" onclick="return confirm('Zmazať?')">
                                                <i class="fas fa-trash me-1"></i> Zmazať
                                            </a>
                                            <small class="mt-3 opacity-50">ID: #<?= $img['id'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Skript na synchronizáciu tabov s URL
    document.addEventListener("DOMContentLoaded", function() {
        const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (event) {
                const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
                const newUrl = window.location.pathname + '?tab=' + targetId;
                window.history.pushState({path: newUrl}, '', newUrl);
            });
        });
    });
</script>
</body>
</html>