<?php
header("Content-Type: application/json; charset=UTF-8");

include "db.php";


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Fetch all material types
    // Adjust column names (e.g. MatTypeID, MaterialType) if your schema differs
    $stmt = $pdo->query("SELECT MatTypeID, MaterialType FROM TypesOfMaterial ORDER BY MaterialType ASC");
    $types = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => $types
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}