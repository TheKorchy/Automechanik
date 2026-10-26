<?php
include "connect_web.php";

// Inicializácia premenných
$service = $name = $l_name = $email = $about = $spz = "";
$price = 0;
$last_id = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Získanie dát z POST
    $service = $_POST['service'] ?? '';
    $name    = $_POST['name'] ?? '';
    $l_name  = $_POST['l_name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $about   = $_POST['about'] ?? '';
    $price   = $_POST['price'] ?? 0;
    $spz     = $_POST['spz'] ?? NULL; // Načítanie ŠPZ

    // 2. SQL INSERT - Pridaný stĺpec 'spz' a 'datum' nastavený na NULL
    // 'stav' nastavujeme na 'Čaká' ako predvolenú hodnotu
    $sql = "INSERT INTO odoslane 
            (service, name, l_name, email, about, price, spz, datum, stav) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NULL, 'Čaká')";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Chyba prípravy dopytu: " . $conn->error);
    }

    // 3. Bindovanie parametrov: "sssssds" 
    // (5x string, 1x double/cena, 1x string/spz)
    $stmt->bind_param(
        "sssssds", 
        $service, 
        $name, 
        $l_name, 
        $email, 
        $about, 
        $price, 
        $spz
    );

    if (!$stmt->execute()) {
        die("Chyba pri zápise do DB: " . $stmt->error);
    }

    $last_id = $stmt->insert_id; 
    $stmt->close();

} else {
    // Ak niekto vstúpi na stránku bez odoslania formulára, načítame posledný záznam
    $result = $conn->query("SELECT * FROM odoslane ORDER BY id DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $service = $row['service'];
        $name    = $row['name'];
        $l_name  = $row['l_name'];
        $email   = $row['email'];
        $about   = $row['about'];
        $price   = $row['price'];
        $spz     = $row['spz'];
        $last_id = $row['id'];
    } else {
        die("Žiadne údaje na zobrazenie.");
    }
}
?>
<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Potvrdenie odoslania | Torysa Elite</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');
        body { background: #020617; color: white; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); color: white; border-radius: 20px; }
        .text-primary { color: #3b82f6 !important; }
        .btn-success { background: #10b981; border: none; padding: 12px 25px; border-radius: 12px; font-weight: bold; transition: 0.3s; }
        .btn-success:hover { background: #059669; transform: translateY(-2px); }
    </style>
</head>
<body>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-5">
                    <i class="fas fa-check-circle text-primary" style="font-size: 4rem;"></i>
                    <h1 class="mt-3 fw-bold">Údaje boli úspešne uložené!</h1>
                    <p class="opacity-50">Vaša rezervácia bola zaznamenaná v systéme pod ID #<?php echo $last_id; ?>.</p>
                </div>

                <div class="card p-4 shadow-lg">
                    <div class="mb-3">
                        <small class="text-primary fw-bold">SERVISNÝ ÚKON</small>
                        <div class="fs-5"><?php echo htmlspecialchars($service); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-primary fw-bold">MENO</small>
                            <div><?php echo htmlspecialchars($name); ?></div>
                        </div>
                        <div class="col-6">
                            <small class="text-primary fw-bold">PRIEZVISKO</small>
                            <div><?php echo htmlspecialchars($l_name); ?></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-primary fw-bold">EMAIL</small>
                        <div><?php echo htmlspecialchars($email); ?></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-primary fw-bold">ŠPZ VOZIDLA</small>
                        <div class="fw-bold"><?php echo $spz ? htmlspecialchars($spz) : '<span class="opacity-50 fw-normal">Nezadané</span>'; ?></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-primary fw-bold">NAVRHOVANÁ CENA</small>
                        <div class="fs-4 fw-bold text-primary"><?php echo number_format($price, 2, ',', ' '); ?> €</div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-center mt-5">
                    <a href="kontakt.php" class="btn btn-outline-light px-4" style="border-radius: 12px;">Späť</a>

                    <a href="export.php?name=<?php echo urlencode($name); ?>&l_name=<?php echo urlencode($l_name); ?>&price=<?php echo urlencode($price); ?>&service=<?php echo urlencode($service); ?>&id=<?php echo $last_id; ?>"
                       class="btn btn-success">
                        <i class="fas fa-file-pdf me-2"></i>Exportovať do PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>