<?php
include "connect_web.php";
include "nav_admin.php";
require_once "config/auth.php";
requireLogin();

if (!isset($_SESSION['user'])) {
    header("Location: prihlasenie.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];
$userRole = $user['role']; // 'admin', 'mechanik', alebo 'user'
$email = $user['email'];
$message = "";
$activeTab = $_GET['tab'] ?? 'prehlad';

// --- LOGIKA 1: ZMENA STAVU (ADMIN A MECHANIK) ---
if (isset($_POST['update_status']) && ($userRole === 'admin' || $userRole === 'mechanik')) {
    $id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $novy_stav = mysqli_real_escape_string($conn, $_POST['stav']);
    mysqli_query($conn, "UPDATE odoslane SET stav = '$novy_stav' WHERE id = '$id'");
    if(isset($_POST['ajax'])) { exit(json_encode(['success' => true])); }
    header("Location: profil.php?tab=prehlad");
    exit();
}

// --- LOGIKA 2: ÚPRAVA ALEBO STORNO (IBA BEŽNÝ USER) ---
if ($userRole === 'user') {
    if (isset($_POST['edit_order'])) {
        $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);
        $service = mysqli_real_escape_string($conn, $_POST['service']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $l_name = mysqli_real_escape_string($conn, $_POST['l_name']);
        $about = mysqli_real_escape_string($conn, $_POST['about']);
        $spz = mysqli_real_escape_string($conn, $_POST['spz']);

        $sql = "UPDATE odoslane SET service='$service', name='$name', l_name='$l_name', about='$about', spz='$spz' 
                WHERE id='$orderId' AND email='$email' AND (stav='Čaká' OR stav IS NULL)";

        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success border-0 shadow-sm rounded-4 mb-4'><i class='fas fa-check-circle me-2'></i>Rezervácia bola úspešne aktualizovaná.</div>";
        }
    }

    if (isset($_POST['delete_order'])) {
        $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);
        $sql = "DELETE FROM odoslane WHERE id='$orderId' AND email='$email' AND (stav='Čaká' OR stav IS NULL)";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-danger border-0 shadow-sm rounded-4 mb-4'><i class='fas fa-trash me-2'></i>Objednávka bola zrušená.</div>";
        }
    }
}

// --- LOGIKA 3: AKTUALIZÁCIA PROFILU (PRE VŠETKÝCH) ---
if (isset($_POST['save_profile'])) {
    $newUsername = mysqli_real_escape_string($conn, $_POST['username']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['email']);
    $updateQuery = "UPDATE uzivatelia SET username = '$newUsername', email = '$newEmail' WHERE id = '$userId'";
    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['user']['username'] = $newUsername;
        $_SESSION['user']['email'] = $newEmail;
        if (!empty($_POST['password'])) {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE uzivatelia SET heslo = '$hashed' WHERE id = '$userId'");
        }
        if (!empty($_FILES['avatar']['name'])) {
            $targetDir = "uploads/avatars/";
            $fileName = $userId . "_" . time() . "_" . basename($_FILES["avatar"]["name"]);
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetDir . $fileName)) {
                mysqli_query($conn, "UPDATE uzivatelia SET avatar = '$fileName' WHERE id = '$userId'");
                $_SESSION['user']['avatar'] = $fileName;
            }
        }
        $message = "<div class='alert alert-success border-0 shadow-sm rounded-4 mb-4'>Profil bol úspešne aktualizovaný!</div>";
    }
}

