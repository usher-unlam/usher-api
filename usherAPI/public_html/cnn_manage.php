<?php
require_once 'db_connect.php';
$fullOutput = false;

$link = conectar();
if(isset($_GET['id']){
  $id = $_GET['id'];
  }
  else{
    echo "Error: No se especific� un ID"; 
  }

if(isset($_GET['status']){
  $status = $_GET['status'];
  }
  else{
    echo "Error: No se especific� un status"; 
  }
  
$status = $_GET['status'];
$sqlQuery = "UPDATE camserver SET status = "
      . $status
      . " WHERE id = "
      . $id; 
/*$sqlQuery = "SELECT * FROM estado_banca ORDER BY id DESC LIMIT 1";
$sqlQuery = "SELECT camserver, camserver as id, tstamp as time, estadoUbicaciones as estado "
			. "FROM estado "
			. "WHERE camserver = 'MANUAL' or tstamp > DATE_SUB(CURDATE(), INTERVAL 1 DAY) "
			. "ORDER BY prioridad ASC, time DESC";*/
//$resultado = mysql_query($sqlQuery, $link);
if (!$resultado = $link->query($sqlQuery)) {
    echo "Lo sentimos, este sitio web est� experimentando problemas.";
	
    // De nuevo, no hacer esto en un sitio público, aunque nosotros mostraremos
	// cómo obtener información del error
	if ($fullOutput) {
		echo "Error: La ejecución de la consulta falló debido a: \n";
		echo "Query: " . $sqlQuery . "\n";
		echo "Errno: " . $link->errno . "\n";
		echo "Error: " . $link->error . "\n";
	}
    exit;
}
$rowTot = $resultado->num_rows;
if ($rowTot === 0) {
	echo "Lo sentimos. No se pudo encontrar datos. Inténtelo de nuevo.";
    exit;
}
$rows = $resultado->fetch_all(MYSQLI_ASSOC);
$UBI_ESC_CHAR = "_";
$ubi = 0; $ubiTot = strlen($rows[0]["estado"]);
$est = str_repeat("0",$ubiTot);
$seleccion = '';
for ($ubi = 0; $ubi < $ubiTot; $ubi++) {
	$seleccion .= $ubi . "->\n";
	for ($row = 0; $row < $rowTot; $row++) {
		if (strlen($rows[$row]["estado"]) > $ubi) {
			$seleccion .= "  ->". $row . " ->". $rows[$row]["estado"][$ubi];
			if($rows[$row]["estado"][$ubi] !== $UBI_ESC_CHAR){
				$est[$ubi] = $rows[$row]["estado"][$ubi];
				$seleccion .= " *\n";
				break;
			}
			$seleccion .= "\n";
		}
	}
}
date_default_timezone_set("America/Argentina/Buenos_Aires"); 
$upDate = time() - (24 * 60 * 60);
foreach ($rows as $key => $row) {
	$upd =  strtotime($row["time"]);
	if($upDate < $upd)
	$upDate = $upd;
}

# Salida JSON de API
echo json_encode(array('update' => date('c',$upDate), 'estado' => $est ));

// Comparación: estados CamServer ordenados por prioridad = resultado
if ($fullOutput) {
	echo '<pre>';
	echo "\n" . $sqlQuery . "\n";
	foreach ($rows as $key => $row) {
		echo "[" . $key . "] " . $row["estado"]. "\n";
	}
	echo "[=] " . $est. "\n\n";
	echo $seleccion;
	echo '</pre>';
}
/*while($row = $resultado->fetch_assoc()){
	//while($row=mysql_fetch_array($resultado)){
		echo "ID: " .$row['id']. "<br>Estado de bancas: " .$row['estado']. "<br>Marca de tiempo: " .$row['time']. "<br>";
		if ($regI == 0) // MANUAL
}*/ 

$resultado->free();
$link->close();

@