<?php
require_once 'db_connect_web.php';

$link = conectar();

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
                      ORDER BY tstamp DESC
                      LIMIT 1");
    mysqli_stmt_bind_param($statement, "s", $session_id);
      
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

//Hago un fetch para ver el registro más reciente de status y voy sumando en un array usando de índice associated_block los presentes y los totales de cada bloque
//Luego minutes se calcula sumando la frecuencia de carga de las estadísticas de bloque al valor de 'minutes' del último registro cargado en block_hist
mysqli_stmt_fetch($statement);

//Cargo el array de bancas para poder hacer las asignaciones correspondientes desde el array en el próximo paso.
//Inicializo los contadores de totales y presentes para cada bloque.
for($k=1; $k<=strlen($estadoUbicaciones); $k++){
  mysqli_stmt_fetch($statement_benchs);
  $bench_block[$number] = $associated_block;
  $bench_member[$number] = $associated_member;
  $blocks[$associated_block]["presents"] = 0;
  $blocks[$associated_block]["total"] = 0; 
}

//Las bancas arrancan en 1... como el índice de $bench_block son las bancas, debo arrancar el for en 1. 
for($j = 1; $j <= strlen($estadoUbicaciones); $j++){ 
  $blocks[$bench_block[$j]]["presents"] += intval($estadoUbicaciones[$j-1]);
  $blocks[$bench_block[$j]]["total"]++;
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

//Decodifico lo leido de la tabla cronserver y tomo la info correspondiente a la frecuencia de generación del historial.
mysqli_stmt_fetch($statement_cronserver);
$json_config = json_decode($config, true);
$fill_block_frec = $json_config['fill_block_frec'];

    //Hago un select a la  tabla block_history conocer el 'minutes' del ultimo registro cargado 
    if($link){
      $statement_block_history = mysqli_prepare($link, "SELECT minutes
        									FROM block_history
    											WHERE session_id = ?
                          ORDER BY minutes DESC
                          LIMIT 1");
        
        mysqli_stmt_bind_param($statement_block_history, "s", $session_id);
          
    		if($statement_block_history){		
    			mysqli_stmt_execute($statement_block_history);
    			mysqli_stmt_store_result($statement_block_history);
    			mysqli_stmt_bind_result($statement_block_history, $minutes);
        }
        else{
           $response["error"] = "La consulta de minutos en block_history no fue ejecutada";
        }

        //Sumo la frecuencia de actualización a los minutos del último registro cargado en block_history. Si es el primer registro le pongo '0'.
        if(mysqli_stmt_fetch($statement_block_history)){
          $elapsed_time = $minutes + $fill_block_frec;
        }
        else{
          $elapsed_time = 0;
        }
    }
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