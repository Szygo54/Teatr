<?php
session_start();
require_once 'database.php';

$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imie = trim($_POST['imie']);
    $email = trim($_POST['email']);
    $haslo = $_POST['haslo'];

    if (!empty($imie) && !empty($email) && !empty($haslo)) {
        $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("SELECT id FROM Uzytkownicy WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $komunikat = "Konto z tym adresem e-mail już istnieje.";
            } else {
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
    <style>
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

        .logo-img { 
            width: 100%;
            max-width: 320px; 
            height: auto;
            margin-bottom: 40px; 
            display: block;
        }

        .form-container {
            background-color: #262626; 
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

        .komunikat-error, .komunikat-sukces {
            color: #fff;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .komunikat-error {
            background-color: #9e4747; 
        }
        
        .komunikat-sukces {
            background-color: #829356;
        }

        label {
            display: block;
            color: #aaaaaa;
            font-size: 13px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input[type="text"],
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

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #829356;
        }

        button[type="submit"] { 
            width: 100%;
            padding: 15px; 
            background-color: #829356; 
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

        .link-logowanie {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #aaaaaa;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .link-logowanie:hover {
            color: #829356;
        }
    </style>
</head>
<body>

    <img src="zdjecia/logo.png" alt="Logo Teatr Jura" class="logo-img">

    <div class="form-container">
        <h2>Załóż konto</h2>
        
        <?php if ($komunikat): ?>
            <?php $czy_sukces = strpos($komunikat, 'udana') !== false; ?>
            <div class="<?= $czy_sukces ? 'komunikat-sukces' : 'komunikat-error' ?>">
                <strong><?= $komunikat ?></strong>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>Imię i nazwisko</label>
            <input type="text" name="imie" required placeholder="np. Jan Kowalski">
            
            <label>E-mail</label>
            <input type="email" name="email" required placeholder="np. jan@domena.pl">
            
            <label>Hasło</label>
            <input type="password" name="haslo" required placeholder="Wpisz hasło">
            
            <button type="submit">Zarejestruj się</button>
        </form>
        
        <a href="logowanie.php" class="link-logowanie">Masz już konto? Zaloguj się</a>
    </div>

</body>
</html>