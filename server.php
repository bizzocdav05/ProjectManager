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

    if ($action == "password-reset") {
        $id_utente = login_required();
        $password = genera_codice(16, 0);
        
        echo $password;
        $conn->query("UPDATE Utenti SET password='$password' WHERE ID=$id_utente;");
        $conn->query("DELETE FROM Codici WHERE codice='$password';");

        echo "UPDATE Utenti SET password='$password' WHERE ID=$id_utente;";
        echo "DELETE FROM Codici WHERE codice='$password';";
        
        send_email($id_utente, "Reset Password", "<html lang='it'><head><title>Resetta Password</title></head><body><p>Nuova password: $password</p></body></html>");

        logout();
    }

    if ($action == "new-profile-image") {
        $id_utente = login_required();
        if(isset($_FILES["file_immagine"])) {
            $nome_file = "immagine profilo $id_utente";
            $tipo_file = $_FILES["file_immagine"]["type"];
            $dati_file = file_get_contents($_FILES["file_immagine"]["tmp_name"]);
        
            // Comprimo l'immagine            
            $immagine = imagecreatefromstring($dati_file);
            $img_x = imagesx($immagine);
            $img_y = imagesy($immagine);

            $size = 300;
            $new_img = imagecreatetruecolor($size, $size);
            imagecopyresampled($new_img, $immagine, 0, 0, 0, 0, $size, $size, $img_x, $img_y);

            ob_start();
            imagejpeg($new_img, NULL, 75);
            $dati_file = ob_get_clean();

            imagedestroy($immagine);
            imagedestroy($new_img);

            // Prepara la query per l'inserimento dei dati nel database
            $stmt = $conn->prepare("INSERT INTO Immagine (nome, tipo, dati) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome_file, $tipo_file, $dati_file);
        
            // Esegue la query
            $stmt->execute();
        
            // Chiudi la connessione al database
            $stmt->close();

            $id_immagine = $conn->insert_id;

            $result = $conn->query("SELECT img_profilo FROM Utenti WHERE ID=$id_utente AND img_profilo IS NOT NULL");
            if ($result->num_rows == 1) {
                $old_img = $result->fetch_assoc()["img_profilo"];
                $conn->query("DELETE FROM Immagine WHERE ID=$old_img;");
            }

            $conn->query("UPDATE Utenti SET img_profilo=$id_immagine WHERE ID=$id_utente;");

            echo json_encode(get_user_img_profilo());
        }
    }

    $conn->close();
  
}
?>