<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . "/config/auth.php";
requireAdmin(); 
include "connect_web.php";
include "nav_admin.php";
$user = $_SESSION['user'] ?? null;

// --- Štatistiky ---
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM uzivatelia")->fetch_assoc()['total'] ?? 0;
$totalAdmins = $conn->query("SELECT COUNT(*) as total FROM uzivatelia WHERE role='admin'")->fetch_assoc()['total'] ?? 0;
$totalMechs = $conn->query("SELECT COUNT(*) as total FROM uzivatelia WHERE role='mechanik'")->fetch_assoc()['total'] ?? 0;
$totalOrders = $conn->query("SELECT COUNT(*) as total FROM odoslane")->fetch_assoc()['total'] ?? 0;

// Načítanie používateľov
$usersQuery = $conn->query("SELECT id, username, email, role FROM uzivatelia ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Torysa Servis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }
        .admin-main-wrapper { margin-left: 260px; transition: 0.3s; }
        .content-padding { padding: 40px; }
        .card-stat { border: none; border-radius: 20px; padding: 25px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: 0.3s; height: 100%; }
        .stat-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .card-modern { border: none; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); background: white; padding: 30px; }
        
        /* Štýly pre roly */
        .badge-role { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; border: none; }
        .role-admin { background-color: #dc3545; color: white; }
        .role-mechanik { background-color: #ffc107; color: #000; }
        .role-user { background-color: #0d6efd; color: white; }

        @media (max-width: 992px) { .admin-main-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

<?php include_once 'admin_dash_sidebar.php'; ?>

<div class="admin-main-wrapper">
    <div class="content-padding">
        <div class="page-header mb-5">
            <h2 class="fw-bold text-dark mb-1">Systémový prehľad</h2>
            <p class="text-muted">Vitajte späť, <strong><?= htmlspecialchars($user['username'] ?? 'Admin') ?></strong>.</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3"><div class="card-stat d-flex justify-content-between align-items-center"><div><h6 class="text-muted mb-1">Používatelia</h6><h3 class="fw-bold"><?= $totalUsers ?></h3></div><div class="stat-icon bg-primary text-white"><i class="fas fa-users"></i></div></div></div>
            <div class="col-md-3"><div class="card-stat d-flex justify-content-between align-items-center"><div><h6 class="text-muted mb-1">Admini</h6><h3 class="fw-bold"><?= $totalAdmins ?></h3></div><div class="stat-icon bg-danger text-white"><i class="fas fa-user-shield"></i></div></div></div>
            <div class="col-md-3"><div class="card-stat d-flex justify-content-between align-items-center"><div><h6 class="text-muted mb-1">Mechanici</h6><h3 class="fw-bold"><?= $totalMechs ?></h3></div><div class="stat-icon bg-warning text-dark"><i class="fas fa-wrench"></i></div></div></div>
            <div class="col-md-3"><div class="card-stat d-flex justify-content-between align-items-center"><div><h6 class="text-muted mb-1">Objednávky</h6><h3 class="fw-bold"><?= $totalOrders ?></h3></div><div class="stat-icon bg-info text-white"><i class="fas fa-tools"></i></div></div></div>
        </div>

        <div class="card card-modern">
            <h5 class="fw-bold mb-4"><i class="fas fa-list me-2 text-primary"></i> Zoznam registrovaných používateľov</h5>
            
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small uppercase">
                            <th>ID</th>
                            <th>Meno</th>
                            <th>E-mail</th>
                            <th>Rola</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($u = $usersQuery->fetch_assoc()): 
                        // Dynamické priradenie CSS triedy podľa roly
                        $roleClass = 'role-user'; // Predvolená
                        if ($u['role'] === 'admin') $roleClass = 'role-admin';
                        if ($u['role'] === 'mechanik') $roleClass = 'role-mechanik';
                    ?>
                        <tr>
                            <td class="text-muted small">#<?= $u['id'] ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge rounded-pill badge-role px-3 py-2 <?= $roleClass ?>">
                                    <?= strtoupper($u['role']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function(){
        $('#usersTable').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/sk.json" },
            "pageLength": 10,
            "responsive": true,
            "order": [[0, "desc"]]
        });
    });
</script>
</body>
</html>