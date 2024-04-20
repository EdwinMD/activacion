<?php

function guardarlog($servidor,$suscripcion,$ip,$mensaje,$api){
    include "conexionLocalhost.php";
    date_default_timezone_set('America/Guayaquil');
    $fecha = date("Y-m-d H:i:s");
    
    
    $ins = "INSERT INTO erroresmk (fecha,servidor,suscripcion,ip,error,api) VALUES ('$fecha','$servidor','$suscripcion','$ip','$mensaje','$api')";
    $result = $conRad->query($ins);
    
    if ($result) { echo "\n";printf("Se ha creado un nuevo registro en los BBDD LOGs con el ID: %d", $conRad->insert_id);
    } else { printf("Error al insertar el registro: %s", $conRad->error);  } 
}

function guardar_data_usuario($suscripcion,$estado,$ip,$servidor,$sucursal){
    include "conexionLocalhost.php";
    date_default_timezone_set('America/Guayaquil');
    $fecha = date("Y-m-d H:i:s");

    $ins1 = "SELECT suscripcion,estado,ip,servidor,sucursal FROM data_usuarios WHERE suscripcion = '$suscripcion'";
    $resul = $conRad->query($ins1);
    if ($resul->num_rows == 0) {
        $ins = "INSERT INTO data_usuarios (suscripcion,estado,ip,servidor,sucursal,fecha) VALUES ('$suscripcion','$estado','$ip','$servidor','$sucursal','$fecha')";
        $result = $conRad->query($ins);

        if ($result) { 
            printf("\n Se ha creado un data_usuario: %d", $conRad->insert_id);
        } else { 
            printf("\n Error al insertar un registro en data_usuario: %s", $conRad->error);  
        } 
    } else {
        $ins = "UPDATE data_usuarios SET 
              estado = '$estado',
              ip = '$ip',
              servidor = '$servidor',
              sucursal = '$sucursal',
              fecha='$fecha'
          WHERE suscripcion = '$suscripcion'";
        if ($conRad->query($ins)) {
            printf("\n Se ha ACTUALIZADO data_usuario- ID: %d", $conRad->insert_id);
        } else {
            printf("\n Error al actualizar: data_usuario %s", mysqli_error($conRad));
        }
    }
}

