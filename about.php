<?php $pageTitle = 'About'; ?>
<?php include 'header.php'; ?>
        <?php include 'chalkboard-bg.php'; ?>
<style>
    .home-wrapper {
        flex: 1;
        width: 100%;
        padding: 5rem 5% 3rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .home-image {
        width: 48%;
        position: sticky;
        top: 5rem;
    }

    .home-image img {
        width: 100%;
        border-radius: 4px;
        box-shadow: 0 8px 40px rgba(0,0,0,0.5);
    }

    .faq {
        width: 40%;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .faq-btn {
        width: 100%;
        background: rgba(22, 34, 24, 0.75);
        border: 1px solid rgba(240,236,224,0.15);
        border-radius: 3px;
        color: var(--chalk-white);
        font-family: 'Crimson Pro', Georgia, serif;
        font-size: 1.15rem;
        font-weight: 400;
        letter-spacing: 0.03em;
        text-align: left;
        padding: 1rem 1.4rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s, border-color 0.2s;
    }

    .faq-btn:hover {
        background: rgba(201,168,76,0.1);
        border-color: rgba(201,168,76,0.4);
    }

    .faq-btn.open {
        background: rgba(201,168,76,0.12);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .faq-btn .chevron {
        font-size: 0.75rem;
        transition: transform 0.25s;
        color: var(--chalk-faint);
        flex-shrink: 0;
    }

    .faq-btn.open .chevron {
        transform: rotate(180deg);
        color: var(--accent-gold);
    }

    .faq-body {
        display: none;
        background: rgba(15, 25, 17, 0.7);
        border: 1px solid rgba(201,168,76,0.25);
        border-top: none;
        border-bottom-left-radius: 3px;
        border-bottom-right-radius: 3px;
        padding: 1.2rem 1.4rem;
        color: var(--chalk-dim);
        font-family: 'Crimson Pro', Georgia, serif;
        font-size: 1.05rem;
        line-height: 1.7;
        animation: slideDown 0.2s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .faq-body ul {
        margin: 0.4rem 0 0 1rem;
        padding: 0;
    }

    .faq-body li { margin-bottom: 0.4rem; }

    .faq-body a {
        color: var(--accent-gold);
        text-decoration: none;
        border-bottom: 1px solid rgba(201,168,76,0.35);
    }

    .faq-body a:hover { border-color: var(--accent-gold); }

    @media (max-width: 768px) {
        .home-wrapper { flex-direction: column; }
        .home-image, .faq { width: 100%; position: static; }
    }
</style>

<div class="home-wrapper">

    <div class="home-image">
        <img src="Images/about_background.jpg" alt="Math">
    </div>

    <div class="faq">

        <div class="faq-item">
            <button class="faq-btn" onclick="toggleFaq(this)">
                What is MathMatch?
                <span class="chevron">&#9660;</span>
            </button>
            <div class="faq-body">
                MathMatch is a new way to study for your classes, help out students looking
                for answers, and make new friends!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-btn" onclick="toggleFaq(this)">
                How does it work?
                <span class="chevron">&#9660;</span>
            </button>
            <div class="faq-body">
                <ul>
                    <li>Students get to ask questions on the question board</li>
                    <li>Tutors look for questions on the very same board</li>
                    <li>When a tutor chooses their question, both the tutor and student match up</li>
                    <li>When a match occurs, they enter a chatroom where they can talk and interact with each other to solve the question and any ones following</li>
                    <li>You can match with multiple users on the platform with different subjects</li>
                    <li>Satisfied with the service received? Unmatch with your pair after you're done</li>
                </ul>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-btn" onclick="toggleFaq(this)">
                Why should I use it?
                <span class="chevron">&#9660;</span>
            </button>
            <div class="faq-body">
                <ul>
                    <li>It offers a more human connection to education</li>
                    <li>You get to know your fellow alumni and make new connections</li>
                    <li>Maybe even start something new!</li>
                    <li>Get your questions solved by real humans, not robots!</li>
                </ul>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-btn" onclick="toggleFaq(this)">
                How much does it cost?
                <span class="chevron">&#9660;</span>
            </button>
            <div class="faq-body">
                The low low price of ZERO dollars (and ZERO cents).
                We only run off of donations from the school and through people like you!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-btn" onclick="toggleFaq(this)">
                Is this safe?
                <span class="chevron">&#9660;</span>
            </button>
            <div class="faq-body">
                Yup — there's a feature to report any messages that break our terms of service.
                If a user is found violating the terms of service, they are banned from MathMatch,
                no exceptions.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-btn" onclick="toggleFaq(this)">
                Where can I sign up?
                <span class="chevron">&#9660;</span>
            </button>
            <div class="faq-body">
                <a href="signup.php">Right here!</a> — or via the Sign Up button in the top right.
            </div>
        </div>

    </div>
</div>

<script>
function toggleFaq(btn) {
    const body = btn.nextElementSibling;
    const isOpen = btn.classList.contains('open');

    if (isOpen) {
        btn.classList.remove('open');
        body.style.display = 'none';
    } else {
        btn.classList.add('open');
        body.style.display = 'block';
    }
}
</script>

<?php include 'footer.php' ?>
</body>
</html>
