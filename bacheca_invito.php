<?php
include "utils.php";
session_start();

login_required();
$id_console = set_console();
$id_utente = get_utente();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["sharing"])) {
    $codice_invito = $_GET["sharing"];

    $conn = connection();
    $sql = "SELECT codice, ID, console FROM Bacheca;";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (substr(hash("sha256", $row["codice"]), 0, 32) == $codice_invito) {
                //TODO: privilegi

                $temp = set_bacheca($row["codice"], true);
                var_dump($temp);
                if ($temp[0] != false) {
                    header("Location: bacheca.php?codice=" . urlencode($row["codice"]));
                    exit();
                } elseif ($temp[1] != "bacheca_non_esiste") {
                    // header("Location: bacheche.php");
                    // exit();
                }

                $codice = genera_codice(16, $row["ID"]);
                $conn->query(create_sql("INSERT INTO Bacheca_assoc",
                    array("other", "bacheca", "codice", "codice_bacheca"),
                    array($id_utente, $row["ID"], $codice, $row["codice"])
                ));

                header("Location: bacheca.php?codice=" . urlencode($row["codice"]));
                exit();
            }
        }
    }

    echo "<h1>Link invito non valido</h1>";

    $conn->close();
}
?>