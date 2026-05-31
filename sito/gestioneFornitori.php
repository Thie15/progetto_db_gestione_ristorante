<?php
include("inc/datiConnessione.php");
try {
    include("inc/startConn.php");
    include("inc/checklogin.php");

    if(!$_SESSION["logged"] || isset($_SESSION["personale"]) || isset($_SESSION["fornitore"])){
        session_unset();
        session_destroy();
        header("location:login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartristo - Gestione fornitori</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/gestioneFornitori.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Gestione fornitori</h1>

    <?php
    if(isset($_SESSION["gf_errore"])){
        echo "<p class='gf-msg gf-msg-errore'>".$_SESSION["gf_errore"]."</p>";
        unset($_SESSION["gf_errore"]);
    }
    if(isset($_SESSION["gf_ok"])){
        echo "<p class='gf-msg gf-msg-ok'>".$_SESSION["gf_ok"]."</p>";
        unset($_SESSION["gf_ok"]);
    }
    ?>

    <!-- LISTA FORNITORI -->
    <div class="gf-sezione">
        <p class="gf-sezione-label">Fornitori registrati</p>
        <?php
        $res = $conn->query("
            SELECT f.*,
                a.Username,
                COUNT(of2.IDOrdineFornitore) AS NumOrdini
            FROM fornitori f
            LEFT JOIN account a ON a.IDFornitore = f.IDFornitore
            LEFT JOIN ordinifornitori of2 ON of2.IDFornitore = f.IDFornitore
            GROUP BY f.IDFornitore
            ORDER BY f.Nome ASC
        ");
        if($res->rowCount() < 1){
            echo "<p class='gf-vuoto'>Nessun fornitore registrato.</p>";
        } else {
            $fornitori = $res->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='gf-tabella'>";
            echo "<thead><tr>
                    <th>Nome</th>
                    <th>P.IVA</th>
                    <th>Comune</th>
                    <th>Ordini</th>
                    <th>Account</th>
                    <th>Azioni</th>
                  </tr></thead><tbody>";
            foreach($fornitori as $f){
                $username = $f["Username"] ?? "<span class='gf-nessuno'>nessuno</span>";
                $ordini   = $f["NumOrdini"] > 0
                    ? "<span class='gf-badge-ordini'>{$f['NumOrdini']}</span>"
                    : "<span class='gf-nessuno'>0</span>";
                $nomeFornitore = htmlspecialchars($f["Nome"], ENT_QUOTES);
                $btnElimina = $f["NumOrdini"] > 0
                    ? "<span class='gf-btn gf-btn-disabled' title='Impossibile eliminare: ha ordini collegati'>Elimina</span>"
                    : "<a class='gf-btn gf-btn-elimina' href='checkGestioneFornitori.php?azione=elimina&id={$f['IDFornitore']}'
                        onclick=\"return confirm('Eliminare il fornitore $nomeFornitore?')\">Elimina</a>";
                echo "<tr>";
                echo "  <td class='gf-nome'>{$f['Nome']}</td>";
                echo "  <td class='gf-piva'>{$f['PIVA']}</td>";
                echo "  <td>{$f['Indirizzo_Comune']}</td>";
                echo "  <td>$ordini</td>";
                echo "  <td class='gf-username'>$username</td>";
                echo "  <td>
                            <a class='gf-btn gf-btn-modifica' href='modificaFornitore.php?id={$f['IDFornitore']}'>Modifica</a>
                            $btnElimina
                        </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>

    <!-- FORM AGGIUNGI -->
    <div class="gf-sezione">
        <p class="gf-sezione-label">Aggiungi nuovo fornitore</p>
        <form class="gf-form" method="post" action="checkGestioneFornitori.php?azione=aggiungi">

            <div class="gf-campo">
                <label>Nome azienda</label>
                <input type="text" name="nome" placeholder="Es. FreshFood SRL" required>
            </div>
            <div class="gf-campo">
                <label>Partita IVA</label>
                <input type="text" name="piva" placeholder="01234567890" maxlength="11" required>
            </div>
            <div class="gf-campo">
                <label>Comune</label>
                <input type="text" name="comune" placeholder="Milano" required>
            </div>
            <div class="gf-campo">
                <label>Via</label>
                <input type="text" name="via" placeholder="Via Roma" required>
            </div>
            <div class="gf-campo">
                <label>Civico</label>
                <input type="text" name="civico" placeholder="12" required>
            </div>
            <div class="gf-campo">
                <label>CAP</label>
                <input type="text" name="cap" placeholder="20100" required>
            </div>

            <div class="gf-sep-form"></div>

            <div class="gf-campo">
                <label>Username account</label>
                <input type="text" name="username" placeholder="freshfood" required>
            </div>
            <div class="gf-campo">
                <label>Email account</label>
                <input type="email" name="email" placeholder="info@freshfood.it" required>
            </div>
            <div class="gf-campo">
                <label>Password</label>
                <input id="gf-pwd" type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="gf-campo gf-campo-full">
                <button type="submit">Aggiungi fornitore</button>
            </div>
        </form>
    </div>

    <script>
        async function sha256(msg){
            const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(msg));
            return Array.from(new Uint8Array(buf)).map(b=>b.toString(16).padStart(2,'0')).join('');
        }
        document.querySelector('.gf-form').addEventListener('submit', async function(e){
            e.preventDefault();
            const pwd = document.getElementById('gf-pwd');
            pwd.value = await sha256(pwd.value);
            e.target.submit();
        });
    </script>

<?php
} catch(PDOException $e){
    echo "<h2 style='color:red;'>".$e->getMessage()."</h2>";
}
?>
</body>
</html>