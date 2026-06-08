<?php
session_start();
require_once 'database.php';

// Zabezpieczenie: tylko dla admina
if (!isset($_SESSION['user_id']) || $_SESSION['user_rola'] !== 'admin') {
    die("Brak uprawnień. <a href='index.php'>Wróć do strony głównej</a>");
}

$komunikat = '';

// --- 1. OBSŁUGA DODAWANIA NOWEGO SPEKTAKLU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_spektakl'])) {
    $tytul = trim($_POST['tytul']);
    $opis = trim($_POST['opis']);
    $data_wystawienia = $_POST['data_wystawienia'];
    $cena = $_POST['cena'];

    $plakat_sciezka = '';
    if (isset($_FILES['plakat']) && $_FILES['plakat']['error'] === UPLOAD_ERR_OK) {
        $katalog = 'zdjecia/';
        if (!is_dir($katalog)) { mkdir($katalog, 0777, true); }
        $nazwa_pliku = time() . '_' . basename($_FILES['plakat']['name']);
        $sciezka_docelowa = $katalog . $nazwa_pliku;
        if (move_uploaded_file($_FILES['plakat']['tmp_name'], $sciezka_docelowa)) {
            $plakat_sciezka = $sciezka_docelowa;
        } else { $komunikat = "Błąd: Nie udało się zapisać plakatu."; }
    } else { $komunikat = "Błąd: Wymagane jest dodanie plakatu."; }

    if (empty($komunikat)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO Spektakle (tytul, opis, cena, plakat) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tytul, $opis, $cena, $plakat_sciezka]);
            $spektakl_id = $pdo->lastInsertId();
            
            $stmtT = $pdo->prepare("INSERT INTO Terminy (spektakl_id, data_wystawienia) VALUES (?, ?)");
            $stmtT->execute([$spektakl_id, $data_wystawienia]);
            $pdo->commit();
            $komunikat = "Spektakl i pierwszy termin dodany pomyślnie.";
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $komunikat = "Błąd: " . $e->getMessage();
        }
    }
}

// --- 2. OBSŁUGA DODAWANIA SAMEGO TERMINU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_termin'])) {
    $spektakl_id = (int)$_POST['spektakl_id'];
    $nowa_data = $_POST['nowa_data'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Terminy (spektakl_id, data_wystawienia) VALUES (?, ?)");
        $stmt->execute([$spektakl_id, $nowa_data]);
        $komunikat = "Nowy termin spektaklu został dodany do kalendarium.";
    } catch (\PDOException $e) {
        $komunikat = "Błąd podczas dodawania terminu: " . $e->getMessage();
    }
}

// --- 3. OBSŁUGA DODAWANIA AKTORA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_aktora'])) {
    $imie = trim($_POST['imie_nazwisko']);
    $spec = trim($_POST['specjalizacja']);

    $aktor_sciezka = '';
    if (isset($_FILES['zdjecie_aktora']) && $_FILES['zdjecie_aktora']['error'] === UPLOAD_ERR_OK) {
        $katalog = 'zdjecia/';
        if (!is_dir($katalog)) { mkdir($katalog, 0777, true); }
        $nazwa_pliku = time() . '_aktor_' . basename($_FILES['zdjecie_aktora']['name']);
        $sciezka_docelowa = $katalog . $nazwa_pliku;
        if (move_uploaded_file($_FILES['zdjecie_aktora']['tmp_name'], $sciezka_docelowa)) {
            $aktor_sciezka = $sciezka_docelowa;
        } else { $komunikat = "Błąd: Nie udało się zapisać zdjęcia aktora."; }
    } else { $komunikat = "Błąd: Zdjęcie aktora jest wymagane."; }

    if (empty($komunikat)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Aktorzy (imie_nazwisko, zdjecie, specjalizacja) VALUES (?, ?, ?)");
            $stmt->execute([$imie, $aktor_sciezka, $spec]);
            $komunikat = "Aktor został dodany do zespołu.";
        } catch (\PDOException $e) {
            $komunikat = "Błąd podczas dodawania aktora: " . $e->getMessage();
        }
    }
}

