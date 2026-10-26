<?php
session_start();
include "connect_web.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $heslo_raw = $_POST["heslo"]; // Pôvodné heslo pre validáciu (ak by si chcel napr. dĺžku)
    $heslo = password_hash($heslo_raw, PASSWORD_DEFAULT);

    // --- 1. KROK: KONTROLA EXISTENCIE UŽÍVATEĽA ---
    $check_sql = "SELECT id FROM uzivatelia WHERE email = ? OR username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Ak sa našiel aspoň jeden záznam
        $error = "Užívateľ s týmto e-mailom alebo menom už existuje.";
    } else {
        // --- 2. KROK: SPRACOVANIE AVATARU (iba ak užívateľ neexistuje) ---
        $avatarName = 'default.png';

        if (!empty($_FILES['avatar']['name'])) {
            $targetDir = "uploads/avatars/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatarName = uniqid() . "." . $ext;

            move_uploaded_file(
                    $_FILES['avatar']['tmp_name'],
                    $targetDir . $avatarName
            );
        }

        // --- 3. KROK: SAMOTNÝ ZÁPIS DO DATABÁZY ---
        $sql = "INSERT INTO uzivatelia (username, email, heslo, role, avatar)
                VALUES (?, ?, ?, 'user', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $heslo, $avatarName);

        if ($stmt->execute()) {
            header("Location: prihlasenie.php?registration=success");
            exit();
        } else {
            $error = "Chyba pri registrácii: " . $conn->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrácia | Autoservis Torysa Elite</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=Plus+Jakarta+Sans:wght@200..800&display=swap');

        :root {
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.25);
            --dark-bg: #020617;
            --card-bg: rgba(15, 23, 42, 0.6);
            --text-main: #f1f5f9;
            --border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            background-image: radial-gradient(circle at 50% 50%, rgba(30, 41, 59, 0.7) 0%, var(--dark-bg) 80%);
        }

        #cursor-glow {
            position: fixed;
            top: 0; left: 0;
            width: 600px; height: 600px;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            transform: translate(-50%, -50%);
            transition: opacity 0.5s ease;
            opacity: 0;
        }

        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 40px;
            padding: 50px;
            backdrop-filter: blur(20px);
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            box-shadow: 0 50px 100px -20px rgba(0,0,0,0.5);
        }

        .auth-title {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: -1.5px;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            margin-left: 5px;
            opacity: 0.8;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 15px;
            color: white;
            padding: 12px 20px;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.07);
            border-color: var(--accent);
            box-shadow: 0 0 15px var(--accent-glow);
            color: white;
        }

        .btn-elite {
            background: var(--accent);
            color: white;
            padding: 15px;
            border-radius: 100px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            transition: 0.4s;
            margin-top: 10px;
        }

        .btn-elite:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--accent-glow);
            background: #2563eb;
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Vlastný štýl pre input type file */
        input[type="file"]::file-selector-button {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 5px 15px;
            margin-right: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="file"]::file-selector-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>

<div id="cursor-glow"></div>

<div class="container d-flex justify-content-center">
    <div class="auth-card">
        <h3 class="auth-title">Vytvoriť <span style="color: var(--accent);">účet</span></h3>

        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user me-2"></i>Používateľské meno</label>
                <input type="text" name="username" class="form-control" placeholder="napr. Peter123" required>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope me-2"></i>E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="vas@email.sk" required>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock me-2"></i>Heslo</label>
                <input type="password" name="heslo" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="fas fa-image me-2"></i>Avatar (nepovinné)</label>
                <input type="file" name="avatar" class="form-control" accept="image/*">
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger border-0 rounded-4 py-2 text-center" style="background: rgba(220, 38, 38, 0.2); color: #fca5a5;">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-elite w-100">Zaregistrovať sa</button>
        </form>

        <div class="auth-footer">
            Už máš účet? <a href="prihlasenie.php">Prihlás sa</a>
        </div>
    </div>
</div>

<script>
    // Sledovanie myši pre glow efekt
    const glow = document.getElementById('cursor-glow');
    document.addEventListener('mousemove', (e) => {
        glow.style.opacity = "1";
        glow.style.left = e.clientX + 'px';
        glow.style.top = e.clientY + 'px';
    });
</script>

</body>
</html>