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
        <h1>Prenota facilmente un nuovo tavolo</h1>
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>