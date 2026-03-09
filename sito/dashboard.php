<?php
    session_start();
    if(isset($_SESSION["personale"])){
        echo "Benvenuto $_SESSION[username] del personale";
    }
    if(isset($_SESSION["fornitore"])){
        echo "Benvenuto $_SESSION[username] del fornitore";
    }
?>