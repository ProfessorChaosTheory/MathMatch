<?php
// ============================================================
//  loginAction.php
//  Handles POST from login.php, validates credentials against
//  the mathmatch database, and redirects accordingly.
// ============================================================

session_start();

// ── 1. Only accept POST requests ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// ── 2. Collect and sanitise inputs ──────────────────────────
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// If either field is empty, send straight back to login
if ($username === '' || $password === '') {
    header('Location: login.php');
    exit;
}

// ── 3. Database connection ───────────────────────────────────
// Adjust host / dbname / user / pass to match your XAMPP setup.
// By default XAMPP uses root with no password.
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
    // Don't expose DB errors to the browser in production.
    // Log them instead and show the failure page.
    error_log('DB connection failed: ' . $e->getMessage());
    header('Location: loginFailure.php');
    exit;
}

// ── 4. Look up the user by username ─────────────────────────
// We use a prepared statement to prevent SQL injection.
$stmt = $pdo->prepare(
    'SELECT userID, username, user_email, password, usertype, is_tutor
     FROM users
     WHERE username = :username
     LIMIT 1'
);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

// ── 5. Verify password ──────────────────────────────────────
// password_verify() checks the submitted password against the
// bcrypt hash stored by password_hash() at registration time.
if (!$user || !password_verify($password, $user['password'])) {
    header('Location: loginFailure.php');
    exit;
}

// ── 6. Login succeeded — populate session ───────────────────
// Regenerate the session ID to prevent session fixation attacks.
session_regenerate_id(true);

$_SESSION['loggedin']  = true;
$_SESSION['userID']    = $user['userID'];
$_SESSION['username']  = $user['username'];
$_SESSION['user_email'] = $user['user_email'];
$_SESSION['usertype']  = $user['usertype'];
$_SESSION['is_tutor']  = $user['is_tutor'];

// ── 7. Redirect to success page ─────────────────────────────
header('Location: loginSuccess.php');
exit;
?>
