<?php
/**
 * CORS Helper — Sistem Tahfidz Restu 2
 * Include file ini di SEMUA endpoint PHP API yang butuh sesi dan akses dari React frontend.
 * 
 * Cara pakai: require_once __DIR__ . '/../config/cors.php';
 */

$allowed_origins = [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'http://localhost',
    'http://localhost:5173', // Vite default port (jika dev mode berubah)
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // Fallback: izinkan semua selama development (matikan di production)
    header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
}

// WAJIB: agar cookie sesi PHP dikirim oleh browser
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
