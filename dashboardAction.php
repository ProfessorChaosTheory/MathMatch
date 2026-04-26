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

$userID   = (int)$_SESSION['userID'];
$usertype = (int)$_SESSION['usertype'];
$username = $_SESSION['username'];
$action   = $_POST['action'] ?? '';

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

    // ── Student cancels a session they booked ───────────────
    case 'cancel_session':
        $sessionID = (int)($_POST['session_id'] ?? 0);

        if ($sessionID === 0) {
            $_SESSION['dash_flash'] = 'error: Invalid cancellation request.';
            header('Location: dashboard.php');
            exit;
        }

        // Fetch session — must be booked by this user as student
        $chk = $pdo->prepare(
            'SELECT s.session_ID, s.TutorID, s.date, s.time,
                    u.username AS tutor_name
             FROM session s
             JOIN users u ON u.userID = s.TutorID
             WHERE s.session_ID = :sid
               AND s.StudentID  = :uid
               AND s.is_scheduled = 1'
        );
        $chk->execute([':sid' => $sessionID, ':uid' => $userID]);
        $sess = $chk->fetch();

        if (!$sess) {
            $_SESSION['dash_flash'] = 'error: Session not found or already cancelled.';
            header('Location: dashboard.php');
            exit;
        }

        // Revert to offered state
        $revert = $pdo->prepare(
            'UPDATE session SET StudentID = NULL, is_scheduled = 0
             WHERE session_ID = :sid'
        );
        $revert->execute([':sid' => $sessionID]);

        // Clear whichever TT slot holds this session ID
        foreach (['TT1_ID','TT2_ID','TT3_ID'] as $col) {
            $clr = $pdo->prepare(
                "UPDATE users SET $col = NULL
                 WHERE userID = :uid AND $col = :sid"
            );
            $clr->execute([':uid' => $userID, ':sid' => $sessionID]);
        }

        // Notify tutor
        $dateStr = date('M j, Y', strtotime($sess['date']));
        $timeStr = date('g:i A',  strtotime($sess['time']));
        $msg     = "$username has cancelled their session with you on $dateStr at $timeStr. The slot is open again.";
        $notify  = $pdo->prepare(
            'INSERT INTO notifications (userID, message) VALUES (:uid, :msg)'
        );
        $notify->execute([':uid' => $sess['TutorID'], ':msg' => $msg]);

        $_SESSION['dash_flash'] = "Session on $dateStr at $timeStr cancelled.";
        header('Location: dashboard.php');
        exit;

    // ── Tutor cancels one of their booked appointments ───────
    case 'cancel_tutor_session':
        $sessionID = (int)($_POST['session_id'] ?? 0);

        if ($sessionID === 0) {
            $_SESSION['dash_flash'] = 'error: Invalid cancellation request.';
            header('Location: dashboard.php');
            exit;
        }

        // Fetch session — must belong to this tutor
        $chk = $pdo->prepare(
            'SELECT s.session_ID, s.StudentID, s.date, s.time,
                    u.username AS student_name
             FROM session s
             JOIN users u ON u.userID = s.StudentID
             WHERE s.session_ID  = :sid
               AND s.TutorID     = :uid
               AND s.is_scheduled = 1'
        );
        $chk->execute([':sid' => $sessionID, ':uid' => $userID]);
        $sess = $chk->fetch();

        if (!$sess) {
            $_SESSION['dash_flash'] = 'error: Session not found or already cancelled.';
            header('Location: dashboard.php');
            exit;
        }

        // Revert to offered state
        $revert = $pdo->prepare(
            'UPDATE session SET StudentID = NULL, is_scheduled = 0
             WHERE session_ID = :sid'
        );
        $revert->execute([':sid' => $sessionID]);

        // Clear the TT slot on the student's row
        foreach (['TT1_ID','TT2_ID','TT3_ID'] as $col) {
            $clr = $pdo->prepare(
                "UPDATE users SET $col = NULL
                 WHERE userID = :uid AND $col = :sid"
            );
            $clr->execute([':uid' => $sess['StudentID'], ':sid' => $sessionID]);
        }

        // Notify the student
        $dateStr = date('M j, Y', strtotime($sess['date']));
        $timeStr = date('g:i A',  strtotime($sess['time']));
        $msg     = "$username has cancelled your tutoring session on $dateStr at $timeStr.";
        $notify  = $pdo->prepare(
            'INSERT INTO notifications (userID, message) VALUES (:uid, :msg)'
        );
        $notify->execute([':uid' => $sess['StudentID'], ':msg' => $msg]);

        $_SESSION['dash_flash'] = "Appointment on $dateStr at $timeStr cancelled. Student notified.";
        header('Location: dashboard.php');
        exit;

    default:
        header('Location: dashboard.php');
        exit;
}
?>
