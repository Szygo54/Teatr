<?php
session_start();
require_once 'database.php';

// Zabezpieczenie: tylko dla admina
if (!isset($_SESSION['user_id']) || $_SESSION['user_rola'] !== 'admin') {
    die("Brak uprawnień. <a href='index.php'>Wróć do strony głównej</a>");
}

$komunikat = '';

// --- OBSŁUGA DODAWANIA SPEKTAKLU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_spektakl'])) {
    $tytul = trim($_POST['tytul']);
    $opis = trim($_POST['opis']);
    $data_wystawienia = $_POST['data_wystawienia'];
    $cena = $_POST['cena'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Spektakle (tytul, opis, data_wystawienia, cena) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tytul, $opis, $data_wystawienia, $cena]);
        $komunikat = "Spektakl dodany pomyślnie.";
    } catch (\PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
    }
}

// --- OBSŁUGA USUWANIA SPEKTAKLU ---
if (isset($_GET['usun_spektakl'])) {
    $id_do_usuniecia = (int)$_GET['usun_spektakl'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Spektakle WHERE id = ?");
        $stmt->execute([$id_do_usuniecia]);
        $komunikat = "Spektakl usunięty.";
    } catch (\PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
    }
}

// --- OBSŁUGA ANULOWANIA REZERWACJI ---
if (isset($_GET['usun_bilet'])) {
    $bilet_id = (int)$_GET['usun_bilet'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Rezerwacje WHERE id = ?");
        $stmt->execute([$bilet_id]);
        $komunikat = "Rezerwacja została anulowana (miejsce zwolnione).";
    } catch (\PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
    }
}

// --- POBIERANIE DANYCH DO STATYSTYK (DASHBOARD) ---
// 1. Łączny przychód i liczba biletów
$sqlStatystyki = "SELECT COUNT(r.id) as liczba_biletow, SUM(s.cena) as laczny_przychod 
                  FROM Rezerwacje r 
                  JOIN Spektakle s ON r.spektakl_id = s.id";
$stmtStat = $pdo->query($sqlStatystyki);
$statOgokolne = $stmtStat->fetch(PDO::FETCH_ASSOC);

$liczba_biletow = $statOgokolne['liczba_biletow'] ?? 0;
$laczny_przychod = $statOgokolne['laczny_przychod'] ?? 0;

// 2. Najpopularniejszy spektakl
$sqlTop = "SELECT s.tytul, COUNT(r.id) as sprzedane 
           FROM Spektakle s 
           LEFT JOIN Rezerwacje r ON s.id = r.spektakl_id 
           GROUP BY s.id, s.tytul 
           ORDER BY sprzedane DESC 
           LIMIT 1";
$stmtTop = $pdo->query($sqlTop);
$topSpektakl = $stmtTop->fetch(PDO::FETCH_ASSOC);


// --- POBIERANIE LIST DO TABEL ---
$stmtSpektakle = $pdo->query("SELECT * FROM Spektakle ORDER BY data_wystawienia ASC");
$spektakle = $stmtSpektakle->fetchAll(PDO::FETCH_ASSOC);

$sqlRezerwacje = "SELECT r.id as rezerwacja_id, u.imie, u.email, s.tytul, m.rzad, m.numer, r.data_zakupu 
                  FROM Rezerwacje r
                  JOIN Uzytkownicy u ON r.uzytkownik_id = u.id
                  JOIN Spektakle s ON r.spektakl_id = s.id
                  JOIN Miejsca m ON r.miejsce_id = m.id
                  ORDER BY r.data_zakupu DESC";
