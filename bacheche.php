<?php
    include "utils.php";

    session_start();
    login_required();
    set_console();

    if (isset($_SESSION["id_utente"])) {
        $data = array();
        $conn = connection();

        $id_utente = $_SESSION["id_utente"];
        $id_console = $_SESSION["id_console"];
        
        // Bacheche
        $sql = "SELECT ID, nome, codice FROM Bacheca WHERE console=$id_console;";
        $result_bacheca = $conn->query($sql);
        
       	$data = array("length" => $result_bacheca->num_rows, "list" => array());
        if ($result_bacheca->num_rows > 0) {
            while($row_bacheca = $result_bacheca->fetch_assoc()) {
                array_push($data["list"], array( "nome" => $row_bacheca["nome"], "codice" => $row_bacheca["codice"]));
            }
        }
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

    <style>
        body {
            background-color:  #f3e0ad;
        }
        #popup {
            display: none;
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

        .navbar > * {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: center;
            height: 100px;
            background-color: #e0ab23;
            margin: 0;
        }

        .navbar-left > *, .navbar-right > * {
            margin: 20px;
        }

        .header {
            background-color: #f3e0ad;
            height: 100vh;
            width: 100%;
        }

        #login {
            position: sticky;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
        }

        .logo {
            width: 170px;
            height: 170px;
        }

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
            }



        .bacheca
        {
            font-family: "Concert One", sans-serif;
            font-weight: bolder;

            border: none;
            color: black;
            
            padding: 16px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            transition-duration: 0.4s;
            cursor: pointer;
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

        .writing{
            text-decoration: underline;
            font-family: "Concert One", sans-serif;
            font-weight: bolder;
            font-style: normal;
            font-size: larger;
        }

        .title{
            font-family: "Concert One", sans-serif;
            font-weight: bolder;
            margin-top: 0px;
            padding-top: 120px;
            margin-right: 80px;
            margin-left: 90px;
        }

        .img_text{
            height: 300px;
            width: 300px;
            margin-right: 40px;
            margin-top: 70px;

        }

        .bar{
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            
        }

    </style>
</head>
<body>
    <div id="popup">

    </div>

    <div class="navbar"> <!--tutta la barra-->

        <div class="navbar-left" > <!--logo  e titolo -->
            <img  src="img/logo_scritta_completo.png"  class="logo">
            

            <p class="writing">Chi siamo</p>
            <p class="writing">Soluzioni</p>

        </div>

        <div class="navbar-right" style="margin-right: 10%;"> <!--barra destra-->
            <p class="button">Log In</p>
            <p class="button">Sign Up</p>
        </div>
    </div>

    <!-- content hidden -->
    <div id="bacheca-prototipo" class="bacheca bacheca-elem" style="display: none">
        <p class="bacheca-nome"><span></span></p>
    </div>

    <form id="form-nuova-bacheca" method="post" style="display: none">
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

        $("#form-nuova-bacheca").submit(function (e) {
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
                    "action": "new-bacheca",
                    "nome": data["nome"]
                },
                crossDomain: true,

                success: function (result) {
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        show_bacheca(result.bacheca);
                    }
                    popup.close();
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("#container > div.bacheca-elem").click(function (e) {
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
                e.preventDefault();
                if (!$(e.target).closest(popup).length) {
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
            popup.empty();
            let new_ = elem.clone(true);
            new_.attr("id", "");
            console.log(new_)
            new_.show();
            new_.appendTo(popup)

            popup.mostra();
        }
    </script>
</body>
</html>