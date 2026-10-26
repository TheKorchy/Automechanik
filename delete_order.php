<?php
include "connect_web.php";

if (!isset($_SESSION['user'])) header("Location: prihlasenie.php");

$user = $_SESSION['user'];
$id = intval($_GET['id'] ?? 0);

$result = $conn->query("SELECT * FROM odoslane WHERE id=$id");
if ($result->num_rows === 0) die("Objednávka nenájdená");
$order = $result->fetch_assoc();

if ($user['role'] === 'user' && $order['email'] !== $user['email']) die("Nemáš prístup");

$conn->query("DELETE FROM odoslane WHERE id=$id");
header("Location: profil.php");
exit();
