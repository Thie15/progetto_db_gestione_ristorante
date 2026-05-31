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
        header("location:gestionePiatti.php");
        exit();
    }
    $id = (int)$_POST["id"];

    if(!isset($_POST["nome"]) || trim($_POST["nome"]) == "" ||
       !isset($_POST["prezzo"]) || trim($_POST["prezzo"]) == ""){
        $_SESSION["mpi_errore"] = "Nome e prezzo sono obbligatori";
        header("location:modificaPiatto.php?id=$id");
        exit();
    }

    $categorieValide = ["Antipasto","Primo","Secondo","Contorno","Dessert","Bevanda"];
    if(!isset($_POST["categoria"]) || !in_array($_POST["categoria"], $categorieValide)){
        $_SESSION["mpi_errore"] = "Categoria non valida";
        header("location:modificaPiatto.php?id=$id");
        exit();
    }

    $nomeFile = $_POST["immagine_attuale"];

    if(isset($_FILES["immagine"]) && $_FILES["immagine"]["error"] === UPLOAD_ERR_OK){
        $tipiConsentiti = ["image/webp","image/jpeg","image/png"];
        $mimeType = mime_content_type($_FILES["immagine"]["tmp_name"]);

        if(!in_array($mimeType, $tipiConsentiti)){
            $_SESSION["mpi_errore"] = "Formato immagine non supportato";
            header("location:modificaPiatto.php?id=$id");
            exit();
        }
        if($_FILES["immagine"]["size"] > 2 * 1024 * 1024){
            $_SESSION["mpi_errore"] = "L'immagine supera i 2MB";
            header("location:modificaPiatto.php?id=$id");
            exit();
        }

        $percorsoVecchio = "img/piatti/".$nomeFile;
        if(file_exists($percorsoVecchio)) unlink($percorsoVecchio);

        if(!move_uploaded_file($_FILES["immagine"]["tmp_name"], "img/piatti/".$nomeFile)){
            $_SESSION["mpi_errore"] = "Errore nel salvataggio dell'immagine";
            header("location:modificaPiatto.php?id=$id");
            exit();
        }
    }

    $conn->prepare("UPDATE piatti SET Nome = ?, Prezzo = ?, Categoria = ? WHERE IDPiatto = ?")
         ->execute([trim($_POST["nome"]), (float)$_POST["prezzo"], $_POST["categoria"], $id]);

    $conn->prepare("DELETE FROM aux_piatti_ingredienti WHERE IDPiatto = ?")->execute([$id]);
    if(!empty($_POST["ingredienti"])){
        $insIng = $conn->prepare("INSERT INTO aux_piatti_ingredienti (IDPiatto, IDIngrediente) VALUES (?, ?)");
        foreach($_POST["ingredienti"] as $idIng){
            $idIng = (int)$idIng;
            $chk = $conn->prepare("SELECT IDIngrediente FROM ingredienti WHERE IDIngrediente = ?");
            $chk->execute([$idIng]);
            if($chk->rowCount() == 1){
                $insIng->execute([$id, $idIng]);
            }
        }
    }

    $_SESSION["mpi_ok"] = "Piatto aggiornato con successo";
    header("location:modificaPiatto.php?id=$id");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>