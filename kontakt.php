<?php
include "connect_web.php";
// Navbar sa vloží automaticky (predpokladáme, že v navbar.php sú už tvoje nové tmavé štýly)
include 'navbar.php';

// Kontrola prihláseného užívateľa pre automatické vyplnenie
if (session_status() === PHP_SESSION_NONE) session_start();
$is_logged = isset($_SESSION['user']);
$user_data = $is_logged ? $_SESSION['user'] : null;
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Kontakt | Torysa Elite</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=Plus+Jakarta+Sans:wght@300;400;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --accent: #3b82f6;
            --dark-bg: #020617;
            --card-bg: rgba(15, 23, 42, 0.4);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark-bg);
            color: var(--text-main);
            overflow-x: hidden;
            background-image: radial-gradient(circle at 50% 0%, rgba(30, 41, 59, 0.7) 0%, var(--dark-bg) 70%);
        }

        .contact-wrapper {
            padding: 100px 0;
            position: relative;
            z-index: 1;
        }

        /* Sklenená karta - Glassmorphism */
        .glass-master-card {
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--border);
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
        }

        .hero-tag {
            text-transform: uppercase;
            letter-spacing: 4px;
            color: var(--accent);
            font-weight: 700;
            font-size: 0.75rem;
            display: block;
        }

        h2.display-5 {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 800;
            letter-spacing: -2px;
        }

        /* Moderné vstupy */
        .form-input-elite, .form-select-elite {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            padding: 16px 20px;
            border-radius: 18px;
            color: white;
            transition: 0.3s;
            width: 100%;
        }

        .form-input-elite:focus, .form-select-elite:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-select-elite option {
            background: #0f172a;
            color: white;
        }

        /* Smart Autofill Banner */
        .autofill-prompt {
            background: rgba(59, 130, 246, 0.1);
            border: 1px dashed var(--accent);
            padding: 15px 20px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .icon-magic {
            color: var(--accent);
            font-size: 1.2rem;
            margin-right: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }

        /* Akčné tlačidlá */
        .btn-elite-action {
            background: var(--accent);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 18px;
            font-weight: 800;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.4s;
            text-decoration: none;
        }

        .btn-elite-action:hover {
            filter: brightness(1.2);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-elite-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: white;
            padding: 18px 25px;
            border-radius: 18px;
            transition: 0.3s;
        }

        .btn-elite-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .icon-box {
            width: 48px; height: 48px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            margin-right: 15px;
        }

        /* Vizuálny efekt pre autofill */
        .animate-fill {
            animation: highlightFill 0.8s ease-out;
        }
        @keyframes highlightFill {
            0% { background: rgba(59, 130, 246, 0.2); }
            100% { background: rgba(255, 255, 255, 0.03); }
        }

        .price-input-wrapper { position: relative; }
        .price-input-wrapper i {
            position: absolute;
            right: 20px; top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
        }

        @media (max-width: 991px) {
            .border-end { border-right: none !important; border-bottom: 1px solid var(--border); }
        }
    </style>
</head>
<body>

<section class="contact-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="glass-master-card">
                    <div class="row g-0">

                        <div class="col-lg-4 p-5 d-flex flex-column justify-content-between border-end" style="border-color: var(--border) !important;">
                            <div>
                                <span class="hero-tag mb-3">Kontaktujte nás</span>
                                <h2 class="display-5 text-white mb-4">Máte <br><span class="text-primary">problém?</span></h2>
                                <p class="text-white opacity-50 mb-5">Naši experti analyzujú každú požiadavku s precíznosťou Torysa Service.</p>
                            </div>

                            <div class="contact-info">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-box"><i class="fas fa-location-dot"></i></div>
                                    <div>
                                        <small class="text-primary fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Kde nás nájdete</small>
                                        <p class="text-white mb-0 small">Torysa 219, Torysa</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box"><i class="fas fa-phone-volume"></i></div>
                                    <div>
                                        <small class="text-primary fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Zavolajte nám</small>
                                        <p class="text-white mb-0 small">+421 910 365 967</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 p-4 p-md-5">

                            <?php if ($is_logged): ?>
                                <div class="autofill-prompt">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-wand-magic-sparkles icon-magic"></i>
                                        <div class="small">
                                            <span class="text-white opacity-50 d-block">Identifikovali sme váš profil</span>
                                            <span class="text-white fw-bold">Vyplniť údaje pre <?= htmlspecialchars($user_data['username']) ?>?</span>
                                        </div>
                                    </div>
                                    <button type="button" id="btn-autofill" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">AUTOFILL</button>
                                </div>
                            <?php endif; ?>

                            <form action="kontakt_vypis.php" method="post">
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <select name="service" class="form-select-elite" required>
                                            <option value="" disabled selected>Zvoľte servisný úkon</option>
                                            <option value="Servis motora">Servis motora</option>
                                            <option value="Servis podvozku">Servis podvozku</option>
                                            <option value="Servis prevodoviek">Servis prevodoviek</option>
                                            <option value="Diagnostika">Diagnostika</option>
                                            <option value="Pneuservis">Pneuservis</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <input type="text" name="name" class="form-input-elite" placeholder="Meno" required>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <input type="text" name="l_name" class="form-input-elite" placeholder="Priezvisko" required>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <input type="email" name="email" class="form-input-elite" placeholder="E-mailová adresa" required>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <div class="price-input-wrapper">
                                            <input type="text" name="spz" class="form-input-elite" placeholder="ŠPZ vozidla (nepovinné)" style="text-transform: uppercase;">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <textarea name="about" class="form-input-elite" rows="4" placeholder="Opíšte nám symptómy vášho vozidla..." required></textarea>
                                    </div>

                                    <div class="col-md-12 mb-5">
                                        <div class="price-input-wrapper">
                                            <input type="number" step="0.01" name="price" class="form-input-elite pe-5" placeholder="Navrhovaný rozpočet" required>
                                            <i class="fas fa-euro-sign"></i>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="d-flex flex-column flex-md-row gap-3">
                                            <button type="submit" class="btn-elite-action flex-grow-1">
                                                <span>ODOSLAŤ REZERVÁCIU</span>
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                            <button type="submit" formaction="export.php" class="btn-elite-secondary" title="Exportovať do PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
    // 1. Logika pre zachytávanie parametra z URL
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const serviceParam = urlParams.get('service');
        if (serviceParam) {
            const selectElement = document.querySelector('select[name="service"]');
            if (selectElement) {
                for (let option of selectElement.options) {
                    let optionValueUrl = option.value.toLowerCase()
                        .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                        .replace(/\s+/g, '-');
                    if (optionValueUrl === serviceParam) {
                        option.selected = true;
                        break;
                    }
                }
            }
        }
    });

    // 2. SMART AUTOFILL Logika
    const autofillBtn = document.getElementById('btn-autofill');
    if (autofillBtn) {
        autofillBtn.addEventListener('click', function() {
            const userData = <?php echo json_encode($user_data); ?>;

            const nameField = document.querySelector('input[name="name"]');
            const lNameField = document.querySelector('input[name="l_name"]');
            const emailField = document.querySelector('input[name="email"]');

            if (userData && userData.username) {
                let userStr = userData.username.trim();
                let formattedName = userStr.replace(/([a-z0-9])([A-Z])/g, '$1 $2');
                let nameParts = formattedName.split(/\s+/);

                if (nameParts.length > 1) {
                    nameField.value = nameParts[0];
                    lNameField.value = nameParts.slice(1).join(' ');
                } else {
                    nameField.value = formattedName;
                    lNameField.focus();
                }
            }

            if (emailField && userData && userData.email) {
                emailField.value = userData.email;
            }

            // Vizuálny efekt
            [nameField, lNameField, emailField].forEach(el => {
                if (el && el.value !== "") {
                    el.classList.add('animate-fill');
                    el.style.borderColor = 'var(--accent)';
                    setTimeout(() => { el.classList.remove('animate-fill'); }, 1000);
                }
            });

            document.querySelector('.autofill-prompt').style.transition = '0.5s';
            document.querySelector('.autofill-prompt').style.opacity = '0';
            setTimeout(() => { document.querySelector('.autofill-prompt').style.display = 'none'; }, 500);
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>