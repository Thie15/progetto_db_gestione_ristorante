<?php 
        // creo un nuovo oggetto PDO (connessione)
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // setto la modalità di errori del PDO come exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>