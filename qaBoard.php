<?php


//  qaBoard.php
  
//  - All logged-in users: post questions
//  - Tutors (2) + Admins (1): post answers
//  - Admins (1): edit and delete questions and answers

session_start();

// Public page — guests can view but not post
$loggedIn = !empty($_SESSION['loggedin']);
$userID   = $loggedIn ? (int)$_SESSION['userID']   : 0;
$usertype = $loggedIn ? (int)$_SESSION['usertype']  : 0;
$username = $loggedIn ? htmlspecialchars($_SESSION['username']) : '';

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

// ── Load all questions with asker username ───────────────────
$questions = $pdo->query(
    'SELECT q.questions_asked_ID, q.question,
            u.username AS asker, u.userID AS askerID
     FROM questions_asked q
     JOIN users u ON u.userID = q.userID
     ORDER BY q.questions_asked_ID DESC'
)->fetchAll();

// ── For each question load its answers via qa_ag ─────────────
$answerStmt = $pdo->prepare(
    'SELECT a.answers_given_ID, a.answer,
            u.username AS tutor, u.userID AS tutorID
     FROM qa_ag x
     JOIN answers_given a ON a.answers_given_ID = x.answers_given_ID
     JOIN users         u ON u.userID = a.userID
     WHERE x.questions_asked_ID = :qid
     ORDER BY a.answers_given_ID ASC'
);

$board = [];
foreach ($questions as $q) {
    $answerStmt->execute([':qid' => $q['questions_asked_ID']]);
    $q['answers'] = $answerStmt->fetchAll();
    $board[] = $q;
}

