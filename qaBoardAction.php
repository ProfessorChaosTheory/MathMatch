<?php

//  qaBoardAction.php
//  Handles all POST actions from qaBoard.php:
//    post_question  — any logged-in user
//    post_answer    — tutors (2) and admins (1) only
//    edit_question  — admins (1) only
//    edit_answer    — admins (1) only
//    delete_question — admins (1) only
//    delete_answer  — admins (1) only

session_start();

// ── Authorization guard ──────────────────────────────────
if (empty($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: qaBoard.php');
    exit;
}

$userID   = (int)$_SESSION['userID'];
$usertype = (int)$_SESSION['usertype'];
$action   = $_POST['action'] ?? '';

// ── DB connection ──────────────────────────────────────────
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
    $_SESSION['board_flash'] = 'error: Database connection failed.';
    header('Location: qaBoard.php');
    exit;
}

// ── Helper: flash and redirect ─────────────────────────
function flashRedirect(string $msg, string $url = 'qaBoard.php'): void {
    $_SESSION['board_flash'] = $msg;
    header("Location: $url");
    exit;
}

// ── Dispatch ────────────────────────────────────────────────
switch ($action) {

    // ── Post a question (all logged-in users) ────────────────
    case 'post_question':
        $question = trim($_POST['question'] ?? '');
        if ($question === '') {
            flashRedirect('error: Question cannot be empty.');
        }
        // answered status
        $stmt = $pdo->prepare(
            'INSERT INTO questions_asked (userID, question)
             VALUES (:uid, :q)'
        );
        $stmt->execute([':uid' => $userID, ':q' => $question]);
        flashRedirect('Your question was posted.');
        break;

    // ── Post an answer (tutors and admins only) ───────────────
    case 'post_answer':
        if ($usertype !== 1 && $usertype !== 2) {
            flashRedirect('error: Only tutors can post answers.');
        }
        $qid    = (int)($_POST['question_id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');
        if ($qid === 0 || $answer === '') {
            flashRedirect('error: Answer cannot be empty.');
        }

        // Verify the question exists
        $chk = $pdo->prepare('SELECT questions_asked_ID FROM questions_asked WHERE questions_asked_ID = :qid');
        $chk->execute([':qid' => $qid]);
        if (!$chk->fetch()) {
            flashRedirect('error: Question not found.');
        }

        // Insert answer
        $stmt = $pdo->prepare(
            'INSERT INTO answers_given (userID, answer)
             VALUES (:uid, :a)'
        );
        $stmt->execute([':uid' => $userID, ':a' => $answer]);
        $aid = (int)$pdo->lastInsertId();

        // Link via cross-reference table
        $xref = $pdo->prepare(
            'INSERT INTO qa_ag (questions_asked_ID, answers_given_ID)
             VALUES (:qid, :aid)'
        );
        $xref->execute([':qid' => $qid, ':aid' => $aid]);

        flashRedirect('Your answer was posted.');
        break;

    // ── Edit a question (admins only) ────────────────────────
    case 'edit_question':
        if ($usertype !== 1) {
            flashRedirect('error: Only admins can edit questions.');
        }
        $qid      = (int)($_POST['question_id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        if ($qid === 0 || $question === '') {
            flashRedirect('error: Invalid edit request.');
        }
        $stmt = $pdo->prepare(
            'UPDATE questions_asked SET question = :q WHERE questions_asked_ID = :qid'
        );
        $stmt->execute([':q' => $question, ':qid' => $qid]);
        flashRedirect('Question updated.');
        break;

    // ── Edit an answer (admins only) ─────────────────────────
    case 'edit_answer':
        if ($usertype !== 1) {
            flashRedirect('error: Only admins can edit answers.');
        }
        $aid    = (int)($_POST['answer_id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');
        if ($aid === 0 || $answer === '') {
            flashRedirect('error: Invalid edit request.');
        }
        $stmt = $pdo->prepare(
            'UPDATE answers_given SET answer = :a WHERE answers_given_ID = :aid'
        );
        $stmt->execute([':a' => $answer, ':aid' => $aid]);
        flashRedirect('Answer updated.');
        break;

    // ── Delete a question (admins only) ──────────────────────
    // FK ON DELETE CASCADE handles qa_ag and answers_given rows automatically
    case 'delete_question':
        if ($usertype !== 1) {
            flashRedirect('error: Only admins can delete questions.');
        }
        $qid = (int)($_POST['question_id'] ?? 0);
        if ($qid === 0) {
            flashRedirect('error: Invalid delete request.');
        }
        $stmt = $pdo->prepare('DELETE FROM questions_asked WHERE questions_asked_ID = :qid');
        $stmt->execute([':qid' => $qid]);
        flashRedirect('Question deleted.');
        break;

    // ── Delete an answer (admins only) ───────────────────────
    // FK ON DELETE CASCADE on qa_ag handles the cross-reference row automatically.
    case 'delete_answer':
        if ($usertype !== 1) {
            flashRedirect('error: Only admins can delete answers.');
        }
        $aid = (int)($_POST['answer_id'] ?? 0);
        if ($aid === 0) {
            flashRedirect('error: Invalid delete request.');
        }

        // Find the question this answer belongs to (via qa_ag) for the flash anchor
        $find = $pdo->prepare(
            'SELECT questions_asked_ID FROM qa_ag WHERE answers_given_ID = :aid'
        );
        $find->execute([':aid' => $aid]);
        $find->fetch(); // consumed; CASCADE on FK cleans up qa_ag automatically

        $stmt = $pdo->prepare('DELETE FROM answers_given WHERE answers_given_ID = :aid');
        $stmt->execute([':aid' => $aid]);

        flashRedirect('Answer deleted.');
        break;

    default:
        header('Location: qaBoard.php');
        exit;
}
?>
