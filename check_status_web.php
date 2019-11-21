<?php
require_once 'db_connect_web.php';
require_once 'db_connect.php';

$link = conectar();
$linkRec = conectarRec();

if(isset($_POST['server'])){
  $svr = $_POST['server'];
  }
  elseif(isset($_GET['server'])){
        $svr = $_GET['server'];
  }
  else{
    echo "Error, no se especificó un servidor";
    exit;
  }

if(isset($_POST['banca'])){
  $banca = $_POST['banca'];
  }
  
  if($linkRec){
		$statement = mysqli_prepare($linkRec, "SELECT camserver, prioridad, MAX(tstamp), estadoUbicaciones
    									FROM estado
											WHERE camserver LIKE ? ");
                      /*OR camserver LIKE 'MANUAL'
                      GROUP BY camserver");*/
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
    
    if($link){
      $statement_benchs = mysqli_prepare($link, "SELECT number, associated_member_id, associated_block_id, manual_state FROM benchs");
    
      if($statement_benchs){
         mysqli_stmt_execute($statement_benchs);
         mysqli_stmt_store_result($statement_benchs);
  			 mysqli_stmt_bind_result($statement_benchs, $number, $associated_member, $associated_block, $manual_state);
      }
      else{
         $response["error"] = "La consulta a las bancas no fue ejecutada";
      }
    
      while(mysqli_stmt_fetch($statement_benchs)){
          $benchs[$number] = $manual_state;
      }
   
     $defStatus = '';
     $manual_state = '';
   
    //El array de bancas arranca en la posición 1; En la tabla está así, por eso arranco con $i+1. Así devuelvo la info correspondiente a cada banca en su subíndice
    for ($i=0; $i < count($benchs); $i++){
        if($benchs[$i+1] < 2){
           $defStatus .= $benchs[$i+1];
        }
        else{
           $defStatus .= $fetch[$svr]["benchs"][$i];
        }
        $manual_state .= $benchs[$i+1];
    }
    
    if($response["succes"]){
      $response["status"] = $defStatus;
      $response["manual_state"] = $manual_state;
      $response["read_state"] = $fetch[$svr]["benchs"];
    }
    
    }
    else{
      $response["succes"] = false;
    }
    
    echo json_encode($response);
    
    $link->close();
?>