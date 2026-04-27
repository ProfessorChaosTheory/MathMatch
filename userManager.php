<?php
session_start();
require 'DBConnect.php';

// Admin check
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 1) {
    die("Access denied");
}

// PDO connection (keep just ONE)
$pdo = new PDO("mysql:host=localhost;dbname=mathmatch;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// ✏️ Handle update
if (isset($_POST['update'])) {
    $stmt = $pdo->prepare("
        UPDATE users 
        SET usertype = ?, username = ?, user_email = ?
        WHERE userID = ?
    ")
    ;
    echo "<script>alert('User information has been updated.');</script>";
    
    $stmt->execute([
        $_POST['usertype'],
        $_POST['username'],
        $_POST['user_email'],
        $_POST['userID']
    ]);
}

// Handle delete
if (isset($_POST['delete'])) {
    $userID = $_POST['userID'];

    // ❌ Prevent deleting yourself
    if ($userID == $_SESSION['userID']) {
        echo "<script>alert('You cannot delete your own account.');</script>";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE userID = ?");
        $stmt->execute([$userID]);

        echo "<script>alert('User deleted successfully.');</script>";
    }
}



// 📥 Fetch users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<html>
    <?php include 'header.php' ?>
    <?php include 'chalkboard-bg.php'; ?>
    <div class="container w-75 mt-3">
        <h2>User Management</h2>
    </div>
    <div class="page">
    <div class="dash-wrap">
        <div class="card mb-3">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($users as $user): ?>
                <tr>
                    <form method="POST">
                        <td><?= $user['userID'] ?></td>

                        <td>
                            <select name="usertype">
                                <option value="0" <?= $user['usertype']==0?'selected':'' ?>>User</option>
                                <option value="1" <?= $user['usertype']==1?'selected':'' ?>>Admin</option>
                                <option value="2" <?= $user['usertype']==2?'selected':'' ?>>Tutor</option>
                                <option value="3" <?= $user['usertype']==3?'selected':'' ?>>Student</option>
                            </select>
                        </td>

                        <td>
                            <input type="text" name="username" value="<?= $user['username'] ?>">
                        </td>

                        <td>
                            <input type="email" name="user_email" value="<?= $user['user_email'] ?>">
                        </td>

                        <td>
                            <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
                            <button type="submit" name="update">Update</button>
                            
                            <!-- 🗑️ Delete button -->
                            <button type="submit" name="delete"
                                onclick="return confirm('Are you sure you want to delete this user?');"
                                style="color:red;">
                                Delete
                            </button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    </div>
    <?php include 'footer.php' ?>
    
</html>

