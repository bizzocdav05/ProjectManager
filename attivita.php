<?php
// bacheca

include "utils.php";

session_start();
login_required();
$id_console = set_console();

date_default_timezone_set('CET');

function create_new_colore($colori) {
    $conn = connection();
    $red = $colori["red"];
    $green = $colori["green"];
    $blue = $colori["blue"];

    // controllo esistenza
    $result = $conn->query("SELECT ID FROM Colore WHERE red=$red AND green=$green AND blue=$blue");
    if ($result->num_rows == 1) {
        return $result->fetch_assoc()["ID"];
    }

    $conn->query(create_sql("INSERT INTO Colore",
        array("red", "green", "blue"),
        array($red, $green, $blue)
    ));

    $result = $conn->query("SELECT ID FROM Colore WHERE red=$red AND green=$green AND blue=$blue");
    if ($result->num_rows == 1) {
        return $result->fetch_assoc()["ID"];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    $conn = connection();

    if ($action == "new-bacheca" && isset($_POST["nome"])) {
        $codice = genera_codice(16, -1);  // id temp per la creazione, poi da update
    
        $result = $conn->query(create_sql("INSERT INTO Bacheca", array("console", "nome", "codice"), array($_SESSION["id_console"], $_POST["nome"], $codice)));
        
        // cerco la bacheca per inserire l'id nel codice
        $sql = "SELECT ID FROM Bacheca WHERE console=$id_console AND codice='$codice';";
        $result = $conn->query($sql);

        $id_bacheca = $result->fetch_assoc()["ID"];

        // update
        $sql = "UPDATE Codici SET bacheca=$id_bacheca WHERE codice='$codice';";
        $result = $conn->query($sql);

        $dati = array("esito" => true, "bacheca" => array("nome" => $_POST["nome"], "codice" => $codice));
        echo json_encode($dati);
        exit();
    }

    if (!isset($_POST["codice_bacheca"])) {
        exit();
    }

    // cerco bacheca attraverso il codice
    $temp = set_bacheca($_POST["codice_bacheca"]);
    $codice_bacheca = $_POST["codice_bacheca"];
    $id_bacheca = $temp[0];
    $privilegi = $temp[1];

    $id_utente = get_utente();

    $dati = array("esito" => false);

    if ($action == "bacheche-list") {
        $dati["esito"] = true;
        $dati["list"] = get_bacheche_list();
    }
    
    if ($action == "new-attivita") {
        $dati["attivita"] = array();
        if (isset($_POST["titolo"])) {
            $data = date("Y-m-d");
            $codice_attivita = genera_codice(16, $id_bacheca);

            $sql = create_sql("INSERT INTO Attivita",
                array("titolo, data_creazione, data_ultima_modifica", "bacheca", "codice"),
                array($_POST["titolo"], $data, $data, $id_bacheca, $codice_attivita));
            $result = $conn->query($sql);
            
            $dati["esito"] = true;
            $dati["attivita"]["info"]["titolo"] = $_POST["titolo"];
            $dati["attivita"]["info"]["dati-ultima-modifica"] = $data;
            $dati["attivita"]["info"]["data-creazione"] = $data;
            $dati["attivita"]["info"]["codice"] = $codice_attivita;

            $dati["attivita"]["lista"] = array("length" => 0, "list" => array());
        }
    }

    if ($action == "new-lista") {
        if (isset($_POST["codice_attivita"]) && isset($_POST["nome"]) && isset($_POST["descrizione"]))
        {
            $codice_attivita = $_POST["codice_attivita"];

            $codici = array("bacheca" => $codice_bacheca, "codice" => $codice_attivita);

            $nome = $_POST["nome"];
            $descrizione = $_POST["descrizione"];
            
            // cerco ID attivita (autorizzato)
            $id_attivita = get_elem_by_code("Attivita", $codici);

            $codice = genera_codice(16, $id_bacheca);

            $conn->query(create_sql(
                "INSERT INTO Lista",
                array("attivita", "nome", "descrizione", "data_creazione", "data_ultima_modifica", "codice"),
                array($id_attivita, $nome, $descrizione, date("Y-m-d"), date("Y-m-d"), $codice)
            ));

            $dati["esito"] = true;
            $dati["lista"] = array(
                "nome" => $nome,
                "descrizione" => $descrizione,
                "data_creazione" => date("Y-m-d"),
                "data_ultima_modifica" => date("Y-m-d"),
                "codice" => $codice,
                "commento" => array("length" => 0, "list" => array()),
                "etichetta" => array("length" => 0, "list" => array()),
                "checkbox" => array("length" => 0, "list" => array()),
                "scadenza" => array("length" => 0, "list" => array()),
            );
        }
    }

    if ($action == "delete-lista") {
        if (isset($_POST["codice_lista"])) {
            $codice_lista = $_POST["codice_lista"];

            // cancello da codici
            $sql = "DELETE FROM Codici WHERE codice='$codice_lista' AND bacheca=$id_bacheca;";
            $result = $conn->query($sql);

            if (!$result) {
                echo "autorizzazione negata";
                $conn->close();
                exit();
            }

            // cancello lista
            $sql = "DELETE FROM Lista WHERE codice='$codice_lista';";
            $result = $conn->query($sql);

            $conn->close();
            exit();
        }
    }

    if ($action == "delete-attivita") {
        if (isset($_POST["codice_attivita"])) {
            $codice_attivita = $_POST["codice_attivita"];

            // cancello da codici
            $sql = "DELETE FROM Codici WHERE codice='$codice_attivita' AND bacheca=$id_bacheca;";
            $result = $conn->query($sql);

            if (!$result) {
                echo "autorizzazione negata";
                $conn->close();
                exit();
            }

            // cancello lista
            $sql = "DELETE FROM Attivita WHERE codice='$\';";
            $result = $conn->query($sql);

            $conn->close();
            exit();
        }
    }

    if ($action == "new-commento") {
        if (isset($_POST["testo"]) && $_POST["codice_lista"]) {
            $testo = $_POST["testo"];
            $codice_lista = $_POST["codice_lista"];

            $codice = genera_codice(16, $id_bacheca);
            $id_lista = get_elem_by_code("Lista", array("codice" => $codice_lista, "bacheca" => $codice_bacheca));

            $conn->query(create_sql("INSERT INTO Commento",
                array("testo", "data_creazione", "user", "lista", "codice"),
                array($testo, date("Y-m-d"), $id_utente, $id_lista, $codice)
            ));

            $sql = "SELECT nome, cognome FROM Utenti WHERE ID = $id_utente";
            $result = $conn->query($sql);
            $row_utente = $result->fetch_assoc();

            $dati["esito"] = true;
            $dati["commento"] = array(
                "length" => 1,
                "list" => array( 0 => array(
                    "testo" => $testo,
                    "codice" => $codice,
                    "data_creazione" => date("Y-m-d"),
                    "actual_user" => true,
                    "nome_utente" => $row_utente["nome"] ." " . $row_utente["cognome"]
                )));
        }
    }

    if ($action == "new-checkbox") {
        if (isset($_POST["testo"]) && isset($_POST["codice_lista"])) {
            $testo = $_POST["testo"];
            
            $codice_lista = $_POST["codice_lista"];

            $codice = genera_codice(16, $id_bacheca);
            $id_lista = get_elem_by_code("Lista", array("codice" => $codice_lista, "bacheca" => $codice_bacheca));

            $conn->query(create_sql("INSERT INTO Checkbox",
                array("testo", "is_check", "lista", "codice"),
                array($testo, "false", $id_lista, $codice)
            ));

            $dati["esito"] = true;
            $dati["checkbox"] = array(
                "length" => 1,
                "list" => array( 0 => array(
                    "testo" => $testo,
                    "codice" => $codice,
                    "is_check" => "false"
            )));
        }
    }

    if ($action == "new-etichetta") {
        if (isset($_POST["testo"]) && isset($_POST["codice_lista"]) && isset($_POST["red"]) && isset($_POST["green"]) && isset($_POST["blue"])) {
            $testo = $_POST["testo"];
            $colori = array("red" => $_POST["red"], "green" => $_POST["green"], "blue" => $_POST["blue"]);

            $id_colore = create_new_colore($colori);
            
            $codice_lista = $_POST["codice_lista"];

            $codice = genera_codice(16, $id_bacheca);
            $id_lista = get_elem_by_code("Lista", array("codice" => $codice_lista, "bacheca" => $codice_bacheca));

            $conn->query(create_sql("INSERT INTO Etichetta",
                array("testo", "colore", "lista", "codice"),
                array($testo, $id_colore, $id_lista, $codice)
            ));

            $dati["esito"] = true;
            $dati["etichetta"] = array(
                "length" => 1,
                "list" => array( 0 => array(
                    "testo" => $testo,
                    "codice" => $codice,
                    "red" => $colori["red"],
                    "green" => $colori["green"],
                    "blue" => $colori["blue"]
            )));
        }
    }

    if ($action == "new-scadenza") {
        if (isset($_POST["data"]) && isset($_POST["codice_lista"])) {
            $data = $_POST["data"];            
            $codice_lista = $_POST["codice_lista"];

            $codice = genera_codice(16, $id_bacheca);
            $id_lista = get_elem_by_code("Lista", array("codice" => $codice_lista, "bacheca" => $codice_bacheca));

            $conn->query(create_sql("INSERT INTO Scadenza",
                array("data", "lista", "codice"),
                array($data, $id_lista, $codice)
            ));

            $dati["esito"] = true;
            $dati["scadenza"] = array(
                "length" => 1,
                "list" => array( 0 => array(
                    "data" => $data,
                    "codice" => $codice,
                    "valida" => valida_data($data)
            )));
        }
    }

    if ($action == "change-checkbox") {
        if (isset($_POST["is_check"]) && isset($_POST["codice"])) {
            $codice_checkbox = $_POST["codice"];
            $is_check = $_POST["is_check"];

            $id_checkbox = get_elem_by_code("Checkbox", array("codice" => $codice_checkbox, "bacheca" => $codice_bacheca));

            $conn->query("UPDATE Checkbox SET is_check='$is_check' WHERE ID=$id_checkbox");
        }
    }

    if ($action == "change-scadenza") {
        if (isset($_POST["codice_lista"]) && isset($_POST["data"])) {
            $codice_lista = $_POST["codice_lista"];
            $data = $_POST["data"];

            $id_lista = get_elem_by_code("Lista", array("codice" => $codice_lista, "bacheca" => $codice_bacheca));

            $conn->query("UPDATE Scadenza SET data='$data' WHERE lista=$id_lista");
            $codice = $conn->query("SELECT codice FROM  Scadenza WHERE lista=$id_lista")->fetch_assoc()["codice"];

            $dati["esito"] = true;
            $dati["scadenza"] = array(
                "length" => 1,
                "list" => array( 0 => array(
                    "data" => $data,
                    "codice" => $codice,
                    "valida" => valida_data($data)
            )));
        }
    }

    if ($action == "delete-etichetta" || $action == "delete-commento" || $action == "delete-checkbox" || $action == "delete-scadenza") {
        if ($action == "delete-commento") { $nome_codice = "codice_commento"; $tabella = "Commento"; }
        if ($action == "delete-etichetta") { $nome_codice = "codice_etichetta"; $tabella = "Etichetta"; }
        if ($action == "delete-checkbox") { $nome_codice = "codice_checkbox"; $tabella = "Checkbox"; }
        if ($action == "delete-scadenza") { $nome_codice = "codice_scadenza"; $tabella = "Scadenza"; }

        if (isset($_POST[$nome_codice])) {
            $codice = $_POST[$nome_codice];
            
            // cancello da codici
            $sql = "DELETE FROM Codici WHERE codice='$codice' AND bacheca=$id_bacheca;";
            $result = $conn->query($sql);

            if (!$result) {
                echo "autorizzazione negata";
                $conn->close();
                exit();
            }

            // cancello lista
            $sql = "DELETE FROM $tabella WHERE codice='$codice';";
            $result = $conn->query($sql);

            $conn->close();
            exit();
        }
    }

    if ($action == "delete-membro") {
        if (isset($_POST["codice"])) {
            $codice = $_POST["codice"];

            // cancello codice
            $sql = "DELETE FROM Codici WHERE codice='$codice' AND bacheca=$id_bacheca;";
            $conn->query($sql);

            // cancello associazione
            $sql = "DELETE FROM Bacheca_assoc WHERE codice='$codice';";
            $conn->query($sql);

            $conn->close();
            exit();
        }
    }

    if ($action == "sposta-lista") {
        if (isset($_POST["from_codice"]) && isset($_POST["to_codice"]) && isset($_POST["codice_lista"])) {
            $codice_attivita_from = $_POST["from_codice"];
            $codice_attivita_to = $_POST["to_codice"];
            $codice_lista = $_POST["codice_lista"];

            $idx_attivita_from = get_elem_by_code("Attivita", array("bacheca" => $codice_bacheca, "codice" => $codice_attivita_from));
            $idx_attivita_to = get_elem_by_code("Attivita", array("bacheca" => $codice_bacheca, "codice" => $codice_attivita_to));
            $idx_lista = get_elem_by_code("Lista", array("bacheca" => $codice_bacheca, "codice" => $codice_lista));

            // sposto in lista default per ottenere i dati della singola lista
            $conn->query("UPDATE Lista SET attivita=0 WHERE ID=$idx_lista");
            $dati_lista = get_dati_liste(0)["list"][0];

            // sposto in posizione corretta
            $conn->query("UPDATE Lista SET attivita=$idx_attivita_to WHERE ID=$idx_lista");

            $dati["esito"] = true;
            $dati["lista"] = $dati_lista;
        }
    }

    if ($action == "get-msg-init") {
        $sql = "SELECT utente, testo, orario, codice FROM Chat WHERE bacheca=$id_bacheca ORDER BY orario ASC LIMIT 100;";
        $result = $conn->query($sql);
        
        $dati["esito"] = true;
        $dati["data"] = array();
        $dati["data"]["length"] = $result->num_rows;
        $dati["data"]["list"] = get_msg_from_query($result, $id_utente);
    }

    if ($action == "get-new-msg") {
        if (isset($_POST["codice_ultimo_messaggio"])) {
            $last_code = $_POST["codice_ultimo_messaggio"];
            if ($last_code !== "")
            {
                $sql = "SELECT utente, testo, orario, codice FROM Chat WHERE bacheca=$id_bacheca AND orario > (SELECT orario FROM Chat WHERE codice = '$last_code') ORDER BY orario ASC;";
                $result = $conn->query($sql);

                $dati["esito"] = true;
                $dati["data"] = array();
                $dati["data"]["length"] = $result->num_rows;
                $dati["data"]["list"] = get_msg_from_query($result, $id_utente);
            }
            else {
                $sql = "SELECT utente, testo, orario, codice FROM Chat WHERE bacheca=$id_bacheca ORDER BY orario ASC LIMIT 100;";
                $result = $conn->query($sql);

                $dati["esito"] = true;
                $dati["data"] = array();
                $dati["data"]["length"] = $result->num_rows;
                $dati["data"]["list"] = get_msg_from_query($result, $id_utente);
            }
        }
    }

    if ($action == "create-new-msg") {
        if (isset($_POST["testo"])) {
            $testo = $_POST["testo"];
            $codice = genera_codice(16, $id_bacheca);
            $conn->query(create_sql(
                "INSERT INTO Chat",
                array("testo", "codice", "utente", "bacheca"),
                array($testo, $codice, $id_utente, $id_bacheca)
            ));

            $dati["esito"] = true;
            $dati["data"] = array();
            $dati["data"]["length"] = 1;
            $dati["data"]["list"] = array();
            $dati["data"]["list"][0]["codice"] = $codice;
            $dati["data"]["list"][0]["nome_utente"] = get_nome_utente($id_utente);
        }
    }

    if ($action == "img-user-profile") {
        if (isset($_POST["codice_utente"])) {
            $conn = connection();
            $codice = $_POST["codice_utente"];

            $result = $conn->query("SELECT ID FROM Utenti WHERE codice='$codice';");
            $id_utente = $result->fetch_assoc()["ID"];
            $dati["esito"] = true;
            $dati["data"] = get_user_img_profilo($id_utente);
        }
    }

    echo json_encode($dati);
    $conn->close();
}
?>