<?php
// ============================================================
//  resetAction.php
//  Validates the new password, hashes it, updates the database,
//  clears the reset session variables, redirects to login.
// ============================================================

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['reset_userID'])) {
    header('Location: forgot.php');
    exit;
}

$newPassword     = $_POST['new_password']     ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// ── Basic validation ─────────────────────────────────────────
if ($newPassword === '' || $newPassword !== $confirmPassword) {
    header('Location: showPassword.php');
    exit;
}

if (strlen($newPassword) < 6) {
    header('Location: showPassword.php');
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

// ── Hash and save the new password ──────────────────────────
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'UPDATE users SET password = :password WHERE userID = :userID'
);
$stmt->execute([
    ':password' => $hash,
    ':userID'   => $_SESSION['reset_userID'],
]);

// ── Clean up reset session variables ────────────────────────
unset(
    $_SESSION['reset_userID'],
    $_SESSION['reset_username'],
    $_SESSION['reset_question'],
    $_SESSION['reset_passhash']
);

// ── Send to login with a success flag ───────────────────────
$_SESSION['reset_success'] = true;
header('Location: login.php');
exit;
?>
