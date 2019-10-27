<?php
require_once 'db_connect_web.php';

$link = conectar();

if(!isset($_POST['server'])){
  echo "Error, no se especific� un servidor";
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
      $response["error"] = "No se estableci� la conexi�n a la base de datos";
    }
       
    $response["succes"] = false;
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;
        $fetch[$camserver]["priority"] = $priority;
        $fetch[$camserver]["benchs"] = $estadoUbicaciones;
		    $fetch[$camserver]["tstamp"] = $tstamp;
    }
    
    $statement_benchs = mysqli_prepare($link, "SELECT number, associated_member, associated_block, manual_state FROM benchs");
    
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
   
    //El array de bancas arranca en la posici�n 1; En la tabla est� as�, por eso arranco con $i+1. As� devuelvo la info correspondiente a cada banca en su sub�ndice
    for ($i=0; $i < count($benchs); $i++){
        if($benchs[$i+1] < 2){
           $defStatus .= $benchs[$i+1];
        }
        else{
           $defStatus .= $fetch[$svr]["benchs"][$i];
        }
        $manual_state .= $benchs[$i+1];
    }
    
    /*for ($i=0; $i < strlen($fetch["MANUAL"]["benchs"]); $i++){
          if($fetch["MANUAL"]["benchs"][$i] == 2){
            $defStatus[$i] = $fetch[$svr]["benchs"][$i];
          }
          else{
            $defStatus[$i] = $fetch["MANUAL"]["benchs"][$i];
          }
    }*/
    
    if($response["succes"]){
      $response["status"] = $defStatus;
      $response["manual_state"] = $manual_state;
    }
    
    echo json_encode($response);
    
    $link->close();
?>