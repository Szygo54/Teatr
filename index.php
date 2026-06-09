<?php
session_start();
require_once 'database.php';

try {
    $stmtPolecany = $pdo->query("SELECT s.id, s.tytul, s.opis, t.data_wystawienia, s.cena, s.plakat 
                                 FROM Spektakle s 
                                 JOIN Terminy t ON s.id = t.spektakl_id 
                                 ORDER BY RAND() LIMIT 1");
    $polecany = $stmtPolecany->fetch(PDO::FETCH_ASSOC);

    $stmtHarmonogram = $pdo->query("SELECT t.id AS termin_id, s.tytul, t.data_wystawienia, s.id AS spektakl_id 
                                     FROM Terminy t 
                                     JOIN Spektakle s ON t.spektakl_id = s.id 
                                     ORDER BY t.data_wystawienia ASC LIMIT 4");
    $harmonogram = $stmtHarmonogram->fetchAll(PDO::FETCH_ASSOC);

    $stmtSztuki = $pdo->query("SELECT id, tytul, opis, plakat FROM Spektakle LIMIT 4");
    $sztuki = $stmtSztuki->fetchAll(PDO::FETCH_ASSOC);

    $stmtAktorzy = $pdo->query("SELECT imie_nazwisko, zdjecie, specjalizacja FROM Aktorzy ORDER BY RAND() LIMIT 4");
    $aktorzy = $stmtAktorzy->fetchAll(PDO::FETCH_ASSOC);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teatr Jura - Strona Główna</title>
    <style>
        html, body { 
            height: 100%; 
            margin: 0; 
            padding: 0; 
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background-color: #1a1a1a; 
            color: #e0e0e0; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }

        main {
            flex: 1 0 auto; 
            padding-bottom: 50px; 
        }
        
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 
        
        .header-sekcja { text-align: center; margin: 20px 20px 40px 20px; }
        .header-sekcja h1 { color: white; font-size: 48px; margin: 0; text-transform: uppercase; letter-spacing: 5px; }
        .podtytul { color: #aaaaaa; text-transform: uppercase; letter-spacing: 4px; font-size: 14px; margin-top: 5px; }
        .logo-teatru { max-width: 120px; height: auto; margin-bottom: 5px; }

        .kontener-sekcji { max-width: 1200px; margin: 0 auto 60px auto; padding: 0 20px; }
        .naglowek-sekcji { margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .naglowek-sekcji a { font-size: 28px; color: #fff; text-decoration: none; transition: 0.3s; }
        .naglowek-sekcji a:hover { color: #829356; }

        .hero-karta { display: block; text-decoration: none; background: #333; border-radius: 8px; overflow: hidden; position: relative; box-shadow: 0 15px 35px rgba(0,0,0,0.5); transition: 0.3s; }
        .hero-karta:hover { transform: translateY(-5px); }
        .hero-plakat { height: 450px; background: #2a2a2a; display: flex; align-items: center; justify-content: center; }
        .hero-plakat img { height: 100%; width: 100%; object-fit: cover; }
        .hero-tresc { position: absolute; bottom: 0; left: 0; width: 100%; padding: 40px 30px; background: linear-gradient(transparent, rgba(0,0,0,0.95)); box-sizing: border-box; }
        .hero-tytul { font-size: 42px; color: white; margin: 0 0 10px 0; text-transform: uppercase; }
        .hero-info { color: #ccc; font-size: 16px; }

        .lista-spektakli { display: flex; flex-direction: column; }
        .wiersz-spektaklu { 
            display: grid; 
            grid-template-columns: 80px 1fr auto; 
            align-items: center; 
            gap: 40px; 
            padding: 25px 0; 
            text-decoration: none; 
            border-bottom: 1px solid #222;
            transition: all 0.3s ease;
        }
        .w-data { text-align: left; }
        .w-dzien { font-size: 32px; font-weight: 300; color: #fff; line-height: 1; margin-bottom: 5px; transition: color 0.3s; }
        .w-miesiac { font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 2px; transition: color 0.3s; }
        .w-info { display: flex; flex-direction: column; gap: 5px; }
        .w-czas { font-size: 13px; color: #666; letter-spacing: 1px; }
        .w-tytul-tekst { font-size: 22px; color: #cecdcd; font-weight: 300; margin: 0; text-transform: uppercase; letter-spacing: 2px; transition: color 0.3s; }
        
        .w-akcja { text-align: right; }
        .btn-kup { display: flex; align-items: center; justify-content: flex-end; color: #666; text-decoration: none; font-weight: normal; text-transform: uppercase; font-size: 12px; letter-spacing: 2px; transition: all 0.3s ease; }
        .btn-kup::after { content: '→'; margin-left: 10px; font-size: 16px; transition: transform 0.3s ease; }

        .wiersz-spektaklu:hover .w-tytul-tekst { color: #fff; }
        .wiersz-spektaklu:hover .w-dzien { color: #829356; }
        .wiersz-spektaklu:hover .btn-kup { color: #829356; }
        .wiersz-spektaklu:hover .btn-kup::after { transform: translateX(8px); color: #829356; }

        .siatka-plakatow { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .karta-sztuki { background: #262626; border-radius: 8px; overflow: hidden; text-decoration: none; transition: 0.3s; display: block; }
        .karta-sztuki:hover { transform: scale(1.03); }
        .mini-plakat { height: 400px; background: #333; }
        .mini-plakat img { width: 100%; height: 100%; object-fit: cover; }
        .mini-tresc { padding: 20px; text-align: center; }
        .mini-tytul { color: white; margin: 0; font-size: 20px; text-transform: uppercase; }

        .siatka-aktorow-index { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; }
        .karta-aktora-index { text-align: left; cursor: pointer; }
        .zdjecie-aktora-index { height: 380px; background: #111; overflow: hidden; margin-bottom: 15px; border-radius: 12px; }
        .zdjecie-aktora-index img { width: 100%; height: 100%; object-fit: cover; display: block; filter: grayscale(100%); transition: transform 0.6s ease, filter 0.6s ease; }
        .karta-aktora-index:hover .zdjecie-aktora-index img { transform: scale(1.05); filter: grayscale(0%); }
        .dane-aktora-index { padding: 0 5px; }
        .imie-aktora-index { color: #e0e0e0; font-size: 18px; font-weight: 300; margin: 0 0 5px 0; text-transform: uppercase; letter-spacing: 1px; transition: color 0.3s; }
        .rola-aktora-index { color: #829356; font-size: 11px; margin: 0; letter-spacing: 3px; text-transform: uppercase; }
        .karta-aktora-index:hover .imie-aktora-index { color: #fff; }


        @media (max-width: 768px) {
            .top-bar { flex-direction: column; gap: 15px; padding: 15px; text-align: center; font-size:10px }
            .top-bar div { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
            .top-bar a { margin-left: 0; font-size:10px}

            .logo-teatru { max-width: 70px; } 
            .header-sekcja h1 { font-size: 28px; letter-spacing: 2px; margin-top: 10px; } 
            .podtytul { font-size: 10px; letter-spacing: 2px; } 
            .hero-tytul { font-size: 26px; }
            .naglowek-sekcji a { font-size: 22px; }

            .wiersz-spektaklu { 
                grid-template-columns: 50px 1fr auto; 
                gap: 10px; 
                text-align: left; 
                padding: 15px 0;
            }
            .w-data { text-align: center; }
            .w-dzien { font-size: 22px; margin-bottom: 2px; }
            .w-miesiac { font-size: 9px; }
            .w-tytul-tekst { font-size: 14px; }
            .w-czas { font-size: 10px; }
            .w-akcja { text-align: right; }
            .btn-kup { justify-content: flex-end; font-size: 10px; }
            .btn-kup::after { font-size: 14px; margin-left: 5px; }

            .siatka-plakatow { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 15px; 
            }
            .mini-plakat { height: 180px; } 
            .mini-tresc { padding: 10px; }
            .mini-tytul { font-size: 14px; }

            .siatka-aktorow-index { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 15px; 
            }
            .zdjecie-aktora-index { height: 180px; } 
            .imie-aktora-index { font-size: 14px; }
            .rola-aktora-index { font-size: 9px; }

            .stopka-strony { 
                padding: 15px 10px !important; 
                font-size: 11px !important; 
            }
            .top-bar { padding: 10px; font-size: 11px; }
            .top-bar a { margin: 5px; }

            .hero-plakat { height: 250px; } 
            .hero-tytul { font-size: 20px; } 
            .hero-tresc { padding: 15px; }
        }
    </style>
</head>

<body>
    <main>

        <div class="top-bar">
            <div><?php if (isset($_SESSION['user_id'])): ?> Witaj, <strong><?= htmlspecialchars($_SESSION['user_imie']) ?></strong><?php endif; ?></div>
            <div>
                <a href="kalendarium.php">Kalendarium</a>
                <a href="spektakle.php">Repertuar</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="moje_bilety.php" class="link-akcent">Moje bilety</a> 
                    
                    <?php if (isset($_SESSION['user_rola']) && $_SESSION['user_rola'] === 'admin'): ?><a href="admin.php" class="link-admin">Panel Admina</a><?php endif; ?>
                    <a href="wyloguj.php">Wyloguj</a>
                <?php else: ?>
                    <a href="logowanie.php">Zaloguj</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="header-sekcja">
            <img src="zdjecia/logo.png" alt="Logo Teatr Jura" class="logo-teatru">
            <h1>Teatr Jura</h1>
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
            
            <div class="lista-spektakli">
                <?php foreach ($harmonogram as $s): 
                    $ts = strtotime($s['data_wystawienia']);
                ?>
                    <a href="spektakl.php?id=<?= $s['spektakl_id'] ?>" class="wiersz-spektaklu">
                        <div class="w-data">
                            <div class="w-dzien"><?= date('d', $ts) ?></div>
                            <div class="w-miesiac"><?= polskiMiesiac(date('n', $ts)) ?></div>
                        </div>
                        <div class="w-info">
                            <div class="w-czas">GODZ. <?= date('H:i', $ts) ?></div>
                            <h3 class="w-tytul-tekst"><?= htmlspecialchars($s['tytul']) ?></h3>
                        </div>
                        <div class="w-akcja">
                            <object><a href="wybor_miejsca.php?termin_id=<?= $s['termin_id'] ?>" class="btn-kup">Bilety</a></object>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
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
                <?php foreach ($aktorzy as $aktor): ?>
                    <div class="karta-aktora-index">
                        <div class="zdjecie-aktora-index">
                            <img src="<?= htmlspecialchars($aktor['zdjecie']) ?>" alt="<?= htmlspecialchars($aktor['imie_nazwisko']) ?>">
                        </div>
                        <div class="dane-aktora-index">
                            <h3 class="imie-aktora-index"><?= htmlspecialchars($aktor['imie_nazwisko']) ?></h3>
                            <p class="rola-aktora-index"><?= htmlspecialchars($aktor['specjalizacja']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>