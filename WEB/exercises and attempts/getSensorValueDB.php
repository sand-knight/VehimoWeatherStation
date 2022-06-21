<html>
    <head>
        <title>Una pagina opensource </title>
    </head>
    <body>
        <?php



            $conn = mysqli_connect("localhost:3306", "giulio", "gennaro", "PHPGuerriero");
         
            if (!$conn) {
                echo "died";
                die("Connessione fallita: " . mysqli_connect_error());
            }
 
            $inserquery="INSERT INTO `Climate` (`Latitude`, `Longitude`, `Datetime`, `Temperature`, `Pressure`, `Humidity`) VALUES ('".$_POST["latitude"]."', '".$_POST["longitude"]."', '".$_POST["hour"].":".$_POST["minute"]."', '".$_POST["temperature"]."', '".$_POST["pressure"]."', '".$_POST["humidity"]."')";

            $result=mysqli_query($conn, $inserquery);
            if($result==false) echo mysqli_connect_error();
            mysqli_close($conn);
      
        ?>
        </table>
    </body>
</html>