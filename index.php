<?php
session_start();
require_once 'database.php';

try {
    // 1. Zmieniający się spektakl (Hero) - JOIN z tabelą Terminy
    $stmtPolecany = $pdo->query("SELECT s.id, s.tytul, s.opis, t.data_wystawienia, s.cena, s.plakat 
                                 FROM Spektakle s 
                                 JOIN Terminy t ON s.id = t.spektakl_id 
                                 ORDER BY RAND() LIMIT 1");
    $polecany = $stmtPolecany->fetch(PDO::FETCH_ASSOC);

    // 2. Skrócony harmonogram - pobieramy z tabeli Terminy połączonej ze Spektaklami
    $stmtHarmonogram = $pdo->query("SELECT t.id AS termin_id, s.tytul, t.data_wystawienia, s.id AS spektakl_id 
                                     FROM Terminy t 
                                     JOIN Spektakle s ON t.spektakl_id = s.id 
                                     ORDER BY t.data_wystawienia ASC LIMIT 4");
    $harmonogram = $stmtHarmonogram->fetchAll(PDO::FETCH_ASSOC);

    // 3. Spis sztuk do kafelków z plakatami (ograniczone do pierwszych 4)
    $stmtSztuki = $pdo->query("SELECT id, tytul, opis, plakat FROM Spektakle LIMIT 4");
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
        body { font-family: 'Segoe UI', sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; padding: 0; padding-bottom: 80px; }
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        
        .header-sekcja { text-align: center; margin: 20px 20px 40px 20px; }
        .podtytul { color: #aaaaaa; text-transform: uppercase; letter-spacing: 4px; font-size: 14px; margin-top: 5px; }
        .logo-teatru { max-width: 120px; height: auto; margin-bottom: 5px; }

        .kontener-sekcji { max-width: 1200px; margin: 0 auto 60px auto; padding: 0 20px; }
        .naglowek-sekcji { margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .naglowek-sekcji a { font-size: 28px; color: #fff; text-decoration: none; transition: 0.3s; }
        .naglowek-sekcji a:hover { color: #829356; }

        /* HERO */
        .hero-karta { display: block; text-decoration: none; background: #333; border-radius: 8px; overflow: hidden; position: relative; box-shadow: 0 15px 35px rgba(0,0,0,0.5); transition: 0.3s; }
        .hero-karta:hover { transform: translateY(-5px); }
        .hero-plakat { height: 450px; background: #2a2a2a; display: flex; align-items: center; justify-content: center; }
        .hero-plakat img { height: 100%; width: 100%; object-fit: cover; }
        .hero-tresc { position: absolute; bottom: 0; left: 0; width: 100%; padding: 40px 30px; background: linear-gradient(transparent, rgba(0,0,0,0.95)); box-sizing: border-box; }
        .hero-tytul { font-size: 42px; color: white; margin: 0 0 10px 0; text-transform: uppercase; }
        .hero-info { color: #ccc; font-size: 16px; }

        /* HARMONOGRAM */
        .wiersz-spektaklu { display: flex; align-items: center; background: #262626; border-radius: 8px; padding: 20px 30px; margin-bottom: 15px; text-decoration: none; transition: 0.3s; }
        .wiersz-spektaklu:hover { background: #2f2f2f; }
        .w-data { width: 15%; }
        .w-dzien { font-size: 32px; font-weight: bold; color: #829356; line-height: 1; }
        .w-miesiac { font-size: 14px; color: #aaa; text-transform: uppercase; }
        .w-czas { width: 15%; font-size: 18px; color: #ccc; }
        .w-tytul { width: 45%; }
        .w-tytul-tekst { font-size: 22px; color: #fff; font-weight: bold; margin: 0; text-transform: uppercase; }
        .w-akcja { width: 25%; text-align: right; }
        .btn-kup { display: inline-block; color: #829356; border: 2px solid #829356; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; transition: 0.3s; }
        .wiersz-spektaklu:hover .btn-kup { background: #829356; color: white; }

        /* PLAKATY */
        .siatka-plakatow { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .karta-sztuki { background: #262626; border-radius: 8px; overflow: hidden; text-decoration: none; transition: 0.3s; display: block; }
        .karta-sztuki:hover { transform: scale(1.03); }
        .mini-plakat { height: 400px; background: #333; }
        .mini-plakat img { width: 100%; height: 100%; object-fit: cover; }
        .mini-tresc { padding: 20px; text-align: center; }
        .mini-tytul { color: white; margin: 0; font-size: 20px; text-transform: uppercase; }

        /* AKTORZY */
        .siatka-aktorow-index { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; }
        .karta-aktora-index { background: #262626; border-radius: 8px; overflow: hidden; text-align: center; }
        .zdjecie-aktora-index { height: 300px; background: #333; display: flex; align-items: center; justify-content: center; color: #555; border-bottom: 3px solid #829356; }
        .dane-aktora-index { padding: 15px; }
        .imie-aktora-index { color: #fff; font-size: 20px; margin: 0 0 5px 0; text-transform: uppercase; }
        .rola-aktora-index { color: #829356; font-size: 13px; margin: 0; }
    </style>
</head>

<body>

    <div class="top-bar">
        <div><?php if (isset($_SESSION['user_id'])): ?> Witaj, <strong><?= htmlspecialchars($_SESSION['user_imie']) ?></strong><?php endif; ?></div>
        <div>
            <a href="kalendarium.php">Kalendarium</a>
            <a href="spektakle.php">Wszystkie Sztuki</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="moje_bilety.php" style="color: #829356;">Moje bilety</a> 
                
                <?php if ($_SESSION['user_rola'] === 'admin'): ?><a href="admin.php">Panel Admina</a><?php endif; ?>
                <a href="wyloguj.php">Wyloguj</a>
            <?php else: ?>
                <a href="logowanie.php">Zaloguj</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="header-sekcja">
        <img src="zdjecia/logo.png" alt="Logo Teatr Jura" class="logo-teatru">
        <h1 style="color: white; font-size: 48px; margin: 0; text-transform: uppercase; letter-spacing: 5px;">Teatr Jura</h1>
        <div class="podtytul">Aktualny repertuar i rezerwacja biletów online</div>
    </div>

    <div class="kontener-sekcji">
        <?php if ($polecany): ?>
            <a href="spektakl.php?id=<?= $polecany['id'] ?>" class="hero-karta">
                <div class="hero-plakat"><img src="<?= htmlspecialchars($polecany['plakat']) ?>" alt="Plakat"></div>
                <div class="hero-tresc">
                    <h2 class="hero-tytul"><?= htmlspecialchars($polecany['tytul']) ?></h2>
                    <div class="hero-info"><?= date('d.m.Y', strtotime($polecany['data_wystawienia'])) ?></div>
                </div>
            </a>
        <?php endif; ?>
    </div>

    <div class="kontener-sekcji">
        <h2 class="naglowek-sekcji"><a href="kalendarium.php">Kalendarium</a></h2>
        <?php foreach ($harmonogram as $s): 
            $ts = strtotime($s['data_wystawienia']);
        ?>
            <a href="spektakl.php?id=<?= $s['spektakl_id'] ?>" class="wiersz-spektaklu">
                <div class="w-data"><div class="w-dzien"><?= date('d', $ts) ?></div><div class="w-miesiac"><?= polskiMiesiac(date('n', $ts)) ?></div></div>
                <div class="w-czas"><?= date('H:i', $ts) ?></div>
                <div class="w-tytul"><h3 class="w-tytul-tekst"><?= htmlspecialchars($s['tytul']) ?></h3></div>
                <div class="w-akcja"><object><a href="wybor_miejsca.php?termin_id=<?= $s['termin_id'] ?>" class="btn-kup">Kup bilet</a></object></div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="kontener-sekcji">
        <h2 class="naglowek-sekcji"><a href="spektakle.php">Wszystkie Sztuki</a></h2>
        <div class="siatka-plakatow">
            <?php foreach ($sztuki as $sztuka): ?>
                <a href="spektakl.php?id=<?= $sztuka['id'] ?>" class="karta-sztuki">
                    <div class="mini-plakat"><img src="<?= htmlspecialchars($sztuka['plakat']) ?>" alt="Plakat"></div>
                    <div class="mini-tresc"><h3 class="mini-tytul"><?= htmlspecialchars($sztuka['tytul']) ?></h3></div>
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