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
    <title>Smartristo - Gestione personale</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/gestionePersonale.css">
</head>
<body>
    <?php include("inc/header.php"); ?>
    <h1 class="titoloPagina">Gestione personale</h1>

    <?php
    if(isset($_SESSION["gp_errore"])){
        echo "<p class='gp-msg gp-msg-errore'>".$_SESSION["gp_errore"]."</p>";
        unset($_SESSION["gp_errore"]);
    }
    if(isset($_SESSION["gp_ok"])){
        echo "<p class='gp-msg gp-msg-ok'>".$_SESSION["gp_ok"]."</p>";
        unset($_SESSION["gp_ok"]);
    }
    ?>

    <div class="gp-sezione">
        <p class="gp-sezione-label">Personale attuale</p>
        <?php
        $res = $conn->query("SELECT p.*, a.Username,
            CASE
                WHEN c.IDPersonale IS NOT NULL THEN 'Cuoco'
                WHEN cam.IDPersonale IS NOT NULL THEN 'Cameriere'
                ELSE 'Nessuno'
            END AS Ruolo
            FROM personale p
            LEFT JOIN account a ON a.IDPersonale = p.IDPersonale
            LEFT JOIN cuochi c ON c.IDPersonale = p.IDPersonale
            LEFT JOIN camerieri cam ON cam.IDPersonale = p.IDPersonale
            ORDER BY p.Cognome ASC");

        if($res->rowCount() < 1){
            echo "<p class='gp-vuoto'>Nessun dipendente registrato.</p>";
        } else {
            $personale = $res->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='gp-tabella'>";
            echo "<thead><tr>
                    <th>Nome</th>
                    <th>Ruolo</th>
                    <th>Turno</th>
                    <th>Stipendio</th>
                    <th>Account</th>
                    <th>Azioni</th>
                  </tr></thead><tbody>";
            foreach($personale as $p){
                $badgeClass = match($p["Ruolo"]){
                    "Cuoco"     => "badge-cuoco",
                    "Cameriere" => "badge-cameriere",
                    default     => "badge-nessuno"
                };
                $username = $p["Username"] ?? "<span style='color:#444'>nessuno</span>";
                echo "<tr>";
                echo "  <td>{$p['Nome']} {$p['Cognome']}</td>";
                echo "  <td><span class='gp-badge $badgeClass'>{$p['Ruolo']}</span></td>";
                echo "  <td>{$p['Turno']}</td>";
                echo "  <td>".number_format($p['Stipendio'], 2)."€</td>";
                echo "  <td class='gp-username'>$username</td>";
                echo "  <td>
                            <a class='gp-btn gp-btn-modifica' href='modificaPersonale.php?id={$p['IDPersonale']}'>Modifica</a>
                            <a class='gp-btn gp-btn-elimina' href='checkGestionePersonale.php?azione=elimina&id={$p['IDPersonale']}' onclick=\"return confirm('Eliminare {$p['Nome']} {$p['Cognome']}? Verranno eliminati anche account e ruolo associati.')\">Elimina</a>
                        </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>

    <div class="gp-sezione">
        <p class="gp-sezione-label">Aggiungi nuovo dipendente</p>
        <form class="gp-form" method="post" action="checkGestionePersonale.php?azione=aggiungi">

            <div class="gp-campo">
                <label>Nome</label>
                <input type="text" name="nome" placeholder="Mario" required>
            </div>
            <div class="gp-campo">
                <label>Cognome</label>
                <input type="text" name="cognome" placeholder="Rossi" required>
            </div>
            <div class="gp-campo">
                <label>Ruolo</label>
                <select name="ruolo">
                    <option value="nessuno">Nessuno</option>
                    <option value="cuoco">Cuoco</option>
                    <option value="cameriere">Cameriere</option>
                </select>
            </div>
            <div class="gp-campo">
                <label>Turno</label>
                <select name="turno">
                    <option value="Pranzo">Pranzo</option>
                    <option value="Cena">Cena</option>
                </select>
            </div>
            <div class="gp-campo">
                <label>Stipendio (€)</label>
                <input type="number" name="stipendio" placeholder="1800" min="0" step="0.01" required>
            </div>
            <div class="gp-campo">
                <label>Comune</label>
                <input type="text" name="comune" placeholder="Milano" required>
            </div>
            <div class="gp-campo">
                <label>Via</label>
                <input type="text" name="via" placeholder="Via Roma" required>
            </div>
            <div class="gp-campo">
                <label>Civico</label>
                <input type="text" name="civico" placeholder="12" required>
            </div>
            <div class="gp-campo">
                <label>CAP</label>
                <input type="text" name="cap" placeholder="20100" required>
            </div>

            <div class="gp-sep-form"></div>

            <div class="gp-campo">
                <label>Username account</label>
                <input type="text" name="username" placeholder="m.rossi" required>
            </div>
            <div class="gp-campo">
                <label>Email account</label>
                <input type="email" name="email" placeholder="mario.rossi@smartristo.com" required>
            </div>
            <div class="gp-campo">
                <label>Password</label>
                <input id="gp-pwd" type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="gp-campo gp-campo-full">
                <button type="submit">Aggiungi dipendente</button>
            </div>
        </form>
    </div>

    <script>
        async function sha256(msg){
            const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(msg));
            return Array.from(new Uint8Array(buf)).map(b=>b.toString(16).padStart(2,'0')).join('');
        }
        document.querySelector('.gp-form').addEventListener('submit', async function(e){
            e.preventDefault();
            const pwd = document.getElementById('gp-pwd');
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