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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zespół Aktorski - Teatr Jura</title>
    <style>
        /* --- NAPRAWIONE STYLE DLA STOPKI I BODY --- */
        html, body { height: 100%; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a1a1a; 
            color: #e0e0e0; 
            /* To sprawia, że stopka spada na dół: */
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }
        
        /* Selektor tagu (bez kropki) */
        main {
            flex: 1 0 auto; 
            padding-bottom: 50px;
        }
        
        /* --- PASEK NAWIGACJI --- */
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 
 
        /* --- GÓRA STRONY --- */
        .kontener-sekcji { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        
        /* PRZYWRÓCONY GUZIK POWROTU (zgodny z resztą strony) */
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; transition: 0.3s; }
        .powrot:hover { color: #a4b86c; }
 
        /* --- EDYTORIALOWY STYL AKTORÓW --- */
        .siatka-aktorow { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
            gap: 60px 40px; 
        }
        
        .karta-aktora { 
            text-align: left; 
            cursor: pointer;
        }
 
        /* Zdjęcie (teraz z zaokrąglonymi rogami) */
        .zdjecie-aktora { 
            height: 380px; 
            background: #111; 
            overflow: hidden; 
            margin-bottom: 15px; 
            position: relative;
            border-radius: 12px; 
        }
        
        .zdjecie-aktora img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            display: block; 
            filter: grayscale(100%); 
            transition: transform 0.6s ease, filter 0.6s ease; 
        }
 
        /* Interakcja (Hover) */
        .karta-aktora:hover .zdjecie-aktora img { 
            transform: scale(1.05); 
            filter: grayscale(0%); 
        }
 
        /* Tekst pod zdjęciem */
        .dane-aktora { 
            padding: 0 5px; 
        }
        
        .imie-aktora { 
            color: #e0e0e0; 
            font-size: 18px; 
            font-weight: 300; 
            margin: 0 0 5px 0; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            transition: color 0.3s;
        }
        
        .rola-aktora { 
            color: #829356; 
            font-size: 11px; 
            margin: 0; 
            letter-spacing: 3px; 
            text-transform: uppercase; 
        }
 
        .karta-aktora:hover .imie-aktora {
            color: #fff; 
        }
 
        /* --- RESPONSYWNOŚĆ NA TELEFON --- */
        @media (max-width: 768px) {
            /* Pasek nawigacji kaskadowy */
            .top-bar { flex-direction: column; gap: 15px; padding: 10px; text-align: center; font-size: 10px; }
            .top-bar div { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
            .top-bar a { margin-left: 0; font-size: 10px; margin: 5px; }
 
            /* Kontener i nagłówek */
            .kontener-sekcji { margin: 20px auto; padding: 0 15px; }
            .naglowek-sekcji { font-size: 22px; letter-spacing: 1px; margin-bottom: 20px; }
 
            /* Aktorzy - 2 kolumny na telefonach */
            .siatka-aktorow { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 20px 15px; 
            }
 
            /* Mniejsze zdjęcia */
            .zdjecie-aktora { height: 180px; border-radius: 8px; }
 
            /* Mniejszy tekst */
            .imie-aktora { font-size: 14px; letter-spacing: 0.5px; }
            .rola-aktora { font-size: 9px; letter-spacing: 2px; }
 
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
    </main>
    <?php include 'footer.php'; ?>            
</body>
</html>