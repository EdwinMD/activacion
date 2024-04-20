<?php
include "0.conexionAWS.php";
include "conmk.php";

date_default_timezone_set('UTC');
$fechaconsulta = date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s') . "- 3 minute"));
echo "fecha:" . $fechaconsulta . "\n ";

$direccionesIP = ["Cortado"];  //186.46.33.179
$condiciones = implode("', '", $direccionesIP);

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
                    WHERE
                    update_date >= (NOW() AT TIME ZONE 'UTC' - INTERVAL '120 hours')
                    GROUP BY
                    odoo_subscription_name,username
                    HAVING
                    MAX(CASE WHEN attribute = 'subscription_stage' THEN value END) IN ('$condiciones')
                    ORDER BY
                    MAX(update_date) desc";
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

    if ($servidor <> '187.61.95.210' || $servidor <> '45.230.241.126' || $servidor <> '187.61.94.22' || $servidor <> '187.61.94.18') {
        echo $contrato;
        enviarmk($contrato,$usuario,$contraseña,$ip,$anchobanda,$prioridad,$estadoContrato,$cliente,$tipo_plan,$tipo_cliente,$suc,$servidor);
        echo "\n";
    }
}

function enviarmk2($contrato,$usuario,$contraseña,$ip,$anchobanda,$estadoContrato,$estadoHistorial,$cliente,$suc,$fecha,$servidor){
    require_once  '/var/www/html/activacion/mikrotics/crearusuario.php';
    require_once  '/var/www/html/activacion/mikrotics/crearfirewall.php';
    require_once  '/var/www/html/activacion/mikrotics/crearcolas.php';
    $ser_ip = $servidor;
    // $ser_ip = "187.61.94.146";
    $ser_usuario = "Tele@Lf@CoM";
    $ser_pass = "Alf@neT2024.**";
    $ser_puerto = 18728;
    $service = "pppoe";
    $profile = "default";
    $usuario = $usuario."@alfa";
    echo $usuario;
    $contraseña = $contraseña;
    $ip = $ip;
    $octetos = explode('.', $ip);
    $octetos[3] = '1'; // Cambiar el último octeto
    $localaddres = implode('.', $octetos); // Reconstruir la IP
    $estado = $estadoContrato;
 
    

    crearusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$contraseña,$service,$profile,$ip,$localaddres);
    //buscarusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
    //deshabilitarusuariopppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
    //buscaryeliminarusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);

    if($estadoContrato == 'Activo'){
        $estado = "activo";
    }else{
        $estado = "cortado";
    }

    crearfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$estado,$ip);
    //buscarfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$ip);


    //crearfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$ip,$estado);
}
