<?php
session_start();
include "connect_web.php";

// Kontrola, či je užívateľ admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Prístup zamietnutý.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Tieto názvy (obj_id, stav, user_id) musia sedieť s tvojím formulárom v admin paneli
    $obj_id = $_POST['obj_id'] ?? null;
    $novy_stav = $_POST['stav'] ?? '';
    $user_id = $_POST['user_id'] ?? null;

    if ($obj_id && $user_id) {
        // 1. Aktualizácia stavu v tabuľke objednávok
        $stmt = $conn->prepare("UPDATE odoslane SET stav = ? WHERE id = ?");
        $stmt->bind_param("si", $novy_stav, $obj_id);

        if ($stmt->execute()) {
            // 2. Generovanie 4-riadkovej správy pre užívateľa
            $sprava = "📣 AKTUALIZÁCIA STAVU: #$obj_id\n";
            $sprava .= "Vaše vozidlo je momentálne v stave: **$novy_stav**.\n";
            $sprava .= "Naši technici Torysa Elite venujú maximálnu pozornosť detailom.\n";
            $sprava .= "O ďalšom postupe alebo dokončení vás budeme informovať.";

            // 3. Vloženie notifikácie do databázy
            $stmt_n = $conn->prepare("INSERT INTO notifikacie (uzivatel_id, sprava) VALUES (?, ?)");
            $stmt_n->bind_param("is", $user_id, $sprava);
            $stmt_n->execute();

            // Presmerovanie späť s úspechom
            header("Location: admin_panel.php?update=success");
            exit();
        } else {
            echo "Chyba pri aktualizácii: " . $conn->error;
        }
    } else {
        echo "Chýbajúce údaje.";
    }
}
?>