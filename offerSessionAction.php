<?php
// ============================================================
//  offerSessionAction.php
//  Handles POST from offerSession.php
//    offer_session   — insert a new availability_blocks row
//    rescind_session — delete a block (only if no bookings)
// ============================================================
session_start();

if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$userID   = (int)$_SESSION['userID'];
$usertype = (int)$_SESSION['usertype'];

if ($usertype !== 2 && $usertype !== 1) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: offerSession.php');
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
    $_SESSION['offer_flash'] = 'error: Database connection failed.';
    header('Location: offerSession.php');
    exit;
}

switch ($action) {

    // ── Offer a new availability block ───────────────────────
    case 'offer_session':
        $date      = $_POST['date']       ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $endTime   = $_POST['end_time']   ?? '';
        $slotMins  = (int)($_POST['slot_minutes'] ?? 60);

        if ($date === '' || $startTime === '' || $endTime === '') {
            $_SESSION['offer_flash'] = 'error: Date, start time, and end time are required.';
            header('Location: offerSession.php');
            exit;
        }

        if ($date < date('Y-m-d')) {
            $_SESSION['offer_flash'] = 'error: Please choose a future date.';
            header('Location: offerSession.php');
            exit;
        }

        if ($endTime <= $startTime) {
            $_SESSION['offer_flash'] = 'error: End time must be after start time.';
            header('Location: offerSession.php');
            exit;
        }

        // Verify at least one full slot fits
        $startTs = strtotime($startTime);
        $endTs   = strtotime($endTime);
        $slots   = (int)(($endTs - $startTs) / ($slotMins * 60));
        if ($slots < 1) {
            $_SESSION['offer_flash'] = 'error: The slot length is longer than the block duration.';
            header('Location: offerSession.php');
            exit;
        }

        // Handle class
        $classID     = null;
        $className   = trim($_POST['class_name'] ?? '');
        $classIDPost = (int)($_POST['classID'] ?? 0);

        if ($classIDPost > 0) {
            $classID = $classIDPost;
        } elseif ($className !== '') {
            $ins = $pdo->prepare(
                'INSERT INTO classes (class_name, description) VALUES (:name, :desc)'
            );
            $ins->execute([':name' => $className, ':desc' => '']);
            $classID = (int)$pdo->lastInsertId();
        }

        $stmt = $pdo->prepare(
            'INSERT INTO availability_blocks
                (TutorID, date, start_time, end_time, slot_minutes, ClassID)
             VALUES
                (:uid, :date, :start, :end, :mins, :classID)'
        );
        $stmt->execute([
            ':uid'     => $userID,
            ':date'    => $date,
            ':start'   => $startTime,
            ':end'     => $endTime,
            ':mins'    => $slotMins,
            ':classID' => $classID,
        ]);

        $_SESSION['offer_flash'] =
            "Block offered: $slots slot" . ($slots !== 1 ? 's' : '') .
            " of $slotMins minutes on $date.";
        header('Location: offerSession.php');
        exit;

    // ── Partial rescind — remove selected slots, rebuild block ──
    case 'partial_rescind':
        $blockID    = (int)($_POST['block_id']     ?? 0);
        $slotMins   = (int)($_POST['slot_minutes'] ?? 0);
        $blockDate  =       $_POST['block_date']   ?? '';
        $startTime  =       $_POST['start_time']   ?? '';
        $endTime    =       $_POST['end_time']      ?? '';
        $classID    = ($_POST['class_id'] ?? '') !== '' ? (int)$_POST['class_id'] : null;
        $rescindArr =       $_POST['rescind_slots'] ?? [];

        if ($blockID === 0 || $slotMins === 0 || empty($rescindArr)) {
            $_SESSION['offer_flash'] = 'error: Invalid partial rescind request.';
            header('Location: offerSession.php');
            exit;
        }

        // Confirm block belongs to this tutor
        $chk = $pdo->prepare(
            'SELECT block_ID FROM availability_blocks
             WHERE block_ID = :bid AND TutorID = :uid'
        );
        $chk->execute([':bid' => $blockID, ':uid' => $userID]);
        if (!$chk->fetch()) {
            $_SESSION['offer_flash'] = 'error: Block not found.';
            header('Location: offerSession.php');
            exit;
        }

        // Safety: confirm none of the selected slots are booked
        foreach ($rescindArr as $slotTime) {
            $chkBooked = $pdo->prepare(
                'SELECT session_ID FROM session
                 WHERE TutorID = :uid AND date = :date
                   AND time = :time AND is_scheduled = 1'
            );
            $chkBooked->execute([
                ':uid'  => $userID,
                ':date' => $blockDate,
                ':time' => $slotTime,
            ]);
            if ($chkBooked->fetch()) {
                $_SESSION['offer_flash'] = 'error: One or more selected slots are already booked. Refresh and try again.';
                header('Location: offerSession.php');
                exit;
            }
        }

        // Build the full ordered slot list for this block
        $blockStart = strtotime($startTime);
        $blockEnd   = strtotime($endTime);
        $stepSecs   = $slotMins * 60;
        $allSlots   = [];
        for ($t = $blockStart; $t + $stepSecs <= $blockEnd; $t += $stepSecs) {
            $allSlots[] = date('H:i:s', $t);
        }

        // Mark which slots are kept vs rescinded
        $rescindSet = array_flip($rescindArr); // for O(1) lookup
        $kept = array_filter($allSlots, fn($s) => !isset($rescindSet[$s]));
        $kept = array_values($kept);

        // Compute contiguous ranges from kept slots
        // A contiguous run starts a new block when there's a gap > slotMins between slots
        $newBlocks = [];
        if (!empty($kept)) {
            $runStart = $kept[0];
            $prev     = strtotime($kept[0]);

            for ($i = 1; $i < count($kept); $i++) {
                $curr = strtotime($kept[$i]);
                if ($curr - $prev > $stepSecs) {
                    // Gap detected — close this run and start a new one
                    $newBlocks[] = [
                        'start' => $runStart,
                        'end'   => date('H:i:s', $prev + $stepSecs),
                    ];
                    $runStart = $kept[$i];
                }
                $prev = $curr;
            }
            // Close the final run
            $newBlocks[] = [
                'start' => $runStart,
                'end'   => date('H:i:s', $prev + $stepSecs),
            ];
        }

        // Delete the original block
        $del = $pdo->prepare('DELETE FROM availability_blocks WHERE block_ID = :bid');
        $del->execute([':bid' => $blockID]);

        // Insert replacement blocks
        $ins = $pdo->prepare(
            'INSERT INTO availability_blocks
                (TutorID, date, start_time, end_time, slot_minutes, ClassID)
             VALUES (:uid, :date, :start, :end, :mins, :classID)'
        );
        foreach ($newBlocks as $nb) {
            $ins->execute([
                ':uid'     => $userID,
                ':date'    => $blockDate,
                ':start'   => $nb['start'],
                ':end'     => $nb['end'],
                ':mins'    => $slotMins,
                ':classID' => $classID,
            ]);
        }

        $removed = count($rescindArr);
        $rebuilt = count($newBlocks);
        $_SESSION['offer_flash'] =
            "$removed slot" . ($removed !== 1 ? 's' : '') . " rescinded. " .
            ($rebuilt > 0
                ? "Remaining availability saved as $rebuilt block" . ($rebuilt !== 1 ? 's' : '') . "."
                : "No remaining slots — block removed entirely.");
        header('Location: offerSession.php');
        exit;

    // ── Rescind a block (cancels any booked sessions within it) ─
    case 'rescind_session':
        $blockID = (int)($_POST['block_id'] ?? 0);
        if ($blockID === 0) {
            $_SESSION['offer_flash'] = 'error: Invalid block.';
            header('Location: offerSession.php');
            exit;
        }

        // Confirm block belongs to this tutor and fetch its details
        $chk = $pdo->prepare(
            'SELECT block_ID, date, start_time, end_time
             FROM availability_blocks
             WHERE block_ID = :bid AND TutorID = :uid'
        );
        $chk->execute([':bid' => $blockID, ':uid' => $userID]);
        $block = $chk->fetch();

        if (!$block) {
            $_SESSION['offer_flash'] = 'error: Block not found.';
            header('Location: offerSession.php');
            exit;
        }

        // Find any booked sessions within this block
        $bookedStmt = $pdo->prepare(
            'SELECT s.session_ID, s.StudentID, s.date, s.time,
                    u.username AS student_name
             FROM session s
             JOIN users u ON u.userID = s.StudentID
             WHERE s.TutorID      = :uid
               AND s.date         = :date
               AND s.time        >= :start
               AND s.time        <  :end
               AND s.is_scheduled = 1'
        );
        $bookedStmt->execute([
            ':uid'   => $userID,
            ':date'  => $block['date'],
            ':start' => $block['start_time'],
            ':end'   => $block['end_time'],
        ]);
        $bookedSessions = $bookedStmt->fetchAll();

        // Cancel each booked session and notify the student
        $revert = $pdo->prepare(
            'UPDATE session SET StudentID = NULL, is_scheduled = 0
             WHERE session_ID = :sid'
        );
        $clearSlot = $pdo->prepare(
            'UPDATE users SET
                TT1_ID = CASE WHEN TT1_ID = :sid THEN NULL ELSE TT1_ID END,
                TT2_ID = CASE WHEN TT2_ID = :sid THEN NULL ELSE TT2_ID END,
                TT3_ID = CASE WHEN TT3_ID = :sid THEN NULL ELSE TT3_ID END
             WHERE userID = :uid'
        );
        $notify = $pdo->prepare(
            'INSERT INTO notifications (userID, message) VALUES (:uid, :msg)'
        );

        $tutorName = $_SESSION['username'];
        foreach ($bookedSessions as $s) {
            $dateStr = date('M j, Y', strtotime($s['date']));
            $timeStr = date('g:i A',  strtotime($s['time']));
            $revert->execute([':sid' => $s['session_ID']]);
            $clearSlot->execute([':sid' => $s['session_ID'], ':uid' => $s['StudentID']]);
            $msg = "$tutorName has cancelled your tutoring session on $dateStr at $timeStr.";
            $notify->execute([':uid' => $s['StudentID'], ':msg' => $msg]);
        }

        // Delete the block
        $del = $pdo->prepare('DELETE FROM availability_blocks WHERE block_ID = :bid');
        $del->execute([':bid' => $blockID]);

        $count = count($bookedSessions);
        $_SESSION['offer_flash'] = 'Availability block rescinded.'
            . ($count > 0 ? " $count booked session" . ($count !== 1 ? 's' : '') . ' cancelled and students notified.' : '');
        header('Location: offerSession.php');
        exit;

    default:
        header('Location: offerSession.php');
        exit;
}
?>
