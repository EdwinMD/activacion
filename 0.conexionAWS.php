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
        $conAWS = pg_connect("host=pruebas-alfanet1.cjrqfpisjtqx.us-east-1.rds.amazonaws.com dbname=radiusintegration user=odoo password=odoo") 
        or die("Error al conectar: ".pg_last_error());
        echo 'Connected successfully';
        //mysql_select_db("asteriskcdrdb",$conexion);
       //mysql_query("SET NAMES 'utf8'"); 

?>

