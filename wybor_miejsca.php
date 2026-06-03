<?php
session_start();
require_once 'database.php';

// Wymuszamy logowanie - bez konta nie kupisz biletu
if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

if (!isset($_GET['spektakl_id'])) {
    die("Błąd: Nie wybrano spektaklu.");
}

$spektakl_id = (int)$_GET['spektakl_id'];

try {
    // 1. Pobieramy informacje o wybranym spektaklu
    $stmt = $pdo->prepare("SELECT tytul, cena, data_wystawienia FROM Spektakle WHERE id = ?");
    $stmt->execute([$spektakl_id]);
    $spektakl = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$spektakl) die("Taki spektakl nie istnieje.");

    // 2. Pobieramy WSZYSTKIE miejsca
    $stmtMiejsca = $pdo->query("SELECT id, rzad, numer FROM Miejsca ORDER BY rzad, numer");
    $wszystkie_miejsca = $stmtMiejsca->fetchAll(PDO::FETCH_ASSOC);

    // 3. Pobieramy ZAJĘTE miejsca dla tego spektaklu
    $stmtZajete = $pdo->prepare("SELECT miejsce_id FROM Rezerwacje WHERE spektakl_id = ?");
    $stmtZajete->execute([$spektakl_id]);
    // Tworzymy prostą tablicę zawierającą tylko ID zajętych miejsc
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
        body { font-family: Arial, sans-serif; text-align: center; background-color: #f4f4f4; }
        .scena { background: #333; color: white; padding: 10px; margin: 20px auto; width: 80%; border-radius: 5px; font-weight: bold; letter-spacing: 5px;}
        
        /* Magia CSS Grid do ułożenia 12 miejsc w 8 rzędach */
        .sala { 
            display: grid; 
            grid-template-columns: repeat(12, 1fr); 
            gap: 10px; 
            max-width: 800px; 
            margin: 0 auto; 
        }
        
        /* Stylowanie checkboxów udających fotele */
        .fotel-label {
            display: block;
            padding: 15px 5px;
            background-color: #4CAF50; /* Zielony - wolne */
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            user-select: none;
        }
        .fotel-label:hover { background-color: #45a049; }
        
        /* Ukrywamy domyślne pole checkboxa */
        .fotel-checkbox { display: none; }
        
        /* Zmiana koloru, gdy użytkownik zaznaczy wolne miejsce */
        .fotel-checkbox:checked + .fotel-label { background-color: #2196F3; } /* Niebieski - wybrane */
        
        /* Styl dla miejsc zajętych przez innych */
        .fotel-zajety { background-color: #f44336; color: white; cursor: not-allowed; opacity: 0.7; }
        
        .przycisk-koszyk { margin-top: 30px; padding: 15px 30px; background-color: #800020; color: white; border: none; border-radius: 5px; font-size: 18px; cursor: pointer; }
    </style>
</head>
<body>

    <h2>Spektakl: <?= htmlspecialchars($spektakl['tytul']) ?></h2>
    <p>Data: <?= $spektakl['data_wystawienia'] ?> | Cena za bilet: <?= $spektakl['cena'] ?> PLN</p>

    <div class="scena">SCENA</div>

    <form action="koszyk.php" method="POST">
        <input type="hidden" name="spektakl_id" value="<?= $spektakl_id ?>">
        
        <div class="sala">
            <?php foreach ($wszystkie_miejsca as $m): ?>
                <?php 
                    // Sprawdzamy czy to konkretne ID znajduje się w tablicy zajętych
                    $czy_zajete = in_array($m['id'], $zajete_id); 
                ?>
                <div>
                    <?php if ($czy_zajete): ?>
                        <div class="fotel-label fotel-zajety">R<?= $m['rzad'] ?> M<?= $m['numer'] ?></div>
                    <?php else: ?>
                        <input type="checkbox" name="wybrane_miejsca[]" value="<?= $m['id'] ?>" id="miejsce_<?= $m['id'] ?>" class="fotel-checkbox">
                        <label for="miejsce_<?= $m['id'] ?>" class="fotel-label">R<?= $m['rzad'] ?> M<?= $m['numer'] ?></label>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="przycisk-koszyk">Przejdź do podsumowania</button>
    </form>

</body>
</html>