<?php
// account
include "utils.php";

function login($conn, $campi)
{
    $sql = "SELECT ID From Utenti Where mail='" . $campi['mail']. "' AND password='" . $campi["password"] . "';";
    echo $sql;
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
    // echo "loggato"; 
        while($row = $result->fetch_assoc()) {
        return $row["ID"];
        }
    }

    return false;
}

function registrazione($conn, $campi)
{    
    $sql = "INSERT INTO Utenti (";
    $sql_values = "";
    foreach($campi as $nome => $value) {
        $sql .= $nome . ", ";
        $sql_values .= "'" . $value . "'" . ", ";
    }

    $sql = rtrim($sql, ", ");
    $sql_values = rtrim($sql_values, ", ");
    $sql .= ") VALUES (" . $sql_values . ");";
    
    $result = $conn->query($sql);
    
    if (!$result) { return false; }
    
    $id_utente = login($conn, $campi);
    if ($id_utente) { echo "Registrazione effettuata"; }

    // Creo console
    $sql = "INSERT INTO Console(utente) Values ('" . $id_utente . "');";
    $result = $conn->query($sql);

    if ($result->num_rows >= 0) {
        $sql = "SELECT ID From Console Where utente='" . $id_utente . "';";
        $result = $conn->query($sql);
        if ($result->num_rows == 1) {
        while($row = $result->fetch_assoc()) {
            $id_console = $row["ID"];
        }
        }

        // Aggiungo ad utente
        $sql = "UPDATE Utenti SET console='" . $id_console . "'Where ID='" . $id_utente . "';";
        $result = $conn->query($sql);
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]))
{
    $conn = connection();
    $action = $_POST["action"];

    if (isset($_POST["campi"])) {
        $campi = $_POST["campi"];

        if ($action == "newuser") {
        if (login($conn, $campi)) { echo "utente già esistenete"; }
        else {
            registrazione($conn, $campi);
            }
            
        }
        elseif ($action == "login") {
        $esito = login($conn, $campi);
            
            if ($esito) {
                echo "loggato";
                session_start();
                $_SESSION["id_utente"] = $esito;

                set_console();
            }
        }
    }


    if ($action == "update-user-data") {
        $id_utente = login_required();
        if (isset($_POST["nome"]) && isset($_POST["cognome"]) && isset($_POST["email"])) {
            $nome = $_POST["nome"];
            $cognome = $_POST["cognome"];
            $email = $_POST["email"];

            $sql = "UPDATE Utenti SET nome='$nome', cognome='$cognome', mail='$email' WHERE ID=$id_utente;";
            echo $sql;
            $conn->query($sql);
        }
    }

    if ($action == "change-password") {
        $id_utente = login_required();

        if (isset($_POST["password_vecchia"]) && isset($_POST["password_1"]) && isset($_POST["password_1"])) {
            $old_ps = $_POST["password_vecchia"];
            $ps1 = $_POST["password_1"];
            $ps2 = $_POST["password_2"];

            if ($ps1 == $ps2) {
                $sql = "SELECT password FROM Utenti WHERE ID=$id_utente;";
                $result = $conn->query($sql)->fetch_assoc();

                if ($result["password"] == $old_ps) {
                    $sql = "UPDATE Utenti SET password='$ps_1' WHERE ID=$id_utente;";
                    $conn->query($sql);
                }

                logout();
            }
        }
    }

    if ($action == "reset-password") {
        $id_utente = login_required();
        $password = genera_codice(16, 0);
        
        $conn->query("UPDATE Utenti SET password='$password' WHERE ID=$id_utente;");
        $conn->query("DELETE FROM Codici WHERE codice='$password';");
        
        send_email($idutente, "Reset Password", "<!DOCTYPE html><html lang='it'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Resetta Password</title></head><body><p>Nuova password: $password</p></body></html>");

        logout();
    }

    $conn->close();
  
}
?>