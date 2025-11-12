<?php
// ==============================
// FILE: Database.php
// ==============================

$host = "localhost";
$user = "root";
$pass = ""; // ubah jika MySQL Debian kamu pakai password
$dbname = "db_restoran";

try {
    // Koneksi menggunakan PDO (kompatibel dengan PHP 5.6)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Set error mode ke exception biar bisa ditangkap
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Jika gagal, kirim error JSON
    echo json_encode(array("error" => "Koneksi database gagal: " . $e->getMessage()));
    exit();
}
?>
