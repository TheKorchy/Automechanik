<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include "connect_web.php";

// 1. OCHRANA PRÍSTUPU - Hneď na začiatku skontrolujeme, či je prihlásený
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

// Dátumová logika
$month = isset($_GET["m"]) ? intval($_GET["m"]) : date("m");
$year  = isset($_GET["y"]) ? intval($_GET["y"]) : date("Y");

$firstDay = mktime(0,0,0,$month,1,$year);
$daysInMonth = date("t",$firstDay);
$dayOfWeek = date("N",$firstDay)-1;

// Načítanie naplánovaných dát
$planned = [];
$res = $conn->query("SELECT * FROM odoslane WHERE MONTH(datum)='$month' AND YEAR(datum)='$year'");
while($row = $res->fetch_assoc()){
    $d = date("Y-m-d", strtotime($row["datum"]));
    $planned[$d][] = $row;
}

// Fronta (Neplánované)
$unplanned = $conn->query("SELECT * FROM odoslane WHERE (datum IS NULL OR datum = '0000-00-00' OR datum = '') ORDER BY id DESC");
$unplanned_count = $unplanned->num_rows;

$monthsSk = [1=>"Január",2=>"Február",3=>"Marec",4=>"Apríl",5=>"Máj",6=>"Jún",7=>"Júl",8=>"August",9=>"September",10=>"Október",11=>"November",12=>"December"];

