<?php
session_start();
require_once 'database.php';

try {
    // NAPRAWIONE ZAPYTANIE: Dodano 'plakat' do listy pobieranych pól
    $stmt = $pdo->query("SELECT id, tytul, opis, plakat FROM Spektakle GROUP BY tytul");
    $sztuki = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wszystkie Sztuki - Teatr Jura</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; padding: 0; padding-bottom: 80px; }
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .kontener-sekcji { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; }
        
        /* Siatka plakatów */
        .siatka-plakatow { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; }
        .karta-sztuki { background: #262626; border-radius: 8px; overflow: hidden; text-decoration: none; transition: transform 0.3s; display: flex; flex-direction: column; }
        .karta-sztuki:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
        
        /* NAPRAWIONE: Wyświetlanie obrazka */
        .mini-plakat { height: 600px; background: #333; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .mini-plakat img { width: 100%; height: 100%; object-fit: cover; }
        
        .mini-tresc { padding: 25px; text-align: center; }
        .mini-tytul { color: white; margin: 0; font-size: 24px; text-transform: uppercase; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div><?php if (isset($_SESSION['user_id'])) echo "Witaj, <strong style='color: white;'>" . htmlspecialchars($_SESSION['user_imie']) . "</strong>"; ?></div>
        <div>
            <a href="index.php" style="color: #fff;">Strona Główna</a>
            <a href="kalendarium.php">Kalendarium</a>
        </div>
    </div>

    <div class="kontener-sekcji">
        <a href="index.php" class="powrot">&larr; Wróć na stronę główną</a>
        <h2 class="naglowek-sekcji">Wszystkie Spektakle</h2>
        
        <div class="siatka-plakatow">
            <?php foreach ($sztuki as $sztuka): ?>
                <a href="spektakl.php?id=<?= $sztuka['id'] ?>" class="karta-sztuki">
                    <div class="mini-plakat">
                        <img src="<?= htmlspecialchars($sztuka['plakat']) ?>" alt="<?= htmlspecialchars($sztuka['tytul']) ?>">
                    </div>
                    <div class="mini-tresc">
                        <h3 class="mini-tytul"><?= htmlspecialchars($sztuka['tytul']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>