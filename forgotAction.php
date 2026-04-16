<?php
// ============================================================
//  forgotAction.php
//  Step 1 handler: validates the submitted username, stores
//  the security question in session, redirects to secQuestion.php
// ============================================================

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot.php');
    exit;
}

$username = trim($_POST['username'] ?? '');

if ($username === '') {
    header('Location: forgot.php');
    exit;
}

// ── Database connection ──────────────────────────────────────
$host   = 'localhost';
$dbname = 'mathmatch';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    header('Location: forgotFailure.php');
    exit;
}

// ── Look up username ─────────────────────────────────────────
$stmt = $pdo->prepare(
    'SELECT userID, username, security_question
     FROM users
     WHERE username = :username
     LIMIT 1'
);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user) {
    // Username not found — go to failure page
    header('Location: forgotFailure.php');
    exit;
}

// ── Store in session and move to security question page ──────
$_SESSION['reset_userID']   = $user['userID'];
$_SESSION['reset_username'] = $user['username'];
$_SESSION['reset_question'] = $user['security_question'];

header('Location: secQuestion.php');
exit;
?>
