<?php
include "connect_web.php";


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$id = (int)($_GET['id'] ?? 0);

$res = $conn->prepare("SELECT * FROM odoslane WHERE id=? AND email=?");
$res->bind_param("is", $id, $user['email']);
$res->execute();
$order = $res->get_result()->fetch_assoc();

if (!$order) die("Objednávka neexistuje");
if (($order['stav'] ?? 'Čaká') !== 'Čaká') die("Objednávku už nemožno upraviť");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("
        UPDATE odoslane
        SET service=?, name=?, l_name=?, about=?
        WHERE id=?
    ");
    $stmt->bind_param(
            "ssssi",
            $_POST['service'],
            $_POST['name'],
            $_POST['l_name'],
            $_POST['about'],
            $id
    );
    $stmt->execute();
    header("Location: my_orders.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Upraviť objednávku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card mx-auto" style="max-width:600px">
        <div class="card-body">

            <h4 class="mb-3">✏️ Úprava objednávky #<?= $order['id'] ?></h4>

            <form method="post">
                <input class="form-control mb-2" name="service" value="<?= htmlspecialchars($order['service']) ?>" required>
                <input class="form-control mb-2" name="name" value="<?= htmlspecialchars($order['name']) ?>" required>
                <input class="form-control mb-2" name="l_name" value="<?= htmlspecialchars($order['l_name']) ?>" required>
                <textarea class="form-control mb-3" name="about" required><?= htmlspecialchars($order['about']) ?></textarea>
                <button class="btn btn-success w-100">Uložiť</button>
            </form>

        </div>
    </div>
</div>
</body>
</html>