// --- OBSŁUGA USUWANIA (GET) ---
if (isset($_GET['usun_spektakl'])) {
    $id_do_usuniecia = (int)$_GET['usun_spektakl'];
    $pdo->prepare("DELETE FROM Spektakle WHERE id = ?")->execute([$id_do_usuniecia]);
    $komunikat = "Sztuka usunięta całkowicie.";
}
if (isset($_GET['usun_termin'])) {
    $id_terminu = (int)$_GET['usun_termin'];
    $pdo->prepare("DELETE FROM Terminy WHERE id = ?")->execute([$id_terminu]);
    $komunikat = "Termin spektaklu usunięty.";
}
if (isset($_GET['usun_bilet'])) {
    $bilet_id = (int)$_GET['usun_bilet'];
    $pdo->prepare("DELETE FROM Rezerwacje WHERE id = ?")->execute([$bilet_id]);
    $komunikat = "Rezerwacja anulowana.";
}
if (isset($_GET['usun_aktora'])) {
    $id_aktora = (int)$_GET['usun_aktora'];
    $pdo->prepare("DELETE FROM Aktorzy WHERE id = ?")->execute([$id_aktora]);
    $komunikat = "Aktor usunięty z zespołu.";
}
if (isset($_GET['usun_uzytkownika'])) {
    $id_user = (int)$_GET['usun_uzytkownika'];
    $pdo->prepare("DELETE FROM Uzytkownicy WHERE id = ? AND rola != 'admin'")->execute([$id_user]);
    $komunikat = "Użytkownik został usunięty.";
}

// --- POBIERANIE DANYCH DO WIDOKÓW ---
// Statystyki
$statOgokolne = $pdo->query("SELECT COUNT(r.id) as liczba_biletow, SUM(s.cena) as laczny_przychod FROM Rezerwacje r JOIN Terminy t ON r.termin_id = t.id JOIN Spektakle s ON t.spektakl_id = s.id")->fetch(PDO::FETCH_ASSOC);
$liczba_biletow = $statOgokolne['liczba_biletow'] ?? 0;
$laczny_przychod = $statOgokolne['laczny_przychod'] ?? 0;

