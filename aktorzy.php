<?php
session_start();
require_once 'database.php';

try {
    // Pobieramy wszystkich aktorów z bazy danych
    $stmt = $pdo->query("SELECT imie_nazwisko, zdjecie, specjalizacja FROM Aktorzy");
    $aktorzy = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zespół Aktorski - Teatr Jura</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; padding: 0; padding-bottom: 80px; }
        
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 

        .kontener-sekcji { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        .powrot:hover { color: #a4b86c; }

        .siatka-aktorow { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; }
        .karta-aktora { background: #262626; border-radius: 8px; overflow: hidden; text-align: center; transition: transform 0.3s, box-shadow 0.3s; }
        .karta-aktora:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.4); }

        /* Zdjęcie wypełniające prostokąt */
        .zdjecie-aktora { height: 350px; background: #333; overflow: hidden; border-bottom: 3px solid #829356; }
        .zdjecie-aktora img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .dane-aktora { padding: 20px; }
        .imie-aktora { color: #fff; font-size: 20px; margin: 0 0 5px 0; text-transform: uppercase; }
        .rola-aktora { color: #829356; font-size: 14px; margin: 0; letter-spacing: 1px; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                Witaj, <strong style="color: #ffffff;"><?= htmlspecialchars($_SESSION['user_imie']) ?></strong>
            <?php endif; ?>
        </div>
        <div>
            <a href="index.php">Strona Główna</a>
            <a href="spektakle.php">Repertuar</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_rola'] === 'admin'): ?><a href="admin.php" class="link-admin">Panel Admina</a><?php endif; ?>
                <a href="wyloguj.php">Wyloguj</a>
            <?php else: ?>
                <a href="logowanie.php">Zaloguj się</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="kontener-sekcji">
        <a href="index.php" class="powrot">&larr; Wróć na stronę główną</a>
        <h2 class="naglowek-sekcji">Nasz Zespół</h2>
        
        <div class="siatka-aktorow">
            <?php foreach ($aktorzy as $aktor): ?>
                <div class="karta-aktora">
                    <div class="zdjecie-aktora">
                        <img src="<?= htmlspecialchars($aktor['zdjecie']) ?>" alt="<?= htmlspecialchars($aktor['imie_nazwisko']) ?>">
                    </div>
                    <div class="dane-aktora">
                        <h3 class="imie-aktora"><?= htmlspecialchars($aktor['imie_nazwisko']) ?></h3>
                        <p class="rola-aktora"><?= htmlspecialchars($aktor['specjalizacja']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>