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

    $azione = $_GET["azione"] ?? "";

    if($azione === "aggiungi"){

        if(!isset($_POST["nome"]) || trim($_POST["nome"]) == "" ||
           !isset($_POST["prezzo"]) || trim($_POST["prezzo"]) == ""){
            $_SESSION["gpi_errore"] = "Nome e prezzo sono obbligatori";
            header("location:gestionePiatti.php");
            exit();
        }

        $categorieValide = ["Antipasto","Primo","Secondo","Contorno","Dessert","Bevanda"];
        if(!isset($_POST["categoria"]) || !in_array($_POST["categoria"], $categorieValide)){
            $_SESSION["gpi_errore"] = "Categoria non valida";
            header("location:gestionePiatti.php");
            exit();
        }

        if(!isset($_FILES["immagine"]) || $_FILES["immagine"]["error"] !== UPLOAD_ERR_OK){
            $_SESSION["gpi_errore"] = "Errore nel caricamento dell'immagine";
            header("location:gestionePiatti.php");
            exit();
        }

        $tipiConsentiti = ["image/webp", "image/jpeg", "image/png"];
        $mimeType = mime_content_type($_FILES["immagine"]["tmp_name"]);
        if(!in_array($mimeType, $tipiConsentiti)){
            $_SESSION["gpi_errore"] = "Formato immagine non supportato. Usa webp, jpg o png";
            header("location:gestionePiatti.php");
            exit();
        }

        if($_FILES["immagine"]["size"] > 2 * 1024 * 1024){
            $_SESSION["gpi_errore"] = "L'immagine supera i 2MB";
            header("location:gestionePiatti.php");
            exit();
        }

        $ins = $conn->prepare("INSERT INTO piatti (Nome, Prezzo, Categoria, Immagine) VALUES (?, ?, ?, '00.webp')");
        $ins->execute([
            trim($_POST["nome"]),
            (float)$_POST["prezzo"],
            $_POST["categoria"]
        ]);
        $idPiatto = $conn->lastInsertId();

        $nomeFile = str_pad($idPiatto, 2, "0", STR_PAD_LEFT).".webp";
        $destinazione = "img/piatti/".$nomeFile;

        if(!move_uploaded_file($_FILES["immagine"]["tmp_name"], $destinazione)){
            $conn->prepare("DELETE FROM piatti WHERE IDPiatto = ?")->execute([$idPiatto]);
            $_SESSION["gpi_errore"] = "Errore nel salvataggio dell'immagine. Controlla i permessi della cartella img/piatti/";
            header("location:gestionePiatti.php");
            exit();
        }

        $conn->prepare("UPDATE piatti SET Immagine = ? WHERE IDPiatto = ?")->execute([$nomeFile, $idPiatto]);

        if(!empty($_POST["ingredienti"])){
            $insIng = $conn->prepare("INSERT INTO aux_piatti_ingredienti (IDPiatto, IDIngrediente) VALUES (?, ?)");
            foreach($_POST["ingredienti"] as $idIng){
                $idIng = (int)$idIng;
                $chk = $conn->prepare("SELECT IDIngrediente FROM ingredienti WHERE IDIngrediente = ?");
                $chk->execute([$idIng]);
                if($chk->rowCount() == 1){
                    $insIng->execute([$idPiatto, $idIng]);
                }
            }
        }

        $_SESSION["gpi_ok"] = "Piatto \"".trim($_POST["nome"])."\" aggiunto con successo";
        header("location:gestionePiatti.php");
        exit();
    }

    if($azione === "elimina"){
        if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
            $_SESSION["gpi_errore"] = "ID non valido";
            header("location:gestionePiatti.php");
            exit();
        }
        $id = (int)$_GET["id"];

        $res = $conn->prepare("SELECT Immagine FROM piatti WHERE IDPiatto = ?");
        $res->execute([$id]);
        if($res->rowCount() == 0){
            $_SESSION["gpi_errore"] = "Piatto non trovato";
            header("location:gestionePiatti.php");
            exit();
        }
        $immagine = $res->fetch()["Immagine"];

        $conn->prepare("DELETE FROM aux_piatti_ingredienti WHERE IDPiatto = ?")->execute([$id]);
        $conn->prepare("DELETE FROM piatti WHERE IDPiatto = ?")->execute([$id]);

        $percorso = "img/piatti/".$immagine;
        if(file_exists($percorso)) unlink($percorso);

        $_SESSION["gpi_ok"] = "Piatto eliminato con successo";
        header("location:gestionePiatti.php");
        exit();
    }

    header("location:gestionePiatti.php");
    exit();

} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>