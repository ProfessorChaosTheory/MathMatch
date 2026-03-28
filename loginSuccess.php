<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Welcome</title>
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
            --accent-green:#5a9e6f;
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
            max-width: 460px;
            background: rgba(22, 34, 24, 0.82);
            border: 1px solid rgba(90,158,111,0.35);
            border-radius: 4px;
            padding: 3rem 2.8rem;
            box-shadow:
                0 0 0 4px rgba(22,34,24,0.5),
                0 0 0 5px rgba(90,158,111,0.12),
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
            color: var(--accent-green);
            margin-bottom: 1rem;
            animation: pop 0.5s 0.3s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes pop {
            from { opacity: 0; transform: scale(0.5); }
            to   { opacity: 1; transform: scale(1); }
        }

        h1 {
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        h1 strong { color: var(--accent-gold); font-weight: 600; }

        p {
            color: var(--chalk-dim);
            font-style: italic;
            font-size: 1.05rem;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: var(--accent-gold);
            color: var(--board-edge);
            border: none;
            border-radius: 2px;
            font-family: 'Crimson Pro', serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s, box-shadow 0.15s;
        }

        .btn:hover {
            opacity: 0.9;
            box-shadow: 0 4px 18px rgba(201,168,76,0.35);
        }

        .meta {
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--chalk-faint);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 300;
        }
    </style>
</head>
<body>

<div class="page">
    <div class="card">
        <div class="icon"><i class="fa-solid fa-circle-check"></i></div>
        <h1>Welcome to <strong>MathMatch</strong></h1>
        <p>You have successfully signed in.</p>
        <a href="index.php" class="btn">
            <i class="fa-solid fa-house"></i>&ensp;Go to Dashboard
        </a>
        <div class="meta">
            Session started &mdash; <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</div>

<?php include 'footer.php' ?>
</body>
</html>
