<?php
include "connect_web.php";


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Ak ID neprichádza, zobrazíme zoznam objednávok na výber
$id = $_GET['id'] ?? null;

if ($id && is_numeric($id) && intval($id) > 0) {
    $id = intval($id);

    $stmt = $conn->prepare("SELECT * FROM odoslane WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        die("<h3 style='color:red;'>Objednávka s ID #$id neexistuje.</h3>");
    }

    $order = $res->fetch_assoc();

    // Spracovanie formulára
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stav = $_POST['stav'];

        $stmt = $conn->prepare("UPDATE odoslane SET stav = ? WHERE id = ?");
        $stmt->bind_param("si", $stav, $id);
        $stmt->execute();

        sendOrderStatusMail($order['email'], $id, $stav);

        header("Location: admin_panel.php?stav=ok");
        exit;
    }

    $showForm = true;

} else {
    // ID neprichádza → zobrazíme zoznam všetkých objednávok
    $orders = $conn->query("SELECT id, service, name, l_name, email, about, stav FROM odoslane ORDER BY id DESC");
    $showForm = false;
}

// Funkcia na odoslanie HTML emailu
function sendOrderStatusMail($to, $orderId, $stav) {
    $subject = "Zmena stavu objednávky #$orderId";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial; background:#f4f4f4; }
            .box { background:#fff; padding:20px; border-radius:6px; }
            .status { font-size:18px; font-weight:bold; }
        </style>
    </head>
    <body>
        <div class='box'>
            <h2>Autoservis Torysa</h2>
            <p>Vaša objednávka <strong>#{$orderId}</strong> má nový stav:</p>
            <p class='status'>🔔 {$stav}</p>
            <hr>
            <p>Ďakujeme za dôveru.</p>
        </div>
    </body>
    </html>
    ";
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Autoservis <noreply@autoservis.sk>\r\n";

    mail($to, $subject, $message, $headers);
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Zmena stavu objednávky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">

    <?php if ($showForm): ?>
        <div class="card mx-auto" style="max-width:500px">
            <div class="card-body">
                <h4>🔧 Stav objednávky #<?= htmlspecialchars($order['id']) ?></h4>
                <form method="post">
                    <select name="stav" class="form-select mb-3">
                        <option <?= ($order['stav']=='Čaká')?'selected':'' ?>>Čaká</option>
                        <option <?= ($order['stav']=='V riešení')?'selected':'' ?>>V riešení</option>
                        <option <?= ($order['stav']=='Dokončené')?'selected':'' ?>>Dokončené</option>
                    </select>
                    <button class="btn btn-primary w-100">Uložiť</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <h3>Vyberte objednávku na úpravu:</h3>
        <table class="table table-bordered mt-3">
            <thead>
            <tr>
                <th>ID</th>
                <th>Meno</th>
                <th>Email</th>
                <th>Stav</th>
                <th>Akcia</th>
            </tr>
            </thead>
            <tbody>
            <?php while($row = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['stav']) ?></td>
                    <td>
                        <a href="edit_order_admin.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Upraviť</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
</body>
</html>
