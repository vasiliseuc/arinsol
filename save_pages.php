<?php
// Prevent any HTML output from PHP errors disrupting the JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

require_once __DIR__ . '/config/config.php';

// Clear output
ob_clean();

header('Content-Type: application/json');

// Auth Check
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$file = $_POST['file'] ?? '';
$content = $_POST['content'] ?? '';

// Whitelist files for security
$allowedFiles = [
    'privacy-policy.php' => __DIR__ . '/privacy-policy.php',
    'terms-conditions.php' => __DIR__ . '/terms-conditions.php'
];

if (!array_key_exists($file, $allowedFiles)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file']);
    exit;
}

$path = $allowedFiles[$file];

// Create backup
$backupDir = __DIR__ . '/data-changes';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
$backupFile = $backupDir . '/' . $file . '.backup_' . date('Y-m-d_H-i-s');

if (file_exists($path)) {
    copy($path, $backupFile);
}

// Write file
if (file_put_contents($path, $content) !== false) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
}
