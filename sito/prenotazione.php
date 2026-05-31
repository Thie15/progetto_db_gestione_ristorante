<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smartristo - Prenotazione</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/prenotazione.css">
    </head>
    <body>
        <?php include("inc/header.php"); ?>
        <h1 class="titoloPagina">Prenota un tavolo</h1>
        <?php
            if(isset($_SESSION["prenotazione_errore"])){
                echo "<p class='msg-errore'>".$_SESSION["prenotazione_errore"]."</p>";
                unset($_SESSION["prenotazione_errore"]);
            }
            if(isset($_SESSION["prenotazione_ok"])){
                $id = $_SESSION["prenotazione_ok"];
                unset($_SESSION["prenotazione_ok"]);
                echo "<div class='box-successo'>";
                echo "  <p class='successo-titolo'>Prenotazione effettuata!</p>";
                echo "  <p class='successo-sub'>Il tuo ID prenotazione è:</p>";
                echo "  <p class='successo-id'>$id</p>";
                echo "</div>";
            }
        ?>
        <form class="form-prenotazione" method="post" action="checkPrenotazione.php">
            <div class="campo">
                <label for="ora">Ora</label>
                <input type="time" name="ora" id="ora" required>
            </div>
            <div class="campo">
                <label for="data">Data</label>
                <input type="date" name="data" id="data" required>
            </div>
            <div class="campo">
                <label for="persone">Numero di persone</label>
                <input type="number" name="persone" id="persone" min="1" max="20" required>
            </div>
            <div class="campo">
                <label for="pagamento">Metodo di pagamento</label>
                <select name="pagamento" id="pagamento">
                    <option value="Contanti">Contanti</option>
                    <option value="Carta">Carta</option>
                    <option value="PayPal">PayPal</option>
                    <option value="Satispay">Satispay</option>
                    <option value="Bonifico">Bonifico</option>
                </select>
            </div>
            <div class="campo campo-full">
                <button type="submit">Prenota</button>
            </div>
        </form>
    </body>
</html>