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
    
    // Fetch all storage locations sorted by name
    $stmt = $pdo->query("SELECT LocationID, Location, LocationDescription FROM Locations ORDER BY LENGTH(Location) ASC, Location ASC");
    $locations = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => $locations
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
