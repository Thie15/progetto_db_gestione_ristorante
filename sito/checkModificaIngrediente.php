<?php
session_start();
include("inc/datiConnessione.php");
try {
    include("inc/startConn.php");
    include("inc/checklogin.php");

    if(!$_SESSION["logged"] || isset($_SESSION["personale"]) || isset($_SESSION["fornitore"])){
        header("location:login.php");
        exit();
    }

    if(!isset($_POST["id"]) || !is_numeric($_POST["id"])){
        header("location:gestioneIngredienti.php");
        exit();
    }
    $id = (int)$_POST["id"];

    if(!isset($_POST["nome"]) || trim($_POST["nome"]) == ""){
        $_SESSION["mi_errore"] = "Il nome è obbligatorio";
        header("location:modificaIngrediente.php?id=$id");
        exit();
    }

    $unitaValide = ["g","kg","pz","l"];
    if(!in_array($_POST["unita"], $unitaValide)){
        $_SESSION["mi_errore"] = "Unità di misura non valida";
        header("location:modificaIngrediente.php?id=$id");
        exit();
    }

    $chk = $conn->prepare("SELECT IDIngrediente FROM ingredienti WHERE Nome = ? AND IDIngrediente != ?");
    $chk->execute([trim($_POST["nome"]), $id]);
    if($chk->rowCount() > 0){
        $_SESSION["mi_errore"] = "Nome già in uso da un altro ingrediente";
        header("location:modificaIngrediente.php?id=$id");
        exit();
    }

    $conn->prepare("UPDATE ingredienti SET Nome = ?, UnitaMisura = ?, Quantita = ? WHERE IDIngrediente = ?")
         ->execute([trim($_POST["nome"]), $_POST["unita"], (int)$_POST["quantita"], $id]);

    $conn->prepare("DELETE FROM aux_ingredienti_specifiche WHERE IDIngrediente = ?")->execute([$id]);
    if(!empty($_POST["specifiche"])){
        $insSpec = $conn->prepare("INSERT INTO aux_ingredienti_specifiche (IDIngrediente, IDSpecifica) VALUES (?, ?)");
        foreach($_POST["specifiche"] as $idSpec){
            $idSpec = (int)$idSpec;
            $chkSpec = $conn->prepare("SELECT IDSpecifica FROM specifiche WHERE IDSpecifica = ?");
            $chkSpec->execute([$idSpec]);
            if($chkSpec->rowCount() == 1){
                $insSpec->execute([$id, $idSpec]);
            }
        }
    }

    $_SESSION["mi_ok"] = "Ingrediente aggiornato con successo";
    header("location:modificaIngrediente.php?id=$id");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>