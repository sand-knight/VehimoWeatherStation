<?php

 require ('vendor/autoload.php');
 use PhpMqtt\Client\MqttClient;
 use PhpMqtt\Client\ConnectionSettings;
 
 $server="localhost";
 $port="1883";
 $user="webserver";
 $passw="unapasswordsicura";
 

 $mqtt = new MqttClient($server, $port, "dajedajedaje");
 $connectionSettings = (new ConnectionSettings)
        ->setUsername("webserver")
        ->setPassword("unapasswordsicura");
 $mqtt->connect($connectionSettings, true);
 $mqtt->subscribe('Climate', function ($topic, $message){
	 
    $arr = [];
    $fields=explode('&', $message);
    foreach ($fields as $item){
      
      $parts = explode("=", $item);
      
      if( isset($parts[0]) && isset($parts[1]) ){
      
		$arr[$parts[0]] = $parts[1];
	  }
	  
    }
    
    if ( count($arr, 1) < 12){
		echo "Missing values: .".count($arr, 1)."\n";
		die ("No NULL values allowed");
	}
    
    
    $datetime=$arr["year"].'-'.$arr["month"].'-'.$arr["day"].'_'.$arr["hour"].'-'.$arr["minute"].'-'.$arr["second"];
    
    $inserquery="INSERT INTO `Climate` (`device_id`, `Latitude`, `Longitude`, `Timestamp`, `Temperature`, `Pressure`, `Humidity`) VALUES ('";
    $inserquery.=$arr["device_id"]."', '".$arr["latitude"]."', '".$arr["longitude"]."', STR_TO_DATE('";
    $inserquery.=$datetime."', '%Y-%m-%d_%H-%i-%s'), '".$arr["temperature"]."', '".$arr["pressure"]."', '".$arr["humidity"]."')";
    
    $conn = mysqli_connect("localhost:3306", "webserver", "passwordsicura", "VehiMoWS");
         
    if (!$conn) {
        echo "Connection failed";
        die("Connection failed: " . mysqli_connect_error());
    }
 
            

    $result=mysqli_query($conn, $inserquery);
    if($result==false) {
		echo "Error with the query";
		die("Result did not make to db".mysqli_connect_error());
	}
    mysqli_close($conn);
    echo $inserquery;
    echo "\nDatabase response was: ".$result."\n";
  }, 0);

 $mqtt->loop(true); 

?>
