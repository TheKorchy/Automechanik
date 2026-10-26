<?php
include "connect_web.php";

$id=$_POST['id'];
$service=$_POST['service'];

$stmt=$conn->prepare("UPDATE odoslane SET service=? WHERE id=?");
$stmt->bind_param("si",$service,$id);
$stmt->execute();
