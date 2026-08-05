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
$matTypeId = !empty($_POST['MatTypeID']) ? intval($_POST['MatTypeID']) : null;
$typeName = trim($_POST['MaterialType'] ?? '');

if ($action === 'create') {
    if (!$typeName) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Type name required"]);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO TypesOfMaterial (MaterialType) VALUES (:type)");
    $stmt->execute(['type' => $typeName]);
    echo json_encode(["status" => "success", "message" => "Material type created"]);
} 
elseif ($action === 'update') {
    if (!$matTypeId || !$typeName) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Type ID and Name required"]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE TypesOfMaterial SET MaterialType = :type WHERE MatTypeID = :id");
    $stmt->execute(['type' => $typeName, 'id' => $matTypeId]);
    echo json_encode(["status" => "success", "message" => "Material type updated"]);
} 
elseif ($action === 'delete') {
    if (!$matTypeId) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Type ID required"]);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM TypesOfMaterial WHERE MatTypeID = :id");
    $stmt->execute(['id' => $matTypeId]);
    echo json_encode(["status" => "success", "message" => "Material type deleted"]);
}