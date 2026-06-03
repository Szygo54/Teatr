<?php
$host = '127.0.0.1';
$db   = 'teatr_db'; 
$user = 'root';     
$pass = '';         

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), $e->getCode());
}
?>