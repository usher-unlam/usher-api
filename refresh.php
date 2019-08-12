<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    //$con = mysqli_connect("localhost", "root", "", "usuarios");
    
    $statement = mysqli_prepare($link, "SELECT busyState FROM benchs");
    mysqli_stmt_execute($statement);
    
    mysqli_stmt_store_result($statement);
    mysqli_stmt_bind_result($statement, $busystate);
    
    $response = array();
    $response["succes"] = false; 
    
    $statusstr = "";
    $cant = 0;
    
    while(mysqli_stmt_fetch($statement)){
        $statusstr = $statusstr.$busystate;
        $cant++;
    }
    if($statusstr){
      $response["succes"] = true;  
      $response["status"] = $statusstr;
      $response["cantidad"] = $cant;
    }
    
    echo json_encode($response);
?>