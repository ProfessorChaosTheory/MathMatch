
<html>
    <head>
        <title>MathMatch</title>
    <br>
    <?php include 'header.php' ?>
</head>
</html>
<?php

require "DBConnect.php";

if ($_GET['pswd'] !== $_GET['pswd2']) {
    echo "Passwords don't match.";
    die();
    
}
// collect form data
$usertype = 1;
$uname = $_GET["uname"];
$email = $_GET["email"];
$pswd = $_GET["pswd"];
$question = $_GET["question"];
$answer = $_GET["answer"];
$is_tutor = NULL;
$TT_1 = NULL;
$TT_2 = NULL;
$TT_3 = NULL;
$hash = password_hash($pswd, PASSWORD_DEFAULT);

$sql = "insert into users values (0, '". $usertype . "', '" . $uname . "', '" . 
  $email . "', '" . $pswd . "', '" . $question . "', '" . 
  $answer . "', '" . $is_tutor . "', '" . 
  $TT_1 . "', '" . $TT_2 . "', '" . $TT_3 . "', '" . $hash . "')";
echo modifyDB($sql) . "<br>Use back button to return.";
?>
