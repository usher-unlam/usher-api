<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    if(isset($_POST['action'])){
      $action = $_POST['action'];
    }
    
    if($link){
      $response = array();
      if(mysqli_query($link, "ALTER EVENT usher_web.status_sync " .$action)){
        $response["succes"] = true;
        }else{
          $response["succes"] = false;
          }
    }   
    
    echo json_encode($response);
    
    $link->close();
?>