$topSpektakl = $pdo->query("SELECT s.tytul, COUNT(r.id) as sprzedane FROM Spektakle s LEFT JOIN Terminy t ON s.id = t.spektakl_id LEFT JOIN Rezerwacje r ON t.id = r.termin_id GROUP BY s.id, s.tytul ORDER BY sprzedane DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Listy
$spektakle = $pdo->query("SELECT s.id as spektakl_id, s.tytul, s.cena, t.id as termin_id, t.data_wystawienia FROM Spektakle s JOIN Terminy t ON s.id = t.spektakl_id ORDER BY t.data_wystawienia ASC")->fetchAll(PDO::FETCH_ASSOC);
$unikalne_spektakle = $pdo->query("SELECT id, tytul FROM Spektakle")->fetchAll(PDO::FETCH_ASSOC);
$wszyscy_aktorzy = $pdo->query("SELECT * FROM Aktorzy ORDER BY imie_nazwisko ASC")->fetchAll(PDO::FETCH_ASSOC);
$wszyscy_uzytkownicy = $pdo->query("SELECT id, imie, email, rola FROM Uzytkownicy ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$sqlRezerwacje = "SELECT r.id as rezerwacja_id, u.imie, u.email, s.tytul, t.data_wystawienia, m.rzad, m.numer, r.data_zakupu 
                  FROM Rezerwacje r
                  JOIN Uzytkownicy u ON r.uzytkownik_id = u.id
                  JOIN Terminy t ON r.termin_id = t.id
                  JOIN Spektakle s ON t.spektakl_id = s.id
                  JOIN Miejsca m ON r.miejsce_id = m.id
                  ORDER BY r.data_zakupu DESC";
$rezerwacje = $pdo->query($sqlRezerwacje)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Admina - Teatr Jura</title>
    <style>
        /* --- STYLIZACJA SUWAKÓW (SCROLLBAR) --- */
        /* Dla Firefoxa */
        * {
            scrollbar-width: thin;
            scrollbar-color: #444 #1a1a1a;
        }
        /* Dla Chrome, Edge, Safari */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1a1a1a; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: #444; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #666; 
        }
        
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a1a1a; color: #e0e0e0; display: flex; flex-direction: column; min-height: 100vh; margin:0; }
        main { flex: 1 0 auto; padding-bottom: 50px; }
        
        .top-bar { background-color: #262626; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 14px; }
        .top-bar .zalogowany-jako { color: #aaaaaa; }
        .top-bar .zalogowany-jako strong { color: #829356; }
        .top-bar a { color: #aaaaaa; text-decoration: none; margin-left: 20px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        .top-bar a:hover { color: #829356; }

        .header-sekcja { text-align: center; margin: 40px 20px; }
        h1 { font-weight: 300; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; color: #ffffff; }
        .podtytul { color: #9e4747; text-transform: uppercase; letter-spacing: 4px; font-size: 14px; font-weight: bold; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        .komunikat { padding: 15px; border-radius: 4px; margin-bottom: 30px; font-weight: bold; text-align: center; font-size: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .komunikat-sukces { background-color: #829356; color: #fff; }
        .komunikat-blad { background-color: #9e4747; color: #fff; }

        .panel { background: #262626; padding: 30px; border-radius: 8px; margin-bottom: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h3 { border-bottom: 1px dashed #444; padding-bottom: 15px; color: #829356; font-weight: 400; letter-spacing: 1px; text-transform: uppercase; margin-top: 0; margin-bottom: 25px; }

        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 10px; }
        .stat-karta { background-color: #333; padding: 25px; border-radius: 8px; text-align: center; border-bottom: 4px solid #829356; transition: transform 0.2s; }
        .stat-karta:hover { transform: translateY(-5px); }
        .stat-tytul { color: #aaaaaa; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .stat-wartosc { color: #ffffff; font-size: 32px; font-weight: bold; margin: 0; }
        .stat-wartosc span { color: #829356; font-size: 18px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-full { grid-column: 1 / -1; }

        input[type="text"], input[type="datetime-local"], input[type="number"], input[type="file"], select, textarea {
            width: 100%; padding: 12px; background-color: #333; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; font-size: 15px; font-family: inherit; transition: 0.3s;
        }
        input[type="file"] { padding: 9px; cursor: pointer; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #829356; }

        .btn-dodaj { background-color: #829356; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; display: block; width: 100%; }
        .btn-dodaj:hover { background-color: #6a7944; }

        table { width: 100%; table-layout: fixed; border-collapse: collapse; font-size: 14px; text-align: left; }
        th, td { padding: 15px; border-bottom: 1px solid #444; }
        th { background-color: #1a1a1a; color: #aaaaaa; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; font-weight: bold; }
        tr:hover { background-color: #333; }
        td strong { color: #ffffff; }

 
        /* Minimalistyczne akcje - tylko tekst */
        .btn-action { 
            display: inline-block; 
            text-decoration: none; 
            color: #555; /* Bardzo subtelny, ciemnoszary */
            font-size: 11px; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            transition: color 0.3s ease; 
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
            margin-right: 15px;
        }

        /* Kolory po najechaniu */
        .btn-action:hover.usun { color: #9e4747; } /* Czerwony przy usuwaniu */
        .btn-action:hover.edytuj { color: #829356; } /* Zielony przy innych akcjach */

        .brak-danych { color: #aaaaaa; font-style: italic; }
    </style>
</head>
<body>
    <main>
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
                <div class="panel" style="margin-bottom: 0;">
                    <h3>Dodaj nowy spektakl</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Tytuł sztuki</label>
                                <input type="text" name="tytul" required>
                            </div>
                            <div>
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Pierwszy termin</label>
                                <input type="datetime-local" name="data_wystawienia" required>
                            </div>
                            <div>
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Cena biletu (PLN)</label>
                                <input type="number" step="0.01" name="cena" required>
                            </div>
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Plakat (plik obrazu)</label>
                                <input type="file" name="plakat" accept="image/*" required>
                            </div>
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Opis spektaklu</label>
                                <textarea name="opis" rows="3" required></textarea>
                            </div>
                        </div>
                        <button type="submit" name="dodaj_spektakl" class="btn-dodaj">Zapisz nowy spektakl</button>
                    </form>
                </div>

                <div class="panel" style="margin-bottom: 0;">
                    <h3>Dodaj kolejny termin spektaklu</h3>
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Wybierz Spektakl z bazy</label>
                                <select name="spektakl_id" required>
                                    <option value="" disabled selected>-- Wybierz spektakl --</option>
                                    <?php foreach ($unikalne_spektakle as $us): ?>
                                        <option value="<?= $us['id'] ?>"><?= htmlspecialchars($us['tytul']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Nowy termin</label>
                                <input type="datetime-local" name="nowa_data" required>
                            </div>
                        </div>
                        <button type="submit" name="dodaj_termin" class="btn-dodaj" style="margin-top: 10px;">Dodaj termin</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <h3>Repertuar</h3>
                <?php if (empty($spektakle)): ?>
                    <p class="brak-danych">Baza spektakli jest pusta.</p>
                <?php else: ?>
                    <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                        <table>
                            <tr>
                                <th>Sztuka</th>
                                <th>Data wystawienia</th>
                                <th>Cena</th>
                                <th>Akcja (Termin / Cała Sztuka)</th>
                            </tr>
                            <?php foreach ($spektakle as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['tytul']) ?></strong></td>
                                    <td><?= date('d.m.Y H:i', strtotime($s['data_wystawienia'])) ?></td>
                                    <td style="color: #829356; font-weight: bold;"><?= $s['cena'] ?> PLN</td>
                                    <td>
                                        <a href="admin.php?usun_termin=<?= $s['termin_id'] ?>" class="btn-action usun" onclick="return confirm('Usunąć ten konkretny termin?');">Usuń tylko ten termin</a>
                                        <a href="admin.php?usun_spektakl=<?= $s['spektakl_id'] ?>" class="btn-action usun" onclick="return confirm('UWAGA: Usunięcie usunie SZTUKĘ ze wszystkimi jej terminami i rezerwacjami!');">Usuń cały spektakl</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px; margin-bottom: 40px;">
                <div class="panel" style="margin-bottom: 0;">
                    <h3>Dodaj Aktora</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Imię i Nazwisko</label>
                                <input type="text" name="imie_nazwisko" required>
                            </div>
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Specjalizacja / Rola</label>
                                <input type="text" name="specjalizacja" placeholder="np. Aktor Dramatyczny" required>
                            </div>
                            <div class="form-full">
                                <label style="color: #aaa; font-size: 12px; text-transform: uppercase;">Zdjęcie</label>
                                <input type="file" name="zdjecie_aktora" accept="image/*" required>
                            </div>
                        </div>
                        <button type="submit" name="dodaj_aktora" class="btn-dodaj">Dodaj do zespołu</button>
                    </form>
                </div>
                <div class="panel" style="margin-bottom: 0;">
                    <h3>Zespół Aktorski</h3>
                    <div style="overflow-y: auto; max-height: 400px;">
                        <table>
                            <tr>
                                <th>Aktor</th>
                                <th>Specjalizacja</th>
                                <th>Akcja</th>
                            </tr>
                            <?php foreach ($wszyscy_aktorzy as $a): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($a['imie_nazwisko']) ?></strong></td>
                                    <td><?= htmlspecialchars($a['specjalizacja']) ?></td>
                                    <td><a href="admin.php?usun_aktora=<?= $a['id'] ?>" class="btn-action usun" onclick="return confirm('Usunąć tego aktora?');">Usuń</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h3>Baza Użytkowników</h3>
                <div style="overflow-x: auto; max-height: 300px; overflow-y: auto;">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Imię</th>
                            <th>E-mail</th>
                            <th>Uprawnienia</th>
                            <th>Akcja</th>
                        </tr>
                        <?php foreach ($wszyscy_uzytkownicy as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['imie']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td style="color: <?= $u['rola'] === 'admin' ? '#9e4747' : '#aaa' ?>; text-transform: uppercase; font-size: 12px; font-weight:bold;">
                                    <?= htmlspecialchars($u['rola']) ?>
                                </td>
                                <td>
                                    <?php if ($u['rola'] !== 'admin'): ?>
                                        <a href="admin.php?usun_uzytkownika=<?= $u['id'] ?>" class="btn-action usun" onclick="return confirm('Trwale usunąć tego użytkownika i jego dane?');">Zablokuj / Usuń</a>
                                    <?php else: ?>
                                        <span style="text-transform: uppercase; font-size: 11px; color:#666; font-weight:bold;">Główny Admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3>Baza Rezerwacji (Bilety)</h3>
                <?php if (empty($rezerwacje)): ?>
                    <p class="brak-danych">Aktualnie nie ma żadnych sprzedanych biletów.</p>
                <?php else: ?>
                    <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
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
                                        <a href="admin.php?usun_bilet=<?= $r['rezerwacja_id'] ?>" class="btn-action usun" onclick="return confirm('Anulować ten bilet? Miejsce automatycznie wróci na widownię.');">Anuluj</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
    <?php include 'footer.php'; ?> 
</body>
</html>