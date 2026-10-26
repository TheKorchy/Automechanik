<?php
include "connect_web.php";

$counts = ['Čaká'=>0,'Diagnostika'=>0,'Oprava'=>0,'Hotové'=>0];
$res = $conn->query("SELECT stav, COUNT(*) as cnt FROM odoslane GROUP BY stav");
while($row=$res->fetch_assoc()){
    $counts[$row['stav']] = intval($row['cnt']);
}

$data = [
    'labels'=>array_keys($counts),
    'counts'=>array_values($counts),
    'total'=>array_sum($counts),
    'diag'=>$counts['Diagnostika'],
    'repair'=>$counts['Oprava'],
    'done'=>$counts['Hotové']
];

header('Content-Type: application/json');
echo json_encode($data);
