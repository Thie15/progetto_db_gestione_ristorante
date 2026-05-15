<?php
    session_start();
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smartristo</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <?php
            include("inc/header.php");
        ?>
        <h1>Aggiungi un nuovo dipendente</h1>
        <form action="" method="post">
            <label for="nome">Nome</label>
            <input type="text" name="nome">
            <label for="cognome">Cognome</label>
            <input type="text" name="cognome">
            <label for="turno">Turno</label>
            <input type="text" name="turno">
            <label for="stipendio">Stipendio</label>
            <input type="text" name="stipendio">
            <label for="comune">Comune</label>
            <input type="text" name="comune">
            <label for="via">Via</label>
            <input type="text" name="via">
            <label for="civico">Civico</label>
            <input type="text" name="civico">
            <label for="cap">CAP</label>
            <input type="text" name="cap">
            <button type="submit">Aggiungi</button>
        </form>
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>