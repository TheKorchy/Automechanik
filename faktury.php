<?php
session_start();
include "connect_web.php";

// 1. OCHRANA PRÍSTUPU
if (!isset($_SESSION['user'])) {
    header("Location: prihlasenie.php");
    exit();
}

$user = $_SESSION['user'];
$isAdmin = ($user['role'] === 'admin');
$isMechanic = ($user['role'] === 'mechanik');

// Blokovanie bežných užívateľov
if (!$isAdmin && !$isMechanic) {
    echo "<!DOCTYPE html>
    <html lang='sk'>
    <head>
        <meta charset='UTF-8'>
        <title>Prístup zamietnutý</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; }
            .error-box { text-align: center; background: white; padding: 50px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #fee2e2; }
            h1 { color: #e11d48; font-weight: 800; letter-spacing: -1px; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1><i class='fas fa-lock'></i> Prístup zamietnutý</h1>
            <p class='text-muted mb-4'>Sekcia fakturácie je prístupná len pre administrátorov a mechanikov.</p>
            <a href='index.php' class='btn btn-primary rounded-pill px-4 fw-600'>Návrat na domov</a>
        </div>
    </body>
    </html>";
    exit();
}

include 'nav_admin.php';
include 'sidebar.php';

// SQL dopyt - admin aj mechanik vidia všetko pre potreby fakturácie
$query = "SELECT * FROM odoslane ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Evidencia faktúr | Torysa Servis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; margin: 0; }

        .content {
            margin-left: 280px;
            padding: 100px 40px 40px 40px;
            min-height: 100vh;
        }

        .table-container {
            background: white;
            padding: 30px;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 15px;
            border: none;
        }

        .table tbody td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; }

        .btn-download {
            background: #0f172a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-download:hover {
            background: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .price-tag {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 6px 12px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .content { margin-left: 0; padding-top: 80px; }
        }
    </style>
</head>
<body>

<div class="content">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-800 mb-1" style="letter-spacing: -1.5px; font-size: 2rem;">Evidencia faktúr</h2>
            <p class="text-muted small m-0 text-uppercase fw-600" style="letter-spacing: 1px;">Generovanie PDF dokladov pre zákazníkov</p>
        </div>
        <div>
            <?php if($isAdmin): ?>
                <span class="badge bg-dark rounded-pill px-3 py-2 shadow-sm"><i class="fa-solid fa-crown text-warning me-2"></i>Admin Panel</span>
            <?php else: ?>
                <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm"><i class="fa-solid fa-wrench me-2"></i>Mechanik</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-container">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Doklad</th>
                <th>Zákazník</th>
                <th>Služba / Zásah</th>
                <th>Suma k úhrade</th>
                <th class="text-end">Akcia</th>
            </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                    <td>
                        <div class="fw-700 text-dark"><?= htmlspecialchars($row['name'] . ' ' . ($row['l_name'] ?? '')) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                    </td>
                    <td>
                        <div class="fw-600 small"><?= htmlspecialchars($row['service']) ?></div>
                        <div class="text-muted small" style="font-size: 0.7rem;"><?= htmlspecialchars(substr($row['about'] ?? '', 0, 40)) ?>...</div>
                    </td>
                    <td>
                        <span class="price-tag"><?= number_format($row['price'] ?? 0, 2, ',', ' ') ?> €</span>
                    </td>
                    <td class="text-end">
                        <form action="generovat_fakturu.php" method="POST" style="display:inline;">
                            <input type="hidden" name="service" value="<?= htmlspecialchars($row['service']) ?>">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                            <input type="hidden" name="l_name" value="<?= htmlspecialchars($row['l_name'] ?? '') ?>">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($row['email']) ?>">
                            <input type="hidden" name="price" value="<?= $row['price'] ?? 0 ?>">
                            <input type="hidden" name="about" value="<?= htmlspecialchars($row['about'] ?? '') ?>">
                            <button type="submit" class="btn-download">
                                <i class="fas fa-file-pdf"></i> Generovať PDF
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>