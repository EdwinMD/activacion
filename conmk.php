<?php
require_once '/var/www/html/activacion/logs.php';

function enviarmk($contrato,$usuario,$contraseña,$ip,$anchobanda,$prioridad,$estadoContrato,$cliente,$tipo_plan,$tipo_cliente,$suc,$servidor){
    require_once  '/var/www/html/activacion/mikrotics/crearusuario.php';
    require_once  '/var/www/html/activacion/mikrotics/crearfirewall.php';
    require_once  '/var/www/html/activacion/mikrotics/crearcolas.php';

    $ser_ip = (String) $servidor;
    $ser_usuario = "Tele@Lf@CoM";
    $ser_pass = "Alf@neT2024.**";
    $ser_puerto = 18728;
    $service = "pppoe";
    $profile = "default";
    $usuariosinpppoe = $usuario;
    $usuario = $usuario."@alfa";
    $contraseña = $contraseña;
    $ip = $ip;
        $octetos = explode('.', $ip);
        $octetos[3] = '1'; // Cambiar el último octeto
    $localaddres = implode('.', $octetos); // Reconstruir la IP
    $estado = $estadoContrato;
    $tipo_plan=$tipo_plan;
    $tipo_cliente=$tipo_cliente;
 
    if($estadoContrato == 'Activo'|| $estadoContrato == 'Nuevo'){
        $estado = "activo";
    }else{  $estado = "cortado"; }

    include "conexionLocalhost.php";
    date_default_timezone_set('America/Guayaquil');
    $fecha = date("Y-m-d H:i:s");

    $ins1 = "SELECT suscripcion,estado,ip,servidor,sucursal FROM data_usuarios WHERE suscripcion = '$suscripcion'";
    $resul = $conRad->query($ins1);
    if ($resul->num_rows == 0) {
        while ($it = $resul->fetch_assoc()) {
            if ($it["servidor"] <> $ser_ip ){
                $servidor_antiguo=$it["servidor"];
                buscaryeliminarusuarioppoe($servidor_antiguo,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
                buscaryeliminarfirewall($servidor_antiguo, $ser_usuario, $ser_pass, $ser_puerto, $usuario, $ip);
                if($tipo_plan =='Radio'){
                    buscaryeliminarcolas($servidor_antiguo,$ser_usuario,$ser_pass,$ser_puerto,$usuariosinpppoe);
                }
            }
        }
    }

    guardar_data_usuario($usuariosinpppoe,$estadoContrato,$ip,$ser_ip,$suc);

    if($tipo_cliente=='Residencial' && $tipo_plan =='Fibra'){
        crearusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$contraseña,$service,$profile,$ip,$localaddres);
                        //deshabilitarusuariopppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
        crearfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$estado,$ip);
                        //deshabilitarfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
                        
    }elseif($tipo_cliente=='Residencial' && $tipo_plan =='Radio'){
        crearfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$estado,$ip);
                        //deshabilitarfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
        crearcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuariosinpppoe,$ip,$anchobanda,$prioridad,$cliente);
                        //deshabilitarcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuariosinpppoe);

    }elseif($tipo_cliente == 'Corporativo' && $tipo_plan =='Fibra'){
        crearusuarioppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$contraseña,$service,$profile,$ip,$localaddres);
                        //deshabilitarusuariopppoe($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
        crearfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$estado,$ip);
                        //deshabilitarfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
                        
    }elseif($tipo_cliente == 'Corporativo' && $tipo_plan =='Radio'){
        crearfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario,$estado,$ip);
                        //deshabilitarfirewall($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuario);
        crearcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuariosinpppoe,$ip,$anchobanda,$prioridad,$cliente);
                        //deshabilitarcolas($ser_ip,$ser_usuario,$ser_pass,$ser_puerto,$usuariosinpppoe);
    }

}