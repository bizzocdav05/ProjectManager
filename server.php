<?php
// account
include "utils.php";
session_start();

function login($conn, $campi, $auth=false)
{
    $sql = "SELECT ID FROM Utenti WHERE mail='" . $campi['mail']. "' AND password='" . $campi["password"] . "';";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $id_utente = $result->fetch_assoc()["ID"];

        if ($auth) {
            $codice = $campi["auth_code"];
            if ($codice == "") {
                $codice = bin2hex(random_bytes(4));
                $_SESSION["auth_code"] = $codice;

                $mail_html = file_get_contents("mail_template/autentificazione_due_fattori.html");
                $mail_html = str_replace("{codice}", $codice, $mail_html);
        
                send_email($id_utente, "Codice Login", $mail_html);
                return -1;
            }
            else {
                if ($codice == $_SESSION["auth_code"]) {
                    unset($_SESSION["auth_code"]);
                } else {
                    return false;
                }
            }
        }
        
        return $id_utente;
    }

    unset($_SESSION["auth_code"]);
    return false;
}

function registrazione($conn, $campi)
{    
    if ($campi["c_password"] != $campi["password"]) {
        return false;
    }
    unset($campi["c_password"]);

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
    $sql = "INSERT INTO Console (utente) Values ($id_utente);";
    $result = $conn->query($sql);

    $sql = "SELECT ID From Console WHERE utente=$id_utente;";
    $result = $conn->query($sql);

    $id_console = $result->fetch_assoc()["ID"];

    $codice_utente = hash("sha256", $id_utente);
    // Aggiungo ad utente
    $sql = "UPDATE Utenti SET console=$id_console, codice='$codice_utente'  Where ID=$id_utente;";
    $result = $conn->query($sql);

    // invio email per conferma account
    $_SESSION["email_code"] = $codice_utente;

    $mail_html = file_get_contents("mail_template/benvenuto_utente.html");
    $mail_html = str_replace("{nome}", $campi["nome"] . " " . $campi["cognome"], $mail_html);

    send_email($id_utente, "Benvenuto", $mail_html);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]))
{
    $conn = connection();
    $action = $_POST["action"];

    if (isset($_POST["campi"])) {
        $campi = $_POST["campi"];

        if ($action == "new-user") {
            if (login($conn, $campi)) { echo "utente già esistenete"; }
            else { echo registrazione($conn, $campi); }
        }
        elseif ($action == "login") {
            $esito = login($conn, $campi, true);
            // $esito = login($conn, $campi);
            
            if ($esito > 0) {
                $_SESSION["id_utente"] = $esito;
                set_console();

                echo json_encode(true);
            } else {
                echo json_encode($esito);
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

                $mail_html = file_get_contents("mail_template/conferma_cambio_password.html");
                $mail_html = str_replace("{nome}", get_nome_utente($id_utente), $mail_html);

                send_email($id_utente, "Cambio Password", $mail_html);

                logout();
            }
        }
    }

    if ($action == "password-reset") {
        $id_utente = login_required();
        $password = bin2hex(random_bytes(8));
        
        $conn->query("UPDATE Utenti SET password='$password' WHERE ID=$id_utente;");
        $conn->query("DELETE FROM Codici WHERE codice='$password';");

        $mail_html = file_get_contents("mail_template/resetta_password.html");
        $mail_html = str_replace("{password}", $password, $mail_html);
        $mail_html = str_replace("{nome}", get_nome_utente($id_utente), $mail_html);

        send_email($id_utente, "Reset Password", $mail_html);

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

            $size = 100;
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
            $mail_html = file_get_contents("mail_template/benvenuto_utente.html");
            $mail_html = str_replace("{codice}", $codice_utente, $mail_html);

            send_email($id_utente, "Benvenuto su Torg", $mail_html);
        }
    }

    if ($action == "get-theme-color") {
        $id_utente = login_required();
        $result = $conn->query("SELECT tema FROM Utenti WHERE ID=$id_utente;");
        echo json_encode(array("colori" => get_theme_colors($result->fetch_assoc()["tema"])));
    }

    if ($action == "new-theme-color") {
        $id_utente = login_required();

        if (isset($_POST["new_color"])) {
            $nuovo = $_POST["new_color"];

            $conn->query("UPDATE Utenti SET tema='$nuovo' WHERE ID=$id_utente;");
            echo json_encode(array("colori" => get_theme_colors($nuovo)));
        }
    }

    $conn->close();
  
}
?>