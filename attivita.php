<?php
include "utils.php";

session_start();
login_required();
date_default_timezone_set('CET');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];
    
    $conn = connection();

    if ($action == "new-attivita") {
        if (isset($_POST["titolo"])) {
            $data = date("Y-m-d");
            $sql = create_sql("INSERT INTO Attivita",
                array("titolo, data_creazione, data_ultima_modifica", "bacheca"),
                array($_POST["titolo"], $data, $data));
            $result = $conn->query($sql);
        }
    }
}
?>