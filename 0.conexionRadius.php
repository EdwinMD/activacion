<?php  // generar 

/*
host:           localhost
port:           3306
database:       rd
usuario:        root
password:       IiSbEa@alfanet1311
*/

        $conRad = mysqli_connect("localhost","radius","Cables.Alfa2021**","activacion"); 
        if (!$conRad) {
                die("Error al conectar a la base de datos: " . mysqli_connect_error());
            }
            echo 'Connected successfully';


?>
