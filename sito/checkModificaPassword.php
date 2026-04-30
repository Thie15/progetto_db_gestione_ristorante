<?php
include("inc/datiConnessione.php");
try{
    session_start();
    include("inc/startConn.php");

    var_dump($_POST);
    
    if($_POST["passwordVecchia"] === hash('sha256', '')){
        $_SESSION["password_vecchia"] = "E necessario inserire la password vecchia";
    }

    if($_POST["passwordNuova"] === hash('sha256', '')){
        $_SESSION["password_nuova"] = "E necessario inserire la password nuova";
    }

    if(strlen($_POST["passwordVecchia"]) != 64 || strlen($_POST["passwordNuova"]) != 64){
        $_SESSION["password_error"] = "Hash password non valido";
    }

    if(isset($_SESSION["password_vecchia"]) || isset($_SESSION["password_nuova"]) || isset($_SESSION["password_error"])){
        header("location:modificaPassword.php");
    }else{ 
        $sql = "SELECT * FROM account WHERE Username = '$_SESSION[username]'";
        $results = $conn->query($sql);

        if($results->rowCount()==1){
            $row = $results->fetch();

            $salt_div = str_split($row["Salt"], strlen($row["Salt"])/2);
            $pass_salt = hash('sha256', $salt_div[0].$_POST['passwordVecchia'].$salt_div[1]);

            if($pass_salt === $row["Password"]){
                $saltNuovo = hash('sha256', rand());
                $salt_divNuovo = str_split($saltNuovo, strlen($saltNuovo)/2);
                $pass_saltNuova = hash('sha256', $salt_divNuovo[0].$_POST['passwordNuova'].$salt_divNuovo[1]);
                $sqlModifica = "UPDATE account SET Password = '$pass_saltNuova', Salt = '$saltNuovo' WHERE Username = '$_SESSION[username]'";
                $resultsModifica = $conn->query($sqlModifica);
                if($resultsModifica->rowCount()==1){
                    $_SESSION["errore"] = "La password è stata modificata esegui nuovamente l'accesso";
                    session_unset();
                    session_destroy();
                    header("location:login.php");
                }else{
                    $_SESSION["password_error"] = "La modifica della password non e andata a buon fine";
                    header("location:modificaPassword.php");
                }
            }else{
                $_SESSION["password_vecchia"] = "La password vecchia è errata";
                header("location:modificaPassword.php");
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