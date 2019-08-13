<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    $username = $_POST["username"];
    $password = $_POST["password"];
    $access = "1";
    
    if($link){
      $statement = mysqli_prepare($link, "SELECT * FROM users WHERE username = ? AND password = ? AND access = ?");
      if($statement){
        mysqli_stmt_bind_param($statement, "sss", $username, $password, $access);
        mysqli_stmt_execute($statement);
    
        mysqli_stmt_store_result($statement);
        mysqli_stmt_bind_result($statement, $userID, $name, $surname, $username, $password, $access, $tstamp);
        }
    }
       
    $response = array();
    $response["succes"] = false; 
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;  
        $response["name"] = $name;
        $response["surname"] = $surname;
    }
    
    echo json_encode($response);
    
    $link->close();
?>