<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php'; // Or your PDO connection setup

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

// Read URL parameters
$locationId = !empty($_GET['location']) ? intval($_GET['location']) : null;
$search = !empty($_GET['search']) ? trim($_GET['search']) : null;

// Build Base Query
$query = "
    SELECT m.MaterialID, m.Name, m.MaterialDescription, m.Photo, m.Quantity, l.LocationID, l.Location, l.LocationDescription
    FROM WhereThingsAreStored w
    JOIN Materials m ON m.MaterialID = w.MaterialID
    JOIN Locations l ON l.LocationID = w.LocationID
    WHERE 1=1
";

$params = [];

// Filter by Location if selected
if ($locationId) {
    $query .= " AND w.LocationID = :locationId";
    $params['locationId'] = $locationId;
}

// Filter by Search Query if provided
if ($search) {
    $query .= " AND (m.Name LIKE :s1 OR m.MaterialDescription LIKE :s2 OR l.Location LIKE :s3)";
    $params['s1'] = '%' . $search . '%';
    $params['s2'] = '%' . $search . '%';
    $params['s3'] = '%' . $search . '%';
}

$query .= " ORDER BY m.Name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$materials = $stmt->fetchAll();

echo json_encode(["status" => "success", "data" => $materials]);