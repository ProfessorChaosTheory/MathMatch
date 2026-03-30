<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathMatch – Sign Up</title>
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
</head>
<body>

<?php
// Show error feedback from signupAction.php redirects
$errors = [
    'missing'  => 'Please fill out all fields.',
    'email'    => 'Please enter a valid email address.',
    'mismatch' => 'Passwords do not match.',
    'taken'    => 'That username or email is already registered.',
    'db'       => 'A database error occurred. Please try again.',
];
$err = $_GET['error'] ?? '';
if ($err && isset($errors[$err])): ?>
    <div class="container w-75 mt-3">
        <div class="alert alert-danger"><?php echo htmlspecialchars($errors[$err]); ?></div>
    </div>
<?php endif; ?>

<div class="container w-75 mt-3">
    <h3>Sign Up Form</h3>
    <p>Please complete and submit the form.</p>

    <form name="signup" action="signupAction.php" method="POST" class="was-validated">
        <div class="mb-3 mt-3">
            <label for="uname" class="form-label">Username:</label>
            <input class="form-control" placeholder="Enter username" name="uname"
                   value="<?php echo htmlspecialchars($_GET['uname'] ?? ''); ?>" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" class="form-control" placeholder="Email address" name="email"
                   value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>
        <div class="mb-3">
            <label for="pswd" class="form-label">Password:</label>
            <input type="password" class="form-control" placeholder="Enter password" name="pswd" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
        </div>
        <div class="mb-3">
            <label for="pswd2" class="form-label">Confirm Password:</label>
            <input type="password" class="form-control" placeholder="Repeat password" name="pswd2" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
        </div>
        <div class="mb-3">
            <label for="question" class="form-label">Security Question:</label>
            <input class="form-control" placeholder="Security Question" name="question"
                   value="<?php echo htmlspecialchars($_GET['question'] ?? ''); ?>" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
        </div>
        <div class="mb-3">
            <label for="answer" class="form-label">Security Answer:</label>
            <input class="form-control" placeholder="Security Answer" name="answer" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="login.php" class="btn btn-secondary ms-2">Back to Login</a>
    </form>
</div>

<?php include 'footer.php' ?>
</body>
</html>
