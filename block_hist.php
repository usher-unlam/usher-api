<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    if(isset($_POST['username'])){
      $username = $_POST["username"];
    }
	else{
		//error, se espera un nombre de usuario
	}
    
    if($link){
		$statement = mysqli_prepare($link, "SELECT blocks.name, block_history.id, block_history.session_id, block_history.minutes, block_history.presents, block_history.total
											FROM users, blocks, block_history
											WHERE users.username LIKE ?
											AND users.member_id = blocks.head_id
											AND blocks.id_block = block_history.block_id");
		mysqli_stmt_bind_param($statement, "s", $username);
      
		if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
			mysqli_stmt_bind_result($statement, $id, $session_id, $block_name, $minutes, $presents, $total);
        }
    }
       
    $response = array();
    $response["succes"] = false; 
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;
		$response[$id]["session_id"] = $session_id;
        $response[$id]["block"] = $block_name;
        $response[$id]["session"] = $session_id;
        $response[$id]["minutes"] = $minutes;
        $response[$id]["presents"] = $presents;
		$response[$id]["total"] = $total;
    }
    
    echo json_encode($response);
    
    $link->close();
?>