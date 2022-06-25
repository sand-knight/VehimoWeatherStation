


<!DOCTYPE html>
<html>
<head>
<title>VehimoWS</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body,h1,h2,h3,h4,h5,h6 {font-family: "Raleway", Arial, Helvetica, sans-serif}
</style>
</head>
<body class="w3-light-grey">

<?php
     
     if (isset($_POST["login"]) && isset ($_POST["passwd"]))
     {
         
         if( isset($_POST["login"]) && isset($_POST["passwd"])){
            $hashed = password_hash($_POST["passwd"], PASSWORD_BCRYPT);
            
            $conn = mysqli_connect("localhost:3306", "webserver", "passwordsicura", "VehiMoWS");

            if (!$conn) {
                echo "died";
                die("Connessione fallita: " . mysqli_connect_error());
            }

            $query='SELECT * FROM Users WHERE Email="'.$_POST["login"].'"';
            $result=mysqli_query($conn, $query );


            if (mysqli_num_rows($result)>0){
                $user=mysqli_fetch_array($result);
                $auth_result=password_verify($_POST["passwd"], $user["password"]);

                if($auth_result) {
                    session_start();

                    $_SESSION["Username"]=$user["Username"];
                    $_SESSION["login"]=$user["Email"];
                    header("Location: /dash.php");
                    exit();
                }else{
                  $msg= "Login failed";
                }
            }else{
              $msg = "User not found ";
            }

                
         }
         
     }
     else
        if (isset ($_POST["Logout"])){
          session_start();
          session_unset();
          session_destroy();
        }



?>

<!-- ------------------------------------------------------- Navigation Bar -------------------------------------------------------->
<div class="w3-bar w3-black w3-large">
  <a href="#" class="w3-bar-item w3-button w3-blue w3-mobile"><i class="fa fa-user w3-margin-right"></i>Login</a>
  <a href="#prods" class="w3-bar-item w3-button w3-mobile">Products</a>
  <a href="#about" class="w3-bar-item w3-button w3-mobile">About</a>
  <a href="#contact" class="w3-bar-item w3-button w3-mobile">Contact</a>
</div>


<!----------------------------------------------------------- Header ---------------------------------------------------------------->
<header class="w3-display-container w3-content" style="max-width:1500px;">
  <img class="w3-image" src="https://i.pinimg.com/originals/34/37/24/3437248a4011e7a6226ec3cf549011ab.jpg" alt="HazMat" style="min-width:1000px" width="1500" height="800">
  <div class="w3-display-left w3-padding w3-col l6 m8">
    
      <?php
        if(!isset($msg)) echo '<div class="w3-container w3-black">';
        else{
          echo '<div class="w3-container w3-red">';
          echo '<h2 class="w3-right">'.$msg.'</h2>';
        }
      ?>
      <h2><i class="fa fa-user w3-margin-right"></i>Login</h2>
    </div>
    
    <div class="w3-container w3-white w3-padding-16">
      <form action="/home.php" method="post">
        <div class="w3-row-padding" style="margin:0 -16px;">
          <div class="w3-half w3-margin-bottom">
            <label><i class="fa fa-envelope"></i> E-Mail</label>
            <input class="w3-input w3-border" type="text" name="login" required>
          </div>
          <div class="w3-half">
            <label><i class="fa fa-unlock-alt"></i> Password </label>
            <input class="w3-input w3-border" type="password" name="passwd" required>
          </div>
        </div>
        <button class="w3-button w3-dark-grey" type="submit"><i class="fa fa-sign-in w3-margin-right"></i> Enter</button>
      </form>
    </div>
  </div>
</header>

