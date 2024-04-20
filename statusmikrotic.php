<?php
require_once '/var/www/html/activacion/help/vendor/autoload.php';
include "conexionLocalhost.php";
include "0.conexionODOO.php";

//$ser_ip = "187.61.92.2";
$ser_usuario = "Tele@Lf@CoM";
$ser_pass = "Alf@neT2024.**";
$ser_puerto = 18728;

//$colas = obtenercolas($ser_ip, $ser_usuario, $ser_pass, $ser_puerto);
//print_r($colas);

$ins1 = "DELETE FROM secrets";
if ($conRad->query($ins1)) {
    printf("Se ha Eliminado Todos los registros de Secrets: %d", $conRad->insert_id);
} else {
    printf("Error al eliminar: %s", mysqli_error($conRad));
}
$ins1 = "DELETE FROM firewalls";
if ($conRad->query($ins1)) {
    printf("Se ha Eliminado Todos los registros de Firewalls: %d", $conRad->insert_id);
} else {
    printf("Error al eliminar: %s", mysqli_error($conRad));
}
$ins1 = "DELETE FROM colas";
if ($conRad->query($ins1)) {
    printf("Se ha Eliminado Todos los registros de Firewalls: %d", $conRad->insert_id);
} else {
    printf("Error al eliminar: %s", mysqli_error($conRad));
}

$sentencia = "select cs.name as servidor, cs.server_ssh_ip as ip, cs.server_ssh_user as usuario, cs.server_ssh_key as pass,
                cs.x_studio_tipo_servidor as tipo, cs.x_studio_sucursal as sucursal
                from core_server cs 
                where cs.active is true and cs.x_studio_tipo_servidor <> 'BRASS'
                order by cs.x_studio_tipo_servidor asc ";
$resultado = pg_query($sentencia);

if ($resultado) {
    while ($fila = pg_fetch_assoc($resultado)) {
        $ser_ip = $fila['ip'];
        $servidor = $fila['servidor'];
        $ser_usuario = $fila['usuario'];
        $ser_pass = $fila['pass'];
        $sucursal = $fila['sucursal'];

        $secrets = obtenersecrets($ser_ip, $ser_usuario, $ser_pass, $ser_puerto);
        //print_r($secrets);
        foreach ($secrets as $row) {
            echo $id = $row[".id"];
            $usuario = $row["name"];
            $partes = explode("@", $usuario);
            $usuariosinalfa = $partes[0];
            #echo $usuariosinalfa=substr($usuario,0,4);
            $service = $row["service"];
            $contraseña = $row["password"];
            $ip = $row["remote-address"];
            $disable = $row["disabled"];

            date_default_timezone_set('America/Guayaquil');
            $fecha = date("Y-m-d H:i:s");

            $ins = "INSERT INTO secrets (sucursal,servidor,name,service,pass,ip,disabled,fecha) 
                            Values ('$sucursal','$ser_ip','$usuario','$service','$contraseña','$ip','$disable','$fecha')";
            if ($conRad->query($ins)) {
                printf("Se ha creado Nuevo - ID: %d", $conRad->insert_id);
            } else {
                printf("Error al insertar: %s", mysqli_error($conRad));
            }

            $sentencia = "SELECT ss.code, sss.name AS estado
                FROM sale_subscription ss
                LEFT JOIN sale_subscription_stage sss ON ss.stage_id = sss.id
                WHERE ss.active is True AND ss.code = '$usuariosinalfa'";
            $resul = pg_query($sentencia);
            if($resul){
                while($fila=pg_fetch_assoc($resul)){
                    echo "este es el contrato en odoo  ". $fila['code'] . "  este es el estado". $fila['estado']; 
                    if($fila['estado'] == "Terminado"){
                        echo "se elimina este registro \n";
                        buscaryeliminarusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
                        buscaryeliminarfirewall($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $usuario, $ip);
                        if($tipo_plan =='Radio'){
                            buscaryeliminarcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuariosinalfa);
                        }
                    }
                }
            }
        }

        $firewall = obtenerfirewalls($ser_ip, $ser_usuario, $ser_pass, $ser_puerto);
        //print_r($data);
        foreach ($firewall as $row) {
            $id = $row[".id"];
            $list = $row["list"];
            $ip = $row["address"];
            $disable = $row["disabled"];
            $usuario = $row["comment"];

            date_default_timezone_set('America/Guayaquil');
            $fecha = date("Y-m-d H:i:s");

            $ins = "INSERT INTO firewalls(sucursal,servidor,comment,list,ip,disabled,fecha) 
                            Values ('$sucursal','$ser_ip','$usuario','$list','$ip','$disable','$fecha')";
            if ($conRad->query($ins)) {
                printf("Se ha creado Nuevo - ID: %d", $conRad->insert_id);
            } else {
                printf("Error al insertar: %s", mysqli_error($conRad));
            }
        }

        $colas = obtenercolas($ser_ip, $ser_usuario, $ser_pass, $ser_puerto);
        //print_r($colas);
        foreach ($colas as $row) {
            $id = $row[".id"];    
            $name = $row["name"];
            $priority = $row["priority"];
            $anchobanda = $row["max-limit"];
            $comment = $row["comment"];
            $disabled = $row["disabled"];

            date_default_timezone_set('America/Guayaquil');
            $fecha = date("Y-m-d H:i:s");

            $ins = "INSERT INTO colas(sucursal,servidor,comment,name,priority,anchobanda,disabled,fecha) 
                            Values ('$sucursal','$ser_ip','$comment','$name','$priority','$anchobanda','$disabled','$fecha')";
            if ($conRad->query($ins)) {
                printf("Se ha creado Nuevo - ID: %d", $conRad->insert_id);
            } else {
                printf("Error al insertar: %s", mysqli_error($conRad));
            }
        }

    }
}



