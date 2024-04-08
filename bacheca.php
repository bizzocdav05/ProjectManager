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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <title>Bacheca</title>

    <style>
        body {
            margin: 0;
        }

        #popup {
            display: none;
            width: 100vw;
            height: 100vh;

            overflow: hidden;

            position: fixed;
            top: 0;
            left: 0;

            background: rgba(145, 152, 163, 0.8);
            box-sizing: border-box;
            z-index: 200;
        }

        #popup-box {
            padding: 10px;

            width: 60vw;
            height: 60vh;

            overflow-y: auto;

            position: absolute;
            top: 20%;
            left: 20%;

            background-color: white;
        }

        #container {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: flex-start;
            flex-wrap: nowrap;

            overflow-x: auto;

            margin: 10px;
        }

        div.attivita-box > div.attivita-info {
            display: none;
        }

        div.attivita-box-isola {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            min-width: 250px;
            width: fit-content;
            height: auto;
            min-height: 50px;

            background-color: black;
            color: white;
            border: 1px solid white;
            border-radius: 10px;

            padding: 20px 0px;
        }

        div.attivita-box-isola > h3 {
            border-bottom: 1px solid white;
            width: 80%;
            font-size: 30px;
            margin-top: 0px;
        }

        div.attivita-lista-isola {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;

            padding-bottom: 10px;
            width: 100%;
        }

        p.lista-nome {
            font-size: 20px;
            /* font-weight: 700; */
            margin: 0px;
        }

        div.lista {
            text-align: left;
            width: 90%;
            border: 1px solid black;
            border-radius: 10px;
            background-color: rgba(145, 152, 163, 0.4);

            padding: 5px 0px 10px 5px;
            margin-bottom: 10px;
        }

        div.lista > p.lista-nome {
            margin: 0px;
        }

        div.lista > p.lista-nome:hover {
            cursor: pointer;
        }

        /* nuova attivta */
        #attivita-nuova div.lista {
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        #attivita-nuova div.lista:hover {
            cursor: pointer;
        }

    </style>
