<?php
include "utils.php";

session_start();
login_required();
$id_console = set_console();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["codice"])) {
    $codice_bacheca = $_GET["codice"];
    $conn = connection();
    $id_utente = $_SESSION["id_utente"];
    $privilegi = 0;

    // controllo tu abbia l'autorizzazione per tale bacheca
    $sql = "SELECT ID FROM Bacheca Where codice='$codice_bacheca' AND console=$id_console;";
    $result = $conn->query($sql);

    if (!$result) {
        echo "<h1>Questa bacheca non esiste</h1>";
        exit();
    }

    if ($result->num_rows == 0) {
        $sql = "SELECT bacheca, privilegi FROM Bacheca_assoc WHERE other=$id_utente AND codice='$codice_bacheca';";
        $result_assoc = $conn->query($sql);

        if ($result_assoc->num_rows > 0) {
            $data = $result_assoc->fetch_assoc();
            $id_bacheca = $data["bacheca"];
            $privilegi = $data["privilegi"];
        } else {
            echo "<h1>Non puoi accede a questa bacheca</h1>";
            exit();
        }
    }

    if (!isset($id_bacheca)) {
        $id_bacheca = $result->fetch_assoc()["ID"];
    }

    //TODO: considerare l'idea di utilizzare questa pagina come API e mostra il tutto in una hmtl usando un JSON come struttura

    // mostro attività
    $sql = "SELECT data_creazione, data_ultima_modifica, titolo FROM Attivita WHERE bacheca=$id_bacheca;";
    $result = $conn->query($sql);

    echo "numero attività mostrate: $result->num_rows";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p>Attività: " . $row['titolo'] . "</p>";

            // lista -> checkbox, etichette, commenti, ...
            echo "<hr><br>";
        }
    }

    //TOOD: crea nuovo elemento (attività, lista, checkbox, ...) -> attivita.php

    //TODO: gestione accessi

} else {
    header("Location: bacheche.php");  // in caso vi accedi con POST o non specifici il codice della bacheca
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <title>Bacheca</title>
</head>
<body>
    <form id="form-nuova-attivita" method="post">
        <label for="">Titolo</label>
        <input type="text" name="titolo" id="">

        <input type="submit" value="Crea Attivita">
    </form>

    <script>
        $("#form-nuova-attivita").submit(function (e) {
            e.preventDefault();

            let array = $(this).serializeArray();

            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });

            data["action"] = "new-attivita";
            let searchParams = new URLSearchParams(window.location.search);
            data["codice"] = searchParams.get('codice');

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    data
                },
                crossDomain: true,

                success: function (result) {
                    console.log(result);
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });


    </script>
</body>
</html>