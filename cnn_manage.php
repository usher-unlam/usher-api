<?php
require_once 'db_connect.php';
$fullOutput = false;

$link = conectarRec();
if(isset($_GET['id'])){
  $id = $_GET['id'];
  }
  else{
    echo "Error: Unspecified ID";
    exit;
  }

if(isset($_GET['status'])){
  $status = $_GET['status'];
  if($status == 'starting' || $status == 'off' || $status == 'restarting' || $status == 'suspending' || $status == 'getstatus'){ 
  }
  else{
    echo "Error: Invalid status";
    exit;
  }
  }
else{
    echo "Error: Unspecified status";
    exit;
}

if($status == 'getstatus'){
  $sqlQuery = "SELECT status FROM camserver WHERE id = '" .$id. "'";
  }else{
    $sqlQuery = "UPDATE camserver SET status = '" .$status. "' WHERE id = '" .$id. "'";
  } 

$resultado = mysqli_query($link, $sqlQuery);
if($resultado){
  if($status == 'getstatus'){
    $rows = $resultado->fetch_all(MYSQLI_ASSOC);
    echo $rows[0]['status'];
    }else{
      echo 'Succesfull';
    }
  }else{
    echo 'Unsuccesfull';
  }

//$resultado->free();
$link->close();