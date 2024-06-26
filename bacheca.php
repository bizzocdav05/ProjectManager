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
    $temp = set_bacheca($codice_bacheca, true);
    $id_bacheca = $temp[0];
    $privilegi = $temp[1];

    $result = $conn->query("SELECT console, nome FROM Bacheca WHERE ID=$id_bacheca;")->fetch_assoc();
    $id_console_bacheca = $result["console"];
    $data["nome_bacheca"] = $result["nome"];

    $data["codice_bacheca"] = $codice_bacheca;
    $data["privilegi"] = $privilegi;

    $data["attivita"] = get_dati_attivita($id_bacheca);    

    //TOOD: crea nuovo elemento (attività, lista, checkbox, ...) -> attivita.php

    //TODO: gestione accessi
    $data["membri"] = get_membri_bacheca($id_bacheca, $id_utente);
    $data["membri"]["proprietario"] = ($id_console_bacheca == $id_console);
    
    if (!$data["membri"]["proprietario"]) {
        $data["membri"]["proprietario_nome"] = get_nome_utente(null, $id_console_bacheca);
        $id_proprietario = $conn->query("SELECT ID FROM Utenti WHERE console=$id_console_bacheca;")->fetch_assoc()["ID"];
        $data["membri"]["proprietario_codice"] = hash("sha256", $id_proprietario);
    }

    $data["codice_invito"] = get_codice_invito($id_bacheca);

    $data["nome_utente"] = get_nome_utente($id_utente);
    $data["img_profilo"] = get_user_img_profilo();

    // imposto data di ultima apertura
    if ($data["membri"]["proprietario"]) {
        $conn->query("UPDATE Bacheca SET ultimo_accesso=CURRENT_TIMESTAMP WHERE ID=$id_bacheca;");
    } else {
        $conn->query("UPDATE Bacheca_assoc SET ultimo_accesso=CURRENT_TIMESTAMP WHERE bacheca=$id_bacheca;");
    }

    // colori del tema
    $result = $conn->query("SELECT tema FROM Utenti WHERE ID=$id_utente;");
    $data["nome_tema"] = $result->fetch_assoc()["tema"];
    $data["tema"] = get_theme_colors( $data["nome_tema"]);

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

    <!--font dei titoli-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Concert+One&display=swap" rel="stylesheet">

    <title>Bacheca</title>

    <style>
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

    body{
        margin:0px;
        background-color: #f3e0ad;
        overflow-x:hidden;
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

    #popup-box.attivita-nuova-popupbox {
        width: 60vh;
        height: 40vh;
    }

    #popup-box.lista-nuova-popupbox {
        width: 60vh;
        height: 60vh;

        position: absolute;
        top: 15%;
    }

    #popup-box.lista-info-popupbox {
        position: absolute;
        top: 5%;
        left: 25%;

        width: 50vw;
        height: 85vh;
    }

    #popup-box.spazi-lavoro-popupbox {

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

        border-style: solid;
        border-width: 2px;
        border-color: #000000;

        box-shadow: 2px 2px 0px 0px black;
        border-radius: 12px;
        padding-left: 12px;
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

        background-color: var(--color-primary);
        color: #000000;

        border-radius: 10px;

        padding: 20px 0px;

        position: relative;

        font-family: "Concert One", sans-serif;
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
        border-radius: 10px;


        padding: 5px 0px 10px 5px;
        margin-bottom: 10px;
        margin-top: 10px;
        box-shadow: 2px 2px 0px 0px black;

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

        box-shadow: none;
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
        flex-direction: column;
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

    div.attivita-tabella {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-around;
        width: 100%;
        font-family: "Concert One", sans-serif;

        border-bottom: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black;

        margin-left: 30px;
        margin-right: 30px;
    }

    div.attivita-tabella:first-child {
        border-top: 1px solid black;
        font-family: "Concert One", sans-serif;
    }

    div.attivita-tabella > div.prima-cella {
        width: 30%;
        /* margin-top: 20px; */
        display: flex;
    justify-content: space-around;
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

/*    #select-type-visual > button.active {
        background-color: green;
    }*/

    p.scadenza-valida {
        background-color: #b7e023;
        color: white;
    }

    p.scadenza-invalida {
        background-color: #E04D23;
        color: white;
    }

    #container-calendario {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 90%;
        margin-left: auto;
        margin-right: auto;
        padding-bottom: 30px;
    }

    div.calendario-header {
    display: flex;
    flex-direction: row;
    justify-content: space-around;
    align-items: center;
    }

    div.calendario-header svg {
        margin-left: 20px;
        margin-right: 20px;
    }

    div.calendario-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 100%;
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
        /* height: 30px; */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: start;
    }

    div.giorno.invalid {
        background-color: rgb(214, 208, 208);
        opacity: 0.6;
    }

    div.numero-giorno.active {
        background-color: #adc0f3;
        color: white;
        border-radius: 5px;
        width: 100%;
    }

    div.scadenza-giorno {
        height: 8vw;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        width: 90%;
        margin-left: 5%;
    }

    div.scadenza-giorno.active {
        background-color: gray;
        border-radius: 10px;
        font-family: "Concert One", sans-serif;
    }

    svg.calendario-arrow {
        width: 30px;
        height: 30px;
    }

    p.calendario-anno {
        font-size: 40px;
        font-weight: bold;
        font-family: "Concert One", sans-serif;
        text-transform: capitalize;

        text-align: center;
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
        width: 270px;
        text-transform: none;

        justify-content: space-around;
        align-items: center;
        display: flex;
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

    .button:active {
        border-width: 4px 0 0;
        background: none;
    } 

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



   /*stile menu laterale*/
   :root {
        --background: #f3e0ad;
        --navbar-width: 256px;
        --navbar-width-min: 80px;
        --navbar-dark-primary: var(--color-primary); /*colore menu*/
        --navbar-dark-secondary: var(--color-secondary); /*colore righe*/
        --navbar-light-primary: #eee; /*colore titolo avatar*/
        --navbar-light-secondary: #eee; /*colore descrizione avatar*/

        --color-primary: var(--color-primary);  /* uguale colore menu */
    }


    #nav-toggle:checked ~ #nav-header {
    width: calc(var(--navbar-width-min) - 16px); 
    }

    #nav-toggle:checked ~ #nav-content, #nav-toggle:checked ~ #nav-footer {
    width: var(--navbar-width-min); 
    }

    /*animazione per far sparire il titolo*/
    #nav-toggle:checked ~ #nav-header #nav-title {
    opacity: 0;
    pointer-events: none;
    transition: opacity .1s; 
    }

    #nav-toggle:checked ~ #nav-header label[for="nav-toggle"] {
    left: calc(50% - 8px);
    transform: translate(-50%); 
    }

    #nav-toggle:checked ~ #nav-header #nav-toggle-burger {
    background: #000000;
    }

    #nav-toggle:checked ~ #nav-header #nav-toggle-burger:before, #nav-toggle:checked ~ #nav-header #nav-toggle-burger::after {
        width: 16px;
        background: #000000;
        transform: translate(0, 0) rotate(0deg); 
    }

    /*animazione per far sparire i contenuti del menu*/
    #nav-toggle:checked ~ #nav-content .nav-button p {
    opacity: 0;
    transition: opacity .1s; 
    }

    /*animazione per far sparire il titolo del menu*/
    #nav-toggle:checked ~ #nav-content .nav-button span {
    opacity: 0;
    transition: opacity .1s; 
    }

    /*animazione per far sparire i contenuti del menu*/
    #nav-toggle:checked ~ #nav-content .nav-button button {
    opacity: 0;
    transition: opacity .1s; 
    }

    #nav-toggle:checked ~ #nav-content .nav-button .fas {
    min-width: calc(100% - 16px); 
    }

    #nav-toggle:checked ~ #nav-footer #nav-footer-avatar {
    margin-left: 0;
    left: 50%;
    transform: translate(-50%); 
    }


    #nav-bar {
    background: var(--navbar-dark-primary);
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    color: var(--navbar-light-primary);
    font-family: "Concert One", sans-serif;
    overflow: hidden;
    user-select: none;
    margin-top: 30px;
    margin-left: 30px;
    }

    #nav-bar hr {
        margin: 0;
        position: relative;
        left: 16px;
        width: calc(100% - 32px);
        border: none;
        border-top: solid 1px var(--navbar-dark-secondary); 
    }

    #nav-bar a {
        color: inherit;
        text-decoration: inherit; 
    }

    #nav-bar input[type="checkbox"] {
        visibility: hidden; 
    }

    #nav-header {
    position: relative;
    width: var(--navbar-width);
    left: 16px;
    width: calc(var(--navbar-width) - 16px);
    min-height: 80px;
    background: var(--navbar-dark-primary);
    border-radius: 16px;
    z-index: 2;
    display: flex;
    align-items: center;
    transition: width .2s; 
    color: #000000;
    font-weight: 900;
    }
    
    #nav-header hr {
        position: absolute;
        bottom: 0; 
    }

    #nav-title {
    font-size: 1.5rem;
    transition: opacity 1s; 
    }

    label[for="nav-toggle"] {
    position: absolute;
    right: 0;
    width: 3rem;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer; 
    }

    #nav-toggle-burger {
    position: relative;
    width: 16px;
    height: 2px;
    background: var(--navbar-dark-primary);
    border-radius: 99px;
    }

    #nav-toggle-burger:before, #nav-toggle-burger:after {
        content: '';
        position: absolute;
        top: -6px;
        width: 10px;
        height: 2px;
        background: #000000;
        border-radius: 99px;
        transform: translate(2px, 8px) rotate(30deg);
        font-weight: 900;
    }

    #nav-toggle-burger:after {
        top: 6px;
        transform: translate(2px, -8px) rotate(-30deg); 
    }

    #nav-content {
    margin: -16px 0;
    padding: 16px 0;
    position: relative;
    flex: 1;
    width: var(--navbar-width);
    background: var(--navbar-dark-primary);
    box-shadow: 0 0 0 16px var(--navbar-dark-primary);
    overflow-x: hidden;
    transition: width .2s; 
    direction: rtl;
    }

    #nav-content::-webkit-scrollbar {
        width: 8px;
        height: 8px; 
    }


    #nav-content::-webkit-scrollbar-button {
        height: 16px; 
    }

    #nav-content-highlight {
    position: absolute;
    left: 16px;
    top: -70px;
    width: calc(100% - 16px);
    height: 54px;
    background: var(--background);
    background-attachment: fixed;
    border-radius: 16px 0 0 16px;
    transition: top .2s; 
    }

    #nav-content-highlight:before, #nav-content-highlight:after {
        content: '';
        position: absolute;
        right: 0;
        bottom: 100%;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        box-shadow: 16px 16px var(--background); 
    }

    #nav-content-highlight:after {
        top: 100%;
        box-shadow: 16px -16px var(--background); 
        border-radius: 16px;
    }

    .nav-button {
    position: relative;
    margin-left: 16px;
    height: 54px;
    display: flex;
    align-items: center;
    color: var(--navbar-light-secondary);
    direction: ltr;
    cursor: pointer;
    z-index: 1;
    transition: color .2s; 
    }

    .nav-button button{
        transition: opacity 1s; 
        color: #000000;
        font-weight: 550;
    }

    .nav-button span {
        transition: opacity 1s; 
        color: #000000;
        font-weight: 550;
    }

    .nav-button:nth-of-type(1):hover {
        color: var(--navbar-dark-primary); 
    }

    .nav-button:nth-of-type(2):hover {
        color: var(--navbar-dark-primary); 
    }

    .nav-button:nth-of-type(2):hover ~ #nav-content-highlight {
        top: 70px; 
    }

    .nav-button:nth-of-type(3):hover {
        color: var(--navbar-dark-primary); 
    }

    .nav-button:nth-of-type(3):hover ~ #nav-content-highlight {
        top: 124px; 
    }

    .nav-button:nth-of-type(4):hover {
        color: var(--navbar-dark-primary); 
    }

    .nav-button:nth-of-type(4):hover ~ #nav-content-highlight {
        top: 178px; 
    }

    .nav-button:nth-of-type(5):hover {
        /* color: var(--navbar-dark-primary);  */
    }

    .nav-button:nth-of-type(5):hover ~ #nav-content-highlight {
        top: 232px; 
    }

    .nav-button:nth-of-type(6):hover {
        color: var(--navbar-dark-primary); 
    }

    .nav-button:nth-of-type(6):hover ~ #nav-content-highlight {
        top: 286px; 
    }

    .nav-button:nth-child(5):hover ~ #nav-content-highlight::after {
        border-radius: 0px;
    }

    
        /* margine sinistro delle scritte*/
    #nav-bar .fas {
    min-width: 3rem;
    text-align: center; 
    }

    div.spazi-lavoro {
        color: var(--navbar-light-primary);
        font-family: "Concert One", sans-serif;
        font-weight: bolder;
        font-style: normal;
        font-size: larger;
        color: #000000;
        cursor: pointer;
        text-decoration: none;
    }

    #popup-spazi-lavoro {
        position: absolute;
        top: 0;
        left: 0;

        width: fit-content;
        height: 30vh;

        background-color: var(--color-primary);
        border-radius: 16px;
        padding: 10px;
        cursor: default;
    
        border: black;
    border-width: 2px;
    border-style: solid
    }
    

    #popup-spazi-lavoro p.lista-text {
        text-decoration: underline;
    }

    #popup-spazi-lavoro svg.svg-chiudi {
        position: absolute;
        top: 0;
        right: 0;
        width: 20px;
        height: 20px;
        cursor: pointer;
        padding-top: 10px;
        padding-right: 10px;
    }

    #popup-spazi-lavoro .lista-text {
        cursor: pointer;
    }

    div.bacheche-list-box {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        width: 90%;
        border: 1px solid white;
        border-radius: 5px;
        padding: 5px;
    }

    div.bacheche-elem {
        background-color: var(--background);
        text-align: center;
        padding: 10px;
        margin: 0;
        border-radius: 7px;
        cursor: pointer;
        font-size: 20px;
        margin-right: 30px;
    }

    div.bacheche-elem:hover {
        background-color: var(--navbar-light-primary);
    }

    div.bacheche-elem p {
        margin: 0;
    }

    .icon{
        margin-right:10px;
        height: 20px;
        width: 20px;
    }

    p.select-visual-format {
        background-color: rgb(224, 171, 35, 0);
        border-width: 0px;
        font-family: "Concert One", sans-serif;
        font-size: 16px;
        color: black;
    }


    circle{
        fill: rgb(0, 0, 0);
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
        padding-left: 30px;
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

    .calendario-nome-giorno{
        font-family: "Concert One", sans-serif;
    }

    .numero-giorno{
        font-family: "Concert One", sans-serif;  
    }

    .vuoto{
        display: none;
        background-color: #f3e0ad;
    }

    #content {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: start;
    }

    #content-right {
        width: 100%;
        margin-top: 22px;
    }

    div.lista h2 {
        width: fit-content;
    }

    #container-membri {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;

        margin-top: 30px;
    }

    div.membri-box {
        display: flex;
        flex-direction: row;
        justify-content: space-evenly;
        align-items: start;
        flex-wrap: wrap;


        width: 70%;
        border: 2px solid black;
        border-radius: 16px;
        padding: 20px;
        min-height: 30%;

        margin-top: 30px;
    }

    div.membro {
        border-radius: 10px;
        background-color: var(--color-primary);
        padding: 10px;
        /* height: 30px; */
        text-align: center;
        min-width: 200px;

        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
    }

    div.membro div.membro-box {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin-left: 20px;
        width: fit-content;
    }

    p.link-invito > svg {
        width: 20px;
        height: 20px;
    }

    div.membro p.select-visual-format {
        background-color: transparent;
        max-width: 100px;
    }

    div.membro p {
        margin: 0;
    }

    div.membro  button.btn-membro-elimina {
        border: 0;
        margin: 0;
        padding: 5px;
        border-radius: 5px;
        background-color: var(--navbar-dark-secondary);
        color: white;
        margin-left: 10px;
    }

    div.membro button.btn-membro-elimina > p.select-visual-format {
        font-size: 14px;
        font-weight: 100;
    }

    span.link-text {
        margin-right: 10px;
    }

    span.link-support {
        color: #5ebd4d;
        font-size: 20px;
        font-weight: bold;
    }

    p.text-format {
        background-color: rgb(224, 171, 35, 0);
        border-width: 0px;
        font-family: "Concert One", sans-serif;
        color: black;
        margin: 0;
    }

    #chat {
        width: 20vw;
        height: 30vh;

        display: flex;
        flex-direction: column;
        align-items: start;
        justify-content: center;

        /* border: 1px solid black; */
        border-top-left-radius: 16px;
        padding: 20px;

        position: fixed;
        bottom: 0;
        right: 0;

        background-color: var(--color-primary);
    }

    #chat.mini {
        height: fit-content;
    }

    #chat > div:not(#chat-manager) {
        width: 100%;
    }

    #chat-manager {
        width: 50%;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-evenly;
    }

    #chat-content {
        height: 90%;
        overflow-y: auto;
        margin-bottom: 10px;
        border: 2px solid var(--color-tertiary);
        border-radius: 10px;
        background-color: var(--background);
    }

    #chat-new-msg {
        height: 10%;
        width: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
    }

    #chat-new-msg input {
        width: 90%;
        height: 20px;
        border-radius: 10px;
        border-color: transparent;
    } 

    div.messaggio-chat {
        width: 75%;
        padding: 5px;
        margin-top: 10%;
    }

    div.msg-proprio {
        margin-right: 0;
        margin-left: auto;
    }

    div.msg-other {
        margin-left: 0;
        margin-right: auto;
    }

    #svg-send-msg {
        width: 30px;
        height: 30px;

        fill: var(--color-primary);
    }

    #svg-send-msg path {
        stroke: var(--color-tertiary);
    }

    #chat-manager svg {
        width: 20px;
        height: 20px;
    }

    div.messaggio-chat p.mittente {
        font-family: "Concert One", sans-serif;
        height: 30px;
        text-align: center;
        color: white;
        margin-left: 10px;
    }
    
    div.messaggio-chat p.testo {
        font-family: "Outfit", sans-serif;
        background-color: #80808087;
        border-radius: 10px;
        margin: 5px;
        padding: 2%;
    }
    
    div.messaggio-chat p.orario {
        font-family: "Outfit", sans-serif;
        text-align: right;
        font-size: x-small;
    }

    div.messaggio-chat div.user-icon {
        width: 30px;
        height: 30px;
    }

    div.messaggio-chat div.mittente-box {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
    }

    .column{
        border-top: 2px solid black;
        border-left: 2px solid black;
        box-shadow: 3px 3px 0px 0px black;
        color: #000000;


        border-radius: 10px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: space-around;
        width: 100px;
    }

    .text-area{
        width: 240px;
        height: 120px;
        margin-top: 30px;
    }


    .lista-crea{
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
    }

    .crea-lista{
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
            width: 100px;
            height: 50px;
            text-decoration: none;
    }

    .crea-lista:after{
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
    }

    .crea-lista:hover{
        filter: brightness(1.1);
    }

    .crea-lista:active{
        border-width: 4px 0 0;
    }

    .btn-reset {
            font-family: "Concert One", sans-serif;
            background-color: #f3e0ad;
            border: solid #dac99b;
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
        
            .btn-reset:after {
        background-clip: padding-box;
        background-color: #f3e0ad;
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

    .btn-reset:active {
        border-width: 4px 0 0;
        background: none;
    } 

    .btn-reset:hover {
        filter: brightness(1.1);
    }

    .input-lista{
        background-color: #eee;
        font-family: "Concert One", sans-serif;
        font-size: large;

        border-width: 0px;
        border-radius: 10px;

    }

    .text-mod{
        font-family: "Concert One", sans-serif;
        font-size: 20px;
    }

    .description{
        font-family: "Concert One", sans-serif;
        font-size: 20px;

        color: #eee;

        border-top: 2px solid #eee;
        border-left: 2px solid #eee;
        box-shadow: 3px 3px 0px 0px #eee;

        border-radius: 10px;

        height: 50px;
        width: 130px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-top: 20px;
        margin-bottom: 20px;
    }

    .membri{
        font-family: "Concert One", sans-serif;
        font-size: 40px;
        margin: 0px;

        border-top: 2px solid black;
        border-left: 2px solid black;
        box-shadow: 3px 3px 0px 0px black;
        color: #000000;
        border-radius: 10px;
        width: 150px;
        text-align: center;
    }

    .link-invito{
        font-family: "Concert One", sans-serif; 
    }

    .link-text{
        text-decoration: underline;
        cursor: pointer;
    }

    .link-title{
        font-size: 20px;
    }

    .display{
        display: flex;
        align-items: baseline;
    }

    .buttons{
        margin-top: 20px;
        margin-bottom: 30px;
        display: flex;
        justify-content: flex-end;
    }

    .commento-nuovo{
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .title-lista {
        font-family: "Concert One", sans-serif;
        font-size: 30px;
        padding-left: 30px;
        padding-right: 15px;
        color: #eee;

        width: 190px;
        height: 50px;

        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin-top: 20px;
    }

    div.lista-info-box .input-nomi:focus{
        outline: none;
        border: none;
    }

    .display-2{
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .organize{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }

    .etichetta-text > span{
        font-family: "Concert One", sans-serif;
    }

    .display-checkbox{
        display: flex;
        justify-content: flex-end;
        flex-direction: column;
        align-items: flex-end;
    }

    img.calendario-arrow {
        width: 30px;
        height: 30px;
    }

    div.calendario-header img {
    margin-left: 20px;
    margin-right: 20px;
}

    .checkbox-nuovo{
        display: flex;
        justify-content: flex-start;
        flex-direction: column;
    }

    .scadenza{
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .cancel{
        display: flex;
        justify-content: center;
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
        text-transform: uppercase;
    }

    .btn-inline {
        text-decoration: underline;
        color: #262626;
        cursor: pointer;
        background: transparent;
    }

    div.lista-commento-box {
        height: 60vh;
        overflow-y: auto;
        border: 1px solid white;
        padding: 10px;
        border-radius: 5px;
    }

    div.commento {
        flex-direction: column;
        justify-content: flex-start;
        align-items: start;
        margin-bottom: 20px;
    }

    div.commento div.mittente-box {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
    }

    p.commento-utente {
        margin-left: 20px;
    }

    p.commento-text {
        margin-left: 20px;
        background-color: var(--background);
        min-width: 50%;
        border-radius: 5px;
        padding: 5px;
        margin-top: 20px;
    }

    p.elimina-commento {
        margin-top: 10px;
        margin-left: 20px;
    }

    div.lista-info-box .input-nomi {
        border: none;
        background: transparent;
    }

    input.lista-descrizione {
        font-size: 20px;
        text-align: left;
        border-bottom: 1px solid black;
    }

    h3.attivita-titolo > input {
        background: transparent;
        text-align: center;
        font-size: 20px;
        width: 100%;
    }

    h3.attivita-titolo > input:focus {
        border: 0;
    }

    div.lista-sposta-box {
        margin-top: 20px;
        margin-bottom: 40px;
        border: 1px solid black;
        border-radius: 10px;
    }

    div.lista-sposta-box p {
        text-align: left;
        border-radius: 5px;
        font-family: "Concert One", sans-serif;
        font-size: 20px;
        padding: 2px;
        margin: 0;
        cursor: pointer;
    }

    div.menu-sposta p:hover {
        color: white;
    }
    </style>
</head>
<body>
    <div class="header">
        <!--parte della barra a sinistra-->
        <div class="navbar">
        <div class="navbar-left" > 

            <!--immagine del logo-->
            <a href="index.php">
            <img  src="img/logo_scritta_completo.png"  class="logo">
            </a>

                <!--tutte le bacheche di un account-->
                <div class="spazi-lavoro">
                    <p>Spazi di Lavoro</p>

                    <div id="popup-spazi-lavoro" style="display: none ">
                        <svg class="svg-chiudi" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><g fill="#0F0F0F"><path d="M8.002 9.416a1 1 0 1 1 1.414-1.414l2.59 2.59 2.584-2.584a1 1 0 1 1 1.414 1.414l-2.584 2.584 2.584 2.584a1 1 0 0 1-1.414 1.414l-2.584-2.584-2.584 2.584a1 1 0 0 1-1.414-1.414l2.584-2.584z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M23 4a3 3 0 0 0-3-3H4a3 3 0 0 0-3 3v16a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3zm-2 0a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1z"/></g></svg>
                        <p class="lista-text" onclick="location.href = 'bacheche.php'">Le tue bacheche</p>
                        <div class="bacheche-list-box">
                        </div>
                    </div>
                </div>

            </div>
            <div class="navbar-right">
                <div class="user-icon" ></div>
                    <div class="account" onclick="location.href = 'account.php'" style="margin-left: 0px;">Account
                    </div>            
            </div>
        </div>
    </div>

    <div id="chat">
        <div id="chat-manager">
            <svg class="svg-down-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 330 330" xml:space="preserve"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path d="M325.607 79.393c-5.857-5.857-15.355-5.858-21.213.001l-139.39 139.393L25.607 79.393c-5.857-5.857-15.355-5.858-21.213.001-5.858 5.858-5.858 15.355 0 21.213l150.004 150a15 15 0 0 0 21.212-.001l149.996-150c5.859-5.857 5.859-15.355.001-21.213"/></svg>
            <svg class="svg-up-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.01 512.01" xml:space="preserve"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path d="M505.755 358.256 271.088 123.589c-8.341-8.341-21.824-8.341-30.165 0L6.256 358.256c-8.341 8.341-8.341 21.824 0 30.165s21.824 8.341 30.165 0l219.584-219.584 219.584 219.584a21.28 21.28 0 0 0 15.083 6.251 21.28 21.28 0 0 0 15.083-6.251c8.341-8.341 8.341-21.824 0-30.165"/></svg>

            <p class="text-format">Chat</p>
        </div>

        <div id="chat-content">
        </div>

        <div id="chat-new-msg">
            <input type="text" name="msg" id="inp-chat-msg">
            <svg id="svg-send-msg" viewBox="0 -0.5 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path clip-rule="evenodd" d="M18.455 9.883 7.063 4.143a1.048 1.048 0 0 0-1.563.733.8.8 0 0 0 .08.326l2.169 5.24c.109.348.168.71.176 1.074a4 4 0 0 1-.176 1.074L5.58 17.83a.8.8 0 0 0-.08.326 1.048 1.048 0 0 0 1.562.732l11.393-5.74a1.8 1.8 0 0 0 0-3.265" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
    </div>


    <div id="content">
        <!-- menu laterale -->
        <div id="content-left">
            <div id="nav-bar"><input id="nav-toggle" type="checkbox" />
                <div id="nav-header"><a id="nav-title" target="_blank">S<i class="fab fa-codepen"></i>pazio di Lavoro</a><label for="nav-toggle"><span id="nav-toggle-burger"></span></label>
                    <hr/>
                </div> 

                    <!--contenuto del menu laterale-->

                <div id="nav-content">
                    <div class="nav-button"><span>Viste della Bacheca</span></div>

                    <div class="nav-button active select-type-visual" value="isola">
                        <i class="fas fa-heart"></i>
                        <p class="select-visual-format">Isola</p>
                    </div>

                    <div class="nav-button select-type-visual" value="tabella">
                        <i class="fas fa-heart"></i>
                        <p class="select-visual-format">Tabella</p>
                    </div>
                    
                    <div class="nav-button select-type-visual" value="calendario">
                        <i class="fas fa-heart"></i>
                        <p class="select-visual-format">Calendario</p>
                    </div>

                    <hr/>

                    <div class="nav-button select-type-visual" value="membri">
                        <i class="fas fa-thumbtack"></i>
                        <p class="select-visual-format">Membri</p>
                    </div>

                    <div class="nav-button select-type-visual" value="impostazioni">
                        <i class="fas fa-thumbtack"></i>
                        <p class="select-visual-format">Impostazioni</p>
                    </div>


                    <div id="nav-content-highlight"></div> <!--barra del colore dello sfondo che si muove-->


                </div>
            </div>
        </div>

        <div id="content-right">
            <div id="container-isola" style="display: none" >
                <div id="attivita-nuova" class="attivita-box">
                    <div class="attivita-lista attivita-lista-isola">
                    <div class="lista">
                        <h2 class="button"> <img src="img/aggiungi.png" class="icon"> Aggiungi una nuova attività</h2>
                    </div>
                    </div>
                </div>
            </div>

            <div id="container-tabella" style="display: none">
        
                <div id="attivita-tabella-header" class="attivita-tabella tabella-header">
                
                    <div class="prima-cella ">
                        <h3 class="attivita-titolo column">Attività</h3>
                    </div>

                    <div class="seconda-cella ">
                        <h3 class="attivita-titolo column">Liste</h3>
                    </div>

                    <div class="terza-cella ">
                        <h3 class="attivita-titolo column">Etichette</h3>
                    </div>

                    <div class="quarta-cella ">
                        <h3 class="attivita-titolo column">Scadenza</h3>
                    </div>

                </div>
            </div>

            
            <div id="container-calendario" style="display: none">
                <div class="calendario-header">
                        <img class="calendario-arrow" direction="left" src="img/freccia_sinistra.png">
                        <p class="calendario-anno"><span></span></p>
                        <img class="calendario-arrow" direction="right" src="img/freccia_destra.png">
                </div>
                <div class="calendario-body"></div>
            </div>

            <div id="container-membri" style="display: none">
                <h1 class="membri">Membri</h1>

                <div class="membri-box">
                </div>

                <p class="link-invito"> <span class="link-title">Link invito:</span>
                    <span class="link-text">http://torg.altervista.org/bacheca_invito.php?sharing=<span class="link-value"></span>  </span>
                    <svg class="link-support" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M208 0h124.1C344.8 0 357 5.1 366 14.1L433.9 82c9 9 14.1 21.2 14.1 33.9V336c0 26.5-21.5 48-48 48H208c-26.5 0-48-21.5-48-48V48c0-26.5 21.5-48 48-48M48 128h80v64H64v256h192v-32h64v48c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48"/></svg>
                    <span class="link-support">Copied!</span>
                </p>    


            </div>

            <div id="container-impostazioni" style="display: none">
                <div class="organize" style="justify-content: start;">
                    <label for="inp-nome-bacheca" class="title">Nome Bacheca: </label>
                    <input type="text" class="input" name="nome" id="inp-nome-bacheca">
                </div>

                <button class="button crea btn-elimina-bacheca" style="background-color: #ff5252;">Elimina Bacheca</button>
            </div>
        </div>
    </div>

    <div id="cestino" class="cestino">
        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
            <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
        </svg>
    </div>

    <div id="popup" >
        <div id="popup-box">
    </div>

    <div id="attivita-prototipo-isola" class="attivita-box attivita-box-isola" style="display: none">
        <div class="attivita-header">
            <h3 class="attivita-titolo"><input class="input"></h3>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><circle cx="256" cy="256" r="48" fill="#fff"/><circle cx="256" cy="128" r="48" fill="#fff"/><circle cx="256" cy="384" r="48" fill="#fff"/></svg>
        </div>

        <div class="attivita-lista attivita-lista-isola">

            <div class="lista lista-nuova">
                <p class="lista-nome"> <img src="img/aggiungi.png" style="width: 15px; height: 15px;">  Aggiungi una nuova lista</p>
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
                <label for="" class="title">Titolo</label>
                <input  class="input" type="text" name="titolo" id="" required>

               <div class="flex"><input type="submit" value="Crea Attività" class="crea"></div> 
            </form>
        </div>
    </div>

    <div id="lista-info-prototipo" class="lista-info-box" style="display: none">
        <div class="lista-info">
            <div class="display-2">
                <input type="text" class="lista-nome title-lista input-nomi">
            </div>

            <p class="lista-text description">Descrizione</p>
            <input type="text" class="lista-descrizione input-nomi">

            <div class="organize">
                <div>
                    <p class="description" style="width: 160px;">Le Tue Etichette</p>
                    <div class="lista-etichetta-box"></div>
                </div>

                <div style="display: flex; align-items: flex-end; flex-direction: column;">
                    <p class="lista-text description">Etichetta</p>

                    <div class="etichetta-nuova">
                        <div class="colore-etichetta">
                            <input type="text" name="testo" id="" placeholder="Nome" class="input-lista" style="height: 30px;">
                            <input type="color" name="colore" id="" class="input-lista">
                        </div>

                        <div class="buttons"><button class="btn-etichetta-nuova crea-lista">Crea</button>
                            <button class="btn-etichetta-reset btn-reset">Annulla</button>
                        </div> 
                    </div>
                </div>
            </div>

            <div class="organize">
                <div>
                    <p class="description" style="width: 200px; margin-right: 30px; margin-bottom: 0px;">Le Tue Checkbox</p>
                    <div class="lista-checkbox-box"></div>
                </div>

                <div class="display-checkbox">
                    <p class="lista-text description">Checkbox</p>
                    <div class="checkbox-nuovo">
                        <textarea name="testo" id="" cols="40" rows="2" placeholder="Checkbox" class="input-lista"></textarea>
                        <div class="buttons">
                            <button class="btn-checkbox-nuovo crea-lista">Crea</button>
                            <button class="btn-checkbox-reset btn-reset">Annulla</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="">
                <div>
                    <p class="description">Commenti</p>
                    <div class="lista-commento-box"></div>
                </div>

                <div class="display-checkbox">
                    <p class="lista-text description">Commento</p>

                    <div class="commento-nuovo">
                        <textarea name="commento" id="" cols="40" rows="5" placeholder="Inserisci il tuo commento" class="input-lista"></textarea>
                        <div class="buttons">  
                            <button class="btn-commento-nuovo crea-lista">Invia</button>
                            <button class="btn-commento-reset btn-reset">Annulla</button>
                        </div> 
                    </div>
                </div>
            </div>

            <div class="organize">
                <div>
                    <p class="description" style="width: 200px; margin-right: 30px; margin-bottom: 0px;">Sposta Lista</p>
                    <div class="lista-sposta-box">
                        <p class="lista-text attuale"></p>
                    </div>
                    </div>
                </div>
            </div>

            <p class="lista-text description">Scadenza</p>
            <div class="lista-scadenza-box"></div>
            <div class="cancel">
                <button class="lista-delete btn-reset" style="width: 200px; margin-top: 50px;  display: flex; justify-content: center;">Cancella lista</button>
            </div>
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
        <div class="checkbox-item">
            <input type="checkbox" name="" id="">
        </div>
        <p class="checkbox-text text-mod"><span></span></p>
        <div id="cestino" class="cestino">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
                <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
            </svg>
        </div>
    </div>

    <div id="commento-prototipo" class="commento" style="display: none">
        <div class="mittente-box">
            <div class="user-icon"></div>
            <p class="text-format commento-utente"><span></span></p>
        </div>
        <p class="text-format commento-text"><span></span></p>
        <p class="btn-inline elimina-commento">Elimina</p>
    </div>

    <div id="scadenza-prototipo" class="scadenza" style="display: none">
        <input class="scadenza-text input-lista" type="date" name="scadenza" style="width: 200px; margin-bottom: 30px; height: 50px;">
        <button class="btn-scadenza-nuovo crea-lista" style="width: 200px;margin: 0px;">Aggiungi scadenza</button>
        <button class="btn-scadenza-elimina btn-reset">Elimina</button>
    </div>

    <div id="settimana-prototipo" class="settimana" style="display: none">
    </div>

    <div id="giorno-prototipo" class="giorno" style="display: none">
        <div class="numero-giorno"></div>
        <div class="scadenza-giorno"></div>
    </div>

    <div id="intestazione-prototipo" class="intestazione" style="display: none">
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Lunedì</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Martedì</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Mercoledì</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Giovedì</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Venerdì</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Sabato</p></div>
        <div class="intestazione-giorno"><p class="calendario-nome-giorno">Domenica</p></div>
    </div>

    <div id="lista-nuova-prototipo" style="display: none">
        <div class="lista-info">
            <form class="form-nuova-lista lista-crea" method="post">
                <div>
                <label for="" class="title">Nome</label>
                <input type="text" name="nome" id="" class="input"></div>

                <div class="display">
                <label for="" class="title">Descrizione</label>
                <textarea name="descrizione" id="" cols="30" rows="10" class="input text-area"></textarea></div>

                <input type="submit" value="Aggiungi Lista" class="crea">
            </form>
        </div>
    </div>
    <div id="membro-prototipo" class="membro" style="display: none">
        <div class="user-icon">

        </div>
        <div class="membro-box">
            <p class="membro-proprietario select-visual-format"></p>
            <p class="membro-other select-visual-format"></p>
            <button class="btn-membro-elimina" style="display: none"><p class="select-visual-format">Elimina</p></button>
        </div>
    </div>

    <div id="bacheche-list-prototipo" class="bacheche-elem" style="display: none">
        <p class="text-format nome"></p>
    </div>

    <div id="messaggio-prototipo" class="messaggio-chat" style="display: none">
        <div class="mittente-box">
            <div class="user-icon"></div>
            <p class="text-format mittente"></p>
        </div>
        <p class="text-format testo"></p>
        <p class="text-format orario"></p>
    </div>

    <p id="sposta-prototipo" class="sposta-elem"></p>

    <script></script>
    <script>
        class Visualizator {
            constructor (data) {
                let searchParams = new URLSearchParams(window.location.search);
                this.codice_bacheca = searchParams.get('codice');

                this.data = data;
                this.tipo = "isola";

                this.codLastMsg = null;
                this.chat_status = false;  // true: open
                this.chat_img_profile = {};  // cod_utente => img

                this.cod_idx = {     // associa ad ogni codice l'indice (di dati)
                    "attivita": {},
                    "lista": {},
                    "checkbox": {},
                    "commento": {},
                    "etichetta": {},
                    "scadenza": {},
                    "membri": {}
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
                    self.popup.box.removeClass();
                    $(window).off("click");
                }

                this.popup.add = function (elem, class_name="") {
                    self.popup.box.empty();
                    self.popup.box.addClass(class_name);

                    let new_ = elem.clone(true);
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
                        this.add_idx_elem("lista", dati_lista.codice, dati.info.codice, j);

                        for (let idx_checkbox = 0; idx_checkbox < dati_lista.checkbox.length; idx_checkbox++) {
                            let dati_checkbox = dati_lista.checkbox.list[idx_checkbox];
                            this.add_idx_elem("checkbox", dati_checkbox.codice, dati_lista.codice, idx_checkbox);
                        }

                        for (let idx_commento = 0; idx_commento < dati_lista.commento.length; idx_commento++) {
                            let dati_commento = dati_lista.commento.list[idx_commento];
                            this.add_idx_elem("commento", dati_commento.codice, dati_lista.codice, idx_commento);
                        }

                        for (let idx_etichetta = 0; idx_etichetta < dati_lista.etichetta.length; idx_etichetta++) {
                            let dati_etichetta = dati_lista.etichetta.list[idx_etichetta];
                            this.add_idx_elem("etichetta", dati_etichetta.codice, dati_lista.codice, idx_etichetta);
                        }

                        for (let idx_scadenza = 0; idx_scadenza < dati_lista.scadenza.length; idx_scadenza++) {
                            let dati_scadenza = dati_lista.scadenza.list[idx_scadenza];
                            this.add_idx_elem("scadenza", dati_scadenza.codice, dati_lista.codice, idx_scadenza);
                        }
                    }
                }

                for (let i = 0; i < this.data.membri.length; i++) {
                    let dati = this.data.membri.list[i];
                    this.add_idx_elem("membri", dati.codice, this.codice_bacheca);
                }
            }

            // method
            show_user_name(target = $("div.header div.user-icon")) {
                console.log(this.data["img_profilo"]);
                if (this.data["img_profilo"]["tipo"] == "default")
                    target.text(this.data["nome_utente"].split(" ").map(p=>p.charAt(0).toUpperCase()).join(" "));
                else
                    target.css("background-image", `url('data:${this.data.img_profilo.tipo};base64,${this.data.img_profilo.dati}')`)
            }

            show_user_chat_img(target, codice_utente) {
                if (this.chat_img_profile.hasOwnProperty(codice_utente)) {
                    let img = this.chat_img_profile[codice_utente];

                    if (img.tipo == "default")
                        target.text(img.dati.split(" ").map(p => p.charAt(0).toUpperCase()).join(" "));
                    else
                        target.css("background-image", img.dati);
                } else {
                    this.get_chat_user_img(codice_utente, target);
                }

            }

            add_idx_elem(tipo, codice, codice_superiore, idx=null) {
                // codice_superiore è il codice dell'elemento superiore per la localizzazione

                if (idx === null) idx = Object.keys(this.cod_idx[tipo]).length;
                this.cod_idx[tipo][codice] = [idx, codice_superiore];
            }

            get_idx(tipo, codice) {
                return this.cod_idx[tipo][codice];
            }

            change_type(tipo) {
                this.tipo = tipo;
                this.clear_html();
                this.show_dati();
            }

            create_lista(dati) {
                let elem = this.elements[this.tipo].lista.clone(true);
                elem.attr("id", dati.codice);

                elem.find("p.lista-nome > span").text(dati.nome);

                elem.show();                
                elem.get(0).dati = dati;
                return elem;
            }

            create_attivita(dati) {
                let info = dati.info;
                // let elem = this.elements[this.tipo].attivita.clone(true);
                let elem = $("#attivita-prototipo-isola").clone(true);
                if (this.tipo == "tabella") elem = $("#attivita-prototipo-tabella").clone(true);

                elem.attr("id", info.codice);

                elem.find("h3.attivita-titolo > input").val(info.titolo);

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

                if (info.actual_user) this.show_user_name(elem.find("div.user-icon"));
                else this.show_user_chat_img(elem.find("div.user-icon"), info.codice_autore);

                elem.find("p.commento-utente > span").text(info.nome_utente);
                elem.find("p.commento-text > span").text(info.testo);

                elem.find(".elimina-commento").attr("id", "button-" + info.codice);
                elem.show();
                
                return elem.add(this.crea_commento(dati, idx+1));  // Ricorsione per calcolarli tutti
            }

            crea_scadenza(dati) {
                let elem = $("#scadenza-prototipo").clone(true);
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
                elem.show();
                return elem;
            }

            crea_sposta(cod_att_attuale) {
                let box = $("<div class='menu-sposta'></div>");
                for (let cod in this.cod_idx["attivita"]) {
                    let elem = $("#sposta-prototipo").clone(true);
                
                    let nome = this.data.attivita.list[this.get_idx("attivita", cod)[0]].info["titolo"];
                    elem.attr("codice", cod);
                    
                    if (cod == cod_att_attuale) {
                        elem.text("\u2713" + " " + nome);
                        elem.addClass("actual");
                    }
                    else
                        elem.text("- " + nome);

                    elem.show();
                    console.log(elem);
                    box.append(elem);
                }
                $("#popup div.lista-sposta-box").append(box);
            }

            crea_giorno(numero, valido, attivo, scadenza) {
                // numero: numero giorno, valido: mese corrente, attivo: giorno odierno, scadenza: ha una scadenza
                let elem = $("#giorno-prototipo").clone(true);
                elem.attr("id", "giorno-" + numero);

                elem.find("div.numero-giorno").text(numero);
                if (scadenza !== null) {
                    let [idx, idx_l, idx_a] = this.get_elem_location("scadenza", scadenza)
                    let lista = this.data.attivita.list[idx_a].lista.list[idx_l];
                    elem.find("div.scadenza-giorno").text(lista.nome);
                    elem.find("div.scadenza-giorno").addClass("active");
                }

                if (attivo) elem.find("div.numero-giorno").addClass("active");

                if (!valido) elem.addClass("invalid");

                elem.show();
                return elem;
            }

            crea_settimana(giorni, numero) {
                let elem = $("#settimana-prototipo").clone(true);
                elem.attr("id", "settimana-" + numero);

                giorni.forEach(giorno => {
                    elem.append(this.crea_giorno(giorno.numero, giorno.is_valid, giorno.is_active, giorno.scadenza));
                });
                    

                elem.show();
                return elem;
            }

            crea_calendario(anno=2024, mese=null) {
                let date;
                if (mese === null) date = new Date(anno, new Date().getMonth());
                else date = new Date(anno, mese);
                
                this.actual_date = new Date(date);

                let giorno_settimana_mese = date.getDay() || 7;  // normalizzazione del giorno
                let data_odierna = new Date();

                // Aggiungo anno
                $("#container-calendario > div.calendario-header > p.calendario-anno > span")
                .text(date.toLocaleString('it', { month: 'long' }) + " " + date.getFullYear());
                // .text(date.getFullYear() + "/" + (date.getMonth() || 12));

                // Cerco scadenze del mese
                let scadenze = Object.fromEntries(Array.from({length: 31}, (_, i) => [i + 1, 0]));  // giorno: codice_lista
                this.get_liste_with_scadenza().forEach((lista) => {
                    this.data.attivita.list[lista[1]].lista.list[lista[0]].scadenza.list
                    .forEach((scadenza) => {
                        let data_scadenza = new Date(scadenza.data);
                        if (data_scadenza.getMonth() == date.getMonth())
                            scadenze[data_scadenza.toDateString()] = scadenza.codice;
                    });
                });

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
                            is_valid: current_date.getMonth() == this.actual_date.getMonth() && current_date.getFullYear() == this.actual_date.getFullYear(),
                            is_active: current_date.toDateString() == data_odierna.toDateString(),
                            scadenza: scadenze[current_date.toDateString()] || null
                        };
                    });
                    this.crea_settimana(array_giorni, i+1).appendTo("#container-calendario > div.calendario-body");
                }
            }

            crea_membro(tipo) {
                if (tipo == "settings")
                {
                    $("#container-membri > p.link-invito span.link-value").text(this.data.codice_invito);
                    $("#container-membri span.link-support").hide();
                    $("#container-membri svg.link-support").show();
                }

                if (tipo == "proprietario") {
                    let elem = $("#membro-prototipo").clone(true);
                    elem.attr("id", "");                    

                    elem.find("p.membro-other").remove();
                    elem.find("button.btn-membro-elimina").remove();

                    if (this.data.membri.proprietario == true) elem.find("p.membro-proprietario").text("TU");
                    else elem.find("p.membro-proprietario").text(this.data.membri.proprietario_nome.toUpperCase());

                    // Icona
                    if (this.data.membri.proprietario) this.show_user_name(elem.find("div.user-icon"));
                    else this.show_user_chat_img(elem.find("div.user-icon"), this.data.membri.proprietario_codice);

                    elem.find("p.membro-proprietario").text(elem.find("p.membro-proprietario").text() + " (proprietario)");

                    elem.show();
                    elem.appendTo("#container-membri > div.membri-box");
                }

                if (tipo == "other") {
                    for (let i = 0; i < this.data.membri.length; i++) {
                        let dati = this.data.membri.list[i];

                        let elem = $("#membro-prototipo").clone(true);
                        elem.attr("id", dati.codice);
                        elem.find("p.membro-proprietario").remove();

                        // icona
                        if (dati.current) this.show_user_name(elem.find("div.user-icon"));
                        else this.show_user_chat_img(elem.find("div.user-icon"), dati.codice_utente);

                        if (dati.current) elem.find("button.btn-membro-elimina").hide();
                        else elem.find("button.btn-membro-elimina").show();

                        if (dati.current) elem.find("p.membro-other").append("TU");
                        else elem.find("p.membro-other").append(dati.nome);

                        elem.show();
                        elem.appendTo("#container-membri > div.membri-box");
                    }
                }
            }

            crea_spazi_lavoro(dati_bacheche, pos_x, pos_y) {
                let elem = $("#popup-spazi-lavoro");
                elem.find("div.bacheche-list-box").empty();

                for (let i = 0; i < dati_bacheche.length; i++) {
                    let info = dati_bacheche.list[i];
                    let elem_bacheca = $("#bacheche-list-prototipo").clone(true);
                    elem_bacheca.attr("id", info.codice);
                    
                    elem_bacheca.find("p.nome").text(info.nome);

                    elem_bacheca.show();
                    elem.find("div.bacheche-list-box").append(elem_bacheca);
                }

                // visual.popup.add(elem, "spazi-lavoro-popup");
                elem.css("left", pos_x);
                elem.css("top", pos_y);
            }

            skip_month(avanti=true) {
                let date = new Date(this.actual_date);
                if (avanti) date.setMonth(date.getMonth() + 1);
                else date.setMonth(date.getMonth() - 1);

                this.clear_html();
                $("#container-calendario").show();
                this.crea_calendario(date.getFullYear(), date.getMonth());
            }

            create_new_lista(dati, codice_attivita) {
                let idx = this.get_idx("attivita", codice_attivita)[0];
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

            get_liste_with_scadenza() {
                let array = [];
                for (let codice in this.cod_idx["lista"]) {
                    let idx_attivita = this.get_idx("attivita", this.get_idx("lista", codice)[1])[0];
                    let idx_lista = this.get_idx("lista", codice)[0];

                    console.log(idx_lista)
                    if (this.data.attivita.list[idx_attivita].lista.list[idx_lista].scadenza.length > 0)
                        array.push([idx_attivita, idx_lista]);
                }

                return array;
            }

            show_dati() {
                if (this.tipo == "isola") {
                    for (let i = 0; i < this.data.attivita.length; i++)
                        this.create_attivita(this.data.attivita.list[i]);
                    
                    $("#container-isola").show();
                }

                if (this.tipo == "tabella") {
                    this.get_liste_with_scadenza().forEach((lista) => {
                        let idx_attivita = lista[0];
                        let idx_lista = lista[1];

                        let info_attivita = this.data.attivita.list[idx_attivita].info;
                        let info_lista = this.data.attivita.list[idx_attivita].lista.list[idx_lista];
                        let info_etichette = info_lista.etichetta;
                        let info_scadenza = info_lista.scadenza.list[0];

                        let elem = $("#attivita-prototipo-tabella").clone(true);
                        elem.attr("id", "attivita-" + info_attivita.codice);

                        elem.find("div.prima-cella > h3.attivita-titolo > input").val(info_attivita.titolo);
                        elem.find("div.prima-cella").attr("id", info_attivita.codice);

                        elem.find("div.seconda-cella > p.lista-nome > span").text(info_lista.nome);
                        elem.find("div.seconda-cella > p.lista-nome").attr("id", info_lista.codice);
                        
                        let elem_eti = "";
                        elem.find("div.terza-cella > div.lista-etichetta-box").append(this.crea_etichetta(info_etichette).find("p.etichetta-text"));

                        elem.find("div.quarta-cella > p.lista-scadenza").text(info_scadenza.data);
                        elem.find("div.quarta-cella > p.lista-scadenza").addClass((info_scadenza.valida) ? "scadenza-valida": "scadenza-invalida");
                        
                        elem.show();
                        elem.appendTo("#container-tabella");
                    });

                    $("#container-tabella").show();
                }

                if (this.tipo == "calendario") {
                    this.crea_calendario();
                    $("#container-calendario").show();
                }

                if (this.tipo == "membri") {
                    this.crea_membro("settings");
                    this.crea_membro("proprietario");
                    this.crea_membro("other");
                    $("#container-membri").show();
                }

                if (this.tipo == "impostazioni") {
                    $("#inp-nome-bacheca").val(dati.nome_bacheca);
                    $("#container-impostazioni").show();
                }
            }

            clear_html() {
                $('#container-isola').children().not('#attivita-nuova').remove();
                $("#container-isola").hide();
                
                $("#container-tabella").children().not("#attivita-tabella-header").remove();
                $("#container-tabella").hide();

                $("#container-calendario > div.calendario-body").empty();
                $("#container-calendario").hide();

                $("#container-membri > div.membri-box").empty();
                $("#container-membri").hide();

                $("#container-impostazioni").hide();
            }

            show_lista_info(dati) {
                let elem = $("#lista-info-prototipo").clone(true);
                elem.attr("id", dati.codice);

                // Aggiungo valori campi
                elem.find("input.lista-nome").val(dati.nome);
                elem.find("input.lista-descrizione").val(dati.descrizione);
                
                // Aggiungo elementi
                elem.find("div.lista-checkbox-box").append(this.crea_checkbox(dati.checkbox));
                elem.find("div.lista-etichetta-box").append(this.crea_etichetta(dati.etichetta));
                elem.find("div.lista-commento-box").append(this.crea_commento(dati.commento));
                elem.find("div.lista-scadenza-box").append(this.crea_scadenza(dati.scadenza));
                elem.find("div.lista-sposta-box p.attuale").text("\u2713" + " " +this.data.attivita.list[this.get_idx("lista", dati.codice)[0]].info["titolo"]);

                // Aggiungo proprietà
                elem.find(".text-mod > span").attr("contenteditable", "true");
                elem.find(".text-mod > span").on("focus", e => e.currentTarget.value = $(e.currentTarget).text());
                elem.find("p.text-mod > span").on('keydown', e => { if (e.keyCode === 13) { e.preventDefault(); $(e.currentTarget).blur(); } });
                elem.find("p.text-mod > span").blur(e =>{ let target = $(e.currentTarget); if (target.get(0).value != target.text()) console.log(target.text()) });

                // Mostro
                elem.show();
                this.popup.add(elem, "lista-info-popupbox");
            }

            show_lista_nuova(codice_attivita) {
                let elem = $("#lista-nuova-prototipo").clone(true);
                elem.attr("id");
                elem.find("form.form-nuova-lista").append($('<input>').attr({
                    type: 'hidden',
                    name: 'codice_attivita',
                    value: codice_attivita
                }));
                this.popup.add(elem, "lista-nuova-popupbox");
            }

            show_attivita_nuova() {
                let elem = $("#attivita-nuova-prototipo").clone(true);
                elem.attr("id");

                this.popup.add(elem, "attivita-nuova-popupbox");
            }

            show_msg(dati) {
                for (let i = 0; i < dati.length; i++) {
                    let msg = dati.list[i];
                    let elem = $("#messaggio-prototipo").clone(true);
                    let target = $("#chat-content");
                    elem.attr("id", msg.codice);

                    // cerco immagine profilo
                    if (msg.current)
                        this.show_user_name(elem.find("div.user-icon"));
                    else
                        this.show_user_chat_img(elem.find("div.user-icon"), msg.codice_utente);

                    elem.find("p.mittente").text(msg.nome_utente);
                    elem.find("p.testo").text(msg.testo);
                    elem.find("p.orario").text(msg.orario);

                    if (msg.current) elem.addClass("msg-proprio");
                    else elem.addClass("msg-other");
                    
                    elem.show();
                    target.append(elem);

                    this.codLastMsg = msg.codice;
                    target.scrollTop(target.prop("scrollHeight"));
                }
            }

            show_sposta_box(cod_attivita) {
                this.crea_sposta(cod_attivita);
                $("#popup div.lista-sposta-box p.attuale").hide();
            }

            hide_sposta_box(cod_lista, cod_press) {
                let cod_attivita = this.get_idx("lista", cod_lista)[1];
                
                $("#popup div.lista-sposta-box p.attuale").show();
                $("#popup div.menu-sposta").remove();
                let self = this;
                // cambio attivita
                if (cod_attivita != cod_press) {
                    console.log(cod_attivita, cod_press, cod_lista)
                    $.ajax({
                        url: "attivita.php",
                        type: "POST",
                        data: {
                            "action": "sposta-lista",
                            "codice_lista": cod_lista,
                            "from_codice": cod_attivita,
                            "to_codice": cod_press,
                            "codice_bacheca": this.codice_bacheca
                        },
                        success: function (result) {
                            console.log(result)
                            result = JSON.parse(result);
                            if (result.esito)
                            {
                                self.popup.close();
                                // let dati = se.data.attivita.list[this.get_idx("attivita", cod_attivita)[0]].lista.list[this.get_idx("lista", cod_lista)[0]];
                                self.cancella_lista(cod_lista, false);
                                $("#" + cod_lista).remove();
                                self.create_new_lista(result.lista, cod_press);
                            }
                        }
                    });
                }
                    
            }

            cancella_lista(codice_lista, cancella=true) {
                let idx_lista = this.get_idx("lista", codice_lista)[0];
                let idx_att = this.get_idx("attivita", this.get_idx("lista", codice_lista)[1])[0];
                
                
                delete this.cod_idx["lista"][codice_lista];
                delete this.data.attivita.list[idx_att].lista.list[idx_lista];
                this.data.attivita.list[idx_att].lista.length -= 1;

                if (cancella)
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

            loop_chat() {
                this.aggiornamento_messaggi = setInterval(() => this.get_new_msg(), 1000);
            }

            mostra_chat() {
                $("#chat > *:not(#chat-manager)").show();

                $("#chat").removeClass("mini");
                $("#chat-manager > svg.svg-up-arrow").hide();
                $("#chat-manager > svg.svg-down-arrow").show();
                
                this.loop_chat();
            }

            chiudi_chat() {
                $("#chat > *:not(#chat-manager)").hide();
                
                $("#chat").addClass("mini");
                $("#chat-manager > svg.svg-up-arrow").show();
                $("#chat-manager > svg.svg-down-arrow").hide();

                clearInterval(this.aggiornamento_messaggi);
            }

            get_chat_user_img(codice_utente, target) {
                const self = this;

                $.ajax({
                    url: "attivita.php",
                    type: "POST",
                    data: {
                        "action": "img-user-profile",
                        "codice_bacheca": this.codice_bacheca,
                        "codice_utente": codice_utente
                    },
                    crossDomain: true,

                    success: function (result) {
                        result = JSON.parse(result);
                        if (result.esito == true) {
                            let dati = result.data;
                            if (dati.tipo != "default")
                                dati.dati = `url('data:${dati.tipo};base64,${dati.dati}')`;

                            self.chat_img_profile[codice_utente] = dati;
                            self.show_user_chat_img(target, codice_utente);
                        }
                    },

                    error: function (err) {
                        console.log(err);
                    }
                });
            }

            get_new_msg() {
                $.ajax({
                    url: "attivita.php",
                    type: "POST",
                    data: {
                        "action": "get-new-msg",
                        "codice_ultimo_messaggio": this.codLastMsg,
                        "codice_bacheca": this.codice_bacheca
                    },
                    success: function (result) {
                        result = JSON.parse(result);
                        if (result.esito)
                            visual.show_msg(result.data);
                    },
                    error: function (result) {
                        console.log(result);
                    }
                });
            }
        }

        function set_theme_color(colors) {
            $("html").css("--color-primary", colors[0]);
            $("html").css("--color-secondary", colors[1]);
            $("html").css("--color-tertiary", colors[2]);
        }

        // Passaggio dati
        let dati = <?php echo json_encode($data); ?>;

        console.log(dati);

        set_theme_color(dati.tema);

        let searchParams = new URLSearchParams(window.location.search);
        const CODICE_BACHECA = searchParams.get('codice');

        let visual = new Visualizator(dati);
        visual.show_user_name();
        visual.show_dati();
        visual.chiudi_chat();

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
            let colore = target.parent().siblings("div.colore-etichetta").find("input[name='colore']");
            let testo = target.parent().siblings("div.colore-etichetta").find("input[name='testo']");

            console.log(target, colore, testo);
            if (colore.val() && testo.val()) {
                let color_hex = colore.val().replace('#', '');

                // Converti il valore esadecimale in RGB
                let red = parseInt(color_hex.substring(0, 2), 16);
                let green = parseInt(color_hex.substring(2, 4), 16);
                let blue = parseInt(color_hex.substring(4, 6), 16);

                let codice_lista = target.closest("div.lista-info-box").attr("id");

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
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        //$("#" + codice_lista).find("div.lista-etichetta-box").append(visual.crea_etichetta(result.etichetta));
                        visual.popup.find("div.lista-etichetta-box").append(visual.crea_etichetta(result.etichetta));
                        testo.val(""); colore.val("");
                        visual.aggiungi_elemento_lista("etichetta", codice_lista, result.etichetta.list[0]);
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
            }
        });

        $("body").on("click", "button.btn-etichetta-reset", function (e) {
            $(e.currentTarget).parent().siblings("div.colore-etichetta").find("input").val('');
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
            let testo = target.parent().siblings("textarea");

            let codice_lista = target.closest("div.lista-info-box").attr("id");
            if (testo.val()) {
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
                        result =JSON.parse(result);
                        console.log(result);
                        if (result.esito == true) {
                            visual.popup.find("div.lista-checkbox-box").prepend(visual.crea_checkbox(result.checkbox));
                            testo.val("");
                            visual.aggiungi_elemento_lista("checkbox", codice_lista, result.checkbox.list[0]);
                        }
                    },

                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });

        $("body").on("click", "button.btn-checkbox-reset", function (e) {
            $(e.currentTarget).parent().siblings("textarea").val('');
        });

        $("body").on("click", "div.lista-checkbox-box div.cestino", function (e) {
            visual.cancella_elemento_lista("checkbox", $(e.currentTarget).attr("id").split("-")[1]);
            console.log("codice:", $(e.currentTarget).attr("id").split("-")[1]);
        });

        $("body").on("click", "button.btn-commento-nuovo", function (e) {
            let target = $(e.currentTarget);
            let testo = target.parent().siblings("textarea");

            let codice_lista = target.closest("div.lista-info-box").attr("id");
            if (testo.val()) {
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
                        result =JSON.parse(result);
                        console.log(result);
                        if (result.esito == true) {
                            visual.popup.find("div.lista-commento-box").prepend(visual.crea_commento(result.commento));
                            testo.val("");
                            visual.aggiungi_elemento_lista("commento", codice_lista, result.commento.list[0]);
                        }
                    },

                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });

        $("body").on("click", "button.btn-commento-reset", function (e) {
            $(e.currentTarget).parent().siblings("textarea").val('');
        });

        $("body").on("click", "div.lista-commento-box .elimina-commento", function (e) {
            visual.cancella_elemento_lista("commento", $(e.currentTarget).attr("id").split("-")[1]);
        });

        $("body").on("click", "button.btn-scadenza-nuovo", function (e) {
            let target = $(e.currentTarget);
            let data = target.siblings("input[name='scadenza']");
            
            let codice_lista = target.closest("div.lista-info-box").attr("id");
            if (data.val()) {
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
                            visual.popup.find("div.lista-scadenza-box").append(visual.crea_scadenza(result.scadenza));
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
            visual.popup.find("div.lista-scadenza-box").append(visual.crea_scadenza({"length": 0}));
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

        $("body").on("click", "div.membro > button.btn-membro-elimina", function (e) {
            let target = $(e.currentTarget);
            let codice = target.closest("div.membro").attr("id");
            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "delete-membro",
                    "codice_bacheca": CODICE_BACHECA,
                    "codice": codice
                },
                crossDomain: true,

                success: function () {
                    target.closest("div.membro").remove();
                    let idx_membro = visual.get_idx("membri", codice)[0];
                    delete visual.data.membri.list[idx_membro];
                    visual.data.membri.length -= 1;
                    delete visual.cod_idx["membri"][codice];
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("body").on("click", "div.lista-sposta-box div.sposta-attivita", function(e) {
            let target = $(e.currentTarget);
            let cod_lista = target.attr("cod_lista");
            let cod_attivita_from = target.attr("cod_attivita");
            let cod_attivita_to = target.find("p.lista-text").text();

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "sposta-lista",
                    "codice_bacheca": CODICE_BACHECA,
                    "from": cod_attivita_from,
                    "to": cod_attivita_to,
                    "codice_lista": cod_lista
                },
                crossDomain: true,

                success: function (result) {
                    result = JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        visual.cancella_lista(cod_lista, false);
                        visual.create_new_lista(result.lista, cod_attivita_to);
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("body").on("click", "div.bacheche-list-box div.bacheche-elem", function(e) {
            let target = $(e.currentTarget);
            let cod_bacheca = target.attr("id");
            location.href = location.href = "bacheca.php?codice=" +encodeURIComponent(cod_bacheca);
        });

        $("body").on("input", "div.lista-info-box input.lista-nome", function (e) {
            let target = $(e.currentTarget);
            let codice_lista = target.closest("div.lista-info-box").attr("id");

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "change-lista-nome",
                    "nome": target.val(),
                    "codice_lista": codice_lista,
                    "codice_bacheca": CODICE_BACHECA
                },
                success: function (result) {
                    console.log(result);
                    visual.data.attivita.list[visual.get_idx("attivita", visual.get_idx("lista", codice_lista)[1])[0]].lista.list[visual.get_idx("lista", codice_lista)[0]].nome = target.val();
                    $("#" + codice_lista).find("p.lista-nome > span").text(target.val());
                },
                error: function (result) {
                    console.log(result);
                }
            });
        });

        $("body").on("input", "div.lista-info-box input.lista-descrizione", function (e) {
            let target = $(e.currentTarget);

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "change-lista-descizione",
                    "descrizione": target.val(),
                    "codice_lista": target.closest("div.lista-info-box").attr("id"),
                    "codice_bacheca": CODICE_BACHECA
                },
                success: function (result) {
                    console.log(result);
                },
                error: function (result) {
                    console.log(result);
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
            visual.cancella_lista(target.closest("div.lista-info-box").attr("id"));
            visual.popup.close();
            e.stopPropagation();
        });

        // click aprire sposta
        $("body").on("click", "#popup div.lista-sposta-box p.attuale", function (e) {
            visual.show_sposta_box(visual.get_idx("lista", $(e.currentTarget).closest("div.lista-info-box").attr("id"))[1]);
        });

        // click fare sposta
        $("body").on("click", "#popup div.menu-sposta p", function (e) {
            let target = $(e.currentTarget);
            visual.hide_sposta_box(
                target.closest("div.lista-info-box").attr("id"),
                target.attr("codice")
            );
        });

        // Bottone modalità
        $("div.select-type-visual").on("click", function (e) {
            let elem = $(e.currentTarget);
            if (elem.hasClass("active")) return;

            $("div.select-type-visual").removeClass("active");
            visual.change_type(elem.attr("value"));
            elem.addClass("active");
        });

        // svg girare calendario
        $("svg.calendario-arrow").click(function (e) {
            let target = $(e.currentTarget);
            visual.skip_month(target.attr("direction") == "right");
        });

        // svg copiare link invito
        $("p.link-invito > svg.link-support").click(function (e) {
            let svg = $(e.currentTarget);
            let p = svg.siblings("span.link-support");
            let link = svg.siblings("span.link-text").text();

            let elem = $("<input>").appendTo("body").val(link).select();
            document.execCommand("copy");
            elem.remove();

            svg.hide();
            p.show();

            setTimeout(() => {
                svg.show();
                p.hide();
            }, 2000);
        });

        // mostro spazi di lavoro
        $("div.spazi-lavoro").click(function (e) {
            let target = $(e.currentTarget);

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "bacheche-list",
                    "codice_bacheca": CODICE_BACHECA
                },
                crossDomain: true,

                success: function (result) {
                    result = JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        let offset = target.offset();
                        target.find("#popup-spazi-lavoro").show();
                        visual.crea_spazi_lavoro(result.list, offset.left, offset.top + target.outerHeight());
                    }
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        // chiudo spazi di lavoro
        $("#popup-spazi-lavoro svg.svg-chiudi").click(function(e) {
            $("#popup-spazi-lavoro").hide();
            e.stopPropagation();
        });

        // Invio messaggi in chat
        $("#svg-send-msg").click(function (e) {
            let target = $(e.currentTarget);
            let testo = target.siblings("input[name='msg']");

            if (!testo.val()) return;

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "create-new-msg",
                    "codice_bacheca": CODICE_BACHECA,
                    "testo": testo.val()
                },
                crossDomain: true,

                success: function (result) {
                    console.log(result);
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        visual.get_new_msg();
                    }

                    testo.val("");
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        // Mostro chat
        $("#chat-manager svg.svg-up-arrow").click(function (e) {
            visual.mostra_chat(); 
        });

        $("#chat-manager svg.svg-down-arrow").click(function (e) {
            visual.chiudi_chat(); 
        });

        // cambio nome attivita
        $("div.attivita-box-isola h3.attivita-titolo > input").change(function (e) {
            let target = $(e.currentTarget);
            let codice_attivita = target.closest("div.attivita-box-isola").attr("id");
            console.log(codice_attivita);

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "change-attivita-titolo",
                    "titolo": target.val(),
                    "codice_bacheca": CODICE_BACHECA,
                    "codice_attivita": codice_attivita
                },
                success: function (result) {
                    visual.data.attivita.list[visual.get_idx("attivita", codice_attivita)[0]].info["titolo"] = target.val();
                    console.log(result);
                },
                error: function (result) {
                    console.log(result);
                }
            });
        });

        // cambio nome bacheca
        $("#inp-nome-bacheca").change(function (e) {
            let target = $(e.currentTarget);

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "change-bacheca-nome",
                    "nome": target.val(),
                    "codice_bacheca": CODICE_BACHECA
                },
                success: function (result) {
                    console.log(result);
                },
                error: function (result) {
                    console.log(result);
                }
            });
        });

        // cancello bacheca
        $("button.btn-elimina-bacheca").click(function (e) {
            let target = $(e.currentTarget);

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "delete-bacheca",
                    "codice_bacheca": CODICE_BACHECA
                },
                success: function (result) {
                    location.href = "bacheche.php";
                    console.log(result);
                },
                error: function (result) {
                    console.log(result);
                }
            });
        });

    </script>
</body>
</html>