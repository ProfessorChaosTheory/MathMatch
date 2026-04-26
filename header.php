<?php
require 'DBConnect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
</head>
<body>


    
<div class="w3-bar w3-top w3-blue" id="main" style="position: fixed; top: 0; z-index: 9999">
    <button id="openNav" class="w3-bar-item w3-button w3-blue w3-hover-black w3-hover-text-white w3-left w3-xlarge" onclick="w3_open()">MathMatch <?php "\n" ?> </button>
    <a href="index.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white w3-xlarge">Home</a>
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
  <div class="w3-sidebar w3-bar-block w3-card w3-animate-left w3-black w3-top" style="display:none; position: fixed; top: 0; z-index: 9999" id="mySidebar">
  <button class="w3-bar-item w3-button w3-large"
  onclick="w3_close()">Close &times;</button>
  <a href="sorry.php" class="w3-bar-item w3-button">Tutoring</a>
  <a href="sorry.php" class="w3-bar-item w3-button">Question Board</a>
  <a href="sorry.php" class="w3-bar-item w3-button">Class Tutoring Calendar</a>
  <a href="sorry.php" class="w3-bar-item w3-button">Miscellaneous</a>
  <?php if (isset($_SESSION['username']) && ($_SESSION['usertype']) == 1): ?>
    <div class="dropdown">
        <button class="drop-btn w3-button">Admin Tools <i class="fa fa-caret-down"></i></button>      
        <div class="dropdown-content">
            <a href="registerClass.php" class="w3-bar-item w3-button">Class Registration</a>
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
