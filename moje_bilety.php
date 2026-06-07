<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

$uzytkownik_id = $_SESSION['user_id'];
$imie_uzytkownika = $_SESSION['user_imie'];

try {
    // Łączymy Rezerwacje -> Terminy -> Spektakle
    $sql = "SELECT r.id as rezerwacja_id, s.tytul, t.data_wystawienia, m.rzad, m.numer, r.data_zakupu 
            FROM Rezerwacje r
            JOIN Terminy t ON r.termin_id = t.id
            JOIN Spektakle s ON t.spektakl_id = s.id
            JOIN Miejsca m ON r.miejsce_id = m.id
            WHERE r.uzytkownik_id = ?
            ORDER BY t.data_wystawienia ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uzytkownik_id]);
    $bilety = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    die("Błąd bazy: " . $e->getMessage());
}

// Konwersja obrazków na format Base64 dla PDF
function getBase64Image($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return ''; 
}

$logo_base64 = getBase64Image('zdjecia/logo.png');
$qr_base64 = getBase64Image('zdjecia/qr.png');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje bilety - Teatr Jura</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; padding: 0; padding-bottom: 80px; }
        
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 
        
        /* Ujednolicony układ ze spektakli */
        .kontener-sekcji { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        
        /* Tabela biletów wewnątrz kontenera */
        .panel { background: #262626; padding: 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; font-size: 15px; text-align: left; }
        th, td { padding: 16px; border-bottom: 1px solid #444; }
        th { color: #aaaaaa; text-transform: uppercase; font-size: 12px; }
        tr:hover { background-color: #333; }
        
        .btn-pdf { background-color: #829356; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .btn-pdf:hover { background-color: #6a7944; }
        
        /* Szablony PDF */
        .szablon-bilet { display: none; }
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
        <h2 class="naglowek-sekcji">Twoje Bilety</h2>

        <div class="panel">
            <?php if (empty($bilety)): ?>
                <p style="text-align:center; font-size: 18px;">Brak kupionych biletów. <br><br><a href="spektakle.php" style="color: #829356; text-decoration: none;">Przejdź do repertuaru</a></p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Spektakl</th>
                        <th>Data</th>
                        <th>Miejsce</th>
                        <th style="text-align: right;">Akcja</th>
                    </tr>
                    <?php foreach ($bilety as $b): ?>
                        <tr>
                            <td><strong style="color: #ffffff;"><?= htmlspecialchars($b['tytul']) ?></strong></td>
                            <td><?= date('d.m.Y, H:i', strtotime($b['data_wystawienia'])) ?></td>
                            <td>Rząd <?= htmlspecialchars($b['rzad']) ?> / Miejsce <?= htmlspecialchars($b['numer']) ?></td>
                            <td style="text-align: right;"><button class="btn-pdf" onclick="generujBilet(<?= $b['rezerwacja_id'] ?>)">Pobierz Bilet</button></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
        
        <div id="ukryte-szablony">
            <?php if (!empty($bilety)): ?>
                <?php foreach ($bilety as $b): ?>
                    <div id="szablon-<?= $b['rezerwacja_id'] ?>" class="szablon-bilet" style="background-color: #1a1a1a; color: #e0e0e0; padding: 40px; border: 10px solid #829356; box-sizing: border-box; width: 650px;">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <img src="<?= $logo_base64 ?>" style="max-width: 180px;" alt="Logo Teatru">
                        </div>
                        <div style="background-color: #262626; padding: 25px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #333;">
                            <div style="flex: 1; padding-right: 20px;">
                                <h2 style="margin-top: 0; color: #ffffff;"><?= htmlspecialchars($b['tytul']) ?></h2>
                                <p><strong>Termin:</strong> <?= date('d.m.Y, H:i', strtotime($b['data_wystawienia'])) ?></p>
                                <p><strong>Właściciel:</strong> <?= htmlspecialchars($imie_uzytkownika) ?></p>
                                <h3 style="color: #829356; margin-bottom: 5px;">Miejsce:</h3>
                                <ul style="color: #cccccc; margin-top: 5px; padding-left: 20px;">
                                    <li>Rząd <strong style="color: #ffffff;"><?= htmlspecialchars($b['rzad']) ?></strong> | Miejsce <strong style="color: #ffffff;"><?= htmlspecialchars($b['numer']) ?></strong></li>
                                </ul>
                            </div>
                            
                            <div style="text-align: center; background: #ffffff; padding: 15px; border-radius: 10px; border: 3px solid #829356; width: 140px;">
                                <?php if ($qr_base64): ?>
                                    <img src="<?= $qr_base64 ?>" alt="Kod QR" style="width: 100%; height: auto; display: block;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 140px; background: #eee; line-height: 140px; color: #333; font-size: 12px;">Brak QR</div>
                                <?php endif; ?>
                                <p style="margin: 10px 0 0 0; font-size: 12px; font-weight: bold; color: #000;">Okaż przy wejściu</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function generujBilet(id) {
            const el = document.getElementById('szablon-' + id);
            
            // Odkrywamy na chwilę przed zrzutem
            el.style.display = 'block'; 

            const opcje = {
                margin:       0.5,
                filename:     'Bilet_Teatr_Jura_' + id + '.pdf',
                image:        { type: 'jpeg', quality: 1 },
                // Zwiększony windowWidth to ostateczna tarcza na obcinanie z prawej strony
                html2canvas:  { scale: 2, useCORS: true, logging: false, windowWidth: 1024 },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opcje).from(el).save().then(() => {
                // Po zrobieniu zrzutu wracamy do ukrycia elementu
                el.style.display = 'none'; 
            });
        }
    </script>
</body>
</html>