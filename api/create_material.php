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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['Name'] ?? '');
    $typeOfMaterial = intval($_POST['MatTypeID'] ?? 0);
    $description = trim($_POST['MaterialDescription'] ?? '');
    $locationId = intval($_POST['LocationID'] ?? 0);

    if (empty($name) || $typeOfMaterial === 0 || $locationId === 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Name, Material Type, and Location are required."]);
        exit;
    }

    // Handle photo saving
    $photoFilename = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../photos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $photoFilename = uniqid() . '.' . $extension;
        
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photoFilename)) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to save image file."]);
            exit;
        }
    }

    try {
        // Begin Transaction
        $pdo->beginTransaction();

        // 1. Insert into Materials
        $stmtMat = $pdo->prepare("
            INSERT INTO Materials (Name, TypeOfMaterial, MaterialDescription, Photo) 
            VALUES (:name, :type, :description, :photo)
        ");
        $stmtMat->execute([
            'name' => $name,
            'type' => $typeOfMaterial,
            'description' => $description,
            'photo' => $photoFilename
        ]);

        $newMaterialId = $pdo->lastInsertId();

        // 2. Insert into WhereThingsAreStored
        $stmtLoc = $pdo->prepare("
            INSERT INTO WhereThingsAreStored (MaterialID, LocationID) 
            VALUES (:materialId, :locationId)
        ");
        $stmtLoc->execute([
            'materialId' => $newMaterialId,
            'locationId' => $locationId
        ]);

        // Commit Transaction
        $pdo->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Material created successfully!",
            "materialId" => $newMaterialId
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
    exit;
}
