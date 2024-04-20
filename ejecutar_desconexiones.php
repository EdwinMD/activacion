<?php
include "0.conexionRadius.php";
//extraigo todos los items de la tabla tareas para enviar a desconectar
$ins1 = "SELECT usuario, sucursal, servidor_ant, estado, ip_ant, dominio_ant, dominio, fecha
          FROM tareas
          LIMIT 50";
$resul = $conRad->query($ins1);

print_r($resul);
// verifico si existe alguna tarea si no existe salgo de todo.
if ($resul->num_rows == 0) {
  echo "no existe tarea para ejecutar";
} else {
  //si existe tarea  
  while ($tarea = $resul->fetch_assoc()) { // si existe una tarea empiezo a iterarlas
    $usuario = $tarea["usuario"];
    $sucursal = $tarea["sucursal"];
    $servidor = $tarea["servidor_ant"];
    $estado = $tarea["estado"];
    $ip = $tarea["ip_ant"];
    $dominio_ant = $tarea["dominio_ant"];
    $dominio = $tarea["dominio"];
    $fecha = (new DateTime($tarea["fecha"]))->format('Y-m-d H:i:s');

    $resultado = listado_servidores($tarea["servidor_ant"], $sucursal);     // traigo la informacion de los NAS del Radius o los BRASS
    if ($resultado->num_rows == 0) {
      eliminar_tarea($usuario, $fecha);
    } else {
      //Si Existe un Servidor o un Brass creado en el Radius   
      while ($it = $resultado->fetch_assoc()) {
        desconectar($it["NASIDENTIFIER"], $it["SECRET"], $usuario, $ip, $fecha);
      }
    }
  }
}

function listado_servidores($ip_servidor, $sucursal)
{
  include "0.conexionRadius.php";
  switch ($sucursal) {
    case 1:
      $suc = "BRASS-SD";
      break;
    case 2:
      $suc = "BRASS-EC";
      break;
    case 3:
      $suc = "BRASS-QV";
      break;
    case 4:
      $suc = "BRASS-PV";
      break;
    case 5:
      $suc = "BRASS-MT";
      break;
    case 7:
      $suc = "BRASS-CH";
      break;
    case 13:
      $suc = "BRASS-QT";
      break;
    default:
      echo "No existe ni el Servidor ni el BRASS";
  }
  $ins1 = "SELECT NASIDENTIFIER, SECRET, NASTYPE FROM RADCLIENTLIST WHERE NASIDENTIFIER = '$ip_servidor' OR NASTYPE = '$suc'";
  $resultado = $conRad->query($ins1);
  return $resultado;
}

function desconectar($nas, $password_nas, $usuario, $ip, $fecha)
{
  $NAS = $nas;
  $PASS_NAS = $password_nas;
  $PORT_NAS = "3799";
  $comando = "/opt/radiator/radiator/radpwtst -trace 4 -noauth -noacct -code Disconnect-Request -s " . $NAS . " -secret=" . $PASS_NAS . " -auth_port " . $PORT_NAS . " User-Name=" . $usuario . "@alfa" . " Framed-IP-Address=" . $ip;
  //echo $comando;
  exec($comando, $salida, $codigoSalida);
  eliminar_tarea($usuario, $fecha);
  echo "El código de salida fue: " . $codigoSalida;
  echo "\n";
  echo "Ahora imprimiré las líneas de salida:";
  foreach ($salida as $linea) {
    echo $linea;
    echo "\n";
  }

}

function eliminar_tarea($usuario, $fecha)
{
  include "0.conexionRadius.php";
  $ins1 = "DELETE FROM tareas WHERE usuario = '$usuario' AND fecha = '$fecha'";
  if ($conRad->query($ins1)) {
    printf("\n Se ha ELIMINADO correctamente.\n");
  } else {
    printf("\n Error al eliminar:.\n%s", mysqli_error($conRad));
  }
}

?>