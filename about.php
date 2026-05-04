<?php $pageTitle = 'About'; ?>
<?php include 'header.php'; ?>
        <?php include 'chalkboard-bg.php'; ?>
        <div class="page" style="align-items: flex-start; padding-top: 5rem;">
            <button onClick="myFunction('about1')" class="w3-btn w3-block w3-left-align">What is MathMatch?
            </button>
            <div id="about1" class="w3-container w3-animate-left w3-hide">MathMatch is a new way to study for your classes,
                help out students looking for answers, and make new friends! </div>
            <button onClick="myFunction('about2')" class="w3-btn w3-block w3-left-align">How does it work?</button>
            <div class="w3-container w3-animate-left w3-hide" id="about2">
                <h4>Glad you asked!</h4>
                <ul class="w3-ul">
                    <li>Students get to ask questions on the question board</li>
                    <li>Tutors look for questions on the very same board</li>
                    <li>When a tutor chooses their question, both the tutor and student match up</li>
                    <li>When a match occurs, they enter a chatroom where they can talk and interact with each other to solve the question and any ones following</li>
                    <li>You can match with multiple users on the platform with different subjects</li>
                    <li>Satisfied with the service received? Unmatch with your pair after you're done</li>
                </ul>
            </div>
            <button onClick="myFunction('about3')" class="w3-btn w3-block w3-left-align">Ok, Why should I use it?</button>
            <div class="w3-container w3-animate-left w3-hide" id="about3">
                <ul class="w3-ul">
                    <li>It offers a more human connection to education</li>
                    <li>You get to know your fellow alumni and make new connections</li>
                    <li>Maybe even start something new!</li>
                    <li>Get your questions solved by real humans, not robots!</li>
                </ul>
            </div>
            <button onClick="myFunction('about4')" class="w3-btn w3-block w3-left-align">How much is it?</button>
            <div class="w3-container w3-animate-left w3-hide" id="about4">
                <p>The low low price of ZERO dollars! (and ZERO cents). 
                    We only run off of donations from the school and through people like you! </p> </div>
            <button onClick="myFunction('about5')" class="w3-btn w3-block w3-left-align">Is this safe?</button>
            <div  class="w3-container w3-animate-left w3-hide" id="about5">
                <p>Yup, there's a feature to report any messages from either user that breaks our terms of service. 
                    If a user is found violating the terms of service, then the user is banned from MathMatch, no exceptions
                </p>
            </div>
            <button onClick="myFunction('about6')" class="w3-btn w3-block w3-left-align">Where can I sign up?</button>
            <div class="w3-container w3-animate-left w3-hide" id="about6">
                <a href="signup.php"> Right here! </a>
                <p>
                    or on the top right of the page!
                </p>
            </div>
        </div>
        <?php include 'footer.php' ?>
        </div>


        <script>
            function myFunction(id) {
                var x = document.getElementById(id);
                if (x.className.indexOf("w3-show") == -1) {
                    x.className += " w3-show";
                    x.previousElementSibling.className += " w3-blue";
                } else {
                    x.className = x.className.replace(" w3-show", "");
                    x.previousElementSibling.className =
                            x.previousElementSibling.className.replace("w3-blue", "");
                }
            }
        </script>

