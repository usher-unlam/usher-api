<?php
require_once 'db_connect.php';

$link = conectar();

if(!isset($_GET['server'])){
  echo "Error, no se especificó un servidor";
  exit;
  }

$svr = $_GET['server'];

if(isset($_GET['banca'])){
  $banca = $_GET['banca'];
  $sqlQuery = "SELECT * FROM " .$svr. " WHERE banca = " .$banca;
  }
  else{
    $sqlQuery = "SELECT * FROM " .$svr. " ORDER BY banca ASC";
  }
//$resultado = mysql_query($sqlQuery, $link);
$resultado = $link->query($sqlQuery);
if ($resultado) { 
    while ($row = $resultado->fetch_object()){
      //echo $row->banca;
      $array[$row->banca] = $row;
      //echo $data;
      //print_r($row);
    }
    $res = json_encode($array);
    echo $res;
    //$js = json_decode($res, true);
    //print_r($js['2']['estado']);
	}
  else{
    echo "No se encontraron resultados";
    }

$link->close();