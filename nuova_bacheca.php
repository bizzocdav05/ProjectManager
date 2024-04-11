
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect - Nuova Bacheca</title>

</head>
<body>
    
</body>
</html>

<?php
include "utils.php";

session_start();
login_required();
$id_console = set_console();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $conn = connection();
    $action = $_POST["action"];

    if ($action == "new-bacheca" && isset($_POST["nome"])) {
        $codice = genera_codice(16, -1);
    
        // echo create_sql("INSERT INTO Bacheca", array("console", "nome", "codice"), array($_SESSION["id_console"], $_POST["nome"]));
        $result = $conn->query(create_sql("INSERT INTO Bacheca", array("console", "nome", "codice"), array($_SESSION["id_console"], $_POST["nome"], $codice)));
        

        // cerco la bacheca per inserire l'id nel codice
        $sql = "SELECT ID FROM Bacheca WHERE  id_console=$id_console AND codice='$codice';";
        $result = $conn->query($sql);

        $id_bacheca = $result->fetch_assoc()["ID"];

        // update
        $sql = "UPDATE Codici SET bacheca=$id_bacheca WHERE codice='$codice';";
        $result = $conn->query($sql);
    }
    $conn->close();
}

header("Location: bacheche.php");
exit();

?>