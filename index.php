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
        /* Główne tło i czcionka */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a1a1a; 
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            padding-bottom: 50px;
        }

        /* Elegancki górny pasek nawigacji */
        .top-bar {
            background-color: #262626;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            font-size: 14px;
        }

        .top-bar a {
            color: #aaaaaa;
            text-decoration: none;
            margin-left: 20px;
            transition: color 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } /* Ceglasty dla admina */

        /* Sekcja nagłówka z logo */
        .header-sekcja {
            text-align: center;
            margin: 50px 20px 40px 20px;
        }

        .logo-img { 
            width: 100%;
            max-width: 320px; 
            height: auto;
            margin-bottom: 20px; 
        }

        .podtytul {
            color: #aaaaaa;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 14px;
        }

        /* Siatka spektakli (Kafelki) */
        .kontener-spektakli { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); 
            gap: 40px; 
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Pojedyncza karta spektaklu */
        .karta-spektaklu { 
            background: #262626; 
            border-radius: 8px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.4); 
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Żeby rogi plakatu nie wystawały */
            transition: transform 0.3s;
        }

        .karta-spektaklu:hover {
            transform: translateY(-5px);
        }

        /* Miejsce na plakat */
        .plakat-miejsce {
            background-color: #333333;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-weight: bold;
            letter-spacing: 2px;
            position: relative;
        }

        /* Klasa docelowa dla gotowego plakatu */
        .plakat-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Dolna część karty (teksty) */
        .tresc-karty {
            padding: 25px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .tytul { 
            font-size: 24px; 
            color: #ffffff; 
            margin: 0 0 15px 0; 
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 400;
        }

        .opis { color: #aaaaaa; font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        
        .szczegoly { margin-bottom: 25px; font-size: 15px; }
        .szczegoly strong { color: #ffffff; }
        
        .cena { 
            font-weight: bold; 
            color: #829356; /* Ziemista oliwka */
            font-size: 18px;
            margin-bottom: 20px;
        }

        /* Przycisk zakupu */
        .przycisk-kup { 
            display: block; 
            background-color: #829356; 
            color: white; 
            padding: 15px; 
            text-align: center;
            text-decoration: none; 
            border-radius: 5px; 
            margin-top: auto; /* Zawsze spycha przycisk na sam dół karty */
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s;
        }
        
        .przycisk-kup:hover { background-color: #6a7944; }

        .brak-spektakli { text-align: center; color: #aaaaaa; font-size: 18px; width: 100%; }
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
        <img src="zdjecia/logo.png?v=<?= time() ?>" alt="Logo Teatr Jura" class="logo-img">
        <div class="podtytul">Aktualny repertuar i rezerwacja biletów online</div>
    </div>

    <div class="kontener-spektakli">
        <?php if (empty($spektakle)): ?>
            <p class="brak-spektakli">Obecnie nie gramy żadnych spektakli. Zapraszamy wkrótce!</p>
        <?php else: ?>
            <?php foreach ($spektakle as $s): ?>
                <div class="karta-spektaklu">
                    
                    <div class="plakat-miejsce">
                        PLAKAT WKRÓTCE
                        </div>

                    <div class="tresc-karty">
                        <h2 class="tytul"><?= htmlspecialchars($s['tytul']) ?></h2>
                        <div class="opis"><?= htmlspecialchars($s['opis']) ?></div>
                        
                        <div class="szczegoly">
                            <strong>Data:</strong> <?= date('d.m.Y H:i', strtotime($s['data_wystawienia'])) ?>
                        </div>
                        
                        <div class="cena">
                            Cena biletu: <?= $s['cena'] ?> PLN
                        </div>
                        
                        <a href="wybor_miejsca.php?spektakl_id=<?= $s['id'] ?>" class="przycisk-kup">Kup bilet</a>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>