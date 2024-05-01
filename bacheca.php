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
    $id_console_bacheca = $conn->query("SELECT console FROM Bacheca WHERE ID=$id_bacheca;")->fetch_assoc()["console"];

    $data["codice_bacheca"] = $codice_bacheca;
    $data["privilegi"] = $privilegi;

    $data["attivita"] = get_dati_attivita($id_bacheca);    

    //TOOD: crea nuovo elemento (attività, lista, checkbox, ...) -> attivita.php

    //TODO: gestione accessi
    $data["membri"] = get_membri_bacheca($id_bacheca, $id_utente);
    $data["membri"]["proprietario"] = ($id_console_bacheca == $id_console);
    
    if (!$data["membri"]["proprietario"]) {
        $data["membri"]["proprietario_nome"] = get_nome_utente(null, $id_console_bacheca);
    }

    $data["codice_invito"] = get_codice_invito($id_bacheca);

    $data["nome_utente"] = get_nome_utente($id_utente);

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

        background-color: #e0ab23;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-around;

        border-radius: 16px;
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

        background-color: #e0ab23;
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
        font-family: "Concert One", sans-serif;
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
    }

    div.calendario-header {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        width: 50%;
        margin-left: 50%;
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
        width: 270px;
        text-transform: none;

        justify-content: space-around;
        align-items: center;
        display: flex;
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



    /*stile menu laterale*/
    :root {
        --background: #f3e0ad;
        --navbar-width: 256px;
        --navbar-width-min: 80px;
        --navbar-dark-primary: #e0ab23; /*colore menu*/
        --navbar-dark-secondary: #d05e26; /*colore righe*/
        --navbar-light-primary: #eee; /*colore titolo avatar*/
        --navbar-light-secondary: #eee; /*colore descrizione avatar*/

        --color-primary: #e0ab23;  /* uguale colore menu */
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
        color: var(--navbar-dark-primary); 
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

    
        /* margine sinistro delle scritte*/
    #nav-bar .fas {
    min-width: 3rem;
    text-align: center; 
    }


    /* popup uno */
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
    left: 79.5%;
    
    width: 400px;
    height: 300px;
    }

    .popup .show {
    visibility: visible;

    top: 65%;
    left: 73vh;
    }

        /*Spazi di lavoro*/
    .popup{
        text-decoration: none;
        font-family: "Concert One", sans-serif;
        font-weight: bolder;
        font-style: normal;
        font-size: larger;
        color: #000000;
        cursor: pointer;
        
    }

    .popup:hover{
    color: #f3e0ad;  
    text-decoration: underline;
    }

    hr{
    border-color: #fff;
    border-width: 1px;
    margin-inline-end: auto;
    border-style: solid;
    opacity: 0.7;
    width: 399px;
    }


    /* popup */
    .popup-2 .popuptext-2 {
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
    
    width: 300px;
    height: 500px;
    }

    .popup-2 .show {
    visibility: visible;

    top: 75%;
    right: 10px;
    }

        /*Spazi di lavoro*/
    .popup-2{
        text-decoration: none;
        font-family: "Concert One", sans-serif;
        font-weight: bolder;
        font-style: normal;
        font-size: larger;
        color: #000000;
        cursor: pointer;
        
    }

    .popup-2:hover{
    color: #f3e0ad;  
    text-decoration: underline;
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
    }

    div.lista h2 {
        width: fit-content;
    }

    #container-membri {
        margin-left: 10%;
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
    }

    div.membro {
        border-radius: 10px;
        background-color: var(--color-primary);
        padding: 10px;
        height: 30px;
        text-align: center;
        min-width: 200px;

        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    p.link-invito > svg {
        width: 20px;
        height: 20px;
    }

    div.membro p.select-visual-format {
        background-color: transparent;
        width: 100%;
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
    }

    div.membro button.btn-membro-elimina > p.select-visual-format {
        font-size: 14px;
        font-weight: 100;
    }

    span.link-text {
        margin-right: 10px;
    }

    span.link-support {
        color: green;
    }

    </style>
</head>
<body>
    <div class="header">
        <!--parte della barra a sinistra-->
        <div class="navbar">
           <div class="navbar-left" > 
   
               <!--immagine del logo-->
               <a href="../index.html">
               <img  src="img/logo_scritta_completo.png"  class="logo">
               </a>

               <div class="button" >Crea una nuova bacheca </div>

                <!--tutte le bacheche di un account-->
                  <div class="popup" onclick="popup_function()"> Spazi di Lavoro

                  <div class="popuptext" id="myPopup"> <p style="margin: 10px;"> La tua Bacheca attuale </p>
                  <hr/>
                  <!-- spazio per la bacheca attuale-->


                  <p style="margin: 10px; padding-top: 100px;"> Le tue Bacheche </p>
                  <hr/>
                  <!-- spazio per le bacheche-->
                 
                 </div>
                </div>
              </div>
   
               <div class="navbar-left">
                 <div class="pfp" > E T</div>
                     <div class="popup-2" onclick="location.href = 'account.php'" style="margin-left: 0px;">Account

                       <div class="popuptext-2" id="myPopup_2"> prova </div>
                     
                     </div>
                     
             </div>
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

                    <div class="nav-button select-type-visual" value="isola">
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

                    <div class="nav-button active select-type-visual" value="membri">
                        <i class="fas fa-thumbtack"></i>
                        <p class="select-visual-format">Membri</p>
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
                
                    <div class="prima-cella">
                        <h3 class="attivita-titolo">Attività</h3>
                    </div>

                    <div class="seconda-cella">
                        <h3 class="attivita-titolo">Liste</h3>
                    </div>

                    <div class="terza-cella">
                        <h3 class="attivita-titolo">Etichette</h3>
                    </div>

                    <div class="quarta-cella">
                        <h3 class="attivita-titolo">Scadenza</h3>
                    </div>

                </div>
            </div>

            
            <div id="container-calendario" style="display: none">
                <div class="calendario-header">
                    <p class="calendario-anno"><span></span></p>
                    <div>
                        <svg class="calendario-arrow" direction="left" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 493.468 493.468" xml:space="preserve"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path d="M246.736 0C110.688 0 .008 110.692.008 246.732c0 136.056 110.68 246.736 246.728 246.736S493.46 382.788 493.46 246.732C493.46 110.692 382.784 0 246.736 0m-49.144 249.536 94.764 94.776a5.28 5.28 0 0 1 1.568 3.776c0 1.448-.556 2.784-1.568 3.772l-8.96 8.98c-2.004 2.004-5.568 2.012-7.568 0l-110.14-110.136c-1.008-1.016-1.556-2.38-1.54-3.932a5.35 5.35 0 0 1 1.536-3.852l110.312-110.304a5.3 5.3 0 0 1 3.776-1.56c1.424 0 2.788.556 3.78 1.56l8.968 8.98c2.1 2.06 2.1 5.468.004 7.548l-94.932 94.944a3.846 3.846 0 0 0 0 5.448"/></svg>
                        <svg class="calendario-arrow" direction="right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 455 455" xml:space="preserve" stroke="#000" stroke-width=".005"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round" stroke="#CCC" stroke-width="2.73"/><path d="M227.5 0C101.855 0 0 101.855 0 227.5S101.855 455 227.5 455 455 353.145 455 227.5 353.145 0 227.5 0zm-28.024 355.589-21.248-21.178L284.791 227.5 178.228 120.589l21.248-21.178L327.148 227.5z"/></svg>
                    </div>
                </div>
                <div class="calendario-body"></div>
            </div>

            <div id="container-membri" style="display: none">
                <h1>Membri</h1>
                <p class="link-invito">Link invito:
                    <span class="link-text">http://torg.altervista.org/bacheca_invito.php?sharing=<span class="link-value"></span>  </span>
                    <svg class="link-support" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M208 0h124.1C344.8 0 357 5.1 366 14.1L433.9 82c9 9 14.1 21.2 14.1 33.9V336c0 26.5-21.5 48-48 48H208c-26.5 0-48-21.5-48-48V48c0-26.5 21.5-48 48-48M48 128h80v64H64v256h192v-32h64v48c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48"/></svg>
                    <span class="link-support">Copied!</span>
                </p>    

                <div class="membri-box">
                </div>
            </div>
        </div>
    </div>

    <div id="cestino" class="cestino">
        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 64 64">
            <path d="M 28 11 C 26.895 11 26 11.895 26 13 L 26 14 L 13 14 C 11.896 14 11 14.896 11 16 C 11 17.104 11.896 18 13 18 L 14.160156 18 L 16.701172 48.498047 C 16.957172 51.583047 19.585641 54 22.681641 54 L 41.318359 54 C 44.414359 54 47.041828 51.583047 47.298828 48.498047 L 49.839844 18 L 51 18 C 52.104 18 53 17.104 53 16 C 53 14.896 52.104 14 51 14 L 38 14 L 38 13 C 38 11.895 37.105 11 36 11 L 28 11 z M 18.173828 18 L 45.828125 18 L 43.3125 48.166016 C 43.2265 49.194016 42.352313 50 41.320312 50 L 22.681641 50 C 21.648641 50 20.7725 49.194016 20.6875 48.166016 L 18.173828 18 z"></path>
        </svg>
    </div>

    <div id="popup">
        <div id="popup-box">
        </div>
    </div>

    <div id="attivita-prototipo-isola" class="attivita-box attivita-box-isola" style="display: none">
        <div class="attivita-header">
            <h3 class="attivita-titolo"><span></span></h3>
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
            <form class="form-nuova-lista" method="post">
                <label for="">Nome</label>
                <input type="text" name="nome" id="">

                <label for="">Descrizione</label>
                <textarea name="descrizione" id="" cols="30" rows="10"></textarea>

                <input type="submit" value="Aggiungi Lista">
            </form>
        </div>
    </div>

    <div id="membro-prototipo" class="membro" style="display: none">
        <p class="membro-proprietario select-visual-format"></p>
        <p class="membro-other select-visual-format"></p>
        <button class="btn-membro-elimina" style="display: none"><p class="select-visual-format">Elimina</p></button>
    </div>
    
    <script>
        class Visualizator {
            constructor (data) {
                let searchParams = new URLSearchParams(window.location.search);
                this.codice_bacheca = searchParams.get('codice');

                this.data = data;
                this.tipo = "membri";

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

                for (let i = 0; i < this.data.membri.length; i++) {
                    let dati = this.data.membri.list[i];
                    this.add_idx_elem("membri", dati.codice, this.codice_bacheca);
                }
            }

            // method
            show_user_name() {
                $("div.header div.pfp").text(this.data["nome_utente"].split(" ").map(p=>p.charAt(0).toUpperCase()).join(" "));
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
                    // console.log(giorno) 
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

                        if (dati.current) elem.find("button.btn-membro-elimina").hide();
                        else elem.find("button.btn-membro-elimina").show();

                        if (dati.current) elem.find("p.membro-other").append("TU");
                        else elem.find("p.membro-other").append(dati.nome);

                        elem.show();
                        elem.appendTo("#container-membri > div.membri-box");
                    }
                }
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

                        elem.find("div.prima-cella > h3.attivita-titolo > span").text(info_attivita.titolo);
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
            }

            show_lista_info(dati) {
                let elem = $("#lista-info-prototipo").clone(true);
                elem.attr("id", dati.codice);

                // Aggiungo valori campi
                elem.find("p.lista-codice > span").text(dati.codice);
                elem.find("p.lista-nome > span").text(dati.nome);
                elem.find("p.lista-descrizione > span").text(dati.descrizione);
                
                // Aggiungo elementi
                elem.find("div.lista-checkbox-box").append(this.crea_checkbox(dati.checkbox));
                elem.find("div.lista-etichetta-box").append(this.crea_etichetta(dati.etichetta));
                elem.find("div.lista-commento-box").append(this.crea_commento(dati.commento));
                elem.find("div.lista-scadenza-box").append(this.crea_scadenza(dati.scadenza));

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
        let dati = <?php echo json_encode($data); ?>;

        console.log(dati);

        let searchParams = new URLSearchParams(window.location.search);
        const CODICE_BACHECA = searchParams.get('codice');

        let visual = new Visualizator(dati);
        visual.show_user_name();
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
            visual.cancella_lista(target.closest("div.lista-info").find(".lista-codice > span").text());
            visual.popup.close();
            e.stopPropagation();
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

    </script>
</body>
</html>