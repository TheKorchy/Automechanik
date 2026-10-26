<?php

session_start();

include "connect_web.php";



// 1. OCHRANA PRÍSTUPU - Kontrola prihlásenia

if (!isset($_SESSION['user'])) {

    header("Location: prihlasenie.php");

    exit();

}



$user = $_SESSION['user'];

$email = $user['email'];



// Definícia rolí

$isAdmin = ($user['role'] === 'admin');

$isMechanic = ($user['role'] === 'mechanik');



// 2. BLOKOVANIE ZÁKAZNÍKA - Iba personál (Admin/Mechanik)

if (!$isAdmin && !$isMechanic) {

    header("Location: vsetky_notifikacie.php"); // Zákazníka presmerujeme na jeho správy

    exit();

}



// --- NOVÁ LOGIKA: POČÍTANIE DNEŠNÝCH OBJEDNÁVOK ---

$todayDate = date('Y-m-d');

$todayQuery = $conn->query("SELECT COUNT(*) as dnesne FROM odoslane WHERE DATE(datum) = '$todayDate'");

$todayCount = $todayQuery->fetch_assoc()['dnesne'] ?? 0;



// Načítanie dát pre analytiku

$query = "SELECT * FROM odoslane ORDER BY datum DESC";

$result = $conn->query($query);



$status_counts = ['Čaká' => 0, 'V procese' => 0, 'Hotové' => 0];

$serviceTypes = ['Servis motora', 'Servis podvozku', 'Servis prevodoviek', 'Diagnostika', 'Pneuservis'];

$service_counts = array_fill_keys($serviceTypes, 0);

$service_counts['Iné'] = 0;

$all_orders = [];



while($row = $result->fetch_assoc()) {

    $stav = $row['stav'] ?? 'Čaká';

    if (isset($status_counts[$stav])) $status_counts[$stav]++;

    $naslo_kategoriu = false;

    $assigned_cat = 'Iné';

    foreach ($serviceTypes as $type) {

        if (stripos($row['service'] ?? '', $type) !== false) {

            $service_counts[$type]++;

            $assigned_cat = $type;

            $naslo_kategoriu = true;

            break;

        }

    }

    if (!$naslo_kategoriu) $service_counts['Iné']++;

    $row['cat'] = $assigned_cat;

    $all_orders[] = $row;

}



include 'nav_admin.php';

include 'sidebar.php';

?>

<!DOCTYPE html>

<html lang="sk">

<head>

    <meta charset="UTF-8">

    <title>Analytický Dashboard | Torysa Servis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');

        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; margin: 0; }



        .content {

            margin-left: 280px;

            padding: 100px 40px 40px 40px;

            min-height: 100vh;

        }



        /* Štýl pre novú notifikáciu (správu) */

        .today-alert-card {

            background: #ffffff;

            border-radius: 20px;

            padding: 20px 25px;

            border-left: 6px solid #3b82f6;

            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.08);

            display: flex;

            align-items: center;

            justify-content: space-between;

            text-decoration: none !important;

            transition: 0.3s;

            margin-bottom: 35px;

        }

        .today-alert-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(59, 130, 246, 0.15); }

        .icon-circle { 

            width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); 

            color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;

        }



        .stat-card { background: white; border: none; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: 0.3s; }

        .chart-container { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }

        .order-card-mini { background: white; border-radius: 12px; padding: 15px; margin-bottom: 12px; border-left: 5px solid #3b82f6; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }



        #details-container { display: none; margin-top: 30px; animation: slideUp 0.4s ease-out; }

        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }



        @media (max-width: 992px) {

            .content { margin-left: 0; padding-top: 80px; }

        }

    </style>

</head>

<body>



