<?php
session_start();
require_once 'database.php';

// Wymuszamy logowanie
if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

// Zmieniamy z spektakl_id na termin_id
if (!isset($_GET['termin_id'])) {
    die("Błąd: Nie wybrano terminu spektaklu.");
}

$termin_id = (int)$_GET['termin_id'];

try {
    // 1. Pobieramy informacje o spektaklu i terminie przez JOIN
    $stmt = $pdo->prepare("SELECT s.tytul, s.cena, t.data_wystawienia, t.spektakl_id 
                           FROM Terminy t 
                           JOIN Spektakle s ON t.spektakl_id = s.id 
                           WHERE t.id = ?");
    $stmt->execute([$termin_id]);
    $spektakl = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$spektakl) die("Taki termin nie istnieje.");

    // 2. Pobieramy WSZYSTKIE miejsca
    $stmtMiejsca = $pdo->query("SELECT id, rzad, numer FROM Miejsca ORDER BY rzad, numer");
    $wszystkie_miejsca = $stmtMiejsca->fetchAll(PDO::FETCH_ASSOC);

    // 3. Pobieramy ZAJĘTE miejsca DLA TEGO KONKRETNEGO TERMINU
    $stmtZajete = $pdo->prepare("SELECT miejsce_id FROM Rezerwacje WHERE termin_id = ?");
    $stmtZajete->execute([$termin_id]);
    $zajete_id = $stmtZajete->fetchAll(PDO::FETCH_COLUMN);

} catch (\PDOException $e) {
    die("Błąd bazy: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wybierz miejsca - Teatr Jura</title>
    <style>
        /* KLUCZOWE STYLE DLA STOPKI */
        html, body { 
            height: 100%; 
            margin: 0; 
            padding: 0; 
        }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            text-align: center; 
            background-color: #1a1a1a; 
            color: #e0e0e0; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1 0 auto; /* Pozwala kontenerowi rosnąć i zepchnąć stopkę w dół */
            padding: 40px 20px;
        }
        /* KONIEC STYLÓW DLA STOPKI */

        h2 { font-weight: 300; letter-spacing: 2px; margin-bottom: 5px; text-transform: uppercase; }
        .spektakl-info { color: #aaaaaa; margin-bottom: 40px; font-size: 15px; }
        .cena-akcent { color: #829356; font-weight: bold; }
        .logo-img { width: 100%; max-width: 120px; height: auto; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto; }
        .scena { background-color: #333; height: 45px; width: 100%; max-width: 850px; margin: 0 auto 40px auto; border-radius: 5px 5px 20px 20px; display: flex; justify-content: center; align-items: center; color: #888; font-weight: bold; font-size: 18px; letter-spacing: 8px; box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
        .sala { display: grid; grid-template-columns: repeat(12, 1fr); gap: 12px; max-width: 850px; margin: 0 auto; }
        .fotel-label { display: flex; align-items: center; justify-content: center; height: 50px; background-color:rgb(231, 229, 222); color: #1a1a1a; border-radius: 8px 8px 4px 4px; cursor: pointer; font-size: 12px; font-weight: bold; transition: 0.2s; }
        .fotel-label:hover { transform: scale(1.1); background-color: #d1ccbc; }
        .fotel-checkbox { display: none; }
        .fotel-checkbox:checked + .fotel-label { background-color: #829356; color: #ffffff; box-shadow: 0 0 12px rgba(130, 147, 86, 0.4); transform: scale(1.1); }
        .fotel-zajety { background-color: #9e4747; color: #e0e0e0; cursor: not-allowed; opacity: 0.8; }
        .przycisk-koszyk { margin-top: 30px; padding: 16px 45px; background-color: #829356; color: #ffffff; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
    </style>
</head>
<body>

    <main>
        <img src="zdjecia/logo.png" alt="Logo Teatr Jura" class="logo-img">
        <h2><?= htmlspecialchars($spektakl['tytul']) ?></h2>
        <p class="spektakl-info">
            Data: <?= date('d.m.Y H:i', strtotime($spektakl['data_wystawienia'])) ?> | 
            Bilet: <span class="cena-akcent"><?= htmlspecialchars($spektakl['cena']) ?> PLN</span>
        </p>

        <div class="scena">SCENA</div>

        <form action="koszyk.php" method="POST" id="formularz-rezerwacji">
            <input type="hidden" name="termin_id" value="<?= $termin_id ?>">
            
            <div class="sala">
                <?php foreach ($wszystkie_miejsca as $m): ?>
                    <?php $czy_zajete = in_array($m['id'], $zajete_id); ?>
                    <div>
                        <?php if ($czy_zajete): ?>
                            <div class="fotel-label fotel-zajety">R<?= $m['rzad'] ?><br>M<?= $m['numer'] ?></div>
                        <?php else: ?>
                            <input type="checkbox" name="wybrane_miejsca[]" value="<?= $m['id'] ?>" id="miejsce_<?= $m['id'] ?>" class="fotel-checkbox">
                            <label for="miejsce_<?= $m['id'] ?>" class="fotel-label">R<?= $m['rzad'] ?><br>M<?= $m['numer'] ?></label>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="przycisk-koszyk">Przejdź do podsumowania</button>
        </form>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>