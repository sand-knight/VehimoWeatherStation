<html>
    <head>
        <title>Una pagina opensource </title>
    </head>
    <body>
    <?php
    $f = fopen("file.txt", "a");
    if(isset($_POST["temperature"])){
        fwrite($f, $_POST["temperature"]." | ");
        fwrite($f, $_POST["year"]." | ");
        fwrite($f, $_POST["longitude"]." | ");
        fwrite($f, $_POST["second"]." | ");
    }
    if(isset($_POST["memoryop"])){
        fwrite($f, $_POST["memoryop"] );
        
    }
    if(isset($_POST["distance"])){
        fwrite($f, $_POST["distance"]." | ");
        fwrite($f, $_POST["elapsed"]." | ");
        fwrite($f, $_POST["result"]." | ");
    }
    fclose($f);
    ?>
    </body>
</html>