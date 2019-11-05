<?php
    require_once 'db_connect_web.php';
	// inicializacion
	$link = conectar();
    $response = array();
	$action = NULL;
	$comment = "";
	$statement = NULL;


    if(isset($_POST['action'])){
		$action = $_POST['action'];
	}
	else{
		//error, se espera una accion
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
												VALUES (NULL, CURRENT_TIME(), 0, ?)");
			mysqli_stmt_bind_param($statement, "s", $comment);
		}
		elseif($action == 'end'){
			//buscar el registro m?s nuevo para saber qu? sesi?n voy a cerrar (alternativamente puedo buscar una sesi?n a mano por si hubo alg?n error en el cierre... ver esto)
			$statement = mysqli_prepare($link, "UPDATE `sessions` SET `end_date` = CURRENT_TIME() ORDER BY session_id DESC LIMIT 1");
		}
		
		if($statement){
			mysqli_stmt_execute($statement);
			//mysqli_stmt_store_result($statement);
			//mysqli_stmt_bind_result($statement, $block_name, $session_id, $member_surname, $member_name, $presences, $total);
		}
	}

    $response["succes"] = false;
	
	if(!is_null($statement) && mysqli_stmt_affected_rows($statement)){
		$response["succes"] = true;  
	}
    
	echo json_encode($response);
		
	$link->close();
?>