<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['inicjuj_platnosc'])) {
    header("Location: index.php");
    exit;
}

$spektakl_id = (int)$_POST['spektakl_id'];
$miejsca_do_zapisu = $_POST['miejsca_do_zapisu']; 
$metoda_platnosci = htmlspecialchars($_POST['metoda']);
$uzytkownik_id = $_SESSION['user_id'];

$sukces_db = true;
$komunikat_bledu = "";

// Realny zapis do bazy dzieje się natychmiast na serwerze
foreach ($miejsca_do_zapisu as $m_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Rezerwacje (uzytkownik_id, spektakl_id, miejsce_id) VALUES (?, ?, ?)");
        $stmt->execute([$uzytkownik_id, $spektakl_id, (int)$m_id]);
    } catch (\PDOException $e) {
        $sukces_db = false;
        $komunikat_bledu = "Część miejsc została zarezerwowana przez kogoś innego.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Przetwarzanie płatności...</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; background-color: #f4f4f4; }
        .okno-platnosci { background: white; padding: 40px; border-radius: 8px; max-width: 400px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        
        /* Animacja kręcącego się kółka */
        .spinner {
            border: 8px solid #f3f3f3; 
            border-top: 8px solid #3498db; 
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* Ukrywanie ekranu sukcesu na start */
        #ekran-sukcesu { display: none; }
        .przycisk-powrot { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #333; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

    <div class="okno-platnosci">
        
        <div id="ekran-ladowania">
            <h2 id="status-tekst">Łączenie z operatorem (<?= strtoupper($metoda_platnosci) ?>)...</h2>
            <div class="spinner" id="spinner-ikona"></div>
            <p style="color: #666; font-size: 14px;">Proszę nie zamykać okna przeglądarki.</p>
        </div>

        <div id="ekran-sukcesu">
            <?php if ($sukces_db): ?>
                <h1 style="color: #2e7d32;">✅ Płatność zaakceptowana!</h1>
                <p>Bilety zostały pomyślnie przypisane do Twojego konta.</p>
            <?php else: ?>
                <h1 style="color: #d32f2f;">❌ Błąd transakcji</h1>
                <p><?= $komunikat_bledu ?></p>
            <?php endif; ?>
            <a href="index.php" class="przycisk-powrot">Wróć na stronę główną</a>
        </div>

    </div>

    <script>
        // Czekamy 2 sekundy, zmieniamy tekst (iluzja autoryzacji)
        setTimeout(function() {
            document.getElementById('status-tekst').innerText = "Autoryzacja transakcji...";
        }, 1500);

        // Po 3.5 sekundach ukrywamy ładowanie i pokazujemy wynik z bazy
        setTimeout(function() {
            document.getElementById('ekran-ladowania').style.display = 'none';
            document.getElementById('ekran-sukcesu').style.display = 'block';
        }, 3500);
    </script>

</body>
</html>