</head>
<body>
    <div id="popup">
        <div id="popup-box">

        </div>
    </div>
    
    <div id="select-type-visual">
        <button class="active" value="isola">Isola</button>
        <button value="isola">Table</button>
    </div>

    <div id="container">
        <div id="attivita-nuova" class="attivita-box">
            <h3 class="attivita-titolo">Crea nuova attività</h3>
            <div class="attivita-lista attivita-lista-isola">
               <div class="lista">
                <h2>CREA</h2>
                <div class="lista-info" style="display: none">
                    <form id="form-nuova-attivita" method="post">
                        <label for="">Titolo</label>
                        <input type="text" name="titolo" id="">

                        <input type="submit" value="Crea Attivita">
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- <table>
        <thead>
            <tr>
                <th>Attività</th>
                <th>Liste</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><h3 class="attivita-titolo"><span></span></h3></td>
            </tr>
        </tbody>
    </table> -->

    <!-- <tr id="attivita-prototipo-table" style="display: none">
        <td><h3 class="attivita-titolo"></h3></td>

        <div class="attivita-lista">

            <td class="lista-nuova lista">
                <p class="lista-nome">+ Aggiungi una nuova lista</p>
                <div class="lista-info" style="display: none">
                    <form class="form-nuova-lista" method="post">
                        <label for="">Nome</label>
                        <input type="text" name="nome" id="">

                        <label for="">Descrizione</label>
                        <textarea name="descrizione" id="" cols="30" rows="10"></textarea>

                        <input type="submit" value="Aggiungi Lista">
                    </form>
                </div>
            </td>
        </div>
    </tr>

    <div>
        <td class="lista-prototipo-tabella" class="attivita-box" style="display: none">
            <p class="lista-nome"><span></span></p>

            <div class="lista-info" style="display: none">
                <p class="lista-codice">Codice: <span></span></p>
                <p class="lista-nome"><span></span></p>

                <p class="lista-text">Descrizione</p>
                <p class="lista-descrizione"><span></span></p>
            </div>
        </td>
    </div> -->

    <!-- <div id="lista-prototipo-info-isola" class="lista" style="display: none">
        <p class="lista-nome"><span></span></p>

        <div class="lista-info" style="display: none">
            <p class="lista-codice">Codice: <span></span></p>
            <p class="lista-nome"><span></span></p>

            <p class="lista-text">Descrizione</p>
            <p class="lista-descrizione"><span></span></p>
        </div>
    </div>

    <div id="attivita-prototipo-isola" class="attivita-box attivita-box-isola" style="display: none">
        <h3 class="attivita-titolo"><span></span></h3>

        <div class="attivita-lista attivita-lista-isola">

            <div class="lista lista-nuova">
                <p class="lista-nome">+ Aggiungi una nuova lista</p>

                <div class="lista-info" style="display: none">
                    <form class="form-nuova-lista" method="post">
                        <label for="">Nome</label>
                        <input type="text" name="nome" id="">

                        <label for="">Descrizione</label>
                        <textarea name="descrizione" id="" cols="30" rows="10"></textarea>

                        <input type="submit" value="Aggiungi Lista">
                    </form>
                </div>
            </div>
        </div>
    </div> -->

    <div id="lista-prototipo-isola" class="lista" style="display: none">
        <p class="lista-nome"><span></span></p>
    </div>

    <div id="attivita-prototipo-isola" class="attivita-box attivita-box-isola" style="display: none">
        <h3 class="attivita-titolo"><span></span></h3>

        <div class="attivita-lista attivita-lista-isola">

            <div class="lista lista-nuova">
                <p class="lista-nome">+ Aggiungi una nuova lista</p>
            </div>
        </div>
    </div>

    <div id="attivita-info-prototipo">
        <div class="attivita-info">
            <p class="attivita-codice">Codice: <span></span></p>
            <p class="attivita-data-creazione">Data Creazione: <span></span></p>
            <p class="attivita-data-ultima-modifica">Data ultima modifica: <span></span></p>
            <p class="attivita-titolo">Titolo: <span></span></p>
        </div>
    </div>

    <div id="attivita-nuova-prototipo">
        <div class="lista-info" style="display: none">
            <form id="form-nuova-attivita" method="post">
                <label for="">Titolo</label>
                <input type="text" name="titolo" id="">

                <input type="submit" value="Crea Attivita">
            </form>
        </div>
    </div>

    <div id="lista-info-prototipo">
        <div class="lista-info" style="display: none">
            <p class="lista-codice">Codice: <span></span></p>
            <p class="lista-nome"><span></span></p>

            <p class="lista-text">Descrizione</p>
            <p class="lista-descrizione"><span></span></p>
        </div>
    </div>

    <div id="lista-nuova-prototipo">
        <div class="lista-info" style="display: none">
            <form class="form-nuova-lista" method="post">
                <label for="">Nome</label>
                <input type="text" name="nome" id="">

                <label for="">Descrizione</label>
                <textarea name="descrizione" id="" cols="30" rows="10"></textarea>

                <input type="submit" value="Aggiungi Lista">
            </form>
        </div>
    </div>

    <script>
        class Visualizator {
            constructor (data) {
                this.tipi = ["isola", "table"];
                this.data;
                this.tipo = "isola";

                this.cod_idx = {};  // associa ad ogni codice l'indice (di dati)

                this.elements = {
                    "isola": {
                        "attivita": $('<div class="attivita-box attivita-box-isola" style="display: none"><h3 class="attivita-titolo"><span></span></h3><div class="attivita-lista attivita-lista-isola"><div class="lista lista-nuova"><p class="lista-nome">+ Aggiungi una nuova lista</p></div></div></div>'),
                        "lista": $('<div class="lista"><p class="lista-nome"><span></span></p></div>')
                    }
                }
            }

            add_idx_attivita(idx, codice) {
                this.cod_idx[codice] = {idx, "list"};
            }

            add_idx_lista(idx, codice, cod_attivita) {
                this.cod_idx.cod_attivita.list[codice] = idx;
            }

            init_cod_idx() {
                for (let i = 0; i < data.attivita.length; i++) {
                    let dati = data.attivita.list[i];
                    this.add_idx_attivita(i, dati.info.codice);

                    for (let i = 0; i < dati.lista.length; i++) {
                        let dati_lista = dati.lista.list[i];
                        this.add_idx_lista(i, dati_lista.codice, dati.info.codice);
                    }
                }
            }

            change_type(tipo) {
                if (this.tipi.includes(tipo)) this.tipo = tipo;
            }

            create_lista(dati, codice_attivita) {
                let elem = this.elements[this.tipo].lista.clone(true);
                elem.attr("id", dati.codice);

                elem.find("p.lista-nome > span").text(dati.nome);

                elem.show();
                elem.insertBefore($("#" + codice_attivita + " div.lista-nuova"))
            }

            create_attivita(dati) {
                let info = dati.info;
                let elem = this.elements[this.tipo].attivita.clone(true);
                elem.attr("id", info.codice);

                elem.find("p.attivita-codice > span").text(info.codice);

                for (let i = 0; i < dati.lista.length; i++)
                    create_lista(dati.lista.list[i]).insertBefore(elem.find("div.lista-nuova"));

                elem.insertBefore($("#attivita-nuova"));
                elem.show();
            }
        }

        let type = "isola";
        function get_attivita_prop() {
            return $("#attivita-prototipo-" + type);
        }

        function get_lista_prop() {
            return $("#lista-prototipo-" + type);
        }

        function create_lista(dati) {
            let elem = get_lista_prop().clone(true);
            console.log(elem);
            elem.attr("id", dati.codice);

            elem.find("p.lista-codice > span").text(dati.codice);
            elem.find("p.lista-nome > span").text(dati.nome);
            elem.find("p.lista-descrizione > span").text(dati.descrizione);

            elem.show();
            return elem;
        }

        function show_attivita(dati) {
            let info = dati.info;
            let elem = get_attivita_prop().clone(true);
            elem.attr("id", info.codice);

            elem.find("p.attivita-codice > span").text(info.codice);
            elem.find("p.attivita-data-creazione > span").text(info.data_creazione);
            elem.find("p.attivita-data-ultima-modifica > span").text(info.data_ultima_modifica);
            elem.find(".attivita-titolo > span").text( info.titolo);

            for (let i = 0; i < dati.lista.length; i++)
                create_lista(dati.lista.list[i]).insertBefore(elem.find("div.lista-nuova"));

            elem.insertBefore($("#attivita-nuova"));
            elem.show();
        }

        // Passaggio dati
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
                        $("#" + codice_attivita + " div.attivita-lista").append(create_lista(result.lista));
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        let popup = $("#popup");
        popup.box = $("#popup-box");

        popup.mostra = function () {
            popup.show();

            $(window).on("click", function (e) {
                target = $(e.target);
                if (target.closest(popup).length > target.closest(popup.box).length) {
                    e.preventDefault();
                    popup.close();
                }
            });
        }

        popup.close = function () {
            popup.hide();
            popup.box.empty();
            $(window).off("click");
        }

        popup.add = function (elem) {
            popup.box.empty();
            let new_ = elem.clone(true);
            new_.show();
            popup.box.append(new_);

            popup.mostra();
        }

        $("div.lista").on("click", function (e) {
            popup.add($(this).find("div.lista-info"));
            e.stopPropagation();
        });

        $("#select-type-visual > button").on("click", function (e) {
            if ($(this).hasClass("active")) return;

            $("#select-type-visual > button").removeClass("active");
            $(this).addClass("active");
        });

    </script>
</body>
</html>