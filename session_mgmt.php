<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    if(isset($_POST['action'])){
		$action = $_POST['action'];
    }
	else{
		//error, se espera un nombre de usuario
	}
	if(isset($_POST['comment'])){
		$comment = $_POST['comment'];
    }
	else{
		$comment = '';
	}
    
    if($link){
		if($action == 'start'){
			$statement = mysqli_prepare($link, "INSERT INTO `sessions` (`session_id`, `start_date`, `end_date`, `comment`)
												VALUES (NULL, CURRENT_TIME(), '', ?)");
			mysqli_stmt_bind_param($statement, "s", $comment);
		}
		else{
			//buscar el registro más nuevo para saber qué sesión voy a cerrar (alternativamente puedo buscar una sesión a mano por si hubo algún error en el cierre... ver esto)
		}
      
		if($statement){
			mysqli_stmt_execute($statement)
			//mysqli_stmt_store_result($statement);
			//mysqli_stmt_bind_result($statement, $block_name, $session_id, $member_surname, $member_name, $presences, $total);
        }
    }
    
	printf("filas insertadas: %d\n", mysqli_stmt_affected_rows($stmt));
	
    $response = array();
    $response["succes"] = false; 
	
    /*while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;  
        $response[$session_id]["block"] = $block_name;
        $response[$session_id]["session"] = $session_id;
		$response[$session_id]["member_surname"] = $member_surname;
        $response[$session_id]["member_name"] = $member_name;
        $response[$session_id]["presences"] = $presences;
		$response[$session_id]["total"] = $total;
    }*/
    
    echo json_encode($response);
    
    $link->close();
?>