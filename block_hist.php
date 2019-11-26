<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    if(isset($_POST['username'])){
      $username = $_POST["username"];
    }
    if(isset($_POST['session'])){
      $session = $_POST["session"];
    }
    
    if($username == 'webadmin'){
      $username = '%';
    }
    
	else{
		//error, se espera un nombre de usuario
	}
    
    if($link){
		$statement = mysqli_prepare($link, "SELECT DISTINCT blocks.name, block_history.session_id, block_history.minutes, block_history.presents, block_history.total
											FROM users, blocks, block_history
											WHERE users.username LIKE ?
											AND users.member_id = blocks.head_id
											AND blocks.id_block = block_history.block_id
                      AND session_id = ?");
		mysqli_stmt_bind_param($statement, "ss", $username, $session);
      
		if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
			mysqli_stmt_bind_result($statement, $block_name, $session_id, $minutes, $presents, $total);
        }
    }
       
    $response = array();
    $response["succes"] = false; 
    
    $pos = 0;
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;
		    $response[$pos]["session_id"] = $session_id;
        $response[$pos]["block"] = $block_name;
        $response[$pos]["session"] = $session_id;
        $response[$pos]["minutes"] = $minutes;
        $response[$pos]["presents"] = $presents;
		    $response[$pos]["total"] = $total;
        $pos++;
    }
    
    echo json_encode($response);
    
    $link->close();
?>