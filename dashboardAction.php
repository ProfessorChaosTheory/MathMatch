<?php

//  dashboardAction.php
//  Handles POST actions from dashboard.php.

session_start();

if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$userID = (int)$_SESSION['userID'];
$action = $_POST['action'] ?? '';

// ── DB connection ────────────────────────────────────────────
$host   = 'localhost';
$dbname = 'mathmatch';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser, $dbpass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    $_SESSION['dash_flash'] = 'error: Database connection failed.';
    header('Location: dashboard.php');
    exit;
}

switch ($action) {

    case 'post_question':
        $question = trim($_POST['question'] ?? '');
        if ($question === '') {
            $_SESSION['dash_flash'] = 'error: Question cannot be empty.';
            header('Location: dashboard.php');
            exit;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO questions_asked (userID, question)
             VALUES (:uid, :q)'
        );
        $stmt->execute([':uid' => $userID, ':q' => $question]);
        $_SESSION['dash_flash'] = 'Your question was posted to the board.';
        header('Location: dashboard.php');
        exit;

    default:
        header('Location: dashboard.php');
        exit;
}
?>
