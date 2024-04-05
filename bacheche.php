<?php
    include "utils.php";

    session_start();
    login_required();
    set_console();

    if (isset($_SESSION["id_utente"])) {
        $data = array();
        $conn = connection();

        $id_utente = $_SESSION["id_utente"];
        $id_console = $_SESSION["id_console"];
        
        // Bacheche
        $sql = "SELECT ID, nome, codice FROM Bacheca WHERE console=$id_console;";
        $result_bacheca = $conn->query($sql);
        
       	$data = array("length" => $result_bacheca->num_rows, "list" => array());
        if ($result_bacheca->num_rows > 0) {
            while($row_bacheca = $result_bacheca->fetch_assoc()) {
                array_push($data["list"], array( "nome" => $row_bacheca["nome"], "codice" => $row_bacheca["codice"]));
            }
        }
    }

    $conn->close();
    ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <title>Le tue Bacheche</title>

    <style>
        #container {
            display: flex;
            flex-direction: row;
            width: 50%;
            justify-content: space-evenly;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        div.bacheca {
            width: 200px;
            height: 200px;
            background-color: gray;

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        div.bacheca:hover {
            cursor: pointer;
        }

        p.bacheca-nome {
            font-size: 40px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h3>Loggato</h3>
	<a href="logout.php">Logout</a>

    <div id="bacheca-prototipo" class="bacheca" style="display: none">
        <p class="bacheca-nome"><span></span></p>
    </div>

    <div id="container">
    </div>

    <form id="form-nuova-bacheca" method="post">
        <label for="">Nome: </label>
        <input type="text" name="nome" id="">

        <input type="submit" value="Crea">
    </form>

    <script>
        // Passaggio dati
        let dati = <?php echo json_encode($data); ?>;

        function show_bacheca(dati) {
            let elem = $("#bacheca-prototipo").clone(true);
            elem.attr("id", dati.codice);

            elem.find("p.bacheca-nome > span").text(dati.nome.toUpperCase());
            elem.show();
            return elem;
        }

        for (let i = 0; i < dati.length; i++)
            show_bacheca(dati.list[i]).appendTo("#container");

        $("#form-nuova-bacheca").submit(function (e) {
            e.preventDefault();

            let array = $(this).serializeArray();
            $(this).get(0).reset();
            
            let data = {};
            array.forEach((elem) => {
                data[elem["name"]] = elem["value"];
            });

            $.ajax({
                url: "attivita.php",
                type: "POST",
                data: {
                    "action": "new-bacheca",
                    "nome": data["nome"]
                },
                crossDomain: true,

                success: function (result) {
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        show_bacheca(result.bacheca);
                    }
                    popup.close();
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });

        $("#container > div.bacheca").click(function (e) {
            location.href = "bacheca.php?codice=" +encodeURIComponent($(this).attr("id"));
        });

        popup.add($("form-nuova-bacheca"));
    </script>
</body>
</html>