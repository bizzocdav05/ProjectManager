<?php

function connect_to_server() {
    $servername = "localhost";
    $username = "torg";
    $password = "";

    // Create connection
    $conn = new mysqli($servername, $username, $password);

    // Check connection
    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }

    session_start();

    return $conn;
}

function accedi_user($server) {
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $mail = htmlspecialchars($_POST["mail"]);
        $password = htmlspecialchars($_POST["password"]);

        if (isset($mail) && isset($password))
        {
            $password_hash = password_hash($password);
            $sql = "SELECT * FROM Utenti WHERE Utenti.Email = '$mail' AND Utenti.password = '$password_hash';"
            $result = $server->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $_SESSION["ID"] = $row["ID"];
                    $_SESSION["nome"] = $row["nome"];
                    $_SESSION["cognome"] = $row["cognome"];
                    $_SESSION["console"] = $_row["console"];
                    echo json_encode(array(
                        "status" => "logged"
                    ));
                }
            }
        }
    }
}

function registrazione_user($server)
{
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $nome = htmlspecialchars($_POST["nome"]);
        $cognome = htmlspecialchars($_POST["cognome"]);
        $mail = htmlspecialchars($_POST["email"]);
        $anno_nascita = htmlspecialchars($_POST["anno_nascita"]);
        $password = htmlspecialchars($_POST["password"]);
        $password_2 = htmlspecialchars($_POST["c_password"]);

        if (isset($nome) && isset($cognome) && isset($mail) && isset($anno_nascita) && isset($password) && isset($password_2))
        {
            if ($password == $password_2)
            {
                $password_hash = password_hash($password);
                $sql = "SELECT * FROM Utenti WHERE Utenti.Email = '$mail' AND Utenti.password = '$password_hash';"
                $result = $server->query($sql);

                if ($result->num_rows != 0) {
                    echo json_encode(array(
                        "status" => "duplicate"
                    ));
                }
                else {
                    $sql = "INSERT INTO Console"
                    $sql = "INSERT INTO Utenti (nome, cognome, email, anno_nascita, password) VALUES ($nome, $cognome, $mail, $anno_nascita, $password_hash);"
                }
            }
        }
    }
}

?>