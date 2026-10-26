<?php
include "connect_web.php";
$user = $_SESSION['user'] ?? null;

// SPRACOVANIE OBJEDNÁVKY (pôvodne zo services.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user && isset($_POST['poslat_objednavku'])) {
    $service = $_POST['service'];
    $datum = $_POST['datum'];
    $u_id = $user['id'];
    $meno = $user['username']; // alebo $user['name'] ak ho máš v DB
    $email = $user['email'];

    $stmt = $conn->prepare("INSERT INTO odoslane (user_id, service, name, email, datum, stav) VALUES (?, ?, ?, ?, ?, 'Čaká')");
    $stmt->bind_param("issss", $u_id, $service, $meno, $email, $datum);

    if ($stmt->execute()) {
        header("Location: sluzby.php?success=1");
        exit;
    }
}
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>Naše Služby - Autoservis Torysa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styly.css">
    <style>
        /* Tvoj štýlový dizajn zo sluzby_opis.php */
        body { background-color: #000; color: white; }
        .services-container { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; padding: 50px 0; }
        .service { background: #08263b; border: 1px solid #144a7a; border-radius: 14px; padding: 30px; flex: 1 1 200px; max-width: 240px; text-align: center; cursor: pointer; transition: 0.4s; }
        .service:hover { transform: scale(1.05); box-shadow: 0 0 20px rgba(0,140,255,0.5); }
        .service i { font-size: 2.5rem; color: #00aaff; margin-bottom: 15px; }
        .preview-box { margin-top: 30px; display: none; text-align: center; }
        .preview-box.visible { display: block; }
        .image-frame { width: 500px; height: 300px; margin: 0 auto; border-radius: 12px; overflow: hidden; border: 2px solid #00aaff; }
        .image-frame img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="container py-5">
    <h1 class="text-center fw-bold mb-5">Naše Profesionálne Služby</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">Objednávka bola úspešne odoslaná!</div>
    <?php endif; ?>

    <div class="services-container">
        <?php
        $services_list = [
                "servis-motora" => ["Motory", "fa-engine"],
                "servis-podvozku" => ["Podvozky", "fa-car-side"],
                "servis-prevodoviek" => ["Prevodovky", "fa-gears"],
                "diagnostika" => ["Diagnostika", "fa-microchip"],
                "pneuservis" => ["Pneuservis", "fa-circle-dot"]
        ];

        foreach ($services_list as $key => $info) {
            $res = $conn->query("SELECT cesta FROM galeria WHERE sekcia='$key' LIMIT 1");
            $img = ($res && $res->num_rows > 0) ? $res->fetch_assoc()['cesta'] : "uploads/default.jpg";
            ?>
            <div class="service"
                 data-service-id="<?= $key ?>"
                 data-name="<?= $info[0] ?>"
                 data-img="<?= $img ?>"
                 data-bs-toggle="modal"
                 data-bs-target="#orderModal">
                <i class="fas <?= $info[1] ?>"></i>
                <h3><?= $info[0] ?></h3>
                <p class="text-info">Klikni pre objednanie</p>
            </div>
        <?php } ?>
    </div>

    <div class="preview-box" id="servicePreview">
        <div class="image-frame"><img id="preview-img" src=""></div>
        <h4 id="preview-title" class="mt-3"></h4>
    </div>
</section>

<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content bg-dark text-white border-info">
            <div class="modal-header border-info">
                <h5 class="modal-title">Objednávka: <span id="modalServiceName"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if ($user): ?>
                    <input type="hidden" name="service" id="modalServiceInput">
                    <div class="mb-3">
                        <label>Vaše Meno</label>
                        <input type="text" class="form-control bg-secondary text-white" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Dátum termínu</label>
                        <input type="date" name="datum" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit" name="poslat_objednavku" class="btn btn-info w-100">Potvrdiť rezerváciu</button>
                <?php else: ?>
                    <div class="text-center">
                        <p>Pre objednanie sa musíte najprv prihlásiť.</p>
                        <a href="prihlasenie.php" class="btn btn-primary">Prihlásiť sa</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const orderModal = document.getElementById('orderModal');
    orderModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const serviceId = button.getAttribute('data-service-id');
        const serviceName = button.getAttribute('data-name');

        document.getElementById('modalServiceName').textContent = serviceName;
        document.getElementById('modalServiceInput').value = serviceName;
    });

    // Efekt náhľadu pri hoveri (ak chceš zachovať tú vychytávku)
    document.querySelectorAll('.service').forEach(s => {
        s.addEventListener('mouseenter', () => {
            document.getElementById('servicePreview').classList.add('visible');
            document.getElementById('preview-img').src = s.getAttribute('data-img');
            document.getElementById('preview-title').textContent = s.getAttribute('data-name');
        });
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>