<?php
include "connect_web.php";
session_start();

$user = $_SESSION['user'] ?? null;

$services = [
    "Servis motora",
    "Servis podvozku",
    "Servis prevodoviek",
    "Diagnostika",
    "Pneuservis"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {

    $service = $_POST['service'];
    $datum = $_POST['datum'];

    $stmt = $conn->prepare("
        INSERT INTO odoslane 
        (user_id, service, name, l_name, email, datum, stav)
        VALUES (?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issssss",
        $user['id'],
        $service,
        $user['name'],
        $user['l_name'],
        $user['email'],
        $datum,
        $stav = 'Čaká'
    );

    $stmt->execute();
    header("Location: services.php?ok=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Služby</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php
include 'navbar.php';
?>
<body class="bg-light">

<div class="container py-5">

    <h2 class="text-center mb-4">🛠️ Naše služby</h2>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success text-center">
            Objednávka bola odoslaná ✔
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <?php foreach ($services as $s): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= $s ?></h5>
                        <p class="card-text">
                            Profesionálny servis vozidla.
                        </p>

                        <?php if ($user): ?>
                            <button
                                class="btn btn-primary w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#objednavkaModal"
                                data-service="<?= $s ?>">
                                Objednať
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline-secondary w-100">
                                Prihlás sa
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="objednavkaModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🧾 Objednávka</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="service" id="serviceInput">

                <div class="mb-2">
                    <label>Meno</label>
                    <input class="form-control" value="<?= $user['name'] ?>" disabled>
                </div>

                <div class="mb-2">
                    <label>Priezvisko</label>
                    <input class="form-control" value="<?= $user['l_name'] ?>" disabled>
                </div>

                <div class="mb-2">
                    <label>Email</label>
                    <input class="form-control" value="<?= $user['email'] ?>" disabled>
                </div>

                <div class="mb-2">
                    <label>Dátum</label>
                    <input type="date" name="datum" class="form-control" required>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success w-100">
                    Odoslať objednávku
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modal = document.getElementById('objednavkaModal')
    modal.addEventListener('show.bs.modal', e => {
        document.getElementById('serviceInput').value =
            e.relatedTarget.getAttribute('data-service')
    })
</script>

</body>
</html>
