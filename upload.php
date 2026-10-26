<?php
if (isset($_POST['upload'])) {
    $uploadDir = "uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['files']['name'] as $key => $name) {
        $tmp_name = $_FILES['files']['tmp_name'][$key];
        $size = $_FILES['files']['size'][$key];
        $error = $_FILES['files']['error'][$key];

        if ($error === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($tmp_name);

            if (in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {

                if ($size < 5 * 1024 * 1024) {
                    $newName = uniqid() . "_" . basename($name);
                    $destination = $uploadDir . $newName;

                    if (move_uploaded_file($tmp_name, $destination)) {
                        echo "Súbor $name bol úspešne nahraný.<br>";
                    } else {
                        echo "Nepodarilo sa nahrať súbor $name.<br>";
                    }
                } else {
                    echo "Súbor $name je príliš veľký.<br>";
                }
            } else {
                echo "Súbor $name nie je podporovaný obrázok.<br>";
            }
        } else {
            echo "Chyba pri nahrávaní súboru $name.<br>";
        }
    }
}
?>
