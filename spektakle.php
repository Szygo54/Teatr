<?php
session_start();
require_once 'database.php';
 
try {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wszystkie Sztuki - Teatr Jura</title>
    <style>
        /* STYLE DLA STOPKI */
        html, body { height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1 0 auto; padding-bottom: 50px; }
        
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 
 
        .kontener-sekcji { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; }
        
        /* Siatka plakatów */
        .siatka-plakatow { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; }
        .karta-sztuki { background: #262626; border-radius: 8px; overflow: hidden; text-decoration: none; transition: transform 0.3s; display: flex; flex-direction: column; }
        .karta-sztuki:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
        
        .mini-plakat { height: 600px; background: #333; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .mini-plakat img { width: 100%; height: 100%; object-fit: cover; }
        
        .mini-tresc { padding: 25px; text-align: center; }
        .mini-tytul { color: white; margin: 0; font-size: 24px; text-transform: uppercase; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
 
        /* --- RESPONSYWNOŚĆ NA TELEFON --- */
        @media (max-width: 768px) {
            /* Pasek nawigacji kaskadowy */
            .top-bar { flex-direction: column; gap: 15px; padding: 10px; text-align: center; font-size: 10px; }
            .top-bar div { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
            .top-bar a { margin-left: 0; font-size: 10px; margin: 5px; }
 
            /* Kontener i nagłówek */
            .kontener-sekcji { margin: 20px auto; padding: 0 15px; }
            .naglowek-sekcji { font-size: 22px; letter-spacing: 1px; margin-bottom: 20px; }
 
            /* Plakaty w siatce 2xN na telefonie */
            .siatka-plakatow { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 15px; 
            }
 
            /* Mniejsze plakaty */
            .mini-plakat { height: 180px; }
            .mini-tresc { padding: 10px; }
            .mini-tytul { font-size: 14px; }
 
            /* Mniejszy guzik powrotu */
            .powrot { font-size: 12px; }
 
            /* Mniejszy footer */
            .stopka-strony { 
                padding: 15px 10px !important; 
                font-size: 11px !important; 
            }
        }
    </style>
</head>
<body>
    <main>
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
                    <a href="moje_bilety.php" class="link-akcent">Moje bilety</a>
                    <?php if (isset($_SESSION['user_rola']) && $_SESSION['user_rola'] === 'admin'): ?><a href="admin.php" class="link-admin">Panel Admina</a><?php endif; ?>
                    <a href="wyloguj.php">Wyloguj</a>
                <?php else: ?>
                    <a href="logowanie.php">Zaloguj się</a>
                <?php endif; ?>
            </div>
        </div>
 
        <div class="kontener-sekcji">
            <a href="index.php" class="powrot">&larr; Wróć na stronę główną</a>
            <h2 class="naglowek-sekcji">Spektakle</h2>
        
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
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>
 