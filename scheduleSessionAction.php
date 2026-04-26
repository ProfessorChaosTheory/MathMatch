<?php
// ============================================================
//  scheduleSessionAction.php
//  Handles POST from scheduleSession.php
//    book_session — inserts a session row from a block slot
// ============================================================
session_start();

if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$userID   = (int)$_SESSION['userID'];
$usertype = (int)$_SESSION['usertype'];
$username = $_SESSION['username'];

if ($usertype !== 2 && $usertype !== 3) {
    $_SESSION['schedule_flash'] = 'error: Only students and tutors can book sessions.';
    header('Location: scheduleSession.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: scheduleSession.php');
    exit;
}

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
    $_SESSION['schedule_flash'] = 'error: Database connection failed.';
    header('Location: scheduleSession.php');
    exit;
}

// Purge expired sessions/blocks so TT slots and counts are accurate
include 'cleanup.php';

switch ($action) {

    case 'book_session':
        $blockID     = (int)($_POST['block_id']     ?? 0);
        $tutorID     = (int)($_POST['tutor_id']     ?? 0);
        $date        = $_POST['date']               ?? '';
        $time        = $_POST['time']               ?? '';
        $slotMins    = (int)($_POST['slot_minutes'] ?? 0);

        if ($blockID === 0 || $tutorID === 0 || $date === '' || $time === '') {
            $_SESSION['schedule_flash'] = 'error: Invalid booking request.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Confirm the block still exists and belongs to the stated tutor
        $chkBlock = $pdo->prepare(
            'SELECT block_ID, ClassID FROM availability_blocks
             WHERE block_ID  = :bid
               AND TutorID   = :tid
               AND date      = :date
               AND start_time <= :time1
               AND end_time   >  :time2'
        );
        $chkBlock->execute([
            ':bid'   => $blockID,
            ':tid'   => $tutorID,
            ':date'  => $date,
            ':time1' => $time,
            ':time2' => $time,
        ]);
        $block = $chkBlock->fetch();

        if (!$block) {
            $_SESSION['schedule_flash'] = 'error: That slot is no longer available.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Confirm the specific time slot isn't already booked
        $chkSlot = $pdo->prepare(
            'SELECT session_ID FROM session
             WHERE TutorID = :tid
               AND date    = :date
               AND time    = :time
               AND is_scheduled = 1'
        );
        $chkSlot->execute([':tid' => $tutorID, ':date' => $date, ':time' => $time]);

        if ($chkSlot->fetch()) {
            $_SESSION['schedule_flash'] = 'error: That slot was just booked by someone else.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Check the user hasn't hit the 3-session cap (future sessions only)
        $countStmt = $pdo->prepare(
            'SELECT COUNT(*) AS cnt FROM session
             WHERE StudentID = :uid
               AND is_scheduled = 1
               AND date >= :today'
        );
        $countStmt->execute([':uid' => $userID, ':today' => date('Y-m-d')]);
        if ((int)$countStmt->fetch()['cnt'] >= 3) {
            $_SESSION['schedule_flash'] = 'error: You already have 3 sessions scheduled. Cancel one first.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Insert the session row
        $insert = $pdo->prepare(
            'INSERT INTO session (date, time, ClassID, TutorID, StudentID, is_scheduled)
             VALUES (:date, :time, :classID, :tid, :uid, 1)'
        );
        $insert->execute([
            ':date'    => $date,
            ':time'    => $time,
            ':classID' => $block['ClassID'],
            ':tid'     => $tutorID,
            ':uid'     => $userID,
        ]);
        $newSessionID = (int)$pdo->lastInsertId();

        // Fill the next open TT slot on the user's row
        $slots = $pdo->prepare(
            'SELECT TT1_ID, TT2_ID, TT3_ID FROM users WHERE userID = :uid'
        );
        $slots->execute([':uid' => $userID]);
        $row     = $slots->fetch();
        $slotCol = null;
        if ($row['TT1_ID'] === null)      $slotCol = 'TT1_ID';
        elseif ($row['TT2_ID'] === null)  $slotCol = 'TT2_ID';
        elseif ($row['TT3_ID'] === null)  $slotCol = 'TT3_ID';

        if ($slotCol) {
            $upd = $pdo->prepare("UPDATE users SET $slotCol = :sid WHERE userID = :uid");
            $upd->execute([':sid' => $newSessionID, ':uid' => $userID]);
        }

        // Notify the tutor
        $dateStr = date('M j, Y', strtotime($date));
        $timeStr = date('g:i A',  strtotime($time));
        $msg     = "$username has booked your $slotMins-minute slot on $dateStr at $timeStr.";
        $notify  = $pdo->prepare(
            'INSERT INTO notifications (userID, message) VALUES (:uid, :msg)'
        );
        $notify->execute([':uid' => $tutorID, ':msg' => $msg]);

        $_SESSION['schedule_flash'] = "Session booked for $dateStr at $timeStr.";
        header('Location: dashboard.php');
        exit;

    default:
        header('Location: scheduleSession.php');
        exit;
}
?>
