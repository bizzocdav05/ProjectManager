<?php
include "utils.php";

$id_utente = login_required();
// $id_bacheca = set_bacheca();
$id_bacheca = 0;

$conn = connection();
// $sql = "SELECT utente, testo, orario, codice FROM Chat WHERE bacheca=$id_bacheca ORDER BY orario ASC LIMIT 100;";
// $result = $conn->query($sql);

$data = array();
// $data["length"] = $result->num_rows;
// $data["list"] = get_msg_from_query($result, $id_utente);

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Chat</title>

    <!--font dei titoli-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Concert+One&display=swap" rel="stylesheet">

    <style>
        p.text-format {
            background-color: rgb(224, 171, 35, 0);
            border-width: 0px;
            font-family: "Concert One", sans-serif;
            color: black;
            margin: 0;
        }

        #chat {
            width: 50vw;
            height: 50vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid black;
            padding: 20px;
        }

        #chat > div {
            width: 100%;
        }

        #chat-content {
            height: 90%;
            overflow-y: auto;
        }

        #chat-new-msg {
            height: 10%;
        }

        div.messaggio-chat {
            background-color: gray;
            margin-top: 10%;
        }

        div.messaggio-chat {
            width: 50%;
            padding: 5px;
        }

        div.msg-proprio {
            margin-right: 0;
            margin-left: auto;
        }

        div.msg-other {
            margin-left: 0;
            margin-right: auto;
        }

        #svg-send-msg {
            width: 20px;
            height: 20px;
        }
    
    </style>
</head>
<body>
    <div id="chat">
        <div id="chat-content">

        </div>

        <div id="chat-new-msg">
            <input type="text" name="msg" id="inp-chat-msg">
            <svg id="svg-send-msg" viewBox="0 -0.5 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"/><g stroke-linecap="round" stroke-linejoin="round"/><path clip-rule="evenodd" d="M18.455 9.883 7.063 4.143a1.048 1.048 0 0 0-1.563.733.8.8 0 0 0 .08.326l2.169 5.24c.109.348.168.71.176 1.074a4 4 0 0 1-.176 1.074L5.58 17.83a.8.8 0 0 0-.08.326 1.048 1.048 0 0 0 1.562.732l11.393-5.74a1.8 1.8 0 0 0 0-3.265" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
    </div>

    <div id="messaggio-prototipo" class="messaggio-chat" style="display: none">
        <p class="text-format mittente"></p>
        <p class="text-format testo"></p>
        <p class="text-format orario"></p>
    </div>

    <script>
        function get_new_msg() {
            console.log({"action": "get-new-msg",
                    "codice_ultimo_messaggio": codLastMsg});
            $.ajax({
                url: "chat.php",
                type: "POST",
                data: {
                    "action": "get-new-msg",
                    "codice_ultimo_messaggio": codLastMsg
                },
                success: function (result) {
                    result = JSON.parse(result);
                    console.log(result);
                    show_msg(result.data);
                },
                error: function (result) {
                    console.log(result);
                }
            });
        }

        function show_msg(dati) {
            for (let i = 0; i < dati.length; i++) {
                let msg = dati.list[i];
                let elem = $("#messaggio-prototipo").clone(true);
                let target = $("#chat-content");
                elem.attr("id", msg.codice);

                elem.find("p.mittente").text(msg.nome_utente);
                elem.find("p.testo").text(msg.testo);
                elem.find("p.orario").text(msg.orario);

                if (msg.current) elem.addClass("msg-proprio");
                else elem.addClass("msg-other");
                
                elem.show();
                target.append(elem);

                codLastMsg = msg.codice;
                target.scrollTop(target.prop("scrollHeight"));
            }
        }

        function stopAggiornamento() {
            clearInterval(aggiornamento_messaggi);
        }

        const CODICE_BACHECA = 0;
        let dati = <?php echo json_encode($data); ?>;
        // let codLastMsg = null;
        codLastMsg = null;
        let aggiornamento_messaggi = setInterval(() => get_new_msg(), 1000);

        $("#svg-send-msg").click(function (e) {
            let target = $(e.currentTarget);
            let testo = target.siblings("input[name='msg']");

            if (!testo.val()) return;

            $.ajax({
                url: "chat.php",
                type: "POST",
                data: {
                    "action": "create-new-msg",
                    "codice_bacheca": CODICE_BACHECA,
                    "testo": testo.val()
                },
                crossDomain: true,

                success: function (result) {
                    result =JSON.parse(result);
                    console.log(result);
                    if (result.esito == true) {
                        get_new_msg(false);
                    }

                    testo.val("");
                },

                error: function (err) {
                    console.log(err);
                }
            });
        });
    </script>
</body>
</html>