<?php
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$requestedFile = __DIR__ . $uri;

if ($uri !== '/' && file_exists($requestedFile)) {
    return false;
}

if (preg_match('#\.php$#', $uri)) {
    http_response_code(404);
    echo "<script>window.location.href = '404.php';</script>";
    exit;
}

require_once __DIR__ . '/index.php';
