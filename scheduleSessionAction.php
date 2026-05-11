<?php
// ============================================================
// scheduleSessionAction.php
// Handles POST from scheduleSession.php
// ============================================================

session_start();

if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$userID   = (int)$_SESSION['userID'];
$usertype = (int)$_SESSION['usertype'];
$username = $_SESSION['username'];

if ($usertype !== 1 && $usertype !== 2 && $usertype !== 3) {
    $_SESSION['schedule_flash'] = 'error: You must be logged in.';
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
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['schedule_flash'] = 'error: Database connection failed.';
    header('Location: scheduleSession.php');
    exit;
}

// Cleanup expired blocks/sessions
include 'cleanup.php';

switch ($action) {

    // =========================================================
    // BOOK SESSION
    // =========================================================
    case 'book_session':

        $blockID  = (int)($_POST['block_id'] ?? 0);
        $tutorID  = (int)($_POST['tutor_id'] ?? 0);
        $date     = $_POST['date'] ?? '';
        $time     = $_POST['time'] ?? '';
        $slotMins = (int)($_POST['slot_minutes'] ?? 0);

        if ($blockID === 0 || $tutorID === 0 || $date === '' || $time === '') {
            $_SESSION['schedule_flash'] = 'error: Invalid booking request.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Verify block exists
        $chkBlock = $pdo->prepare(
            'SELECT block_ID, ClassID
             FROM availability_blocks
             WHERE block_ID = :bid
               AND TutorID = :tid
               AND date = :date
               AND start_time <= :time1
               AND end_time > :time2'
        );

        $chkBlock->execute([
            ':bid' => $blockID,
            ':tid' => $tutorID,
            ':date' => $date,
            ':time1' => $time,
            ':time2' => $time
        ]);

        $block = $chkBlock->fetch();

        if (!$block) {
            $_SESSION['schedule_flash'] = 'error: Slot no longer available.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Check if already booked
        $chkSlot = $pdo->prepare(
            'SELECT session_ID
             FROM session
             WHERE TutorID = :tid
               AND date = :date
               AND time = :time
               AND is_scheduled = 1'
        );

        $chkSlot->execute([
            ':tid' => $tutorID,
            ':date' => $date,
            ':time' => $time
        ]);

        if ($chkSlot->fetch()) {
            $_SESSION['schedule_flash'] =
                'error: That slot was just booked.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Limit 3 future sessions
        $countStmt = $pdo->prepare(
            'SELECT COUNT(*) AS cnt
             FROM session
             WHERE StudentID = :uid
               AND is_scheduled = 1
               AND date >= :today'
        );

        $countStmt->execute([
            ':uid' => $userID,
            ':today' => date('Y-m-d')
        ]);

        if ((int)$countStmt->fetch()['cnt'] >= 3) {
            $_SESSION['schedule_flash'] =
                'error: Max 3 scheduled sessions.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Insert session
        $insert = $pdo->prepare(
            'INSERT INTO session
             (date, time, ClassID, TutorID, StudentID, is_scheduled)
             VALUES (:date, :time, :classID, :tid, :uid, 1)'
        );

        $insert->execute([
            ':date' => $date,
            ':time' => $time,
            ':classID' => $block['ClassID'],
            ':tid' => $tutorID,
            ':uid' => $userID
        ]);

        $newSessionID = (int)$pdo->lastInsertId();

        // Fill TT slot
        $slots = $pdo->prepare(
            'SELECT TT1_ID, TT2_ID, TT3_ID
             FROM users
             WHERE userID = :uid'
        );

        $slots->execute([':uid' => $userID]);
        $row = $slots->fetch();

        $slotCol = null;

        if ($row['TT1_ID'] === null) $slotCol = 'TT1_ID';
        elseif ($row['TT2_ID'] === null) $slotCol = 'TT2_ID';
        elseif ($row['TT3_ID'] === null) $slotCol = 'TT3_ID';

        if ($slotCol) {
            $upd = $pdo->prepare(
                "UPDATE users
                 SET $slotCol = :sid
                 WHERE userID = :uid"
            );

            $upd->execute([
                ':sid' => $newSessionID,
                ':uid' => $userID
            ]);
        }

        $_SESSION['schedule_flash'] = 'Session booked.';
        header('Location: dashboard.php');
        exit;


    // =========================================================
    // REMOVE SLOT
    // =========================================================
    case 'remove_slot':

        if ($usertype !== 1) {
            $_SESSION['schedule_flash'] =
                'error: Only admins can remove slots.';
            header('Location: scheduleSession.php');
            exit;
        }

        $blockID  = (int)($_POST['block_id'] ?? 0);
        $tutorID  = (int)($_POST['tutor_id'] ?? 0);
        $date     = $_POST['date'] ?? '';
        $time     = $_POST['time'] ?? '';
        $slotMins = (int)($_POST['slot_minutes'] ?? 0);

        if ($blockID === 0 || $date === '' || $time === '') {
            $_SESSION['schedule_flash'] = 'error: Invalid request.';
            header('Location: scheduleSession.php');
            exit;
        }

        // Check booked
        $chk = $pdo->prepare(
            'SELECT session_ID
             FROM session
             WHERE TutorID = :tid
               AND date = :date
               AND time = :time
               AND is_scheduled = 1'
        );

        $chk->execute([
            ':tid' => $tutorID,
            ':date' => $date,
            ':time' => $time
        ]);

        if ($chk->fetch()) {
            $_SESSION['schedule_flash'] =
                'error: Cancel booking first.';
            header('Location: scheduleSession.php');
            exit;
        }

        $blk = $pdo->prepare(
            'SELECT start_time, end_time, slot_minutes, ClassID
             FROM availability_blocks
             WHERE block_ID = :bid AND TutorID = :tid'
        );

        $blk->execute([
            ':bid' => $blockID,
            ':tid' => $tutorID
        ]);

        $block = $blk->fetch();

        if (!$block) {
            $_SESSION['schedule_flash'] = 'error: Block not found.';
            header('Location: scheduleSession.php');
            exit;
        }

        $blockStart = strtotime($block['start_time']);
        $blockEnd   = strtotime($block['end_time']);
        $stepSecs   = $slotMins * 60;

        $allSlots = [];

        for ($t = $blockStart; $t + $stepSecs <= $blockEnd; $t += $stepSecs) {
            $allSlots[] = date('H:i:s', $t);
        }

        $kept = array_values(
            array_filter($allSlots, fn($s) => $s !== $time)
        );

        $newBlocks = [];

        if (!empty($kept)) {
            $runStart = $kept[0];
            $prev = strtotime($kept[0]);

            for ($i = 1; $i < count($kept); $i++) {
                $curr = strtotime($kept[$i]);

                if ($curr - $prev > $stepSecs) {
                    $newBlocks[] = [
                        'start' => $runStart,
                        'end' => date('H:i:s', $prev + $stepSecs)
                    ];
                    $runStart = $kept[$i];
                }

                $prev = $curr;
            }

            $newBlocks[] = [
                'start' => $runStart,
                'end' => date('H:i:s', $prev + $stepSecs)
            ];
        }

        $del = $pdo->prepare(
            'DELETE FROM availability_blocks WHERE block_ID = :bid'
        );

        $del->execute([':bid' => $blockID]);

        $ins = $pdo->prepare(
            'INSERT INTO availability_blocks
            (TutorID, date, start_time, end_time, slot_minutes, ClassID)
            VALUES (:tid, :date, :start, :end, :mins, :classID)'
        );

        foreach ($newBlocks as $nb) {
            $ins->execute([
                ':tid' => $tutorID,
                ':date' => $date,
                ':start' => $nb['start'],
                ':end' => $nb['end'],
                ':mins' => $slotMins,
                ':classID' => $block['ClassID']
            ]);
        }

        $_SESSION['schedule_flash'] = 'Slot removed successfully.';
        header('Location: scheduleSession.php');
        exit;


    // =========================================================
    // CANCEL BOOKING
    // =========================================================
    case 'cancel_booking':

        if ($usertype !== 1) {
            $_SESSION['schedule_flash'] =
                'error: Only admins can cancel bookings.';
            header('Location: scheduleSession.php');
            exit;
        }

        $tutorID = (int)($_POST['tutor_id'] ?? 0);
        $date    = $_POST['date'] ?? '';
        $time    = $_POST['time'] ?? '';

        if ($tutorID === 0 || $date === '' || $time === '') {
            $_SESSION['schedule_flash'] =
                'error: Invalid cancellation request.';
            header('Location: scheduleSession.php');
            exit;
        }

        $find = $pdo->prepare(
            'SELECT session_ID, StudentID
             FROM session
             WHERE TutorID = :tid
               AND date = :date
               AND time = :time
               AND is_scheduled = 1'
        );

        $find->execute([
            ':tid' => $tutorID,
            ':date' => $date,
            ':time' => $time
        ]);

        $session = $find->fetch();

        if (!$session) {
            $_SESSION['schedule_flash'] = 'error: Booking not found.';
            header('Location: scheduleSession.php');
            exit;
        }

        $sessionID = (int)$session['session_ID'];
        $studentID = (int)$session['StudentID'];

        $clear = $pdo->prepare(
            'UPDATE users
             SET
                TT1_ID = CASE WHEN TT1_ID = :sid1 THEN NULL ELSE TT1_ID END,
                TT2_ID = CASE WHEN TT2_ID = :sid2 THEN NULL ELSE TT2_ID END,
                TT3_ID = CASE WHEN TT3_ID = :sid3 THEN NULL ELSE TT3_ID END
             WHERE userID = :uid'
        );

        $clear->execute([
            ':sid1' => $sessionID,
            ':sid2' => $sessionID,
            ':sid3' => $sessionID,
            ':uid'  => $studentID
        ]);

        $delete = $pdo->prepare(
            'DELETE FROM session WHERE session_ID = :sid'
        );

        $delete->execute([':sid' => $sessionID]);

        $_SESSION['schedule_flash'] =
            'Booking cancelled successfully.';
        header('Location: scheduleSession.php');
        exit;


    default:
        header('Location: scheduleSession.php');
        exit;
}
?>