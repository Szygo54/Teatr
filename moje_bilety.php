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
    // NAPRAWIONE ZAPYTANIE SQL:
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
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje bilety - Teatr Jura</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; margin: 0; padding-bottom: 50px; }
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; text-transform: uppercase; font-weight: bold; font-size: 14px; }
        .header-sekcja { text-align: center; margin: 40px 20px; }
        .logo-img { max-width: 180px; margin-bottom: 15px; }
        .panel { background: #262626; padding: 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); max-width: 1000px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; font-size: 15px; text-align: left; }
        th, td { padding: 16px; border-bottom: 1px solid #444; }
        th { color: #aaaaaa; text-transform: uppercase; font-size: 12px; }
        tr:hover { background-color: #333; }
        .btn-pdf { background-color: #829356; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-transform: uppercase; font-weight: bold; }
        .btn-pdf:hover { background-color: #6a7944; }
        
        /* Ważne dla PDF: musi być widoczne w trakcie generowania */
        .szablon-bilet { display: none; background-color: #ffffff; color: #333; padding: 40px; border: 8px solid #829356; width: 600px; box-sizing: border-box; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div>Witaj, <strong><?= htmlspecialchars($imie_uzytkownika) ?></strong></div>
        <div><a href="index.php">Powrót</a> <a href="wyloguj.php">Wyloguj</a></div>
    </div>

    <div class="header-sekcja">
        <img src="zdjecia/logo.png" alt="Logo" class="logo-img">
        <div style="color:#829356; font-weight:bold; letter-spacing: 2px;">TWOJE BILETY</div>
    </div>

    <div class="panel">
        <?php if (empty($bilety)): ?>
            <p style="text-align:center;">Brak biletów.</p>
        <?php else: ?>
            <table>
                <tr><th>Spektakl</th><th>Data</th><th>Miejsce</th><th>Akcja</th></tr>
                <?php foreach ($bilety as $b): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($b['tytul']) ?></strong></td>
                        <td><?= date('d.m.Y H:i', strtotime($b['data_wystawienia'])) ?></td>
                        <td>Rząd <?= htmlspecialchars($b['rzad']) ?> / Miejsce <?= htmlspecialchars($b['numer']) ?></td>
                        <td><button class="btn-pdf" onclick="generujBilet(<?= $b['rezerwacja_id'] ?>)">Pobierz PDF</button></td>
                    </tr>

                    <div id="szablon-<?= $b['rezerwacja_id'] ?>" class="szablon-bilet">
                        <img src="zdjecia/logo.png" style="width: 150px; display:block; margin: 0 auto 20px;">
                        <h1 style="text-align:center; color:#333;"><?= htmlspecialchars($b['tytul']) ?></h1>
                        <p><strong>Właściciel:</strong> <?= htmlspecialchars($imie_uzytkownika) ?></p>
                        <p><strong>Data:</strong> <?= date('d.m.Y H:i', strtotime($b['data_wystawienia'])) ?></p>
                        <p><strong>Miejsce:</strong> Rząd <?= htmlspecialchars($b['rzad']) ?>, Miejsce <?= htmlspecialchars($b['numer']) ?></p>
                        <div style="text-align:center; margin-top:30px;">
                            <img src="zdjecia/qr.png" style="width:150px; height:150px; background:white; border: 1px solid #ccc;">
                        </div>
                    </div>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function generujBilet(id) {
            const el = document.getElementById('szablon-' + id);
            // html2pdf potrzebuje elementu, który jest widoczny w DOM
            el.style.display = 'block'; 
            html2pdf().from(el).set({
                margin: 10,
                filename: 'Bilet_Teatr_Jura_' + id + '.pdf',
                image: { type: 'jpeg', quality: 0.98 }
            }).save().then(() => {
                el.style.display = 'none'; // Ukryj po wygenerowaniu
            });
        }
    </script>
</body>
</html>