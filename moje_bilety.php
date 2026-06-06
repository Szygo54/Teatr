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
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;}
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px;}
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #800020; color: white; }
    </style>
</head>
<body>
    <h2>Moje zakupione bilety</h2>
    <p><a href="index.php">Wróć na stronę główną</a></p>

    <?php if (empty($bilety)): ?>
        <p>Nie masz jeszcze żadnych kupionych biletów.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Spektakl</th>
                <th>Data spektaklu</th>
                <th>Rząd</th>
                <th>Miejsce</th>
                <th>Data zakupu</th>
            </tr>
            <?php foreach ($bilety as $b): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['tytul']) ?></strong></td>
                    <td><?= date('d.m.Y H:i', strtotime($b['data_wystawienia'])) ?></td>
                    <td><?= $b['rzad'] ?></td>
                    <td><?= $b['numer'] ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($b['data_zakupu'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>