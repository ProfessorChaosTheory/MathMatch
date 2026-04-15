<?php
// ============================================================
//  signupAction.php
//  Handles signup form submission from signup.php
// ============================================================

session_start();

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}

// ── Collect and sanitize inputs ──────────────────────────────
$uname    = trim($_POST['uname']    ?? '');
$email    = trim($_POST['email']    ?? '');
$pswd     =      $_POST['pswd']     ?? '';
$pswd2    =      $_POST['pswd2']    ?? '';
$question = trim($_POST['question'] ?? '');
$answer   = trim($_POST['answer']   ?? '');

// ── Basic server-side validation ─────────────────────────────
if ($uname === '' || $email === '' || $pswd === '' || $question === '' || $answer === '') {
    header('Location: signup.php?error=missing');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: signup.php?error=email');
    exit;
}

if ($pswd !== $pswd2) {
    header('Location: signup.php?error=mismatch');
    exit;
}

// ── Database connection ──────────────────────────────────────
$host   = 'localhost';
$dbname = 'mathmatch';
$dbuser = 'root';   // change to 'mathmatch' once that user is set up in MySQL
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
    header('Location: signup.php?error=db');
    exit;
}

// ── Check for duplicate username or email ────────────────────
$check = $pdo->prepare(
    'SELECT userID FROM users
     WHERE username = :uname OR user_email = :email
     LIMIT 1'
);
$check->execute([':uname' => $uname, ':email' => $email]);

if ($check->fetch()) {
    header('Location: signup.php?error=taken');
    exit;
}

// ── Hash password and insert ─────────────────────────────────
$hashed = password_hash($pswd, PASSWORD_DEFAULT);

// usertype = 1 (student by default)
// is_tutor, TT1_ID, TT2_ID, TT3_ID all have DB defaults (0 / NULL) — omit them
$stmt = $pdo->prepare(
    'INSERT INTO users (usertype, username, user_email, password, security_question, security_answer)
     VALUES (3, :uname, :email, :pswd, :question, :answer)'
);

try {
    $stmt->execute([
        ':uname'    => $uname,
        ':email'    => $email,
        ':pswd'     => $hashed,
        ':question' => $question,
        ':answer'   => $answer,
    ]);
} catch (PDOException $e) {
    error_log('Signup insert failed: ' . $e->getMessage());
    header('Location: signup.php?error=db');
    exit;
}

// ── Success — redirect to login with a flash message ─────────
$_SESSION['signup_success'] = true;
header('Location: login.php');
exit;
?>
