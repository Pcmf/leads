<?php
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
    header("Pragma: no-cache"); // HTTP 1.0.
    header("Expires: 0"); // Proxies.
    date_default_timezone_set('Europe/Lisbon');
//Open connections to DB.
        $con = mysqli_connect("localhost","root","","sislead");
        if (!$con)
        {
            die('Could not connect: ' . mysql_error());
            echo'<h1>Nao ligou</h1>';
        }
//        mysql_select_db("sislead", $con);
                
        mysqli_query($con,"SET NAMES 'utf8'");
        mysqli_query($con,'SET character_set_connection=utf8');
        mysqli_query($con,'SET character_set_client=utf8');
        mysqli_query($con,'SET character_set_results=utf8');
        
?>
