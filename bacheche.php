<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <title>Le tue Bacheche</title>
</head>
<body>
    <?php
    function visualizza_bacheca($nome) {

    }

    session_start();
    if (isset($_SESSION["id_utente"])) {
        $id_utente = $_SESSION["id_utente"];
        
        // Bacheche
        $array["bacheche"] = array();
        $sql = "SELECT ID, nome FROM Bacheca WHERE console=$id_console;";
        $result_bacheca = $conn->query($sql);
        
        if ($result_bacheca->num_rows > 0) {
            while($row_bacheca = $result_bacheca->fetch_assoc()) {
                visualizza_bacheca($row_bacheca["nome"])
            }
        }
    }
    ?>
</body>
</html>