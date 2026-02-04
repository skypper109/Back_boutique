<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 * 
 * Custom server.php for Tauri integration to avoid "Broken pipe" and path resolution issues.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality.
// We use __DIR__ to ensure paths are always relative to the backend root.
$publicPath = __DIR__ . '/public';

if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false;
}

// Ensure index.php exists before requiring it
$indexPath = $publicPath . '/index.php';

if (!file_exists($indexPath)) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Internal Server Error: index.php not found at $indexPath";
    exit(1);
}

require_once $indexPath;
