<?php
    include "utils.php";

    session_start();
    $id_utente = login_required();
    set_console();

    $data = get_bacheche_list();

    // colori del tema
    $conn = connection();
    $result = $conn->query("SELECT tema FROM Utenti WHERE ID=$id_utente;");
    $data["tema"] = get_theme_colors($result->fetch_assoc()["tema"]);

    $data["nome_utente"] = get_nome_utente($id_utente);
    $data["img_profilo"] = get_user_img_profilo();
    ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!--font dei titoli-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Concert+One&display=swap" rel="stylesheet">

    <!--font del testo, possibilmente da cambiare-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <title>Le tue Bacheche</title>

    <style>
    :root {
        --background: #f3e0ad;
        --color-light: #eee;
        
        --color-primary: var(--color-primary);
        --color-secondary: var(--color-secondary);
        --color-tertiary: var(--color-tertiary);

        --color-primary: transparent;
    }

    body {
        background-color:  #f3e0ad;
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
        background-color: var(--background);
    }
    
    #login {
        position: sticky;
        top: 10%;
        left: 50%;
        transform: translateX(-50%);
        z-index: 100;
    }
        /* barra superiore della pagina*/
        .navbar {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 100;
        background-color: var(--color-primary);
        width: 100%;
    }

    /* */
    .navbar > * {
        display: flex;
        flex-direction: row;
        justify-content: space-evenly;
        align-items: center;
        height: 115px;
        background-color: var(--color-primary);
        margin: 0;
    }

    /* */
    .navbar-left > *, .navbar-right > * {
        margin: 20px;
    }

    /* tutta la pagina sotto la barra*/
    .header {
        background-color: #f3e0ad;
        height: 115vh;
        width: 100%;
    }

    /* immagine del logo nella barra superiore*/
    .logo {
        width: 170px;
        height: 170px;
    }

    /* stile "bottoni" */
    .button {
        font-family: "Concert One", sans-serif;
        font-weight: bolder;
        background-color:var(--color-tertiary);
        border: solid transparent;
        border-radius: 16px;
        border-width: 0 0 4px;
        box-sizing: border-box;
        color: #000000;
        cursor: pointer;
        display: inline-block;
        font-size:large;
        font-weight: 700;
        letter-spacing: .8px;
        line-height: 20px;
        margin: 0px 5px 0px 5px;
        overflow: visible;
        padding: 13px 16px;
        text-align: center;
        text-transform: uppercase;
        touch-action: manipulation;
        transform: translateZ(0);
        transition: filter .2s;
        vertical-align: middle;
        white-space: nowrap;
        min-width: 320px;
        width: fit-content;
        text-decoration: none;
        text-transform: none;
        
        margin-top: 30px;
        display: flex;
        align-items: center;
    }

    .button:after {
        background-clip: padding-box;
        background-color: var(--color-secondary);
        border: solid transparent;
        border-radius: 16px;
        border-width: 0 0 4px;
        bottom: -4px;
        content: "";
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        z-index: -1;
    }

    .button:hover {
        filter: brightness(1.1);
 
    }

    .button:active {
        border-width: 4px 0 0;
        background: none;
    }

    /* spazi di lavoro*/
    .writing{
        text-decoration: none;
        font-family: "Concert One", sans-serif;
        font-weight: bolder;
        font-style: normal;
        font-size: larger;
        color: #000000;
        padding
        cursor: pointer;
    }

    .writing:hover{
    color: #f3e0ad;  
    text-decoration: underline;
    cursor: pointer;
    }


 
    .bacheca-list {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: nowrap;

            overflow-x: auto;

            margin: 10px;
            
        }
	.bacheca {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            min-width: 250px;
            width: fit-content;
            height: auto;
            min-height: 50px;
            font-family: "Concert One", sans-serif;
                    
            background-color: var(--color-primary);
            color: black;
            border-radius: 10px;

            margin-top: 10px;
            padding: 30px;
            padding-right: 0px;
            padding-left:0px;
            margin-left:20px;
            margin-right: 20px;
            cursor: pointer;
            position: relative;
        } 
        
        /* popup */
    .popup .popuptext {
    visibility: hidden;
    background-color: var(--color-primary);
    color: #fff;
    font-size: 18px;
    text-align: left;
    padding-left: 8px;

    text-transform: none;

    border-radius: 6px;
    border-style: solid;
    border-color: #eee;
    border-width: 1px;

    position: absolute;
    z-index: 1;
    top: 125%;
    
    width: 400px;
    height: 300px;
    }

    .popup .show {
    visibility: visible;

    top: 65%;
    left: 227px;
    }

        /*Spazi di lavoro*/
    .popup{
        text-decoration: none;
        font-family: "Concert One", sans-serif;
        font-weight: bolder;
        font-style: normal;
        font-size: larger;
        color: #000000;
    }

    .popup:hover{
    color: #f3e0ad;  
    text-decoration: underline;
    }

    hr{
    border-color: #fff;
    border-width: 1px;
    border-style: solid;
    opacity: 0.7;
    }

    .icon{
        margin-right:10px;
        height: 20px;
        width: 20px;
        padding-left:10px;
    }

    .crea{
        font-family: "Concert One", sans-serif;
        font-weight: bolder;
        background-color:var(--color-secondary);
        border: solid var(--color-tertiary);
        border-radius: 16px;
        border-width: 0 0 4px;
        box-sizing: border-box;
        color: #000000;
        cursor: pointer;
        display: inline-block;
        font-size:large;
        font-weight: 700;
        letter-spacing: .8px;
        line-height: 20px;
        margin: 0px 5px 0px 5px;
        overflow: visible;
        padding: 13px 16px;
        text-align: center;
        text-transform: none;
        touch-action: manipulation;
        transform: translateZ(0);
        transition: filter .2s;
        vertical-align: middle;
        white-space: nowrap;
        width: 300px;
        text-decoration: none;

        margin-top: 30px;
        padding-right: 20px;
    }

    .crea:after{
        background-clip: padding-box;
        background-color: var(--color-secondary);
        border: solid var(--color-tertiary);
        border-radius: 16px;
        border-width: 0 0 4px;
        bottom: -4px;
        content: "";
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        z-index: -1;
        width: 300px;
    }

    .crea:hover{
        filter: brightness(1.1);
    }

    .crea:active{
        border-width: 4px 0 0;
    }

    .title{
        font-family: "Concert One", sans-serif;
        font-size: 25px;
        padding-right: 15px;
        color: white;
    }

    .input{
        background-color: #eee;
        font-family: "Concert One", sans-serif;
        font-size: large;

        border-width: 0px;
        border-radius: 10px;

        height: 30px;
    }

    .input:after{
        border-width: 0px;
    }

    .paragrafo{
        font-family: 'Concert One', sans-serif;
        margin-top: 35vh;
        font-size: 28px;
        padding-left: 30px;
        text-decoration:underline;
    }

    .recenti{
        font-family: 'Concert One', sans-serif;
        margin-top: 40px;
        font-size: 28px;
        padding-left: 30px;
        text-decoration:underline;
    }


    .pfp{
    border-radius: 50%;
        width: 50px;
        height: 50px;
        background-color: black;
        color: #eee;
        text-align: center;
        align-items: center;
        display: flex;
        justify-content: space-around;
        font-weight: 600;
        font-family: "Concert One", sans-serif; 
    }

    div.user-icon {
        border-radius: 50%;
        width: 70px;
        height: 70px;

        background-color: black;
        color: var(--color-light);

        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-around;

        text-align: center;
        font-weight: bold;
        font-family: "Concert One", sans-serif; 
        text-transform: uppercase;

        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
    }

    .account{
        font-family: "Concert One", sans-serif;
        font-size: larger;
        font-weight: bold;
        cursor: pointer;
    }

    .account:hover{
        text-decoration: underline;
        color: #f3e0ad;
    }

    #nuova-bacheca-popup {
        
        color: var(--color-primary);
    }

    svg.star {
        width: 20px;
        height: 20px;
        position: absolute;
        right: 5px;
        top: 5px;
    }

    svg.star.active {
        fill: #d05e26;
    }
    </style>
