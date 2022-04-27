<html>
    <head>
        <title>Una stazione meteo opensource </title>
    </head>
    <body>
        <H1> Group 14's HOTNESS </H1>
        <H3>
        <?php $f = fopen("file.txt", "r");
        $string = fread($f, 10);
        fclose($f);
        $Data = explode ("\n", $string);
        echo "Temperature: ".$Data[0]."\n"."Humidity: ".$Data[1]."\n"."Pressure: ".$Data[2] ;
        ?>
        </H3>
    </body>
</html>