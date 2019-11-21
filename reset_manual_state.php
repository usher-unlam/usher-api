<?php
require_once 'db_connect_web.php';
function reset_manual(){
	// inicializacion
	$link_reset = conectar();
    
  if($link_reset){
		$statement = mysqli_prepare($link_reset, "UPDATE `benchs` SET manual_state = 2");
		
		if($statement){
			mysqli_stmt_execute($statement);
		}
	}

  $link_reset->close();
 
	if(mysqli_stmt_affected_rows($statement) > 0){
		return true; 
	}
  else{
 	  return false;
  }
}
?>