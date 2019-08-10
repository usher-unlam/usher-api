<?php
require_once 'db_connect.php';
$fullOutput = false;

$link = conectar();
if(isset($_GET['id'])){
  $id = $_GET['id'];
  }
  else{
    echo "Error: Unspecified ID";
    exit;
  }

if(isset($_GET['status'])){
  $status = $_GET['status'];
  if($status == 'starting' || $status == 'off' || $status == 'restarting' || $status == 'suspending'){ 
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
  
$sqlQuery = "UPDATE camserver SET status = '" .$status. "' WHERE id = '" .$id. "'"; 

$resultado = mysqli_query($link, $sqlQuery);
if($resultado){
  echo "Succesfull";
  }

//$resultado->free();
$link->close();