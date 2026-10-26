<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autoservis";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Chyba pripojenia: " . $conn->connect_error);
}

// Zachytenie údajov z POST
$service  = isset($_POST['service']) ? $_POST['service'] : '';
$name     = isset($_POST['name']) ? $_POST['name'] : '';
$l_name   = isset($_POST['l_name']) ? $_POST['l_name'] : '';
$email    = isset($_POST['email']) ? $_POST['email'] : '';
$about    = isset($_POST['about']) ? $_POST['about'] : '';
$price    = isset($_POST['price']) ? $_POST['price'] : '';

// Uloženie do DB
if (!empty($service) && !empty($name) && !empty($l_name) && !empty($email) && !empty($about) && !empty($price)) {
    $sql = "INSERT INTO odoslane (service, name, l_name, email, about, price) VALUES (?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssd", $service, $name, $l_name, $email, $about, $price);
    $stmt->execute();
    $stmt->close();
}

// Získanie posledného záznamu
$sql = "SELECT service, name, l_name, email, about, price 
        FROM odoslane ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row    = $result->fetch_assoc();
    $service= $row['service'];
    $name   = $row['name'];
    $l_name = $row['l_name'];
    $email  = $row['email'];
    $about  = $row['about'];
    $price  = $row['price'];
} else {
    $service = $name = $l_name = $email = $about = $price = "N/A";
}

$conn->close();
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Potvrdenie odoslania</title>
    <link rel="stylesheet" href="styly.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <h1 class="text-center mb-4 text-primary">Údaje boli odoslané!</h1>
                <div class="card p-4 shadow">
                    <h5>Kategória: <span class="text-primary"><?php echo htmlspecialchars($service); ?></span></h5>
                    <p><strong>Meno:</strong> <?php echo htmlspecialchars($name); ?></p>
                    <p><strong>Priezvisko:</strong> <?php echo htmlspecialchars($l_name); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                    <p><strong>Správa:</strong> <?php echo nl2br(htmlspecialchars($about)); ?></p>
                    <p><strong>Navrhovaná cena:</strong> <?php echo htmlspecialchars($price); ?> €</p>
                </div>

                <div class="text-center mt-4">
                    <a href="Kontakt.html" class="btn btn-primary">Späť na formulár</a>
                    <a href="export.php" class="btn btn-success">Exportovať do PDF</a>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
