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

function genera_codice($length = 16) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $code = '';
    $max = strlen($characters) - 1;
    
    // Genera il codice casuale
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[mt_rand(0, $max)];
    }
    
    if (connection()->query("SELECT codice FROM Codici WHERE codice=$code")->num_rows > 0) {  // codice già esistente
        return genera_codice($length);
    }

    connection()->query(create_sql("INSERT INTO Codici", array("codice"), array($code)));
    return $code;
}

function login_required() {
    if (!isset($_SESSION["id_utente"])) {
        header("Location: login.html");
        exit();
    }
}

function set_bacheca($codice_bacheca) {
    $conn = connection();
    $id_console = set_console();
    $id_utente = $_SESSION["id_utente"];
    $privilegi = 0;

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

    return array($id_bacheca, $privilegi);
}

?>