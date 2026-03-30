<?php
session_start();

// Must have come through the full forgot flow
if (empty($_SESSION['reset_userID'])) {
    header('Location: forgot.php');
    exit;
}

$username = htmlspecialchars($_SESSION['reset_username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&family=JetBrains+Mono:wght@300;400&display=swap" rel="stylesheet">
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
    <style>

        .card {
            width: 100%;
            max-width: 460px;
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

        hr {
            border: none;
            border-top: 1px solid rgba(240,236,224,0.1);
            margin: 0 0 1.8rem;
        }

        /* Info banner explaining hashing */
        .info-box {
            display: flex;
            gap: 0.9rem;
            align-items: flex-start;
            background: rgba(212,149,106,0.1);
            border: 1px solid rgba(212,149,106,0.3);
            border-radius: 2px;
            padding: 1rem 1.2rem;
            margin-bottom: 2rem;
            font-size: 0.97rem;
            line-height: 1.6;
            color: var(--chalk-dim);
        }

        .info-box i {
            color: var(--accent-warm);
            font-size: 1.1rem;
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .info-box strong {
            color: var(--chalk-white);
            font-weight: 600;
        }

        /* Form fields */
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

        .input-wrap i.icon-left {
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
            padding: 0.75rem 2.8rem 0.75rem 2.4rem;
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

        .input-wrap:focus-within i.icon-left { color: var(--accent-gold); }

        .toggle-pw {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--chalk-faint);
            cursor: pointer;
            font-size: 0.85rem;
            padding: 0.2rem;
            transition: color 0.2s;
            line-height: 1;
        }

        .toggle-pw:hover { color: var(--chalk-white); }

        /* Password strength meter */
        .strength-wrap {
            margin-top: 0.5rem;
            height: 3px;
            background: rgba(240,236,224,0.08);
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width 0.3s, background-color 0.3s;
        }

        .strength-label {
            margin-top: 0.3rem;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            color: var(--chalk-faint);
            min-height: 1em;
        }

        /* Mismatch hint */
        .hint {
            margin-top: 0.35rem;
            font-size: 0.8rem;
            font-family: 'JetBrains Mono', monospace;
            color: #c0554a;
            min-height: 1em;
            display: none;
        }

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
            <div class="icon"><i class="fa-solid fa-unlock-keyhole"></i></div>
            <h1>Reset <strong>Password</strong></h1>
        </div>

        <hr>

        <div class="info-box">
            <i class="fa-solid fa-circle-info"></i>
            <div>
                <strong>Your original password cannot be displayed.</strong><br>
                For your security, passwords are encrypted using a one-way hash
                the moment you create them — meaning nobody, including this system,
                can read what they were. Use the form below to set a new one.
            </div>
        </div>

        <form action="resetAction.php" method="POST">

            <div class="field">
                <label for="new_password">New Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock icon-left"></i>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        placeholder="choose a new password"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="toggle-pw" id="toggleNew" aria-label="Show password">
                        <i class="fa-solid fa-eye" id="eyeNew"></i>
                    </button>
                </div>
                <div class="strength-wrap">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="strength-label" id="strengthLabel"></div>
            </div>

            <div class="field">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock icon-left"></i>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="repeat your new password"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="toggle-pw" id="toggleConfirm" aria-label="Show password">
                        <i class="fa-solid fa-eye" id="eyeConfirm"></i>
                    </button>
                </div>
                <div class="hint" id="matchHint">Passwords do not match.</div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fa-solid fa-floppy-disk"></i>&ensp;Save Password
                </button>
                <a href="login.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>&ensp;Cancel
                </a>
            </div>

        </form>
    </div>
</div>

<script>
    // ── Toggle visibility helpers ────────────────────────────
    function makeToggle(btnId, iconId, inputId) {
        document.getElementById(btnId).addEventListener('click', () => {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            const show  = input.type === 'text';
            input.type  = show ? 'password' : 'text';
            icon.className = show ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
        });
    }
    makeToggle('toggleNew',     'eyeNew',     'new_password');
    makeToggle('toggleConfirm', 'eyeConfirm', 'confirm_password');

    // ── Password strength meter ──────────────────────────────
    const newPw       = document.getElementById('new_password');
    const confirmPw   = document.getElementById('confirm_password');
    const bar         = document.getElementById('strengthBar');
    const label       = document.getElementById('strengthLabel');
    const matchHint   = document.getElementById('matchHint');
    const submitBtn   = document.getElementById('submitBtn');

    function scorePassword(pw) {
        let score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score;
    }

    const levels = [
        { label: '',          color: 'transparent', pct: '0%'   },
        { label: 'Very weak', color: '#c0554a',     pct: '20%'  },
        { label: 'Weak',      color: '#c0554a',     pct: '40%'  },
        { label: 'Fair',      color: '#c9a84c',     pct: '60%'  },
        { label: 'Strong',    color: '#5a9e6f',      pct: '80%'  },
        { label: 'Very strong', color: '#5a9e6f',   pct: '100%' },
    ];

    newPw.addEventListener('input', () => {
        const score = scorePassword(newPw.value);
        const lvl   = levels[score] || levels[0];
        bar.style.width           = lvl.pct;
        bar.style.backgroundColor = lvl.color;
        label.textContent         = newPw.value.length ? lvl.label : '';
        checkMatch();
    });

    confirmPw.addEventListener('input', checkMatch);

    function checkMatch() {
        const mismatch = confirmPw.value.length > 0 &&
                         confirmPw.value !== newPw.value;
        matchHint.style.display = mismatch ? 'block' : 'none';
        submitBtn.disabled      = mismatch;
    }
</script>

<?php include 'footer.php' ?>
</body>
</html>
