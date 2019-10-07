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
		$statement = mysqli_prepare($link, "SELECT blocks.name, member_history.session_id, members.surname, members.name, member_history.presences, member_history.total
											FROM users, blocks, members, member_history
											WHERE users.username LIKE ?
											AND users.member_id = blocks.head_id
											AND blocks.id_block = member_history.block_id
											AND member_history.member_id = members.id_member");
		mysqli_stmt_bind_param($statement, "s", $username);
      
		if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
			mysqli_stmt_bind_result($statement, $block_name, $session_id, $member_id, $presences, $total);
        }
    }
       
    $response = array();
    $response["succes"] = false; 
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;  
        $response[$session_id]["block"] = $block_name;
        $response[$session_id]["session"] = $session_id;
		$response[$session_id]["member_surname"] = $member_surname;
        $response[$session_id]["member_name"] = $member_name;
        $response[$session_id]["presences"] = $presences;
		$response[$session_id]["total"] = $total;
    }
    
    echo json_encode($response);
    
    $link->close();
?>