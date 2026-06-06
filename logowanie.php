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
    <style>
        /* Główne tło i czcionka - spójne z wyborem miejsc */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a1a1a; 
            color: #e0e0e0;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* Logo nad formularzem */
        .logo-img { 
            width: 100%;
            max-width: 220px; 
            height: auto;
            margin-bottom: 40px; 
            display: block;
        }

        /* Elegancki kontener formularza */
        .form-container {
            background-color: #262626; /* Ciemniejszy szary dla wyróżnienia */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 350px;
        }
        
        h2 { 
            font-weight: 300; 
            letter-spacing: 2px; 
            margin-top: 0;
            margin-bottom: 25px; 
            text-transform: uppercase; 
            text-align: center;
        }

        /* Styl komunikatu o błędzie */
        .komunikat-error {
            background-color: #9e4747; /* Ceglasty czerwony (taki jak zajęte miejsca) */
            color: #fff;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        /* Pola tekstowe formularza */
        label {
            display: block;
            color: #aaaaaa;
            font-size: 13px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background-color: #333;
            border: 1px solid #444;
            color: #fff;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #829356; /* Ziemista oliwka po kliknięciu */
        }

        /* Przycisk logowania */
        button[type="submit"] { 
            width: 100%;
            padding: 15px; 
            background-color: #829356; /* Ziemista oliwka */
            color: #ffffff; 
            border: none; 
            border-radius: 5px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        button[type="submit"]:hover { 
            background-color: #6a7944; 
        }

        /* Link do rejestracji na dole */
        .link-rejestracja {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #aaaaaa;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .link-rejestracja:hover {
            color: #829356;
        }
    </style>
</head>
<body>

    <img src="zdjecia/logo.png" alt="Logo Teatr Jura" class="logo-img">

    <div class="form-container">
        <h2>Zaloguj się</h2>
        
        <?php if ($komunikat): ?>
            <div class="komunikat-error">
                <strong><?= $komunikat ?></strong>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>E-mail</label>
            <input type="email" name="email" required placeholder="Wpisz swój e-mail">
            
            <label>Hasło</label>
            <input type="password" name="haslo" required placeholder="Wpisz hasło">
            
            <button type="submit">Zaloguj</button>
        </form>
        
        <a href="rejestracja.php" class="link-rejestracja">Nie masz konta? Zarejestruj się</a>
    </div>

</body>
</html>