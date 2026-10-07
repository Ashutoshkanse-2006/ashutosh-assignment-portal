<?php
// Single entry point for Vercel. Every request is rewritten here (see vercel.json) and this file
// runs the matching page from the app/ folder, e.g. /products.php -> app/products.php.
// Using one function keeps the project inside Vercel's free-plan function limits.

$__uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__path = trim(rawurldecode($__uri), '/');

// Folder URLs that should open a specific page
// /faculty (no trailing slash) redirects, so relative links inside that folder resolve correctly
if ($__path === 'faculty') { header('Location: /faculty/login.php', true, 302); exit; }
$__aliases = ['' => 'index.php'];
if (array_key_exists($__path, $__aliases)) {
    $__path = $__aliases[$__path];
} elseif (substr($__path, -4) !== '.php') {
    $__path .= '.php';          // allow /products as well as /products.php
}

$__root   = realpath(__DIR__ . '/../app');
$__target = realpath($__root . '/' . $__path);

if (!$__target || !is_file($__target)
    || strpos($__target, $__root . DIRECTORY_SEPARATOR) !== 0
    || strpos($__path, 'includes/') === 0) {
    http_response_code(404);
    echo '404 - Page not found';
    exit;
}

$__rel = '/' . str_replace('\\', '/', substr($__target, strlen($__root) + 1));
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $__rel;
$_SERVER['SCRIPT_FILENAME'] = $__target;

chdir(dirname($__target));   // so relative includes like "../includes/db.php" keep working
require $__target;
