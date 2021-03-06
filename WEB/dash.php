<?php
    session_start();
    if( !isset($_SESSION["Username"]) ){
      header("Location: /home.php");
      exit();
    }else{
      $conn = mysqli_connect("localhost:3306", "webserver", "passwordsicura", "VehiMoWS");

      if (!$conn) {
          echo "died";
          die("Connessione fallita: " . mysqli_connect_error());
      }

      $query='SELECT COUNT(Timestamp) as ntuples FROM Climate, Registered_Devices WHERE Climate.device_id=Registered_Devices.id AND Registered_Devices.User="'.$_SESSION["login"].'"';
      $result=mysqli_query($conn, $query );


      if (mysqli_num_rows($result)>0){
          $row=mysqli_fetch_array($result);
          $ntuples=$row["ntuples"];

      }else{
        $adevice="msql error";
      }


      $query='SELECT COUNT(Timestamp) as newtuples FROM Climate, Registered_Devices WHERE Climate.device_id=Registered_Devices.id AND Registered_Devices.User="'.$_SESSION["login"].'" AND Timestamp>CURDATE()';
      $result=mysqli_query($conn, $query );


      if (mysqli_num_rows($result)>0){
          $row=mysqli_fetch_array($result);
          $newtuples=$row["newtuples"];

      }else{
        $adevice="msql error";
      }


      $query='SELECT device_name FROM Registered_Devices WHERE User="'.$_SESSION["login"].'"';
      $result=mysqli_query($conn, $query );


      if (mysqli_num_rows($result)>0){
          $row=mysqli_fetch_array($result);
          $adevice=$row["device_name"];

      }else{
        $adevice="msql error";
      }
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
      <span>Welcome, <strong><?php echo $_SESSION["Username"]; ?> </strong></span><br>

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
    <a href="/dash.php" class="w3-bar-item w3-button w3-padding w3-blue"><i class="fa fa-users fa-fw"></i>  Overview</a>
    <a href="/table.php" class="w3-bar-item w3-button w3-padding"><i class="fa fa-table fa-fw"></i>  Table</a>
    <a href="/map.php" class="w3-bar-item w3-button w3-padding"><i class="fa fa-map fa-fw"></i>  Map</a>
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
    <h5><b><i class="fa fa-dashboard"></i> My Dashboard</b></h5>
  </header>

  <div class="w3-row-padding w3-margin-bottom">
    <div class="w3-quarter">
      <a href="/table.php" style="text-decoration: none">
      <div class="w3-container w3-red w3-padding-16">
        <div class="w3-left"><i class="fa fa-table w3-xxxlarge"></i></div>
        <div class="w3-right">
          <h3><?php echo $ntuples; ?></h3>
        </div>
        <div class="w3-clear"></div>
        <h4>View table</h4>
      </div>
      </a>
    </div>
    <div class="w3-quarter">
      <a href="/map.php" style="text-decoration: none">
      <div class="w3-container w3-blue w3-padding-16">
        <div class="w3-left"><i class="fa fa-map w3-xxxlarge"></i></div>
        <div class="w3-right">
          <h3><?php echo $ntuples; ?></h3>
        </div>
        <div class="w3-clear"></div>
        <h4>View on map</h4>
      </div>
      </a>
    </div>
    <div class="w3-quarter">
      
      <div class="w3-container w3-teal w3-padding-16">
        <div class="w3-left"><i class="fa fa-share-alt w3-xxxlarge"></i></div>
        <div class="w3-right">
          <h3>23</h3>
        </div>
        <div class="w3-clear"></div>
        <h4>Shares</h4>
      </div>
      
    </div>
  </div>

  <div class="w3-panel">
    <div class="w3-row-padding" style="margin:0 -16px">
      <div class="w3-quarter">
        <h5>Devices</h5>
        <div class="w3-display-container">
        <img src="https://sc04.alicdn.com/kf/H18acc56e6be74678a4bcc9de3b75d375R.jpg" style="width:100%" alt="Google Regional Map">
        <div class="w3-display-bottomleft w3-container"><?php echo $adevice; ?></div>
        </div>
      </div>
      <div class="w3-threequarter">
        <h5>Feeds</h5>
        <table class="w3-table w3-striped w3-white">
          <tr>
            <td><i class="fa fa-user w3-text-blue w3-large"></i></td>
            <td>New records</td>
            <td><i> <?php echo $newtuples; ?></i></td>
          </tr>
          <tr>
            <td><i class="fa fa-bell w3-text-red w3-large"></i></td>
            <td>Database errors</td>
            <td><i>0</i></td>
          </tr>
          <!-- ----------------------------------------------------------------------FEEDS LISTA
          <tr>
            <td><i class="fa fa-users w3-text-yellow w3-large"></i></td>
            <td>New record, over 40 users.</td>
            <td><i>17 mins</i></td>
          </tr>
          <tr>
            <td><i class="fa fa-comment w3-text-red w3-large"></i></td>
            <td>New comments.</td>
            <td><i>25 mins</i></td>
          </tr>
          <tr>
            <td><i class="fa fa-bookmark w3-text-blue w3-large"></i></td>
            <td>Check transactions.</td>
            <td><i>28 mins</i></td>
          </tr>
          <tr>
            <td><i class="fa fa-laptop w3-text-red w3-large"></i></td>
            <td>CPU overload.</td>
            <td><i>35 mins</i></td>
          </tr>
          <tr>
            <td><i class="fa fa-share-alt w3-text-green w3-large"></i></td>
            <td>New shares.</td>
            <td><i>39 mins</i></td>
          </tr>
          ------------------------------------------------------------------------------FINE LISTA FEEDS
          -->
        </table>
      </div>
    </div>
  </div>
  <hr>
  <!-- -------------------------------------------- BARRE DI COMPLETAMENTO, STATS
  <div class="w3-container">
    <h5>General Stats</h5>
    <p>New Visitors</p>
    <div class="w3-grey">
      <div class="w3-container w3-center w3-padding w3-green" style="width:25%">+25%</div>
    </div>

    <p>New Users</p>
    <div class="w3-grey">
      <div class="w3-container w3-center w3-padding w3-orange" style="width:50%">50%</div>
    </div>

    <p>Bounce Rate</p>
    <div class="w3-grey">
      <div class="w3-container w3-center w3-padding w3-red" style="width:75%">75%</div>
    </div>
  </div>
  <hr>
  ------------------------------------------------------------------------------------------- BARRE STATS
  -->

  <!-- -------------------------------------------------------------------------------------- HOVERABLE TABLE
  <div class="w3-container">
    <h5>Countries</h5>
    <table class="w3-table w3-striped w3-bordered w3-border w3-hoverable w3-white">
      <tr>
        <td>United States</td>
        <td>65%</td>
      </tr>
      <tr>
        <td>UK</td>
        <td>15.7%</td>
      </tr>
      <tr>
        <td>Russia</td>
        <td>5.6%</td>
      </tr>
      <tr>
        <td>Spain</td>
        <td>2.1%</td>
      </tr>
      <tr>
        <td>India</td>
        <td>1.9%</td>
      </tr>
      <tr>
        <td>France</td>
        <td>1.5%</td>
      </tr>
    </table><br>
    <button class="w3-button w3-dark-grey">More Countries  <i class="fa fa-arrow-right"></i></button>
  </div>
  -------------------------------------------------------------------------------------------------------HOVERABLE TABLE
  -->
  
  <!-- ---------------------------------------------------------------------------------------------------------------- RECENT USERS
  <hr>
  <div class="w3-container">
    <h5>Recent Users</h5>
    <ul class="w3-ul w3-card-4 w3-white">
      <li class="w3-padding-16">
        <img src="/w3images/avatar2.png" class="w3-left w3-circle w3-margin-right" style="width:35px">
        <span class="w3-xlarge">Mike</span><br>
      </li>
      <li class="w3-padding-16">
        <img src="/w3images/avatar5.png" class="w3-left w3-circle w3-margin-right" style="width:35px">
        <span class="w3-xlarge">Jill</span><br>
      </li>
      <li class="w3-padding-16">
        <img src="/w3images/avatar6.png" class="w3-left w3-circle w3-margin-right" style="width:35px">
        <span class="w3-xlarge">Jane</span><br>
      </li>
    </ul>
  </div>
  <hr>
  -------------------------------------------------------------------------------------------- NEW USERS
  -->

	<!------------------------------------------------------------------------------------------ RECENT COMMENTS
  <div class="w3-container">
    <h5>Recent Comments</h5>
    <div class="w3-row">
      <div class="w3-col m2 text-center">
        <img class="w3-circle" src="/w3images/avatar3.png" style="width:96px;height:96px">
      </div>
      <div class="w3-col m10 w3-container">
        <h4>John <span class="w3-opacity w3-medium">Sep 29, 2014, 9:12 PM</span></h4>
        <p>Keep up the GREAT work! I am cheering for you!! Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><br>
      </div>
    </div>

    <div class="w3-row">
      <div class="w3-col m2 text-center">
        <img class="w3-circle" src="/w3images/avatar1.png" style="width:96px;height:96px">
      </div>
      <div class="w3-col m10 w3-container">
        <h4>Bo <span class="w3-opacity w3-medium">Sep 28, 2014, 10:15 PM</span></h4>
        <p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><br>
      </div>
    </div>
  </div>
  ------------------------------------------------------------------------------------ RECENT COMMENTS
  -->
  
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

