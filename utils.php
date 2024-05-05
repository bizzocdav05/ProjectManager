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

    $id_utente = login_required();
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

function set_bacheca($codice_bacheca, $silent=false) {
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
        $conn->close();

        if ($silent) {
            return array(false, "bacheca_non_esiste");
        } else {
            echo "<h1>Questa bacheca non esiste</h1>";
            exit();
        }
    }

    if ($result->num_rows == 0) {
        $sql = "SELECT bacheca, privilegi FROM Bacheca_assoc WHERE other=$id_utente AND codice_bacheca='$codice_bacheca';";
        $result_assoc = $conn->query($sql);

        if ($result_assoc->num_rows > 0) {
            $data = $result_assoc->fetch_assoc();
            $id_bacheca = $data["bacheca"];
            $privilegi = $data["privilegi"];
        } else {
            $conn->close();

            if ($silent) {
                return array(false, "bacheca_non_accesso");
            } else {
                echo "<h1>Non puoi accede a questa bacheca</h1>";
                exit();
            }
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
            $conn->close();
            return $result->fetch_assoc()["ID"];
        }
    }}

    $conn->close();
    exit();
}

function valida_data($data) {
    $data_other = new DateTime($data);
    $data_odierna = new DateTime();

    if ($data_other <= $data_odierna) {
        return false;
    } else {
        return true;
    }
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
            $dati_lista["etichetta"] = $dati_etichetta;

            // scadenza
            $sql = "SELECT codice, Scadenza.data as data_ FROM Scadenza WHERE lista=" . $row_lista["ID"] . ";";
            $result_scadenza = $conn->query($sql);

            $dati_scadenza = array("list" => array());
            if (!$result_scadenza) {
                $dati_scadenza["length"] = 0;
            } else {
                $dati_scadenza["length"] = $result_scadenza->num_rows;
            }

            if ($result_scadenza->num_rows > 0) {
                while ($row_scadenza = $result_scadenza->fetch_assoc()) {
                    array_push($dati_scadenza["list"], array(
                        "codice" => $row_scadenza["codice"],
                        "data" => $row_scadenza["data_"],
                        "valida" => valida_data($row_scadenza["data_"])
                    ));
                }
            }
            $dati_lista["scadenza"] = $dati_scadenza;

            array_push($dati["list"], $dati_lista);
        }
    }

    $conn->close();
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

    $conn->close();
    return $data;
}

function get_nome_utente($id_utente = null, $id_console = null) {
    if ($id_utente != null)
        $sql = "SELECT nome, cognome FROM Utenti WHERE ID=$id_utente;";
    elseif ($id_console != null) {
        $sql = "SELECT nome, cognome FROM Utenti WHERE console=$id_console;";
    } else {
        exit();
    }

    $conn = connection();
    $result = $conn->query($sql)->fetch_assoc();

    return $result["nome"] . " " . $result["cognome"];
}

function get_membri_bacheca($id_bacheca, $id_utente) {
    $id_utente = get_utente();

    $conn = connection();
    $sql = "SELECT other, codice FROM Bacheca_assoc WHERE bacheca=$id_bacheca";
    $result = $conn->query($sql);

    $dati = array("length" => $result->num_rows, "list" => array());
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($dati["list"], array(
                "codice" => $row["codice"],
                "current" => ($row["other"] == $id_utente),
                "privilegi" => $row["privilegi"],
                "nome" => get_nome_utente($id_utente)
            ));
        }
    }

    $conn->close();
    return $dati;
}

function get_codice_invito($id_bacheca) {
    $conn = connection();
    $sql = "SELECT codice FROM Bacheca WHERE ID=$id_bacheca";
    $result = $conn->query($sql);

    $codice = $result->fetch_assoc()["codice"];
    $codice_invito = substr(hash("sha256", $codice), 0, 32);

    $conn->close();
    return $codice_invito;
}

function send_email($id_utente, $oggetto, $corpo) {
    $conn = connection();
    $destinatario = $conn->query("SELECT mail FROM Utenti WHERE ID=$id_utente")->fetch_assoc()["mail"];
    $mittente = "no-reply@torg.com";

    // Aggiungi intestazioni per specificare il mittente e altri dettagli
    $headers = "From: no-reply@torg.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Invia l'email
    echo $destinatario;
    mail($destinatario, $oggetto, $corpo, $headers);
}
$to = 'bizzocdav05@zanelli.edu.it'; // note the comma

// Subject
$subject = 'Birthday Reminders for August';

// Message
$message = '
<html>
<head>
  <title>Birthday Reminders for August</title>
</head>
<body>
  <p>Here are the birthdays upcoming in August!</p>
  <table>
    <tr>
      <th>Person</th><th>Day</th><th>Month</th><th>Year</th>
    </tr>
    <tr>
      <td>Johny</td><td>10th</td><td>August</td><td>1970</td>
    </tr>
    <tr>
      <td>Sally</td><td>17th</td><td>August</td><td>1973</td>
    </tr>
  </table>
</body>
</html>
';

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

// Additional headers
$headers[] = 'To: Mary <mary@example.com>, Kelly <kelly@example.com>';
$headers[] = 'From: Birthday Reminder <birthday@example.com>';
$headers[] = 'Cc: birthdayarchive@example.com';
$headers[] = 'Bcc: birthdaycheck@example.com';

// Mail it
// mail($to, $subject, $message, implode("\r\n", $headers));

function logout() {
    if(isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-7000000, '/');
    }
}

function get_user_img_profilo() {
    $id_utente = login_required();
    $conn = connection();

    $result = $conn->query("SELECT img_profilo FROM Utenti WHERE ID=$id_utente AND img_profilo IS NOT NULL;");
    if ($result->num_rows == 0) {
        $conn->close();
        return false;
    }
    
    $id_img = $result->fetch_assoc()["img_profilo"];
    $result = $conn->query("SELECT tipo, dati FROM Immagine WHERE ID=$id_img;")->fetch_assoc();
    
    $conn->close();
    return array( "tipo" => $result["tipo"], "dati" => base64_encode($result["dati"]) );

}

function get_msg_from_query($result, $id_utente) {
    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row["current"] = ($row["utente"] == $id_utente) ? true : false;
            $row["nome_utente"] = get_nome_utente($id_utente);
            unset($row["utente"]); // Rimuovi l'id dal messaggio
            $data[] = $row;
        }
    }

    return $data;
}

function get_bacheche_list() {
    $id_console = set_console();
    $data = array();
    $conn = connection();
    
    // Bacheche
    $sql = "SELECT ID, nome, codice FROM Bacheca WHERE console=$id_console;";
    $result_bacheca = $conn->query($sql);
    
       $data = array("length" => $result_bacheca->num_rows, "list" => array());
    if ($result_bacheca->num_rows > 0) {
        while($row_bacheca = $result_bacheca->fetch_assoc()) {
            array_push($data["list"], array( "nome" => $row_bacheca["nome"], "codice" => $row_bacheca["codice"]));
        }
    }

    $conn->close();
    return $data;
}
?>