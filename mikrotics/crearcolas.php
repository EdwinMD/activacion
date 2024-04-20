<?php
require_once '/var/www/html/activacion/help/vendor/autoload.php';
//$config = new \RouterOS\Config(['host' => '187.61.94.146', 'user' => 'sys77', 'pass' => 'conexalfa77', 'port' => 18728,]);


function buscarcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion){
    try {
        // Configurar las credenciales de conexión
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto, ]);
        $client = new \RouterOS\Client($config);
        $queryFind = (new \RouterOS\Query('/queue/simple/print'))
        ->where('name', $suscripcion);
        $response = $client->query($queryFind)->read();
        print_r($response);
        if (count($response) > 0) {
            $userId = $response[0]['.id'];
            echo "El .id de la cola es ". $suscripcion ." es: " . $userId;
        } else {
            echo "La cola es ". $suscripcion ." no existe.";
        }
    } catch (\Exception $e) {
        echo 'Unable to connect to the router with error: ' . $e->getMessage();
    }
}

function crearcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion,$remoteaddress,$bw,$prioridad,$cliente){
    try {
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
        }
        $query = (new \RouterOS\Query('/queue/simple/add'))
            ->equal('name', $suscripcion)
            ->equal('target', $remoteaddress)
            ->equal('max-limit', $bw) // 1M/1M es un ejemplo para límite máximo de subida/bajada
            ->equal('priority', $prioridad)
            ->equal('comment', $cliente);
        // Ejecutar la consulta
        $response = $client->query($query)->read();
        //print_r($response);    
        echo "La cola ha sido actualizada.";
    } catch (\Exception $e) {
        echo 'Error al conectar con el router: ' . $e->getMessage();
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

function deshabilitarcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$suscripcion){
    try {
        $config = new \RouterOS\Config(['host' => $ser_ip, 'user' => $ser_usuario, 'pass' => $ser_pass, 'port' => $ser_puerto, ]);
        $client = new \RouterOS\Client($config);
        
        $queryFind = (new \RouterOS\Query('/queue/simple/print'))
        ->where('name', $suscripcion);
        $response = $client->query($queryFind)->read();
        $queueId = $response[0]['.id'];
        echo $queueId;
        $queryDisable = (new \RouterOS\Query('/queue/simple/set'))
                ->equal('.id', $queueId)
                ->equal('disabled', 'yes');
        $response = $client->query($queryDisable)->read();
        echo "La cola ha sido deshabilitada.";
    } catch (\Exception $e) {
            echo 'Error al conectar con el router: ' . $e->getMessage();
    }
}