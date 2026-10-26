<?php
session_start();
include "connect_web.php";


if (!isset($_SESSION['user'])) {
    header("Location: prihlasenie.php");
    exit();
}
$user = $_SESSION['user'];
$email = $user['email'];

// Definícia rolí
$isAdmin = ($user['role'] === 'admin');
$isMechanic = ($user['role'] === 'mechanik');

// 2. BLOKOVANIE ZÁKAZNÍKA - Ak nie je admin ani mechanik, nepustíme ho ďalej
if (!$isAdmin && !$isMechanic) {
    echo "<!DOCTYPE html>
    <html lang='sk'>
    <head>
        <meta charset='UTF-8'>
        <title>Prístup zamietnutý</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; }
            .error-box { text-align: center; background: white; padding: 50px; border-radius: 20px; shadow: 0 10px 30px rgba(0,0,0,0.1); }
            h1 { color: #e11d48; font-weight: 800; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1><i class='fas fa-lock'></i> Prístup zamietnutý</h1>
            <p class='text-muted'>Ľutujeme, ale táto sekcia je vyhradená len pre personál servisu.</p>
            <a href='index.php' class='btn btn-primary rounded-pill px-4'>Vrátiť sa na domovskú stránku</a>
        </div>
    </body>
    </html>";
    exit(); // Zastavíme vykonávanie zvyšku skriptu
}

include 'nav_admin.php';

$vin_data = null;
$error = null;