function obtenersecrets($ser_ip, $ser_usuario, $ser_pass, $ser_puerto)
{
    try {
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
        $client = new \RouterOS\Client($config);
        $queryFind = (new \RouterOS\Query('/ppp/secret/print'));
        $response = $client->query($queryFind)->read();
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
    return $response;
}
function obtenerfirewalls($ser_ip, $ser_usuario, $ser_pass, $ser_puerto)
{
    try {
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
        $client = new \RouterOS\Client($config);

        $queryFind = (new \RouterOS\Query('/ip/firewall/address-list/print'));
        $response = $client->query($queryFind)->read();
        //print_r($response);
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
    return $response;
}
function obtenercolas($ser_ip, $ser_usuario, $ser_pass, $ser_puerto)
{
    try {
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
        $client = new \RouterOS\Client($config);
        $queryFind = (new \RouterOS\Query('/queue/simple/print'));
        $response = $client->query($queryFind)->read();
        //print_r($response);
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
    return $response;
}

function buscaryeliminarusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion){
    try {
        // Configurar las credenciales de conexión
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto, ]);
        $client = new \RouterOS\Client($config);

        $queryFind = (new \RouterOS\Query('/ppp/secret/print'))
        ->where('name', $suscripcion);
        $response = $client->query($queryFind)->read();

        if (count($response) > 0) {
            $userId = $response[0]['.id'];
    
            // Crear una consulta para eliminar el usuario
            $queryRemove = (new \RouterOS\Query('/ppp/secret/remove'))
                ->equal('.id', $userId);
            // Ejecutar la consulta para eliminar el usuario
            $response = $client->query($queryRemove)->read();
    
            echo "El usuario ". $suscripcion ." ha sido eliminado.";
        } else {
            echo "El usuario ". $suscripcion ." no existe.";
        }
        
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
}

function buscaryeliminarfirewall($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $suscripcion, $remoteaddress)
{
    try {
        // Configurar las credenciales de conexión
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
        $client = new \RouterOS\Client($config);

        $queryFind = (new \RouterOS\Query('/ip/firewall/address-list/print'))
            ->where('address', $remoteaddress)
            ->where('comment', $suscripcion);
        $response = $client->query($queryFind)->read();
        print_r($response);

        if (count($response) > 0) {
            $userId = $response[0]['.id'];
            // Crear una consulta para eliminar el usuario
            $queryRemove = (new \RouterOS\Query('/ip/firewall/address-list/remove'))
                ->equal('.id', $userId);
            // Ejecutar la consulta para eliminar el usuario
            $response = $client->query($queryRemove)->read();
            echo "El firewall ". $suscripcion ." ha sido eliminado.";
        } else {
            echo "El firewall ". $suscripcion ." no existe.";
        }


    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
}

function buscaryeliminarcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion){
    try {
        // Configurar las credenciales de conexión
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto, ]);
        $client = new \RouterOS\Client($config);
        $queryFind = (new \RouterOS\Query('/queue/simple/print'))
        ->where('name', $suscripcion);
        $response = $client->query($queryFind)->read();
        print_r($response);
    
        foreach ($response as $entry) {
            $queryRemove = (new \RouterOS\Query('/queue/simple/remove'))
                ->equal('.id', $entry['.id']);
                $response  = $client->query($queryRemove)->read();
                //print_r($response);
        }
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
}


/*
---------------------------------------------SECRETS---------------------------
    [857] => Array
        (
            [.id] => *C2A
            [name] => 2005747@alfa
            [service] => pppoe
            [caller-id] =>
            [password] => 1309875134
            [profile] => default
            [local-address] => 10.205.74.1
            [remote-address] => 10.205.74.160
            [routes] =>
            [ipv6-routes] =>
            [limit-bytes-in] => 0
            [limit-bytes-out] => 0
            [last-logged-out] => feb/05/2024 13:47:44
            [last-caller-id] => B0:A7:B9:9E:E2:E3
            [last-disconnect-reason] => hung-up
            [disabled] => false
        )



----------------------------------------FIREWALLS -----

    [937] => Array
        (
            [.id] => *1C63E
            [list] => activo
            [address] => 10.205.73.178
            [creation-time] => feb/06/2024 18:53:00
            [dynamic] => false
            [disabled] => false
            [comment] => 108234@alfa
        )
------------------------------------COLAS -----------------

    [257] => Array
        (
            [.id] => *6F3
            [name] => 25869
            [target] => 10.16.97.102/32
            [parent] => none
            [packet-marks] =>
            [priority] => 8/8
            [queue] => default-small/default-small
            [limit-at] => 0/0
            [max-limit] => 25000000/25000000
            [burst-limit] => 0/0
            [burst-threshold] => 0/0
            [burst-time] => 0s/0s
            [bucket-size] => 0.1/0.1
            [bytes] => 38105527/466624487
            [total-bytes] => 0
            [packets] => 196706/378448
            [total-packets] => 0
            [dropped] => 0/1185
            [total-dropped] => 0
            [rate] => 0/320
            [total-rate] => 0
            [packet-rate] => 0/0
            [total-packet-rate] => 0
            [queued-packets] => 0/0
            [total-queued-packets] => 0
            [queued-bytes] => 0/0
            [total-queued-bytes] => 0
            [invalid] => false
            [dynamic] => false
            [disabled] => false
            [comment] => MORA MORAN OMAR ELEUTERIO
        )
            ->equal('name', $suscripcion)
            ->equal('target', $remoteaddress)
            ->equal('max-limit', $bw) // 1M/1M es un ejemplo para límite máximo de subida/bajada
            ->equal('priority', $prioridad)
            ->equal('comment', $cliente);

        */
