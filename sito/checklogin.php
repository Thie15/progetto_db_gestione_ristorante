<?php
include("inc/datiConnessione.php");
try{
    session_start();
    include("inc/startConn.php");

    function controlla($campo, $sessione, $messaggio_errore){

        if(isset($_POST[$campo]) && trim($_POST[$campo]) != "") {
            return true;
        }
        else {
            $_SESSION["$sessione"] = $messaggio_errore;
            return false;
        }
    }
    
    controlla("username", "username_error", "E necessario inserire un username");
    controlla("password", "password_error", "E necessario inserire una password");

    if($_POST["password"] === hash('sha256', '')){
        $_SESSION["password_error"] = "E necessario inserire una password";
    }

    if(strlen($_POST["password"]) != 64){
        $_SESSION["password_error"] = "Hash password non valido";
    }

    if(isset($_SESSION["username_error"]) || isset($_SESSION["password_error"])){
        header("location:login.php");
    }else{ 
        $sql = "SELECT * FROM account WHERE username = '$_POST[username]'";
        $results = $conn->query($sql);

        if($results->rowCount()==1){
            $row = $results->fetch();

            $salt_div = str_split($row["Salt"], strlen($row["Salt"])/2);
            $pass_salt = hash('sha256', $salt_div[0].$_POST['password'].$salt_div[1]);

            if($pass_salt === $row["Password"]){
                $_SESSION["username"] = $row["Username"];
                $_SESSION["password"] = $_POST["password"];
                header("location:dashboard.php");
                //var_dump($_SESSION);
            }else{
                $_SESSION["errore"] = "Password errata";
                header("location:login.php");
            }
        }else{
            $_SESSION["errore"] = "Username non valido";
            header("location:login.php");
        }
    }

}catch(PDOException $e) {
    // stampando il messaggio di errore
    echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
}
?>