<?php
// ============================================================
//  cleanup.php
//  Purges expired scheduling data silently.
//  Include this on any frequently-visited page after session_start().
//
//  Opens its own PDO connection so it is not affected by
//  any other database connections opened by includes.
// ============================================================

try {
    $cleanupPdo = new PDO(
        'mysql:host=localhost;dbname=mathmatch;charset=utf8mb4',
        'root', '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Cleanup DB connection failed: ' . $e->getMessage());
    return; // Fail silently — cleanup is non-critical
}

$yesterday = date('Y-m-d', strtotime('yesterday'));

// 1. Remove past availability blocks
$cleanupPdo->prepare(
    'DELETE FROM availability_blocks WHERE date < :d'
)->execute([':d' => $yesterday]);

// 2. Remove past session rows — ON DELETE SET NULL on
//    users.TT1_ID/TT2_ID/TT3_ID clears slot references automatically
$cleanupPdo->prepare(
    'DELETE FROM session WHERE date < :d'
)->execute([':d' => $yesterday]);

$cleanupPdo = null;
