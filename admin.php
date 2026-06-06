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

// Pobieranie spektakli do tabeli
$stmtSpektakle = $pdo->query("SELECT * FROM Spektakle ORDER BY data_wystawienia ASC");
$spektakle = $stmtSpektakle->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie wszystkich rezerwacji z bazy (łączenie 4 tabel!)
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
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;}
        .panel { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left;}
        th { background-color: #333; color: white;}
        .btn-usun { color: white; background: red; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;}
        .btn-usun:hover { background: darkred; }
        h3 { border-bottom: 2px solid #800020; padding-bottom: 5px; color: #800020; }
    </style>
</head>
<body>

    <h1>Panel Zarządzania - Teatr Jura</h1>
    <p><a href="index.php">Wróć na stronę główną</a> | <a href="wyloguj.php">Wyloguj</a></p>

    <?php if ($komunikat) echo "<p style='color:green; font-weight:bold; padding: 10px; background: #e8f5e9; border: 1px solid #c8e6c9;'>$komunikat</p>"; ?>

    <div class="panel">
        <h3>1. Dodaj nowy spektakl</h3>
        <form method="POST" action="">
            <input type="text" name="tytul" placeholder="Tytuł sztuki" required style="width: 300px; padding: 5px;"><br><br>
            <input type="datetime-local" name="data_wystawienia" required style="padding: 5px;"><br><br>
            <input type="number" step="0.01" name="cena" placeholder="Cena (PLN)" required style="padding: 5px;"><br><br>
            <textarea name="opis" placeholder="Opis..." rows="3" style="width: 300px; padding: 5px;"></textarea><br><br>
            <button type="submit" name="dodaj_spektakl" style="padding: 8px 15px; background: #2e7d32; color: white; border: none; cursor:pointer;">Dodaj</button>
        </form>
    </div>

    <div class="panel">
        <h3>2. Baza Rezerwacji (Zarządzanie biletami)</h3>
        <?php if (empty($rezerwacje)): ?>
            <p>Brak sprzedanych biletów.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID Ref.</th>
                    <th>Klient</th>
                    <th>E-mail</th>
                    <th>Spektakl</th>
                    <th>Miejsce (Rząd/Numer)</th>
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
                            <a href="admin.php?usun_bilet=<?= $r['rezerwacja_id'] ?>" class="btn-usun" onclick="return confirm('Anulować ten bilet? Miejsce wróci do puli wolnych.');">Anuluj bilet</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h3>3. Aktualny Repertuar</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Data</th>
                <th>Cena</th>
                <th>Akcja</th>
            </tr>
            <?php foreach ($spektakle as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><strong><?= htmlspecialchars($s['tytul']) ?></strong></td>
                    <td><?= $s['data_wystawienia'] ?></td>
                    <td><?= $s['cena'] ?> PLN</td>
                    <td>
                        <a href="admin.php?usun_spektakl=<?= $s['id'] ?>" class="btn-usun" onclick="return confirm('Usunąć sztukę? Wszystkie kupione na nią bilety również znikną z bazy!');">Usuń sztukę</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>
</html>