</head>
<body>
    <div id="popup">
        <div id="popup-box">
        </div>
    </div>

         <!--parte della barra a sinistra-->
         <div class="navbar">
            <div class="navbar-left" > 
    
                <!--immagine del logo-->
                <a href="index.php">
                <img  src="img/logo_scritta_completo.png"  class="logo">
                </a>

              
               </div>
    
                <div class="navbar-left">
					<div class="user-icon"></div>
                    <div class="account" onclick="location.href = 'account.php'" style="margin-left: 0px;">Account</div> 
              </div>

       
    </div>
    
    <!-- Lista bacheche recenti -->
    <div class="recenti"> Visualizzate di Recente </div>
    <div class="bacheca-list" id="container_recenti">
    </div>

    <!-- Lista bacheche preferite -->
    <div class="recenti"> Preferiti </div>
    <div class="bacheca-list" id="container_preferiti">
    </div>
    
    <!-- lista bacheche utente -->
    <div class="paragrafo"> Le tue Bacheche </div>
    <div class="bacheca-list" id="container">
    </div>

    <div id="nuova-bacheca" class="button" style="margin-bottom: 30px; margin-left: 30px;"> <img src="img/aggiungi.png" class="icon"> Aggiungi una nuova bacheca</div>

    <!-- content hidden -->
    <div id="bacheca-prototipo" class="bacheca bacheca-elem" style="display: none">
        <svg class="star" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path d="M9.153 5.408C10.42 3.136 11.053 2 12 2s1.58 1.136 2.847 3.408l.328.588c.36.646.54.969.82 1.182s.63.292 1.33.45l.636.144c2.46.557 3.689.835 3.982 1.776.292.94-.546 1.921-2.223 3.882l-.434.507c-.476.557-.715.836-.822 1.18-.107.345-.071.717.001 1.46l.066.677c.253 2.617.38 3.925-.386 4.506s-1.918.051-4.22-1.009l-.597-.274c-.654-.302-.981-.452-1.328-.452s-.674.15-1.328.452l-.596.274c-2.303 1.06-3.455 1.59-4.22 1.01-.767-.582-.64-1.89-.387-4.507l.066-.676c.072-.744.108-1.116 0-1.46-.106-.345-.345-.624-.821-1.18l-.434-.508c-1.677-1.96-2.515-2.941-2.223-3.882S3.58 8.328 6.04 7.772l.636-.144c.699-.158 1.048-.237 1.329-.45s.46-.536.82-1.182z" stroke="#1C274C" stroke-width="1.5"/></svg>
        <p class="bacheca-nome"><span></span></p>
    </div>

    <form id="form-nuova-bacheca" class="form-nuova-bacheca" method="post" style="display: none">
        <label for="" class="title">Nome: </label>
        <input class="input" type="text" name="nome" id=""> 

        <div>
            <input class="crea" type="submit" value="Crea Bacheca">
        </div>
    </form>


    <script>
        class FilterBacheche {
            constructor (dati) {
                this.data = dati;
            }

            by_ultimo_accesso() {
                let dati = this.data.list;
                let bacheche = []

                let indici = Object.keys(dati);
                indici.sort(function(a, b) {
                    return new Date(dati[b].ultimo_accesso) - new Date(dati[a].ultimo_accesso);
                });

                for (let i = 0; i < indici.length; i++) {
                    bacheche.push(dati[indici[i]]);
                }

                return bacheche;
            }

            by_preferiti(value=true) {
                let dati = this.data.list;
                let bacheche = [];

                for (let idx in dati)
                    if (dati[idx].preferita == value)
                        bacheche.push(dati[idx]);

                return bacheche;
            }
        }

        function show_bacheca(dati, target=$("#container")) {
            let elem = $("#bacheca-prototipo").clone(true);
            elem.attr("id", "");

            elem.attr("codice", dati.codice);
            elem.get(0).proprietario = dati.proprietario;

            elem.find("p.bacheca-nome > span").text(dati.nome.toUpperCase());

            if (dati.preferita == true) elem.find("svg.star").addClass("active");

            elem.show();
            target.append(elem);
        }

        function show_user_name(target = $("div.user-icon")) {
            if (dati["img_profilo"]["tipo"] == "default")
                target.text(dati["nome_utente"].split(" ").map(p=>p.charAt(0).toUpperCase()).join(" "));
            else
                target.css("background-image", `url('data:${dati.img_profilo.tipo};base64,${dati.img_profilo.dati}')`)
        }

        function set_theme_color(colors) {
            $("html").css("--color-primary", colors[0]);
            $("html").css("--color-secondary", colors[1]);
            $("html").css("--color-tertiary", colors[2]);
            $("html").css("--color-quaternary", colors[3]);
        }

        function mostra_tutte_bacheche() {
            $("#container-recenti, #container-preferiti, #container").empty();
            // Mostro bacheche utente
            for (let i = 0; i < dati.length; i++)
                show_bacheca(dati.list[i])


            // Mostro bacheche recenti
            filter.by_ultimo_accesso().forEach(dati_bacheca => {
                show_bacheca(dati_bacheca, $("#container_recenti"))
            });

            // Mostro bacheche preferite
            filter.by_preferiti().forEach(dati_bacheca => {
                show_bacheca(dati_bacheca, $("#container_preferiti"))
            });
        }

        // Passaggio dati
        let dati = <?php echo json_encode($data); ?>;
        
        set_theme_color(dati.tema);
        show_user_name();

        let filter = new FilterBacheche(dati);
        mostra_tutte_bacheche();


        // Event Listener globali
        $("body").on("click", "svg.star", function (e) {
            let target = $(e.currentTarget);
            let bacheca = target.closest("div.bacheca");

            e.stopImmediatePropagation();

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "toggle-preferiti",
                    "codice_bacheca": bacheca.attr("codice")
                },
                crossDomain: true,

                success: function (result) {
                    console.log(result);

                    if (target.hasClass("active"))
                        $("#container_preferiti div.bacheca[codice=" + bacheca.attr("codice") + "]").remove();
                    else
                        $("#container_preferiti").append(bacheca.clone(true));
                    
                    $("div.bacheca[codice=" + bacheca.attr("codice") + "] svg.star").toggleClass("active");
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("body").on("submit", "#nuova-bacheca-popup", (function (e) {
            e.preventDefault();

            let array = $(this).serializeArray();
            $(this).get(0).reset();
            
            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });
            console.log(data);
            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "new-bacheca",
                    "nome": data["nome"]
                },
                crossDomain: true,

                success: function (result) {
                    console.log(result);
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        dati.list.push(result.bacheca);
                        dati.length += 1;
                        filter.data = dati;
                        mostra_tutte_bacheche();
                    }
                    popup.close();
                },

                error: function (err) {
                    console.log(err);
                }
            });
        }));

        $("body").on("click", ".bacheca-list > div.bacheca-elem", function (e) {
            location.href = "bacheca.php?codice=" +encodeURIComponent($(this).attr("codice"));
        });

        let popup = $("#popup");
        popup.box = $("#popup-box");

        $("#nuova-bacheca").click(function (e) {
            e.stopPropagation();
            popup.add($("#form-nuova-bacheca"));
        });

        popup.mostra = function () {
            $(this).show();
            console.log("show")
            $(window).on("click", function (e) {
                let target = $(e.target);
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
            console.log(new_)
            new_.attr("id", "nuova-bacheca-popup");
            new_.show();
            popup.box.append(new_);

            popup.mostra();
        }

    </script>
</body>
</html>
