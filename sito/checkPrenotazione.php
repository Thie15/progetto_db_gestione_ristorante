<?php
session_start();
include("inc/datiConnessione.php");
try {
    include("inc/startConn.php");

    // Validazione campi
    if(
        !isset($_POST["ora"])      || trim($_POST["ora"])      == "" ||
        !isset($_POST["data"])     || trim($_POST["data"])     == "" ||
        !isset($_POST["persone"])  || trim($_POST["persone"])  == "" ||
        !isset($_POST["pagamento"])|| trim($_POST["pagamento"])== ""
    ){
        $_SESSION["prenotazione_errore"] = "Tutti i campi sono obbligatori";
        header("location:prenotazione.php");
        exit();
    }

    $ora      = $_POST["ora"];
    $data     = $_POST["data"];
    $persone  = (int)$_POST["persone"];
    $pagamento = $_POST["pagamento"];

    // Pagamenti validi (whitelist, non fidarsi del client)
    $pagamentiValidi = ["Contanti", "Carta", "PayPal", "Satispay", "Bonifico"];
    if(!in_array($pagamento, $pagamentiValidi)){
        $_SESSION["prenotazione_errore"] = "Metodo di pagamento non valido";
        header("location:prenotazione.php");
        exit();
    }

    if($persone < 1 || $persone > 20){
        $_SESSION["prenotazione_errore"] = "Numero di persone non valido";
        header("location:prenotazione.php");
        exit();
    }

    // Cerca un tavolo libero con posti sufficienti per data e ora
    // Un tavolo è libero se non ha prenotazioni nello stesso giorno alla stessa ora
    $risultato = $conn->prepare("
        SELECT t.IDTavolo FROM tavoli t
        WHERE t.Posti >= ?
        AND t.IDTavolo NOT IN (
            SELECT apt.IDTavolo
            FROM aux_prenotazioni_tavoli apt
            INNER JOIN prenotazioni p ON p.IDPrenotazione = apt.IDPrenotazione
            WHERE p.DataPrenotazione = ? AND p.Ora = ?
        )
        ORDER BY t.Posti ASC
        LIMIT 1
    ");
    $risultato->execute([$persone, $data, $ora]);

    if($risultato->rowCount() == 0){
        $_SESSION["prenotazione_errore"] = "Nessun tavolo disponibile per la data, l'orario e il numero di persone selezionati";
        header("location:prenotazione.php");
        exit();
    }

    $tavolo = $risultato->fetch();
    $idTavolo = $tavolo["IDTavolo"];

    // Inserisce la prenotazione
    $ins = $conn->prepare("INSERT INTO prenotazioni (Ora, DataPrenotazione, NumeroPersone, MetodoPagamento) VALUES (?, ?, ?, ?)");
    $ins->execute([$ora, $data, $persone, $pagamento]);
    $idPrenotazione = $conn->lastInsertId();

    // Collega il tavolo alla prenotazione
    $insAux = $conn->prepare("INSERT INTO aux_prenotazioni_tavoli (IDPrenotazione, IDTavolo) VALUES (?, ?)");
    $insAux->execute([$idPrenotazione, $idTavolo]);

    $_SESSION["prenotazione_ok"] = $idPrenotazione;
    header("location:prenotazione.php");
    exit();

} catch(PDOException $e) {
    echo "<h2 style='color:red; font-weight:bold'>".$e->getMessage()."</h2>";
}
?>