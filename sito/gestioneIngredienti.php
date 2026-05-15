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
        <h1>Aggiungi un nuovo ingrediente</h1>
        <form action="" method="post">
            <label for="nome">Nome</label>
            <input type="text" name="nome">
        </form>
        <h1>Ingredienti in magazzino</h1>
        <?php
            
        ?>
        <?php    
            }catch(PDOException $e){
                echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
            }
        ?>
    </body>
</html>