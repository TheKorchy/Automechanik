<?php
session_start();
include "connect_web.php";

if (!isset($_SESSION['user'])) exit;

$user = $_SESSION['user'];
$id = (int)$_GET['id'];

$q = $conn->query("SELECT * FROM odoslane WHERE id=$id");
$order = $q->fetch_assoc();
if (!$order) die("Neexistuje");

if ($user['role']==='user' && $order['email']!==$user['email']) die("Prístup zakázaný");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detail objednávky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card">
        <div class="card-body">

            <h4>🧾 Objednávka #<?= $order['id'] ?></h4>
            <p><b>Služba:</b> <?= $order['service'] ?></p>
            <p><b>Stav:</b> <?= $order['stav'] ?? 'Čaká' ?></p>
            <p><b>Popis:</b> <?= $order['about'] ?></p>

        </div>
    </div>
</div>
</body>
</html>
