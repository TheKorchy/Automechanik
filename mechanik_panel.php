<?php
session_start();
include "connect_web.php";

// 1. OCHRANA PRÍSTUPU
if (!isset($_SESSION['user'])) {
    header("Location: prihlasenie.php");
    exit();
}

$user = $_SESSION['user'];
$u_id = $user['id'];
$isAdmin = ($user['role'] === 'admin');
$isMechanic = ($user['role'] === 'mechanik');

if (!$isAdmin && !$isMechanic) {
    header("Location: index.php");
    exit();
}

// 2. JEDNOTNÉ SPRACOVANIE ZMENY STAVU (S NOTIFIKÁCIAMI)
if (isset($_POST['quick_update'])) {
    $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);
    $newStatus = mysqli_real_escape_string($conn, $_POST['new_status']);
    $mechanicName = $user['username'];
    $now = date("Y-m-d H:i:s");

    // A. Získame info o objednávke (email a službu)
    $infoRes = $conn->query("SELECT email, service, name FROM odoslane WHERE id = '$orderId'");
    $orderData = $infoRes->fetch_assoc();
    $custEmail = $orderData['email'];
    $serviceName = $orderData['service'];
    $custName = $orderData['name'];

    // B. Nájdeme ID zákazníka v tabuľke 'uzivatelia' podľa emailu
    $userCheck = $conn->query("SELECT id FROM uzivatelia WHERE email = '$custEmail'");
    $userData = $userCheck->fetch_assoc();
    $customerId = $userData['id'] ?? null;

    // C. Update stavu v DB
    $stmt = $conn->prepare("UPDATE odoslane SET stav = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $orderId);

    if ($stmt->execute()) {
        // --- 1. NOTIFIKÁCIA PRE ZÁKAZNÍKA ---
        if ($customerId) {
            $msgCust = "Vaša objednávka #$orderId ($serviceName) zmenila stav na: **$newStatus**.";
            $conn->query("INSERT INTO notifikacie (uzivatel_id, sprava, datum, precitane) VALUES ('$customerId', '$msgCust', '$now', 0)");
        }

        // --- 2. NOTIFIKÁCIA PRE ADMINA ---
        $msgAdmin = "Mechanik $mechanicName zmenil stav zákazky #$orderId ($custName) na: $newStatus.";
        $admins = $conn->query("SELECT id FROM uzivatelia WHERE role = 'admin'");
        while($adm = $admins->fetch_assoc()) {
            $ad_id = $adm['id'];
            $conn->query("INSERT INTO notifikacie (uzivatel_id, sprava, datum, precitane) VALUES ('$ad_id', '$msgAdmin', '$now', 0)");
        }

        header("Location: mechanik_panel.php?success=1");
    } else {
        header("Location: mechanik_panel.php?error=1");
    }
    exit();
}

include "nav_admin.php"; // Obsahuje zvonček a prepojenie na sidebar
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanik Panel | Interný systém</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }

        .content { margin-left: 280px; padding: 110px 40px 40px 40px; min-height: 100vh; transition: 0.3s; }

        .header-top-wrapper { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }

        .user-badge-box {
            background: white; padding: 10px 18px; border-radius: 16px;
            border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .work-card {
            background: white; border-radius: 20px; border: 1px solid #e2e8f0;
            margin-bottom: 20px; padding: 25px; position: relative;
            transition: 0.3s; overflow: hidden;
        }
        .work-card:hover { transform: translateY(-4px); border-color: #3b82f6; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }

        .card-status-bar { height: 100%; width: 6px; position: absolute; left: 0; top: 0; }
        .status-waiting { background: #f59e0b; }
        .status-working { background: #3b82f6; }

        .spz-badge {
            background: #0f172a; color: #fff; padding: 6px 12px; border-radius: 8px;
            font-family: monospace; font-weight: 800; display: inline-block;
        }

        @media (max-width: 992px) { .content { margin-left: 0; padding-top: 100px; padding-left: 15px; padding-right: 15px; } }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="header-top-wrapper">
        <div>
            <h2 class="fw-800 mb-1" style="letter-spacing: -1.5px;">Dielenský dispečing</h2>
            <p class="text-muted mb-0">Správa aktívnych opráv</p>
        </div>

        <div class="user-badge-box d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <div class="fw-bold small"><?= htmlspecialchars($user['username']) ?></div>
                <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <span class="badge <?= $isAdmin ? 'bg-danger' : 'bg-primary' ?> rounded-pill px-3 py-2">
                <i class="fas <?= $isAdmin ? 'fa-user-shield' : 'fa-tools' ?> me-2"></i>
                <?= $isAdmin ? 'ADMIN' : 'MECHANIK' ?>
            </span>
        </div>
    </div>

    <?php
    $query = "SELECT * FROM odoslane WHERE stav != 'Hotové' ORDER BY FIELD(stav, 'V procese', 'Čaká'), id DESC";
    $result = $conn->query($query);
    $activeCount = ($conn->query("SELECT COUNT(*) as c FROM odoslane WHERE stav = 'V procese'"))->fetch_assoc()['c'];
    ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-white p-3 rounded-4 border d-inline-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                    <i class="fas fa-car-side fa-lg"></i>
                </div>
                <div>
                    <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">Aktuálne na dielni</div>
                    <div class="h4 mb-0 fw-800"><?= $activeCount ?> vozidlá</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()):
                $isInProgress = ($row['stav'] === 'V procese');
                ?>
                <div class="col-12">
                    <div class="work-card">
                        <div class="card-status-bar <?= $isInProgress ? 'status-working' : 'status-waiting' ?>"></div>
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center border-end py-2">
                                <div class="spz-badge mb-2"><?= htmlspecialchars($row['spz'] ?: 'BEZ ŠPZ') ?></div>
                                <div class="small fw-800 <?= $isInProgress ? 'text-primary' : 'text-warning' ?>">
                                    <?= $isInProgress ? 'V OPRAVE' : 'ČAKÁ' ?>
                                </div>
                            </div>
                            <div class="col-md-6 ps-md-4 py-2">
                                <div class="text-muted small mb-1 fw-600">Zákazka #<?= $row['id'] ?></div>
                                <h5 class="fw-800 text-dark mb-1"><?= htmlspecialchars($row['service']) ?></h5>
                                <div class="text-secondary small fw-600">
                                    <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($row['name']) ?> <?= htmlspecialchars($row['l_name'] ?? '') ?>
                                </div>
                            </div>
                            <div class="col-md-4 text-end py-2">
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="quick_update" value="1">

                                    <?php if (!$isInProgress): ?>
                                        <button name="new_status" value="V procese" class="btn btn-primary rounded-pill px-4 fw-bold">
                                            <i class="fas fa-play me-2"></i>Prevziať
                                        </button>
                                    <?php else: ?>
                                        <button name="new_status" value="Hotové" class="btn btn-success rounded-pill px-4 fw-bold">
                                            <i class="fas fa-check me-2"></i>Hotovo
                                        </button>
                                        <button name="new_status" value="Čaká" class="btn btn-outline-secondary rounded-pill ms-2" title="Pozastaviť">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <img src="https://illustrations.popsy.co/gray/success.svg" style="width: 200px;" alt="Hotovo">
                <h4 class="mt-4 fw-800 text-muted">Žiadne čakajúce opravy!</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>