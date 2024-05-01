<?php
include "utils.php";

login_required();
$id_utente = get_utente();

$data = array();
$conn = connection();
$result = $conn->query("SELECT nome, cognome, mail FROM Utenti WHERE ID = $id_utente;")->fetch_assoc();

$data["email_utente"] = $result["mail"];
$data["nome_utente"] = $result["nome"];
$data["cognome_utente"] = $result["cognome"];

?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Font dei titoli -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Concert+One&display=swap" rel="stylesheet">

    <!-- <link rel="stylesheet" href="header_navbar.css"> -->

    <title>Il tuo profilo</title>

    <style>
        :root {
            --background: #f3e0ad;
            --color-light: #eee;
            
            --color-primary: #e0ab23;
            --color-secondary: #d05e26;
            --color-tertiary: #8f411a;
        }

        body{
            margin: 0px;
            background-color: var(--background);
            overflow-x: hidden;
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

            width: 60vh;
            height: 40vh;

            overflow-y: auto;

            position: absolute;
            top: 20%;
            left: 35%;

            background-color: var(--color-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-around;

            border-radius: 16px;
        }

        /* Barra superiore della pagina*/
        .navbar {
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            align-items: center;
            
            background-color: var(--color-primary);

            font-family: "Concert One", sans-serif;
            overflow: hidden;
        }

        #img-logo {
            width: 170px;
            height: auto;
        }

        .pfp{
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
        }

        div.navbar-right {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: center;
            width: 20%;
        }

        p.text-format {
            background-color: rgb(224, 171, 35, 0);
            border-width: 0px;
            font-family: "Concert One", sans-serif;
            font-size: 16px;
            color: black;
        }

        h1.text-format {
            font-weight: bold;
            font-size: 50px;
            cursor: pointer;
        }

        #container {
            margin: auto;
            margin-top: 5%;

            width: 80%;
            display: flex;
            flex-direction: row;
            justify-content: stretch;
            align-items: start;
        }

        div.content-left {
            width: 60%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: start;
        }

        div.content-right {
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }

        div.content-right > * {
            margin-top: 10%;
        }

        form {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: start;
        }

        div.form-content, div.form-content-button {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 20px;
        }

        div.form-content-button {
            width: 100%;
            justify-content: space-evenly;
            margin-top: 20px;
        }

        form p.text-format {
            min-width: 150px;
            margin: 0;
            font-size: 20px;
        }

        div.form-content input {
            border-radius: 5px;
            background-color: var(--color-light);

            font-size: large;
            border: 1px solid black;
            padding: 5px;
        }

        .btn {
            font-family: "Concert One", sans-serif;
            border: 0;
            margin: 0;
            padding: 5px;
            border-radius: 5px;

            background-color: var(--color-primary);
            color: black;
            
            text-align: center;
            font-weight: bold;
            font-size: 20px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--color-secondary);
            font-size: 20px;
        }

        .btn-red {
            background-color: var(--color-tertiary);
            font-size: 25px;
        }

        div.content-right button {
            font-size: 25px;
        }

        form p.save-success {
            color: green;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        form p.save-success {
            color: red;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        div.separator {
            height: 1px;
            background-color: black;
            width: 90%;
            margin: 20px 0px 40px 10px;
        }

        input[type="text"]:read-only {
            text-align: center;
            background-color: unset;
        }

        #form-password-utente p.text-format {
            min-width: 200px;
        }

    </style>
</head>
<body>
    <div id="popup">
        <div id="popup-box">
        </div>
    </div>

    <nav class="navbar">
        <div class="navbar-left"> 
            <!-- Immagine del logo -->
            <img id="img-logo" src="img/logo_scritta_completo.png" class="logo">
        </div>

        <div class="navbar-center">
            <h1 class="text-format">IL TUO PROFILO TORG</h1>
        </div>

        <div class="navbar-right">
            <div class="pfp"><h2></h2></div>
        </div>
    </nav>

    <div id="container">
        <div class="content-left">

            <form class="info">
                
            </form>

            <div class="separator"></div>

            <form id="form-dati-utente" method="post">
                <p class="text-format save-success" style="display: none">Tutte le modifiche sono state salvate!</p>

                <div class="form-content">
                    <label for="inp-nome-utente"><p class="text-format">Nome</p></label>
                    <input type="text" name="nome" id="inp-nome-utente" spellcheck="false">
                </div>

                <div class="form-content">
                    <label for="inp-cognome-utente"><p class="text-format">Cognome</p></label>
                    <input type="text" name="cognome" id="inp-cognome-utente" spellcheck="false">
                </div>

                <div class="form-content">
                    <label for="inp-email-utente"><p class="text-format">Email</p></label>
                    <input type="email" name="email" id="inp-email-utente" spellcheck="false">
                </div>

                <div class="form-content-button">
                    <input type="submit" class="btn btn-primary" value="Salva">
                    <input type="reset" class="btn" value="Annulla">
                </div>
            </form>

            <div class="separator"></div>

            <form method="post" id="form-password-utente">
                <p class="text-format save-invalid" style="display: none"></p>

                <div class="form-content">
                    <label for="inp-old_ps-utente"><p class="text-format">Vecchia Password</p></label>
                    <input type="text" name="password_vecchia" id="inp-old_ps-utente" spellcheck="false" required>
                </div>

                <div class="form-content">
                    <label for="inp-new_ps1-utente"><p class="text-format">Nuova Password</p></label>
                    <input type="password" name="password_1" id="inp-new_ps1-utente" required>
                </div>

                <div class="form-content">
                    <label for="inp-new_ps2-utente"><p class="text-format">Conferma Password</p></label>
                    <input type="password" name="password_2" id="inp-new_ps2-utente" required>
                </div>

                <div class="form-content-button">
                    <input type="submit" class="btn btn-primary" value="Cambia Password">
                    <input type="reset" class="btn" value="Annulla">
                </div>
            </form>

        </div>
        <div class="content-right">
            <button class="btn btn-primary" id="btn-logout"><p class="text-format">LOGOUT</p></button>
            <button class="btn btn-primary" id="btn-password-reset"><p class="text-format">RESETTA PASSWORD</p></button>
            <button class="btn btn-red" id="btn-delete-account"><p class="text-format">CANCELLA ACCOUNT</p></button>
        </div>
    </div>

    <script>
        function init_popup() {
            popup = $("#popup");
            popup.box = $("#popup-box");
            
            const self = this;
            popup.mostra = function () {
                popup.show();

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
                // new_.attr("id", "");
                new_.show();
                popup.box.append(new_);

                popup.mostra();
            }
        }
        
        function init_dati_utente() {
            $("div.pfp > h2").text(dati["nome_utente"][0] + " " + dati["cognome_utente"][0]);
            $("#inp-email-utente").val(dati["email_utente"]);
            $("#inp-nome-utente").val(dati["nome_utente"]);
            $("#inp-cognome-utente").val(dati["cognome_utente"]);
        }

        let popup;
        let dati = <?php echo json_encode($data); ?>;

        init_dati_utente();

        $("#img-logo").click(() => location.href = "index.html");

        $("#form-dati-utente").submit(function (e) {
            let target = $(e.currentTarget);
            e.preventDefault();
            let array = target.serializeArray();
            
            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });
            console.log(data);
            
            $.ajax({
                url: "server.php",
                type: "POST",
                data: {
                    "action": "update-user-data",
                    "nome": data["nome"],
                    "cognome": data["cognome"],
                    "email": data["email"]
                },
                crossDomain: true,

                success: function (result) {
                    dati["nome_utente"] = data["nome"];
                    dati["cognome_utente"] = data["cognome"];
                    dati["email_utente"] = data["email"];

                    target.find("p.save-success").show();
                    target.find("input[type='text'], input[type='email']").css("background-color", "#1ac94f");

                    setTimeout(() => {
                        target.find("p.save-success").hide();
                        target.find("input[type='text'], input[type='email']").css("background-color", "");
                    }, 2000);
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("#form-password-utente").submit(function (e) {
            let target = $(e.currentTarget);
            e.preventDefault();

            if (target.find("input[name='password_vecchia']").val()
                && target.find("input[name='password_1']").val() && target.find("input[name='password_2']").val()) {

                if (target.find("input[name='password_1']").val() != target.find("input[name='password_2']").val()) {
                    target.find("p.save-invalid").show();
                    target.find("p.save-invalid").text("Le due nuove password non corrispondoo");
                    setTimeout(() => {
                        target.find("p.save-invalid").hide();
                    }, 2000);
                    return;
                }

                let array = target.serializeArray();
                
                let data = {};
                array.forEach((elem) => {
                    data[elem["name"]] = elem["value"];
                });
                console.log(data);

                
                $.ajax({
                    url: "server.php",
                    type: "POST",
                    data: {
                        "action": "change-password",
                        "password_vecchia": data["password_vecchia"],
                        "password_1": data["password_1"],
                        "password_2": data["password_2"],
                    },
                    crossDomain: true,

                    success: function (result) {
                        dati["nome_utente"] = data["nome"];
                        dati["cognome_utente"] = data["cognome"];
                        target.find("p.save-success").show();

                        setTimeout(() => {
                            target.find("p.save-success").hide();
                        }, 2000);
                    },

                    error: function (err) {
                        console.log(err);
                    }
                });
            }
            else {
                target.find("p.save-invalid").show();
                target.find("p.save-invalid").text("Compila tutti i campi con le tue password")
                setTimeout(() => {
                    target.find("p.save-invalid").hide();
                }, 2000);
            }
                ;
        });
        
        $("#form-dati-utente").on("reset", function(e) {
            e.preventDefault();

            $("#inp-nome-utente").val(dati["nome_utente"]);
            $("#inp-cognome-utente").val(dati["cognome_utente"]);
        })

        $("#btn-logout").click(() => locatoin.href = "logout.php");
        $("#btn-password-reset").click( () => 
            $.ajax({
                url: "server.php",
                type: "POST",
                data: {
                    "action": "password_reset"
                },
                success: function () {
                    location.href = "login.html";
                },
                error: function (err) {
                    console.log(err);
                }
            })
        );

        $("#btn-delete-account").click( () => 
            $.ajax({
                url: "server.php",
                type: "POST",
                data: {
                    "action": "delete_account"
                },
                success: function () {
                    location.href = "login.html";
                },
                error: function (err) {
                    console.log(err);
                }
            })
        );
        
    </script>
</body>
</html>