<div class="content">



    <?php if ($todayCount > 0): ?>

    <a href="objednavky.php" class="today-alert-card animate__animated animate__fadeInDown">

        <div class="d-flex align-items-center">

            <div class="icon-circle me-3">

                <i class="fas fa-bell animate__animated animate__swing animate__infinite"></i>

            </div>

            <div>

                <h6 class="mb-0 fw-800 text-dark">Nové objednávky v systéme</h6>

                <p class="mb-0 text-muted small">Dnes pribudlo <strong><?= $todayCount ?></strong> nových prác. Kliknutím prejdete na zoznam.</p>

            </div>

        </div>

        <div class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">Zobraziť</div>

    </a>

    <?php endif; ?>



    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-800 mb-1" style="letter-spacing: -1.5px;">Systémový prehľad</h2>

            <p class="text-muted small">Vitajte, <?= htmlspecialchars($user['username']) ?>. Tu je aktuálny stav dielne.</p>

        </div>

        <div class="d-flex align-items-center gap-3">

            <div class="text-end d-none d-md-block">

                <div class="fw-bold small"><?= htmlspecialchars($user['username'] ?? 'Používateľ') ?></div>

                <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($user['email']) ?></div>

            </div>

            <span class="badge <?= $isAdmin ? 'bg-danger' : 'bg-warning text-dark' ?> rounded-pill px-3 py-2 shadow-sm">

                <i class="fas <?= $isAdmin ? 'fa-user-shield' : 'fa-tools' ?> me-2"></i>

                <?= $isAdmin ? 'ADMIN' : 'MECHANIK' ?>

            </span>

        </div>

    </div>



    <div class="row mb-5">

        <div class="col-md-4">

            <div class="card stat-card p-4 border-start border-primary border-5">

                <h6 class="text-muted small text-uppercase fw-700">Čakajúce</h6>

                <h2 class="fw-800"><?= $status_counts['Čaká'] ?></h2>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card stat-card p-4 border-start border-warning border-5">

                <h6 class="text-muted small text-uppercase fw-700">Na zdviháku</h6>

                <h2 class="fw-800"><?= $status_counts['V procese'] ?></h2>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card stat-card p-4 border-start border-success border-5">

                <h6 class="text-muted small text-uppercase fw-700">Vybavené</h6>

                <h2 class="fw-800"><?= $status_counts['Hotové'] ?></h2>

            </div>

        </div>

    </div>



    <div class="row">

        <div class="col-lg-7 mb-4">

            <div class="chart-container">

                <h5 class="fw-bold mb-4"><i class="fas fa-chart-pie me-2 text-primary"></i>Rozdelenie prác</h5>

                <canvas id="servicesChart" height="250"></canvas>

            </div>

        </div>



        <div class="col-lg-5 mb-4">

            <div class="chart-container">

                <h5 class="fw-bold mb-4"><i class="fas fa-list-check me-2 text-primary"></i>Prehľad služieb</h5>

                <div class="list-group list-group-flush">

                    <?php foreach($service_counts as $name => $count): ?>

                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 py-3">

                            <span class="fw-600 text-secondary"><?= $name ?></span>

                            <span class="badge bg-light text-dark rounded-pill border"><?= $count ?>x</span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    </div>



    <div id="details-container">

        <div class="card stat-card p-4">

            <h4 class="fw-800 mb-4" id="details-title"></h4>

            <div id="details-list"></div>

        </div>

    </div>

</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



<script>

    const allOrders = <?= json_encode($all_orders) ?>;

    const ctx = document.getElementById('servicesChart').getContext('2d');



    const servicesChart = new Chart(ctx, {

        type: 'bar',

        data: {

            labels: <?= json_encode(array_keys($service_counts)) ?>,

            datasets: [{

                data: <?= json_encode(array_values($service_counts)) ?>,

                backgroundColor: '#3b82f6',

                borderRadius: 12,

                hoverBackgroundColor: '#1d4ed8'

            }]

        },

        options: {

            responsive: true,

            onClick: (evt, elements) => {

                if (elements.length > 0) {

                    const index = elements[0].index;

                    const category = servicesChart.data.labels[index];

                    showFilteredOrders(category);

                }

            },

            plugins: { legend: { display: false } },

            scales: {

                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },

                x: { grid: { display: false } }

            }

        }

    });



    function showFilteredOrders(category) {

        const container = document.getElementById('details-container');

        const list = document.getElementById('details-list');

        const title = document.getElementById('details-title');



        const filtered = allOrders.filter(o => o.cat === category);

        title.innerHTML = `<i class="fas fa-wrench me-2 text-primary"></i>Práce v kategórii: ${category}`;

        list.innerHTML = "";

        container.style.display = "block";



        if(filtered.length === 0) {

            list.innerHTML = "<p class='text-muted'>Žiadne objednávky pre túto službu.</p>";

        } else {

            filtered.forEach(order => {

                const badgeClass = order.stav === 'Hotové' ? 'bg-success' : (order.stav === 'V procese' ? 'bg-warning' : 'bg-danger');

                list.innerHTML += `

                    <div class="order-card-mini d-flex justify-content-between align-items-center">

                        <div>

                            <div class="fw-bold text-dark">${order.name} <span class="badge bg-dark ms-2" style="font-family: monospace;">${order.spz || '---'}</span></div>

                            <div class="small text-muted mt-1">${order.service}</div>

                        </div>

                        <div class="text-end">

                            <span class="badge ${badgeClass} text-uppercase" style="font-size: 0.65rem;">${order.stav}</span>

                            <div class="small text-muted mt-1 fw-bold">${order.datum}</div>

                        </div>

                    </div>

                `;

            });

        }

        window.scrollTo({ top: container.offsetTop - 100, behavior: 'smooth' });

    }

</script>

</body>

</html>