<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&family=JetBrains+Mono:wght@300;400&display=swap" rel="stylesheet">
    <?php include 'header.php' ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --chalk-white:  #f0ece0;
            --chalk-dim:    #b8b09a;
            --chalk-faint:  #6b6457;
            --board-dark:   #1a2a20;
            --board-mid:    #1f3128;
            --board-edge:   #162218;
            --accent-gold:  #c9a84c;
            --accent-warm:  #d4956a;
            --danger:       #c0554a;
        }

        html, body {
            height: 100%;
            font-family: 'Crimson Pro', Georgia, serif;
            background-color: var(--board-dark);
            color: var(--chalk-white);
            overflow: hidden;
        }

        /* ── Chalkboard background texture ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 28px,
                    rgba(255,255,255,0.018) 28px,
                    rgba(255,255,255,0.018) 29px
                ),
                radial-gradient(ellipse at 20% 80%, rgba(26,42,32,0.9) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(31,49,40,0.8) 0%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── Floating math symbols ── */
        .math-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .math-bg span {
            position: absolute;
            font-family: 'Crimson Pro', serif;
            font-style: italic;
            color: rgba(240, 236, 224, 0.055);
            animation: drift linear infinite;
            user-select: none;
        }

        @keyframes drift {
            from { transform: translateY(110vh) rotate(-8deg); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            to   { transform: translateY(-10vh) rotate(8deg); opacity: 0; }
        }

        /* ── Layout ── */
        .page {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        /* ── Card ── */
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

        /* ── Header ── */
        .card-header {
            text-align: center;
            margin-bottom: 2.2rem;
        }

        .card-header .sigma {
            display: inline-block;
            font-size: 2.8rem;
            color: var(--accent-gold);
            font-style: italic;
            line-height: 1;
            margin-bottom: 0.6rem;
            text-shadow: 0 0 20px rgba(201,168,76,0.35);
            animation: glow 3s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { text-shadow: 0 0 12px rgba(201,168,76,0.25); }
            to   { text-shadow: 0 0 28px rgba(201,168,76,0.55); }
        }

        .card-header h1 {
            font-size: 1.85rem;
            font-weight: 300;
            letter-spacing: 0.06em;
            color: var(--chalk-white);
        }

        .card-header h1 strong {
            font-weight: 600;
            color: var(--accent-gold);
        }

        .card-header p {
            margin-top: 0.35rem;
            font-size: 0.95rem;
            color: var(--chalk-dim);
            font-style: italic;
            letter-spacing: 0.03em;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid rgba(240,236,224,0.1);
            margin: 0 0 2rem;
        }

        /* ── Form fields ── */
        .field {
            margin-bottom: 1.4rem;
            animation: fadeUp 0.7s cubic-bezier(0.22,1,0.36,1) both;
        }

        .field:nth-child(1) { animation-delay: 0.1s; }
        .field:nth-child(2) { animation-delay: 0.2s; }

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

        .input-wrap {
            position: relative;
        }

        .input-wrap i.icon-left {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--chalk-faint);
            font-size: 0.85rem;
            transition: color 0.2s;
            pointer-events: none;
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

        .input-wrap input::placeholder {
            color: var(--chalk-faint);
            font-style: italic;
        }

        .input-wrap input:focus {
            border-color: var(--accent-gold);
            background: rgba(201,168,76,0.06);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
        }

        .input-wrap input:focus + .icon-left,
        .input-wrap:focus-within i.icon-left {
            color: var(--accent-gold);
        }

        /* toggle password visibility */
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

        /* ── Buttons ── */
        .btn-row {
            display: flex;
            gap: 0.85rem;
            margin-top: 2rem;
            animation: fadeUp 0.7s 0.3s cubic-bezier(0.22,1,0.36,1) both;
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

        /* ── Footer note ── */
        .card-foot {
            margin-top: 1.8rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--chalk-faint);
            font-style: italic;
            animation: fadeUp 0.7s 0.4s cubic-bezier(0.22,1,0.36,1) both;
        }

        .card-foot a {
            color: var(--chalk-dim);
            text-decoration: none;
            border-bottom: 1px solid rgba(184,176,154,0.3);
            transition: color 0.2s, border-color 0.2s;
        }

        .card-foot a:hover {
            color: var(--accent-gold);
            border-color: var(--accent-gold);
        }
    </style>
</head>
<body>
<?php if (!empty($_SESSION['reset_success'])): ?>
    <div style="
        max-width: 420px;
        margin: 1rem auto 0;
        padding: 0.75rem 1.2rem;
        background: rgba(90,158,111,0.12);
        border: 1px solid rgba(90,158,111,0.35);
        border-radius: 2px;
        color: #f0ece0;
        font-family: 'Crimson Pro', Georgia, serif;
        font-style: italic;
        font-size: 1rem;
        text-align: center;
    ">
        <i class="fa-solid fa-circle-check" style="color:#5a9e6f; margin-right:0.5rem;"></i>
        Password updated successfully — please sign in.
    </div>
    <?php unset($_SESSION['reset_success']); ?>
<?php endif; ?>
    
<!-- Floating math symbols -->
<div class="math-bg" id="mathBg"></div>

<div class="page">
    <div class="card">
        <div class="card-header">
            <div class="sigma">∑</div>
            <h1>Math<strong>Match</strong></h1>
            <p>Sign in to continue your session</p>
        </div>

        <hr class="divider">

        <form action="loginAction.php" method="POST">

            <div class="field">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user icon-left"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="your username"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock icon-left"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="toggle-pw"
                        id="togglePw"
                        aria-label="Show password"
                        title="Show / hide password"
                    >
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-right-to-bracket"></i>&ensp;Sign In
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='forgot.php'">
                    <i class="fa-solid fa-key"></i>&ensp;Forgot?
                </button>
            </div>

        </form>

        <div class="card-foot">
            New here? <a href="signup.php">Create an account</a>
        </div>
    </div>
</div>

<script>
    // ── Password toggle ──────────────────────────────────────
    const pwInput  = document.getElementById('password');
    const togglePw = document.getElementById('togglePw');
    const eyeIcon  = document.getElementById('eyeIcon');

    togglePw.addEventListener('click', () => {
        const showing = pwInput.type === 'text';
        pwInput.type  = showing ? 'password' : 'text';
        eyeIcon.className = showing ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
        togglePw.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    });

    // ── Floating math symbols ────────────────────────────────
    const symbols = [
        // ── Heavy analysis & complex functions ──
        'ζ(s) = \u2211 n\u207Bs',              // Riemann zeta series
        '\u0393(z) = \u222B\u2080^\u221E t^(z\u22121)e\u207Bt dt', // Gamma integral
        'erf(x) = (2/\u221A\u03C0)\u222B\u2080\u02E3 e\u207Bt\u00B2 dt', // Error function
        'J\u03BD = \u2211(\u22121)\u1D50/m!\u0393(m+\u03BD+1)',    // Bessel series
        'Li(x) = \u222B\u2082\u02E3 dt/ln t',  // Logarithmic integral
        'f(z) = \u222E f(w)/(w\u2212z) dw',    // Cauchy integral formula
        'W(x)e^W(x) = x',                       // Lambert W implicit form
        '\u2202\u00B2u/\u2202t\u00B2 = c\u00B2\u2207\u00B2u', // Wave equation
        '\u2207\u00B2\u03C6 = \u03C1/\u03B5\u2080',            // Poisson equation
        'i\u210F \u2202\u03C8/\u2202t = \u0124\u03C8',         // Schrödinger
        'Ric \u2212 \u00BDRg = 8\u03C0GT',     // Einstein field equations
        // ── Topology & geometry ──
        '\u03C7 = V \u2212 E + F',              // Euler characteristic
        'K = \u03BA\u2081\u00B7\u03BA\u2082',   // Gaussian curvature
        '\u222E \u03BA ds = 2\u03C0\u03C7',     // Gauss-Bonnet theorem
        '\u03C0\u2081(S\u00B9) \u2245 \u2124',  // Fundamental group of circle
        'H\u207F(X;\u2124)',                     // Singular homology
        'T\u00B2 = S\u00B9 \u00D7 S\u00B9',    // Torus as product space
        'RP\u00B2 = S\u00B2/~',                 // Real projective plane
        'M\u00F6bius: (x,y)\u2192(x,1\u2212y)', // Möbius identification
        'd\u03C9 = \u222E\u2202M \u03C9',       // Stokes theorem (general)
        // ── Number theory & algebra ──
        'a\u1D56 \u2261 a (mod p)',              // Fermat's little theorem
        'e^(i\u03C0) + 1 = 0',                  // Euler's identity
        '\u03D5 = (1+\u221A5)/2',               // Golden ratio
        'p(n) ~ e^\u03C0\u221A(2n/3) / 4n\u221A3', // Hardy-Ramanujan
        '\u2211 1/p diverges',                   // Euler prime divergence
        // ── Transforms & probability ──
        'F\u0302(\u03BE) = \u222Bf(x)e^(\u22122\u03C0ix\u03BE)dx', // Fourier transform
        'E[X] = \u222Bx f(x)dx',                // Expectation
        'f*g = \u222Bf(\u03C4)g(t\u2212\u03C4)d\u03C4', // Convolution
        '\u039B(n) = ln p if n=p\u1D4F',        // von Mangoldt function
        'M(x) = \u2211\u207F\u2264\u02E3 \u03BC(n)', // Mertens function
    ];

    const bg = document.getElementById('mathBg');

    for (let i = 0; i < 30; i++) {
        const el   = document.createElement('span');
        el.textContent = symbols[i % symbols.length];
        el.style.cssText = `
            left:     ${Math.random() * 100}%;
            font-size:${1 + Math.random() * 2.4}rem;
            animation-duration:  ${18 + Math.random() * 28}s;
            animation-delay:    -${Math.random() * 40}s;
        `;
        bg.appendChild(el);
    }
</script>

<?php include 'footer.php' ?>
</body>
</html>
