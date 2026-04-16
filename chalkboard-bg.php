<?php
/*
 * chalkboard-bg.php
 * ─────────────────────────────────────────────────────────────
 * Drop-in animated chalkboard background for MathMatch pages.
 *
 * Usage — place this ONCE inside the page <head>:
 *   <?php include 'chalkboard-bg.php'; ?>
 *
 * Then add <div id="mathBg" class="math-bg"></div>
 * as the FIRST child of <body>.  The JS at the bottom of this
 * file will populate it automatically.
 *
 * The file emits:
 *   • CSS custom-property palette        (:root)
 *   • Base html/body reset               (font, color, height)
 *   • Chalkboard texture pseudo-element  (body::before)
 *   • Floating-symbol layer              (.math-bg / .math-bg span)
 *   • <div id="mathBg"> injection script (runs after DOM ready)
 * ─────────────────────────────────────────────────────────────
 */
?>
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
    min-height: 100%;
    font-family: 'Crimson Pro', Georgia, serif;
    background-color: var(--board-dark);
    color: var(--chalk-white);
    overflow-x: hidden;
    /* flex column so header/footer sit naturally above/below .page */
    display: flex;
    flex-direction: column;
}

/* ── Global page centering ───────────────────────────────── */
/* .page grows to fill whatever vertical space remains after
   the header and footer, then centres its card within that.  */
.page {
    position: relative;
    z-index: 1;          /* above the background layers (z-index: -1) */
    flex: 1;             /* fills remaining body height between nav & footer */
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
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

<!-- Floating math symbols: rendered by the script below -->
<div id="mathBg" class="math-bg"></div>

<script>
(function () {
    const symbols = [
        /* ── Heavy analysis & complex functions ── */
        'ζ(s) = \u2211 n\u207Bs',
        '\u0393(z) = \u222B\u2080^\u221E t^(z\u22121)e\u207Bt dt',
        'erf(x) = (2/\u221A\u03C0)\u222B\u2080\u02E3 e\u207Bt\u00B2 dt',
        'J\u03BD = \u2211(\u22121)\u1D50/m!\u0393(m+\u03BD+1)',
        'Li(x) = \u222B\u2082\u02E3 dt/ln t',
        'f(z) = \u222E f(w)/(w\u2212z) dw',
        'W(x)e^W(x) = x',
        '\u2202\u00B2u/\u2202t\u00B2 = c\u00B2\u2207\u00B2u',
        '\u2207\u00B2\u03C6 = \u03C1/\u03B5\u2080',
        'i\u210F \u2202\u03C8/\u2202t = \u0124\u03C8',
        'Ric \u2212 \u00BDRg = 8\u03C0GT',
        /* ── Topology & geometry ── */
        '\u03C7 = V \u2212 E + F',
        'K = \u03BA\u2081\u00B7\u03BA\u2082',
        '\u222E \u03BA ds = 2\u03C0\u03C7',
        '\u03C0\u2081(S\u00B9) \u2245 \u2124',
        'H\u207F(X;\u2124)',
        'T\u00B2 = S\u00B9 \u00D7 S\u00B9',
        'RP\u00B2 = S\u00B2/~',
        'M\u00F6bius: (x,y)\u2192(x,1\u2212y)',
        'd\u03C9 = \u222E\u2202M \u03C9',
        /* ── Number theory & algebra ── */
        'a\u1D56 \u2261 a (mod p)',
        'e^(i\u03C0) + 1 = 0',
        '\u03D5 = (1+\u221A5)/2',
        'p(n) ~ e^\u03C0\u221A(2n/3) / 4n\u221A3',
        '\u2211 1/p diverges',
        /* ── Transforms & probability ── */
        'F\u0302(\u03BE) = \u222Bf(x)e^(\u22122\u03C0ix\u03BE)dx',
        'E[X] = \u222Bx f(x)dx',
        'f*g = \u222Bf(\u03C4)g(t\u2212\u03C4)d\u03C4',
        '\u039B(n) = ln p if n=p\u1D4F',
        'M(x) = \u2211\u207F\u2264\u02E3 \u03BC(n)',
    ];

    function populate() {
        const bg = document.getElementById('mathBg');
        if (!bg) return;
        for (let i = 0; i < 30; i++) {
            const el = document.createElement('span');
            el.textContent = symbols[i % symbols.length];
            el.style.cssText =
                'left:'               + (Math.random() * 100)      + '%;' +
                'font-size:'          + (1 + Math.random() * 2.4)  + 'rem;' +
                'animation-duration:' + (18 + Math.random() * 28)  + 's;' +
                'animation-delay:-'   + (Math.random() * 40)       + 's;';
            bg.appendChild(el);
        }
    }

    /* Run as soon as the DOM is available */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', populate);
    } else {
        populate();
    }
})();
</script>
