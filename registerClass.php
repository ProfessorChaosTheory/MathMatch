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
<?php if (!empty($_SESSION['registerC_success'])): ?>
    <div style="
        max-width: 560px;
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
        Class successfully registered.
    </div>
    <?php unset($_SESSION['signup_success']); ?>
<?php endif; ?>


<?php
// Show error feedback from registerClass.php redirects
$errors = [
    'missing'  => 'Please fill out all fields.',
    'db'       => 'A database error occurred. Please try again.',
    'dup'      => 'Class has already been registered.'
];
$err = $_GET['error'] ?? '';
if ($err && isset($errors[$err])): ?>
    <div class="container w-75 mt-3">
        <div class="alert alert-danger"><?php echo htmlspecialchars($errors[$err]); ?></div>
    </div>
<?php endif; ?>

<div class="container w-75 mt-3">
    <h3>Class Registration Form</h3>
    <p>Please complete and submit the form.</p>

    <form name="registerClass" action="registerCAction.php" method="POST" class="was-validated">
        <div class="mb-3 mt-3">
            <label for="class" class="form-label">Class Name:</label>
            <input class="form-control" placeholder="Enter Class Name" name="cname"
                   value="<?php echo htmlspecialchars($_GET['cname'] ?? ''); ?>" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
        </div>
        <div class="mb-3 mt-3">
            <label for="classD" class="form-label">Class Description:</label>
            <input class="form-control" placeholder="Enter Class Description" name="classD"
                   value="<?php echo htmlspecialchars($_GET['classD'] ?? ''); ?>" required>
            <div class="valid-feedback">Valid.</div>
            <div class="invalid-feedback">Please fill out this field.</div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php include 'footer.php' ?>
</body>
</html>

