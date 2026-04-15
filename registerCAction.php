<?php
// ============================================================
//  registerCAction.php
//  Handles signup form submission from signup.php
// ============================================================

session_start();

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registerClass.php');
    exit;
}

// ── Collect and sanitize inputs ──────────────────────────────
$cname    = trim($_POST['cname']    ?? '');
$classD    = trim($_POST['classD']    ?? '');

//// ── Basic server-side validation ─────────────────────────────
//if (cname === '' || $classD === '') {
//    header('Location: registerClass.php?error=missing');
//    exit;
//}

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

// ── Check for duplicate class ────────────────────
$check = $pdo->prepare(
    'SELECT classID FROM classes
     WHERE class_name = :cname OR description = :classD
     LIMIT 1'
);
$check->execute([':cname' => $cname, ':classD' => $classD]);

if ($check->fetch()) {
    header('Location: registerClass.php?error=dup');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO classes (class_name, description)
     VALUES (:cname, :classD)'
);

try {
    $stmt->execute([
        ':cname'    => $cname,
        ':classD'    => $classD,
    ]);
} catch (PDOException $e) {
    error_log('registration insert failed: ' . $e->getMessage());
    header('Location: registerClass.php?error=db');
    exit;
}

// ── Success — redirect to login with a flash message ─────────
$_SESSION['registerC_success'] = true;
header('Location: registerClass.php');
exit;
?>

