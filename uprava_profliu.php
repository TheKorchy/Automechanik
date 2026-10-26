<?php
session_start();
include "connect_web.php";

// 1. Overenie prihlásenia
if (!isset($_SESSION['user'])) {
    header("Location: prihlasenie.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];
$message = "";

// 2. Spracovanie formulára
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = mysqli_real_escape_string($conn, $_POST['username']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['email']);
    $newPassword = $_POST['password'];

    $updateQuery = "UPDATE uzivatelia SET username = '$newUsername', email = '$newEmail' WHERE id = '$userId'";

    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['user']['username'] = $newUsername;
        $_SESSION['user']['email'] = $newEmail;

        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE uzivatelia SET password = '$hashedPassword' WHERE id = '$userId'");
        }

        if (!empty($_FILES['avatar']['name'])) {
            $targetDir = "uploads/avatars/";
            if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

            $fileName = $userId . "_" . time() . "_" . basename($_FILES["avatar"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if (in_array(strtolower($fileType), $allowTypes)) {
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFilePath)) {
                    mysqli_query($conn, "UPDATE uzivatelia SET avatar = '$fileName' WHERE id = '$userId'");
                    $_SESSION['user']['avatar'] = $fileName;
                }
            }
        }
        $message = "<div class='alert alert-success border-0 shadow-sm'><i class='fas fa-check-circle me-2'></i>Profil bol úspešne aktualizovaný!</div>";
    } else {
        $message = "<div class='alert alert-danger border-0 shadow-sm'><i class='fas fa-exclamation-triangle me-2'></i>Chyba: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Nastavenia účtu | Autoservis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary-blue: #3b82f6; --dark-bg: #111827; }
        body { background: #f8fafc; font-family: 'Inter', system-ui, -apple-system, sans-serif; }

        /* Sidebar */
        .sidebar { width: 260px; height: 100vh; position: fixed; background: var(--dark-bg); color: white; padding: 25px 20px; z-index: 1000; }
        .sidebar h4 { color: var(--primary-blue); font-weight: 800; letter-spacing: -1px; margin-bottom: 40px; text-align: center; }
        .sidebar a { display: flex; align-items: center; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 10px; margin-bottom: 8px; transition: 0.2s all; }
        .sidebar a i { width: 25px; font-size: 1.1rem; }
        .sidebar a:hover, .sidebar a.active { background: #1f2937; color: white; }
        .sidebar a.active { color: var(--primary-blue); background: rgba(59, 130, 246, 0.1); }

        /* Content */
        .content { margin-left: 260px; padding: 50px; }

        /* Profile Card */
        .profile-card { background: white; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); padding: 40px; max-width: 800px; margin: 0 auto; border: 1px solid #f1f5f9; }

        /* Avatar Section */
        .avatar-section { position: relative; width: 140px; margin: 0 auto 35px; }
        .avatar-preview { width: 140px; height: 140px; object-fit: cover; border-radius: 40px; border: 4px solid white; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transition: 0.3s; }
        .btn-upload { position: absolute; bottom: -5px; right: -5px; background: var(--primary-blue); color: white; width: 42px; height: 42px; border-radius: 15px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 4px solid white; box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3); transition: 0.2s; }
        .btn-upload:hover { transform: scale(1.1); background: #2563eb; }

        /* Form styling */
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; margin-bottom: 8px; }
        .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background: #f8fafc; transition: 0.2s; }
        .form-control:focus { background: white; border-color: var(--primary-blue); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .input-group-text { border-radius: 12px 0 0 12px; border-right: none; background: #f8fafc; color: #94a3b8; }
        .input-group .form-control { border-radius: 0 12px 12px 0; }

        .btn-save { background: var(--primary-blue); color: white; border: none; padding: 15px; border-radius: 14px; font-weight: 700; transition: 0.3s; margin-top: 20px; }
        .btn-save:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>AUTOSERVIS</h4>
    <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="profil.php"><i class="fas fa-user"></i> Profil</a>
    <a href="uprava_profliu.php" class="active"><i class="fas fa-user-cog"></i> Uprava profilu</a>
    <a href="admin_panel.php"><i class="fas fa-shield-alt"></i> Admin Panel</a>
    <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt"></i> Odhlásiť</a>
</div>

<div class="content">
    <div class="profile-card">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark mb-1">Nastavenia profilu</h2>
            <p class="text-muted">Aktualizujte svoje osobné údaje a zabezpečenie účtu</p>
        </div>

        <?= $message ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="avatar-section">
                <?php $currentAvatar = !empty($_SESSION['user']['avatar']) ? $_SESSION['user']['avatar'] : 'default.png'; ?>
                <img src="uploads/avatars/<?= $currentAvatar ?>" class="avatar-preview" id="previewImg">
                <label for="avatar" class="btn-upload">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" name="avatar" id="avatar" style="display:none;" onchange="previewFile()">
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Používateľské meno</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['username']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mailová adresa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Nové heslo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Ponechajte prázdne pre zachovanie pôvodného">
                    </div>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-2"></i> Uložiť zmeny profilu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewFile() {
        const preview = document.getElementById('previewImg');
        const file = document.getElementById('avatar').files[0];
        const reader = new FileReader();
        reader.onloadend = () => preview.src = reader.result;
        if (file) reader.readAsDataURL(file);
    }
</script>

</body>
</html>