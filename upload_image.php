<?php
require_once __DIR__ . '/config/config.php';

// Set JSON response header
header('Content-Type: application/json');

// Check authentication via session
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please login first.']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];
$fileName = $file['name'];
$fileTmp = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($fileTmp);

if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
    exit;
}

// Validate file size (max 10MB)
if ($fileSize > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'File size exceeds 10MB limit']);
    exit;
}

// Check if fixed filename is provided (for logo, software1, software2)
$fixedFilename = isset($_POST['fixedFilename']) ? $_POST['fixedFilename'] : null;

if ($fixedFilename) {
    // Use the fixed filename
    $fileName = $fixedFilename;
} else {
    // Sanitize filename and add timestamp
    $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
    $fileName = time() . '_' . $fileName; // Add timestamp to prevent conflicts
}

// Set upload directory
$uploadDir = __DIR__ . '/assets/';

// Create backup of old file if it exists
$targetFile = $uploadDir . $fileName;
if (file_exists($targetFile)) {
    $backupDir = __DIR__ . '/data-changes';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    $backupFile = $backupDir . '/image_backup_' . date('Y-m-d_H-i-s') . '_' . basename($targetFile);
    copy($targetFile, $backupFile);
}

// Move uploaded file
if (move_uploaded_file($fileTmp, $targetFile)) {
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'filename' => $fileName,
        'url' => 'assets/' . $fileName
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file. Check directory permissions.']);
}
