<?php
require 'DBConnect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0);
    session_start();
}

function navLink(string $dest, bool $public = false): string {
    if ($public || !empty($_SESSION['loggedin'])) {
        return $dest;
    }
    return 'login.php';
}
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="stylesheet" href="mystyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&family=JetBrains+Mono:wght@300;400&display=swap" rel="stylesheet">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – MathMatch' : 'MathMatch'; ?></title>
<style>
/* ── Palette ─────────────────────────────────────────────── */
:root {
    --chalk-white: #f0ece0;
    --chalk-dim:   #b8b09a;
    --chalk-faint: #6b6457;
    --board-dark:  #243d2a;   /* lightened from #1a2a20 */
    --board-mid:   #2a4733;   /* lightened from #1f3128 */
    --board-edge:  #1c3022;   /* lightened from #162218 */
    --accent-gold: #c9a84c;
    --accent-warm: #d4956a;
    --accent-green:#5a9e6f;
    --accent-red:  #c0554a;
}

/* ── Base reset ──────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html {
    height: 100%;
}

body {
    min-height: 100vh;
    font-family: 'Crimson Pro', Georgia, serif;
    background-color: var(--board-dark);
    color: var(--chalk-white);
    overflow-x: hidden;
    /* flex column so header/footer sit naturally above/below .page */
    display: flex;
    flex-direction: column;
}

/* ── Navbar / sidebar z-index fix ────────────────────────── */
#main      { z-index: 100; }
#mySidebar { z-index: 200; }

/* ── Global page centering ───────────────────────────────── */
.page {
    position: relative;
    z-index: 0;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    padding-top: 4rem;   /* clear the fixed navbar */
}

/* ── Chalkboard texture — sits behind everything incl. nav ── */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
        repeating-linear-gradient(
            0deg,
            transparent,
            transparent 28px,
            rgba(255,255,255,0.028) 28px,
            rgba(255,255,255,0.028) 29px
        ),
        radial-gradient(ellipse at 20% 80%, rgba(28,48,34,0.55) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 20%, rgba(42,71,51,0.45) 0%, transparent 55%);
    pointer-events: none;
    z-index: -1;   /* behind header, footer, and page content */
}

/* ── Floating math-symbol layer — also behind nav ────────── */
.math-bg {
    position: fixed;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    overflow: hidden;
}

.math-bg span {
    position: absolute;
    font-family: 'Crimson Pro', serif;
    font-style: italic;
    color: rgba(240, 236, 224, 0.07);
    animation: drift linear infinite;
    user-select: none;
}

@keyframes drift {
    from { transform: translateY(110vh) rotate(-8deg); opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    to   { transform: translateY(-10vh)  rotate( 8deg); opacity: 0; }
}
</style>
</head>
<body>

<div class="w3-bar w3-top w3-green"  style="position: fixed; top: 0; z-index: 9999"  id="main">
    <button id="openNav" class="w3-bar-item w3-button w3-lime w3-hover-white w3-left w3-xlarge" onclick="w3_open()">MathMatch</button>
    <a href="index.php" class="w3-bar-item w3-button w3-hover-white w3-text-black w3-xlarge">Home</a>
    <a href="about.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white w3-xlarge">About</a>
    <div class="w3-right">
    <a href="signup.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white w3-right w3-xlarge">Sign Up!</a>
    </div>
    <div class="w3-right">
        <?php if (!empty($_SESSION['loggedin'])): ?>
            <a href="logoutaction.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white w3-right w3-xlarge">Logout</a>
        <?php else: ?>
            <a href="login.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white w3-right w3-xlarge">Login</a>
        <?php endif; ?>
    </div>
</div>
<div class="w3-sidebar w3-bar-block w3-card w3-animate-left w3-black w3-top" style="display:none" id="mySidebar">
  <button class="w3-bar-item w3-button w3-large"
  onclick="w3_close()">Close &times;</button>
  <a href="sorry.php" class="w3-bar-item w3-button">Tutoring</a>
  <a href="<?php echo navLink('qaBoard.php', true); ?>" class="w3-bar-item w3-button">Question Board</a>
  <a href="<?php echo navLink('scheduleSession.php', true); ?>" class="w3-bar-item w3-button">Class Tutoring Calendar</a>
  <a href="sorry.php" class="w3-bar-item w3-button">Miscellaneous</a>
  <a href="<?php echo navLink('dashboard.php'); ?>" class="w3-bar-item w3-button">Dashboard</a>
  <?php if (isset($_SESSION['username']) && ($_SESSION['usertype']) == 1): ?>
    <div class="dropdown">
        <button class="drop-btn w3-button">Admin Tools <i class="fa fa-caret-down"></i></button>      
        <div class="dropdown-content">
            <a href="registerClass.php" class="w3-bar-item w3-button">Class Registration</a>
            <a href="userManager.php" class="w3-bar-item w3-button">User Management</a>
        </div>
    </div>
  <?php endif; ?>
</div>
 
<script>
function w3_open() {
  document.getElementById("main").style.marginLeft = "15%";
  document.getElementById("mySidebar").style.width = "15%";
  document.getElementById("mySidebar").style.display = "block";
  document.getElementById("openNav").style.display = 'none';
}
function w3_close() {
  document.getElementById("main").style.marginLeft = "0%";
  document.getElementById("mySidebar").style.display = "none";
  document.getElementById("openNav").style.display = "inline-block";
}
</script>