if (isset($_POST['vin'])) {
    $vin = strtoupper(trim($_POST['vin']));

    // Základná validácia VIN (I, O, Q sú zakázané)
    if (strlen($vin) !== 17) {
        $error = "VIN musí mať presne 17 znakov.";
    } elseif (preg_match('/[IOQ]/', $vin)) {
        $error = "VIN nesmie obsahovať znaky I, O alebo Q.";
    } else {
        // API od NHTSA
        $url = "https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/" . $vin . "?format=json";

        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents($url, false, $ctx);

        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['Results'])) {
                $vin_data = [];
                foreach ($data['Results'] as $result) {
                    // Uložíme hodnoty, ktoré nie sú prázdne alebo "Null"
                    if (!empty($result['Value']) && $result['Value'] !== "Not Applicable" && $result['Value'] !== "Null") {
                        $vin_data[$result['Variable']] = $result['Value'];
                    }
                }
            }
        } else {
            $error = "Server neodpovedá. Skontrolujte pripojenie k internetu.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIN Decoder | Autoservis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            margin: 0;
        }

        /* Ak používaš sidebar, tento margin zabezpečí, aby obsah nebol pod ním */
        .content-wrapper {
            margin-left: 260px;
            padding: 0;
            transition: 0.3s;
        }

        .page-header {
            background: #ffffff;
            padding: 40px 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 40px;
        }

        .vin-card {
            background: white;
            padding: 35px;
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        }

        .vin-input {
            font-family: 'Monaco', 'Consolas', monospace;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-size: 1.3rem;
            border-radius: 16px;
            padding: 18px 25px;
            border: 2px solid #e2e8f0;
            transition: 0.3s;
        }

        .vin-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .result-group {
            background: #f8fafc;
            border-radius: 18px;
            padding: 20px;
            height: 100%;
        }

        .result-item {
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .result-item:last-child { border-bottom: none; }

        .result-label {
            color: #64748b;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .result-value {
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }

        .badge-vin {
            background: #3b82f6;
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 15px;
            display: inline-block;
        }

        .text-unavailable {
            color: #94a3b8;
            font-weight: 400;
            font-style: italic;
        }

        @media (max-width: 992px) {
            .content-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content-wrapper">

    <div class="page-header">
        <div class="container text-center">
            <h1 class="fw-bold mb-2">Bezplatný VIN Decoder</h1>
            <p class="text-muted">Zistite podrobné informácie o vozidle v priebehu sekundy.</p>

        </div>
    </div>

    <div class="container pb-5">
        <div class="text-center align-items-center gap-3">
            <div class>
                <div class="fw-bold small"><?= htmlspecialchars($user['username'] ?? 'Používateľ') ?></div>
                <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <span class="badge <?= $isAdmin ? 'bg-danger' : 'bg-warning text-dark' ?> rounded-pill px-3 py-2 shadow-sm">
                <i class="fas <?= $isAdmin ? 'fa-user-shield' : 'fa-tools' ?> me-2"></i>
                <?= $isAdmin ? 'ADMIN' : 'MECHANIK' ?>
            </span>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="vin-card mb-4">
                    <form method="POST">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-3">Identifikačné číslo vozidla (VIN)</label>
                        <div class="input-group">
                            <input type="text" name="vin" class="form-control vin-input"
                                   placeholder="Zadajte 17 znakov..." maxlength="17" required
                                   value="<?= htmlspecialchars($_POST['vin'] ?? '') ?>">
                            <button class="btn btn-primary px-4 fw-bold shadow-sm" type="submit" style="border-radius: 0 16px 16px 0;">
                                <i class="fas fa-search me-2"></i> Preveriť
                            </button>
                        </div>
                        <?php if ($error): ?>
                            <div class="text-danger small mt-3 fw-bold">
                                <i class="fas fa-exclamation-triangle me-1"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if ($vin_data): ?>
                    <div class="vin-card">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <h4 class="fw-bold m-0"><i class="fas fa-car-side me-2 text-primary"></i>Technické údaje</h4>
                            <span class="badge-vin">VIN: <?= htmlspecialchars($vin) ?></span>
                        </div>



                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="result-group">
                                    <div class="result-item">
                                        <span class="result-label">Značka</span>
                                        <span class="result-value text-primary">
                                            <?= !empty($vin_data['Make']) ? $vin_data['Make'] : '<span class="text-unavailable">Dáta nedostupné</span>' ?>
                                        </span>
                                    </div>
                                    <div class="result-item">
                                        <span class="result-label">Model</span>
                                        <span class="result-value">
                                            <?= !empty($vin_data['Model']) ? $vin_data['Model'] : '<span class="text-unavailable">Regionálne obmedzené</span>' ?>
                                        </span>
                                    </div>
                                    <div class="result-item">
                                        <span class="result-label">Modelový rok</span>
                                        <span class="result-value">
                                            <?= !empty($vin_data['Model Year']) ? $vin_data['Model Year'] : '<span class="text-unavailable">Nezistené</span>' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="result-group">
                                    <div class="result-item">
                                        <span class="result-label">Karoséria</span>
                                        <span class="result-value">
                                            <?= !empty($vin_data['Body Class']) ? $vin_data['Body Class'] : '<span class="text-unavailable">Nedefinované</span>' ?>
                                        </span>
                                    </div>
                                    <div class="result-item">
                                        <span class="result-label">Objem motora</span>
                                        <span class="result-value">
                                            <?php
                                            if (!empty($vin_data['Displacement (L)'])) {
                                                echo $vin_data['Displacement (L)'] . " L";
                                            } elseif (!empty($vin_data['Displacement (CC)'])) {
                                                echo ($vin_data['Displacement (CC)'] / 1000) . " L";
                                            } else {
                                                echo '<span class="text-unavailable">Dáta nedostupné pre tento región</span>';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="result-item">
                                        <span class="result-label">Krajina výroby</span>
                                        <span class="result-value">
                                            <?= !empty($vin_data['Plant Country']) ? $vin_data['Plant Country'] : '<span class="text-unavailable">Neznáme</span>' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-4 border-start border-4 border-primary bg-light small text-muted">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Tieto dáta sú získané z globálnej databázy <strong>NHTSA</strong>. Pri vozidlách vyrobených výhradne pre európsky trh (napr. staršie modely Škoda) môžu byť technické detaily obmedzené.
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>