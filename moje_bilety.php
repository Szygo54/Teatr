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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje bilety - Teatr Jura</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1 0 auto; padding-bottom: 50px; }

        
        
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .top-bar a:hover { color: #829356; }
        .top-bar .link-akcent { color: #829356; }
        .top-bar .link-admin { color: #9e4747; } 
        
        .kontener-sekcji { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .naglowek-sekcji { font-size: 32px; color: #fff; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; text-transform: uppercase; }
        .powrot { display: inline-block; margin-bottom: 20px; color: #829356; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        
        .panel { background: #262626; padding: 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; font-size: 15px; text-align: left; }
        th, td { padding: 16px; border-bottom: 1px solid #444; }
        th { color: #aaaaaa; text-transform: uppercase; font-size: 12px; }
        tr:hover { background-color: #333; }
        
        .btn-pdf { background-color: #829356; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .btn-pdf:hover { background-color: #6a7944; }

        /* Szablony biletów po prostu ukrywamy na stronie */
        #ukryte-szablony { display: none; }

        /* KARTY BILETÓW NA TELEFONIE */
        @media (max-width: 768px) {
            .top-bar { flex-direction: column; gap: 15px; padding: 15px; text-align: center; font-size:10px }
            .top-bar div { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
            .top-bar a { margin-left: 0; font-size:10px}
            
            .kontener-sekcji { margin: 20px auto; padding: 0 15px; }
            .naglowek-sekcji { font-size: 24px; text-align: center; }
            .powrot { display: block; text-align: center; font-size: 16px; padding: 10px 0; }
            
            .panel { padding: 10px; background: transparent; box-shadow: none; }
            
            table, thead, tbody, th, td, tr { display: block; width: 100%; box-sizing: border-box; }
            thead tr { display: none; } 
            
            tr { 
                background: #262626; 
                margin-bottom: 20px; 
                border-radius: 12px; 
                padding: 15px; 
                border: 1px solid #444;
                box-shadow: 0 5px 15px rgba(0,0,0,0.4);
            }
            
            td { 
                position: relative;
                padding: 12px 10px 12px 45% !important; 
                text-align: right !important; 
                border-bottom: 1px dashed #444; 
                font-size: 14px;
                min-height: 20px;
            }
            
            td::before { 
                content: attr(data-label); 
                position: absolute; 
                left: 10px; 
                top: 12px;
                width: 40%; 
                white-space: nowrap; 
                text-align: left; 
                font-weight: bold; 
                color: #829356; 
                text-transform: uppercase;
                font-size: 12px;
            }
            
            td:last-child { 
                border-bottom: none; 
                padding-bottom: 0; 
                text-align: center !important;
                padding-left: 10px !important;
                margin-top: 10px;
            }
            td:last-child::before { display: none; }
            
            .btn-pdf { width: 100%; padding: 18px; font-size: 15px; }
        }
    </style>
</head>
<body>

    <div id="ukryte-szablony">
        <?php if (!empty($bilety)): ?>
            <?php foreach ($bilety as $b): ?>
                <div id="szablon-<?= $b['rezerwacja_id'] ?>" style="background-color: #1a1a1a; color: #e0e0e0; padding: 40px; border: 10px solid #829356; box-sizing: border-box; width: 650px; margin: 0;">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <img src="<?= $logo_base64 ?>" style="max-width: 180px;" alt="Logo Teatru">
                    </div>
                    <div style="background-color: #262626; padding: 25px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #333;">
                        <div style="flex: 1; padding-right: 20px;">
                            <h2 style="margin-top: 0; color: #ffffff;"><?= htmlspecialchars($b['tytul']) ?></h2>
                            <p><strong>Termin:</strong> <?= date('d.m.Y, H:i', strtotime($b['data_wystawienia'])) ?></p>
                            <p><strong>Właściciel:</strong> <?= htmlspecialchars($imie_uzytkownika) ?></p>
                            <h3 style="color: #829356; margin-bottom: 5px;">Miejsca:</h3>
                            <ul style="color: #cccccc; margin-top: 5px; padding-left: 20px;">
                                <li>Rząd <strong style="color: #ffffff;"><?= htmlspecialchars($b['rzad']) ?></strong> | Miejsce <strong style="color: #ffffff;"><?= htmlspecialchars($b['numer']) ?></strong></li>
                            </ul>
                        </div>
                        
                        <div style="text-align: center; background: #ffffff; padding: 15px; border-radius: 10px; border: 3px solid #829356; width: 140px; flex-shrink: 0;">
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
                        <thead>
                            <tr>
                                <th>Spektakl</th>
                                <th>Data</th>
                                <th>Miejsce</th>
                                <th style="text-align: right;">Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bilety as $b): ?>
                                <tr>
                                    <td data-label="Spektakl"><strong style="color: #ffffff;"><?= htmlspecialchars($b['tytul']) ?></strong></td>
                                    <td data-label="Data"><?= date('d.m.Y, H:i', strtotime($b['data_wystawienia'])) ?></td>
                                    <td data-label="Miejsce">Rząd <?= htmlspecialchars($b['rzad']) ?> / Miejsce <?= htmlspecialchars($b['numer']) ?></td>
                                    <td data-label="Akcja" style="text-align: right;"><button class="btn-pdf" onclick="generujBilet(<?= $b['rezerwacja_id'] ?>)">Pobierz Bilet</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function generujBilet(id) {
            // Pobieramy oryginalny ukryty element
            const oryginalnyEl = document.getElementById('szablon-' + id);
            
            // Klonujemy go do tymczasowego kontenera wymuszającego 800px szerokości
            // Dzięki temu telefon (który ma np. 390px) nie utnie nam prawej strony biletu (z kodem QR!)
            const wrapper = document.createElement('div');
            wrapper.style.position = 'fixed';
            wrapper.style.top = '0';
            wrapper.style.left = '0';
            wrapper.style.width = '800px'; 
            wrapper.style.zIndex = '-9999';
            
            const klon = oryginalnyEl.cloneNode(true);
            klon.style.display = 'block';
            wrapper.appendChild(klon);
            document.body.appendChild(wrapper);

            const opcje = {
                margin:       0.5,
                filename:     'Bilet_Teatr_Jura_' + id + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false, windowWidth: 800 },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            
            // Po zapisaniu PDF usuwamy nasz tymczasowy kontener
            html2pdf().set(opcje).from(klon).save().then(() => {
                document.body.removeChild(wrapper); 
            });
        }
    </script>
</body>
</html>