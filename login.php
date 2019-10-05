<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    $username = $_POST["username"];
    $password = $_POST["password"];
    //$access = "1";
    
    if($link){
      $statement = mysqli_prepare($link, "SELECT * FROM users WHERE username = ? AND password = ?"); //AND access = ?");
      if($statement){
        mysqli_stmt_bind_param($statement, "ss", $username, $password); //, $access);
        mysqli_stmt_execute($statement);
    
        mysqli_stmt_store_result($statement);
        mysqli_stmt_bind_result($statement, $userID, $name, $surname, $username, $password, $access, $tstamp, $member_id);
        }
    }
       
    $response = array();
    $response["succes"] = false; 
	$response["exists"] = false;
    
	//Si hay resultado va a ser único (no permitimos usuarios duplicados)
    while(mysqli_stmt_fetch($statement)){
		$response["exists"] = true;
		if($access == '1'){
			$response["succes"] = true;  
			$response["name"] = $name;
			$response["surname"] = $surname;
    }
    
    echo json_encode($response);
    
    $link->close();
?>