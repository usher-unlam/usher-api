<?php
function conectar()
{
$server = "localhost";
$database = "usher";
$user = "usher";
$pass = "rgdlpl55";

        if(!($link=mysql_connect($server, $user, $pass)))
        {
                echo "Hay problemas al conectarse con MySQL";
                echo mysql_error();
                exit();
        }
        if(!mysql_select_db($database, $link))
        {
                echo "Hay problemas al seleccionar la base de datos";
                exit();
        }
        return $link;
}
?>
