<?php
session_start();
include "connect_web.php";

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- A. RESET: Všetko z daného mesiaca do Fronty ---
if ($action === 'reset') {
    $m = intval($_POST['month']);
    $y = intval($_POST['year']);
    $stmt = $conn->prepare("UPDATE odoslane SET datum = '', stav = 'Čaká' WHERE MONTH(datum) = ? AND YEAR(datum) = ?");
    $stmt->bind_param("ii", $m, $y);
    $stmt->execute();
    header("Location: kalendar.php?m=$m&y=$y");
    exit;
}

// --- B. AUTO-PLÁN: Inteligentné rozdelenie podľa kapacity ---
if ($action === 'autoplan') {
    $limit = intval($_POST['max_per_day']);
    $start_date = $_POST['date_from'];
    $end_date = $_POST['date_to'];

    // Načítame len čakajúce objednávky (zoradené od najstaršej)
    $unplanned = $conn->query("SELECT id FROM odoslane WHERE (datum IS NULL OR datum = '' OR datum = '0000-00-00') ORDER BY id ASC");

    $current = strtotime($start_date);
    $last = strtotime($end_date);

    while ($order = $unplanned->fetch_assoc()) {
        $found = false;
        while ($current <= $last) {
            $day_of_week = date("N", $current);

            if ($day_of_week > 5) { // Preskočiť sobotu a nedeľu
                $current = strtotime("+1 day", $current);
                continue;
            }

            $check_date = date("Y-m-d", $current);
            $res_count = $conn->query("SELECT COUNT(*) as c FROM odoslane WHERE datum = '$check_date'");
            $count = $res_count->fetch_assoc()['c'];

            if ($count < $limit) {
                $oid = $order['id'];
                $conn->query("UPDATE odoslane SET datum = '$check_date', stav = 'V procese' WHERE id = $oid");
                $found = true;
                break; // Našli sme deň pre toto auto, ideme na ďalšie auto
            } else {
                $current = strtotime("+1 day", $current); // Plno, skúsime ďalší deň
            }
        }
        if (!$found) break; // Ak sme prešli celé obdobie a niet miesta, končíme
    }
    header("Location: kalendar.php?m=" . date('m', strtotime($start_date)) . "&y=" . date('Y', strtotime($start_date)));
    exit;
}

// --- C. ASSIGN: Uloženie po Drag & Drop ---
if ($action === 'assign') {
    $id = intval($_POST['order_id']);
    $date = $_POST['planned_date'];
    $stmt = $conn->prepare("UPDATE odoslane SET datum = ?, stav = 'V procese' WHERE id = ?");
    $stmt->bind_param("si", $date, $id);
    echo json_encode(["success" => $stmt->execute()]);
    exit;
}

// --- D. GET DETAILS: Dáta pre Modal ---
if ($action === 'get_details') {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM odoslane WHERE id = $id");
    echo json_encode($res->fetch_assoc());
    exit;
}