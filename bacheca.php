<?php
include "utils.php";

session_start();
login_required();
$id_console = set_console();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["codice"])) {
    $data = array();

    $conn = connection();
    $codice_bacheca = $_GET["codice"];
    $id_utente = $_SESSION["id_utente"];

    // controllo tu abbia l'autorizzazione per tale bacheca
    $temp = set_bacheca($codice_bacheca);
    $id_bacheca = $temp[0];
    $privilegi = $temp[1];

    $data["codice_bacheca"] = $codice_bacheca;
    $data["privilegi"] = $privilegi;

    //TODO: considerare l'idea di utilizzare questa pagina come API e mostra il tutto in una hmtl usando un JSON come struttura

    // mostro attività
    $sql = "SELECT ID, data_creazione, data_ultima_modifica, titolo, codice FROM Attivita WHERE bacheca=$id_bacheca;";
    $result = $conn->query($sql);

    // echo "numero attività mostrate: $result->num_rows";
    $data["attivita"] = array("length" => $result->num_rows, "list" => array());

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dati = array("info" => array(
                "data_creazione" => $row["data_creazione"],
                "data_ultima_modifica" => $row["data_ultima_modifica"],
                "titolo" => $row["titolo"],
                "codice" => $row["codice"])
            );

            // cerco liste
            $sql = "SELECT nome, descrizione, data_creazione, data_ultima_modifica, codice FROM Lista WHERE attivita='" . $row["ID"] . "';";
            $result_lista = $conn->query($sql);

            $dati["lista"]["length"] = $result_lista->num_rows;
            $dati["lista"]["list"] = array();

            if ($result_lista->num_rows > 0) {
                while ($row_lista = $result_lista->fetch_assoc()) {
                    $dati_lista = array(
                        "data_creazione" => $row_lista["data_creazione"],
                        "data_ultima_modifica" => $row_lista["data_ultima_modifica"],
                        "nome" => $row_lista["nome"],
                        "descrizione" => $row_lista["descrizione"],
                        "codice" => $row_lista["codice"]
                    );

                    array_push($dati["lista"]["list"], $dati_lista);
                }
            }

            // lista -> checkbox, etichette, commenti, ...
            
            // inserisco le informazioni
            array_push($data["attivita"]["list"], $dati);
        }
    }

    //TOOD: crea nuovo elemento (attività, lista, checkbox, ...) -> attivita.php

    //TODO: gestione accessi

    // echo "<script> let dati=" . json_encode($data) . ";</script>";
    $conn->close();

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
    <div id="container">        
    </div>

    <form id="form-nuova-attivita" method="post">
        <label for="">Titolo</label>
        <input type="text" name="titolo" id="">

        <input type="submit" value="Crea Attivita">
    </form>

    <div id="lista-prototipo" style="display: none">
            <p class="lista-codice">Codice: <span></span></p>
            <p class="lista-nome">Nome: <span></span></p>
            <p class="lista-descrizione">Descrizione: <span></span></p>
    </div>

    <div id="attivita-prototipo" class="attivita-box" style="display: none">
        <div class="attivita-info">
            <p class="attivita-codice">Codice: <span></span></p>
            <p class="attivita-data-creazione">Data Creazione: <span></span></p>
            <p class="attivita-data-ultima-modifica">Data ultima modifica: <span></span></p>
            <p class="attivita-titolo">Titolo: <span></span></p>
        </div>

        <h3>Liste:</h3>
        <ul class="attivita-lista">
            <!-- <li>Lista n</li> -->
        </ul>

        <form class="form-nuova-lista" method="post">
            <label for="">Nome</label>
            <input type="text" name="nome" id="">

            <label for="">Descrizione</label>
            <textarea name="descrizione" id="" cols="30" rows="10"></textarea>

            <input type="submit" value="Aggiungi Lista">
        </form>
    </div>

    <script>
        function create_lista(dati) {
            let elem = $("#lista-prototipo").clone(true);
            elem.attr("id", dati.codice);

            elem.find("p.lista-codice > span").text(dati.codice);
            elem.find("p.lista-nome > span").text(dati.nome);
            elem.find("p.lista-descrizione > span").text(dati.descrizione);

            elem.show();
            return elem;
        }

        function show_attivita(dati) {
            let info = dati.info;
            let elem = $("#attivita-prototipo").clone(true);
            elem.attr("id", info.codice);

            elem.find("p.attivita-codice > span").text(info.codice);
            elem.find("p.attivita-data-creazione > span").text(info.data_creazione);
            elem.find("p.attivita-data-ultima-modifica > span").text(info.data_ultima_modifica);
            elem.find("p.attivita-titolo > span").text( info.titolo);

            for (let i = 0; i < dati.lista.length; i++)
                elem.find(".attivita-lista").append($("<li>").append(create_lista(dati.lista.list[i])));

            elem.appendTo("#container");
            elem.show();

            $("#container").append("<hr>");
        }

        let dati = <?php echo json_encode($data)?>;
        console.log(dati);

        for (let i = 0; i < dati.attivita.length; i++) {
            show_attivita(dati.attivita.list[i]);
        }

        $("#form-nuova-attivita").submit(function (e) {
            e.preventDefault();

            let array = $(this).serializeArray();
            $(this).get(0).reset();
            
            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });

            let searchParams = new URLSearchParams(window.location.search);

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "new-attivita",
                    "codice_bacheca": searchParams.get('codice'),
                    "titolo": data["titolo"]
                },
                crossDomain: true,

                success: function (result) {
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        show_attivita(result.attivita);
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("div.attivita-box form.form-nuova-lista").submit(function (e) {
            e.preventDefault();

            let array = $(this).serializeArray();
            // $(this).get(0).reset();
            
            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });

            let searchParams = new URLSearchParams(window.location.search);
            let codice_attivita = $(this).parent().attr("id");
            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "new-lista",
                    "codice_bacheca": searchParams.get('codice'),
                    "codice_attivita": codice_attivita,
                    "nome": data["nome"],
                    "descrizione": data["descrizione"],
                },
                crossDomain: true,

                success: function (result) {
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true)
                        $("#" + codice_attivita + " ul.attivita-lista").append($("<li>").append(create_lista(result.lista)));
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });
            

    </script>
</body>
</html>