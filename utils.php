<?php

function connection() {
    $servername = "localhost";
    $username = "torg";
    $password = "";
    $database = "my_torg";

    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function set_console() {
    session_start();

    if (isset($_SESSION["id_console"])) {
        return $_SESSION["id_console"];
    }

    $id_utente = $_SESSION["id_utente"];
    $conn = connection();

    $sql = "SELECT ID FROM Console WHERE utente=$id_utente";
    $result_console = $conn->query($sql);

    $id_console = $result_console->fetch_assoc()["ID"];
    $_SESSION["id_console"] = $id_console;
    
    $conn->close();

    return $id_console;
}

function create_sql($start, $names, $values) {
    $sql = $start . " ";

    $sql .= "(";
    for ($i = 0; $i < count($names); $i++) {
        $sql .= $names[$i];

        if ($i != count($names)-1) $sql .= ",";
    }
    $sql .= ") ";

    $sql .= "Values (";
    for ($i = 0; $i < count($values); $i++) {
        $sql .= "'" . $values[$i] . "'";

        if ($i != count($values)-1) $sql .= ",";
    }
    $sql .= ");";

    return $sql;
}

function genera_codice($length = 16, $id_bacheca) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $code = '';
    $max = strlen($characters) - 1;
    
    // Genera il codice casuale
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[mt_rand(0, $max)];
    }

    $conn = connection();
    
    if ($conn->query("SELECT codice FROM Codici WHERE codice=$code")->num_rows > 0) {  // codice già esistente
        return genera_codice($length, $id_bacheca);
    }

    // salvo il codice
    $conn->query(create_sql("INSERT INTO Codici", array("codice", "bacheca"), array($code, $id_bacheca)));
    $conn->close();

    return $code;
}

function get_utente() {
    session_start();

    if (isset($_SESSION["id_utente"])) {
        return $_SESSION["id_utente"];
    }
    return false;
}

function login_required() {
    if (!get_utente()) {
        header("Location: login.html");
        exit();
    }
    return get_utente();
}

function set_bacheca($codice_bacheca) {
    // controlla se l'utente può accedere alla bacheca

    session_start();
    $conn = connection();
    $id_console = set_console();
    $id_utente = $_SESSION["id_utente"];

    $privilegi = 0;
    $id_bacheca = -1;

    $sql = "SELECT ID FROM Bacheca Where codice='$codice_bacheca' AND console=$id_console;";
    $result = $conn->query($sql);

    if (!$result) {
        echo "<h1>Questa bacheca non esiste</h1>";
        exit();
    }

    if ($result->num_rows == 0) {
        $sql = "SELECT bacheca, privilegi FROM Bacheca_assoc WHERE other=$id_utente AND codice='$codice_bacheca';";
        $result_assoc = $conn->query($sql);

        if ($result_assoc->num_rows > 0) {
            $data = $result_assoc->fetch_assoc();
            $id_bacheca = $data["bacheca"];
            $privilegi = $data["privilegi"];
        } else {
            echo "<h1>Non puoi accede a questa bacheca</h1>";
            exit();
        }
    }

    if ($result->num_rows > 0) {
        $id_bacheca = $result->fetch_assoc()["ID"];
    }
    $conn->close();

    return array($id_bacheca, $privilegi);
}

function get_elem_by_code($tipo, $codici) {
    // permette di ottenere l'indice dell'elemento, se l'utente ha i permessi necessari
    // "codice" è il codice da controllare, passare anche quello della "bacheca" per controllo

    $conn = connection();
    
    $temp = set_bacheca($codici["bacheca"]);
    $id_bacheca = $temp[0];
    $privilegi = $temp[1];

    $sql = "SELECT bacheca FROM Codici WHERE codice='" . $codici["codice"] . "'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
    if ($result->fetch_assoc()["bacheca"] == $id_bacheca) {
        // controllo privilegi
        $sql = "SELECT ID FROM $tipo WHERE codice='" . $codici["codice"] . "'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            return $result->fetch_assoc()["ID"];
        }
    }}

    exit();
}

