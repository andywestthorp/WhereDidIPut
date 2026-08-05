<?php
header("Content-Type: application/json; charset=UTF-8");

$targetDir = "../photos/";

// Ensure photos directory exists
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "File upload error code: " . $file['error']]);
        exit;
    }

    // Validate image type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid file format."]);
        exit;
    }

    // Generate unique filename (e.g., matching hex-style names like 6580a5e62efa6.png)
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $newFilename = uniqid() . '.' . $extension;
    $targetFilePath = $targetDir . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        echo json_encode([
            "status" => "success", 
            "filename" => $newFilename
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to save file to server."]);
    }
    exit;
}

http_response_code(400);
echo json_encode(["status" => "error", "message" => "No file uploaded."]);