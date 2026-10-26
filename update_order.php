<?php
include "connect_web.php";

$id = intval($_POST['id']);
$stav = $_POST['stav'];

$stmt = $conn->prepare("SELECT email FROM odoslane WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();

$stmt = $conn->prepare("UPDATE odoslane SET stav=? WHERE id=?");
$stmt->bind_param("si",$stav,$id);
$stmt->execute();

mail($order['email'], "Zmena stavu objednávky #$id", "Vaša objednávka #$id má nový stav: $stav");
echo json_encode(['success'=>true]);
