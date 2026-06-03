<?php
session_start();
require_once 'database.php';

$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imie = trim($_POST['imie']);
    $email = trim($_POST['email']);
    $haslo = $_POST['haslo'];

    if (!empty($imie) && !empty($email) && !empty($haslo)) {
        // Hashowanie hasła - absolutna podstawa bezpieczeństwa!
        $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);

        try {
            // Sprawdzamy, czy mail już istnieje
            $stmt = $pdo->prepare("SELECT id FROM Uzytkownicy WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $komunikat = "Konto z tym adresem e-mail już istnieje.";
            } else {
                // Wrzucamy do bazy
                $stmt = $pdo->prepare("INSERT INTO Uzytkownicy (imie, email, haslo) VALUES (?, ?, ?)");
                $stmt->execute([$imie, $email, $haslo_hash]);
                $komunikat = "Rejestracja udana! Możesz się teraz zalogować.";
            }
        } catch (\PDOException $e) {
            $komunikat = "Błąd bazy danych: " . $e->getMessage();
        }
    } else {
        $komunikat = "Wypełnij wszystkie pola!";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja - Teatr Jura</title>
</head>
<body>
    <h2>Załóż konto</h2>
    <?php if ($komunikat) echo "<p><strong>$komunikat</strong></p>"; ?>
    
    <form method="POST" action="">
        <label>Imię i nazwisko:</label><br>
        <input type="text" name="imie" required><br><br>
        
        <label>E-mail:</label><br>
        <input type="email" name="email" required><br><br>
        
        <label>Hasło:</label><br>
        <input type="password" name="haslo" required><br><br>
        
        <button type="submit">Zarejestruj się</button>
    </form>
    <br>
    <a href="logowanie.php">Masz już konto? Zaloguj się</a>
</body>
</html>