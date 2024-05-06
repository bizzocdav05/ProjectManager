<?php
include "utils.php";

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["email_code"])) {
    session_start();

    if ($_SESSION["email_code"] == $_GET["email_code"]) {
        $id_utente = get_utente();

        $conn = connection();
        $conn->query("UPDATE Utenti SET active=true WHERE ID=$id_utente;");
    }
}

header("Location: bacheche.php");
exit();
?>