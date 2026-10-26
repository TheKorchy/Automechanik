<?php
session_start();
include "connect_web.php";

// Overenie prihlásenia (bezpečnosť)
if (!isset($_SESSION['user'])) {
    die(json_encode(["success" => false, "message" => "Neprihlásený užívateľ"]));
}

// Zjednotenie vstupu pre POST aj GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Pomocná funkcia na odosielanie vnútro-systémových notifikácií zákazníkom
function sendNotification($conn, $order_id, $date) {
    $res = $conn->query("SELECT email, service FROM odoslane WHERE id = $order_id");
    if ($res && $row = $res->fetch_assoc()) {
        $email = $row['email'];
        $service = mysqli_real_escape_string($conn, $row['service']);
        $prettyDate = date("d.m.Y", strtotime($date));
        
        // Nájdeme ID užívateľa podľa emailu
        $uRes = $conn->query("SELECT id FROM uzivatelia WHERE email = '$email'");
        if ($uRes && $uRow = $uRes->fetch_assoc()) {
            $u_id = $uRow['id'];
            $msg = "Vaša objednávka #$order_id ($service) bola naplánovaná na termín: $prettyDate.";
            $conn->query("INSERT INTO notifikacie (uzivatel_id, sprava, precitane, datum) VALUES ($u_id, '$msg', 0, NOW())");
        }
    }
}

// --- 1. ASSIGN: Drag & Drop z kalendára alebo do fronty ---
if ($action === 'assign') {
    $id = intval($_POST['order_id']);
    $date = $_POST['planned_date'];

    if ($date && $date !== "null" && $date !== "" && $date !== "undefined") {
        // PLÁNOVANIE: Nastavíme dátum a stav V procese
        $stmt = $conn->prepare("UPDATE odoslane SET datum = ?, stav = 'V procese' WHERE id = ?");
        $stmt->bind_param("si", $date, $id);
        if ($stmt->execute()) {
            sendNotification($conn, $id, $date);
            echo json_encode(["success" => true]);
        }
    } else {
        // ODPLÁNOVANIE (späť do fronty): Nastavíme prázdny dátum a stav Čaká
        // Týmto zabránime, aby MySQL dosadilo dnešný dátum
        $stmt = $conn->prepare("UPDATE odoslane SET datum = '0000-00-00', stav = 'Čaká' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "status" => "unplanned"]);
        }
    }
    exit;
}

// --- 2. AUTO_DISTRIBUTE / AUTOPLAN: Rozdelenie z fronty na vybrané dni ---
if ($action === 'auto_distribute' || $action === 'autoplan') {
    $days = $_POST['selected_days'] ?? []; // Pole dátumov, ktoré si v kalendári zaklikol
    $limit = intval($_POST['limit_per_day'] ?? $_POST['max_per_day'] ?? 1);

    if (empty($days)) {
        die(json_encode(["success" => false, "message" => "Žiadne dni neboli vybrané"]));
    }

    // Načítame objednávky, ktoré sú vo fronte (nemajú dátum)
    $res = $conn->query("SELECT id FROM odoslane WHERE (datum IS NULL OR datum = '' OR datum = '0000-00-00') ORDER BY id ASC");
    $queue = [];
    while($row = $res->fetch_assoc()) { $queue[] = $row['id']; }

    if (empty($queue)) {
        die(json_encode(["success" => false, "message" => "Fronta je prázdna"]));
    }

    $idx = 0;
    $countCalculated = 0;
    foreach ($days as $date) {
        for ($i = 0; $i < $limit; $i++) {
            if (isset($queue[$idx])) {
                $orderId = $queue[$idx];
                $conn->query("UPDATE odoslane SET datum = '$date', stav = 'V procese' WHERE id = $orderId");
                sendNotification($conn, $orderId, $date);
                $idx++;
                $countCalculated++;
            } else { 
                break 2; 
            }
        }
    }
    echo json_encode(["success" => true, "message" => "Rozdelené: $countCalculated"]);
    exit;
}

// --- 3. BULK_CLEAR: Vymazanie všetkých termínov z vybraných dní ---
if ($action === 'bulk_clear') {
    $days = $_POST['selected_days'] ?? [];
    foreach ($days as $date) {
        $date = mysqli_real_escape_string($conn, $date);
        // Vrátime objednávky z daného dňa do fronty
        $conn->query("UPDATE odoslane SET datum = '0000-00-00', stav = 'Čaká' WHERE datum = '$date'");
    }
    echo json_encode(["success" => true]);
    exit;
}

// --- 4. RESET: Všetko z daného mesiaca späť do fronty ---
if ($action === 'reset') {
    $m = intval($_POST['month']);
    $y = intval($_POST['year']);
    $stmt = $conn->prepare("UPDATE odoslane SET datum = '0000-00-00', stav = 'Čaká' WHERE MONTH(datum) = ? AND YEAR(datum) = ?");
    $stmt->bind_param("ii", $m, $y);
    $stmt->execute();
    header("Location: kalendar.php?m=$m&y=$y");
    exit;
}

// --- 5. GET DETAILS: Pre zobrazenie v modálnom okne ---
if ($action === 'get_details') {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM odoslane WHERE id = $id");
    echo json_encode($res->fetch_assoc());
    exit;
}
?>