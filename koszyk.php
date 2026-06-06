<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: logowanie.php");
    exit;
}

// Zabezpieczenie: jeśli przesłano pusty koszyk, wracamy do wyboru miejsc
if (!isset($_POST['wybrane_miejsca']) || empty($_POST['wybrane_miejsca'])) {
    $id = isset($_POST['spektakl_id']) ? (int)$_POST['spektakl_id'] : 0;
    if ($id > 0) {
        header("Location: wybor_miejsca.php?spektakl_id=" . $id);
    } else {
        header("Location: index.php");
    }
    exit;
}

$spektakl_id = (int)$_POST['spektakl_id'];
$wybrane_miejsca = $_POST['wybrane_miejsca'];

try {
    $stmt = $pdo->prepare("SELECT tytul, cena FROM Spektakle WHERE id = ?");
    $stmt->execute([$spektakl_id]);
    $spektakl = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $ilosc_biletow = count($wybrane_miejsca);
    $laczna_kwota = $ilosc_biletow * $spektakl['cena'];

    $placeholdery = str_repeat('?,', count($wybrane_miejsca) - 1) . '?';
    $stmtM = $pdo->prepare("SELECT rzad, numer FROM Miejsca WHERE id IN ($placeholdery)");
    $stmtM->execute($wybrane_miejsca);
    $miejsca_info = $stmtM->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Koszyk - Teatr Jura</title>
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
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .logo-img { 
            width: 100%;
            max-width: 320px; 
            height: auto;
            margin-bottom: 40px; 
            display: block;
        }

        .podsumowanie { 
            background-color: #262626; 
            padding: 40px; 
            border-radius: 8px; 
            width: 100%;
            max-width: 500px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.5); 
            text-align: center;
            box-sizing: border-box;
        }

        h2 { font-weight: 300; letter-spacing: 2px; margin-top: 0; margin-bottom: 5px; text-transform: uppercase; }
        h3 { color: #aaaaaa; font-weight: normal; margin-top: 0; margin-bottom: 20px; }
        hr { border: 0; height: 1px; background-color: #444; margin: 20px 0; }

        .lista-miejsc {
            list-style: none; 
            padding: 0;
            margin: 0;
            text-align: left;
            background-color: #1a1a1a;
            border-radius: 5px;
            padding: 15px;
            border: 1px solid #333;
        }

        .lista-miejsc li {
            padding: 8px 0;
            border-bottom: 1px dashed #444;
            display: flex;
            justify-content: space-between;
        }
        
        .lista-miejsc li:last-child { border-bottom: none; }

        .kwota-akcent { color: #829356; font-size: 24px; margin: 25px 0; font-weight: bold; }

        .metody-platnosci { text-align: left; margin: 30px 0; }
        .metody-platnosci strong {
            display: block; margin-bottom: 15px; color: #aaaaaa; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;
        }

        .metody-platnosci label { 
            display: block; margin-bottom: 10px; padding: 15px; background-color: #333; border: 1px solid #444; border-radius: 5px; cursor: pointer; font-size: 15px; transition: border-color 0.3s, background-color 0.3s;
        }

        .metody-platnosci label:hover { border-color: #829356; background-color: #2a2a2a; }
        .metody-platnosci input[type="radio"] { margin-right: 10px; accent-color: #829356; }

        .przycisk-zaplac { 
            background-color: #829356; color: #ffffff; padding: 16px 30px; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px; transition: background-color 0.3s; text-transform: uppercase; letter-spacing: 1px;
        }
        
        .przycisk-zaplac:hover { background-color: #6a7944; }
    </style>
</head>
<body>

    <img src="zdjecia/logo.png?v=<?= time() ?>" alt="Logo Teatr Jura" class="logo-img">

    <div class="podsumowanie">
        <h2>Kasa Biletowa</h2>
        <h3><?= htmlspecialchars($spektakl['tytul']) ?></h3>
        
        <hr>
        
        <p style="text-align: left; margin-bottom: 10px; color: #aaaaaa;">Wybrane miejsca (<?= $ilosc_biletow ?>):</p>
        <ul class="lista-miejsc">
            <?php foreach ($miejsca_info as $m): ?>
                <li>
                    <span>Rząd <?= htmlspecialchars($m['rzad']) ?></span>
                    <strong>Miejsce <?= htmlspecialchars($m['numer']) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <hr>
        
        <div class="kwota-akcent">Do zapłaty: <?= number_format($laczna_kwota, 2) ?> PLN</div>

        <form method="POST" action="platnosc.php">
            <input type="hidden" name="spektakl_id" value="<?= $spektakl_id ?>">
            <?php foreach ($wybrane_miejsca as $m_id): ?>
                <input type="hidden" name="miejsca_do_zapisu[]" value="<?= htmlspecialchars($m_id) ?>">
            <?php endforeach; ?>
            
            <div class="metody-platnosci">
                <strong>Wybierz metodę płatności:</strong>
                
                <label><input type="radio" name="metoda" value="blik" required> BLIK</label>
                <label><input type="radio" name="metoda" value="karta"> Karta Płatnicza (Visa/Mastercard)</label>
                <label><input type="radio" name="metoda" value="przelew"> Szybki przelew (Przelewy24)</label>
            </div>
            
            <button type="submit" name="inicjuj_platnosc" class="przycisk-zaplac">Przejdź do płatności</button>
        </form>
    </div>

</body>
</html>