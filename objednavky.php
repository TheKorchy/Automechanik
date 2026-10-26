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
    echo "<div style='text-align:center; padding:50px;'><h1>Prístup zamietnutý</h1><a href='index.php'>Späť</a></div>";
    exit();
}

// 2. SPRACOVANIE ZMENY STAVU (IBA ADMIN)
if (isset($_POST['update_status']) && $isAdmin) {
    $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);
    $newStatus = mysqli_real_escape_string($conn, $_POST['new_status']);

    $infoRes = $conn->query("SELECT email, name, service FROM odoslane WHERE id = '$orderId'");
    $orderData = $infoRes->fetch_assoc();

    $customer_email = $orderData['email'];
    $service_name = $orderData['service'];
    $customer_name = $orderData['name'];
    $now = date("Y-m-d H:i:s");

    $userCheck = $conn->query("SELECT id FROM uzivatelia WHERE email = '$customer_email'");
    $userData = $userCheck->fetch_assoc();
    $customer_id = $userData['id'] ?? null;

    $stmt = $conn->prepare("UPDATE odoslane SET stav = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $orderId);

    if ($stmt->execute()) {
        if ($customer_id) {
            $msg_user = "Stav Vašej objednávky #$orderId ($service_name) bol zmenený na: $newStatus.";
            $conn->query("INSERT INTO notifikacie (uzivatel_id, sprava, datum, precitane) VALUES ('$customer_id', '$msg_user', '$now', 0)");
        }

        $msg_mech = "Admin zmenil stav objednávky #$orderId ($customer_name) na: $newStatus.";
        $mechs = $conn->query("SELECT id FROM uzivatelia WHERE role = 'mechanik'");
        while($m = $mechs->fetch_assoc()) {
            $m_id = $m['id'];
            $conn->query("INSERT INTO notifikacie (uzivatel_id, sprava, datum, precitane) VALUES ('$m_id', '$msg_mech', '$now', 0)");
        }

        header("Location: objednavky.php?success=1");
    } else {
        header("Location: objednavky.php?error=1");
    }
    exit();
}

include 'nav_admin.php';

$query = "SELECT * FROM odoslane ORDER BY id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objednávky | Interný systém</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .content { margin-left: 260px; padding: 40px; margin-top: 80px; transition: 0.3s; }

        .table-container {
            background: white; padding: 30px; border-radius: 24px;
            border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.02);
        }

        .filter-btn {
            border: none; padding: 10px 20px; border-radius: 14px;
            font-weight: 700; font-size: 0.85rem; transition: 0.3s;
            background: white; color: #64748b; border: 1px solid #e2e8f0;
        }
        .filter-btn.active { background: #3b82f6; color: white; border-color: #3b82f6; }

        .spz-badge-sm {
            background: #0f172a; color: #fff; padding: 4px 10px; border-radius: 6px;
            font-family: monospace; font-size: 0.75rem; font-weight: 700;
        }

        .badge-status { padding: 6px 14px; border-radius: 10px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
        .status-caka { background: #fff7ed; color: #9a3412; border: 1px solid #ffedd5; }
        .status-proces { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .status-hotove { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }

        @media (max-width: 992px) { .content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="page-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="mb-1 fw-800" style="letter-spacing: -1px;">Správa objednávok</h2>
            <p class="text-muted mb-0">Evidencia a stav servisných prác</p>
        </div>

        <div class="d-none d-md-flex align-items-center gap-3 bg-white p-2 px-3 rounded-pill border">
            <div class="text-end">
                <div class="fw-bold small"><?= htmlspecialchars($user['username']) ?></div>
                <div class="text-muted" style="font-size: 0.65rem;"><?= $isAdmin ? 'Administrátor' : 'Mechanik' ?></div>
            </div>
            <i class="fas fa-user-circle fa-2x text-primary"></i>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <button class="filter-btn active" onclick="filterStatus('', this)">Všetky</button>
        <button class="filter-btn" onclick="filterStatus('Čaká', this)">Čakajúce</button>
        <button class="filter-btn" onclick="filterStatus('V procese', this)">V procese</button>
        <button class="filter-btn" onclick="filterStatus('Hotové', this)">Hotové</button>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table id="ordersTable" class="table table-hover align-middle mb-0">
                <thead>
                <tr class="text-muted small">
                    <th>ID</th>
                    <th>ZÁKAZNÍK</th>
                    <th>SLUŽBA & VOZIDLO</th>
                    <th>DÁTUM</th>
                    <th>STAV</th>
                    <th class="text-center">AKCIA (ADMIN)</th>
                </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()):
                    $stav = $row['stav'] ?: 'Čaká';
                    $class = ($stav == 'Čaká') ? 'status-caka' : (($stav == 'V procese') ? 'status-proces' : 'status-hotove');
                    ?>
                    <tr>
                        <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                        <td>
                            <div class="fw-700"><?= htmlspecialchars($row['name']) ?> <?= htmlspecialchars($row['l_name'] ?? '') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                        </td>
                        <td>
                            <div class="fw-600 text-primary"><?= htmlspecialchars($row['service']) ?></div>
                            <div class="mt-1"><span class="spz-badge-sm"><?= htmlspecialchars($row['spz'] ?: 'BEZ ŠPZ') ?></span></div>
                        </td>
                        <td>
                            <div class="small fw-600"><?= ($row['datum'] == '0000-00-00 00:00:00') ? 'Termín neurčený' : date("d.m.Y", strtotime($row['datum'])) ?></div>
                        </td>
                        <td data-search="<?= $stav ?>">
                            <span class="badge-status <?= $class ?>"><?= $stav ?></span>
                        </td>
                        <td>
                            <form method="POST" class="d-flex justify-content-center">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="new_status" class="form-select form-select-sm w-auto" onchange="this.form.submit()" <?= !$isAdmin ? 'disabled' : '' ?> style="border-radius: 8px; font-weight: 600;">
                                    <option value="Čaká" <?= $stav=='Čaká'?'selected':'' ?>>Čaká</option>
                                    <option value="V procese" <?= $stav=='V procese'?'selected':'' ?>>V procese</option>
                                    <option value="Hotové" <?= $stav=='Hotové'?'selected':'' ?>>Hotové</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    let table;
    $(document).ready(function() {
        // Inicializácia DataTable
        table = $('#ordersTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/sk.json' },
            order: [[0, 'desc']],
            pageLength: 10,
            columnDefs: [
                { targets: 5, orderable: false } // Akcie sa nebudú dať zoraďovať
            ]
        });
    });

    // Opravená funkcia filtra
    function filterStatus(status, btn) {
        // Vizuálny prepínač tlačidiel
        $('.filter-btn').removeClass('active');
        $(btn).addClass('active');

        // Samotný filter v 4. stĺpci (index 4)
        // Používame inteligentné hľadanie bez striktného regexu, aby to bolo plynulé
        table.column(4).search(status).draw();
    }
</script>

</body>
</html>