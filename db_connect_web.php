<?php
function conectar()
{
$server = "usher.sytes.net";
$database = "usher_web";
$user = "usher";
$pass = "usher101";
        //Creas una variable de tipo objeto mysqli con los datos de la bd y el charset que quieras
        $link = mysqli_connect($server, $user, $pass, $database);

        if ($link->connect_errno) {
                // No se debe revelar información delicada
                echo "Lo sentimos, este sitio web está experimentando problemas.";

                // Algo que no se debería de hacer en un sitio público, aunque este ejemplo lo mostrará
                // de todas formas, es imprimir información relacionada con errores de MySQL -- se podría registrar
                echo "Error: Fallo al conectarse a MySQL debido a: \n";
                echo "Errno: " . $link->connect_errno . "\n";
                echo "Error: " . $link->connect_error . "\n";
    
                exit;
        } else
                //$link->set_charset("utf8");
        return $link;
}
?>