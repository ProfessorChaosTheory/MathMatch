<?php
// ============================================================
//  secQuestionAction.php
//  Step 2 handler: validates the security answer and redirects
//  to showPassword.php on success or forgotFailure.php on fail.
// ============================================================

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['reset_userID'])) {
    header('Location: forgot.php');
    exit;
}

$answer = trim($_POST['security_answer'] ?? '');

if ($answer === '') {
    header('Location: secQuestion.php');
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

// ── Fetch the stored security answer and password ────────────
$stmt = $pdo->prepare(
    'SELECT security_answer, password
     FROM users
     WHERE userID = :userID
     LIMIT 1'
);
$stmt->execute([':userID' => $_SESSION['reset_userID']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: forgotFailure.php');
    exit;
}

// Case-insensitive comparison so "chance" matches "Chance"
if (strcasecmp(trim($user['security_answer']), $answer) !== 0) {
    header('Location: forgotFailure.php');
    exit;
}

// ── Answer correct — store password hash in session ──────────
// NOTE: We store the hash here. showPassword.php will not display
// the hash — it will display a note explaining that passwords are
// hashed and cannot be recovered, and offer a reset link instead.
// This is the correct and secure behaviour.
$_SESSION['reset_passhash'] = $user['password'];

header('Location: showPassword.php');
exit;
?>
