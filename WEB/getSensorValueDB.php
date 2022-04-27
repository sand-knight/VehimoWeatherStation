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
 
             
            $result=mysqli_query($conn, "SELECT * FROM Climate");

            if (mysqli_num_rows($result)>0){
        ?>

        <table>
            <tr>

                <th> Latutude </th>
                <th> Longitude </th>
                <th> TimeStamp </th>
                <th> Temperature </th>
                <th> Pressure </th>
                <th> Humidity </th>
            </tr>
        <?php
                //we're into the if
                while ( $row=mysqli_fetch_array($result)){        

                    echo "<td>".$row["Latitude"]."</td>";
                    echo "<td>".$row["Longitude"]."</td>";
                    echo "<td>".$row["Datetime"]."</td>";
                    echo "<td>".$row["Temperature"]."</td>";
                    echo "<td>".$row["Pressure"]."</td>";
                    echo "<td>".$row["Humidity"]."</td>"; 
                }
            }
            mysqli_close($conn);
    
        ?>
        </table>
    </body>
</html>