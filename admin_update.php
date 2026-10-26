<?php
include "connect_web.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_SESSION['user']['role'] === 'admin') {
    $obj_id = $_POST['obj_id'];
    $novy_stav = $_POST['stav'];
    $user_id = $_POST['user_id']; // ID užívateľa, ktorému patrí auto

    // 1. Zmena stavu objednávky
    $stmt = $conn->prepare("UPDATE objednavky SET stav = ? WHERE id = ?");
    $stmt->bind_param("si", $novy_stav, $obj_id);

    if ($stmt->execute()) {
        // 2. Vytvorenie 4-riadkovej správy
        $sprava = "Aktualizácia stavu vašej objednávky č. #$obj_id\n";
        $sprava .= "Vozidlo bolo presunuté do stavu: **$novy_stav**.\n";
        $sprava .= "Naši experti Torysa Elite pokračujú v práci podľa plánu.\n";
        $sprava .= "O ďalších zmenách vás budeme čoskoro informovať.";

        $n_stmt = $conn->prepare("INSERT INTO notifikacie (uzivatel_id, sprava) VALUES (?, ?)");
        $n_stmt->bind_param("is", $user_id, $sprava);
        $n_stmt->execute();

        header("Location: admin_panel.php?success=1");
    }
}
?>