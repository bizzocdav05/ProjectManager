<?php
include "utils.php";

session_start();
login_required();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nome"])) {
    $conn = connection();

    $id_console = set_console();
    $codice = genera_codice(16);
    
    echo create_sql("INSERT INTO Bacheca", array("console", "nome", "codice"), array($_SESSION["id_console"], $_POST["nome"]));
    $result = $conn->query(create_sql("INSERT INTO Bacheca", array("console", "nome", "codice"), array($_SESSION["id_console"], $_POST["nome"], $codice)));

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect - Nuova Bacheca</title>
    <!-- <meta http-equiv="refresh" content="0; URL=bacheche.php"> -->

</head>
<body>
    
</body>
</html>