<?php
include "utils.php";

session_start();
login_required();
$id_console = set_console();

date_default_timezone_set('CET');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    $conn = connection();

    if ($action == "new-bacheca" && isset($_POST["nome"])) {
        $codice = genera_codice(16, -1);
    
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
    $id_bacheca = $temp[0];
    $privilegi = $temp[1];

    $dati = array("esito" => false);
    // nuova attività
    if ($action == "new-attivita") {
        $dati["attivita"] = array();
        if (isset($_POST["titolo"])) {
            $data = date("Y-m-d");
            $codice_attivita = genera_codice(16, $id_bacheca);
            $sql = create_sql("INSERT INTO Attivita",
                array("titolo, data_creazione, data_ultima_modifica", "bacheca", "codice"),
                array($_POST["titolo"], $data, $data, $id_bacheca, $codice_attivita));
            $result = $conn->query($sql);
            $dati["attivita"]["info"]["titolo"] = $_POST["titolo"];
            $dati["attivita"]["info"]["dati-ultima-modifica"] = $data;
            $dati["attivita"]["info"]["data-creazione"] = $data;
            $dati["attivita"]["info"]["codice"] = $codice_attivita;

            $dati["attivita"]["lista"] = array("length" => 0, "list" => array());
        }
    }

    if ($action == "new-lista") {
        if (isset($_POST["codice_bacheca"]) && isset($_POST["codice_attivita"]) && isset($_POST["nome"]) && isset($_POST["descrizione"]))
        {
            $codice_bacheca = $_POST["codice_bacheca"];
            $codice_attivita = $_POST["codice_attivita"];

            $codici = array("bacheca" => $codice_bacheca, "attivita" => $codice_attivita);

            $nome = $_POST["nome"];
            $descrizione = $_POST["descrizione"];
            
            // cerco ID attivita (autorizzato)
            $id_attivita = get_elem_by_code("attivita", $codici);

            $codice = genera_codice(16, $id_bacheca);

            $conn->query(create_sql(
                "INSERT INTO Lista",
                array("attivita", "nome", "descrizione", "data_creazione", "data_ultima_modifica", "codice"),
                array($id_attivita, $nome, $descrizione, date("Y-m-d"), date("Y-m-d"), $codice)
            ));

            $dati["lista"] = array(
                "nome" => $nome,
                "descrizione" => $descrizione,
                "data_creazione" => date("Y-m-d"),
                "data_ultima_modifica" => date("Y-m-d"),
                "codice" => $codice,
                "commento" => array("length" => 0, "list" => array()),
                "etichetta" => array("length" => 0, "list" => array()),
                "checkbox" => array("length" => 0, "list" => array()),
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
            $codice_lista = $_POST["codice_attivita"];

            // cancello da codici
            $sql = "DELETE FROM Codici WHERE codice='$codice_lista' AND bacheca=$id_bacheca;";
            $result = $conn->query($sql);

            if (!$result) {
                echo "autorizzazione negata";
                $conn->close();
                exit();
            }

            // cancello lista
            $sql = "DELETE FROM Attivita WHERE codice='$codice_lista';";
            $result = $conn->query($sql);

            $conn->close();
            exit();
        }
    }

    $dati["esito"] = true;
    echo json_encode($dati);
    $conn->close();
}
?>