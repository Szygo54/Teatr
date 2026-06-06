<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

$uzytkownik_id = $_SESSION['user_id'];

try {
    // Używamy JOIN, żeby wyciągnąć czytelne dane o rezerwacjach zalogowanego użytkownika
    $sql = "SELECT s.tytul, s.data_wystawienia, m.rzad, m.numer, r.data_zakupu 
            FROM Rezerwacje r
            JOIN Spektakle s ON r.spektakl_id = s.id
            JOIN Miejsca m ON r.miejsce_id = m.id
            WHERE r.uzytkownik_id = ?
            ORDER BY s.data_wystawienia ASC";
            
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
    <style>
        /* Główne tło i czcionka */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a1a1a; 
            color: #e0e0e0;
            margin: 0;
            padding-bottom: 50px;
        }

        /* Górny pasek nawigacji */
        .top-bar {
            background-color: #262626;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            font-size: 14px;
        }

        .top-bar .zalogowany-jako { color: #aaaaaa; }
        .top-bar .zalogowany-jako strong { color: #829356; }

        .top-bar a {
            color: #aaaaaa;
            text-decoration: none;
            margin-left: 20px;
            transition: color 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .top-bar a:hover { color: #829356; }

        /* Nagłówek z logo */
        .header-sekcja {
            text-align: center;
            margin: 40px 20px;
        }

        /* Ustawienia dla mniejszego logo */
        .logo-img { 
            width: 100%;
            max-width: 180px; 
            height: auto;
            margin-bottom: 15px; 
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .podtytul {
            color: #829356; /* Oliwkowy akcent */
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Kontener główny */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Panel z biletami */
        .panel { 
            background: #262626; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.5); 
        }

        /* Nowoczesne tabele dla trybu ciemnego */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 15px; 
            text-align: left; 
        }
        
        th, td { 
            padding: 16px; 
            border-bottom: 1px solid #444; 
        }
        
        th { 
            background-color: #1a1a1a; 
            color: #aaaaaa; 
            text-transform: uppercase; 
            font-size: 12px; 
            letter-spacing: 1px; 
            font-weight: bold; 
        }
        
        tr:hover { background-color: #333; }
        
        td strong { color: #ffffff; font-size: 16px; }
        
        /* Wyróżnienie miejsca */
        .miejsce-akcent {
            color: #829356;
            font-weight: bold;
            font-size: 16px;
        }

        .brak-biletow {
            text-align: center;
            padding: 40px 20px;
        }

        .brak-biletow p {
            color: #aaaaaa;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .btn-repertuar {
            display: inline-block;
            background-color: #829356;
            color: #ffffff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s;
        }

        .btn-repertuar:hover { background-color: #6a7944; }

    </style>
</head>
<body>

    <div class="top-bar">
        <div class="zalogowany-jako">
            Witaj, <strong><?= htmlspecialchars($_SESSION['user_imie']) ?></strong>
        </div>
        <div>
            <a href="index.php">Wróć na stronę główną</a>
            <a href="wyloguj.php">Wyloguj</a>
        </div>
    </div>

    <div class="header-sekcja">
        <img src="zdjecia/logo.png?v=<?= time() ?>" alt="Logo Teatr Jura" class="logo-img">
        <div class="podtytul">Twoje zakupione bilety</div>
    </div>

    <div class="container">
        <div class="panel">
            <?php if (empty($bilety)): ?>
                <div class="brak-biletow">
                    <p>Twój portfel biletowy jest pusty. Nie masz jeszcze żadnych rezerwacji.</p>
                    <a href="index.php" class="btn-repertuar">Sprawdź repertuar</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <tr>
                            <th>Spektakl</th>
                            <th>Data spektaklu</th>
                            <th>Rząd i Miejsce</th>
                            <th>Data zakupu</th>
                        </tr>
                        <?php foreach ($bilety as $b): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['tytul']) ?></strong></td>
                                <td><?= date('d.m.Y H:i', strtotime($b['data_wystawienia'])) ?></td>
                                <td class="miejsce-akcent">Rząd <?= $b['rzad'] ?> / Miejsce <?= $b['numer'] ?></td>
                                <td style="color: #aaaaaa; font-size: 13px;"><?= date('d.m.Y H:i', strtotime($b['data_zakupu'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>