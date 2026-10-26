<?php
include "connect_web.php";
session_start();

if (isset($_SESSION['user'])) {
    $u_id = $_SESSION['user']['id'];
    $conn->query("UPDATE notifikacie SET precitane = 1 WHERE uzivatel_id = $u_id");
}

// Vráti užívateľa tam, kde bol
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>