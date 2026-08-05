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

// Read incoming request action & ID
$action     = $_POST['action'] ?? 'update';
$materialId = !empty($_POST['MaterialID']) ? intval($_POST['MaterialID']) : null;

if (!$materialId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing Material ID"]);
    exit;
}

// -----------------------------------------------------------------------------
// 1. DELETE ACTION (CONSUMED / REMOVED)
// -----------------------------------------------------------------------------
if ($action === 'delete') {
    try {
        $pdo->beginTransaction();

        // Remove mapping entry from junction table
        $stmt1 = $pdo->prepare("DELETE FROM WhereThingsAreStored WHERE MaterialID = :id");
        $stmt1->execute(['id' => $materialId]);

        // Remove material entry
        $stmt2 = $pdo->prepare("DELETE FROM Materials WHERE MaterialID = :id");
        $stmt2->execute(['id' => $materialId]);

        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Item deleted successfully"]);
    } catch (\PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Delete failed: " . $e->getMessage()]);
    }
    exit;
}

// -----------------------------------------------------------------------------
// 2. UPDATE ACTION (NAME, DESCRIPTION, QUANTITY, LOCATION)
// -----------------------------------------------------------------------------
$name        = trim($_POST['Name'] ?? '');
$description = trim($_POST['MaterialDescription'] ?? '');
$quantity    = isset($_POST['Quantity']) ? intval($_POST['Quantity']) : 1;
$locationId  = !empty($_POST['LocationID']) ? intval($_POST['LocationID']) : null;

if (!$name || !$locationId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Name and Location are required fields"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Update Materials record
    $stmt1 = $pdo->prepare("
        UPDATE Materials 
        SET Name = :name, 
            MaterialDescription = :desc, 
            Quantity = :qty 
        WHERE MaterialID = :id
    ");
    $stmt1->execute([
        'name' => $name,
        'desc' => $description,
        'qty'  => $quantity,
        'id'   => $materialId
    ]);

    // Update Location mapping record
    $stmt2 = $pdo->prepare("
        UPDATE WhereThingsAreStored 
        SET LocationID = :locId 
        WHERE MaterialID = :id
    ");
    $stmt2->execute([
        'locId' => $locationId,
        'id'    => $materialId
    ]);

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Item updated successfully"]);
} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Update failed: " . $e->getMessage()]);
}
