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

        div.etichetta {
            width: fit-content;
            padding: 10px;
            border-radius: 5px;
        }

        div.etichetta p {
            margin: 0px;
        }

        div.lista-etichetta-box {
            display: flex;
            flex-wrap: wrap;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
        }

        div.checkbox {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
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
        <button class="active" value="isola">Isola</button>
        <button value="isola">Table</button>
    </div>

    <div id="container">
        <div id="attivita-nuova" class="attivita-box">
            <h3 class="attivita-titolo">Crea nuova attività</h3>
            <div class="attivita-lista attivita-lista-isola">
               <div class="lista">
                <h2>CREA</h2>
            </div>
            </div>
        </div>
    </div>

    <div id="attivita-prototipo-isola" class="attivita-box attivita-box-isola" style="display: none">
        <h3 class="attivita-titolo"><span></span></h3>

        <div class="attivita-lista attivita-lista-isola">

            <div class="lista lista-nuova">
                <p class="lista-nome">+ Aggiungi una nuova lista</p>
            </div>
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
            </div>

            <p class="lista-text">Checkbox</p>
            <div class="lista-checkbox-box">
            </div>
            <div class="checkbox-nuovo">
                <textarea name="testo" id="" cols="50" rows="5" placeholder="Nome"></textarea>
                <button class="btn-checkbox-nuovo">Crea</button>
                <button class="btn-checkbox-reset">Annulla</button>
            </div>

            <p class="lista-text">Commento</p>
            <div class="lista-commento-box">
            </div>

            <button class="lista-delete">Cancella lista</button>
        </div>
    </div>

    <div id="etichetta-prototipo" class="etichetta"  style="display: none">
        <p class="etichetta-text text-mod"><span></span></p>
    </div>

    <div id="checkbox-prototipo" class="checkbox">
        <div class="checkbox-item"><input type="checkbox" name="" id=""></div>
        <p class="checkbox-text text-mod"><span></span></p>
        <div id="cestino" class="cestino">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
                <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
            </svg>
        </div>
    </div>

    <div id="commento-prototipo" class="commento" style="display: none">
        <p class="commento-text text-mod"><span></span></p>
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
                this.tipo = "isola";

                this.cod_idx = {     // associa ad ogni codice l'indice (di dati)
                    "attivita": {},
                    "lista": {},
                    "checkbox": {}
                };
                /*  STRUCTURE  //
                {
                    "attivita": {
                        "codice": 0,
                        "codice2": 1
                    },
                    "lista": {
                        "codice": 0,
                        "codice2": 1
                    },
                    "element": {
                        "codice": 0,
                        "codice2": 1
                    }
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
                            this.add_idx_elem("checkbox", dati_commento.codice, dati_lista.codice);
                        }

                        for (let idx_etichetta = 0; idx_etichetta < dati_lista.etichetta.length; idx_etichetta++) {
                            let dati_etichetta = dati_lista.etichetta.list[idx_etichetta];
                            this.add_idx_elem("checkbox", dati_etichetta.codice, dati_lista.codice);
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
                if (["isola", "table"].includes(tipo)) this.tipo = tipo;
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
                let elem = this.elements[this.tipo].attivita.clone(true);
                elem.attr("id", info.codice);

                // elem.find("p.attivita-codice > span").text(info.codice);
                elem.find("h3.attivita-titolo > span").text(info.titolo);

                for (let i = 0; i < dati.lista.length; i++) 
                    this.create_lista(dati.lista.list[i]).insertBefore(elem.find("div.lista-nuova"));

                elem.insertBefore($("#attivita-nuova"));
                elem.show();
            }

            crea_checkbox(dati, idx=0) {
                console.log(dati, idx);
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
                
                return elem.add(this.crea_checkbox(dati, idx+1));  // Ricorsione per calcolarli tutti
            }

            crea_etichetta(dati, idx=0) {
                console.log(dati);
                // terminazione
                if (dati.length <= idx) return $("");

                let elem = $("#etichetta-prototipo").clone(true);
                let info = dati.list[idx];
                elem.attr("id", info.codice);

                elem.find("p.etichetta-text > span").text(info.testo);
                elem.css("background-color", `rgba(${info.red}, ${info.green}, ${info.blue}, 0.5)`);
                elem.show();
                
                return elem.add(this.crea_etichetta(dati, idx+1));  // Ricorsione per calcolarli tutti
            }

            crea_commento(dati, idx=0) {
                // terminazione
                if (dati.length <= idx) return $("");

                let elem = $("#commento-prototipo").clone(true);
                let info = dati.list[idx];
                elem.removeAttr("id");

                elem.find("p.commento-text > span").text(info.testo);
                
                return elem.add(this.crea_etichetta(dati, idx+1));  // Ricorsione per calcolarli tutti
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
                for (let i = 0; i < this.data.attivita.length; i++) {
                    this.create_attivita(this.data.attivita.list[i]);
                } 
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
                elem.find("div.lista-etichetta-box").append(this.crea_etichetta(dati.commento));

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

            cancella_checkbox(codice_checkbox) {
                let idx = this.get_idx("checkbox", codice_checkbox)[0];
                console.log(idx);
                
                let codice_lista = this.get_idx("checkbox", codice_checkbox)[1];
                let idx_lista = this.get_idx("lista", codice_lista)[0];

                console.log(codice_lista, idx_lista);

                let codice_attivita = this.get_idx("lista", codice_lista)[1];
                console.log(codice_attivita);
                let idx_attivita = this.get_idx("attivita", codice_attivita)[0];

                delete this.cod_idx["checkbox"][codice_attivita];
                delete this.data.attivita.list[idx_attivita].lista.list[idx_lista].checkbox.list[idx];
                this.data.attivita.list[idx_attivita].lista.list[idx_lista].checkbox.length -= 1;

                $.ajax({url:"attivita.php",type:"POST",data:{"action":"delete-checkbox","codice_checkbox":codice_checkbox, "codice_bacheca":this.codice_bacheca},crossDomain:true,success:function(result){console.log(result)},error:function(err){console.log(err)}});
                $("#" + codice_checkbox).remove();
            }
        }

        // Passaggio dati
        let dati = <?php echo json_encode($data)?>;
        console.log(dati);

        let searchParams = new URLSearchParams(window.location.search);
        const CODICE_BACHECA = searchParams.get('codice');

        let visual = new Visualizator(dati);

        // for (let i = 0; i < dati.attivita.length; i++) {
        //     // show_attivita(dati.attivita.list[i]);
        //     visual.create_attivita();
        // }

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
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
            }
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

        $("body").on("click", "div.cestino", function (e) {
            visual.cancella_checkbox($(e.currentTarget).attr("id").split("-")[1]);
            console.log("codice:", $(e.currentTarget).attr("id").split("-")[1]);
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
            if ($(this).hasClass("active")) return;

            $("#select-type-visual > button").removeClass("active");
            $(this).addClass("active");
        });

    </script>
</body>
</html>