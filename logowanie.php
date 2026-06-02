<?php
session_start();
require_once 'database.php';

$komunikat = '';

// Jeśli użytkownik jest już zalogowany, wywalamy go na stronę główną
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $haslo = $_POST['haslo'];

    if (!empty($email) && !empty($haslo)) {
        try {
            // Szukamy użytkownika po mailu
            $stmt = $pdo->prepare("SELECT id, imie, haslo, rola FROM Uzytkownicy WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Weryfikacja hasła
            if ($user && password_verify($haslo, $user['haslo'])) {
                // Odpalamy sesję!
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_imie'] = $user['imie'];
                $_SESSION['user_rola'] = $user['rola'];
                
                header("Location: index.php"); // Przekierowanie po sukcesie
                exit;
            } else {
                $komunikat = "Błędny e-mail lub hasło.";
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
    <title>Logowanie - Teatr Jura</title>
</head>
<body>
    <h2>Zaloguj się</h2>
    <?php if ($komunikat) echo "<p><strong>$komunikat</strong></p>"; ?>
    
    <form method="POST" action="">
        <label>E-mail:</label><br>
        <input type="email" name="email" required><br><br>
        
        <label>Hasło:</label><br>
        <input type="password" name="haslo" required><br><br>
        
        <button type="submit">Zaloguj</button>
    </form>
    <br>
    <a href="rejestracja.php">Nie masz konta? Zarejestruj się</a>
</body>
</html>