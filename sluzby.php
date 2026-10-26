<?php
$servername = "localhost";
$username = "root";      // zmeň podľa phpMyAdmin
$password = "";          // zmeň podľa phpMyAdmin
$dbname = "autoservis";

// vytvorenie pripojenia
$conn = new mysqli($servername, $username, $password, $dbname);

// kontrola pripojenia
if ($conn->connect_error) {
    die("Chyba pripojenia: " . $conn->connect_error);
}

// načítanie záznamov
$sql = "SELECT id, nazov, popis, cena, vytvorene FROM sluzby";
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>Zoznam služieb</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="p-5">

<h1>Zoznam služieb</h1>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Názov</th>
        <th>Popis</th>
        <th>Cena (€)</th>
        <th>Vytvorené</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row["id"]."</td>
                    <td>".$row["nazov"]."</td>
                    <td>".$row["popis"]."</td>
                    <td>".$row["cena"]."</td>
                    <td>".$row["vytvorene"]."</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Žiadne služby nenájdené</td></tr>";
    }
    ?>
    </tbody>
</table>

</body>
</html>

<?php
$conn->close();
?>
