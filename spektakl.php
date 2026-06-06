<?php
session_start();
require_once 'database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_spektaklu = (int)$_GET['id'];

try {
    // 1. Pobieranie danych spektaklu
    $sql = "SELECT s.*, MIN(t.data_wystawienia) as najblizszy_termin, t.id as termin_id
            FROM Spektakle s
            LEFT JOIN Terminy t ON s.id = t.spektakl_id
            WHERE s.id = ?
            GROUP BY s.id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_spektaklu]);
    $spektakl = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$spektakl) {
        die("Błąd 404: Taki spektakl nie istnieje. <a href='index.php'>Wróć</a>");
    }

    // 2. Pobieranie obsady (aktorów) z tabeli łączącej
    $sqlAktorzy = "SELECT a.imie_nazwisko, a.zdjecie, a.specjalizacja 
                   FROM Aktorzy a
                   JOIN Obsada o ON a.id = o.aktor_id
                   WHERE o.spektakl_id = ?";
    $stmtAktorzy = $pdo->prepare($sqlAktorzy);
    $stmtAktorzy->execute([$id_spektaklu]);
    $obsada = $stmtAktorzy->fetchAll(PDO::FETCH_ASSOC);

    // 3. NOWE: Pobieranie wszystkich terminów dla tego spektaklu
    $sqlTerminy = "SELECT id, data_wystawienia FROM Terminy WHERE spektakl_id = ? ORDER BY data_wystawienia ASC";
    $stmtTerminy = $pdo->prepare($sqlTerminy);
    $stmtTerminy->execute([$id_spektaklu]);
    $wszystkie_terminy = $stmtTerminy->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($spektakl['tytul']) ?> - Teatr Jura</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; padding: 0; padding-bottom: 80px; }
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        
        .kontener-sekcji { max-width: 1250px; margin: 40px auto; padding: 0 20px; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        
        .spektakl-layout { display: flex; gap: 50px; background: #262626; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .kolumna-lewa { flex: 0.8; } 
        .kolumna-prawa { flex: 1.2; display: flex; flex-direction: column; }
        
        .plakat-duzy { width: 100%; height: 500px; display: flex; align-items: center; justify-content: center; }
        .plakat-duzy img { max-width: 100%; max-height: 100%; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); object-fit: contain; }
        
        .tytul-spektaklu { font-size: 42px; color: #fff; margin: 0 0 20px 0; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .opis-spektaklu { font-size: 16px; line-height: 1.8; color: #bbb; margin-bottom: 40px; flex-grow: 1; }
        
        .detale { background: #1a1a1a; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        .detale p { margin: 10px 0; font-size: 18px; }
        .detale strong { color: #fff; }
        .cena-tag { color: #829356; font-size: 24px; font-weight: bold; }
        
        .btn-wielki { display: block; background: #829356; color: white; text-align: center; padding: 20px; text-decoration: none; font-size: 20px; font-weight: bold; text-transform: uppercase; border-radius: 5px; transition: 0.3s; }
        .btn-wielki:hover { background: #6a7944; }

        /* NOWE: Style dla sekcji Terminów */
        .terminy-sekcja { margin-top: 50px; background: #262626; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .terminy-naglowek { color: #829356; font-size: 28px; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 10px; margin-top: 0; margin-bottom: 30px; }
        .terminy-siatka { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .termin-karta { background: #1a1a1a; padding: 20px; border-radius: 5px; border-left: 4px solid #829356; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .termin-karta:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.4); }
        .termin-info p { margin: 5px 0; }
        .termin-data { font-size: 20px; font-weight: bold; color: #fff; }
        .termin-godzina { font-size: 14px; color: #aaa; }
        .termin-btn { background: #829356; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; text-transform: uppercase; transition: 0.3s; }
        .termin-btn:hover { background: #6a7944; }

        /* Style dla sekcji Obsady */
        .obsada-sekcja { margin-top: 50px; background: #262626; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .obsada-naglowek { color: #829356; font-size: 28px; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 10px; margin-top: 0; margin-bottom: 30px; }
        .aktorzy-siatka { display: flex; flex-wrap: wrap; gap: 40px; justify-content: flex-start; }
        .aktor-karta { text-align: center; width: 200px; }
        .aktor-zdjecie { 
            width: 140px; 
            height: 190px; 
            border-radius: 8px; 
            object-fit: cover; 
            border: 3px solid #1a1a1a; 
            margin-bottom: 15px; 
            transition: 0.3s; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.5); 
        }
        .aktor-karta:hover .aktor-zdjecie { border-color: #829356; transform: scale(1.05); }
        .aktor-imie { font-size: 16px; font-weight: bold; color: #fff; margin: 0 0 5px 0; }
        .aktor-specjalizacja { font-size: 12px; color: #aaa; text-transform: uppercase; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div><?php if (isset($_SESSION['user_id'])) echo "Witaj, <strong style='color: white;'>" . htmlspecialchars($_SESSION['user_imie']) . "</strong>"; ?></div>
        <div>
            <a href="index.php" style="color: #fff;">Strona Główna</a>
            <a href="spektakle.php">Wszystkie Sztuki</a>
        </div>
    </div>

    <div class="kontener-sekcji">
        <a href="javascript:history.back()" class="powrot">&larr; Wróć</a>
        
        <div class="spektakl-layout">
            <div class="kolumna-lewa">
                <div class="plakat-duzy"><img src="<?= htmlspecialchars($spektakl['plakat']) ?>" alt="Plakat"></div>
            </div>
            
            <div class="kolumna-prawa">
                <h1 class="tytul-spektaklu"><?= htmlspecialchars($spektakl['tytul']) ?></h1>
                
                <div class="opis-spektaklu">
                    <?= nl2br(htmlspecialchars($spektakl['opis'])) ?>
                </div>
                
                <div class="detale">
                    <?php if ($spektakl['najblizszy_termin']): ?>
                        <p><strong>Najbliższe wystawienie:</strong><br> 
                        <?= date('d.m.Y', strtotime($spektakl['najblizszy_termin'])) ?> r. o godz. <?= date('H:i', strtotime($spektakl['najblizszy_termin'])) ?></p>
                    <?php else: ?>
                        <p><strong>Najbliższe wystawienie:</strong><br> Brak zaplanowanych terminów.</p>
                    <?php endif; ?>
                    <p><strong>Cena biletu:</strong> <span class="cena-tag"><?= number_format($spektakl['cena'], 2) ?> PLN</span></p>
                </div>
                
                <?php if ($spektakl['termin_id']): ?>
                    <a href="wybor_miejsca.php?termin_id=<?= $spektakl['termin_id'] ?>" class="btn-wielki">Kup bilet na ten spektakl</a>
                <?php else: ?>
                    <button class="btn-wielki" style="background:#555;" disabled>Brak biletów</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($wszystkie_terminy)): ?>
            <div class="terminy-sekcja">
                <h2 class="terminy-naglowek">Wszystkie terminy</h2>
                <div class="terminy-siatka">
                    <?php foreach ($wszystkie_terminy as $termin): ?>
                        <div class="termin-karta">
                            <div class="termin-info">
                                <p class="termin-data"><?= date('d.m.Y', strtotime($termin['data_wystawienia'])) ?></p>
                                <p class="termin-godzina">Godz. <?= date('H:i', strtotime($termin['data_wystawienia'])) ?></p>
                            </div>
                            <a href="wybor_miejsca.php?termin_id=<?= $termin['id'] ?>" class="termin-btn">Kup Bilet</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($obsada)): ?>
            <div class="obsada-sekcja">
                <h2 class="obsada-naglowek">Obsada spektaklu</h2>
                <div class="aktorzy-siatka">
                    <?php foreach ($obsada as $aktor): ?>
                        <div class="aktor-karta">
                            <img src="<?= htmlspecialchars($aktor['zdjecie']) ?>" alt="<?= htmlspecialchars($aktor['imie_nazwisko']) ?>" class="aktor-zdjecie">
                            <h3 class="aktor-imie"><?= htmlspecialchars($aktor['imie_nazwisko']) ?></h3>
                            <div class="aktor-specjalizacja"><?= htmlspecialchars($aktor['specjalizacja']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>

</body>
</html>