<?php
// ============================================================
//  scheduleSession.php
//  Students (3) and tutors (2) browse available slots derived
//  from availability_blocks and book individual slots.
// ============================================================
session_start();

if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$userID   = (int)$_SESSION['userID'];
$usertype = (int)$_SESSION['usertype'];

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
    die('Database connection failed.');
}

// ── Load all upcoming availability blocks (excluding own) ────
$blockStmt = $pdo->prepare(
    'SELECT b.block_ID, b.date, b.start_time, b.end_time,
            b.slot_minutes, b.ClassID,
            c.class_name,
            u.username AS tutor_name,
            u.userID   AS tutor_id
     FROM availability_blocks b
     JOIN users u ON u.userID = b.TutorID
     LEFT JOIN classes c ON c.classID = b.ClassID
     WHERE b.TutorID != :uid
       AND b.date >= :today
     ORDER BY b.date ASC, b.start_time ASC'
);
$blockStmt->execute([':uid' => $userID, ':today' => date('Y-m-d')]);
$blocks = $blockStmt->fetchAll();

// ── For each block, compute available (unbooked) slots ───────
// A slot is taken if a session row exists for that tutor/date/time
$takenStmt = $pdo->prepare(
    'SELECT time FROM session
     WHERE TutorID = :tid
       AND date    = :date
       AND time   >= :start
       AND time   <  :end
       AND is_scheduled = 1'
);

$availableSlots = [];
foreach ($blocks as $b) {
    $takenStmt->execute([
        ':tid'   => $b['tutor_id'],
        ':date'  => $b['date'],
        ':start' => $b['start_time'],
        ':end'   => $b['end_time'],
    ]);
    $takenRows  = $takenStmt->fetchAll();
    $takenTimes = array_column($takenRows, 'time');

    $slotStart = strtotime($b['start_time']);
    $slotEnd   = strtotime($b['end_time']);
    $stepSecs  = (int)$b['slot_minutes'] * 60;

    for ($t = $slotStart; $t + $stepSecs <= $slotEnd; $t += $stepSecs) {
        $slotTime = date('H:i:s', $t);
        // Skip already booked times
        if (in_array($slotTime, $takenTimes, true)) continue;

        $availableSlots[] = [
            'block_ID'    => $b['block_ID'],
            'date'        => $b['date'],
            'time'        => $slotTime,
            'slot_minutes'=> $b['slot_minutes'],
            'class_name'  => $b['class_name'],
            'tutor_name'  => $b['tutor_name'],
            'tutor_id'    => $b['tutor_id'],
        ];
    }
}

// ── Flash message ────────────────────────────────────────────
$flash = $_SESSION['schedule_flash'] ?? '';
unset($_SESSION['schedule_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Schedule Tutoring</title>
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
    <style>
        .schedule-wrap { width: 100%; padding: 1.5rem 2rem 3rem; }
        .page-title { color: #f0ece0; margin-bottom: 1.5rem; }
        .page-title h2 { font-size: 2rem; font-weight: 700; }
        .page-title p  { font-size: 1rem; opacity: 0.75; margin: 0; }
        .card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 3px 14px rgba(0,0,0,0.4);
            color: #212529;
        }
        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            color: #212529;
            font-weight: 700;
        }
        .text-muted { color: #6c757d !important; }
        hr.chalk { border-color: rgba(240,236,224,0.25); }
    </style>
</head>
<body>
<div class="page">
<div class="schedule-wrap">

    <div class="page-title">
        <h2>Schedule Tutoring</h2>
        <p>Book an available slot from a tutor's offered times.</p>
    </div>
    <hr class="chalk">

    <?php if ($flash): ?>
        <div class="alert <?php echo strpos($flash,'error') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary">&larr; Back to Dashboard</a>
    </div>

    <?php if (empty($availableSlots)): ?>
        <div class="card">
            <div class="card-body text-muted fst-italic">
                No tutoring slots are available right now. Check back later.
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">Available Slots</div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Tutor</th>
                        <th>Class</th>
                        <?php if ($usertype === 2 || $usertype === 3): ?>
                        <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($availableSlots as $slot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($slot['date']); ?></td>
                    <td><?php echo date('g:i A', strtotime($slot['time'])); ?></td>
                    <td><?php echo (int)$slot['slot_minutes']; ?> min</td>
                    <td><?php echo htmlspecialchars($slot['tutor_name']); ?></td>
                    <td><?php echo $slot['class_name']
                        ? htmlspecialchars($slot['class_name'])
                        : '<em class="text-muted">Unspecified</em>'; ?></td>
                    <?php if ($usertype === 2 || $usertype === 3): ?>
                    <td>
                        <form action="scheduleSessionAction.php" method="POST"
                              onsubmit="return confirm('Book this slot?')"
                              class="d-inline">
                            <input type="hidden" name="action"      value="book_session">
                            <input type="hidden" name="block_id"    value="<?php echo (int)$slot['block_ID']; ?>">
                            <input type="hidden" name="tutor_id"    value="<?php echo (int)$slot['tutor_id']; ?>">
                            <input type="hidden" name="date"        value="<?php echo htmlspecialchars($slot['date']); ?>">
                            <input type="hidden" name="time"        value="<?php echo htmlspecialchars($slot['time']); ?>">
                            <input type="hidden" name="slot_minutes" value="<?php echo (int)$slot['slot_minutes']; ?>">
                            <input type="hidden" name="class_id"    value="<?php echo $slot['class_name'] ? '' : ''; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Book</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>
</div>

<?php include 'footer.php' ?>
</body>
</html>
