<?php

include "utils.php";

function login($conn, $campi)
{
	$sql = "SELECT ID From Utenti Where mail='" . $campi['mail']. "' AND password='" . $campi["password"] . "';";
 	//echo $sql;
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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["campi"]) && isset($_POST["action"]))
{
	$conn = connection();

    $campi = $_POST["campi"];
    $action = $_POST["action"];
    
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

    $conn->close();
  
}
?>