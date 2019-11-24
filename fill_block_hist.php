<?php
require_once 'db_connect_web.php';

$link = conectar();

/*if(isset($_POST['session_id'])){
  $session = $_POST['session_id'];
}
else{
    echo "Error, no se especificó la sesión";
    exit;
}*/

if($link){
     $statement_sessions = mysqli_prepare($link, "SELECT session_id FROM `sessions` WHERE 1 ORDER BY session_id DESC LIMIT 1");
    
     if($statement_sessions){
         mysqli_stmt_execute($statement_sessions);
         mysqli_stmt_store_result($statement_sessions);
  			 mysqli_stmt_bind_result($statement_sessions, $session_id);
     }
     else{
         $response["error"] = "La consulta de sesiones no fue ejecutada";
     }
     
     //Hago el fetch para guardar el session_id de la consulta anterior
     mysqli_stmt_fetch($statement_sessions);
}
else{
    echo "No fue posible obtener el último session_id";
}     

if($link){
		$statement = mysqli_prepare($link, "SELECT camserver, prioridad, tstamp, estadoUbicaciones
    									FROM status
											WHERE camserver LIKE 'SVR1'
                      AND session_id = ?
                      ORDER BY tstamp DESC");
                      //LIMIT 2");
    mysqli_stmt_bind_param($statement, "s", $session_id);
		//Revisar esta lógica para que no traiga un registro correspondiente a la sesión anterior (validar por fecha en la query)
      
		if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
			mysqli_stmt_bind_result($statement, $camserver, $priority, $tstamp, $estadoUbicaciones);
    }
    else{
       $response["error"] = "La consulta de estados no fue ejecutada";
    }
    
    $statement_benchs = mysqli_prepare($link, "SELECT number, associated_member_id, associated_block_id FROM benchs");
      
    if($statement_benchs){		
			  mysqli_stmt_execute($statement_benchs);
			  mysqli_stmt_store_result($statement_benchs);
			  mysqli_stmt_bind_result($statement_benchs, $number, $associated_member, $associated_block);
    }
    else{
        $response["error"] = "La consulta de bancas no fue ejecutada";
    }
}
else{
   $response["error"] = "No se estableció la conexión a la base de datos";
}

$response["succes"] = false;

//Cargo el array de bancas para poder hacer las asignaciones correspondientes desde el array en el próximo paso.
//Inicializo los contadores de totales y presentes para cada bloque.
While(mysqli_stmt_fetch($statement_benchs)){
  $bench_block[$number] = $associated_block;
  $bench_member[$number] = $associated_member;
  $blocks[$associated_block]["presents"] = 0;
  $blocks[$associated_block]["total"] = 0; 
}

//Hago un fetch para ver el registro más reciente de status (leí 2) y voy sumando en un array usando de índice associated_block los presentes y los totales de cada bloque
//Luego minutes se calcula haciendo la diferencia entre el tstamp del registro más actual y el siguiente.
mysqli_stmt_fetch($statement);
$record_time = date_create_from_format('Y-m-d H:i:s', $tstamp);

//Las bancas arrancan en 1... como el índice de $bench_block son las bancas, debo arrancar el for en 1. 
for($j = 1; $j <= strlen($estadoUbicaciones); $j++){ 
  $blocks[$bench_block[$j]]["presents"] += intval($estadoUbicaciones[$j-1]);
  $blocks[$bench_block[$j]]["total"]++;
}

//Hago un segundo fetch para leer el registro de estado inmediatamente anterior y calcular la diferencia de tiempo entre ambos. Si es el primer registro que leo va a dar false la lectura.
//Si es el primer registro le cargo '0'.
/*if(mysqli_stmt_fetch($statement)){
  $record_time = $record_time->diff(date_create_from_format('Y-m-d H:i:s',$tstamp));
  $time = $record_time->s;
  echo "Diferencia de minutos entre lecturas: " .$record_time->i. "\n";
}
else{
  $time = 0; 
}*/

//Leo el resto de las filas de status para saber la cantidad y multiplicar por el tiempo de sync. Así se cuántos minutos transcurrieron desde el inicio de la
//sesión hasta el registro actual.
$reg_quant = 1;
While(mysqli_stmt_fetch($statement)){
  $reg_quant++;
}

//Hago un select a la  tabla cronserver para saber cada cuánto está sincronizando los estados
if($link){
  $statement_cronserver = mysqli_prepare($link, "SELECT config
    									FROM cronserver
											WHERE id LIKE 'SYNC1'");
      
		if($statement_cronserver){		
			mysqli_stmt_execute($statement_cronserver);
			mysqli_stmt_store_result($statement_cronserver);
			mysqli_stmt_bind_result($statement_cronserver, $config);
    }
    else{
       $response["error"] = "La consulta de configuración de cronserver ejecutada";
    }

//Decodifico lo leido de la tabla cronserver y tomo la info correspondiente a la frecuencia de sync. Lo divido por 60 para obtener la expresión en minutos (está guardado en segundos).
mysqli_stmt_fetch($statement_cronserver);
$json_config = json_decode($config, true);
$fill_block_frec = $json_config['fill_block_frec']/60;

//Multiplico la cantidad de registros leidos por la frecuencia de sincronizacion.
$elapsed_time = $reg_quant*$fill_block_frec;
echo "Cantidad de registros: " .$reg_quant. " - Frecuencia de sync: " .$fill_block_frec. " - Tiempo transcurrido: " .$elapsed_time. "\n";
}

//Acá preparo el insert
$insertions = 0;
echo "Estadisticas de sesion: " .$session_id. "\n";
//Recorro el array de bloques y realizando los inserts.
foreach($blocks as $block_id => $block_info){
  //print_r($block_id);
  //echo "Bloque numero: " .$block_id. " - Presentes: " .$block_info["presents"]. " - Total: " .$block_info["total"]. "\n";
  //}
    $statement_insert = mysqli_prepare($link, "INSERT INTO block_history (session_id, block_id, minutes, presents, total) VALUES (?, ?, ?, ?, ?)");
    if($statement_insert){
      mysqli_stmt_bind_param($statement_insert, "sssss", $session_id, $block_id, $elapsed_time, $block_info["presents"], $block_info["total"]);
      if(mysqli_stmt_execute($statement_insert)){
        $insertions++;
        $response["succes"] = true;
      }
      else{
        $response["error"] = mysqli_error($link);
      }
    }
    else{
        $response["error"] = mysqli_error($link);
    }
}

$response["insertions"] = $insertions;
$benchs["succes"] = $response["succes"];
           
echo json_encode($response);
    
$link->close();
?>