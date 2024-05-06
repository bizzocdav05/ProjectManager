<?php
include "utils.php";

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["email_code"])) {
    session_start();

    if ($_SESSION["email_code"] == $_GET["email_code"]) {
        $id_utente = get_utente();

        $conn = connection();
        $conn->query("UPDATE Utenti SET active=1 WHERE ID=$id_utente;");

        unset($_SESSION["email_code"]);
    }
}

header("Location: bacheche.php");
exit();
?>