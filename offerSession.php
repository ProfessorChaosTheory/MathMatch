<?php
// ============================================================
//  offerSession.php
//  Tutors (usertype 2) and admins (1) only.
//  Allows a tutor to offer availability blocks (start time,
//  end time, slot length) and rescind unbooked blocks.
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

// ── Load classes for dropdown ────────────────────────────────
$classes = $pdo->query(
    'SELECT classID, class_name FROM classes ORDER BY class_name'
)->fetchAll();

// ── Load this tutor's offered blocks ────────────────────────
$blockStmt = $pdo->prepare(
    'SELECT b.block_ID, b.date, b.start_time, b.end_time,
            b.slot_minutes, b.ClassID,
            c.class_name,
            COUNT(s.session_ID) AS booked_count
     FROM availability_blocks b
     LEFT JOIN classes c ON c.classID = b.ClassID
     LEFT JOIN session s ON s.TutorID = b.TutorID
                         AND s.date    = b.date
                         AND s.time   >= b.start_time
                         AND s.time   <  b.end_time
                         AND s.is_scheduled = 1
     WHERE b.TutorID = :uid
       AND b.date >= :today
     GROUP BY b.block_ID
     ORDER BY b.date ASC, b.start_time ASC'
);
$blockStmt->execute([':uid' => $userID, ':today' => date('Y-m-d')]);
$blocks = $blockStmt->fetchAll();

// ── Helper: compute total slots in a block ───────────────────
function totalSlots(string $start, string $end, int $mins): int {
    $s = strtotime($start);
    $e = strtotime($end);
    if ($e <= $s || $mins <= 0) return 0;
    return (int)(($e - $s) / ($mins * 60));
}

// ── For each block, compute which slot times are booked ──────
$bookedTimesStmt = $pdo->prepare(
    'SELECT time FROM session
     WHERE TutorID      = :uid
       AND date         = :date
       AND time        >= :start
       AND time        <  :end
       AND is_scheduled = 1'
);

