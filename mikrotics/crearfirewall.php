<?php
require_once '/var/www/html/activacion/help/vendor/autoload.php';
require_once '/var/www/html/activacion/logs.php';
//$config = new \RouterOS\Config(['host' => '187.61.94.146', 'user' => 'sys77', 'pass' => 'conexalfa77', 'port' => 18728,]);


function buscarfirewall($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $suscripcion, $remoteaddress)
{
    try {
        // Configurar las credenciales de conexión
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
        $client = new \RouterOS\Client($config);
        $queryFind = (new \RouterOS\Query('/ip/firewall/address-list/print'))
            ->where('address', $remoteaddress)
            ->where('comment', $suscripcion);
        $response = $client->query($queryFind)->read();

        if (count($response) > 0) {
            $userId = $response[0]['.id'];
            echo "El .id del usuario " . $suscripcion . " es: " . $userId;
        } else {
            echo "El usuario " . $suscripcion . " no existe.";
        }
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
}

function crearfirewall($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $suscripcion, $estado, $remoteaddress)
{
    try {
        // Configurar las credenciales de conexión
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
        $client = new \RouterOS\Client($config);

        $queryFind = (new \RouterOS\Query('/ip/firewall/address-list/print'))
        ->where('comment', $suscripcion);
//        ->where('address', $remoteaddress);
        $response = $client->query($queryFind)->read();
        print_r($response);
        foreach ($response as $entry) {
            $queryRemove = (new \RouterOS\Query('/ip/firewall/address-list/remove'))
                ->equal('.id', $entry['.id']);
            $response = $client->query($queryRemove)->read();
        }
                        /*
                        $queryFind = (new \RouterOS\Query('/ip/firewall/address-list/print'))
                            //              ->where('address', $remoteaddress)
                            ->where('comment', $suscripcion);
                        $response = $client->query($queryFind)->read();
                        foreach ($response as $entry) {
                            $queryRemove = (new \RouterOS\Query('/ip/firewall/address-list/remove'))
                                ->equal('.id', $entry['.id']);
                            $response = $client->query($queryRemove)->read();
                        }
                        */
        $query = (new \RouterOS\Query('/ip/firewall/address-list/add'))
            ->equal('address', $remoteaddress) // Dirección o rango de direcciones
            ->equal('list', $estado) // Nombre de la lista de direcciones
            ->equal('comment', $suscripcion); // Comentario (opcional)
        // Ejecutar la consulta

        $n = 0;
        while ($n < 4) {
            try {
                $response = $client->query($query)->read();
                if (isset($response['0']['.id'])) {
                    break;
                }
            } catch (\Throwable $th) {
                error_log('Error en la consulta: ' . $th->getMessage());
            }
            $n++;
        }
        print_r($response);
        if (isset($response['0']['.id'])) {
            $mensaje = 'Operación realizada con éxito' . $response['0']['.id'] . ' ' . $response['0']['list'];
        } else {
            $mensaje = $estado . '     ' . $response['after']['message'];
        }



        guardarlog($ser_ip, $suscripcion, $remoteaddress, $mensaje, 'Addrest List');
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
        guardarlog($ser_ip, $suscripcion, $remoteaddress, $e->getMessage(), 'Addrest List');
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

function deshabilitarfirewall($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $suscripcion)
{
    try {
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
        $client = new \RouterOS\Client($config);

        $queryFind = (new \RouterOS\Query('/ip/firewall/address-list/print'))
            ->where('comment', $suscripcion);
        $response = $client->query($queryFind)->read();
        print_r($response);

        $queueId = $response[0]['.id'];
        echo $queueId;
        $queryDisable = (new \RouterOS\Query('/ip/firewall/address-list/set'))
            ->equal('.id', $queueId)
            ->equal('disabled', 'yes');
        $response = $client->query($queryDisable)->read();
        echo "la Lista ha sido deshabilitada.";
    } catch (\Exception $e) {
        echo 'Error al conectar con el router: ' . $e->getMessage();
    }
}