<!-- Page content -->
<div class="w3-content" style="max-width:1532px;">

  <div class="w3-container w3-margin-top" id="prods">
    <h3>Weather stations</h3>
    <p>
    	Different and scalable solutions for any kind of weather analisis, for any mobility available.
    </p>
  </div>
  
  
  <div class="w3-row-padding w3-padding-16">
  <!----------------------------------------- FIRST CARD ------------------------------->
  
    <div class="w3-third w3-margin-bottom">
      <img src="https://columbiaweather.com/assets/media/products/Orion/Orion_VM.jpg" alt="Lancelot" style="width:100%">
      <div class="w3-container w3-white">
        <h3> Vehicle Mount Weather Station</h3>
        <h6 class="w3-opacity">From $300/mo</h6>
        
        <p class="w3-large"><i class="fa fa-globe"></i> <i class="fa fa-map-o"></i> <i class="fa fa-wifi"></i> <i class="fa fa-bar-chart"> </i></p>
        <button class="w3-button w3-block w3-black w3-margin-bottom">See products</button>
      </div>
    </div>
    
    <!-------------------------------------- SECOND CARD ------------------------------------------------>
    <div class="w3-third w3-margin-bottom">
      <img src="https://columbiaweather.com/assets/media/Portable.jpg" alt="Camelot" style="width:100%">
      <div class="w3-container w3-white">
        <h3>Portable Weather Station</h3>
        <h6 class="w3-opacity">From $200/mo</h6>
        
        <p class="w3-large"><i class="fa fa-bar-chart"></i> <i class="fa fa-signal"></i> <i class="fa fa-bell"></i></p>
        <button class="w3-button w3-block w3-black w3-margin-bottom">See solutions</button>
      </div>
    </div>
    
    <!----------------------------------------- THIRD CARD ----------------------------------------------->
    <div class="w3-third w3-margin-bottom">
      <img src="https://www.gannett-cdn.com/authoring/2016/09/05/NDNJ/ghows-LK-3b9ff694-37f3-51d3-e053-0100007f0e44-3b8b159b.jpeg?crop=4031,2277,x0,y746&width=2560" alt="Bedivere" style="height:354px; width:100%">
      <div class="w3-container w3-white">
        <h3>Flexible plan</h3>
        <h6 class="w3-opacity">From $799</h6>
        <p class="w3-large"><i class="fa fa-globe"></i> <i class="fa fa-map-o"></i> <i class="fa fa-wifi"></i> <i class="fa fa-bar-chart"> </i></p>
        <button class="w3-button w3-block w3-black w3-margin-bottom">See products</button>
      </div>
    </div>
  </div>

  <div class="w3-row-padding" id="about">
    <div class="w3-col l4 12">
      <h3>About</h3>
      <h6>
      Our company was born as an University IoT project. After attaing perfect score and gaining international interest, we patented our project, and produced the first propotype here in Aversa, with the help of a fundrising.<br>
      After 30 years of experience, our products reached astonishing quality/price ratios, and rock solid reliability. Our main clients are from USA though.</h6>
    <p>We accept: <i class="fa fa-credit-card w3-large"></i> <i class="fa fa-cc-mastercard w3-large"></i> <i class="fa fa-cc-amex w3-large"></i> <i class="fa fa-cc-cc-visa w3-large"></i><i class="fa fa-cc-paypal w3-large"></i></p>
    </div>
    <div class="w3-col l8 12">
      <!-- Image of location/map -->
      <img src="https://media-cdn.tripadvisor.com/media/photo-o/17/d5/9e/d3/dsc-1220-largejpg.jpg" class="w3-image w3-greyscale" style="width:100%;">
    </div>
  </div>
  
  <div class="w3-row-padding w3-large w3-center" style="margin:32px 0">
    <div class="w3-third"><i class="fa fa-map-marker w3-text-red"></i> 42 Some adr, Aversa (CE) Italy</div>
    <div class="w3-third"><i class="fa fa-phone w3-text-red"></i> Phone: +39 333 5318008 </div>
    <div class="w3-third"><i class="fa fa-envelope w3-text-red"></i> Email: mail@mail.com</div>
  </div>

  <div class="w3-panel w3-red w3-leftbar w3-padding-32">
    <h6><i class="fa fa-info w3-deep-orange w3-padding w3-margin-right"></i> Due to silicon crisis, some devices may not be in stock right now!</h6>
  </div>

  <div class="w3-container">
    <h3>Some photos</h3>
    <h6>You can find our devices anywhere in the world:</h6>
  </div>
  
  <div class="w3-row-padding w3-padding-16 w3-text-white w3-large">
    <div class="w3-half w3-margin-bottom">
      <div class="w3-display-container">
        <img src="https://www.newscaststudio.com/wp-content/uploads/2015/09/wmc1.png" alt="Cinque Terre" style="width:100%">
        <span class="w3-display-bottomleft w3-padding">News reporters</span>
      </div>
    </div>
    <div class="w3-half">
      <div class="w3-row-padding" style="margin:0 -16px">
        <div class="w3-half w3-margin-bottom">
          <div class="w3-display-container">
            <img src="https://thenewswheel.com/wp-content/uploads/2017/03/Tornado-Hunters-Ford-F-150-Lariat-7.jpg" alt="New York" style="height:260px;width:100%">
            <span class="w3-display-bottomleft w3-padding">Tornado hunters</span>
          </div>
        </div>
        <div class="w3-half w3-margin-bottom">
          <div class="w3-display-container">
            <img src="https://columbiaweather.com/assets/media/Vehicle-Mount.jpg" alt="San Francisco" style="width:100%">
            <span class="w3-display-bottomleft w3-padding">Hazardous Materials Emergency Response</span>
          </div>
        </div>
      </div>
      <div class="w3-row-padding" style="margin:0 -16px">
        <div class="w3-half w3-margin-bottom">
          <div class="w3-display-container">
            <img src="https://cdn10.picryl.com/photo/2008/05/02/winchester-va-may-02-2008-mount-weather-participated-in-the-shenandoah-apple-f95193-1024.jpg" alt="Pisa" style="width:100%">
            <span class="w3-display-bottomleft w3-padding">Fire Fighters</span>
          </div>
        </div>
        <div class="w3-half w3-margin-bottom">
          <div class="w3-display-container">
            <img src="http://broadcast.weathermetrics.com/images/27.jpg" alt="Paris" style="width:100%">
            <span class="w3-display-bottomleft w3-padding">Meteo Forecast Mobile Labs</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="w3-container w3-padding-32 w3-black w3-opacity w3-card w3-hover-opacity-off" style="margin:32px 0;">
    <h2>Get the best offers first!</h2>
    <p>Join our newsletter.</p>
    <label>E-mail</label>
    <input class="w3-input w3-border" type="text" placeholder="Your Email address">
    <button type="button" class="w3-button w3-red w3-margin-top">Subscribe</button>
  </div>

  <div class="w3-container" id="contact">
    <h2>Contact</h2>
    <p>If you have any questions, do not hesitate to ask them.</p>
    <i class="fa fa-map-marker w3-text-red" style="width:30px"></i> 42 Some adr, Aversa (CE) Italy<br>
    <i class="fa fa-phone w3-text-red" style="width:30px"></i> Phone: +39 333 5318008<br>
    <i class="fa fa-envelope w3-text-red" style="width:30px"> </i> Email: mail@mail.com<br>
    <form action="/action_page.php" target="_blank">
      <p><input class="w3-input w3-padding-16 w3-border" type="text" placeholder="Name" required name="Name"></p>
      <p><input class="w3-input w3-padding-16 w3-border" type="text" placeholder="Email" required name="Email"></p>
      <p><input class="w3-input w3-padding-16 w3-border" type="text" placeholder="Message" required name="Message"></p>
      <p><button class="w3-button w3-black w3-padding-large" type="submit">SEND MESSAGE</button></p>
    </form>
  </div>

