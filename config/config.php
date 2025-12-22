<?php
/**
 * Configuration file for the JSON Editor
 * 
 * Set your access key below. Users must enter this exact key to access the editor.
 */

// Path to the data.json file
define('DATA_FILE', __DIR__ . '/../data.json');

// Access key - change this to your own long random key
// You can generate one at: https://www.random.org/strings/
$accessKey = 'arinsol-2025-secure-key-50c0fef8e4288e2576abd2f24b7f55ad';

/**
 * Check if the provided key matches the access key
 */
function checkKey($providedKey) {
    global $accessKey;
    return trim($providedKey) === trim($accessKey);
}
