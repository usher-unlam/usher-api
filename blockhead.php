<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    if($link){
      if(isset($_POST['username'])){
        $username = $_POST["username"];
      }
	  
      $statement = mysqli_prepare($link, "SELECT blocks.name FROM users, blocks
										  WHERE users.username LIKE ?
										  AND users.member_id = blocks.head_id");

	  mysqli_stmt_bind_param($statement, "s", $username);
      
      if($statement){		
        mysqli_stmt_execute($statement);
        mysqli_stmt_store_result($statement);
        mysqli_stmt_bind_result($statement, $block_name);
        }
    }   
    
    $response = array();
    $response["succes"] = false; 
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;  
        $response["block_name"] = $block_name;
    }
    
    echo json_encode($response);
    
    $link->close();
?>