<?php
function conectarRec()
{
$server = "usher.sytes.net";
$database = "usher_rec";
$user = "usher";
$pass = "usher101";
        //Creas una variable de tipo objeto mysqli con los datos de la bd y el charset que quieras
        $link = new mysqli($server, $user, $pass, $database);

        if ($link->connect_errno) {
                // No se debe revelar información delicada
                echo "Lo sentimos, este sitio web está experimentando problemas.";

                // Algo que no se debería de hacer en un sitio público, aunque este ejemplo lo mostrará
                // de todas formas, es imprimir información relacionada con errores de MySQL -- se podría registrar
                echo "Error: Fallo al conectarse a MySQL debido a: \n";
                echo "Errno: " . $link->connect_errno . "\n";
                echo "Error: " . $link->connect_error . "\n";
    
                // Podría ser conveniente mostrar algo interesante, aunque nosotros simplemente saldremos
                exit;
        } else
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
