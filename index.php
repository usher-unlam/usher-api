<?php
//header("Content-Type: text/html;charset=utf-8");
header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');
/*
Este archivo funciona como front controller.

En produccion, para que todos los request se ruteen a traves de este archivo, verificar que el
 rewrite module en la config de Apache esté instalado y habilitado.

http://stackoverflow.com/questions/6890200/what-is-a-front-controller-and-how-is-it-implemented-in-php
*/

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');
require_once 'config.php';

/*
Definimos path.
En modo debug, $path ya está definida.
Si no, se define en funcion del httpRequest.
*/
$fullPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!isset($path)) {
    //Esta linea salio de aca: Simulate Apache mod_rewrite routing
    //https://cloud.google.com/appengine/docs/php/config/mod_rewrite
    $path = $fullPath;
    //$path = str_replace('/usherAPI/public_html', '', $path);
    $path = str_replace('/usher-api', '', $path);
}else{
    $path = $fullPath;
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
elseif (($path == '/cnnmanage') && $auth == true) {
    include_once 'cnn_manage.php';
}
elseif (($path == '/login') && $auth == true) {
    include_once 'login.php';
}
elseif (($path == '/refresh') && $auth == true) {
    include_once 'refresh.php';
}
elseif (($path == '/register') && $auth == true) {
    include_once 'register.php';
}
elseif (($path == '/sessions') && $auth == true) {
    include_once 'sessions.php';
}
elseif (($path == '/member_hist') && $auth == true) {
    include_once 'member_hist.php';
}
elseif (($path == '/block_hist') && $auth == true) {
    include_once 'block_hist.php';
}
else{
    echo 'Acceso denegado' . $path;
}
?>
