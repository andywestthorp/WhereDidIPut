<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$action = $_POST['action'] ?? '';
$locationId = !empty($_POST['LocationID']) ? intval($_POST['LocationID']) : null;
$location = trim($_POST['Location'] ?? '');
$description = trim($_POST['LocationDescription'] ?? '');

if ($action === 'create') {
    if (!$location) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Location name is required"]);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO Locations (Location, LocationDescription) VALUES (:loc, :desc)");
    $stmt->execute(['loc' => $location, 'desc' => $description]);
    echo json_encode(["status" => "success", "message" => "Location created"]);
} 
elseif ($action === 'update') {
    if (!$locationId || !$location) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Location ID and Name are required"]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE Locations SET Location = :loc, LocationDescription = :desc WHERE LocationID = :id");
    $stmt->execute(['loc' => $location, 'desc' => $description, 'id' => $locationId]);
    echo json_encode(["status" => "success", "message" => "Location updated"]);
} 
elseif ($action === 'delete') {
    if (!$locationId) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Location ID required"]);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM Locations WHERE LocationID = :id");
    $stmt->execute(['id' => $locationId]);
    echo json_encode(["status" => "success", "message" => "Location deleted"]);
}