$stmtRezerwacje = $pdo->query($sqlRezerwacje);
$rezerwacje = $stmtRezerwacje->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Admina - Teatr Jura</title>
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

        /* Nagłówek */
        .header-sekcja {
            text-align: center;
            margin: 40px 20px;
        }

        h1 { 
            font-weight: 300; 
            letter-spacing: 2px; 
            text-transform: uppercase; 
            margin-bottom: 5px; 
            color: #ffffff;
        }

        .podtytul {
            color: #9e4747; /* Ceglasty akcent dla admina */
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Kontener główny */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Style komunikatów */
        .komunikat {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
            font-weight: bold;
            text-align: center;
            font-size: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .komunikat-sukces { background-color: #829356; color: #fff; }
        .komunikat-blad { background-color: #9e4747; color: #fff; }

        /* Pojedynczy panel z zawartością */
        .panel { 
            background: #262626; 
            padding: 30px; 
            border-radius: 8px; 
            margin-bottom: 40px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.5); 
        }

        h3 { 
            border-bottom: 1px dashed #444; 
            padding-bottom: 15px; 
            color: #829356; 
            font-weight: 400; 
            letter-spacing: 1px; 
            text-transform: uppercase; 
            margin-top: 0;
            margin-bottom: 25px;
        }

        /* --- STYL DASHBOARDU (STATYSTYK) --- */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 10px;
        }

        .stat-karta {
            background-color: #333;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            border-bottom: 4px solid #829356;
            transition: transform 0.2s;
        }
        
        .stat-karta:hover { transform: translateY(-5px); }

        .stat-tytul {
            color: #aaaaaa;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-wartosc {
            color: #ffffff;
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }

        .stat-wartosc span {
            color: #829356;
            font-size: 18px;
        }

        /* Formularz dodawania */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-full { grid-column: 1 / -1; }

        input[type="text"],
        input[type="datetime-local"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px;
            background-color: #333;
            border: 1px solid #444;
            color: #fff;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #829356;
        }

        .btn-dodaj { 
            background-color: #829356; 
            color: white; 
            padding: 12px 30px; 
            border: none; 
            border-radius: 5px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            transition: background-color 0.3s; 
            display: block;
            width: 100%;
        }
        
        .btn-dodaj:hover { background-color: #6a7944; }

        /* Nowoczesne tabele dla trybu ciemnego */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 14px; 
            text-align: left; 
        }
        
        th, td { 
            padding: 15px; 
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
        
        td strong { color: #ffffff; }

        /* Przyciski akcji (Usuwanie) */
        .btn-usun { 
            display: inline-block; 
            background-color: #9e4747; 
            color: #ffffff; 
            padding: 8px 15px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            transition: background-color 0.3s; 
        }
        
        .btn-usun:hover { background-color: #7a3636; }

        .brak-danych { color: #aaaaaa; font-style: italic; }

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
        <h1>Teatr Jura</h1>
        <div class="podtytul">Panel Zarządzania</div>
    </div>

    <div class="container">
        
        <?php if ($komunikat): ?>
            <?php $czy_blad = strpos(strtolower($komunikat), 'błąd') !== false; ?>
            <div class="komunikat <?= $czy_blad ? 'komunikat-blad' : 'komunikat-sukces' ?>">
                <?= htmlspecialchars($komunikat) ?>
            </div>
        <?php endif; ?>

        <div class="panel">
            <h3>Podsumowanie Sprzedaży</h3>
            <div class="dashboard-grid">
                <div class="stat-karta">
                    <div class="stat-tytul">Sprzedane bilety</div>
                    <div class="stat-wartosc"><?= $liczba_biletow ?> <span>szt.</span></div>
                </div>
                
                <div class="stat-karta">
                    <div class="stat-tytul">Całkowity przychód</div>
                    <div class="stat-wartosc"><?= number_format($laczny_przychod, 2, ',', ' ') ?> <span>PLN</span></div>
                </div>
                
                <div class="stat-karta">
                    <div class="stat-tytul">Hit repertuaru</div>
                    <div class="stat-wartosc" style="font-size: 20px; line-height: 1.6;">
                        <?php if ($topSpektakl && $topSpektakl['sprzedane'] > 0): ?>
                            <?= htmlspecialchars($topSpektakl['tytul']) ?><br>
                            <span style="color: #aaaaaa; font-size: 14px;">(<?= $topSpektakl['sprzedane'] ?> rezerwacji)</span>
                        <?php else: ?>
                            <span style="color: #aaaaaa; font-size: 16px;">Brak danych</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h3>Dodaj nowy spektakl</h3>
            <form method="POST" action="">
                <div class="form-grid">
                    <div>
                        <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Tytuł sztuki</label>
                        <input type="text" name="tytul" placeholder="Wpisz tytuł..." required>
                    </div>
                    <div>
                        <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Data i godzina</label>
                        <input type="datetime-local" name="data_wystawienia" required>
                    </div>
                    <div class="form-full">
                        <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Cena biletu (PLN)</label>
                        <input type="number" step="0.01" name="cena" placeholder="np. 80.00" required>
                    </div>
                    <div class="form-full">
                        <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Opis spektaklu</label>
                        <textarea name="opis" placeholder="Krótki opis sztuki..." rows="4" required></textarea>
                    </div>
                </div>
                <button type="submit" name="dodaj_spektakl" class="btn-dodaj">Zapisz do repertuaru</button>
            </form>
        </div>

        <div class="panel">
            <h3>Baza Rezerwacji (Bilety)</h3>
            <?php if (empty($rezerwacje)): ?>
                <p class="brak-danych">Aktualnie nie ma żadnych sprzedanych biletów.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <tr>
                            <th>ID Ref.</th>
                            <th>Klient</th>
                            <th>E-mail</th>
                            <th>Spektakl</th>
                            <th>Miejsce (Rząd / Nr)</th>
                            <th>Data zakupu</th>
                            <th>Akcja</th>
                        </tr>
                        <?php foreach ($rezerwacje as $r): ?>
                            <tr>
                                <td>#<?= $r['rezerwacja_id'] ?></td>
                                <td><strong><?= htmlspecialchars($r['imie']) ?></strong></td>
                                <td><?= htmlspecialchars($r['email']) ?></td>
                                <td><?= htmlspecialchars($r['tytul']) ?></td>
                                <td>Rząd <?= $r['rzad'] ?>, Miejsce <?= $r['numer'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($r['data_zakupu'])) ?></td>
                                <td>
                                    <a href="admin.php?usun_bilet=<?= $r['rezerwacja_id'] ?>" class="btn-usun" onclick="return confirm('Czy na pewno chcesz anulować ten bilet? Miejsce automatycznie wróci do puli wolnych na widowni.');">Anuluj bilet</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h3>Aktualny Repertuar</h3>
            <?php if (empty($spektakle)): ?>
                <p class="brak-danych">Baza spektakli jest pusta.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Tytuł</th>
                            <th>Data wystawienia</th>
                            <th>Cena</th>
                            <th>Akcja</th>
                        </tr>
                        <?php foreach ($spektakle as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><strong><?= htmlspecialchars($s['tytul']) ?></strong></td>
                                <td><?= date('d.m.Y H:i', strtotime($s['data_wystawienia'])) ?></td>
                                <td style="color: #829356; font-weight: bold;"><?= $s['cena'] ?> PLN</td>
                                <td>
                                    <a href="admin.php?usun_spektakl=<?= $s['id'] ?>" class="btn-usun" onclick="return confirm('UWAGA: Usunięcie sztuki sprawi, że zniknie ona ze strony, a wszystkie kupione na nią bilety zostaną bezpowrotnie usunięte z bazy! Kontynuować?');">Usuń sztukę</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>