<?php
session_start();

// Must have come through the full forgot flow
if (empty($_SESSION['reset_userID'])) {
    header('Location: forgot.php');
    exit;
}

$question = htmlspecialchars($_SESSION['reset_question']);
$username = htmlspecialchars($_SESSION['reset_username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Security Question</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&family=JetBrains+Mono:wght@300;400&display=swap" rel="stylesheet">
    <?php include 'header.php' ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --chalk-white: #f0ece0;
            --chalk-dim:   #b8b09a;
            --chalk-faint: #6b6457;
            --board-dark:  #1a2a20;
            --board-edge:  #162218;
            --accent-gold: #c9a84c;
        }

        html, body {
            height: 100%;
            font-family: 'Crimson Pro', Georgia, serif;
            background-color: var(--board-dark);
            color: var(--chalk-white);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                repeating-linear-gradient(
                    0deg, transparent, transparent 28px,
                    rgba(255,255,255,0.018) 28px,
                    rgba(255,255,255,0.018) 29px
                );
            pointer-events: none;
            z-index: 0;
        }

        .page {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 2rem;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: rgba(22, 34, 24, 0.82);
            border: 1px solid rgba(240,236,224,0.12);
            border-radius: 4px;
            padding: 3rem 2.8rem 2.6rem;
            box-shadow:
                0 0 0 4px rgba(22,34,24,0.5),
                0 0 0 5px rgba(240,236,224,0.06),
                0 24px 60px rgba(0,0,0,0.55);
            animation: fadeUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .card-header .icon {
            font-size: 2.5rem;
            color: var(--accent-gold);
            margin-bottom: 0.6rem;
        }

        .card-header h1 {
            font-size: 1.75rem;
            font-weight: 300;
            letter-spacing: 0.05em;
        }

        .card-header h1 strong {
            font-weight: 600;
            color: var(--accent-gold);
        }

        .card-header p {
            margin-top: 0.4rem;
            font-size: 0.95rem;
            color: var(--chalk-dim);
            font-style: italic;
        }

        hr {
            border: none;
            border-top: 1px solid rgba(240,236,224,0.1);
            margin: 0 0 2rem;
        }

        /* The security question display box */
        .question-box {
            background: rgba(201,168,76,0.08);
            border: 1px solid rgba(201,168,76,0.25);
            border-radius: 2px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.6rem;
            font-size: 1.05rem;
            font-style: italic;
            color: var(--chalk-white);
            line-height: 1.5;
        }

        .question-box span {
            display: block;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-style: normal;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent-gold);
            margin-bottom: 0.4rem;
        }

        .field { margin-bottom: 1.4rem; }

        .field label {
            display: block;
            font-size: 0.8rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--chalk-dim);
            margin-bottom: 0.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 300;
        }

        .input-wrap { position: relative; }

        .input-wrap i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--chalk-faint);
            font-size: 0.85rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-wrap input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.4rem;
            background: rgba(240,236,224,0.05);
            border: 1px solid rgba(240,236,224,0.15);
            border-radius: 2px;
            color: var(--chalk-white);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.95rem;
            font-weight: 300;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        .input-wrap input::placeholder { color: var(--chalk-faint); font-style: italic; }

        .input-wrap input:focus {
            border-color: var(--accent-gold);
            background: rgba(201,168,76,0.06);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
        }

        .input-wrap:focus-within i { color: var(--accent-gold); }

        .btn-row {
            display: flex;
            gap: 0.85rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 0.78rem 1rem;
            border: none;
            border-radius: 2px;
            font-family: 'Crimson Pro', serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
        }

        .btn:active { transform: scale(0.97); }

        .btn-primary {
            background: var(--accent-gold);
            color: var(--board-edge);
        }

        .btn-primary:hover {
            opacity: 0.9;
            box-shadow: 0 4px 18px rgba(201,168,76,0.35);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(240,236,224,0.18);
            color: var(--chalk-dim);
        }

        .btn-secondary:hover {
            border-color: rgba(240,236,224,0.4);
            color: var(--chalk-white);
        }
    </style>
</head>
<body>

<div class="page">
    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="fa-solid fa-shield-halved"></i></div>
            <h1>Security <strong>Question</strong></h1>
            <p>Answering for: <strong><?php echo $username; ?></strong></p>
        </div>

        <hr>

        <div class="question-box">
            <span>Your security question</span>
            <?php echo $question; ?>
        </div>

        <form action="secQuestionAction.php" method="POST">
            <div class="field">
                <label for="security_answer">Your Answer</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input
                        type="text"
                        id="security_answer"
                        name="security_answer"
                        placeholder="your answer"
                        autocomplete="off"
                        required
                    >
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>&ensp;Submit
                </button>
                <a href="forgot.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>&ensp;Back
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php' ?>
</body>
</html>