// Načítanie objednávok
$query = ($userRole === 'admin' || $userRole === 'mechanik') ? "SELECT * FROM odoslane" : "SELECT * FROM odoslane WHERE email='$email'";
$result = $conn->query($query . " ORDER BY id DESC");
$all_orders = [];
while($row = $result->fetch_assoc()) {
    $all_orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Môj Profil | Torysa Servis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');
        :root { --primary: #3b82f6; --dark: #0f172a; --bg: #f8fafc; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }

        .content { margin-left: 260px; padding: 40px; transition: 0.3s; }
        .card-modern { background: white; border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); padding: 30px; margin-bottom: 25px; }

        /* User Order Cards */
        .order-card { border: 1px solid #e2e8f0; border-radius: 20px; padding: 25px; background: white; transition: 0.3s; position: relative; overflow: hidden; height: 100%; }
        .order-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .status-badge { position: absolute; top: 0; right: 0; padding: 8px 20px; font-size: 0.7rem; font-weight: 800; border-bottom-left-radius: 15px; text-transform: uppercase; }
        .spz-badge { background: #1e293b; color: white; padding: 4px 12px; border-radius: 8px; font-family: monospace; font-size: 0.85rem; }

        /* Kanban Styles */
        .workflow-col { background: #f1f5f9; padding: 20px; border-radius: 22px; min-height: 500px; border: 1px solid rgba(0,0,0,0.05); }
        .workflow-item { background: white; padding: 18px; border-radius: 16px; margin-bottom: 15px; border-left: 5px solid var(--primary); cursor: grab; transition: 0.2s; }

        /* Avatar */
        .avatar-preview { width: 120px; height: 120px; border-radius: 40px; object-fit: cover; border: 4px solid white; }

        @media (max-width: 992px) { .content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<?php include_once 'admin_dash_sidebar.php'; ?>

<div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
        <div>
            <h2 class="fw-800 mb-1 text-dark">Môj účet</h2>
            <p class="text-muted">Vitajte späť, <?= htmlspecialchars($user['username']) ?></p>
        </div>
        <ul class="nav nav-pills bg-white p-2 rounded-4 shadow-sm" id="profileTabs">
            <li class="nav-item"><a class="nav-link <?= $activeTab=='prehlad'?'active':'' ?> rounded-3 fw-600" href="?tab=prehlad">Objednávky</a></li>
            <li class="nav-item ms-2"><a class="nav-link <?= $activeTab=='nastavenia'?'active':'' ?> rounded-3 fw-600" href="profil.php?tab=nastavenia">Nastavenia</a></li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade <?= $activeTab=='prehlad'?'show active':'' ?>">
            <?= $message ?>

            <?php if($userRole === 'admin' || $userRole === 'mechanik'): ?>
                <div class="row g-4">
                    <?php
                    $columns = ['Čaká' => 'bg-secondary', 'V procese' => 'bg-warning', 'Hotové' => 'bg-success'];
                    foreach($columns as $title => $bgClass): ?>
                        <div class="col-lg-4">
                            <div class="workflow-col shadow-sm" ondrop="drop(event)" ondragover="allowDrop(event)" data-status="<?= $title ?>">
                                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                                    <h6 class="fw-800 text-muted m-0"><?= strtoupper($title) ?></h6>
                                    <span class="badge bg-white text-dark rounded-pill border"><?= count(array_filter($all_orders, function($o) use ($title) { return ($o['stav'] ?? 'Čaká') == $title; })) ?></span>
                                </div>
                                <?php foreach($all_orders as $o): if(($o['stav'] ?? 'Čaká') == $title): ?>
                                    <div class="workflow-item shadow-sm" draggable="true" ondragstart="drag(event)" id="order-<?= $o['id'] ?>" data-id="<?= $o['id'] ?>">
                                        <div class="small text-muted mb-1">#<?= $o['id'] ?> | <span class="spz-badge" style="font-size: 0.6rem;"><?= htmlspecialchars($o['spz'] ?? '---') ?></span></div>
                                        <div class="fw-bold text-dark mb-2"><?= htmlspecialchars($o['service']) ?></div>
                                        <div class="small fw-600 text-primary"><i class="fas fa-user me-1"></i> <?= htmlspecialchars($o['name']) ?></div>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="row g-4">
                    <?php if(empty($all_orders)): ?>
                        <div class="col-12 text-center py-5">
                            <div class="card-modern">
                                <i class="fas fa-calendar-times fa-3x text-light mb-3"></i>
                                <h5>Zatiaľ nemáte žiadne objednávky</h5>
                                <a href="kontakt.php" class="btn btn-primary rounded-pill px-4 mt-3">Objednať sa do servisu</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($all_orders as $o):
                            $stav = $o['stav'] ?? 'Čaká';
                            $statusColor = ($stav == 'Čaká') ? '#64748b' : (($stav == 'V procese') ? '#f59e0b' : '#10b981');
                            $canEdit = ($stav == 'Čaká');
                            ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="order-card">
                                    <div class="status-badge" style="background: <?= $statusColor ?>; color: white;">
                                        <?= $stav ?>
                                    </div>
                                    <div class="mb-3">
                                        <span class="text-muted small fw-bold text-uppercase">ID #<?= $o['id'] ?></span>
                                        <h5 class="fw-800 mt-1 text-dark"><?= htmlspecialchars($o['service']) ?></h5>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="spz-badge me-2"><?= htmlspecialchars($o['spz'] ?? '---') ?></span>
                                        <span class="small text-muted"><i class="fas fa-clock me-1"></i> <?= date("d.m.Y H:i", strtotime($o['datum'])) ?></span>
                                    </div>
                                    <p class="text-muted small mb-4" style="height: 40px; overflow: hidden;">
                                        <?= htmlspecialchars($o['about'] ?: 'Žiadny dodatočný popis.') ?>
                                    </p>

                                    <div class="d-flex gap-2">
                                        <?php if($canEdit): ?>
                                            <button class="btn btn-light rounded-pill flex-grow-1 fw-600 btn-sm py-2"
                                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($o)) ?>)">
                                                <i class="fas fa-edit me-1"></i> Upraviť
                                            </button>
                                            <form method="POST" onsubmit="return confirm('Naozaj chcete zrušiť túto objednávku?');" class="flex-grow-1">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <button type="submit" name="delete_order" class="btn btn-outline-danger rounded-pill w-100 fw-600 btn-sm py-2">
                                                    <i class="fas fa-times me-1"></i> Zrušiť
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div class="alert alert-info py-2 px-3 rounded-pill w-100 mb-0 small text-center border-0">
                                                <i class="fas fa-info-circle me-1"></i> Prebiehajúcu prácu nie je možné meniť.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade <?= $activeTab=='nastavenia'?'show active':'' ?>">
            <div class="card-modern mx-auto shadow-sm" style="max-width: 800px;">
                <form action="profil.php?tab=nastavenia" method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <?php $avt = !empty($user['avatar']) ? $user['avatar'] : 'default.png'; ?>
                            <img src="uploads/avatars/<?= $avt ?>" class="avatar-preview shadow-sm" id="pImg">
                            <label for="avatar" class="upload-badge" style="position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 35px; height: 35px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white;"><i class="fas fa-camera"></i></label>
                            <input type="file" name="avatar" id="avatar" style="display:none;" onchange="prevA()">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2 text-uppercase">Meno / Firma</label>
                            <input type="text" name="username" class="form-control rounded-4 border-light bg-light py-3" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2 text-uppercase">E-mailová adresa</label>
                            <input type="email" name="email" class="form-control rounded-4 border-light bg-light py-3" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="save_profile" class="btn btn-primary w-100 py-3 rounded-4 fw-800">ULOŽIŤ ZMENY</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <form method="POST">
                <div class="modal-header border-0 bg-light p-4">
                    <h5 class="fw-800 mb-0">Upraviť rezerváciu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="order_id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="small fw-bold text-muted">SLUŽBA</label>
                            <input type="text" name="service" id="edit_service" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">MENO</label>
                            <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">PRIEZVISKO</label>
                            <input type="text" name="l_name" id="edit_l_name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold text-muted">ŠPZ VOZIDLA</label>
                            <input type="text" name="spz" id="edit_spz" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold text-muted">POZNÁMKA</label>
                            <textarea name="about" id="edit_about" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Zavrieť</button>
                    <button type="submit" name="edit_order" class="btn btn-primary rounded-pill px-4 fw-600">Uložiť zmeny</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function prevA() {
        const file = document.getElementById('avatar').files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => document.getElementById('pImg').src = e.target.result;
            reader.readAsDataURL(file);
        }
    }

    function openEditModal(order) {
        $('#edit_id').val(order.id);
        $('#edit_service').val(order.service);
        $('#edit_name').val(order.name);
        $('#edit_l_name').val(order.l_name);
        $('#edit_spz').val(order.spz);
        $('#edit_about').val(order.about);
        new bootstrap.Modal(document.getElementById('editOrderModal')).show();
    }

    <?php if($userRole === 'admin' || $userRole === 'mechanik'): ?>
    function allowDrop(ev) { ev.preventDefault(); }
    function drag(ev) { ev.dataTransfer.setData("text", ev.target.id); }
    function drop(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        var el = document.getElementById(data);
        var targetCol = ev.target.closest('.workflow-col');
        if (targetCol) {
            targetCol.appendChild(el);
            $.post("profil.php", {
                update_status: true,
                order_id: el.getAttribute('data-id'),
                stav: targetCol.getAttribute('data-status'),
                ajax: true
            });
        }
    }
    <?php endif; ?>
</script>
</body>
</html>