<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

if (!isset($_POST['wybrane_miejsca']) || empty($_POST['wybrane_miejsca'])) {
    die("Nie wybrano żadnych miejsc! <a href='index.php'>Wróć</a>");
}

$spektakl_id = (int)$_POST['spektakl_id'];
$wybrane_miejsca = $_POST['wybrane_miejsca'];

try {
    $stmt = $pdo->prepare("SELECT tytul, cena FROM Spektakle WHERE id = ?");
    $stmt->execute([$spektakl_id]);
    $spektakl = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
        body { font-family: Arial, sans-serif; text-align: center; margin: 50px; background-color: #f4f4f4;}
        .podsumowanie { background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .przycisk-zaplac { background-color: #2e7d32; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 18px; cursor: pointer; width: 100%; margin-top: 20px;}
        .przycisk-zaplac:hover { background-color: #1b5e20; }
        .metody-platnosci { text-align: left; margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .metody-platnosci label { display: block; margin-bottom: 10px; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>

    <div class="podsumowanie">
        <h2>Kasa Biletowa</h2>
        <h3><?= htmlspecialchars($spektakl['tytul']) ?></h3>
        <hr>
        <p>Wybrane miejsca (ilość: <?= $ilosc_biletow ?>):</p>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($miejsca_info as $m): ?>
                <li><strong>Rząd <?= $m['rzad'] ?>, Miejsce <?= $m['numer'] ?></strong></li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <h3 style="color: #800020;">Do zapłaty: <?= number_format($laczna_kwota, 2) ?> PLN</h3>

        <form method="POST" action="platnosc.php">
            <input type="hidden" name="spektakl_id" value="<?= $spektakl_id ?>">
            <?php foreach ($wybrane_miejsca as $m_id): ?>
                <input type="hidden" name="miejsca_do_zapisu[]" value="<?= htmlspecialchars($m_id) ?>">
            <?php endforeach; ?>
            
            <div class="metody-platnosci">
                <strong>Wybierz metodę płatności:</strong><br><br>
                <label><input type="radio" name="metoda" value="blik" required> BLIK</label>
                <label><input type="radio" name="metoda" value="karta"> Karta Płatnicza (Visa/Mastercard)</label>
                <label><input type="radio" name="metoda" value="przelew"> Przelewy24 (Szybki przelew)</label>
            </div>
            
            <button type="submit" name="inicjuj_platnosc" class="przycisk-zaplac">Przejdź do płatności</button>
        </form>
    </div>

</body>
</html>