<?php
include "utils.php";

session_start();
login_required();
$id_console = set_console();

date_default_timezone_set('CET');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    $conn = connection();

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
            $codice_attivita = genera_codice(16);
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
            if (!isset($_POST["ha_scandenza"])) {
                $ha_scadenza = 0;
            }
            $codice_bacheca = $_POST["codice_bacheca"];
            $codice_attivita = $_POST["codice_attivita"];

            $codici = array("bacheca" => $codice_bacheca, "attivita" => $codice_attivita);

            $nome = $_POST["nome"];
            $descrizione = $_POST["descrizione"];
            
            // cerco ID attivita (autorizzato)
            $id_attivita = get_elem_by_code("attivita", $codici);

            $codice = genera_codice(16);

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
                "codice" => $codice
            );
        }
    }

    $dati["esito"] = true;
    echo json_encode($dati);
    $conn->close();
}
?>