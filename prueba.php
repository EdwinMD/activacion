<?php

$contrato = "edwin2";
$usuario = "edwin3";
$contraseña = "edwin";
$ip = '192.168.106.103';
$anchobanda = '10M/10M';
$cliente = 'Edwin Morales';
$suc = 'SANTO DOMINGO';
$tipo_plan = 'Fibra';
$tipo_cliente = 'Residencial';
$servidor = '187.61.95.162';
$estadoContrato = 'Activo';
$prioridad = '8/8';


enviarmk($contrato, $usuario, $contraseña, $ip, $anchobanda, $prioridad, $estadoContrato, $cliente, $tipo_plan, $tipo_cliente, $suc, $servidor);


function enviarmk($contrato, $usuario, $contraseña, $ip, $anchobanda, $prioridad, $estadoContrato, $cliente, $tipo_plan, $tipo_cliente, $suc, $servidor)
{
    require_once '/var/www/html/activacion/mikrotics/crearusuario.php';
    require_once '/var/www/html/activacion/mikrotics/crearfirewall.php';
    require_once '/var/www/html/activacion/mikrotics/crearcolas.php';

    $ser_ip = (string) $servidor;
    $ser_usuario = "Tele@Lf@CoM";
    $ser_pass = "Alf@neT2024.**";
    $ser_puerto = 18728;
    $service = "pppoe";
    $profile = "default";
    $usuariosinpppoe = $usuario;
    $usuario = $usuario . "@alfa";
    $contraseña = $contraseña;
    $ip = $ip;
    $octetos = explode('.', $ip);
    $octetos[3] = '1'; // Cambiar el último octeto
    $localaddres = implode('.', $octetos); // Reconstruir la IP
    $estado = $estadoContrato;
    $tipo_plan = $tipo_plan;
    $tipo_cliente = $tipo_cliente;

    if ($estadoContrato == 'Activo' || $estadoContrato == 'Nuevo') {
        $estado = "activo";
    } else {
        $estado = "cortado";
    }

    include "conexionLocalhost.php";
    date_default_timezone_set('America/Guayaquil');
    $fecha = date("Y-m-d H:i:s");
    
    #crearusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$contraseña,$service,$profile,$ip,$localaddres);
    #crearfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$estado,$ip);
    #buscaryeliminarusuarioppoe($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $usuario);
    #buscaryeliminarfirewall($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $usuario, $ip);
    #crearcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuariosinpppoe,$ip,$anchobanda,$prioridad,$cliente);
    buscaryeliminarcolas($ser_ip, $ser_usuario, $ser_pass, $ser_puerto, $usuariosinpppoe);

}
