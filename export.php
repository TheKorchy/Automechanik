<?php
require('fpdf/fpdf.php');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autoservis";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Chyba pripojenia: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM odoslane ORDER BY created_at DESC");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Zoznam odoslanych kontaktov',0,1,'C');
$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(30,10,'Služba',1);
$pdf->Cell(40,10,'Meno',1);
$pdf->Cell(50,10,'Email',1);
$pdf->Cell(30,10,'Cena',1);
$pdf->Cell(40,10,'Vytvorene',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);
while($row = $result->fetch_assoc()) {
    $pdf->Cell(30,10,$row['service'],1);
    $pdf->Cell(40,10,$row['meno'],1);
    $pdf->Cell(50,10,$row['email'],1);
    $pdf->Cell(30,10,$row['price'].' €',1);
    $pdf->Cell(40,10,$row['created_at'],1);
    $pdf->Ln();
}

// Stiahne PDF (nie otvorí v browseri)
$pdf->Output('D','odoslane.pdf');

$conn->close();
?>
