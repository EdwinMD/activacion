<?php  // generar 

/*
AWS
host:           pruebas-alfanet1.cjrqfpisjtqx.us-east-1.rds.amazonaws.com
port:           5432
Dtabase:        radiusintegration
usuario:        odoo
contraseña:     odoo

*/

        //$conexion = mysql_connect("localhost","root","tuclave");
        $conAWS = pg_connect("host=trescloud-alfanet-15-0-4417468.dev.odoo.com dbname=trescloud-alfanet-15-0-4417468 user=e_trescloud_alfanet_15_0_4417468 password=72c2197fcd21b924fe2475e1d71e31") 
        or die("Error al conectar: ".pg_last_error());
        echo 'Connected successfully';
        //mysql_select_db("asteriskcdrdb",$conexion);
       //mysql_query("SET NAMES 'utf8'"); 

?>
