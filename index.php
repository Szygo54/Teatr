<?php
session_start();
// 1. Dołączamy połączenie z bazą danych
require_once 'database.php';

try {
    // 2. Przygotowujemy zapytanie SQL, żeby pobrać nadchodzące spektakle
    $stmt = $pdo->query("SELECT id, tytul, opis, data_wystawienia, cena FROM Spektakle ORDER BY data_wystawienia ASC");
    $spektakle = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    die("Błąd podczas pobierania spektakli: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Teatr Jura - Repertuar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f4; }
        h1 { color: #333; text-align: center; }
        .kontener-spektakli { display: flex; flex-direction: column; gap: 20px; margin-top: 30px; }
        .karta-spektaklu { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tytul { font-size: 24px; color: #800020; margin: 0 0 10px 0; }
        .cena { font-weight: bold; color: #2e7d32; }
        .przycisk-kup { display: inline-block; background-color: #800020; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
        .przycisk-kup:hover { background-color: #5a0016; }
    </style>
</head>
<body>

    <h1>Witamy w Teatrze Jura</h1>
    <div style="text-align: center; margin-bottom: 20px;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Zalogowano jako: <strong><?= htmlspecialchars($_SESSION['user_imie']) ?></strong> | <a href="wyloguj.php">Wyloguj</a></p>
        <?php else: ?>
            <p><a href="logowanie.php">Zaloguj się</a> | <a href="rejestracja.php">Zarejestruj się</a></p>
        <?php endif; ?>
    </div>
    <p style="text-align: center;">Aktualny repertuar i rezerwacja biletów online</p>

    <div class="kontener-spektakli">
        <?php if (empty($spektakle)): ?>
            <p>Obecnie nie gramy żadnych spektakli. Zapraszamy wkrótce!</p>
        <?php else: ?>
            <?php foreach ($spektakle as $s): ?>
                <div class="karta-spektaklu">
                    <h2 class="tytul"><?= htmlspecialchars($s['tytul']) ?></h2>
                    <p><?= htmlspecialchars($s['opis']) ?></p>
                    <p><strong>Data:</strong> <?= date('d.m.Y H:i', strtotime($s['data_wystawienia'])) ?></p>
                    <p class="cena">Cena biletu: <?= $s['cena'] ?> PLN</p>
                    <a href="wybor_miejsca.php?spektakl_id=<?= $s['id'] ?>" class="przycisk-kup">Kup bilet</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>