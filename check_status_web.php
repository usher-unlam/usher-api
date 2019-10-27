<?php
require_once 'db_connect_web.php';

$link = conectar();

if(!isset($_POST['server'])){
  echo "Error, no se especificó un servidor";
  exit;
  }

$svr = $_POST['server'];

if(isset($_POST['banca'])){
  $banca = $_POST['banca'];
  }
  
  if($link){
		$statement = mysqli_prepare($link, "SELECT camserver, prioridad, MAX(tstamp), estadoUbicaciones
    									FROM status
											WHERE camserver LIKE ?
                      OR camserver LIKE 'MANUAL'
                      GROUP BY camserver");
		mysqli_stmt_bind_param($statement, "s", $svr);
      
		if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
			mysqli_stmt_bind_result($statement, $camserver, $priority, $tstamp, $estadoUbicaciones);
        }
     else{
       $response["error"] = "La consulta no fue ejecutada";
       }
    }
    else{
      $response["error"] = "No se estableció la conexión a la base de datos";
    }
       
    $response["succes"] = false;
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;
        $fetch[$camserver]["priority"] = $priority;
        $fetch[$camserver]["benchs"] = $estadoUbicaciones;
		    $fetch[$camserver]["tstamp"] = $tstamp;
    }
    
    for ($i=0; $i < strlen($fetch["MANUAL"]["benchs"]); $i++){
          if($fetch["MANUAL"]["benchs"][$i] == 2){
            $defStatus[$i] = $fetch[$svr]["benchs"][$i];
          }
          else{
            $defStatus[$i] = $fetch["MANUAL"]["benchs"][$i];
          }
    }
    
    if($response["succes"])
      $response["status"] = $defStatus;
    
    echo json_encode($response);
    
    $link->close();
?>