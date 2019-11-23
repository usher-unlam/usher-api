<?php
require_once 'db_connect_web.php';

$link = conectar();

if(isset($_POST['session_id'])){
  $session = $_POST['session_id'];
}
else{
    echo "Error, no se especificó la sesión";
    exit;
}

if($link){
		$statement = mysqli_prepare($link, "SELECT camserver, prioridad, tstamp, estadoUbicaciones
    									FROM status
											WHERE camserver LIKE 'SVR1'");
		//mysqli_stmt_bind_param($statement, "s", $svr);
      
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

$benchs["succes"] = false;       
$response["succes"] = false;

$regTotales = 0;
//Hago un primer fetch para inicializar el array de bancas con los valores del primer registro y para conocer la cantidad de bancas para el for
mysqli_stmt_fetch($statement);
for($i=1 ; $i<=strlen($estadoUbicaciones); $i++){
    $benchs[$i]["session"] = $session; 
    $benchs[$i]["presences"] = (int) $estadoUbicaciones[$i-1];
}

//Recorro las rows y para cada una recorro el string estadoUbicaciones y sumo (0 o 1) a la cantidad de presencias de cada banca ($benchs[$i]) 
while(mysqli_stmt_fetch($statement)){
     $response["succes"] = true;
     for($i=1 ; $i<=strlen($estadoUbicaciones); $i++){ 
       $benchs[$i]["presences"] = $benchs[$i]["presences"] + (int) $estadoUbicaciones[$i-1];
     }
     $regTotales++;
}

if($response["succes"]){
      $statement_benchs = mysqli_prepare($link, "SELECT number, associated_member_id, associated_block_id FROM benchs");
      
      if($statement_benchs){		
			  mysqli_stmt_execute($statement_benchs);
			  mysqli_stmt_store_result($statement_benchs);
			  mysqli_stmt_bind_result($statement_benchs, $number, $associated_member, $associated_block);
      }
      else{
         $response["error"] = "La consulta de bancas no fue ejecutada";
      } 

      //En cada row tengo una banca. Guardo el associated_block en el array de benchs que cargué en el While anterior y cargo el total de registros leídos en 'status'
      while(mysqli_stmt_fetch($statement_benchs)){
        $benchs[$number]["block_id"] = $associated_block;
        $benchs[$number]["member_id"] = $associated_member;
        $benchs[$number]["total"] = $regTotales;
      }
}

$insertions = 0;
for($i=1 ; $i<=strlen($estadoUbicaciones); $i++){		
    $statement_insert = mysqli_prepare($link, "INSERT INTO member_history (session_id, block_id, member_id, presences, total) VALUES (?, ?, ?, ?, ?)");
    if($statement_insert){
      mysqli_stmt_bind_param($statement_insert, "sssss", $session, $benchs[$i]["block_id"], $benchs[$i]["member_id"], $benchs[$i]["presences"], $benchs[$i]["total"]);
      if(mysqli_stmt_execute($statement_insert)){
        $insertions++;
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