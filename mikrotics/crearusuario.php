<?php
require_once '/var/www/html/activacion/help/vendor/autoload.php';
require_once '/var/www/html/activacion/logs.php';

//$config = new \RouterOS\Config(['host' => '187.61.94.146', 'user' => 'sys77', 'pass' => 'conexalfa77', 'port' => 18728,]);


function buscarusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion){
        try {
            // Configurar las credenciales de conexión
            $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto, ]);
            $client = new \RouterOS\Client($config);
            $query = (new \RouterOS\Query('/ppp/secret/print'))
                ->where('name', $suscripcion); 
            $response = $client->query($query)->read();

            if (count($response) > 0) {
                $userId = $response[0]['.id'];
                echo "El .id del usuario 'edwin' es: " . $userId;
            } else {
                echo "El usuario 'edwin' no existe.";
            }
            
        } catch (\Exception $e) {
            echo 'Unable to connect to the router with error: ' . $e->getMessage();
        }
}

function crearusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion,$password,$service,$profile,$remoteaddress,$localaddres){
        try {
            $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto,]);
            $client = new \RouterOS\Client($config);
    
            $queryFind = (new \RouterOS\Query('/ppp/secret/print'))  
            ->where('name', $suscripcion);
            //->where('password', $password);
            $response = $client->query($queryFind)->read();

            $contador = 0;
            if (!empty($response)) {
                $contador++;
                if ($response['0']['remote-address'] == $remoteaddress && $response['0']['name'] == $suscripcion && $response['0']['password'] == $password) {
                        echo "\n No se ha ingresado el secret porque ya existe \n";
                        $mensaje = 'ya existe por tal motivo no se crea en el CCR';
                        $contador++;
                } 
            }
            if ($contador == 1) {
                if (count($response) > 0) {
                    $userId = $response[0]['.id'];
                    $queryRemove = (new \RouterOS\Query('/ppp/secret/remove'))
                        ->equal('.id', $userId);
                    $response = $client->query($queryRemove)->read();
                    echo "El usuario " . $suscripcion . " ha sido eliminado.";
                } else {
                    echo "El usuario " . $suscripcion . " no existe.";
                }
            }
            if ($contador == 0 || $contador == 1) {
                $query = (new \RouterOS\Query('/ppp/secret/add'))
                    ->equal('name', $suscripcion)
                    ->equal('password', $password)
                    ->equal('service', $service)
                    ->equal('profile', $profile)
                    ->equal('remote-address', $remoteaddress)
                    ->equal('local-address', $localaddres); // Reemplaza 'default' con el perfil PPPoE deseado si es necesario
                //$response = $client->query($query)->read();
                $n = 0;
                while ($n < 4) {
                    try {
                        $response = $client->query($query)->read();
                        if (isset($response['after']['ret'])) { break; }
                    } catch (\Throwable $th) {
                        error_log('Error en la consulta: ' . $th->getMessage());
                    }
                    //echo $n;
                    $n++;
                    echo $n;
                }
                print_r($response);
                if (empty($response)) {
                    $mensaje = 'No se obtuvo respuesta del CCR';
                } elseif (isset($response['after']['ret'])) {                       // Manejar la respuesta específica como *CE1
                    $mensaje = 'Respuesta del MikroTik: ' . $response['after']['ret'];
                } elseif (isset($response['after']['message'])) {                   // Manejar un mensaje de error
                    $mensaje = $response['after']['message'];
                } else { $mensaje = 'Respuesta desconocida';
                }
            }
            guardarlog($ser_ip, $suscripcion, $remoteaddress, $mensaje, 'insertar secret');
        } catch (\Exception $e) {
            echo 'Unable to connect to the router with error: ' . $e->getMessage();
            guardarlog($ser_ip, $suscripcion, $remoteaddress, $e->getMessage() , 'insertar secret');
        }
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

function deshabilitarusuariopppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion){
    try {
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto, ]);
        $client = new \RouterOS\Client($config);
        
        $queryFind = (new \RouterOS\Query('/ppp/secret/print'))
        ->where('name', $suscripcion);
        $response = $client->query($queryFind)->read();
        $queueId = $response[0]['.id'];
        echo $queueId;
        $queryDisable = (new \RouterOS\Query('/ppp/secret/set'))
                ->equal('.id', $queueId)
                ->equal('disabled', 'yes');
        $response = $client->query($queryDisable)->read();
        echo "el usuario ha sido deshabilitada.";
    } catch (\Exception $e) {
            echo 'Error al conectar con el router: ' . $e->getMessage();
    }
}