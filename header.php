<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="stylesheet" href="mystyles.css">
    
<body>
 <div class="w3-sidebar w3-bar-block w3-card w3-animate-left w3-black w3-top" style="display:none" id="mySidebar">
  <button class="w3-bar-item w3-button w3-large"
  onclick="w3_close()">Close &times;</button>
  <a href="sorry.php" class="w3-bar-item w3-button">Tutoring</a>
  <a href="sorry.php" class="w3-bar-item w3-button">Question Board</a>
  <a href="sorry.php" class="w3-bar-item w3-button">Class Tutoring Calendar</a>
  <a href="sorry.php" class="w3-bar-item w3-button">Miscellaneous</a>
</div>

<div id="main">
    <div class="w3-cell-row" style="width:100%">

    <div class="w3-container w3-blue w3-cell">
        <a href="index.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white"><h2>Home</h2></a>
        <a href="sorry.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white"><h2>About</h2></a>
        <a href="sorry.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white"><h2>Contact</h2></a>
    </div>
        
    <div class="w3-container w3-blue w3-cell">
        <button id="openNav" class="w3-button w3-blue w3-hover-black w3-hover-text-white w3-left" onclick="w3_open()"><h1>MathMatch <?php "\n" ?> Services</button>
    </div>
        
    <div class="w3-container w3-blue w3-cell"> 
        &nbsp;
    </div>

       
    <div class="w3-container w3-blue w3-cell">
        <input type="text" class="w3-bar-item w3-input w3-margin-top" placeholder="Search..">
        <a href="#" class="w3-bar-item w3-button w3-black w3-hover-blue w3-hover-text-black">Go</a>
        <div class="w3-right">
            <a href="signup.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white boxed"><b>Sign Up!</b></a>
        </div>
        <div class="w3-right">
            <a href="login.php" class="w3-bar-item w3-button w3-hover-black w3-text-black w3-hover-text-white
               "><b>Login</b></a>
    </div>
      
</div>
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
