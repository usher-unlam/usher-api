<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    if(isset($_POST['action'])){
      $action = $_POST['action'];
    }
    
    if($link){
	    $statement = mysqli_prepare($link, "ALTER EVENT usher_web.sync_status ?");
		mysqli_stmt_bind_param($statement, "s", $action);
      
        if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
        }
    }
       
    $response = array();
    $response["succes"] = false; 
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;  
    }
    
    echo json_encode($response);
    
    $link->close();
?>