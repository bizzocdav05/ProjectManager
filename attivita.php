<?php
include "utils.php";

session_start();
login_required();
$id_console = set_console();

date_default_timezone_set('CET');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    $conn = connection();

    // cerco bacheca attraverso il codice
    if (isset($_POST["codice"])) {
        $codice = $_POST["codice"];
        $sql = "SELECT ID FROM Bacheca WHERE codice='$codice' AND console=$id_console ;";
        $result = $conn->query($sql);

        if ($result->num_rows == 0) {
            
        }

        if ($result->num_rows > 0) {
            $id_bacheca = $result->fetch_assoc()["ID"];
        }
    }

    if ($action == "new-attivita") {
        if (isset($_POST["titolo"])) {
            $data = date("Y-m-d");
            $sql = create_sql("INSERT INTO Attivita",
                array("titolo, data_creazione, data_ultima_modifica", "bacheca"),
                array($_POST["titolo"], $data, $data, $id_bacheca));
            $result = $conn->query($sql);
        }
    }
}
?>