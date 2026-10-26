<?php
include "connect_web.php";

if (!isset($_SESSION['user'])) {
    header("Location: prihlasenie.php");
    exit;
}

$user = $_SESSION['user'];
$email = $conn->real_escape_string($user['email']);

/* =========================
   USER ORDERS
   ========================= */
$orders = $conn->query("
    SELECT * FROM odoslane
    WHERE email='$email'
    ORDER BY id DESC
");

/* =========================
   ADMIN STATS
   ========================= */
if ($user['role'] === 'admin') {
    $stats = $conn->query("
        SELECT stav, COUNT(*) AS pocet
        FROM odoslane
        GROUP BY stav
    ");
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Moje objednávky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4 text-center">🧾 Moje objednávky</h2>

    <!-- 🔔 INFO -->
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success text-center">
            Objednávka bola úspešne upravená.
        </div>
    <?php endif; ?>

    <!-- 📊 ADMIN ŠTATISTIKY -->
    <?php if ($user['role'] === 'admin'): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">📊 Štatistiky objednávok</h5>
                <div class="row">
                    <?php while ($s = $stats->fetch_assoc()): ?>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center bg-light">
                                <strong><?= $s['stav'] ?? 'Čaká' ?></strong>
                                <div class="fs-4"><?= $s['pocet'] ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 🧾 OBJEDNÁVKY -->
    <?php if ($orders->num_rows === 0): ?>
        <div class="alert alert-info text-center">
            Nemáš žiadne objednávky.
        </div>
    <?php else: ?>
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Služba</th>
                <th>Dátum</th>
                <th>Stav</th>
                <th>Akcie</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($o = $orders->fetch_assoc()): ?>
                <?php
                $stav = $o['stav'] ?? 'Čaká';
                $badge = match ($stav) {
                    'Čaká' => 'secondary',
                    'V riešení' => 'warning',
                    'Dokončené' => 'success',
                    'Zrušené' => 'danger',
                    default => 'secondary'
                };
                ?>
                <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['service']) ?></td>
                    <td><?= $o['datum'] ?></td>
                    <td>
                        <span class="badge bg-<?= $badge ?>">
                            <?= $stav ?>
                        </span>
                    </td>
                    <td>
                        <a href="order_detail.php?id=<?= $o['id'] ?>"
                           class="btn btn-sm btn-info">🧾 Detail</a>

                        <?php if ($stav === 'Čaká'): ?>
                            <a href="edit_order_user.php?id=<?= $o['id'] ?>"
                               class="btn btn-sm btn-warning">✏️ Upraviť</a>
                        <?php else: ?>
                            <span class="text-muted">Uzamknuté</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-outline-primary">⬅ Späť na web</a>
    </div>

</div>
</body>
</html>
