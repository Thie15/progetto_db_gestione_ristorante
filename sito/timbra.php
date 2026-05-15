<?php
    include("inc/datiConnessione.php");
    try{
        include("inc/startConn.php");
    session_start();
    if((!isset($_GET["tipo"]) && !$_GET["tipo"] == "Entrata") || (!isset($_GET["tipo"]) && !$_GET["tipo"] == "Uscita")){
        echo "Formato della domanda non valido";
        header("location:dashboard.php");
    }
    $id = $_SESSION["personale"];
    $data = date('Y-m-d');
    $ora = date('H:i:s');
    $results = $conn->prepare("INSERT INTO Timbrature (IDPersonale, DataTimbratura, Ora, Tipologia) VALUES (?, ?, ?, ?)");
    $results->execute([$id, $data, $ora, $_GET["tipo"]]);
    header("location:dashboard.php");
?>
<?php    
    }catch(PDOException $e){
        echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
    }
?>