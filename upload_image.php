<?php
// Define log file path
define('DEBUG_LOG', __DIR__ . '/upload_debug.txt');

function logDebug($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(DEBUG_LOG, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Clear previous log on new request (optional, maybe keep append for now)
// file_put_contents(DEBUG_LOG, ""); 

logDebug("Script started.");

// Prevent any HTML output from PHP errors disrupting the JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any unwanted output (warnings, notices, etc.)
ob_start();

try {
    logDebug("Loading config...");
    require_once __DIR__ . '/config/config.php';
    logDebug("Config loaded.");

    // Clear any buffered output
    ob_clean();

    // Set JSON response header
    header('Content-Type: application/json');

    logDebug("Checking POST/FILES...");
    logDebug("FILES: " . print_r($_FILES, true));
    logDebug("POST: " . print_r($_POST, true));
    logDebug("SERVER CONTENT_LENGTH: " . ($_SERVER['CONTENT_LENGTH'] ?? 'Not Set'));

    // Check if POST max size was exceeded (which empties $_POST and $_FILES)
    if (empty($_FILES) && empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $msg = 'The uploaded file is too large. It exceeds the server\'s post_max_size limit.';
        logDebug("Error: $msg");
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    // Check authentication via session
    logDebug("Checking authentication...");
    if (!isAuthenticated()) {
        logDebug("Auth failed.");
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized. Please login first.']);
        exit;
    }
    logDebug("Auth success.");

    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'No file uploaded or upload error.';
        if (isset($_FILES['image']['error'])) {
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errorMsg = 'File exceeds upload_max_filesize in php.ini';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg = 'File exceeds MAX_FILE_SIZE in HTML form';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMsg = 'File was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg = 'No file was uploaded';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMsg = 'Missing a temporary folder';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMsg = 'Failed to write file to disk';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMsg = 'A PHP extension stopped the file upload';
                    break;
            }
        }
        logDebug("Upload Error: $errorMsg");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $errorMsg]);
        exit;
    }

    $file = $_FILES['image'];
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    logDebug("File Details - Name: $fileName, Tmp: $fileTmp, Size: $fileSize");

    // Validate file type
    $allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/ogg'
    ];
    
    // Check if mime_content_type exists
    if (function_exists('mime_content_type')) {
        $fileType = mime_content_type($fileTmp);
    } else {
        $fileType = $file['type']; // Fallback (less secure)
        logDebug("Warning: mime_content_type function missing, using header type.");
    }
    
    logDebug("Detected Mime Type: $fileType");

    if (!in_array($fileType, $allowedTypes)) {
        logDebug("Invalid file type: $fileType");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: Images (JPG, PNG, GIF, WEBP) and Videos (MP4, WEBM).']);
        exit;
    }

    // Validate file size (max 50MB for videos)
    if ($fileSize > 50 * 1024 * 1024) {
        logDebug("File too large: $fileSize");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File size exceeds 50MB limit']);
        exit;
    }

    // Check if fixed filename is provided (for logo, etc.)
    $fixedFilename = isset($_POST['fixedFilename']) ? $_POST['fixedFilename'] : null;

    if ($fixedFilename) {
        // Use the fixed filename
        $fileName = $fixedFilename;
        logDebug("Using fixed filename: $fileName");
    } else {
        // Sanitize filename and add timestamp
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $fileName = time() . '_' . $fileName; // Add timestamp to prevent conflicts
        logDebug("Generated filename: $fileName");
    }

    // Set upload directory
    $uploadDir = __DIR__ . '/assets/';

    // Create assets directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        logDebug("Creating assets dir: $uploadDir");
        if (!mkdir($uploadDir, 0755, true)) {
             logDebug("Failed to create assets dir");
        }
    }

    // Create backup of old file if it exists
    $targetFile = $uploadDir . $fileName;
    if (file_exists($targetFile)) {
        logDebug("Target exists, backing up: $targetFile");
        $backupDir = __DIR__ . '/data-changes';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $backupFile = $backupDir . '/image_backup_' . date('Y-m-d_H-i-s') . '_' . basename($targetFile);
        if (copy($targetFile, $backupFile)) {
            logDebug("Backup created: $backupFile");
        } else {
            logDebug("Backup failed");
        }
    }

    // Move uploaded file
    logDebug("Moving file to: $targetFile");
    if (move_uploaded_file($fileTmp, $targetFile)) {
        logDebug("Move successful.");
        echo json_encode([
            'success' => true,
            'message' => 'Upload successful',
            'filename' => $fileName,
            'url' => 'assets/' . $fileName
        ]);
    } else {
        logDebug("Move failed.");
        http_response_code(500);
        $lastError = error_get_last();
        logDebug("Last PHP Error: " . print_r($lastError, true));
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file. Check directory permissions.']);
    }

} catch (Exception $e) {
    logDebug("Exception caught: " . $e->getMessage());
    logDebug("Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server exception: ' . $e->getMessage()]);
}

logDebug("Script finished.");
?>
