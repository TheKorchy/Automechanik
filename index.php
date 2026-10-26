<?php
include 'connect_web.php';
include 'navbar.php';
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autoservis Torysa | Profesionálna starostlivosť</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #3b82f6;
            --dark-slate: #0f172a;
            --light-slate: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #1e293b; overflow-x: hidden; }

        /* Hero Sekcia s parallax efektom */
        #aiCarousel { height: 650px; }
        #aiCarousel .carousel-item { height: 650px; }
        #aiCarousel .carousel-item img {
            width: 100%; height: 100%; object-fit: cover;
            filter: brightness(0.5) contrast(1.1);
        }

        .hero-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 80%;
            z-index: 10;
        }

        /* Štatistiky - Countery */
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            transition: 0.3s;
            text-align: center;
        }
        .stat-card:hover { transform: translateY(-10px); }
        .stat-number { font-size: 2.5rem; font-weight: 800; color: var(--dark-slate); display: block; }
        .stat-label { color: var(--primary-blue); font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }

        /* Vylepšená sekcia O nás */
        .about-section { padding: 120px 0; background: white; }
        .image-container { position: relative; padding: 20px; }
        .main-img { border-radius: 30px; width: 100%; box-shadow: 0 30px 60px -12px rgba(0,0,0,0.25); }

        /* Dekoratívny prvok za obrázkom */
        .img-back-shape {
            position: absolute;
            top: 0; left: 0; width: 80%; height: 80%;
            background: var(--primary-blue);
            border-radius: 30px;
            z-index: -1;
            opacity: 0.1;
            transform: rotate(-5deg);
        }

        /* Služby mini-grid */
        .service-mini {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: var(--light-slate);
            border-radius: 16px;
            transition: 0.3s;
        }
        .service-mini:hover { background: #eff6ff; }
        .service-mini i {
            width: 45px; height: 45px;
            background: white;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            color: var(--primary-blue);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* CTA Modernizácia */
        .cta-gradient {
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            border-radius: 40px;
            padding: 80px 40px;
            margin-top: -50px;
            position: relative;
            z-index: 5;
        }

        .cta-btn-primary {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 16px;
            font-weight: 700;
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
            transition: 0.3s;
        }
        .cta-btn-primary:hover { transform: translateY(-3px); background: #2563eb; color: white; }

    </style>
</head>

<body>

<div id="aiCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php
        $result = $conn->query("SELECT * FROM galeria WHERE sekcia='index-carousel'");
        $active = true;
        while($row = $result->fetch_assoc()): ?>
            <div class="carousel-item <?= $active ? 'active' : '' ?>">
                <img src="<?= htmlspecialchars($row['cesta']) ?>">
                <div class="hero-overlay">
                    <h1 class="display-2 fw-800 text-white mb-4">Servis, ktorý <span class="text-primary">dýcha</span> kvalitou.</h1>
                    <p class="text-light fs-5 mx-auto mb-5 opacity-75" style="max-width: 600px;">
                        Torysa Servis prináša novú éru starostlivosti o vaše vozidlo. Rýchlo, poctivo a s úsmevom.
                    </p>
                    <a href="sluzby_opis.php" class="cta-btn-primary text-decoration-none">Pozrieť naše služby</a>
                </div>
            </div>
            <?php $active = false; endwhile; ?>
    </div>
</div>

<div class="container" style="margin-top: -60px; position: relative; z-index: 100;">
    <div class="row g-4 justify-content-center">
        <div class="col-md-3">
            <div class="stat-card">
                <span class="stat-number">2.5k+</span>
                <span class="stat-label">Opravených áut</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <span class="stat-number">100%</span>
                <span class="stat-label">Spokojnosť</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <span class="stat-number">12</span>
                <span class="stat-label">Expertov</span>
            </div>
        </div>
    </div>
</div>

<section class="about-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="image-container">
                    <div class="img-back-shape"></div>
                    <img src="obrazky/work-1.jpg" class="main-img" alt="Mechanik">
                </div>
            </div>
            <div class="col-lg-6 ps-lg-5">
                <h6 class="text-primary fw-bold text-uppercase mb-2">Prečo si vybrať nás?</h6>
                <h2 class="display-5 fw-800 mb-4">Váš partner na cestách <br>už celé desaťročie</h2>

                <div class="service-mini">
                    <i class="fas fa-microchip"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Najnovšia diagnostika</h6>
                        <small class="text-muted">Vieme presne určiť chybu bez zbytočných výdavkov.</small>
                    </div>
                </div>

                <div class="service-mini">
                    <i class="fas fa-tools"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Originálne diely</h6>
                        <small class="text-muted">Používame len overené komponenty pre dlhú životnosť.</small>
                    </div>
                </div>

                <div class="service-mini">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Online rezervácia</h6>
                        <small class="text-muted">Objednajte sa z pohodlia domova kedykoľvek.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container mb-5">
    <div class="cta-gradient text-center">
        <?php if (!$user): ?>
            <h2 class="text-white display-5 fw-bold mb-4">Ste pripravení na jazdu bez starostí?</h2>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="registracia.php" class="cta-btn-primary text-decoration-none">Vytvoriť bezplatný účet</a>
                <a href="kontakt.php" class="btn btn-outline-light px-5 py-3 border-2 fw-bold" style="border-radius: 16px;">Kontaktujte nás</a>
            </div>
        <?php else: ?>
            <h2 class="text-white display-5 fw-bold mb-4">Vitajte, <?= htmlspecialchars($user['username']) ?>!</h2>
            <p class="text-light opacity-50 mb-4">Skontrolujte stav vašej objednávky alebo si vytvorte novú.</p>
            <a href="<?= ($user['role'] === 'admin') ? 'dashboard.php' : 'profil.php' ?>" class="cta-btn-primary text-decoration-none">
                Vstúpiť do zákazníckej zóny
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>