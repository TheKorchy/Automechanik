<?php
session_start();
include "connect_web.php";

if (!isset($_SESSION['user'])) {
    header("Location: prihlasenie.php");
    exit();
}

$user = $_SESSION['user'];
$u_id = $user['id'];

// Logika pre mazanie a čítanie (zostáva zachovaná pre funkčnosť)
if (isset($_GET['read_all'])) {
    $conn->query("UPDATE notifikacie SET precitane = 1 WHERE uzivatel_id = $u_id");
    header("Location: vsetky_notifikacie.php"); exit();
}
if (isset($_GET['delete'])) {
    $notif_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $conn->query("DELETE FROM notifikacie WHERE id = $notif_id AND uzivatel_id = $u_id");
    header("Location: vsetky_notifikacie.php"); exit();
}

$query = "SELECT * FROM notifikacie WHERE uzivatel_id = $u_id ORDER BY datum DESC";
$result = $conn->query($query);
$unread_res = $conn->query("SELECT COUNT(*) as c FROM notifikacie WHERE uzivatel_id = $u_id AND precitane = 0");
$unread_count = $unread_res->fetch_assoc()['c'];

include 'nav_admin.php';
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Control | Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=Plus+Jakarta+Sans:wght@300;500;700;800&display=swap');

        :root {
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.5);
            --bg-dark: #02040a;
            --card-glass: rgba(255, 255, 255, 0.015);
            --border-glass: rgba(255, 255, 255, 0.1);
            --text-pure: #ffffff;
            --text-silver: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-silver);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient background */
        body::before {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.08) 0%, transparent 70%),
            radial-gradient(circle at 100% 100%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .main-content { max-width: 1000px; margin: 160px auto 100px; padding: 0 25px; }

        /* Bento Stats */
        .bento-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 50px; }
        .bento-card {
            background: var(--card-glass);
            border: 1px solid var(--border-glass);
            border-radius: 32px;
            padding: 35px;
            backdrop-filter: blur(20px);
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .bento-card:hover { transform: translateY(-5px); border-color: var(--accent); }

        h1 { font-family: 'Bricolage Grotesque'; font-weight: 800; font-size: 4rem; letter-spacing: -4px; margin: 0; }

        /* Search Interface */
        .search-container { position: relative; margin-bottom: 30px; }
        .search-bar {
            width: 100%; background: var(--card-glass); border: 1px solid var(--border-glass);
            border-radius: 24px; padding: 20px 25px 20px 65px; color: var(--text-pure);
            font-size: 1.1rem; transition: 0.3s;
        }
        .search-bar:focus { outline: none; border-color: var(--accent); background: rgba(255,255,255,0.05); }
        .search-icon { position: absolute; left: 25px; top: 50%; transform: translateY(-50%); color: var(--accent); font-size: 1.3rem; }

        /* Notification Engine */
        .notif-wrapper { border-radius: 40px; overflow: hidden; border: 1px solid var(--border-glass); background: var(--card-glass); }

        .notif-row {
            padding: 30px 40px;
            border-bottom: 1px solid var(--border-glass);
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 25px;
            transition: 0.5s;
            cursor: pointer;
        }
        .notif-row:hover { background: rgba(255, 255, 255, 0.03); }

        .status-orb {
            width: 12px; height: 12px; border-radius: 50%;
            position: relative;
        }
        .unread .status-orb {
            background: var(--accent);
            box-shadow: 0 0 20px var(--accent), 0 0 40px var(--accent);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }

        .main-msg {
            color: var(--text-pure);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 5px;
            text-shadow: 0 0 20px rgba(255,255,255,0.1);
        }

        .sub-details {
            display: flex; gap: 20px; color: rgba(255,255,255,0.5); font-weight: 500; font-size: 0.9rem;
        }
        .sub-details span { display: flex; align-items: center; gap: 6px; }
        .sub-details b { color: var(--accent); }

        .time-label { font-weight: 800; font-size: 1.1rem; color: var(--text-pure); text-align: right; }

        /* Advanced Actions */
        .action-circle {
            width: 45px; height: 45px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.05); color: white;
            border: 1px solid transparent; transition: 0.3s;
        }
        .action-circle:hover { background: #ef4444; border-color: #ff7979; transform: rotate(90deg); }

        /* Modal Ultra */
        .modal-content {
            background: radial-gradient(circle at top left, #0f172a, #020617);
            border: 1px solid var(--accent);
            border-radius: 48px;
            padding: 40px;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="bento-grid animate__animated animate__fadeIn">
        <div class="bento-card d-flex flex-column justify-content-center">
            <span class="text-primary fw-bold text-uppercase small mb-2" style="letter-spacing: 5px;">Elite Experience</span>
            <h1>Control <span style="font-weight: 200; opacity: 0.4;">Panel</span></h1>
        </div>
        <div class="bento-card d-flex flex-column align-items-center justify-content-center text-center">
            <div class="h2 fw-800 mb-0"><?= $unread_count ?></div>
            <div class="small opacity-50 text-uppercase fw-bold">Nové správy</div>
            <a href="?read_all=1" class="btn btn-sm btn-primary rounded-pill mt-3 px-4 fw-bold shadow-lg">Všetko prečítať</a>
        </div>
    </div>

    <div class="search-container animate__animated animate__fadeInUp">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="masterSearch" class="search-bar" placeholder="Hľadať v digitálnom archíve...">
    </div>

    <div class="notif-wrapper animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
        <?php if ($result && $result->num_rows > 0): ?>
            <div id="feed">
                <?php while($n = $result->fetch_assoc()):
                    $is_unread = ($n['precitane'] == 0);
                    preg_match('/#(\d+)/', $n['sprava'], $matches);
                    $order_id = $matches[1] ?? 'SYS';
                    ?>
                    <div class="notif-row <?= $is_unread ? 'unread' : 'read' ?>"
                         onclick="openEliteModal(this)"
                         data-id="<?= $order_id ?>"
                         data-msg="<?= htmlspecialchars($n['sprava']) ?>"
                         data-full-date="<?= date("d. F Y — H:i:s", strtotime($n['datum'])) ?>">

                        <div class="status-orb"></div>

                        <div class="content-area">
                            <div class="main-msg"><?= htmlspecialchars($n['sprava']) ?></div>
                            <div class="sub-details">
                                <span><i class="far fa-clock"></i> <?= date("d. m. Y", strtotime($n['datum'])) ?></span>
                                <span><i class="fas fa-fingerprint"></i> TOKEN <b>#<?= $order_id ?></b></span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-4">
                            <div class="time-label"><?= date("H:i", strtotime($n['datum'])) ?></div>
                            <a href="?delete=<?= $n['id'] ?>" class="action-circle" onclick="event.stopPropagation()">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="p-5 text-center opacity-50">
                <i class="fas fa-shield-alt fa-3x mb-3"></i>
                <p class="h5 fw-light">Váš systém je v optimálnom stave. Žiadne hlásenia.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="eliteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl">
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <span class="badge rounded-pill bg-primary px-3 py-2">System Notification</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <h6 class="text-uppercase opacity-50 fw-bold mb-2" style="letter-spacing: 3px;">Identifikátor zákazky</h6>
                <h1 id="m_id" class="mb-5 text-white"></h1>

                <div class="p-4 mb-5" style="background: rgba(255,255,255,0.03); border-left: 4px solid var(--accent); border-radius: 0 20px 20px 0;">
                    <p id="m_msg" class="h3 fw-light text-white mb-0" style="line-height: 1.6;"></p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div id="m_date" class="fw-bold opacity-50"></div>
                    <button class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg" data-bs-dismiss="modal">Potvrdiť prijatie</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Master Search with High Performance
    document.getElementById('masterSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.notif-row').forEach(row => {
            const isVisible = row.innerText.toLowerCase().includes(query);
            row.style.display = isVisible ? 'grid' : 'none';
        });
    });

    // Modal Orchestration
    function openEliteModal(el) {
        const d = el.dataset;
        document.getElementById('m_id').innerText = "TORYSA-" + d.id;
        document.getElementById('m_msg').innerText = d.msg;
        document.getElementById('m_date').innerText = d.fullDate;

        const myModal = new bootstrap.Modal(document.getElementById('eliteModal'));
        myModal.show();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>