<!-- End page content -->
</div>

<!-- Footer -->
<footer class="w3-padding-32 w3-black w3-center w3-margin-top">
  <h5>Find Us On</h5>
  <div class="w3-xlarge w3-padding-16">
    <i class="fa fa-facebook-official w3-hover-opacity"></i>
    <i class="fa fa-instagram w3-hover-opacity"></i>
    <i class="fa fa-snapchat w3-hover-opacity"></i>
    <i class="fa fa-pinterest-p w3-hover-opacity"></i>
    <i class="fa fa-twitter w3-hover-opacity"></i>
    <i class="fa fa-linkedin w3-hover-opacity"></i>
  </div>
  <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank" class="w3-hover-text-green">w3.css</a></p>
</footer>

<!-- Add Google Maps -->
<script>
function myMap() {
  myCenter=new google.maps.LatLng(41.878114, -87.629798);
  var mapOptions= {
    center:myCenter,
    zoom:12, scrollwheel: false, draggable: false,
    mapTypeId:google.maps.MapTypeId.ROADMAP
  };
  var map=new google.maps.Map(document.getElementById("googleMap"),mapOptions);

  var marker = new google.maps.Marker({
    position: myCenter,
  });
  marker.setMap(map);
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBu-916DdpKAjTmJNIgngS6HL_kDIKU0aU&callback=myMap"></script>
<!--
To use this code on your website, get a free API key from Google.
Read more at: https://www.w3schools.com/graphics/google_maps_basic.asp
-->

</body>
</html>
