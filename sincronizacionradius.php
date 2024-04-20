<?php
include "0.conexionISP.php";
include "0.conexionAWS.php";
include "0.conexionRadius.php";
include "consultarnas.php";
include "guardartarea.php";
include "conmk.php";

date_default_timezone_set('UTC');
$fechaconsulta = date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s') . "- 3 minute"));
echo "fecha:" . $fechaconsulta . "\n ";

$sentencia = "SELECT odoo_subscription_name AS suscripcion, username AS username,
                    MAX(CASE WHEN attribute = 'Cleartext-Password' THEN value END) AS \"Cleartext-Password\",
                    MAX(CASE WHEN attribute = 'Framed-IP-Address' THEN value END) AS \"Framed-IP-Address\",
                    MAX(CASE WHEN attribute = 'Radius-Group-ID' THEN value END) AS \"Radius-Group-ID\",
                    MAX(CASE WHEN attribute = 'branch_office' THEN value END) AS \"branch_office\",
                    MAX(CASE WHEN attribute = 'client_name' THEN value END) AS \"client_name\",
                    MAX(CASE WHEN attribute = 'plan_type' THEN value END) AS \"plan_type\",
                    MAX(CASE WHEN attribute = 'client_type' THEN value END) AS \"client_type\",
                    MAX(CASE WHEN attribute = 'radius_bandwidth' THEN value END) AS radius_bandwidth,
                    MAX(CASE WHEN attribute = 'radius_priority' THEN value END) AS radius_priority,
                    MAX(CASE WHEN attribute = 'subscription_stage' THEN value END) AS subscription_stage,
                    MAX(CASE WHEN attribute = 'server_ip' THEN value END) AS server_ip,
                    MAX(update_date AT TIME ZONE 'UTC' AT TIME ZONE 'America/Guayaquil') AS update_date,
                    MAX(update_date) AS update_date2
                    FROM
                    radius_subscription rs 
                    where 
                    update_date >= (NOW() AT TIME ZONE 'UTC' - INTERVAL '3 minutes') 
                    GROUP BY
                    odoo_subscription_name,username
                    ORDER BY
                    MAX(update_date)";
$resultado = pg_query($sentencia);

$resultadoArray = array();
while ($row = pg_fetch_assoc($resultado)) {
    $resultadoArray[] = $row;
}
print_r($resultadoArray);
//print_r($resultadoArray);

echo "-----ESTADO -------- USUARIO ----- PASSWORD --- IP -- GRUPO \n";
for ($x = 0; $x < count($resultadoArray); $x++) {

    $contrato = $resultadoArray[$x]['suscripcion'];
    $usuario = $resultadoArray[$x]['username'];
    $contraseña = $resultadoArray[$x]['Cleartext-Password'];
    $ip = $resultadoArray[$x]['Framed-IP-Address'];
    $anchobanda = $resultadoArray[$x]['radius_bandwidth']."M/".$resultadoArray[$x]['radius_bandwidth']."M";
    $prioridad = $resultadoArray[$x]['radius_priority'];
    $estadoContrato = $resultadoArray[$x]['subscription_stage'];
    $estadoHistorial = $resultadoArray[$x]['subscription_stage'];
    $cliente = $resultadoArray[$x]['client_name'];
    $tipo_plan = $resultadoArray[$x]['plan_type'];
    $tipo_cliente = $resultadoArray[$x]['client_type'];
    $suc = $resultadoArray[$x]['branch_office'];
    $fecha = (new DateTime($resultadoArray[$x]['update_date']))->format('Y-m-d H:i:s');
    $servidor = $resultadoArray[$x]['server_ip'];
    if ($estadoContrato == 'Cortado' || $estadoContrato == 'Suspendido'|| $estadoContrato == 'Terminado') {
        $realm = "alfacortados";
    } else {
        $realm = "alfa";
    }

    date_default_timezone_set('America/Guayaquil');
    $fecha_from = strtotime(date("Y-m-d H:i:s"));
    $fecha_to = strtotime(date("Y-m-d H:i:s") . "+ 5 years");

    //enviarmk($contrato,$usuario,$contraseña,$ip,$anchobanda,$prioridad,$estadoContrato,$cliente,$tipo_plan,$tipo_cliente,$suc,$servidor);
    //echo "\n";
    
    
    $ins1 = "SELECT USERNAME, PASS_WORD, STATICADDRESS,SERVICENAME,EMAIL FROM RADUSERS WHERE USERNAME = '$usuario'";
    $resul = $conRad->query($ins1);

    if ($resul->num_rows == 0) { // si el usuario no existe en el radius, inserta uno nuevo
            $ins = "INSERT INTO RADUSERS (USERNAME,PASS_WORD,STATICADDRESS,FULLNAME,ADDEDDATE,VALIDFROM,VALIDTO,SERVICENAME,EMAIL) 
                    Values ('$usuario','$contraseña','$ip','$cliente','$fecha_from','$fecha_from','$fecha_to','$realm','$servidor')";
            $conRad->query($ins);
            printf("Se ha creado Nuevo----", $conRad->insert_id);
            echo $estadoContrato . "  ";  echo $usuario . "  "; echo $contraseña . "  "; echo $ip . "  \n";
    } else { // si el usuario existe lo actualiza
        while ($it = $resul->fetch_assoc()) {
            if ($estadoContrato == 'Cortado' || $estadoContrato == 'Suspendido' || $estadoContrato == 'Terminado') {
              if ($it["EMAIL"] <> $servidor || $it["SERVICENAME"] <> $realm || 
                    $it["STATICADDRESS"] <> $ip || $it["USERNAME"] <> $usuario || $it["PASS_WORD"] <> $contraseña){

                $ins = "UPDATE RADUSERS SET USERNAME = '$usuario', PASS_WORD = '$contraseña', STATICADDRESS = '$ip',FULLNAME = '$cliente', 
                                            VALIDTO='$fecha_to', SERVICENAME = '$realm', EMAIL = '$servidor'
                    WHERE USERNAME = '$usuario' ";
                $conRad->query($ins);
                printf("El Contrato es Activo -- ", $conRad->insert_id);
                guardarT($usuario, $it["STATICADDRESS"],$suc,$estadoContrato,$fecha,$it["EMAIL"],$it["SERVICENAME"],$realm);
              }
            } elseif ($estadoContrato == 'Activo') {
                if ($it["EMAIL"] <> $servidor || $it["SERVICENAME"] <> $realm || 
                        $it["STATICADDRESS"] <> $ip || $it["USERNAME"] <> $usuario || $it["PASS_WORD"] <> $contraseña) {

                    guardarT($usuario, $it["STATICADDRESS"],$suc,$estadoContrato,$fecha,$it["EMAIL"],$it["SERVICENAME"],$realm);
                    $ins = "UPDATE RADUSERS SET USERNAME = '$usuario',PASS_WORD = '$contraseña', STATICADDRESS = '$ip',FULLNAME = '$cliente', 
                                                VALIDTO='$fecha_to', SERVICENAME = '$realm', EMAIL = '$servidor' 
                    WHERE USERNAME = '$usuario'";
                    $conRad->query($ins);
                    printf("El Contrato es Activo -- ", $conRad->insert_id);
                } 
                else {
                    printf("El Contrato es Activo -- NO HACE NADA", $conRad->insert_id);
                }
            } else {
                echo "no hace nada";
            }
            echo $estadoContrato . "  ";
            echo $usuario . "  ";
            echo $contraseña . "  ";
            echo $ip . "  \n";
        }
    }
}
