<?php
session_start();
if(isset($_SESSION["username"]) && isset($_SESSION["password"])){
    $results = $conn->prepare("SELECT * FROM account WHERE username = ?");
    $results->execute([$_SESSION["username"]]);

    if($results->rowCount()==1){
        $row = $results->fetch();

        $salt_div = str_split($row["Salt"], strlen($row["Salt"])/2);
        $pass_salt = hash('sha256', $salt_div[0].$_SESSION["password"].$salt_div[1]);

        if($pass_salt === $row["Password"]){
            $_SESSION["logged"] = true;
            $_SESSION["utente"] = $row;
            $_SESSION["personale"] = $row["IDPersonale"];
            $_SESSION["fornitore"] = $row["IDFornitore"];
        }else{
            $_SESSION["logged"] = false;
        }
    }else{
        $_SESSION["logged"] = false;
    }
}else{
        $_SESSION["logged"] = false;
    }
?>