<?php
include "connect_web.php";

// 1. Získame všetky fotky pre sekciu 'o-nas' naraz (limitujeme na 2)
$onas_res = $conn->query("SELECT * FROM galeria WHERE sekcia='o-nas' ORDER BY id DESC LIMIT 2");

// Rozdelíme ich do samostatných premenných
$img_hero = $onas_res->fetch_assoc(); // Prvá fotka (najnovšia)
$img_detail = $onas_res->fetch_assoc(); // Druhá fotka (staršia)

// Nastavíme cesty s poistkou
$hero_path = ($img_hero) ? $img_hero['cesta'] : 'obrazky/default-hero.jpg';
$detail_path = ($img_detail) ? $img_detail['cesta'] : 'obrazky/onas-detail.jpg';
include 'navbar.php';
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>O nás | Torysa Elite Service</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&family=Plus+Jakarta+Sans:wght@300;400;700;800&display=swap" rel="stylesheet">
    <style>
        /* ... tvoje štýly zostávajú rovnaké ... */
        :root { --accent: #3b82f6; --dark: #020617; --slate: #64748b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #ffffff; color: var(--dark); overflow-x: hidden; }
        .hero-viewport { height: 90vh; position: relative; background: var(--dark); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .hero-bg-media { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.4; object-fit: cover; }
        .hero-glass-box { position: relative; z-index: 5; padding: 60px; background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 40px; text-align: center; max-width: 900px; }
        .hero-tag { font-family: 'Space Grotesk', sans-serif; text-transform: uppercase; letter-spacing: 5px; color: var(--accent); font-weight: 700; display: block; margin-bottom: 20px; }
        .bento-grid { display: grid; grid-template-columns: repeat(4, 1fr); grid-template-rows: repeat(2, 300px); gap: 20px; margin-top: -100px; position: relative; z-index: 10; }
        .bento-item { background: white; border-radius: 30px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; transition: 0.4s cubic-bezier(0.23, 1, 0.32, 1); }
        .bento-item:hover { transform: scale(1.02); border-color: var(--accent); }
        .bento-1 { grid-column: 1 / 3; grid-row: 1 / 2; background: linear-gradient(45deg, #f8fafc, #ffffff); }
        .bento-2 { grid-column: 3 / 5; grid-row: 1 / 3; background: var(--dark); color: white; display: flex; flex-direction: column; justify-content: center; }
        .bento-3 { grid-column: 1 / 2; grid-row: 2 / 3; }
        .bento-4 { grid-column: 2 / 3; grid-row: 2 / 3; background: var(--accent); color: white; }
        .tech-section { padding: 150px 0; position: relative; }
        .floating-number { position: absolute; font-size: 20rem; font-weight: 900; color: #f1f5f9; z-index: -1; top: 0; left: -50px; line-height: 1; }
        .quality-line { height: 4px; width: 100%; background: #e2e8f0; border-radius: 2px; margin: 20px 0; position: relative; }
        .quality-fill { position: absolute; height: 100%; background: var(--accent); width: 95%; border-radius: 2px; }
        .master-cta { background: var(--dark); border-radius: 60px; padding: 100px 40px; margin-bottom: 50px; position: relative; overflow: hidden; }
        .glow { position: absolute; width: 300px; height: 300px; background: var(--accent); filter: blur(150px); opacity: 0.2; top: -100px; right: -100px; }
    </style>
</head>
<body>



<section class="hero-viewport">
    <img src="<?= $hero_path ?>" class="hero-bg-media">
    <div class="hero-glass-box shadow-lg">
        <span class="hero-tag">Torysa Autoservis</span>
        <h1 class="display-1 fw-800 text-white mb-4" style="letter-spacing: -2px;">Viac ako servis. <br><span class="text-primary">Inžinierstvo.</span></h1>
        <p class="text-white opacity-50 fs-5 mb-0">Budujeme vzťah medzi vami a vaším strojom na báze absolútnej precíznosti.</p>
    </div>
</section>

<div class="container">
    <div class="bento-grid">
        <div class="bento-item bento-1">
            <h3 class="fw-800">Naša Filozofia</h3>
            <p class="text-slate">Neriešime len následky, ale hľadáme príčinu. Každý skrutkový spoj uťahujeme momentovým kľúčom podľa noriem výrobcu.</p>
        </div>
        <div class="bento-item bento-2">
            <h2 class="text-white display-4 fw-800 mb-4">98.7%</h2>
            <p class="text-white fs-4">Miera úspešnosti diagnostiky hneď pri prvej návšteve.</p>
            <div class="quality-line"><div class="quality-fill"></div></div>
            <p class="text-white small opacity-50">Založené na 4500+ servisných úkonoch za rok 2025.</p>
        </div>
        <div class="bento-item bento-3 d-flex align-items-center justify-content-center">
            <i class=" fas fa-microchip fa-4x text-primary opacity-20"></i>
        </div>
        <div class="bento-item bento-4 d-flex flex-column justify-content-between">
            <h5 class="text-white fw-bold">High-Tech <br>Vybavenie</h5>
            <i class="text-white fas fa-arrow-right fa-2x"></i>
        </div>
    </div>
</div>

<section class="tech-section">
    <div class="floating-number">01</div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h6 class="text-primary fw-bold text-uppercase mb-3">Inovácie v praxi</h6>
                <h2 class="display-4 fw-800 mb-4">Digitálna stopa <br>vášho vozidla</h2>
                <p class="text-slate fs-5 mb-4">Každý úkon v našom servise je zaznamenaný v našom systéme.</p>
            </div>

            <div class="col-lg-6 text-center">
                <div class="position-relative">
                    <img src="<?= $detail_path ?>" class="img-fluid rounded-5 shadow-lg" alt="O nás reprezentatívna fotka" style="max-height: 500px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>