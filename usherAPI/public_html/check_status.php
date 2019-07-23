<?php
require_once 'db_connect.php';

$link = conectar();
$sqlQuery = "SELECT * FROM estado_banca ORDER BY id DESC LIMIT 1";
$resultado = mysql_query($sqlQuery, $link);

while($row=mysql_fetch_array($resultado)){
	echo "ID: " .$row['id']. "<br>Estado de bancas: " .$row['estado']. "<br>Marca de tiempo: " .$row['time']. "<br>";
}