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

        p.lista-nome > span:focus {
            display: inline-block;
            min-width: 200px;
            border: 1px solid black;
            padding: 2px;
            border-radius: 5px;
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
            <p class="lista-nome"><span></span></p>

            <p class="lista-text">Descrizione</p>
            <p class="lista-descrizione"><span></span></p>

            <button class="lista-delete">Cancella lista</button>
        </div>
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
                this.data = data;
                this.tipo = "isola";

                this.cod_idx = {     // associa ad ogni codice l'indice (di dati)
                    "attivita": {},
                    "lista": {},
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

                let searchParams = new URLSearchParams(window.location.search);
                this.codice_bacheca = searchParams.get('codice');

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
                    new_.attr("id", "");
                    new_.show();
                    self.popup.box.append(new_);

                    self.popup.mostra();
                }
            }

            init_cod_idx() {
                for (let i = 0; i < this.data.attivita.length; i++) {
                    let dati = this.data.attivita.list[i];
                    this.add_idx_attivita(dati.info.codice);

                    for (let j = 0; j < dati.lista.length; j++) {
                        let dati_lista = dati.lista.list[j];
                        this.add_idx_lista(dati_lista.codice, dati.info.codice);
                    }
                }
            }

            // method
            add_idx_attivita(codice, idx=Object.keys(this.cod_idx["attivita"]).length) {
                this.cod_idx["attivita"][codice] = idx;
            }

            add_idx_lista(codice, idx_attivita, idx=Object.keys(this.cod_idx["lista"]).length) {
                this.cod_idx["lista"][codice] = [idx, idx_attivita];
            }

            get_idx(tipo, codice) {
                console.log(tipo, codice);
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

            create_new_lista(dati, codice_attivita) {
                let idx = this.get_idx("attivita", codice_attivita);
                console.log(idx);
                let idx_list = this.data.attivita.list[idx].lista.list.push(dati);
                this.data.attivita.list[idx].lista.length += 1;

                this.add_idx_lista(dati.codice, codice_attivita);
                this.create_lista(dati).insertBefore($("#" + codice_attivita).find("div.lista-nuova"));
            }

            create_new_attivita(dati) {
                let idx_att = this.data.attivita.list.push(dati);
                this.data.attivita.length += 1;
                this.add_idx_attivita(dati.info.codice);
                this.create_attivita(dati);
            }

            show_dati() {
                for (let i = 0; i < this.data.attivita.length; i++) {
                    this.create_attivita(this.data.attivita.list[i]);
                } 
            }

            show_lista_info(dati) {
                console.log("lista info")
                let elem = $("#lista-info-prototipo").clone(true);
                elem.attr("id", "");
                
                elem.find("p.lista-codice > span").text(dati.codice);
                elem.find("p.lista-nome > span").text(dati.nome);
                elem.find("p.lista-nome > span").attr("contenteditable", "true");
                elem.find("p.lista-descrizione > span").text(dati.descrizione);
                elem.find("p.lista-descrizione > span").attr("contenteditable", "true");

                elem.find("p.lista-nome > span").change(() => console.log($(this).text()));

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
                let idx_att = this.get_idx("attivita", this.get_idx("lista", codice_lista)[1]);
                
                
                delete this.cod_idx["lista"][codice_lista];
                delete this.data.attivita.list[idx_att].lista.list[idx_lista];
                this.data.attivita.list[idx_att].lista.length -= 1;

                $.ajax({url:"attivita.php",type:"POST",data:{"action":"delete-lista","codice_lista":codice_lista, "codice_bacheca":this.codice_bacheca},crossDomain:true,success:function(result){console.log(result)},error:function(err){console.log(err)}});
                $("#" + codice_lista).remove();
            }

            cancella_attivita(codice_attivita) {
                let idx = this.get_idx("attivita", codice_attivita);

                delete this.cod_idx["attivita"][codice_attivita];
                delete this.attivita.list[idx];
                this.data.attivita.length -= 1;

                $.ajax({url:"attivita.php",type:"POST",data:{"action":"delete-lista","codice_attivita":codice_attivita, "codice_bacheca":this.codice_bacheca},crossDomain:true,success:function(result){console.log(result)},error:function(err){console.log(err)}});
                $("#" + codice_attivita).remove();
            }
        }

        // Passaggio dati
        let dati = <?php echo json_encode($data)?>;
        console.log(dati);

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
                    "codice_bacheca": searchParams.get('codice'),
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