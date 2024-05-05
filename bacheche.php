<?php
    include "utils.php";

    session_start();
    login_required();
    set_console();

    $data = get_bacheche_list();
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

            background-color: white;
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
            height: 115px;
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
        width: 320px;
        text-decoration: none;
        text-transform: none;
        
        margin-top: 30px;
        display: flex;
        align-items: center;
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


 
    #container {
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
                    
            background-color: #e0ab23;
            color: black;
            border-radius: 10px;

            margin-top: 10px;
            padding: 30px;
            padding-right: 0px;
            padding-left:0px;
            margin-left:20px;
            margin-right: 20px;
            cursor: pointer;
        } 
        
        /* popup */
.popup .popuptext {
  visibility: hidden;
  background-color: #e0ab23;
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
        background-color:#d05e26;
        border: solid #8f411a;
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
        background-color: #d05e26;
        border: solid #8f411a;
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
                <a href="../index.html">
                <img  src="img/logo_scritta_completo.png"  class="logo">
                </a>

              
               </div>
    
                <div class="navbar-left">
					<div class="pfp" > E T</div>
                      <span class="writing" style="margin-left: 0px;">Account</span>
              </div>

       
    </div>
    
 <div class="recenti"> Visualizzate di Recente </div>

    <!-- content hidden -->
    <div id="bacheca-prototipo" class="bacheca bacheca-elem" style="display: none">
        <p class="bacheca-nome"><span></span></p>
    </div>

    <form id="form-nuova-bacheca" class="form-nuova-bacheca" method="post" style="display: none">
 <label for="" class="title">Nome: </label>
      <input class="input" type="text" name="nome" id=""> 

 <div>       <input class="crea" type="submit" value="Crea Bacheca"> </div>
    </form>
 <div class="paragrafo"> Le tue Bacheche </div>
    <!-- lista bacheche -->
    <div class="bacheca-list" id="container">
        
    </div>
    <div id="nuova-bacheca" class="button"> <img src="img/aggiungi.png" class="icon"> Aggiungi una nuova bacheca</div>


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
            popup.empty();
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