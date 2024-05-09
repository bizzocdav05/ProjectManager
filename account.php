<?php
include "utils.php";


$id_utente = login_required();

$data = array();
$conn = connection();
$result = $conn->query("SELECT nome, cognome, mail FROM Utenti WHERE ID = $id_utente;")->fetch_assoc();

$data["email_utente"] = $result["mail"];
$data["nome_utente"] = $result["nome"];
$data["cognome_utente"] = $result["cognome"];

$img_profilo = get_user_img_profilo();
if ($img_profilo == false) {
    $data["img_profilo"] = "default";
} else {
    $data["img_profilo"] = $img_profilo;
}

// colori del tema
$result = $conn->query("SELECT tema FROM Utenti WHERE ID=$id_utente;");
$data["nome_tema"] = $result->fetch_assoc()["tema"];
$data["tema"] = get_theme_colors( $data["nome_tema"]);
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
        --color-quaternary:#c9991f;
        --color-quaternary: #c9991f

        --color-primary: transparent;
    }

    body{
        margin: 0px;
        background-color: var(--background);
        overflow-x: hidden;
        padding-bottom: 30px;
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
        cursor: pointer;
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
        color: black;
    }

    h1.text-format {
        font-weight: bold;
        font-size: 50px;
        cursor: pointer;
    }

    #container {
        margin-right: 25px;
        margin-top: 0px;
        margin-left: auto;
        
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
        align-items: flex-end;
    }

    div.content-right > * {
        margin-top: 15px;
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
        font-family: "Concert One", sans-serif;
        min-width: 250px;
    }

    .btn {
        font-family: "Concert One", sans-serif;
        background-color: var(--color-primary);
        border: solid var(--color-quaternary);
        border-radius: 16px;
        border-width: 0 0 4px;
        box-sizing: border-box;
        color: #000000;
        cursor: pointer;
        display: inline-block;
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
        text-decoration: none;
        font-size: 20px;
        text-transform: none;
    }
   

    .btn-red {
        background-color: var(--color-tertiary);
        font-size: 25px;
    }

    div.content-right button {
        font-size: 18px;
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

    .btn-lat {
        font-family: "Concert One", sans-serif;
        background-color: var(--color-secondary);
        border-color: var(--color-tertiary);
    }
    
    
    
    #preview-img-profilo > img {
        width: 200px;
        height: auto;
        border-radius: 50%;
    }

    div.pfp-image {
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
    }

    .btn > p.text-format {
        margin: 0;
    }

    div.color-theme-box {
        display: flex;
        flex-direction: row;
        justify-content: space-evenly;
        align-items: center;

        width: 50%;
    }

    div.color-theme-elem {
        padding: 10px;
        border-radius: 16px;
        cursor: pointer;
    }

    div.color-theme-elem p {
        margin: 0;
        font-size: 25px;
        font-weight: bolder;
        user-select: none;
    }

    div.color-theme-elem[colore="giallo"] {
        border: solid #c9991f;
    	border-radius: 16px;
    	border-width: 0 0 4px;
    	box-sizing: border-box;
        background-color: var(--color-primary);
    }

    div.color-theme-elem[colore="blu"] {
        background-color: #1db1fd;
    }

    div.color-theme-elem.active p {
        color: var(--color-light);
    }
    
    .theme{
        margin: 0;
        font-size: 20px;
        font-family: "Concert One", sans-serif;
        padding-right: 20px;
    }
    
    .theme{
    min-width: 150px;
    margin: 0;
    font-size: 20px;
    font-family: "Concert One", sans-serif;
    }
    
        .btn-primary {
        font-family: "Concert One", sans-serif;
        background-color: var(--color-secondary);
        border-color: var(--color-tertiary);
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
            <h1 class="text-format">Il tuo profilo Torg</h1>
        </div>

        <div class="navbar-right">
            <div class="pfp"><h2></h2></div>
        </div>
    </nav>

    <div id="container">
        <div class="content-left">
            <div class="separator"></div>
            
                        <form id="form-immagine-profilo" method="post">
                <div id="preview-img-profilo"></div>

                <div class="form-content">
                    <label for="inp-immagine-profilo"><p class="text-format">Immagine</p></label>
                    <input type="file" name="immagine" id="inp-immagine-profilo" accept="image/*" required>
                </div>

                <div class="form-content-button">
                    <input type="submit" class="btn btn-primary" value="Carica">
                    <input type="reset" class="btn" value="Annulla">
                </div>
            </form>

            <div class="separator"></div>

            <div class="color-theme-box">
                <p class="theme">Tema</p>
                <div class="color-theme-elem" colore="giallo"><p class="text-format">Giallo</p></div>
                <div class="color-theme-elem" colore="blu"><p class="text-format">Azzurro</p></div>
            </div>

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
            <button class="btn btn-primary btn-lat" id="btn-logout"><p class="text-format">Log Out</p></button>
            <button class="btn btn-primary btn-lat" id="btn-password-reset"><p class="text-format">Reset Password</p></button>
            <button class="btn btn-red btn-lat" id="btn-delete-account"><p class="text-format">Cancella Account</p></button>
        </div>
    </div>

    <script>
        function set_theme_color(colors) {
            $("html").css("--color-primary", colors[0]);
            $("html").css("--color-secondary", colors[1]);
            $("html").css("--color-tertiary", colors[2]);
            $("html").css("--color-quaternary", colors[3]);
        }

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
            if (dati["img_profilo"]["tipo"] == "default") {
                $("div.pfp").removeClass("pfp-image");
                $("div.pfp > h2").show();
                $("div.pfp > h2").empty().text(dati["nome_utente"][0] + " " + dati["cognome_utente"][0]);
            } else {
                $("div.pfp").css("background-image", `url('data:${dati.img_profilo.tipo};base64,${dati.img_profilo.dati}')`);
                $("div.pfp").addClass("pfp-image");
                $("div.pfp > h2").hide();
            }

            $("#inp-email-utente").val(dati["email_utente"]);
            $("#inp-nome-utente").val(dati["nome_utente"]);
            $("#inp-cognome-utente").val(dati["cognome_utente"]);
        }

        let popup;
        let dati = <?php echo json_encode($data); ?>;

        set_theme_color(dati.tema);
        $("div.color-theme-elem[colore=" + dati.nome_tema + "]").addClass("active");

        init_dati_utente();

        $("#img-logo").click(() => location.href = "index.php");

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

        $("#btn-logout").click(() => location.href = "logout.php");
        
        $("#btn-password-reset").click( () => 
            $.ajax({
                url: "server.php",
                type: "POST",
                data: {
                    "action": "password-reset"
                },
                success: function (result) {
                    console.log(result);
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
                    "action": "delete-account"
                },
                success: function () {
                    location.href = "login.html";
                },
                error: function (err) {
                    console.log(err);
                }
            })
        );
        
        $("#form-immagine-profilo").submit(function (e) { 
            e.preventDefault();
            let target = $(e.currentTarget);

            let form_data = new FormData(this);

            form_data.append("action", "new-profile-image");
            form_data.append("file_immagine", target.find("input[type='file']").get(0).files[0]);
            console.log(form_data);
            $.ajax({
                url: "server.php",
                type: "POST",
                
                data: form_data,
                processData: false,
                contentType: false,

                success: function (result) {
                    result = JSON.parse(result);
                    dati.img_profilo = result;
                    init_dati_utente();
                    target.trigger("reset");
                },
                error: function (err) {
                    console.log(err);
                }
            })
        });
    
        $("#inp-immagine-profilo").change(function(e) {
            let preview = $("#preview-img-profilo");
            let file = e.currentTarget.files[0];

            if (file) {
                let reader = new FileReader();
                reader.onload = function() {
                    let img = $("<img>").attr("src", reader.result);
                    preview.empty().append(img);
                };
                reader.readAsDataURL(file);
            }
        });

        $("#form-immagine-profilo").on("reset", () =>
            $("#preview-img-profilo").empty()
        );

        $(".color-theme-elem").click(function (e) {
            let target = $(e.currentTarget);

            if (target.hasClass("active")) return;

            $(".color-theme-elem").removeClass("active");
            target.addClass("active");

            $.ajax({
                url: "server.php",
                type: "POST",
                data: {
                    "action": "new-theme-color",
                    "new_color": target.attr("colore")
                },
                success: function (result) {
                    console.log(result);
                    result = JSON.parse(result);
                    set_theme_color(result.colori);
                },
                error: function (result) {}
            });
        });
    </script>
</body>
</html>
