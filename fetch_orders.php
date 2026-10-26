<?php

include "connect_web.php";
$res = $conn->query("SELECT * FROM odoslane");
$data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;
echo json_encode($data);

while($o=$res->fetch_assoc()):
    ?>
    <tr>
        <td><?=$o['id']?></td>
        <td><?=htmlspecialchars($o['service'])?></td>
        <td><?=htmlspecialchars($o['name'])?></td>
        <td><?=htmlspecialchars($o['l_name'])?></td>
        <td><?=htmlspecialchars($o['stav'])?></td>
        <td>
            <button class="btn btn-warning editBtn"
                    data-id="<?=$o['id']?>"
                    data-stav="<?=$o['stav']?>">
                Edit
            </button>
        </td>
    </tr>
<?php endwhile; ?>
