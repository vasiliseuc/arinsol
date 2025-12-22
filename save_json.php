<?php
require_once __DIR__ . '/config/config.php';

// Set JSON response header
header('Content-Type: application/json');

// Get JSON data from request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if key is provided and valid
if (!isset($data['key']) || !checkKey($data['key'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing access key']);
    exit;
}

// Check if data is provided
if (!isset($data['data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No data provided']);
    exit;
}

$jsonContent = $data['data'];

// Validate JSON
$decoded = json_decode($jsonContent);
if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

// Create backup directory if it doesn't exist
$backupDir = __DIR__ . '/data-changes';
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create backup directory. Check permissions.']);
        exit;
    }
}

// Create backup before saving
$backupFile = $backupDir . '/data_' . date('Y-m-d_H-i-s') . '.json';
if (file_exists(DATA_FILE)) {
    if (!copy(DATA_FILE, $backupFile)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create backup. Check permissions.']);
        exit;
    }
}

// Save the JSON file
$result = file_put_contents(DATA_FILE, $jsonContent, LOCK_EX);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to write file. Check file permissions.']);
    exit;
}

// Success
echo json_encode([
    'success' => true,
    'message' => 'File saved successfully',
    'backup' => basename($backupFile)
]);
