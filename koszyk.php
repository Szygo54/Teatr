<?php
session_start();
require_once 'database.php';

// Wymuszamy logowanie
if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

// Zmieniamy logikę na obsługę termin_id
if (!isset($_POST['wybrane_miejsca']) || empty($_POST['wybrane_miejsca'])) {
    $t_id = isset($_POST['termin_id']) ? (int)$_POST['termin_id'] : 0;
    if ($t_id > 0) {
        header("Location: wybor_miejsca.php?termin_id=" . $t_id);
    } else {
        header("Location: index.php");
    }
    exit;
}

$termin_id = (int)$_POST['termin_id'];
$wybrane_miejsca = $_POST['wybrane_miejsca'];

try {
    // NAPRAWA ZAPYTANIA: Pobieramy tytuł i cenę przez JOIN z Terminami
    $stmt = $pdo->prepare("SELECT s.tytul, s.cena 
                           FROM Spektakle s 
                           JOIN Terminy t ON s.id = t.spektakl_id 
                           WHERE t.id = ?");
    $stmt->execute([$termin_id]);
    $spektakl = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$spektakl) die("Błąd: Nie znaleziono danych spektaklu.");

    $ilosc_biletow = count($wybrane_miejsca);
    $laczna_kwota = $ilosc_biletow * $spektakl['cena'];

    $placeholdery = str_repeat('?,', count($wybrane_miejsca) - 1) . '?';
    $stmtM = $pdo->prepare("SELECT rzad, numer FROM Miejsca WHERE id IN ($placeholdery)");
    $stmtM->execute($wybrane_miejsca);
    $miejsca_info = $stmtM->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Koszyk - Teatr Jura</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 10px 20px; box-sizing: border-box; }
        .logo-img { width: 100%; max-width: 120px; margin-bottom: 30px; }
        .podsumowanie { background-color: #262626; padding: 40px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); text-align: center; }
        h2 { font-weight: 300; letter-spacing: 2px; margin-top: 0; text-transform: uppercase; }
        h3 { color: #aaaaaa; font-weight: normal; margin-bottom: 20px; }
        .lista-miejsc { list-style: none; padding: 0; text-align: left; background-color: #1a1a1a; border-radius: 5px; padding: 15px; border: 1px solid #333; }
        .lista-miejsc li { padding: 8px 0; border-bottom: 1px dashed #444; display: flex; justify-content: space-between; }
        .kwota-akcent { color: #829356; font-size: 24px; margin: 25px 0; font-weight: bold; }
        .przycisk-zaplac { background-color: #829356; color: #ffffff; padding: 16px 30px; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; width: 100%; text-transform: uppercase; transition: 0.3s; }
        .przycisk-zaplac:hover { background-color: #6a7944; }

        /* --- NOWE STYLE DLA WYBORU PŁATNOŚCI --- */
        .sekcja-platnosci { margin: 30px 0; text-align: center; }
        .tytul-platnosci { font-size: 14px; font-weight: bold; color: #aaaaaa; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;}
        .metody-kontener { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .metoda-radio { cursor: pointer; display: inline-block; }
        .metoda-radio input[type="radio"] { display: none; }
        .metoda-radio .znacznik { display: block; background-color: #333333; color: #cccccc; padding: 12px 20px; border: 2px solid #444444; border-radius: 5px; font-size: 15px; font-weight: bold; transition: all 0.3s ease; }
        .metoda-radio:hover .znacznik { border-color: #829356; }
        .metoda-radio input[type="radio"]:checked + .znacznik { background-color: #829356; border-color: #829356; color: #ffffff; box-shadow: 0 4px 10px rgba(130, 147, 86, 0.4); }
    </style>
</head>
<body>

    <img src="zdjecia/logo.png" alt="Logo Teatr Jura" class="logo-img">

    <div class="podsumowanie">
        <h2>Kasa Biletowa</h2>
        <h3><?= htmlspecialchars($spektakl['tytul']) ?></h3>
        
        <p style="text-align: left; color: #aaaaaa;">Wybrane miejsca (<?= $ilosc_biletow ?>):</p>
        <ul class="lista-miejsc">
            <?php foreach ($miejsca_info as $m): ?>
                <li><span>Rząd <?= htmlspecialchars($m['rzad']) ?></span> <strong>Miejsce <?= htmlspecialchars($m['numer']) ?></strong></li>
            <?php endforeach; ?>
        </ul>
        
        <div class="kwota-akcent">Do zapłaty: <?= number_format($laczna_kwota, 2) ?> PLN</div>

        <form method="POST" action="platnosc.php">
            <input type="hidden" name="termin_id" value="<?= $termin_id ?>">
            <?php foreach ($wybrane_miejsca as $m_id): ?>
                <input type="hidden" name="miejsca_do_zapisu[]" value="<?= htmlspecialchars($m_id) ?>">
            <?php endforeach; ?>
            
            <div class="sekcja-platnosci">
                <p class="tytul-platnosci">Wybierz metodę płatności:</p>
                <div class="metody-kontener">
                    <label class="metoda-radio">
                        <input type="radio" name="metoda" value="blik" required checked>
                        <span class="znacznik">BLIK</span>
                    </label>
                    
                    <label class="metoda-radio">
                        <input type="radio" name="metoda" value="karta">
                        <span class="znacznik">Karta Płatnicza</span>
                    </label>
                    
                    <label class="metoda-radio">
                        <input type="radio" name="metoda" value="przelew">
                        <span class="znacznik">Szybki przelew</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" name="inicjuj_platnosc" class="przycisk-zaplac">Przejdź do płatności</button>
        </form>
    </div>
</body>
</html>