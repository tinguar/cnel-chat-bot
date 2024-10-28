<?php
//DESHABILITAMOS EL MOSTRAR ERRORES
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1);

require 'vendor/autoload.php';


/*
 * VERIFICACION DEL WEBHOOK
*/
//TOQUEN QUE QUERRAMOS PONER 
$token = '***********************';
//RETO QUE RECIBIREMOS DE FACEBOOK
$palabraReto = $_GET['hub_challenge'];
//TOQUEN DE VERIFICACION QUE RECIBIREMOS DE FACEBOOK
$tokenVerificacion = $_GET['hub_verify_token'];
//SI EL TOKEN QUE GENERAMOS ES EL MISMO QUE NOS ENVIA FACEBOOK RETORNAMOS EL RETO PARA VALIDAR QUE SOMOS NOSOTROS
if ($token === $tokenVerificacion) {
    echo $palabraReto;
    exit;
}

/*
 * RECEPCION DE MENSAJES
 */
//LEEMOS LOS DATOS ENVIADOS POR WHATSAPP
$respuesta = file_get_contents("php://input");
//CONVERTIMOS EL JSON EN ARRAY DE PHP
$respuesta = json_decode($respuesta, true);
//EXTRAEMOS EL MENSAJE DEL ARRAY
$mensaje=$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
$mensaje2=$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title'];
//EXTRAEMOS EL TELEFONO DEL ARRAY
$telefonoCliente=$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['from'];
//EXTRAEMOS EL ID DE WHATSAPP DEL ARRAY
$id=$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['id'];
//EXTRAEMOS EL TIEMPO DE WHATSAPP DEL ARRAY
$timestamp=$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'];
//EXTRAEMOS ID FOTO
$foto=$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'];
$respuestaJson = json_encode($respuesta, JSON_PRETTY_PRINT);
file_put_contents("text.txt", $respuestaJson);
//SI HAY UN MENSAJE
if($telefonoCliente!=null){

    require_once "envia.php";
    //ENVIAMOS LA RESPUESTA VIA WHATSAPP
    enviar($mensaje,$respuesta,$id,$timestamp,$telefonoCliente,$foto,$mensaje2);


}
