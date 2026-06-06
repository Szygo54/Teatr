<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['inicjuj_platnosc'])) {
    header("Location: index.php");
    exit;
}

$spektakl_id = (int)$_POST['spektakl_id'];
$miejsca_do_zapisu = $_POST['miejsca_do_zapisu']; 
$metoda_platnosci = isset($_POST['metoda']) ? htmlspecialchars($_POST['metoda']) : '';
$uzytkownik_id = $_SESSION['user_id'];
$imie_uzytkownika = $_SESSION['user_imie'];

$sukces_db = true;
$komunikat_bledu = "";

// 1. Rezerwacja w bazie danych
foreach ($miejsca_do_zapisu as $m_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Rezerwacje (uzytkownik_id, spektakl_id, miejsce_id) VALUES (?, ?, ?)");
        $stmt->execute([$uzytkownik_id, $spektakl_id, (int)$m_id]);
    } catch (\PDOException $e) {
        $sukces_db = false;
        $komunikat_bledu = "Część miejsc została zarezerwowana przez kogoś innego w trakcie przetwarzania.";
    }
}

// 2. Jeśli sukces, pobieramy dane do wygenerowania biletu PDF
if ($sukces_db) {
    $stmtS = $pdo->prepare("SELECT tytul, data_wystawienia FROM Spektakle WHERE id = ?");
    $stmtS->execute([$spektakl_id]);
    $info_spektakl = $stmtS->fetch(PDO::FETCH_ASSOC);

    $placeholdery = str_repeat('?,', count($miejsca_do_zapisu) - 1) . '?';
    $stmtM = $pdo->prepare("SELECT rzad, numer FROM Miejsca WHERE id IN ($placeholdery)");
    $stmtM->execute($miejsca_do_zapisu);
    $info_miejsca = $stmtM->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Przetwarzanie płatności - Teatr Jura</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; }
        .okno-platnosci { background-color: #262626; padding: 40px; border-radius: 8px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); text-align: center; box-sizing: border-box; }
        #ekran-blik, #ekran-ladowania, #ekran-sukcesu { display: none; }
        .logo-blik { background: #000; color: #fff; padding: 5px 15px; border-radius: 4px; font-weight: bold; font-size: 24px; letter-spacing: 2px; display: inline-block; margin-bottom: 20px; }
        .input-blik { font-size: 32px; letter-spacing: 15px; text-align: center; width: 250px; padding: 15px; border: 2px solid #444; border-radius: 8px; background: #333; color: #fff; margin-bottom: 20px; }
        .btn-potwierdz, .btn-pdf { background-color: #829356; color: #ffffff; padding: 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%; text-transform: uppercase; margin-top: 10px; }
        .spinner { border: 6px solid #333; border-top: 6px solid #829356; border-radius: 50%; width: 70px; height: 70px; animation: spin 1s linear infinite; margin: 30px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .przycisk-powrot { display: inline-block; margin-top: 20px; padding: 12px 25px; background-color: #333; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>

    <?php if ($sukces_db): ?>
    <div id="szablon-bilet-pdf" style="background-color: #1a1a1a; color: #e0e0e0; padding: 50px; border: 10px solid #829356; box-sizing: border-box; width: 800px; display:none;">
        <div style="text-align: center; margin-bottom: 40px;">
            <img src="zdjecia/logo.png?v=<?= time() ?>" style="max-width: 200px; height: auto;">
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; background-color: #262626; padding: 30px; border-radius: 10px;">
            <div style="width: 60%;">
                <h2 style="color: #ffffff; margin-top: 0;"><?= htmlspecialchars($info_spektakl['tytul']) ?></h2>
                <p><strong>Termin:</strong> <?= date('d.m.Y, H:i', strtotime($info_spektakl['data_wystawienia'])) ?></p>
                <p><strong>Właściciel:</strong> <?= htmlspecialchars($imie_uzytkownika) ?></p>
                <h3 style="color: #829356; border-bottom: 1px solid #444; padding-bottom: 10px;">Miejsca:</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach($info_miejsca as $m): ?>
                        <li>Rząd <strong><?= $m['rzad'] ?></strong> | Miejsce <strong><?= $m['numer'] ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div style="width: 35%; text-align: right;">
                <img src="zdjecia/qr.png" alt="Kod QR" style="background: white; padding: 5px; border-radius: 5px; width: 150px; height: 150px;">
                <p style="font-size: 12px; color: #aaaaaa; margin-top: 5px;">Zeskanuj przy wejściu</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="okno-platnosci">
        <div id="ekran-blik">
            <div class="logo-blik">BLIK</div>
            <h2>Wprowadź kod</h2>
            <input type="number" id="kod-blik" class="input-blik" placeholder="000000" maxlength="6">
            <button id="btn-potwierdz-blik" class="btn-potwierdz">Potwierdź płatność</button>
        </div>

        <div id="ekran-ladowania">
            <h2 id="status-tekst">Łączenie z operatorem...</h2>
            <div class="spinner"></div>
        </div>

        <div id="ekran-sukcesu">
            <?php if ($sukces_db): ?>
                <h1 style="color: #829356;">✓ Płatność zaakceptowana!</h1>
                <button class="btn-pdf" onclick="pobierzPDF()">Pobierz bilet (PDF)</button>
                <br>
                <a href="moje_bilety.php" class="przycisk-powrot">Moje bilety</a>
            <?php else: ?>
                <h1 style="color: #9e4747;">✕ Błąd transakcji</h1>
                <p><?= $komunikat_bledu ?></p>
                <a href="index.php" class="przycisk-powrot">Wróć</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const metoda = "<?= $metoda_platnosci ?>".toLowerCase();
        function uruchomPrzetwarzanie() {
            document.getElementById('ekran-blik').style.display = 'none';
            document.getElementById('ekran-ladowania').style.display = 'block';
            setTimeout(() => {
                document.getElementById('ekran-ladowania').style.display = 'none';
                document.getElementById('ekran-sukcesu').style.display = 'block';
            }, 2500);
        }
        
        if (metoda === 'blik') {
            document.getElementById('ekran-blik').style.display = 'block';
            document.getElementById('btn-potwierdz-blik').onclick = () => {
                if(document.getElementById('kod-blik').value.length === 6) uruchomPrzetwarzanie();
            };
        } else {
            uruchomPrzetwarzanie();
        }

        function pobierzPDF() {
            const el = document.getElementById('szablon-bilet-pdf');
            el.style.display = 'block';
            html2pdf().from(el).save('Bilet_Teatr_Jura.pdf');
            setTimeout(() => { el.style.display = 'none'; }, 500);
        }
    </script>
</body>
</html>