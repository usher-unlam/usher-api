<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    if(isset($_POST['username'])){
      $username = $_POST["username"];
    }
	else{
		//error, se espera un nombre de usuario
	  }
    if(isset($_POST['session'])){
      $session = $_POST['session'];
    }
    
    if($link){
		$statement = mysqli_prepare($link, "SELECT blocks.name, member_history.session_id, members.surname, members.name, member_history.presences, member_history.total
											FROM users, blocks, members, member_history
											WHERE users.username LIKE ?
											AND users.member_id = blocks.head_id
											AND blocks.id_block = member_history.block_id
											AND member_history.member_id = members.id_member
                      AND member_history.session_id = ?");
		mysqli_stmt_bind_param($statement, "ss", $username, $session);
      
		if($statement){		
			mysqli_stmt_execute($statement);
			mysqli_stmt_store_result($statement);
			mysqli_stmt_bind_result($statement, $block_name, $session_id, $member_surname, $member_name, $presences, $total);
        }
    }
       
    $response = array();
    $response["succes"] = false;
     
    $pos = 0;
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;  
        $response[$pos]["session"] = $session_id;
        $response[$pos]["block"] = $block_name;
		    $response[$pos]["member_surname"] = $member_surname;
        $response[$pos]["member_name"] = $member_name;
        $response[$pos]["presences"] = $presences;
		    $response[$pos]["total"] = $total;
        $pos++;
    }
    
    echo json_encode($response);
    
    $link->close();
?>