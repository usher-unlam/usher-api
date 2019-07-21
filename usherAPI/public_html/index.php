<?php
header("Content-Type: text/html;charset=utf-8");
/*
Este archivo funciona como front controller.

En produccion, para que todos los request se ruteen a traves de este archivo, verificar que el
 rewrite module en la config de Apache esté instalado y habilitado.

http://stackoverflow.com/questions/6890200/what-is-a-front-controller-and-how-is-it-implemented-in-php
*/

//ini_set('display_errors', 'On');
require_once 'config.php';

/*
Definimos path.
En modo debug, $path ya está definida.
Si no, se define en funcion del httpRequest.
*/
if (!isset($path)) {
    //Esta linea salio de aca: Simulate Apache mod_rewrite routing
    //https://cloud.google.com/appengine/docs/php/config/mod_rewrite
    $fullPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/usherAPI/public_html', '', $fullPath);
}
//$token = $_POST['token'];
$token = $_GET['token'];

//Verificamos el token
$auth = ($token == THISAPP_TOKEN ? true : false);
//$auth = ($token == $token ? true : false);
//Seleccionamos la acción que se ejecuta
if (($path == '/estado_banca') && $auth == true) {
    include_once 'check_status.php';
}
elseif (($path == '/calendar') && $auth == true) {
    //include_once 'sgst_api/sgst_calendar.php';
    $username = $_GET['username'];
    include_once 'sgst_api/ics/'.$username.'.ics';
}
else{
    echo 'Acceso denegado';
}
?>
