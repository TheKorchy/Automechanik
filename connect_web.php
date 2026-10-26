<?php
$servername = "172.17.0.1:3306";  //server
$username = "korchnak"; // meno do databázy
$password = "Servis852!";     // Heslo do databázy
$dbname = "korchnak";   //databáza

// Vytvorenie pripojenia
$conn = new mysqli($servername, $username, $password, $dbname);

// Kontrola pripojenia
if ($conn->connect_error) {
    die("Pripojenie zlyhalo: " . $conn->connect_error);
}

?>