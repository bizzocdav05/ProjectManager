<?php
    include "utils.php";

    session_start();
    login_required();
    set_console();

    function visualizza_bacheca($nome, $codice) {
        echo "<p>Bacheca: $nome <a href='bacheca.php?codice=$codice'>Entra</a></p>";  //TODO: Aggiungi condividi
    }

    if (isset($_SESSION["id_utente"])) {
        echo "loggato<br>";  //TODO: Logout
        $conn = connection();

        $id_utente = $_SESSION["id_utente"];
        $id_console = $_SESSION["id_console"];
        
        // Bacheche
        $array["bacheche"] = array();
        $sql = "SELECT ID, nome, codice FROM Bacheca WHERE console=$id_console;";
        $result_bacheca = $conn->query($sql);
        
        echo "numero bacheche mostrate: $result_bacheca->num_rows<br>";
        if ($result_bacheca->num_rows > 0) {
            while($row_bacheca = $result_bacheca->fetch_assoc()) {
                visualizza_bacheca($row_bacheca["nome"], $row_bacheca["codice"]);
            }
        }
    }
    else {
        echo "<a href='login.html'>Login necessario</p>";
    }

    $conn->close();
    ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <title>Le tue Bacheche</title>
</head>
<body>
    <form action="nuova_bacheca.php" method="post">
        <label for="">Nome: </label>
        <input type="text" name="nome" id="">

        <input type="submit" value="Crea">
    </form>
</body>
</html>