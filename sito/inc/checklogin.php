<?php
session_start();
if(isset($_SESSION["username"]) && isset($_SESSION["password"])){
    $sql = "SELECT * FROM account WHERE username = '$_SESSION[username]'";
    $results = $conn->query($sql);

    if($results->rowCount()==1){
        $row = $results->fetch();

        $salt_div = str_split($row["Salt"], strlen($row["Salt"])/2);
        $pass_salt = hash('sha256', $salt_div[0].$_SESSION["password"].$salt_div[1]);

        if($pass_salt === $row["Password"]){
            $_SESSION["ok"] = true;
        }else{
            $_SESSION["ok"] = false;
        }
    }else{
        $_SESSION["ok"] = false;
    }
}else{
        $_SESSION["ok"] = false;
    }
?>