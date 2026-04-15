<?php

//  dashboard.php
//  Personalized dashboard for all user types.

//  Layout (top to bottom):
//    1. Welcome header
//    2. Scheduling panel  — placeholder for next sprint
//    3. Upcoming sessions — placeholder (3 slots for TT1/TT2/TT3)
//    4. Ask a Question    — inline form
//    5. My Questions      — questions this user posted + answers

session_start();

if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$userID   = (int)$_SESSION['userID'];
$usertype = (int)$_SESSION['usertype'];
$username = htmlspecialchars($_SESSION['username']);

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

// ── Load this user's questions ───────────────────────────────
$qStmt = $pdo->prepare(
    'SELECT questions_asked_ID, question
     FROM questions_asked
     WHERE userID = :uid
     ORDER BY questions_asked_ID DESC'
);
$qStmt->execute([':uid' => $userID]);
$myQuestions = $qStmt->fetchAll();

// ── Load answers for each question via qa_ag ─────────────────
$aStmt = $pdo->prepare(
    'SELECT a.answers_given_ID, a.answer,
            u.username AS tutor
     FROM qa_ag x
     JOIN answers_given a ON a.answers_given_ID = x.answers_given_ID
     JOIN users         u ON u.userID = a.userID
     WHERE x.questions_asked_ID = :qid
     ORDER BY a.answers_given_ID ASC'
);

$myBoard = [];
foreach ($myQuestions as $q) {
    $aStmt->execute([':qid' => $q['questions_asked_ID']]);
    $q['answers'] = $aStmt->fetchAll();
    $myBoard[] = $q;
}

// ── Flash message ────────────────────────────────────────────
$flash = $_SESSION['dash_flash'] ?? '';
unset($_SESSION['dash_flash']);

$roleLabels = [1 => 'Admin', 2 => 'Tutor', 3 => 'Student'];
$roleLabel  = $roleLabels[$usertype] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Dashboard</title>
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
    <style>
        /* ── Full-width content area ── */
        .dash-wrap {
            width: 100%;
            padding: 1.5rem 2rem 3rem;
        }

        /* ── Page title on the chalkboard ── */
        .dash-title { color: #f0ece0; margin-bottom: 1.5rem; }
        .dash-title h2 { font-size: 2rem; font-weight: 700; }
        .dash-title p  { font-size: 1rem; opacity: 0.75; margin: 0; }

        /* ── White cards with dark text ── */
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

        /* ── Muted text readable on white ── */
        .text-muted { color: #6c757d !important; }

        /* ── Session placeholder slots ── */
        .session-slot {
            border: 1px dashed #adb5bd;
            border-radius: 4px;
            padding: 1rem;
            text-align: center;
            color: #6c757d;
        }

        /* ── Textareas ── */
        .card textarea.form-control {
            background: #fff;
            color: #212529;
            border: 1px solid #ced4da;
        }
        .card textarea.form-control:focus {
            border-color: #5a9e6f;
            box-shadow: 0 0 0 3px rgba(90,158,111,0.2);
        }

        /* ── Answer indent stripe ── */
        .border-start.border-success { border-color: #5a9e6f !important; }

        /* ── Section label and chalk divider above My Questions ── */
        .section-label {
            color: #f0ece0;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        hr.chalk { border-color: rgba(240,236,224,0.25); margin-bottom: 1.2rem; }
    </style>
</head>
<body>
<div class="page">
<div class="dash-wrap">

    <!-- ── Welcome ─────────────────────────────────────────── -->
    <div class="dash-title">
        <h2>Welcome, <?php echo $username; ?></h2>
        <p><?php echo $roleLabel; ?></p>
    </div>
    <hr class="chalk">

    <?php if ($flash): ?>
        <div class="alert <?php echo strpos($flash,'error') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>

    <!-- ── Scheduling placeholder ──────────────────────────── -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>Scheduling</span>
            <span class="text-muted small">Coming next sprint</span>
        </div>
        <div class="card-body text-muted fst-italic">
            Scheduling interface will be built here.
        </div>
    </div>

    <!-- ── Upcoming sessions ────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span>Upcoming Sessions</span>
            <span class="text-muted small">Coming next sprint</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="col-md-4">
                    <div class="session-slot">
                        <div class="small mb-1">Session <?php echo $i; ?></div>
                        <em>Not scheduled</em>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- ── Ask a question ───────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-header">Ask a Question</div>
        <div class="card-body">
            <form action="dashboardAction.php" method="POST">
                <input type="hidden" name="action" value="post_question">
                <div class="mb-3">
                    <textarea class="form-control" name="question" rows="3"
                              placeholder="Type your question here…" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Question</button>
                <a href="qaBoard.php" class="btn btn-outline-secondary ms-2">View Full Q&amp;A Board</a>
            </form>
        </div>
    </div>

    <!-- ── My questions & answers ───────────────────────────── -->
    <div class="section-label">My Questions &amp; Answers</div>
    <hr class="chalk">

    <?php if (empty($myBoard)): ?>
        <p class="text-muted fst-italic">You haven't asked any questions yet.</p>
    <?php else: ?>
        <?php foreach ($myBoard as $q):
            $answered = !empty($q['answers']);
        ?>
        <div class="card mb-3">
            <div class="card-header">
                <div class="mb-1">
                    <strong>Q:</strong> <?php echo nl2br(htmlspecialchars($q['question'])); ?>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">your question</small>
                    <?php if ($answered): ?>
                        <span class="badge bg-success">Answered</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Awaiting answer</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($answered): ?>
                    <?php foreach ($q['answers'] as $a): ?>
                    <div class="border-start border-success ps-3 mb-2">
                        <p class="small text-muted mb-1">
                            <strong><?php echo htmlspecialchars($a['tutor']); ?></strong> (tutor)
                        </p>
                        <p class="mb-0">
                            <?php echo nl2br(htmlspecialchars($a['answer'])); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted fst-italic small mb-0">No answer yet — check back later.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</div>

<?php include 'footer.php' ?>
</body>
</html>
