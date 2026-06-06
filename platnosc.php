<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['inicjuj_platnosc'])) {
    header("Location: index.php");
    exit;
}

$termin_id = (int)$_POST['termin_id'];
$miejsca_do_zapisu = $_POST['miejsca_do_zapisu']; 
$metoda_platnosci = isset($_POST['metoda']) ? htmlspecialchars($_POST['metoda']) : '';
$uzytkownik_id = $_SESSION['user_id'];
$imie_uzytkownika = $_SESSION['user_imie'];

$sukces_db = true;
$komunikat_bledu = "";

// 1. Rezerwacja w bazie danych
foreach ($miejsca_do_zapisu as $m_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Rezerwacje (uzytkownik_id, termin_id, miejsce_id) VALUES (?, ?, ?)");
        $stmt->execute([$uzytkownik_id, $termin_id, (int)$m_id]);
    } catch (\PDOException $e) {
        $sukces_db = false;
        $komunikat_bledu = "Przynajmniej jedno z wybranych miejsc zostało już zajęte.";
    }
}

// 2. Pobieranie danych do PDF
if ($sukces_db) {
    $stmtS = $pdo->prepare("SELECT s.tytul, t.data_wystawienia 
                            FROM Spektakle s 
                            JOIN Terminy t ON s.id = t.spektakl_id 
                            WHERE t.id = ?");
    $stmtS->execute([$termin_id]);
    $info_spektakl = $stmtS->fetch(PDO::FETCH_ASSOC);

    $placeholdery = str_repeat('?,', count($miejsca_do_zapisu) - 1) . '?';
    $stmtM = $pdo->prepare("SELECT rzad, numer FROM Miejsca WHERE id IN ($placeholdery)");
    $stmtM->execute($miejsca_do_zapisu);
    $info_miejsca = $stmtM->fetchAll(PDO::FETCH_ASSOC);
}

// 3. Konwersja obrazków na format Base64 
function getBase64Image($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return ''; 
}

$logo_base64 = getBase64Image('zdjecia/logo.png');
$qr_base64 = getBase64Image('zdjecia/qr.png');
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
        
        .input-blik::-webkit-outer-spin-button,
        .input-blik::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .input-blik[type=number] { -moz-appearance: textfield; }

        .btn-potwierdz, .btn-pdf { background-color: #829356; color: #ffffff; padding: 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%; text-transform: uppercase; margin-top: 10px; transition: 0.3s; }
        .btn-potwierdz:hover, .btn-pdf:hover { background-color: #6a7944; }
        
        .spinner { border: 6px solid #333; border-top: 6px solid #829356; border-radius: 50%; width: 70px; height: 70px; animation: spin 1s linear infinite; margin: 30px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        .przycisk-powrot { display: inline-block; margin-top: 20px; padding: 12px 25px; background-color: #333; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .przycisk-powrot:hover { background-color: #444; }
    </style>
</head>
<body>

    <?php if ($sukces_db): ?>
    <div id="szablon-bilet-pdf" style="background-color: #1a1a1a; color: #e0e0e0; padding: 40px; border: 10px solid #829356; box-sizing: border-box; width: 650px; display:none;">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="<?= $logo_base64 ?>" style="max-width: 180px;" alt="Logo Teatru">
        </div>
        <div style="background-color: #262626; padding: 25px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #333;">
            <div style="flex: 1; padding-right: 20px;">
                <h2 style="margin-top: 0; color: #ffffff;"><?= htmlspecialchars($info_spektakl['tytul']) ?></h2>
                <p><strong>Termin:</strong> <?= date('d.m.Y, H:i', strtotime($info_spektakl['data_wystawienia'])) ?></p>
                <p><strong>Właściciel:</strong> <?= htmlspecialchars($imie_uzytkownika) ?></p>
                <h3 style="color: #829356; margin-bottom: 5px;">Miejsca:</h3>
                <ul style="color: #cccccc; margin-top: 5px; padding-left: 20px;">
                    <?php foreach($info_miejsca as $m): ?>
                        <li>Rząd <strong style="color: #ffffff;"><?= htmlspecialchars($m['rzad']) ?></strong> | Miejsce <strong style="color: #ffffff;"><?= htmlspecialchars($m['numer']) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div style="text-align: center; background: #ffffff; padding: 15px; border-radius: 10px; border: 3px solid #829356; width: 140px;">
                <?php if ($qr_base64): ?>
                    <img src="<?= $qr_base64 ?>" alt="Kod QR" style="width: 100%; height: auto; display: block;">
                <?php else: ?>
                    <div style="width: 100%; height: 140px; background: #eee; line-height: 140px; color: #333; font-size: 12px;">Brak QR</div>
                <?php endif; ?>
                <p style="margin: 10px 0 0 0; font-size: 12px; font-weight: bold; color: #000;">Okaż przy wejściu</p>
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
            <h2>Łączenie z operatorem...</h2>
            <div class="spinner"></div>
        </div>

        <div id="ekran-sukcesu">
            <?php if ($sukces_db): ?>
                <h1 style="color: #829356;">✓ Płatność zaakceptowana!</h1>
                <button class="btn-pdf" onclick="pobierzPDF()">Pobierz bilet (PDF)</button>
                <br><a href="moje_bilety.php" class="przycisk-powrot">Moje bilety</a>
            <?php else: ?>
                <h1 style="color: #9e4747;">✕ Błąd transakcji</h1>
                <p><?= htmlspecialchars($komunikat_bledu) ?></p>
                <a href="javascript:history.back()" class="przycisk-powrot">Wróć</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function uruchomPrzetwarzanie() {
            document.getElementById('ekran-blik').style.display = 'none';
            document.getElementById('ekran-ladowania').style.display = 'block';
            setTimeout(() => {
                document.getElementById('ekran-ladowania').style.display = 'none';
                document.getElementById('ekran-sukcesu').style.display = 'block';
            }, 2500);
        }
        
        const metoda = "<?= $metoda_platnosci ?>".toLowerCase();
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
            el.style.display = 'block'; // Pokazujemy na moment renderowania

            const opcje = {
                margin:       0.5, // 0.5 cala marginesu ze wszystkich stron
                filename:     'Bilet_Teatr_Jura.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opcje).from(el).save().then(() => {
                el.style.display = 'none'; // Ukrywamy z powrotem
            });
        }
    </script>
</body>
</html>