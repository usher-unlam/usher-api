<?php
require_once 'db_connect.php';
$fullOutput = false;

$link = conectar();
if(isset($_GET['id'])){
  $id = $_GET['id'];
  }
  else{
    echo "Error: No se especificó un ID";
    exit;
  }

if(isset($_GET['status'])){
  $status = $_GET['status'];
  if($status == 'starting' || $status == 'off' || $status == 'restarting' || $status == 'suspending'){ 
  }
  else{
    echo "Error: No se especificó un estado válido";
    exit;
  }
  }
else{
    echo "Error: No se especificó ningún estado";
    exit;
}
  
$sqlQuery = "UPDATE camserver SET status = '" .$status. "' WHERE id = '" .$id. "'"; 

$resultado = mysqli_query($link, $sqlQuery);
if($resultado){
  echo "Estado actualizado correctamente";
  }

//$resultado->free();
$link->close();