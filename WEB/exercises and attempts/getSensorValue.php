<html>
    <head>
        <title>Una pagina opensource </title>
    </head>
    <body>
    <?php
    $f = fopen("file.txt", "w");
    fwrite($f, $_POST["temperature"]."\n");
    fwrite($f, $_POST["humidity"]."\n");
    fwrite($f, $_POST["pressure"]."\n");
    fwrite($f, $_POST["year"]."\n");
    fwrite($f, $_POST["longitude"]);
    fclose($f);
    ?>
    <?php echo "Hello World! <p>"; ?>
    </body>
</html>