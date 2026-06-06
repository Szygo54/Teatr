<?php
session_start();
require_once 'database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_spektaklu = (int)$_GET['id'];

try {
    // NAPRAWA: Łączymy Spektakl z jego najbliższym terminem
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
        
        /* Zmniejszamy wysokość plakatu i zaokrąglamy rogi */
        .plakat-duzy { width: 100%; height: 500px; display: flex; align-items: center; justify-content: center; }
        .plakat-duzy img { max-width: 100%; max-height: 100%; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        
        .tytul-spektaklu { font-size: 42px; color: #fff; margin: 0 0 20px 0; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .opis-spektaklu { font-size: 16px; line-height: 1.8; color: #bbb; margin-bottom: 40px; flex-grow: 1; }
        
        .detale { background: #1a1a1a; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        .detale p { margin: 10px 0; font-size: 18px; }
        .detale strong { color: #fff; }
        .cena-tag { color: #829356; font-size: 24px; font-weight: bold; }
        
        .btn-wielki { display: block; background: #829356; color: white; text-align: center; padding: 20px; text-decoration: none; font-size: 20px; font-weight: bold; text-transform: uppercase; border-radius: 5px; transition: 0.3s; }
        .btn-wielki:hover { background: #6a7944; }
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
    </div>

</body>
</html>