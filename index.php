<?php
session_start();
require_once 'database.php';

try {
    // 1. Zmieniający się spektakl (Hero)
    $stmtPolecany = $pdo->query("SELECT id, tytul, opis, data_wystawienia, cena FROM Spektakle ORDER BY RAND() LIMIT 1");
    $polecany = $stmtPolecany->fetch(PDO::FETCH_ASSOC);

    // 2. Skrócony harmonogram (np. tylko 4 najbliższe sztuki na stronę główną)
    $stmtHarmonogram = $pdo->query("SELECT id, tytul, opis, data_wystawienia, cena FROM Spektakle ORDER BY data_wystawienia ASC LIMIT 4");
    $harmonogram = $stmtHarmonogram->fetchAll(PDO::FETCH_ASSOC);

    // 3. Spis sztuk do kafelków z plakatami (pobieramy wszystkie)
    $stmtSztuki = $pdo->query("SELECT id, tytul, opis FROM Spektakle GROUP BY tytul");
    $sztuki = $stmtSztuki->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

function polskiMiesiac($numerMiesiaca) {
    $miesiace = ['', 'Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'];
    return $miesiace[(int)$numerMiesiaca];
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Teatr Jura - Strona Główna</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a1a1a; 
            color: #e0e0e0;
            margin: 0; padding: 0; padding-bottom: 80px;
        }

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

        .header-sekcja { text-align: center; margin: 50px 20px 40px 20px; }
        .podtytul { color: #aaaaaa; text-transform: uppercase; letter-spacing: 4px; font-size: 14px; margin-top: 10px; }

        /* UNIWERSALNY KONTENER */
        .kontener-sekcji { max-width: 1200px; margin: 0 auto 60px auto; padding: 0 20px; }
        
        /* ZMIENIONE: Klikalne nagłówki sekcji */
        .naglowek-sekcji {
            margin-bottom: 30px;
            border-bottom: 2px solid #333; padding-bottom: 15px;
            text-transform: uppercase; letter-spacing: 2px;
        }
        .naglowek-sekcji a {
            font-size: 28px;
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }
        .naglowek-sekcji a:hover {
            color: #829356;
        }

        /* 1. HERO BANNER */
        .hero-karta {
            display: block; text-decoration: none; background: #333;
            border-radius: 8px; overflow: hidden; position: relative;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5); transition: transform 0.3s;
        }
        .hero-karta:hover { transform: translateY(-5px); }
        .hero-plakat {
            height: 450px; background-color: #2a2a2a; display: flex;
            align-items: center; justify-content: center; color: #555;
            font-size: 24px; letter-spacing: 5px; font-weight: bold;
        }
        .hero-tresc {
            position: absolute; bottom: 0; left: 0; width: 100%; padding: 40px 30px;
            background: linear-gradient(transparent, rgba(0,0,0,0.95) 80%); box-sizing: border-box;
        }
        .hero-etykieta {
            display: inline-block; background: #829356; color: white; padding: 5px 10px;
            font-size: 12px; text-transform: uppercase; border-radius: 3px; margin-bottom: 10px;
        }
        .hero-tytul { font-size: 42px; color: white; margin: 0 0 10px 0; text-transform: uppercase; }
        .hero-info { color: #ccc; font-size: 16px; }

        /* 2. HARMONOGRAM (SKRÓCONY) */
        .wiersz-spektaklu {
            display: flex; align-items: center; background: #262626;
            border-radius: 8px; padding: 20px 30px; margin-bottom: 15px;
            text-decoration: none; transition: 0.3s;
        }
        .wiersz-spektaklu:hover { background: #2f2f2f; }
        .w-data { width: 15%; }
        .w-dzien { font-size: 32px; font-weight: bold; color: #829356; line-height: 1; }
        .w-miesiac { font-size: 14px; color: #aaa; text-transform: uppercase; }
        .w-czas { width: 15%; font-size: 18px; color: #ccc; }
        .w-tytul { width: 45%; }
        .w-tytul-tekst { font-size: 22px; color: #fff; font-weight: bold; margin: 0; text-transform: uppercase; }
        .w-akcja { width: 25%; text-align: right; }
        .btn-kup {
            display: inline-block; color: #829356; border: 2px solid #829356;
            padding: 10px 20px; text-decoration: none; border-radius: 5px;
            font-weight: bold; text-transform: uppercase; transition: 0.3s;
        }
        .wiersz-spektaklu:hover .btn-kup { background: #829356; color: white; }

        /* 3. SPIS SZTUK Z PLAKATAMI (GRID) */
        .siatka-plakatow {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;
        }
        .karta-sztuki {
            background: #262626; border-radius: 8px; overflow: hidden; text-decoration: none;
            transition: 0.3s; display: block;
        }
        .karta-sztuki:hover { transform: scale(1.03); }
        .mini-plakat { height: 350px; background: #333; display: flex; align-items: center; justify-content: center; color: #666; }
        .mini-tresc { padding: 20px; text-align: center; }
        .mini-tytul { color: white; margin: 0; font-size: 20px; text-transform: uppercase; }

        /* SEKCJA AKTORÓW NA GŁÓWNEJ (PROSTOKĄTY - ZATRZYMANE ANIMACJE) */
        .siatka-aktorow-index {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        @media (max-width: 900px) {
            .siatka-aktorow-index { grid-template-columns: repeat(2, 1fr); }
        }
        .karta-aktora-index {
            background: #262626;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
        }
        .zdjecie-aktora-index {
            height: 300px;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-weight: bold;
            letter-spacing: 2px;
            border-bottom: 3px solid #829356;
        }
        .dane-aktora-index { padding: 15px; }
        .imie-aktora-index { color: #fff; font-size: 20px; margin: 0 0 5px 0; text-transform: uppercase; }
        .rola-aktora-index { color: #829356; font-size: 13px; margin: 0; letter-spacing: 1px; }
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
            <a href="kalendarium.php" style="color: #fff;">Kalendarium</a>
            <a href="spektakle.php" style="color: #fff;">Wszystkie Sztuki</a>
            
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
        <h1 style="color: white; font-size: 48px; margin: 0; text-transform: uppercase; letter-spacing: 5px;">Teatr Jura</h1>
        <div class="podtytul">Aktualny repertuar i rezerwacja biletów online</div>
    </div>

    <div class="kontener-sekcji">
        <?php if ($polecany): ?>
            <a href="spektakl.php?id=<?= $polecany['id'] ?>" class="hero-karta">
                <div class="hero-plakat">DUŻY PLAKAT</div>
                <div class="hero-tresc">
                    <div class="hero-etykieta">Polecamy</div>
                    <h2 class="hero-tytul"><?= htmlspecialchars($polecany['tytul']) ?></h2>
                    <div class="hero-info">
                        <?= date('d.m.Y', strtotime($polecany['data_wystawienia'])) ?> | Godz: <?= date('H:i', strtotime($polecany['data_wystawienia'])) ?>
                    </div>
                </div>
            </a>
        <?php endif; ?>
    </div>

    <div class="kontener-sekcji">
        <h2 class="naglowek-sekcji"><a href="kalendarium.php">Kalendarium</a></h2>
        
        <?php foreach ($harmonogram as $s): 
            $timestamp = strtotime($s['data_wystawienia']);
        ?>
            <a href="spektakl.php?id=<?= $s['id'] ?>" class="wiersz-spektaklu">
                <div class="w-data">
                    <div class="w-dzien"><?= date('d', $timestamp) ?></div>
                    <div class="w-miesiac"><?= polskiMiesiac(date('n', $timestamp)) ?></div>
                </div>
                <div class="w-czas"><?= date('H:i', $timestamp) ?></div>
                <div class="w-tytul"><h3 class="w-tytul-tekst"><?= htmlspecialchars($s['tytul']) ?></h3></div>
                <div class="w-akcja">
                    <object><a href="wybor_miejsca.php?spektakl_id=<?= $s['id'] ?>" class="btn-kup">Kup bilet</a></object>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="kontener-sekcji">
        <h2 class="naglowek-sekcji"><a href="spektakle.php">Wszystkie Sztuki na Scenie Monolit</a></h2>
        
        <div class="siatka-plakatow">
            <?php foreach ($sztuki as $sztuka): ?>
                <a href="spektakl.php?id=<?= $sztuka['id'] ?>" class="karta-sztuki">
                    <div class="mini-plakat">PLAKAT</div>
                    <div class="mini-tresc">
                        <h3 class="mini-tytul"><?= htmlspecialchars($sztuka['tytul']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="kontener-sekcji">
        <h2 class="naglowek-sekcji"><a href="aktorzy.php">Poznaj Nasz Zespół</a></h2>
        
        <div class="siatka-aktorow-index">
            <div class="karta-aktora-index">
                <div class="zdjecie-aktora-index">ZDJĘCIE</div>
                <div class="dane-aktora-index">
                    <h3 class="imie-aktora-index">Jan Kowalski</h3>
                    <p class="rola-aktora-index">Aktor dramatyczny</p>
                </div>
            </div>
            <div class="karta-aktora-index">
                <div class="zdjecie-aktora-index">ZDJĘCIE</div>
                <div class="dane-aktora-index">
                    <h3 class="imie-aktora-index">Anna Nowak</h3>
                    <p class="rola-aktora-index">Aktorka charakterystyczna</p>
                </div>
            </div>
            <div class="karta-aktora-index">
                <div class="zdjecie-aktora-index">ZDJĘCIE</div>
                <div class="dane-aktora-index">
                    <h3 class="imie-aktora-index">Piotr Zieliński</h3>
                    <p class="rola-aktora-index">Aktor</p>
                </div>
            </div>
            <div class="karta-aktora-index">
                <div class="zdjecie-aktora-index">ZDJĘCIE</div>
                <div class="dane-aktora-index">
                    <h3 class="imie-aktora-index">Katarzyna Wiśniewska</h3>
                    <p class="rola-aktora-index">Aktorka gościnna</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>