// ── Flash message ────────────────────────────────────────────
$flash = $_SESSION['board_flash'] ?? '';
unset($_SESSION['board_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Q&amp;A Board</title>
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
    <style>
        /* ── Full-width content area, flush with window ── */
        .board-wrap {
            width: 100%;
            padding: 1.5rem 2rem 3rem;
        }

        /* ── Page title on the chalkboard ── */
        .board-title { color: #f0ece0; margin-bottom: 1.5rem; }
        .board-title h2 { font-size: 2rem; font-weight: 700; }
        .board-title p  { font-size: 1rem; opacity: 0.75; margin: 0; }

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

        /* ── Chalk-colored divider ── */
        hr.chalk { border-color: rgba(240,236,224,0.25); }
    </style>
</head>
<body>
<div class="page">
<div class="board-wrap">

    <div class="board-title">
        <h2>Q&amp;A Board</h2>
        <?php if ($loggedIn): ?>
            <p>Logged in as: <strong><?php echo $username; ?></strong></p>
        <?php else: ?>
            <p><a href="login.php" style="color:var(--accent-gold);">Sign in</a> to ask questions or post answers.</p>
        <?php endif; ?>
    </div>
    <hr style="border-color: rgba(240,236,224,0.2); margin-bottom:1.5rem;">

    <?php if ($flash): ?>
        <div class="alert <?php echo strpos($flash,'error') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>

    <!-- ── Ask a question ───────────────────────────────────── -->
    <?php if ($loggedIn): ?>
    <div class="card mb-4">
        <div class="card-header">Ask a Question</div>
        <div class="card-body">
            <form action="qaBoardAction.php" method="POST">
                <input type="hidden" name="action" value="post_question">
                <div class="mb-3">
                    <textarea class="form-control" name="question" rows="3"
                              placeholder="Type your question here…" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Question</button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="card mb-4">
        <div class="card-body text-muted fst-italic">
            <a href="login.php">Sign in</a> to ask a question.
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Question threads ──────────────────────────────────── -->
    <?php if (empty($board)): ?>
        <p class="text-muted fst-italic">No questions yet — be the first to ask one.</p>
    <?php endif; ?>

    <?php foreach ($board as $q):
        $qid      = (int)$q['questions_asked_ID'];
        $answered = !empty($q['answers']);
    ?>
    <div class="card mb-3" id="thread-<?php echo $qid; ?>">

            <div class="card-header">
            <div class="mb-1">
                <strong>Q:</strong> <?php echo nl2br(htmlspecialchars($q['question'])); ?>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">
                    asked by <em><?php echo htmlspecialchars($q['asker']); ?></em>
                    <?php if (!$answered): ?>
                        <span class="badge bg-warning text-dark ms-2">Unanswered</span>
                    <?php else: ?>
                        <span class="badge bg-success ms-2">Answered</span>
                    <?php endif; ?>
                </small>
                <?php if ($usertype === 1): ?>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="toggleEdit('q','<?php echo $qid; ?>')">Edit</button>
                    <form action="qaBoardAction.php" method="POST"
                          onsubmit="return confirm('Delete this question and all its answers?')"
                          class="d-inline">
                        <input type="hidden" name="action"      value="delete_question">
                        <input type="hidden" name="question_id" value="<?php echo $qid; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <!-- Question text hidden when edit form open -->
            <div id="qtext-<?php echo $qid; ?>" style="display:none;"></div>

            <?php if ($usertype === 1): ?>
            <div id="qedit-<?php echo $qid; ?>" style="display:none;" class="mb-3">
                <form action="qaBoardAction.php" method="POST">
                    <input type="hidden" name="action"      value="edit_question">
                    <input type="hidden" name="question_id" value="<?php echo $qid; ?>">
                    <textarea class="form-control mb-2" name="question"
                              rows="3" required><?php echo htmlspecialchars($q['question']); ?></textarea>
                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    <button type="button" class="btn btn-sm btn-secondary"
                            onclick="toggleEdit('q','<?php echo $qid; ?>')">Cancel</button>
                </form>
            </div>
            <?php endif; ?>

            <?php if (empty($q['answers'])): ?>
                <p class="text-muted fst-italic small">Awaiting a tutor's answer…</p>
            <?php else: ?>
                <?php foreach ($q['answers'] as $a):
                    $aid = (int)$a['answers_given_ID'];
                ?>
                <div class="border-start border-success ps-3 mb-2" id="answer-<?php echo $aid; ?>">
                    <p class="small text-muted mb-1">
                        <strong><?php echo htmlspecialchars($a['tutor']); ?></strong> (tutor)
                    </p>
                    <p id="atext-<?php echo $aid; ?>" class="mb-1">
                        <?php echo nl2br(htmlspecialchars($a['answer'])); ?>
                    </p>
                    <?php if ($usertype === 1): ?>
                    <div id="aedit-<?php echo $aid; ?>" style="display:none;" class="mb-2">
                        <form action="qaBoardAction.php" method="POST">
                            <input type="hidden" name="action"    value="edit_answer">
                            <input type="hidden" name="answer_id" value="<?php echo $aid; ?>">
                            <textarea class="form-control mb-2" name="answer"
                                      rows="2" required><?php echo htmlspecialchars($a['answer']); ?></textarea>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            <button type="button" class="btn btn-sm btn-secondary"
                                    onclick="toggleEdit('a','<?php echo $aid; ?>')">Cancel</button>
                        </form>
                    </div>
                    <div class="d-flex gap-2 mb-1">
                        <button class="btn btn-sm btn-outline-secondary"
                                onclick="toggleEdit('a','<?php echo $aid; ?>')">Edit</button>
                        <form action="qaBoardAction.php" method="POST"
                              onsubmit="return confirm('Delete this answer?')"
                              class="d-inline">
                            <input type="hidden" name="action"    value="delete_answer">
                            <input type="hidden" name="answer_id" value="<?php echo $aid; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($usertype === 1 || $usertype === 2): ?>
            <hr>
            <form action="qaBoardAction.php" method="POST">
                <input type="hidden" name="action"      value="post_answer">
                <input type="hidden" name="question_id" value="<?php echo $qid; ?>">
                <div class="mb-2">
                    <textarea class="form-control" name="answer" rows="2"
                              placeholder="Write your answer here…" required></textarea>
                </div>
                <button type="submit" class="btn btn-sm btn-success">Post Answer</button>
            </form>
            <?php endif; ?>

        </div>
    </div>
    <?php endforeach; ?>

</div>
</div>

<script>
function toggleEdit(type, id) {
    const textEl = document.getElementById((type === 'q' ? 'qtext-' : 'atext-') + id);
    const editEl = document.getElementById((type === 'q' ? 'qedit-' : 'aedit-') + id);
    const editing = editEl.style.display === 'block';
    editEl.style.display = editing ? 'none'  : 'block';
    textEl.style.display  = editing ? 'block' : 'none';
}
</script>

<?php include 'footer.php' ?>
</body>
</html>
