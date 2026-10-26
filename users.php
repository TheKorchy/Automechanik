<?php
session_start();
include "connect_web.php";

// 1. OCHRANA PRÍSTUPU (Admin + Mechanik podľa zadania)
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'mechanik'])) {
    header("Location: index.php");
    exit;
}

// 2. SPRACOVANIE AKCIÍ (UPDATE & DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Úprava používateľa
    if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
        $id = intval($_POST['user_id']);
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $role = $conn->real_escape_string($_POST['role']);

        $conn->query("UPDATE uzivatelia SET username='$username', email='$email', role='$role' WHERE id=$id");
        header("Location: sprava_uzivatelov.php?msg=updated");
        exit;
    }

    // Mazanie používateľa
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = intval($_POST['user_id']);
        // Ochrana: Admin nemôže zmazať sám seba
        if ($id !== $_SESSION['user']['id']) {
            $conn->query("DELETE FROM uzivatelia WHERE id=$id");
        }
        header("Location: sprava_uzivatelov.php?msg=deleted");
        exit;
    }
}

$users = $conn->query("SELECT id, username, email, role FROM uzivatelia ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa personálu | Torysa Servis Elite</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        :root {
            --bg-dark: #090e1a;
            --card-dark: #111827;
            --border-color: rgba(255, 255, 255, 0.04);
            --primary-blue: #3b82f6;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.2px;
        }

        .page-content {
            padding: 130px 0 60px 0;
        }

        /* Moderná karta (Glassmorphism efekt) */
        .elite-card {
            background: rgba(17, 24, 39, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .card-header-elite {
            padding: 25px 35px;
            background: rgba(255, 255, 255, 0.01);
            border-bottom: 1px solid var(--border-color);
        }

        /* Dizajn tabuľky */
        .table {
            --bs-table-bg: transparent;
            --bs-table-border-color: transparent;
            --bs-table-hover-bg: rgba(59, 130, 246, 0.03);
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            color: var(--text-muted);
            padding: 20px 35px;
            border: none;
        }

        .table tbody td {
            padding: 22px 35px;
            border-bottom: 1px solid var(--border-color);
            transition: 0.2s;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Avatar 2.0 */
        .avatar-box {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            color: white;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }

        /* Badges (Roly) */
        .role-badge {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-admin { background-color: #ef4444; color: white; }
        .badge-mechanik { background-color: #f59e0b; color: white; }
        .badge-user { background-color: #475569; color: white; }

        /* Vylepšené vyhľadávanie */
        .search-container {
            position: relative;
            width: 300px;
        }
        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .search-input {
            background: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid var(--border-color) !important;
            color: white !important;
            border-radius: 14px;
            padding: 12px 15px 12px 42px;
            transition: 0.3s;
        }
        .search-input:focus {
            border-color: var(--primary-blue) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }

        /* Modálne okná */
        .modal-content {
            background-color: #111827;
            border: 1px solid var(--border-color);
            border-radius: 28px;
            color: white;
        }
        .modal-header { border-bottom: 1px solid var(--border-color); padding: 25px 30px; }
        .modal-body { padding: 30px; }
        .modal-footer { border-top: 1px solid var(--border-color); padding: 20px 30px; }

        .form-label { color: var(--text-muted); font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; }
        .form-control, .form-select {
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            color: white;
            border-radius: 12px;
            padding: 12px 15px;
        }
        .form-control:focus, .form-select:focus {
            background-color: rgba(0, 0, 0, 0.3);
            border-color: var(--primary-blue);
            color: white;
            box-shadow: none;
        }

        /* Akčné tlačidlá */
        .btn-action {
            width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 10px; background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color); color: var(--text-muted);
            transition: 0.2s;
        }
        .btn-action:hover { background: rgba(59, 130, 246, 0.1); color: var(--primary-blue); border-color: rgba(59, 130, 246, 0.2); }
        .btn-action.text-danger:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="page-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h6 class="fw-800 text-uppercase mb-1" style="color: var(--primary-blue); letter-spacing: 2px; font-size: 0.75rem;">Administrácia</h6>
                <h1 class="display-6 fw-800 text-white" style="letter-spacing: -1.5px;">Správa personálu</h1>
            </div>
            <div class="text-end text-muted small fw-500">
                Prihlásený ako: <span class="badge badge-<?= $_SESSION['user']['role'] ?> ms-1"><?= $_SESSION['user']['role'] ?></span>
            </div>
        </div>

        <div class="elite-card">
            <div class="card-header-elite d-flex justify-content-between align-items-center">
                <h5 class="fw-700 m-0 text-white">Registrovaní členovia</h5>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control search-input" placeholder="Hľadať podľa mena alebo emailu..." id="userSearch">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Člen servisu</th>
                        <th>Kontaktný email</th>
                        <th>Prístupová rola</th>
                        <th class="text-end">Akcie</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-box"><?= strtoupper(substr($u['username'], 0, 1)) ?></div>
                                    <div>
                                        <div class="fw-700 text-white"><?= htmlspecialchars($u['username']) ?></div>
                                        <div class="small text-muted" style="font-size: 0.7rem;">ID: #<?= $u['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-white fw-500 small"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="role-badge badge-<?= $u['role'] ?>">
                                    <?php if($u['role'] === 'admin'): ?><i class="fas fa-crown"></i>
                                    <?php elseif($u['role'] === 'mechanik'): ?><i class="fas fa-screwdriver-wrench"></i>
                                    <?php else: ?><i class="fas fa-user"></i><?php endif; ?>
                                    <?= $u['role'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-action" title="Upraviť" onclick='openEditModal(<?= json_encode($u) ?>)'>
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>

                                    <?php if($u['id'] != $_SESSION['user']['id']): ?>
                                        <button class="btn btn-action text-danger" title="Zmazať" onclick="confirmDelete(<?= $u['id'] ?>, '<?= $u['username'] ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-action opacity-25" title="Sám seba nemôžete zmazať" disabled>
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title fw-800">Upraviť profil člena</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_id">

                <div class="mb-4">
                    <label class="form-label">Meno používateľa</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required placeholder="Zadajte meno">
                </div>
                <div class="mb-4">
                    <label class="form-label">Emailová adresa</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required placeholder="meno@torysa.sk">
                </div>
                <div class="mb-2">
                    <label class="form-label">Prístupová rola</label>
                    <select name="role" id="edit_role" class="form-select">
                        <option value="uzivatel">Užívateľ (Zákazník)</option>
                        <option value="mechanik">Mechanik (Personál)</option>
                        <option value="admin">Administrátor (Úplný prístup)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-link text-white-50 text-decoration-none fw-600" data-bs-dismiss="modal">Zrušiť</button>
                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-800" style="background-color: var(--primary-blue); border: none;">Uložiť zmeny</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form class="modal-content text-center" method="POST" style="border-color: rgba(239, 68, 68, 0.2);">
            <div class="modal-body p-5">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" id="delete_id">

                <div class="mb-3 text-danger">
                    <i class="fas fa-user-times fa-4x opacity-75"></i>
                </div>

                <h5 class="fw-800 text-white mb-2">Odstrániť účet?</h5>
                <p class="small text-muted mb-4">Naozaj chcete zmazať používateľa <b class="text-white" id="delete_name"></b>? Táto akcia je nevratná.</p>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-100 rounded-pill fw-700" style="background: rgba(255,255,255,0.05); border: none;" data-bs-dismiss="modal">Nie, zrušiť</button>
                    <button type="submit" class="btn btn-danger w-100 rounded-pill fw-800" style="background-color: #ef4444; border: none;">Áno, zmazať</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Live vyhľadávanie v tabuľke
    document.getElementById('userSearch').addEventListener('keyup', function() {
        const val = this.value.toLowerCase();
        document.querySelectorAll("tbody tr").forEach(row => {
            // Hľadáme len v stĺpci "Člen" a "Email" pre presnejší výsledok
            const memberCell = row.cells[0].innerText.toLowerCase();
            const emailCell = row.cells[1].innerText.toLowerCase();

            if(memberCell.includes(val) || emailCell.includes(val)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    // Otvorenie Edit Modalu
    function openEditModal(user) {
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    // Otvorenie Delete Modalu
    function confirmDelete(id, name) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_name').innerText = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

</body>
</html>