function get_dati_liste($id_attivita) {
    $conn = connection();
    $id_utente = get_utente();

    // cerco liste
    $sql = "SELECT ID, nome, descrizione, data_creazione, data_ultima_modifica, codice FROM Lista WHERE attivita='" . $id_attivita . "';";
    $result_lista = $conn->query($sql);

    if (!$result_lista) {
        $dati["length"] = 0;
    } else {
        $dati["length"] = $result_lista->num_rows;
    }

    $dati["list"] = array();

    if ($result_lista->num_rows > 0) {
        while ($row_lista = $result_lista->fetch_assoc()) {
            $dati_lista = array(
                "data_creazione" => $row_lista["data_creazione"],
                "data_ultima_modifica" => $row_lista["data_ultima_modifica"],
                "nome" => $row_lista["nome"],
                "descrizione" => $row_lista["descrizione"],
                "codice" => $row_lista["codice"]
            );

            // commenti
            $sql = "SELECT c.codice as codice, c.testo as testo, c.data_creazione as data_creazione, c.user as id_user, u.nome as nome, u.cognome as cognome FROM Commento as c, Utenti as u WHERE c.lista=" . $row_lista["ID"] . " AND c.user = u.ID;";
            $result_commento = $conn->query($sql);

            $dati_commento = array("list" => array());
            if (!$result_commento) {
                $dati_commento["length"] = 0;
            } else {
                $dati_commento["length"] = $result_commento->num_rows;
            }

            if ($result_commento->num_rows > 0) {
                while ($row_commento = $result_commento->fetch_assoc()) {
                    array_push($dati_commento["list"], array(
                        "codice" => $row_commento["codice"],
                        "testo" => $row_commento["testo"],
                        "data_creazione" => $row_commento["data_creazione"],
                        "actual_user" => ($row_commento["id_user"] == $id_utente),
                        "nome_utente" => $row_commento["nome"] . " " . $row_commento["cognome"]
                    ));
                }
            }
            
            $dati_lista["commento"] = $dati_commento;

            // checkbox
            $sql = "SELECT codice, testo, is_check FROM Checkbox WHERE lista=" . $row_lista["ID"] . ";";
            $result_checkbox = $conn->query($sql);

            $dati_checkbox = array("list" => array());
            if (!$result_checkbox) {
                $dati_checkbox["length"] = 0;
            } else {
                $dati_checkbox["length"] = $result_checkbox->num_rows;
            }
            if ($result_checkbox->num_rows > 0) {
                while ($row_checkbox = $result_checkbox->fetch_assoc()) {
                    array_push($dati_checkbox["list"], array(
                        "codice" => $row_checkbox["codice"],
                        "testo" => $row_checkbox["testo"],
                        "is_check" => $row_checkbox["is_check"]
                    ));
                }
            }
            
            $dati_lista["checkbox"] = $dati_checkbox;

            // etichetta
            $sql = "SELECT e.codice as 'codice', e.testo as 'testo', c.blue as 'blue', c.red as 'red', c.green as 'green' FROM Etichetta as e, Colore as c WHERE e.lista=" . $row_lista["ID"] . " AND c.ID = e.colore;";
            $result_etichetta = $conn->query($sql);

            $dati_etichetta = array("list" => array());
            if (!$result_etichetta) {
                $dati_etichetta["length"] = 0;
            } else {
                $dati_etichetta["length"] = $result_etichetta->num_rows;
            }

            if ($result_etichetta->num_rows > 0) {
                while ($row_etichetta = $result_etichetta->fetch_assoc()) {
                    array_push($dati_etichetta["list"], array(
                        "codice" => $row_etichetta["codice"],
                        "testo" => $row_etichetta["testo"],
                        "blue" => $row_etichetta["blue"],
                        "red" => $row_etichetta["red"],
                        "green" => $row_etichetta["green"],
                    ));
                    // var_dump($row_etichetta["codice"]);
                }
            }
            // var_dump($dati_etichetta);
            $dati_lista["etichetta"] = $dati_etichetta;

            array_push($dati["list"], $dati_lista);
        }
    }

    return $dati;
}

function get_dati_attivita($id_bacheca) {
    $conn = connection();

    $sql = "SELECT ID, data_creazione, data_ultima_modifica, titolo, codice FROM Attivita WHERE bacheca=$id_bacheca;";
    $result = $conn->query($sql);

    // echo "numero attività mostrate: $result->num_rows";
    $data = array("length" => $result->num_rows, "list" => array());

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dati = array("info" => array(
                "data_creazione" => $row["data_creazione"],
                "data_ultima_modifica" => $row["data_ultima_modifica"],
                "titolo" => $row["titolo"],
                "codice" => $row["codice"])
            );

            $dati_liste = get_dati_liste($row["ID"]);
            $dati["lista"] = $dati_liste;

            // lista -> checkbox, etichette, commenti, ...
            
            // inserisco le informazioni
            array_push($data["list"], $dati);
        }
    }

    return $data;
}

?>