foreach ($blocks as &$b) {
    $bookedTimesStmt->execute([
        ':uid'   => $userID,
        ':date'  => $b['date'],
        ':start' => $b['start_time'],
        ':end'   => $b['end_time'],
    ]);
    // Store as H:i:s strings for easy comparison
    $b['booked_times'] = array_column($bookedTimesStmt->fetchAll(), 'time');
}
unset($b); // break reference
$flash = $_SESSION['offer_flash'] ?? '';
unset($_SESSION['offer_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Offer Tutoring</title>
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
    <style>
        .offer-wrap { width: 100%; padding: 1.5rem 2rem 3rem; }
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
        hr.chalk { border-color: rgba(240,236,224,0.25); margin-bottom: 1.2rem; }
        .section-label { color: #f0ece0; font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem; }
        #slotPreview { font-size: 0.9rem; color: #6c757d; margin-top: 0.4rem; min-height: 1.2em; }
    </style>
</head>
<body>
<div class="page">
<div class="offer-wrap">

    <div class="page-title">
        <h2>Offer Tutoring</h2>
        <p>Define a block of availability — students will book individual slots within it.</p>
    </div>
    <hr class="chalk">

    <?php if ($flash): ?>
        <div class="alert <?php echo strpos($flash,'error') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>

    <!-- ── Offer a new block ─────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-header">Offer a New Availability Block</div>
        <div class="card-body">
            <form action="offerSessionAction.php" method="POST">
                <input type="hidden" name="action" value="offer_session">
                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" class="form-control" name="date"
                               id="inputDate" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Start Time</label>
                        <input type="time" class="form-control" name="start_time"
                               id="inputStart" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">End Time</label>
                        <input type="time" class="form-control" name="end_time"
                               id="inputEnd" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Slot Length</label>
                        <select class="form-select" name="slot_minutes" id="inputSlot">
                            <option value="30">30 min</option>
                            <option value="45">45 min</option>
                            <option value="60" selected>1 hour</option>
                            <option value="90">1.5 hours</option>
                            <option value="120">2 hours</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Class
                            <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <?php if (empty($classes)): ?>
                            <input type="text" class="form-control" name="class_name"
                                   placeholder="e.g. Calculus II">
                            <small class="text-muted">No classes in system — type one in.</small>
                        <?php else: ?>
                            <select class="form-select" name="classID">
                                <option value="">— Any / unspecified —</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo (int)$c['classID']; ?>">
                                        <?php echo htmlspecialchars($c['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                </div>

                <div id="slotPreview" class="mt-2"></div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Offer This Block</button>
                    <a href="dashboard.php" class="btn btn-outline-secondary ms-2">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Currently offered blocks ─────────────────────────── -->
    <div class="section-label">Your Offered Blocks</div>
    <hr class="chalk">

    <?php if (empty($blocks)): ?>
        <p class="text-muted fst-italic">You have no upcoming availability blocks. Add one above.</p>
    <?php else: ?>
        <?php foreach ($blocks as $b):
            $bid      = (int)$b['block_ID'];
            $total    = totalSlots($b['start_time'], $b['end_time'], (int)$b['slot_minutes']);
            $booked   = (int)$b['booked_count'];
            $allBooked = ($booked >= $total && $total > 0);

            // Build the full slot list for this block
            $slotStart = strtotime($b['start_time']);
            $slotEnd   = strtotime($b['end_time']);
            $stepSecs  = (int)$b['slot_minutes'] * 60;
            $allSlots  = [];
            for ($t = $slotStart; $t + $stepSecs <= $slotEnd; $t += $stepSecs) {
                $allSlots[] = date('H:i:s', $t);
            }
        ?>
        <div class="card mb-3">
            <!-- Block summary row -->
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong><?php echo htmlspecialchars($b['date']); ?></strong>
                    &mdash;
                    <?php echo date('g:i A', strtotime($b['start_time'])); ?>
                    to
                    <?php echo date('g:i A', strtotime($b['end_time'])); ?>
                    &nbsp;
                    <span class="text-muted fw-normal">
                        (<?php echo (int)$b['slot_minutes']; ?> min slots
                        <?php if ($b['class_name']): ?>
                            &middot; <?php echo htmlspecialchars($b['class_name']); ?>
                        <?php endif; ?>)
                    </span>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- Booked count badge -->
                    <span>
                        <?php echo $booked; ?>/<?php echo $total; ?> booked
                        <?php if ($allBooked): ?>
                            <span class="badge bg-success ms-1">Full</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark ms-1">Open</span>
                        <?php endif; ?>
                    </span>

                    <!-- Partial rescind toggle (only if more than 1 slot and not all booked) -->
                    <?php if ($total > 1 && !$allBooked): ?>
                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="togglePartial(<?php echo $bid; ?>)">
                        Rescind Slots
                    </button>
                    <?php endif; ?>

                    <!-- Full rescind -->
                    <form action="offerSessionAction.php" method="POST"
                          onsubmit="return confirm('<?php echo $booked > 0
                            ? "This block has $booked booked slot" . ($booked !== 1 ? 's' : '') . ". Rescinding will cancel those sessions and notify the students. Continue?"
                            : "Rescind this entire availability block?"; ?>')"
                          class="d-inline">
                        <input type="hidden" name="action"   value="rescind_session">
                        <input type="hidden" name="block_id" value="<?php echo $bid; ?>">
                        <button type="submit"
                                class="btn btn-sm <?php echo $booked > 0 ? 'btn-danger' : 'btn-outline-danger'; ?>">
                            Rescind All<?php echo $booked > 0 ? ' &amp; Cancel Bookings' : ''; ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Partial rescind panel (hidden by default) -->
            <?php if ($total > 1 && !$allBooked): ?>
            <div id="partial-<?php echo $bid; ?>" style="display:none;">
                <div class="card-body border-top">
                    <p class="fw-bold mb-2">Select slots to rescind:</p>
                    <form action="offerSessionAction.php" method="POST"
                          onsubmit="return confirmPartial(this)">
                        <input type="hidden" name="action"       value="partial_rescind">
                        <input type="hidden" name="block_id"     value="<?php echo $bid; ?>">
                        <input type="hidden" name="slot_minutes" value="<?php echo (int)$b['slot_minutes']; ?>">
                        <input type="hidden" name="block_date"   value="<?php echo htmlspecialchars($b['date']); ?>">
                        <input type="hidden" name="start_time"   value="<?php echo htmlspecialchars($b['start_time']); ?>">
                        <input type="hidden" name="end_time"     value="<?php echo htmlspecialchars($b['end_time']); ?>">
                        <input type="hidden" name="class_id"     value="<?php echo $b['ClassID'] ?? ''; ?>">

                        <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach ($allSlots as $slotTime):
                            $isBooked = in_array($slotTime, $b['booked_times'], true);
                            $label    = date('g:i A', strtotime($slotTime));
                        ?>
                            <div class="form-check form-check-inline border rounded px-3 py-2
                                        <?php echo $isBooked ? 'bg-light text-muted' : ''; ?>">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="rescind_slots[]"
                                       value="<?php echo htmlspecialchars($slotTime); ?>"
                                       id="slot-<?php echo $bid; ?>-<?php echo str_replace(':', '', $slotTime); ?>"
                                       <?php echo $isBooked ? 'disabled' : ''; ?>>
                                <label class="form-check-label"
                                       for="slot-<?php echo $bid; ?>-<?php echo str_replace(':', '', $slotTime); ?>">
                                    <?php echo $label; ?>
                                    <?php if ($isBooked): ?>
                                        <span class="badge bg-secondary ms-1">Booked</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-sm btn-warning">
                            Rescind Selected Slots
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                onclick="togglePartial(<?php echo $bid; ?>)">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /card -->
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</div>

<script>
const inputStart = document.getElementById('inputStart');
const inputEnd   = document.getElementById('inputEnd');
const inputSlot  = document.getElementById('inputSlot');
const preview    = document.getElementById('slotPreview');

function updatePreview() {
    const start = inputStart.value;
    const end   = inputEnd.value;
    const mins  = parseInt(inputSlot.value);
    if (!start || !end || !mins) { preview.textContent = ''; return; }
    const startMs = new Date('1970-01-01T' + start).getTime();
    const endMs   = new Date('1970-01-01T' + end).getTime();
    if (endMs <= startMs) {
        preview.textContent = 'End time must be after start time.';
        return;
    }
    const slots = Math.floor((endMs - startMs) / (mins * 60000));
    preview.textContent = slots > 0
        ? `This block will offer ${slots} slot${slots !== 1 ? 's' : ''} of ${mins} minutes each.`
        : 'Slot length is longer than the block duration.';
}

inputStart.addEventListener('change', updatePreview);
inputEnd.addEventListener('change',   updatePreview);
inputSlot.addEventListener('change',  updatePreview);

function togglePartial(blockId) {
    const panel = document.getElementById('partial-' + blockId);
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function confirmPartial(form) {
    const checked = form.querySelectorAll('input[name="rescind_slots[]"]:checked');
    if (checked.length === 0) {
        alert('Please select at least one slot to rescind.');
        return false;
    }
    return confirm(`Rescind ${checked.length} slot${checked.length !== 1 ? 's' : ''}?`);
}
</script>

<?php include 'footer.php' ?>
</body>
</html>