<?php
require_once 'db_connect_web.php';

$link = conectar();

if(!isset($_POST['bench'])){
  echo "Error, no se especificó una banca";
  exit;
  }
if(!isset($_POST['status'])){
  echo "Error, no se especificó un estado";
  exit;
  }

$bench = $_POST['bench'];
$status = $_POST['status'];
  
  if($link){
		$statement = mysqli_prepare($link, "UPDATE benchs SET manual_state = ? WHERE number = ? ");
		mysqli_stmt_bind_param($statement, "ss", $status, $bench);
      
		if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
			//mysqli_stmt_bind_result($statement, $camserver, $priority, $tstamp, $estadoUbicaciones);
        }
     else{
       $response["error"] = "La consulta no fue ejecutada";
       }
    }
    else{
      $response["error"] = "No se estableció la conexión a la base de datos";
    }
       
    $response["succes"] = false;
    
    if($statement){
        $response["succes"] = true;
    }
    
    echo json_encode($response);
    
    $link->close();
?>