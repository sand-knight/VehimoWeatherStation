<?php
    session_start();
    if( !isset($_SESSION["Username"]) ){
      header("Location: /home.php");
      exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
<title>VehimoWS</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
   integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
   crossorigin=""/>

<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
   integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="
   crossorigin=""></script>


<style>
html,body,h1,h2,h3,h4,h5 {font-family: "Raleway", sans-serif}
</style>
</head>
<body class="w3-light-grey">

<!-- Top container -->
<div class="w3-bar w3-top w3-black w3-large" style="z-index:4">
  <button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey" onclick="w3_open();"><i class="fa fa-bars"></i>  Menu</button>
  <span class="w3-bar-item w3-right w3-hover-red" onclick=logout() style="cursor:pointer">Logout</span>
</div>

<!-- Sidebar/menu -->
<nav class="w3-sidebar w3-collapse w3-white w3-animate-left" style="z-index:3;width:300px;" id="mySidebar"><br>
  <div class="w3-container w3-row">
    <div class="w3-col s4">
      <img src="https://i.imgflip.com/1nuxj5.jpg" class="w3-circle w3-margin-right" style="width:46px">
    </div>
    <div class="w3-col s8 w3-bar">
      <span>Welcome, <strong><?php echo $_SESSION["Username"]; ?></strong></span><br>

<!----------------------------------------------------------------------------------- BAR ITEMS
	  <a href="#" class="w3-bar-item w3-button"><i class="fa fa-envelope"></i></a>
      <a href="#" class="w3-bar-item w3-button"><i class="fa fa-user"></i></a>
      <a href="#" class="w3-bar-item w3-button"><i class="fa fa-cog"></i></a>
 -------------------------------------------------------------------------------------BAR ITEMS
 -->
    </div>
  </div>
  <hr>
  <div class="w3-container">
    <h5>Dashboard</h5>
  </div>
  <div class="w3-bar-block">
    <a href="#" class="w3-bar-item w3-button w3-padding-16 w3-hide-large w3-dark-grey w3-hover-black" onclick="w3_close()" title="close menu"><i class="fa fa-remove fa-fw"></i>  Close Menu</a>
    <a href="/dash.php" class="w3-bar-item w3-button w3-padding"><i class="fa fa-users fa-fw"></i>  Overview</a>
    <a href="/table.php" class="w3-bar-item w3-button w3-padding"><i class="fa fa-table fa-fw"></i>  Table</a>
    <a href="/map.php" class="w3-bar-item w3-button w3-padding w3-blue"><i class="fa fa-map fa-fw"></i>  Map</a>
    <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-share fa-fw"></i>  Shared</a>
    <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-gear fa-fw"></i> Manage Devices</a>
    <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-bell fa-fw"></i>  Deals</a>
    <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-gears fa-fw"></i>  Settings</a>
  </div>
</nav>


<!-- Overlay effect when opening sidebar on small screens -->
<div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:300px;margin-top:43px;">

  <!-- Header   ---------------------------------------------------------------------------THE DASHBOARD CON LE CARD   -->
  <header class="w3-container" style="padding-top:22px">
    <h5><b><i class="fa fa-map"></i> Map view </b></h5>
  </header>

  
<?php

  $conn = mysqli_connect("localhost:3306", "webserver", "passwordsicura", "VehiMoWS");

  if (!$conn) {
      echo "died";
      die("Connessione fallita: " . mysqli_connect_error());
  }

    
  $query='SELECT Timestamp, Latitude, Longitude, Temperature, Pressure, Humidity FROM Climate WHERE device_id IN (SELECT id FROM Registered_Devices WHERE User="'.$_SESSION["login"].'")';
  $result=mysqli_query($conn, $query );


  if (mysqli_num_rows($result)==0){
    echo "No data to visualize";

    echo '<script type="text/javascript">'."\n";


    echo "var locations = [];\n";
  }else{
    echo '<div class="w3-container w3-dark-grey w3-padding-32">'."\n";
    echo "\t".'<div class="w3-text-black">';
    
   
    echo "\t\t".'<div id="map" style="width: 100%; height: 500px;"></div>'."\n";
    echo "\t".'</div>';
    echo '</div>'."\n\n";

    echo '<script type="text/javascript">'."\n";

    echo "var locations = [\n";
    while ( $row=mysqli_fetch_array($result)){
      echo "{\nMeasures: '".$row["Temperature"]." C°; ".$row["Pressure"]." hPa; ".$row["Humidity"]."%',\n";
      echo "Timestamp: '".$row["Timestamp"]."',\n";
      echo "Latitude: ".$row["Latitude"]." ,\n";
      echo "Longitude: ".$row["Longitude"]."\n},\n";
      
    }
    echo "];";

  }
  mysqli_close($conn);

        
?>

    var map = L.map('map').setView([locations[0]["Latitude"],locations[0]["Longitude"]], 16);      
    
    for (i = 0; i < locations.length; i++) {  
        L.marker([locations[i]["Latitude"], locations[i]["Longitude"]]).addTo(map).bindPopup(
            '<div class="w3-container w3-center"><h6>'+locations[i]["Timestamp"]+"</h6><br>"+
            locations[i]["Measures"]+'</div>'
        );
    }


    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

  </script>
        
<br>
  <div class="w3-container w3-dark-grey w3-padding-32">
    <div class="w3-row">
      <div class="w3-container w3-third">
        <h5 class="w3-bottombar w3-border-green">Devices</h5>
        <p>Buy new</p>
        <p>Review</p>
        <p>Settings</p>
      </div>
      <div class="w3-container w3-third">
        <h5 class="w3-bottombar w3-border-white">System</h5>
        <p>Language</p>
        <p>Storage plans</p>
        <p>Settings</p>
      </div>
      <div class="w3-container w3-third">
        <h5 class="w3-bottombar w3-border-red">Data</h5>
        <p>Find shared data</p>
        <p>Share data</p>
        <p>Data manipulation</p>
      </div>
    </div>
  </div>
  

  <!-- Footer -->
  <footer class="w3-container w3-padding-16 w3-light-grey">
    <h4>FOOTER</h4>
    <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
  </footer>

  <!-- End page content -->
</div>

<script>
// Get the Sidebar
var mySidebar = document.getElementById("mySidebar");

// Get the DIV with overlay effect
var overlayBg = document.getElementById("myOverlay");

// Toggle between showing and hiding the sidebar, and add overlay effect
function w3_open() {
  if (mySidebar.style.display === 'block') {
    mySidebar.style.display = 'none';
    overlayBg.style.display = "none";
  } else {
    mySidebar.style.display = 'block';
    overlayBg.style.display = "block";
  }
}

// Close the sidebar with the close button
function w3_close() {
  mySidebar.style.display = "none";
  overlayBg.style.display = "none";
}

function logout(){
  const form = document.createElement('form');
    form.method="post";
    form.action="/home.php";

    const input= document.createElement("input");
    input.name="Logout";
    input.value="addio";
    input.type="hidden";

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>

</body>
</html>

