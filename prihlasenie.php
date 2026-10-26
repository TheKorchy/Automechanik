<?php
session_start();
include "connect_web.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? '';
    $heslo = $_POST["heslo"] ?? '';

    $sql = "SELECT * FROM uzivatelia WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL chyba: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($heslo, $user["heslo"])) {
            $_SESSION["user"] = [
                    "id" => $user["id"],
                    "username" => $user["username"],
                    "email" => $user["email"],
                    "role" => $user["role"],
                    "avatar" => $user["avatar"]
            ];
            header("Location: index.php");
            exit();
        } else {
            $error = "Nesprávne heslo.";
        }
    } else {
        $error = "Používateľ neexistuje.";
    }
    $stmt->close();
}
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prihlásenie | Autoservis Torysa Elite</title>

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
            max-width: 480px;
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
            margin-bottom: 10px;
        }

        .auth-subtitle {
            text-align: center;
            opacity: 0.6;
            margin-bottom: 35px;
            font-size: 0.95rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 15px;
            color: white;
            padding: 14px 20px;
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
            margin-top: 15px;
        }

        .btn-elite:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--accent-glow);
            background: #2563eb;
        }

        .auth-footer {
            text-align: center;
            margin-top: 30px;
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

        .error-msg {
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
            border-radius: 12px;
            padding: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }
    </style>
</head>
<body>

<div id="cursor-glow"></div>

<div class="container d-flex justify-content-center">
    <div class="auth-card">
        <h3 class="auth-title">Vitajte <span style="color: var(--accent);">späť</span></h3>
        <p class="auth-subtitle">Prihláste sa do svojho Elite účtu</p>

        <form method="POST">
            <div class="mb-3">
                <div class="position-relative">
                    <label class="form-label"><i class="fas fa-envelope me-2"></i>E-mail</label>
                    <input type="email" name="email" class="form-control" placeholder="Emailová adresa" required>
                </div>
            </div>

            <div class="mb-4">
                <div class="position-relative">
                    <label class="form-label"><i class="fas fa-lock me-2"></i>Heslo</label>
                    <input type="password" name="heslo" class="form-control" placeholder="Heslo" required>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-elite w-100">
                Prihlásiť sa
            </button>
        </form>

        <div class="auth-footer">
            Nemáte ešte účet? <a href="registracia.php">Zaregistrujte sa</a>
        </div>
    </div>
</div>

<script>
    const glow = document.getElementById('cursor-glow');
    document.addEventListener('mousemove', (e) => {
        glow.style.opacity = "1";
        glow.style.left = e.clientX + 'px';
        glow.style.top = e.clientY + 'px';
    });
    document.addEventListener('mouseleave', () => {
        glow.style.opacity = "0";
    });
</script>

</body>
</html>