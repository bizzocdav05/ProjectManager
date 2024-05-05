<?php
    include "utils.php";

    session_start();
    login_required();
    set_console();

    $data = get_bacheche_list();

    $conn->close();
    ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <title>Le tue Bacheche</title>

    <style>
        body {
            background-color:  #f3e0ad;
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
            z-index: 101;
            padding: 20px;
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
            background-color: #e0ab23;
            width: 100%;
        }

        /* */
        .navbar > * {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: center;
            height: 100px;
            background-color: #e0ab23;
            margin: 0;
        }

        /* */
        .navbar-left > *, .navbar-right > * {
            margin: 20px;
        }

        /* tutta la pagina sotto la barra*/
        .header {
            background-color: #f3e0ad;
            height: 100vh;
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
            background-color:#8f411a;
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
            width: 100%;
            text-decoration: none;
        }

        .button:after {
            background-clip: padding-box;
            background-color: #d05e26;
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

        /*"Chi siamo"/"Funzioni" */
        .writing{
            text-decoration: none;
            font-family: "Concert One", sans-serif;
            font-weight: bolder;
            font-style: normal;
            font-size: larger;
            color: #000000;
        }

        .writing:hover{
        color: #f3e0ad;  
        text-decoration: underline;
        }

            /* stile "slogan" */
        .title{
            font-family: "Concert One", sans-serif;
            font-weight: bolder;
            padding-top: 120px;
            margin-right: 80px;
            margin-left: 90px;
        }

            /*immagini laterali superiori */
        .top_img{
            height: 300px;
            width: 300px;
            margin-right: 40px;
            margin-top: 70px;

        }
            /*immagini laterali superiori */
        .top_img{
            height: 300px;
            width: 300px;
            margin-right: 40px;
            margin-top: 70px;

        }

            /*immagini laterali superiori */
        .top_img{
            height: 300px;
            width: 300px;
            margin-right: 40px;
            margin-top: 70px;

        }

            /* parte superiore dell'header*/
        .top_bar{
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            
        }

        /* parte intermedia dell'header*/
        .middle_bar{
            display: flex;
            flex-direction:column;
            background-color: #f3e0ad;
        }

        /* titolo istruzioni*/
        .subtitle{
            font-family: "Concert One", sans-serif;
            font-weight: bolder;
            margin-left: 90px;
            padding-top: 200px;
        }

        /* introduzione istruzioni*/
        .text{
            font-family: "Outfit", sans-serif;
            font-weight:900px;
            font-optical-sizing: auto;
            font-weight: 100px;
            font-style: normal;
            font-size: 25px;
            margin-left: 90px;
        }

        /* titoli liste*/
        .paragraph_title{
            font-family: "Concert One", sans-serif;
        font-optical-sizing: auto;
        font-weight: 100px;
        font-style: normal;
        margin-left: 130px;
        }

        /* body liste*/
        .lista{
            font-family: "Outfit", sans-serif;
            font-optical-sizing: auto;
            font-weight: 900px;
            font-size: larger;
            font-style: normal;
            margin-left: 130px; 
        }


        /* dropdown di "Funzioni"*/
        .dropdown {
        position: relative;
        display: inline-block;
        }

        /* contenuto del dropdown*/
        .dropdown-content {
            font-family: "Concert One", sans-serif;
            font-weight: bold;
            justify-content: flex-start;
            align-items: center;
            width: 130vh;
            text-align: center;
            height: 30vh;
        }

        .dropdown-content > div {
            justify-content: center;
            align-items: center;
        }

        .dropdown-content > .dropwdown-elem {
            justify-content: flex-start;
            align-items: center;
            width: 50%;
        }


        .dropdown-content > div {
            justify-content: center;
            align-items: center;
        }

        .dropdown-content > .dropwdown-elem {
            justify-content: flex-start;
            align-items: center;
            width: 50%;
        }

        .dropdown-content {
        visibility: hidden;
        position: absolute;
        z-index: 1;
        }

        .dropdown:hover .dropdown-content {
        visibility: visible;
        background-color: #f7eccd;
        border-radius: 16px;
        }

        .dropdown:hover .dropdown-content {
        visibility: visible;
        background-color: #f7eccd;
        border-radius: 16px;
        }

        /* barra superiore del dropdown*/
        .top-row-dropdwn {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: center;
            flex-wrap: nowrap;
            padding-top: 10px;
            padding-left: 10px;
            padding-bottom: 10px;
            padding-right: 10px;
        }

        /* parte inferiore del dropdown*/
        .bottom-row-dropdwn {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: baseline;
            flex-wrap: nowrap;
            padding-top: 10px;
            padding-left: 10px;
            padding-bottom: 10px;
            padding-right: 10px;
        }

        /* icone all'interno di "Funzioni" */
        .icons{
            width: 50px;
            height: 50px;
        }

        /* stile link all'interno di "Funzioni" */
        .Funzioni_link{
        text-decoration: none;
        color: #000000;
        }

        .Funzioni_link:hover{
            text-decoration: underline;
        }

        /* barra per il bottone e l'ultima frase*/
        .testo{
            margin-left: 30px;
            margin-top: 20px;
            font-family: "Concert One", sans-serif;
        }

        /* ultima frase della pagina*/
        .slogan{
            font-family: "Concert One", sans-serif;
            font-weight: bolder;
            padding-bottom: 20px;
        }

        /* barra per il bottone e l'ultima frase*/
        .bottom_bar{
            display: flex;
            flex-direction: column;
            background-color: #f3e0ad;
            align-items: center;
            padding-top: 15vh;
            padding-bottom: 100px;
        }

        #container {
                display: flex;
                flex-direction: row;
                justify-content: space-evenly;
                align-items: flex-start;
                flex-wrap: nowrap;
        }

        /* barra per il bottone e l'ultima frase*/
        .bottom_bar{
            display: flex;
            flex-direction: column;
            background-color: #f3e0ad;
            align-items: center;
            padding-top: 15vh;
            padding-bottom: 100px;
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
    
    </style>
</head>
<body>
    <div id="popup">

    </div>

    <!--parte della barra a sinistra-->
    <div class="navbar">
        <div class="navbar-left" > 

            <!--immagine del logo-->
            <a href="../index.html">
            <img  src="img/logo_scritta_completo.png"  class="logo">
            </a>
        
            <!--"Chi Siamo"-->
            <div class="dropdown">
            <a href="../chi_siamo.html" style="text-decoration: none;">
            <span class="writing">Chi siamo</span>
            </a>
            </div>

            <!--"Funzioni"-->
            <div class="dropdown">
            <a href="../Funzioni.html" style="text-decoration: none;">
            <span class="writing">Funzioni</span>
            </a>
            <div class="dropdown-content">
                <div>

                    <!-- dropdown di "Funzioni"-->

    <!-- barra superiore di "Funzioni"-->
    <div class="top-row-dropdwn">
                    <div class="drowdown-elem">
                        <a href="../Funzioni.html" class="Funzioni_link">
                        <img src="img/easy_to_use.png" class="icons">
                        <p class="dropdown-title">Semplicità d'Uso</p>
                        </a>
                    </div>

                    <div class="drowdown-elem">
                        <a href="../Funzioni.html" class="Funzioni_link">
                        <img src="/img/data_centralization.png" class="icons">
                        <p class="dropdown-title">Centralizzazione dei Dati</p>
                        </a>
                    </div>

                    <div class="drowdown-elem">     
                        <a href="../Funzioni.html" class="Funzioni_link">
                        <img src="img/collaboration_icon.png" class="icons">
                        <p class="dropdown-title">Collaborazione Fluida</p>
                        </a>
                    </div>

    </div>

    <!-- barra inferiore di "Funzioni"-->
    <div class="bottom-row-dropdwn">
                    <div class="drowdown-elem">
                        <a href="../Funzioni.html" class="Funzioni_link">
                        <img src="/img/real_time.png" class="icons">
                        <p class="dropdown-title">Monitoraggio in Tempo Reale</p>
                        </a>
                    </div>

                    <div class="drowdown-elem">
                        <a href="../Funzioni.html" class="Funzioni_link">
                        <img src="/img/esigenze.png" class="icons">
                        <p class="dropdown-title">Adattabilità alle Esigenze</p>
                        </a>
                    </div>

                    <div class="drowdown-elem">
                        <a href="../Funzioni.html" class="Funzioni_link">
                        <img src="/img/data_security.png" class="icons">
                        <p class="dropdown-title">Sicurezza dei Dati</p>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- barra destra-->
        <div class="navbar-right" style="margin-right: 10%;"> 
            <a class="button" href="../login.html"> Log In </a>
            <a class="button" href="../singup.html"> Sing Up</a>
        </div>
    </div>


    <!-- content hidden -->
    <div id="bacheca-prototipo" class="bacheca bacheca-elem" style="display: none">
        <p class="bacheca-nome"><span></span></p>
    </div>

    <form id="form-nuova-bacheca" class="form-nuova-bacheca" method="post" style="display: none">
        <label for="">Nome: </label>
        <input type="text" name="nome" id="">

        <input type="submit" value="Crea">
    </form>

    <!-- lista bacheche -->
    <div class="bacheca-list" id="container">
        <div id="nuova-bacheca" class="bacheca">Crea nuova bacheca</div>
    </div>


    <script>
        // Passaggio dati
        let dati = <?php echo json_encode($data); ?>;

        function show_bacheca(dati) {
            let elem = $("#bacheca-prototipo").clone(true);
            elem.attr("id", dati.codice);

            elem.find("p.bacheca-nome > span").text(dati.nome.toUpperCase());
            elem.show();
            return elem;
        }

        for (let i = 0; i < dati.length; i++)
            show_bacheca(dati.list[i]).appendTo("#container");

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
                        show_bacheca(result.bacheca).appendTo("#container");
                    }
                    popup.close();
                },

                error: function (err) {
                    console.log(err);
                }
            });
        }));

        $("body").on("click", "#container > div.bacheca-elem", function (e) {
            location.href = "bacheca.php?codice=" +encodeURIComponent($(this).attr("id"));
        });

        let popup = $("#popup");

        $("#nuova-bacheca").click(function (e) {
            e.stopPropagation();
            popup.add($("#form-nuova-bacheca"));
        });

        popup.mostra = function () {
            $(this).show();
            console.log("show")
            $(window).on("click", function (e) {
                let target = $(e.target);
                if (!target.closest(popup).length) {
                    e.preventDefault();
                    console.log("hide");
                    popup.hide();
                    popup.empty();

                    popup.close();
                }
            });
        }

        popup.close = function () {
            popup.hide();
            popup.empty();
            $(window).off("click");
        }

        popup.add = function (elem) {
            popup.box.empty();
            let new_ = elem.clone(true);
            console.log(new_)
            new_.attr("id", "nuova-bacheca-popup");
            new_.show();
            new_.appendTo(popup)

            popup.mostra();
        }
    </script>
</body>
</html>