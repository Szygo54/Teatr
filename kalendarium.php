<?php
session_start();
require_once 'database.php';

try {
    $sql = "SELECT t.id AS termin_id, s.id AS spektakl_id, s.tytul, t.data_wystawienia 
            FROM Terminy t 
            JOIN Spektakle s ON t.spektakl_id = s.id 
            ORDER BY t.data_wystawienia ASC";
    
    $stmtHarmonogram = $pdo->query($sql);
    $harmonogram = $stmtHarmonogram->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

function polskiMiesiacSkrot($numerMiesiaca) {
    $miesiace = ['', 'Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'];
    return $miesiace[(int)$numerMiesiaca];
}

function polskiMiesiacPelny($numerMiesiaca) {
    $miesiace = ['', 'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
    return $miesiace[(int)$numerMiesiaca];
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kalendarium - Teatr Jura</title>
    <style>
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
        
        /* --- IDENTYCZNY PASEK NAWIGACJI --- */
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 

        /* --- IDENTYCZNA GÓRA STRONY (z pliku aktorzy.php) --- */
        .kontener-sekcji { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        .powrot:hover { color: #a4b86c; }
        
        /* --- ASCETYCZNY MINIMALIZM (Repertuar) --- */
        .separator-miesiaca { 
            font-size: 14px; 
            color: #666; 
            margin: 60px 0 10px 0; 
            padding-bottom: 15px; 
            border-bottom: 1px solid #333; 
            text-transform: uppercase; 
            letter-spacing: 4px; 
            font-weight: normal;
        }

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
        .btn-kup { 
            display: flex; 
            align-items: center;
            color: #666; 
            text-decoration: none; 
            font-weight: normal; 
            text-transform: uppercase; 
            font-size: 12px;
            letter-spacing: 2px;
            transition: all 0.3s ease; 
        }
        .btn-kup::after {
            content: '→';
            margin-left: 10px;
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        /* Interakcje (Hover) - ożywają tylko potrzebne elementy */
        .wiersz-spektaklu:hover .w-tytul-tekst { color: #fff; }
        .wiersz-spektaklu:hover .w-dzien { color: #829356; }
        .wiersz-spektaklu:hover .btn-kup { color: #829356; }
        .wiersz-spektaklu:hover .btn-kup::after { transform: translateX(8px); color: #829356; }

        .brak-danych { text-align: left; color: #666; font-size: 14px; padding: 40px 0; letter-spacing: 1px; text-transform: uppercase; }
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
                    <?php if ($_SESSION['user_rola'] === 'admin'): ?><a href="admin.php" class="link-admin">Panel Admina</a><?php endif; ?>
                    <a href="wyloguj.php">Wyloguj</a>
                <?php else: ?>
                    <a href="logowanie.php">Zaloguj się</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="kontener-sekcji">
            <a href="index.php" class="powrot">&larr; Wróć na stronę główną</a>
            <h2 class="naglowek-sekcji">Kalendarium</h2>
            
            <div class="lista-spektakli">
                <?php if (empty($harmonogram)): ?>
                    <div class="brak-danych">Brak zaplanowanych spektakli w repertuarze.</div>
                <?php else: ?>
                    <?php 
                    $ostatni_miesiac_rok = ''; 
                    foreach ($harmonogram as $s): 
                        $timestamp = strtotime($s['data_wystawienia']);
                        $dzien = date('d', $timestamp);
                        $nr_miesiaca = date('n', $timestamp);
                        $rok = date('Y', $timestamp);
                        $skrot_miesiaca = polskiMiesiacSkrot($nr_miesiaca);
                        
                        $biezacy_miesiac_rok = $nr_miesiaca . '-' . $rok;

                        if ($ostatni_miesiac_rok !== $biezacy_miesiac_rok) {
                            echo "<div class='separator-miesiaca'>" . polskiMiesiacPelny($nr_miesiaca) . " $rok</div>";
                            $ostatni_miesiac_rok = $biezacy_miesiac_rok;
                        }
                    ?>
                        <a href="spektakl.php?id=<?= $s['spektakl_id'] ?>" class="wiersz-spektaklu">
                            <div class="w-data">
                                <div class="w-dzien"><?= $dzien ?></div>
                                <div class="w-miesiac"><?= $skrot_miesiaca ?></div>
                            </div>
                            <div class="w-info">
                                <div class="w-czas">GODZ. <?= date('H:i', $timestamp) ?></div>
                                <h3 class="w-tytul-tekst"><?= htmlspecialchars($s['tytul']) ?></h3>
                            </div>
                            <div class="w-akcja">
                                <object><a href="wybor_miejsca.php?termin_id=<?= $s['termin_id'] ?>" class="btn-kup">Bilety</a></object>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>