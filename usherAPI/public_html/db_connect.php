<?php
function conectar()
{
$server = "localhost";
$database = "usher_api";
$user = "usher";
$pass = "usher101";
        //Creas una variable de tipo objeto mysqli con los datos de la bd y el charset que quieras
        $link = mysqli_connect($server, $user, $pass, $database);
        if(!$link)
        {
                echo "Hay problemas al conectar con la base de datos";
                exit();
        }else
                $link->set_charset("utf8");
/*
        $res = $link->query("SELECT * FROM personas");

        while($f = $res->fetch_object()){
            echo $f->nombre.' <br/>';
        }
*/
/*        if(!($link=mysql_connect($server, $user, $pass)))
        {
                echo "Hay problemas al conectarse con MySQL";
                echo mysql_error();
                exit();
        }
        if(!mysql_select_db($database, $link))
        {
                echo "Hay problemas al seleccionar la base de datos";
                exit();
        }*/
        return $link;
}
?>
