<?php
include "utils.php";

$id_utente = login_required();
// $id_bacheca = set_bacheca();
$id_bacheca = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];
    $conn = connection();

    $data = array("esito" => false, "data" => array("length" => 0, "list" => array()));

    if ($action == "get-msg-init") {
        $sql = "SELECT utente, testo, orario, codice FROM Chat WHERE bacheca=$id_bacheca ORDER BY orario ASC LIMIT 100;";
        $result = $conn->query($sql);
        
        $data["esito"] = true;
        $data["data"]["length"] = $result->num_rows;
        $data["data"]["list"] = get_msg_from_query($result, $id_utente);
    }

    if ($action == "get-new-msg") {
        if (isset($_POST["codice_ultimo_messaggio"])) {
            $last_code = $_POST["codice_ultimo_messaggio"];
            if ($last_code !== "")
            {
                $sql = "SELECT utente, testo, orario FROM Chat WHERE bacheca=$id_bacheca AND orario > (SELECT orario FROM Chat WHERE codice = '$last_code') ORDER BY orario ASC;";
                $result = $conn->query($sql);

                $data["esito"] = true;
                $data["data"]["length"] = $result->num_rows;
                $data["data"]["list"] = get_msg_from_query($result, $id_utente);
            }
            else {
                $sql = "SELECT utente, testo, orario, codice FROM Chat WHERE bacheca=$id_bacheca ORDER BY orario ASC LIMIT 100;";
                $result = $conn->query($sql);

                $data["esito"] = true;
                $data["data"]["length"] = $result->num_rows;
                $data["data"]["list"] = get_msg_from_query($result, $id_utente);
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

            $data["esito"] = true;
            $data["data"]["length"] = 1;
            $data["data"]["list"] = array();
            $data["data"]["list"][0]["codice"] = $codice;
            $data["data"]["list"][0]["nome_utente"] = get_nome_utente($id_utente);
        }
    }

    echo json_encode($data);
}

?>