function getServiceStyle($s){
    $s = mb_strtolower($s);
    if(strpos($s,"motor")!==false) return ["icon"=>"fa-engine-warning", "color"=>"#ef4444", "bg"=>"rgba(239, 68, 68, 0.15)"];
    if(strpos($s,"pneu")!==false) return ["icon"=>"fa-tire", "color"=>"#22c55e", "bg"=>"rgba(34, 197, 94, 0.15)"];
    if(strpos($s,"diag")!==false) return ["icon"=>"fa-microchip", "color"=>"#eab308", "bg"=>"rgba(234, 179, 8, 0.15)"];
    return ["icon"=>"fa-tools", "color"=>"#3b82f6", "bg"=>"rgba(59, 130, 246, 0.15)"];
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Elite Planner | Torysa Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@800&family=Plus+Jakarta+Sans:wght@400;700;800&display=swap');
        :root { --bg: #020617; --sidebar-w: 260px; --fronta-w: 320px; --accent: #3b82f6; --border: rgba(255, 255, 255, 0.08); }
        body { background: var(--bg); color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }

        .fronta-panel { position: fixed; left: var(--sidebar-w); top: 70px; width: var(--fronta-w); height: calc(100vh - 70px); background: #070a18; border-right: 1px solid var(--border); padding: 30px 0; z-index: 100; }
        .planner-wrapper { margin-left: calc(var(--sidebar-w) + var(--fronta-w)); margin-top: 70px; padding: 40px; }

        .order-card { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 15px; padding: 15px; margin: 0 20px 10px 20px; cursor: grab; transition: 0.2s; }
        .order-card:hover { border-color: var(--accent); background: rgba(255,255,255,0.05); }

        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; }
        .day-box { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 18px; min-height: 130px; padding: 12px; transition: 0.2s; cursor: crosshair; position: relative; }
        .day-box.selected-brush { border-color: var(--accent); background: rgba(59, 130, 246, 0.1); }

        .cap-bar { width: 100%; height: 4px; background: rgba(255,255,255,0.05); border-radius: 10px; margin: 5px 0; overflow: hidden; }
        .cap-fill { height: 100%; transition: width 0.4s; }

        .job-tag { font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
        .magic-btn { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; border-radius: 50%; background: var(--accent); color: white; border: none; z-index: 999; }
        .modal-content { background: #0a1122; border: 1px solid var(--accent); border-radius: 30px; color: white; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; include 'nav_admin.php'; ?>

<div class="fronta-panel">
    <div class="px-4 mb-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-800 mb-0">Fronta</h5>
        <span class="badge bg-primary rounded-pill"><?= $unplanned_count ?></span>
    </div>
    <div id="unplanned-list" style="height: calc(100% - 50px); overflow-y: auto;">
        <?php while($o = $unplanned->fetch_assoc()): $s = getServiceStyle($o["service"]); ?>
            <div class="order-card" data-id="<?=$o["id"]?>">
                <div class="d-flex justify-content-between mb-1 small text-primary fw-800">#<?=$o["id"]?> <i class="fas <?=$s['icon']?>"></i></div>
                <div class="fw-700 small"><?=$o["service"]?></div>
                <div class="opacity-50 small" style="font-size: 10px;"><?=$o["name"]?></div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="planner-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-800" style="font-family: 'Bricolage Grotesque';"><?= $monthsSk[intval($month)] ?> <?= $year ?></h1>
        <div class="btn-group">
            <a href="?m=<?= ($month==1?12:$month-1) ?>&y=<?= ($month==1?$year-1:$year) ?>" class="btn btn-dark"><i class="fas fa-chevron-left"></i></a>
            <a href="?m=<?= ($month==12?1:$month+1) ?>&y=<?= ($month==12?$year+1:$year) ?>" class="btn btn-dark"><i class="fas fa-chevron-right"></i></a>
        </div>
    </div>

    <div class="calendar-grid">
        <?php
        $dni = ["Po","Ut","St","Št","Pi","So","Ne"];
        foreach($dni as $d) echo "<div class='text-center mb-2 small fw-800 opacity-30'>$d</div>";
        for($i=0; $i<$dayOfWeek; $i++) echo "<div></div>";

        $dailyLimit = 4;
        for($d=1; $d<=$daysInMonth; $d++):
            $date = sprintf("%04d-%02d-%02d", $year, $month, $d);
            $orders = $planned[$date] ?? [];
            $percent = min((count($orders)/$dailyLimit)*100, 100);
            $color = $percent >= 100 ? '#ef4444' : ($percent >= 75 ? '#eab308' : '#3b82f6');
            ?>
            <div class="day-box" data-date="<?=$date?>">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-800 small"><?=$d?>.</span>
                    <div class="cap-bar" style="width: 35px;"><div class="cap-fill" style="width:<?=$percent?>%; background:<?=$color?>;"></div></div>
                </div>
                <div class="drop-zone" data-date="<?=$date?>" style="min-height: 70px;">
                    <?php foreach($orders as $o): $s = getServiceStyle($o["service"]); ?>
                        <div class="job-tag" data-id="<?=$o["id"]?>" style="background: <?=$s['bg']?>; color: <?=$s['color']?>;">
                            <i class="fas <?=$s['icon']?>"></i> <?= $o["service"] ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>

<div class="modal fade" id="autoPlanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">
            <i class="fas fa-magic fa-3x text-primary mb-3"></i>
            <h4 class="fw-800 mb-4">Smart Rozvrh</h4>

            <div class="mb-4 text-start bg-dark p-3 rounded-4">
                <label class="small fw-bold opacity-50 mb-2">MAX. OBJEDNÁVOK / DEŇ</label>
                <input type="range" class="form-range" id="limitRange" min="1" max="10" value="3" oninput="limitLabel.innerText = this.value">
                <div class="text-center fw-800 fs-4 text-primary" id="limitLabel">3</div>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-primary rounded-pill py-3 fw-bold" onclick="executeAction('auto_distribute')">
                    Rozdeliť objednávky
                </button>
                <button class="btn btn-outline-danger rounded-pill py-2" onclick="executeAction('bulk_clear')">
                    <i class="fas fa-trash-alt me-2"></i>Vymazať vybrané dni
                </button>
                <button class="btn btn-link text-white-50" onclick="resetBrush()">Zrušiť výber</button>
            </div>
        </div>
    </div>
</div>

<button class="magic-btn shadow-lg" data-bs-toggle="modal" data-bs-target="#autoPlanModal">
    <i class="fas fa-bolt"></i>
</button>

<script>
    // 1. Drag & Drop konfigurácia
    const sortConfig = {
        group: "planner",
        animation: 200,
        ghostClass: "opacity-0",
        onAdd: (evt) => {
            const fd = new FormData();
            fd.append("action", "assign");
            fd.append("order_id", evt.item.dataset.id);
            fd.append("planned_date", evt.to.dataset.date || "");
            fetch("planner_update.php", { method: "POST", body: fd }).then(() => location.reload());
        }
    };

    new Sortable(document.getElementById("unplanned-list"), sortConfig);
    document.querySelectorAll(".drop-zone").forEach(z => new Sortable(z, sortConfig));

    // 2. Brush Selection (Výber dní myšou)
    let isDrawing = false;
    let selectedDays = new Set();

    document.querySelectorAll('.day-box').forEach(box => {
        box.addEventListener('mousedown', () => { isDrawing = true; toggleDay(box); });
        box.addEventListener('mouseenter', () => { if(isDrawing) toggleDay(box); });
    });

    window.addEventListener('mouseup', () => isDrawing = false);

    function toggleDay(el) {
        const d = el.dataset.date;
        if(selectedDays.has(d)) {
            selectedDays.delete(d);
            el.classList.remove('selected-brush');
        } else {
            selectedDays.add(d);
            el.classList.add('selected-brush');
        }
    }

    function resetBrush() {
        selectedDays.clear();
        document.querySelectorAll('.day-box').forEach(b => b.classList.remove('selected-brush'));
    }

    // 3. Spustenie akcií (Auto-distribúcia alebo Vymazanie)
    function executeAction(type) {
        if(selectedDays.size === 0) return alert("Najprv vyberte dni v kalendári!");

        const fd = new FormData();
        fd.append("action", type);
        fd.append("limit_per_day", document.getElementById('limitRange').value);

        // Zoradíme vybrané dni chronologicky
        const sortedDays = Array.from(selectedDays).sort();
        sortedDays.forEach(d => fd.append("selected_days[]", d));

        fetch("planner_update.php", { method: "POST", body: fd })
            .then(res => res.text())
            .then(data => {
                console.log(data);
                location.reload();
            });
    }
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>