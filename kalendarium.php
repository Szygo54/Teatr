<?php
session_start();
require_once 'database.php';

try {
    // NAPRAWIONE ZAPYTANIE: Łączymy Spektakle z Terminami
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
    <title>Pełne Kalendarium - Teatr Jura</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; padding: 0; padding-bottom: 80px; }
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        
        .header-sekcja { text-align: center; margin: 40px 20px; }
        .kontener-sekcji { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .separator-miesiaca { font-size: 24px; color: #829356; margin: 40px 0 15px 0; padding-bottom: 5px; border-bottom: 1px solid #444; text-transform: uppercase; letter-spacing: 2px; }

        .lista-spektakli { display: flex; flex-direction: column; gap: 15px; }
        .wiersz-spektaklu { display: flex; align-items: center; background: #262626; border-radius: 8px; padding: 20px 30px; text-decoration: none; transition: 0.3s; }
        .wiersz-spektaklu:hover { background: #2f2f2f; transform: translateX(5px); }
        
        .w-data { width: 15%; }
        .w-dzien { font-size: 32px; font-weight: bold; color: #829356; line-height: 1; }
        .w-miesiac { font-size: 14px; color: #aaa; text-transform: uppercase; }
        .w-czas { width: 15%; font-size: 18px; color: #ccc; }
        .w-tytul { width: 45%; }
        .w-tytul-tekst { font-size: 22px; color: #fff; font-weight: bold; margin: 0; text-transform: uppercase; }
        
        .w-akcja { width: 25%; text-align: right; }
        .btn-kup { display: inline-block; color: #829356; border: 2px solid #829356; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; transition: 0.3s; }
        .wiersz-spektaklu:hover .btn-kup { background: #829356; color: white; }

        .brak-danych { text-align: center; color: #aaa; font-size: 18px; padding: 40px; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div><?php if (isset($_SESSION['user_id'])): ?> Witaj, <strong><?= htmlspecialchars($_SESSION['user_imie']) ?></strong><?php endif; ?></div>
        <div>
            <a href="index.php" style="color: #fff;">Strona Główna</a>
        </div>
    </div>

    <div class="header-sekcja">
        <h1 style="color: white; font-size: 40px; margin: 0; text-transform: uppercase; letter-spacing: 3px;">Repertuar</h1>
    </div>

    <div class="kontener-sekcji">
        <a href="index.php" class="powrot">&larr; Wróć na stronę główną</a>
        <h2 class="naglowek-sekcji">Pełne Kalendarium</h2>
        
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
                        <div class="w-czas"><?= date('H:i', $timestamp) ?></div>
                        <div class="w-tytul"><h3 class="w-tytul-tekst"><?= htmlspecialchars($s['tytul']) ?></h3></div>
                        <div class="w-akcja">
                            <object><a href="wybor_miejsca.php?termin_id=<?= $s['termin_id'] ?>" class="btn-kup">Kup bilet</a></object>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>