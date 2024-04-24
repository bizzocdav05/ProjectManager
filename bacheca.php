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

    $data["attivita"] = get_dati_attivita($id_bacheca);    

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
        /* */
        #cestino {
            width: 100px;
            height: 100px;
            display: none;
        }

        .cestino {
            margin-left: 5px;
            margin-right: 5px;
            cursor: pointer;
        }

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

        #container-isola {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: flex-start;
            flex-wrap: nowrap;

            overflow-x: auto;

            margin: 10px;
        }

        div.attivita-header {
            display: flex;
            flex-direction: revert;
            align-items: center;
            justify-content: space-between;
            width: 80%;
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

        p.text-mod > span:focus {
            display: inline-block;
            min-width: 200px;
            /* width: 110%; */
            border: 1px solid black;
            padding: 2px;
            border-radius: 5px;
        }

        div.etichetta-nuova {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
        }

        p.text-mod > span {
            min-width: 20px;
        }

        p.etichetta-text {
            width: fit-content;
            padding: 10px;
            border-radius: 5px;
        }

        div.etichetta p {
            margin: 0px;
        }

        /* div.lista-etichetta-box {
            display: flex;
            flex-wrap: wrap;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
        } */

        div.checkbox, div.commento, div.etichetta {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
        }

        p.commento-utente {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            word-wrap: break-word;
        }

        div.attivita-tabella {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            width: 100%;

            border-bottom: 1px solid black;
        }

        div.attivita-tabella:first-child {
            border-top: 1px solid black;
        }

        div.attivita-tabella > div.prima-cella {
            width: 30%;
            /* margin-top: 20px; */
        }

        div.attivita-tabella > div.seconda-cella {
            width: 30%;
            /* margin-top: 20px; */
        }

        div.attivita-tabella > div.terza-cella {
            width: 20%;
        }

        div.attivita-tabella > div.quarta-cella {
            width: 20%;
        }

        div.attivita-tabella > div.prima-cella h3.attivita-titolo {
            text-align: center;
        }

        div.attivita-tabella > div.quarta-cella p.lista-text {
            width: fit-content;
            padding: 7px;
            border-radius: 7px;
        }

        #select-type-visual > button.active {
            background-color: green;
        }

        p.scadenza-valida {
            background-color: green;
            color: white;
        }

        p.scadenza-invalida {
            background-color: red;
            color: white;
        }

        #container-calendario {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        div.calendario-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 90%;
        }

        div.settimana,
        div.intestazione {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        div.giorno,
        div.intestazione-giorno {
            text-align: center;
            width: 14.2%;
        }

        div.giorno {
            padding: 5px;
            border: 1px solid black;
            height: 30px;
        }

    </style>
</head>
<body>
    <div id="cestino" class="cestino">
        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
            <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
        </svg>
    </div>

    <div id="popup">
        <div id="popup-box">

        </div>
    </div>
    
    <div id="select-type-visual">
        <button value="isola">Isola</button>
        <button value="tabella">Table</button>
        <button class="active" value="calendario">Calendario</button>
    </div>

    <div id="container-isola" style="display: none">
        <div id="attivita-nuova" class="attivita-box">
            <h3 class="attivita-titolo">Crea nuova attività</h3>
            <div class="attivita-lista attivita-lista-isola">
               <div class="lista">
                <h2>CREA</h2>
            </div>
            </div>
        </div>
    </div>

    <div id="container-tabella" style="display: none">
        <div id="attivita-tabella-header" class="attivita-tabella tabella-header">
            <div class="prima-cella">
                <h3 class="attivita-titolo">Attivita</h3>
            </div>

            <div class="seconda-cella">
                <h3 class="attivita-titolo">Liste</h3>
            </div>

            <div class="terza-cella">
                <h3 class="attivita-titolo">Etichette</h3>
            </div>
        </div>
    </div>

    <div id="container-calendario" style="display: none">
        <div class="calendario-header"></div>
        <div class="calendario-body">
        </div>
    </div>

    <div id="attivita-prototipo-isola" class="attivita-box attivita-box-isola" style="display: none">
        <div class="attivita-header">
            <h3 class="attivita-titolo"><span></span></h3>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><circle cx="256" cy="256" r="48" fill="#fff"/><circle cx="256" cy="128" r="48" fill="#fff"/><circle cx="256" cy="384" r="48" fill="#fff"/></svg>
        </div>

        <div class="attivita-lista attivita-lista-isola">

            <div class="lista lista-nuova">
                <p class="lista-nome">+ Aggiungi una nuova lista</p>
            </div>
        </div>
    </div>

    <div id="attivita-prototipo-tabella" class="attivita-tabella" style="display: none">
        <div class="prima-cella">
            <h3 class="attivita-titolo"><span></span></h3>
        </div>

        <div class="seconda-cella">
            <p class="lista-nome"><span></span></p>
        </div>

        <div class="terza-cella">
            <div class="lista-etichetta-box"></div>
        </div>

        <div class="quarta-cella">
            <p class="lista-text lista-scadenza"></p>
        </div>
    </div>

    <div id="lista-prototipo-isola" class="lista" style="display: none">
        <p class="lista-nome"><span></span></p>
    </div>

    <div id="attivita-info-prototipo" style="display: none">
        <div class="attivita-info">
            <p class="attivita-codice">Codice: <span></span></p>
            <p class="attivita-data-creazione">Data Creazione: <span></span></p>
            <p class="attivita-data-ultima-modifica">Data ultima modifica: <span></span></p>
            <p class="attivita-titolo">Titolo: <span></span></p>
        </div>
    </div>

    <div id="attivita-nuova-prototipo" style="display: none">
        <div class="lista-info">
            <form id="form-nuova-attivita" method="post">
                <label for="">Titolo</label>
                <input type="text" name="titolo" id="" required>

                <input type="submit" value="Crea Attivita">
            </form>
        </div>
    </div>

    <div id="lista-info-prototipo" class="lista-info-box" style="display: none">
        <div class="lista-info">
            <p class="lista-codice">Codice: <span></span></p>
            <p class="lista-nome text-mod"><span></span></p>

            <p class="lista-text">Descrizione</p>
            <p class="lista-descrizione text-mod"><span></span></p>

            <p class="lista-text">Etichetta</p>
            <div class="lista-etichetta-box">
            </div>
            <div class="etichetta-nuova">
                <input type="text" name="testo" id="" placeholder="Nome">
                <input type="color" name="colore" id="">
                <button class="btn-etichetta-nuova">Crea</button>
                <button class="btn-etichetta-reset">Annulla</button>
            </div>

            <p class="lista-text">Checkbox</p>
            <div class="lista-checkbox-box">
            </div>
            <div class="checkbox-nuovo">
                <textarea name="testo" id="" cols="40" rows="2" placeholder="Nome"></textarea>
                <button class="btn-checkbox-nuovo">Crea</button>
                <button class="btn-checkbox-reset">Annulla</button>
            </div>

            <p class="lista-text">Commento</p>
            <div class="commento-nuovo">
                <textarea name="commento" id="" cols="40" rows="5" placeholder="Inserisci il tuo commento"></textarea>
                <button class="btn-commento-nuovo">Invia</button>
                <button class="btn-commento-reset">Annulla</button>
            </div>
            <div class="lista-commento-box">
            </div>

            <p class="lista-text">Scadenza</p>
            <div class="lista-scadenza-box"></div>

            <button class="lista-delete">Cancella lista</button>
        </div>
    </div>

    <div id="etichetta-prototipo" class="etichetta"  style="display: none">
        <p class="etichetta-text"><span></span></p>
        <div id="cestino" class="cestino">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
                <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
            </svg>
        </div>
    </div>

    <div id="checkbox-prototipo" class="checkbox" style="display: none">
        <div class="checkbox-item"><input type="checkbox" name="" id=""></div>
        <p class="checkbox-text text-mod"><span></span></p>
        <div id="cestino" class="cestino">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
                <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
            </svg>
        </div>
    </div>

    <div id="commento-prototipo" class="commento" style="display: none">
        <p class="commento-utente"><span></span>:</p>
        <p class="commento-text"><span></span></p>
        <div id="cestino" class="cestino">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
                <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
            </svg>
        </div>
    </div>

    <div id="scadenza-prototipo" class="scadenza" style="display: none">
        <input class="scadenza-text" type="date" name="scadenza">
        <button class="btn-scadenza-nuovo">Aggiungi scadenza</button>
        <button class="btn-scadenza-elimina">Elimina</button>
    </div>

    <div id="settimana-prototipo" class="settimana" style="display: none">
    </div>

    <div id="giorno-prototipo" class="giorno" style="display: none">
    </div>

    <div id="intestazione-prototipo" class="intestazione" style="display: none">
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Lun</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Mar</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Mer</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Gio</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Ven</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Sab</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Dom</p></div>
    </div>

    <div id="lista-nuova-prototipo" style="display: none">
        <div class="lista-info">
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
                let searchParams = new URLSearchParams(window.location.search);
                this.codice_bacheca = searchParams.get('codice');

                this.data = data;
                this.tipo = "calendario";

                this.cod_idx = {     // associa ad ogni codice l'indice (di dati)
                    "attivita": {},
                    "lista": {},
                    "checkbox": {},
                    "commento": {},
                    "etichetta": {},
                    "scadenza": {}
                };
                /*  STRUCTURE  //
                {
                    "element": {
                        "codice": 0,
                        "codice2": 1
                    },
                }
                */

                this.elements = {
                    "isola": {
                        "attivita": $('<div class="attivita-box attivita-box-isola" style="display: none"><h3 class="attivita-titolo"><span></span></h3><div class="attivita-lista attivita-lista-isola"><div class="lista lista-nuova"><p class="lista-nome">+ Aggiungi una nuova lista</p></div></div></div>'),
                        "lista": $('<div class="lista"><p class="lista-nome"><span></span></p></div>')
                    }
                }

                // init
                this.init_cod_idx();
                this.popup;
                this.init_popup();
            }

            // init
            init_popup() {
                this.popup = $("#popup");
                this.popup.box = $("#popup-box");
                
                const self = this;
                this.popup.mostra = function () {
                    self.popup.show();

                    $(window).on("click", function (e) {
                        let target = $(e.target);
                        if (target.closest(self.popup).length > target.closest(self.popup.box).length) {
                            e.preventDefault();
                            self.popup.close();
                        }
                    });
                }

                this.popup.close = function () {
                    self.popup.hide();
                    self.popup.box.empty();
                    $(window).off("click");
                }

                this.popup.add = function (elem) {
                    self.popup.box.empty();
                    let new_ = elem.clone(true);
                    // new_.attr("id", "");
                    new_.show();
                    self.popup.box.append(new_);

                    self.popup.mostra();
                }
            }

            init_cod_idx() {
                for (let i = 0; i < this.data.attivita.length; i++) {
                    let dati = this.data.attivita.list[i];
                    this.add_idx_elem("attivita", dati.info.codice, this.codice_bacheca);

                    for (let j = 0; j < dati.lista.length; j++) {
                        let dati_lista = dati.lista.list[j];
                        this.add_idx_elem("lista", dati_lista.codice, dati.info.codice);

                        for (let idx_checkbox = 0; idx_checkbox < dati_lista.checkbox.length; idx_checkbox++) {
                            let dati_checkbox = dati_lista.checkbox.list[idx_checkbox];
                            this.add_idx_elem("checkbox", dati_checkbox.codice, dati_lista.codice);
                        }

                        for (let idx_commento = 0; idx_commento < dati_lista.commento.length; idx_commento++) {
                            let dati_commento = dati_lista.commento.list[idx_commento];
                            this.add_idx_elem("commento", dati_commento.codice, dati_lista.codice);
                        }

                        for (let idx_etichetta = 0; idx_etichetta < dati_lista.etichetta.length; idx_etichetta++) {
                            let dati_etichetta = dati_lista.etichetta.list[idx_etichetta];
                            this.add_idx_elem("etichetta", dati_etichetta.codice, dati_lista.codice);
                        }

                        for (let idx_scadenza = 0; idx_scadenza < dati_lista.scadenza.length; idx_scadenza++) {
                            let dati_scadenza = dati_lista.scadenza.list[idx_scadenza];
                            this.add_idx_elem("scadenza", dati_scadenza.codice, dati_lista.codice);
                        }
                    }
                }
            }

            // method
            add_idx_elem(tipo, codice, codice_superiore, idx=null) {
                // codice_superiore è il codice dell'elemento superiore per la localizzazione

                if (idx === null) idx = Object.keys(this.cod_idx[tipo]).length;
                this.cod_idx[tipo][codice] = [idx, codice_superiore];
            }

            get_idx(tipo, codice) {
                return this.cod_idx[tipo][codice];
            }

            change_type(tipo) {
                if (["isola", "tabella", "calendario"].includes(tipo)) this.tipo = tipo;
                console.log(["isola", "tabella", "calendario"].includes(tipo));
                this.clear_html();
                this.show_dati();
            }

            create_lista(dati) {
                let elem = this.elements[this.tipo].lista.clone(true);
                elem.attr("id", dati.codice);

                elem.find("p.lista-nome > span").text(dati.nome);

                elem.show();
                // elem.insertBefore($("#" + codice_attivita + " div.lista-nuova"))
                
                elem.get(0).dati = dati;
                return elem;
            }

            create_attivita(dati) {
                let info = dati.info;
                // let elem = this.elements[this.tipo].attivita.clone(true);
                let elem = $("#attivita-prototipo-isola").clone(true);
                if (this.tipo == "tabella") elem = $("#attivita-prototipo-tabella").clone(true);

                elem.attr("id", info.codice);

                elem.find("h3.attivita-titolo > span").text(info.titolo);

                for (let i = 0; i < dati.lista.length; i++) 
                    this.create_lista(dati.lista.list[i]).insertBefore(elem.find("div.lista-nuova"));

                elem.insertBefore($("#attivita-nuova"));
                elem.show();
            }

            crea_checkbox(dati, idx=0) {
                // terminazione
                if (dati.length <= idx) return $("");

                let elem = $("#checkbox-prototipo").clone(true);
                let info = dati.list[idx];
                elem.attr("id", info.codice);

                // TODO: check
                elem.find("p.checkbox-text > span").text(info.testo);
                if (info.is_check == "true") elem.find("div.checkbox-item > input").prop("checked", true);
                else elem.find("div.checkbox-item > input").prop("checked", false);

                elem.find("#cestino").show();
                elem.find("#cestino").css("width", "15px");
                elem.find("#cestino").css("heigth", "15px");
                elem.find("#cestino").css("cursor", "pointer");
                elem.find("#cestino").attr("id", "cestino-" + info.codice);
                elem.show();
                
                return elem.add(this.crea_checkbox(dati, idx+1));  // Ricorsione per calcolarli tutti
            }

            crea_etichetta(dati, idx=0) {
                // terminazione
                if (dati.length <= idx) return $("");

                let elem = $("#etichetta-prototipo").clone(true);
                let info = dati.list[idx];
                elem.attr("id", info.codice);

                elem.find("p.etichetta-text > span").text(info.testo);
                elem.find("p.etichetta-text").css("background-color", `rgba(${info.red}, ${info.green}, ${info.blue}, 0.5)`);

                elem.find("#cestino").show();
                elem.find("#cestino").css("width", "15px");
                elem.find("#cestino").css("heigth", "15px");
                elem.find("#cestino").css("cursor", "pointer");
                elem.find("#cestino").attr("id", "cestino-" + info.codice);
                elem.show();
                
                return elem.add(this.crea_etichetta(dati, idx+1));  // Ricorsione per calcolarli tutti
            }

            crea_commento(dati, idx=0) {
                // terminazione
                if (dati.length <= idx) return $("");

                let elem = $("#commento-prototipo").clone(true);
                let info = dati.list[idx];
                elem.attr("id", info.codice);

                elem.find("p.commento-utente > span").text(info.nome_utente);
                elem.find("p.commento-text > span").text(info.testo);

                elem.find("#cestino").show();
                elem.find("#cestino").css("width", "15px");
                elem.find("#cestino").css("heigth", "15px");
                elem.find("#cestino").css("cursor", "pointer");
                elem.find("#cestino").attr("id", "cestino-" + info.codice);
                elem.show();
                
                return elem.add(this.crea_commento(dati, idx+1));  // Ricorsione per calcolarli tutti
            }

            crea_scadenza(dati) {
                let elem = $("#scadenza-prototipo").clone(true);
                console.log(elem);
                if (dati.length == 0) {
                    elem.attr("id", "");
                    elem.find("button.btn-scadenza-elimina").hide();
                    elem.find("button.btn-scadenza-nuovo").show();
                } else {
                    let info = dati.list[0];
                    elem.attr("id", info.codice);
                    elem.find("input[name='scadenza']").val(info.data);
                    elem.find("button.btn-scadenza-nuovo").hide();
                    elem.find("button.btn-scadenza-elimina").show();
                    //TODO: scaduta?
                }
                console.log(elem);
                elem.show();
                return elem;
            }

            crea_giorno(numero, valido, attivo) {
                let elem = $("#giorno-prototipo").clone(true);
                elem.attr("id", "giorno-" + numero);

                elem.text(numero + " " + attivo);
                elem.css("height", elem.css("width") + "px");
                console.log(elem.css("width") + "px")
                elem.show();
                return elem;
            }

            crea_settimana(giorni, numero) {
                let elem = $("#settimana-prototipo").clone(true);
                elem.attr("id", "settimana-" + numero);

                giorni.forEach(giorno => {
                    elem.append(this.crea_giorno(giorno.numero, giorno.is_valid, giorno.is_active));
                    // console.log(giorno) 
                });
                    

                elem.show();
                return elem;
            }

            crea_calendario(anno=2024, mese=null) {
                let date;
                if (mese === null) date = new Date(anno, new Date().getMonth());
                else date = new Date(anno, mese);
                
                console.log(date);
                this.actual_date = new Date(date);

                let giorno_settimana_mese = date.getDay() || 7;  // normalizzazione del giorno
                let data_odierna = new Date();

                // Aggiungo intestazione
                let inst = $("#intestazione-prototipo").clone(true);
                inst.attr("id", "intestazione-calendario");
                
                // Ordino in base al primo giorno
                for (let i = 0; i < giorno_settimana_mese - 1; i++)
                    inst.append(inst.children().first());
                
                    inst.show();
                inst.appendTo("#container-calendario > div.calendario-body");

                // Creo calendario
                for (let i = 0; i < 5; i++) {
                    let array_giorni = Array.from({length: 7}, (_, j) => {
                        let current_date = new Date(date);
                        current_date.setDate(i * 7 + j + 1);
                        return {
                            numero: current_date.getDate(),
                            is_valid: current_date.getMonth() == data_odierna.getMonth(),
                            is_active: current_date.getDate() == data_odierna.getDate() && current_date.getMonth() == data_odierna.getMonth()
                        };
                    });
                    // console.log(array_giorni)

                    this.crea_settimana(array_giorni, i+1).appendTo("#container-calendario > div.calendario-body");
                }
            }

            skip_month(avanti=true) {
                let date = new Date(this.actual_date);
                if (avanti) date.setMonth(date.getMonth() + 1);
                else date.setMonth(date.getMonth() - 1);

                console.log(date, this.actual_date);
                console.log(date.getFullYear(), date.getMonth())

                this.clear_html();
                $("#container-calendario").show();
                this.crea_calendario(date.getFullYear(), date.getMonth());
            }

            create_new_lista(dati, codice_attivita) {
                let idx = this.get_idx("attivita", codice_attivita)[0];
                console.log(idx);
                let idx_list = this.data.attivita.list[idx].lista.list.push(dati);
                this.data.attivita.list[idx].lista.length += 1;

                this.add_idx_elem("lista", dati.codice, codice_attivita);
                this.create_lista(dati).insertBefore($("#" + codice_attivita).find("div.lista-nuova"));
            }

            create_new_attivita(dati) {
                let idx_att = this.data.attivita.list.push(dati);
                this.data.attivita.length += 1;
                this.add_idx_elem("attivita", dati.info.codice);
                this.create_attivita(dati);
            }

            show_dati() {
                if (this.tipo == "isola") {
                    for (let i = 0; i < this.data.attivita.length; i++)
                        this.create_attivita(this.data.attivita.list[i]);
                    
                    $("#container-isola").show();
                }

                if (this.tipo == "tabella") {
                    for (let codice in this.cod_idx["lista"]) {
                        let idx_attivita = this.get_idx("attivita", this.get_idx("lista", codice)[1])[0];
                        let idx_lista = this.get_idx("lista", codice)[0];

                        let info_attivita = this.data.attivita.list[idx_attivita].info;
                        let info_lista = this.data.attivita.list[idx_attivita].lista.list[idx_lista];
                        let info_etichette = info_lista.etichetta;
                        let info_scadenza = info_lista.scadenza

                        if (info_scadenza.length == 0) continue;
                        info_scadenza = info_scadenza.list[0];

                        let elem = $("#attivita-prototipo-tabella").clone(true);
                        elem.attr("id", "attivita-" + info_attivita.codice);

                        elem.find("div.prima-cella > h3.attivita-titolo > span").text(info_attivita.titolo);
                        elem.find("div.prima-cella").attr("id", info_attivita.codice);

                        elem.find("div.seconda-cella > p.lista-nome > span").text(info_lista.nome);
                        elem.find("div.seconda-cella > p.lista-nome").attr("id", info_lista.codice);
                        
                        let elem_eti = "";
                        elem.find("div.terza-cella > div.lista-etichetta-box").append(this.crea_etichetta(info_etichette).find("p.etichetta-text"));

                        console.log(info_scadenza)
                        elem.find("div.quarta-cella > p.lista-scadenza").text(info_scadenza.data);
                        elem.find("div.quarta-cella > p.lista-scadenza").addClass((info_scadenza.valida) ? "scadenza-valida": "scadenza-invalida");
                        
                        elem.show();
                        elem.appendTo("#container-tabella");
                    }

                    $("#container-tabella").show();
                }

                if (this.tipo == "calendario") {
                    this.crea_calendario();
                    $("#container-calendario").show();
                }
            }

            clear_html() {
                $('#container-isola').children().not('#attivita-nuova').remove();
                $("#container-isola").hide();
                
                $("#container-tabella").empty();
                $("#container-tabella").hide();

                $("#container-calendario > *").empty();
                $("#container-calendario").hide();
            }

            show_lista_info(dati) {
                let elem = $("#lista-info-prototipo").clone(true);
                elem.attr("id", dati.codice);
                console.log(elem.get(0));

                // Aggiungo valori campi
                elem.find("p.lista-codice > span").text(dati.codice);
                elem.find("p.lista-nome > span").text(dati.nome);
                elem.find("p.lista-descrizione > span").text(dati.descrizione);
                
                // Aggiungo elementi
                elem.find("div.lista-checkbox-box").append(this.crea_checkbox(dati.checkbox));
                elem.find("div.lista-etichetta-box").append(this.crea_etichetta(dati.etichetta));
                elem.find("div.lista-commento-box").append(this.crea_commento(dati.commento));
                elem.find("div.lista-scadenza-box").append(this.crea_scadenza(dati.scadenza));
                console.log(dati.scadenza);

                // Aggiungo proprietà
                elem.find(".text-mod > span").attr("contenteditable", "true");
                elem.find(".text-mod > span").on("focus", e => e.currentTarget.value = $(e.currentTarget).text());
                elem.find("p.text-mod > span").on('keydown', e => { if (e.keyCode === 13) { e.preventDefault(); $(e.currentTarget).blur(); } });
                elem.find("p.text-mod > span").blur(e =>{ let target = $(e.currentTarget); if (target.get(0).value != target.text()) console.log(target.text()) });

                // Mostro
                elem.show();
                this.popup.add(elem);
            }

            show_lista_nuova(codice_attivita) {
                let elem = $("#lista-nuova-prototipo").clone(true);
                elem.attr("id");
                elem.find("form.form-nuova-lista").append($('<input>').attr({
                    type: 'hidden',
                    name: 'codice_attivita',
                    value: codice_attivita
                }));
                this.popup.add(elem);
            }

            show_attivita_nuova() {
                let elem = $("#attivita-nuova-prototipo").clone(true);
                elem.attr("id");

                this.popup.add(elem);
            }

            cancella_lista(codice_lista) {
                let idx_lista = this.get_idx("lista", codice_lista)[0];
                let idx_att = this.get_idx("attivita", this.get_idx("lista", codice_lista)[1])[0];
                
                
                delete this.cod_idx["lista"][codice_lista];
                delete this.data.attivita.list[idx_att].lista.list[idx_lista];
                this.data.attivita.list[idx_att].lista.length -= 1;

                $.ajax({url:"attivita.php",type:"POST",data:{"action":"delete-lista","codice_lista":codice_lista, "codice_bacheca":this.codice_bacheca},crossDomain:true,success:function(result){console.log(result)},error:function(err){console.log(err)}});
                $("#" + codice_lista).remove();
            }

            cancella_attivita(codice_attivita) {
                let idx = this.get_idx("attivita", codice_attivita)[0];

                delete this.cod_idx["attivita"][codice_attivita];
                delete this.data.attivita.list[idx];
                this.data.attivita.length -= 1;

                $.ajax({url:"attivita.php",type:"POST",data:{"action":"delete-lista","codice_attivita":codice_attivita, "codice_bacheca":this.codice_bacheca},crossDomain:true,success:function(result){console.log(result)},error:function(err){console.log(err)}});
                $("#" + codice_attivita).remove();
            }

            get_elem_location(tipo, codice) {
                console.log(tipo, codice);
                let [idx, cod_l] = this.get_idx(tipo, codice);
                let [idx_l, cod_a] = this.get_idx("lista", cod_l);
                let idx_a = this.get_idx("attivita", cod_a)[0];

                return [idx, idx_l, idx_a];
            }

            cancella_elemento_lista(tipo, codice, cancella=true) {
                let [idx, idx_lista, idx_attivita] = this.get_elem_location(tipo, codice);
                let codice_attivita = this.data.attivita.list[idx_attivita].info.codice;

                delete this.cod_idx[tipo][codice_attivita];
                delete this.data.attivita.list[idx_attivita].lista.list[idx_lista][tipo].list[idx];
                this.data.attivita.list[idx_attivita].lista.list[idx_lista][tipo].length -= 1;

                if (cancella) {
                    let action = "delete-" + tipo; let tipo_codice = "codice_" + tipo; 
                    $.ajax({url:"attivita.php",type:"POST",data:{"action": action, [tipo_codice]: codice, "codice_bacheca":this.codice_bacheca},crossDomain:true,success:function(result){console.log(result)},error:function(err){console.log(err)}});
                    $("#" + codice).remove();
                }
            }

            aggiungi_elemento_lista(tipo, codice_lista, dati) {
                this.add_idx_elem(tipo, dati.codice, codice_lista);
                let [idx, idx_lista, idx_attivita] = this.get_elem_location(tipo, dati.codice);

                this.data.attivita.list[idx_attivita].lista.list[idx_lista][tipo].list[idx] = dati;
                this.data.attivita.list[idx_attivita].lista.list[idx_lista][tipo].length += 1;
            }

            aggiorna_elemento_lista(tipo, codice_elemento, key, valore) {
                let [idx, idx_lista, idx_attivita] = this.get_elem_location(tipo, codice_elemento);
                this.data.attivita.list[idx_attivita].lista.list[idx_lista][tipo].list[idx][key] = valore;
            }
        }

        // Passaggio dati
        let dati = <?php echo json_encode($data)?>;
        console.log(dati);

        let searchParams = new URLSearchParams(window.location.search);
        const CODICE_BACHECA = searchParams.get('codice');

        let visual = new Visualizator(dati);
        visual.show_dati();

        $("body").on("submit", "#form-nuova-attivita", (function (e) {
            e.preventDefault();

            let array = $(this).serializeArray();
            $(this).get(0).reset();
            
            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "new-attivita",
                    "codice_bacheca": CODICE_BACHECA,
                    "titolo": data["titolo"]
                },
                crossDomain: true,

                success: function (result) {
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        visual.create_new_attivita(result.attivita);
                        visual.popup.close();
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
        }));

        $("form.form-nuova-lista").submit(function (e) {
            e.preventDefault();

            let array = $(this).serializeArray();
            // $(this).get(0).reset();
            
            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });

            let searchParams = new URLSearchParams(window.location.search);
            
            let codice_attivita = data["codice_attivita"];
            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "new-lista",
                    "codice_bacheca": CODICE_BACHECA,
                    "codice_attivita": codice_attivita,
                    "nome": data["nome"],
                    "descrizione": data["descrizione"],
                },
                crossDomain: true,

                success: function (result) {
                    console.log(result);
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        visual.create_new_lista(result.lista, codice_attivita);
                        visual.popup.close();
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("body").on("click", "button.btn-etichetta-nuova", function (e) {
            let target = $(e.currentTarget);
            let colore = target.siblings("input[name='colore']");
            let testo = target.siblings("input[name='testo']");

            console.log(colore, testo);
            if (colore.val() && testo.val()) {
                let color_hex = colore.val().replace('#', '');

                // Converti il valore esadecimale in RGB
                let red = parseInt(color_hex.substring(0, 2), 16);
                let green = parseInt(color_hex.substring(2, 4), 16);
                let blue = parseInt(color_hex.substring(4, 6), 16);

                console.log(red, green, blue); // Visualizza il colore in formato RGB

                let codice_lista = target.closest("div.lista-info-box").attr("id");
                console.log(codice_lista);

                $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "new-etichetta",
                    "codice_bacheca": CODICE_BACHECA,
                    "testo": testo.val(),
                    "red": red,
                    "green": green,
                    "blue": blue,
                    "codice_lista": codice_lista
                },
                crossDomain: true,

                success: function (result) {
                    console.log(result);
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        $("#" + codice_lista).find("div.lista-etichetta-box").append(visual.crea_etichetta(result.etichetta));
                        testo.val(""); colore.val("");
                        visual.aggiungi_elemento_lista("etichetta", codice_lista, result.etichetta);
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
            }
        });

        $("body").on("click", "button.btn-etichetta-reset", function (e) {
            $(e.currentTarget).siblings("input").val('');
        });

        $("body").on("click", "div.lista-etichetta-box div.cestino", function (e) {
            visual.cancella_elemento_lista("etichetta", $(e.currentTarget).attr("id").split("-")[1]);
        });

        $("body").on("change", "div.checkbox > div.checkbox-item > input", function (e) {
            let target = $(e.currentTarget);

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "change-checkbox",
                    "codice_bacheca": CODICE_BACHECA,
                    "is_check": (target.is(":checked")) ? "true" : "false",
                    "codice": target.closest("div.checkbox").attr("id")
                },
                crossDomain: true,

                success: function () {
                    visual.aggiorna_elemento_lista("checkbox", target.closest("div.checkbox").attr("id"), "is_check", (target.is(":checked")) ? "true" : "false")
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("body").on("click", "button.btn-checkbox-nuovo", function (e) {
            let target = $(e.currentTarget);
            let testo = target.siblings("textarea");

            let codice_lista = target.closest("div.lista-info-box").attr("id");
            if (testo.val()) {
                console.log(testo.val());
                $.ajax({
                    url: "attivita.php",
                    type: "POST",
                    data: {
                        "action": "new-checkbox",
                        "codice_bacheca": CODICE_BACHECA,
                        "testo": testo.val(),
                        "codice_lista": codice_lista
                    },
                    crossDomain: true,

                    success: function (result) {
                        console.log(result);
                        result =JSON.parse(result);
                        console.log(result);
                        if (result.esito == true) {
                            $("#" + codice_lista).find("div.lista-checkbox-box").append(visual.crea_checkbox(result.checkbox));
                            testo.val("");
                            visual.aggiungi_elemento_lista("checkbox", codice_lista, result.checkbox);
                        }
                    },

                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });

        $("body").on("click", "button.btn-checkbox-reset", function (e) {
            $(e.currentTarget).siblings("textarea").val('');
        });

        $("body").on("click", "div.lista-checkbox-box div.cestino", function (e) {
            visual.cancella_elemento_lista("checkbox", $(e.currentTarget).attr("id").split("-")[1]);
            console.log("codice:", $(e.currentTarget).attr("id").split("-")[1]);
        });

        $("body").on("click", "button.btn-commento-nuovo", function (e) {
            let target = $(e.currentTarget);
            let testo = target.siblings("textarea");

            let codice_lista = target.closest("div.lista-info-box").attr("id");
            if (testo.val()) {
                console.log(testo.val());
                $.ajax({
                    url: "attivita.php",
                    type: "POST",
                    data: {
                        "action": "new-commento",
                        "codice_bacheca": CODICE_BACHECA,
                        "testo": testo.val(),
                        "codice_lista": codice_lista
                    },
                    crossDomain: true,

                    success: function (result) {
                        console.log(result);
                        result =JSON.parse(result);
                        console.log(result);
                        if (result.esito == true) {
                            $("#" + codice_lista).find("div.lista-commento-box").append(visual.crea_commento(result.commento));
                            testo.val("");
                            visual.aggiungi_elemento_lista("commento", codice_lista, result.commento);
                        }
                    },

                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });

        $("body").on("click", "button.btn-commento-reset", function (e) {
            $(e.currentTarget).siblings("textarea").val('');
        });

        $("body").on("click", "div.lista-commento-box div.cestino", function (e) {
            visual.cancella_elemento_lista("commento", $(e.currentTarget).attr("id").split("-")[1]);
        });

        $("body").on("click", "button.btn-scadenza-nuovo", function (e) {
            let target = $(e.currentTarget);
            let data = target.siblings("input[name='scadenza']");
            
            let codice_lista = target.closest("div.lista-info-box").attr("id");
            if (data.val()) {
                console.log(data.val())
                $.ajax({
                    url: "attivita.php",
                    type: "POST",
                    data: {
                        "action": "new-scadenza",
                        "codice_bacheca": CODICE_BACHECA,
                        "data": data.val(),
                        "codice_lista": codice_lista
                    },
                    crossDomain: true,

                    success: function (result) {
                        result = JSON.parse(result);
                        console.log(result);
                        if (result.esito == true) {
                            target.parent().remove();
                            $("#" + codice_lista).find("div.lista-scadenza-box").append(visual.crea_scadenza(result.scadenza));
                            visual.aggiungi_elemento_lista("scadenza", codice_lista, result.scadenza.list[0]);
                        }
                    },

                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });

        $("body").on("click", "button.btn-scadenza-elimina", function (e) {
            visual.cancella_elemento_lista("scadenza", $(e.currentTarget).parent().attr("id"));
        });

        $("body").on("input", "div.lista-scadenza-box > div.scadenza > input", function (e) {
            let target = $(e.currentTarget);

            if (target.closest("div.scadenza").attr("id") === "") return;

            let codice_lista = target.closest("div.lista-info-box").attr("id");
            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "change-scadenza",
                    "codice_bacheca": CODICE_BACHECA,
                    "data": target.val(),
                    "codice_lista": codice_lista
                },
                crossDomain: true,

                success: function (result) {
                    console.log(result);
                    result = JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        visual.cancella_elemento_lista("scadenza", result.scadenza.list[0].codice, false);
                        visual.aggiungi_elemento_lista("scadenza", codice_lista, result.scadenza.list[0]);
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        // Informazioni lista
        $("body").on("click", "div.attivita-box div.lista", function (e) {
            let target = $(e.currentTarget);
            if (target.hasClass("lista-nuova"))
                visual.show_lista_nuova(target.closest("div.attivita-box").attr("id"));
            else if (target.closest("div.attivita-box").attr("id") == "attivita-nuova")
                visual.show_attivita_nuova();
            else
                visual.show_lista_info(target.get(0).dati);

            e.stopPropagation();
        });

        // Cancellazione lista
        $("body").on("click", "#popup div.lista-info-box button.lista-delete", function (e) {
            let target = $(e.currentTarget);
            console.log(target.closest("div.lista-info").find(".lista-codice > span").text());
            visual.cancella_lista(target.closest("div.lista-info").find(".lista-codice > span").text());
            visual.popup.close();
            e.stopPropagation();
        });

        // Bottone modalità
        $("#select-type-visual > button").on("click", function (e) {
            let elem = $(this);
            if (elem.hasClass("active")) return;

            $("#select-type-visual > button").removeClass("active");
            console.log(elem.attr("value"));
            visual.change_type(elem.attr("value"));
            elem.addClass("active");
        });

    </script>
</body>
</html>