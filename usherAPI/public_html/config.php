<?php

//Definimos todas las variables de configuracion

//token que requiere appengine para devolver una respuesta. Se envia desde zoho.
//TOKEN de la cuenta gparadelo
//define('ZOHO_TOKEN', '352f81f67e3bb1fd8a812c2cb9900ea0');

//TOKEN de la cuenta gbosco
define('ZOHO_TOKEN', '6842bb296b787872726835c76b261bda');
//define('THISAPP_TOKEN', '6P75bDgwn0ng8sJC5ePd');
define('THISAPP_TOKEN', '48370255gBrgdlpl050588');

/*
 *Estas constantes se usan en Emision.php y approve.php
 IDADMIN y NAMEADMIN definen al nuevo propietario de una cotizacion cuando se cambia el propietario.
APROBACIONVENTAS y APROBACIONFINANZAS son los destinantarios de mails cuando una cotizacion necesita aprobacion.
 * */
define('IDADMIN','1210673000000071001');
define('NAMEADMIN','Alejandro Bogunovich');
//produccion
//define('APROBACIONVENTAS','abogunovich@analytical-tech.com');
//define('APROBACIONFINANZAS','abogunovich@analytical-tech.com');
//testing
define('APROBACIONVENTAS','gbosco@analytical.com');
define('APROBACIONFINANZAS','gbosco@analytical.com');

//Estos son los datos de la cuenta desde la que se envian los mails
define('MAILSENDERUSER','do-not-reply@analytical.com');
define('MAILSENDERPASS','VD>u=a9S');
//define('MAILSENDERPASS','Artemisa18$');
//define('MAILSENDERUSER','do-not-reply@analytical-tech.com​');
//define('MAILSENDERPASS','YwO1Av3k');
