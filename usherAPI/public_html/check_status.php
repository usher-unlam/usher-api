<?php
require_once 'db_connect.php';

$link = conectar();
$sqlQuery = "SELECT * FROM estado_banca ORDER BY id DESC LIMIT 1";
$resultado = $link->query($sqlQuery);
//$resultado = mysql_query($sqlQuery, $link);
while($row=$resultado->fetch_array()){
//while($row=mysql_fetch_array($resultado)){
	echo "ID: " .$row['id']. "<br>Estado de bancas: " .$row['estado']. "<br>Marca de tiempo: " .$row['time']. "<br>";
}
//$id =   $_POST['id']; // "1210673000000777064"; //

//$emision = new Emision(ZOHO_TOKEN, $id);

//Agregar mensajes
//echo($emision->validar());
//echo($emision->emitirCotizacion());

