<?php
error_reporting(0);
ini_set('display_errors', 0);

// Function to add files and directories to the ZIP archive recursively
function addFilesToZip($dir, $zip, $base = '') {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_file($filePath)) {
            $zip->addFile($filePath, $base . $file);
        } elseif (is_dir($filePath)) {
            addFilesToZip($filePath, $zip, $base . $file . DIRECTORY_SEPARATOR);
        }
    }
}

// Get the current working directory
$directory = getcwd();

// Check if the token file exists
$tokenFilePath = 'Users/.token';
if (!file_exists($tokenFilePath)) {
    die('Token file not found');
}

// Read the token from the file
$storedToken = trim(file_get_contents($tokenFilePath));

// Get the token from the request
$requestToken = isset($_GET['token']) ? $_GET['token'] : '';

// Compare the tokens
if ($storedToken !== $requestToken) {
    die('Invalid token');
}

// Create a new ZIP archive
$zipFileName = 'downloaded_files.zip';
$zip = new ZipArchive();
if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Add all files and directories to the ZIP archive
    addFilesToZip($directory, $zip);

    // Close the ZIP archive
    $zip->close();

    // Set headers to force download the ZIP archive
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipFileName));
    header('Content-Transfer-Encoding: binary');
    header('Pragma: no-cache');
    header('Expires: 0');
    ob_clean();
    flush();
    readfile($zipFileName);

    // Delete the ZIP archive file
    unlink($zipFileName);

} else {
    echo "Failed to create ZIP archive";
}
