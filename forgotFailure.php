<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Verification Failed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&family=JetBrains+Mono:wght@300;400&display=swap" rel="stylesheet">
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
    <style>

        .card {
            width: 100%;
            max-width: 460px;
            background: rgba(22, 34, 24, 0.82);
            border: 1px solid rgba(192,85,74,0.35);
            border-radius: 4px;
            padding: 3rem 2.8rem;
            box-shadow:
                0 0 0 4px rgba(22,34,24,0.5),
                0 0 0 5px rgba(192,85,74,0.12),
                0 24px 60px rgba(0,0,0,0.55);
            text-align: center;
            animation: fadeUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .icon {
            font-size: 3rem;
            color: var(--accent-red);
            margin-bottom: 1rem;
            animation: shake 0.5s 0.3s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes shake {
            0%   { transform: translateX(0); }
            20%  { transform: translateX(-8px); }
            40%  { transform: translateX(8px); }
            60%  { transform: translateX(-5px); }
            80%  { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }

        h1 {
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        h1 strong { color: var(--accent-red); font-weight: 600; }

        p {
            color: var(--chalk-dim);
            font-style: italic;
            font-size: 1.05rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-row {
            display: flex;
            gap: 0.85rem;
            justify-content: center;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.6rem;
            border-radius: 2px;
            font-family: 'Crimson Pro', serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s, box-shadow 0.15s, border-color 0.15s;
        }

        .btn-primary {
            background: var(--accent-gold);
            color: var(--board-edge);
            border: none;
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
        <div class="icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <h1><strong>Verification Failed</strong></h1>
        <p>
            We couldn't verify your identity.<br>
            The username or security answer did not match our records.
        </p>
        <div class="btn-row">
            <a href="forgot.php" class="btn btn-primary">
                <i class="fa-solid fa-arrow-rotate-left"></i>&ensp;Try Again
            </a>
            <a href="login.php" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>&ensp;Back to Login
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php' ?>
</body>
</html>
