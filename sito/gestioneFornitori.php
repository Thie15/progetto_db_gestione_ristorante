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
        <h1>Aggiungi un nuovo fornitore</h1>
        <form action="" method="post">
            <label for="piva">PIVA</label>
            <input type="text" name="piva">
            <label for="nome">Nome</label>
            <input type="text" nome="nome">
            <label for="comune">Comune</label>
            <input type="text" name="comune">
            <label for="via">Via</label>
            <input type="text" name="via">
            <label for="civico">Civico</label>
            <input type="text" name="civico">
            <label for="cap">CAP</label>
            <input type="text" name="cap">
        </form>
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>