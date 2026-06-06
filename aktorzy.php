<?php
session_start();

// Tablica z aktorami - tutaj Szymon może łatwo dodawać i usuwać osoby
$aktorzy = [
    ['imie' => 'Jan Kowalski', 'rola' => 'Aktor dramatyczny'],
    ['imie' => 'Anna Nowak', 'rola' => 'Aktorka charakterystyczna'],
    ['imie' => 'Piotr Zieliński', 'rola' => 'Aktor'],
    ['imie' => 'Katarzyna Wiśniewska', 'rola' => 'Aktorka gościnna'],
    ['imie' => 'Michał Lewandowski', 'rola' => 'Aktor'],
    ['imie' => 'Zofia Szymańska', 'rola' => 'Aktorka'],
    ['imie' => 'Tomasz Kamiński', 'rola' => 'Aktor charakterystyczny'],
    ['imie' => 'Magdalena Dąbrowska', 'rola' => 'Aktorka dramatyczna']
];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zespół Aktorski - Teatr Jura</title>
    <style>
        /* Główne style spójne z resztą strony */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a1a1a; 
            color: #e0e0e0;
            margin: 0; padding: 0; padding-bottom: 80px;
        }

        /* Górny pasek nawigacji */
        .top-bar {
            background-color: #262626; padding: 15px 40px; display: flex;
            justify-content: space-between; align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px;
        }
        .top-bar a {
            color: #aaaaaa; text-decoration: none; margin-left: 20px;
            text-transform: uppercase; font-weight: bold; transition: 0.3s;
        }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 

        .header-sekcja { text-align: center; margin: 40px 20px; }
        
        /* Kontener */
        .kontener-sekcji { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .naglowek-sekcji {
            font-size: 32px; color: #fff; margin-bottom: 30px;
            border-bottom: 2px solid #333; padding-bottom: 15px;
            text-transform: uppercase; letter-spacing: 2px;
        }

        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        .powrot:hover { color: #a4b86c; }

        /* Siatka prostokątnych kart aktorów */
        .siatka-aktorow {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .karta-aktora {
            background: #262626;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .karta-aktora:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }

        /* Prostokątne miejsce na zdjęcie */
        .zdjecie-aktora {
            height: 350px;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-weight: bold;
            letter-spacing: 2px;
            border-bottom: 3px solid #829356;
        }

        .dane-aktora {
            padding: 20px;
        }
        .imie-aktora {
            color: #fff;
            font-size: 22px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .rola-aktora {
            color: #829356;
            font-size: 14px;
            margin: 0;
            letter-spacing: 1px;
        }
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
            <a href="index.php" style="color: #fff;">Strona Główna</a>
            <a href="spektakle.php">Repertuar</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_rola'] === 'admin'): ?>
                    <a href="admin.php" class="link-admin">Panel Admina</a>
                <?php else: ?>
                    <a href="moje_bilety.php">Moje bilety</a>
                <?php endif; ?>
                <a href="wyloguj.php">Wyloguj</a>
            <?php else: ?>
                <a href="logowanie.php">Zaloguj się</a>
                <a href="rejestracja.php" class="link-akcent">Załóż konto</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="header-sekcja">
        <h1 style="color: white; font-size: 40px; margin: 0; text-transform: uppercase; letter-spacing: 3px;">Nasz Zespół</h1>
    </div>

    <div class="kontener-sekcji">
        <a href="index.php" class="powrot">&larr; Wróć na stronę główną</a>
        <h2 class="naglowek-sekcji">Aktorzy</h2>
        
        <div class="siatka-aktorow">
            <?php foreach ($aktorzy as $aktor): ?>
                <div class="karta-aktora">
                    <div class="zdjecie-aktora">ZDJĘCIE</div>
                    <div class="dane-aktora">
                        <h3 class="imie-aktora"><?= htmlspecialchars($aktor['imie']) ?></h3>
                        <p class="rola-aktora"><?= htmlspecialchars($aktor['rola']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>