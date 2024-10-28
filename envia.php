<?php

//enviar.php
/*
 * RECIBIMOS LA RESPUESTA
*/
function enviar($recibido, $enviado, $idWA,$timestamp,$telefonoCliente,$foto,$recibido2) {

    header('Content-Type: text/plain; charset=utf-8');

        //TOKEN QUE NOS DA FACEBOOK
        $token = '***************';
        //NUESTRO TELEFONO
        $telefono = $telefonoCliente;
        //IDENTIFICADOR DE NÚMERO DE TELÉFONO
        $telefonoID = '*******************';
        //URL A DONDE SE MANDARA EL MENSAJE
        $url = 'https://graph.facebook.com/v17.0/' . $telefonoID . '/messages';
        //CONFIGURACION DEL MENSAJE
       
               
        $mensaje = '{
                "messaging_product": "whatsapp",
                "recipient_type": "individual",
                "to": "' . $telefonoCliente . '",
                "type": "interactive",
                "interactive": {
                    "type": "button",
                    "header": {
                        "type": "text",
                        "text": "¡Bienvenid@ 🤗! Gracias por escribirme."
                    },
                    "body": {
                        "text": "Aquí podrás consultar los horarios de cortes establecidos por CNEL. Este es un proyecto independiente creado por desarrolladores de Ecuador con el objetivo de mejorar la visualización de los horarios de cortes de energía del país. 🇪🇨\n\nAl interactuar con el bot estas aceptando los términos y condiciones https://cnel-ficha.tinguar.com/terminos-y-condiciones"
                    },
                    "footer": {
                        "text": "Por favor selecciona una opción"
                    },
                    "action": {
                        "buttons": [
                            {
                                "type": "reply",
                                "reply": {
                                    "id": "button1-id",
                                    "title": "Cedula de identidad"
                                }
                            },
                            {
                                "type": "reply",
                                "reply": {
                                    "id": "button2-id",
                                    "title": "Código Único"
                                }
                            },
                            {
                                "type": "reply",
                                "reply": {
                                    "id": "button3-id",
                                    "title": "Cuenta Contrato"
                                }
                            }
                        ]
                    }
                }
            }';
   
        if($recibido2=="Cedula de identidad" || $recibido=="cedula de identidad")
        {
            $mensaje = ''
                . '{'
                . '"messaging_product": "whatsapp", '
                . '"recipient_type": "individual",'
                . '"to": "' . $telefonoCliente . '", '
                . '"type": "text", '
                . '"text": '
                . '{'
                . '     "body":"Ingrese su número de cedula",'
                . '     "preview_url": true, '
                . '} '
                . '}';
        }
        if($recibido2=="Código Único" || $recibido=="Código Único")
        {
            $mensaje = ''
                . '{'
                . '"messaging_product": "whatsapp", '
                . '"recipient_type": "individual",'
                . '"to": "' . $telefonoCliente . '", '
                . '"type": "text", '
                . '"text": '
                . '{'
                . '     "body":"Ingrese su Código Único",'
                . '     "preview_url": true, '
                . '} '
                . '}';
        }
        if($recibido2=="Cuenta Contrato" || $recibido=="Cuenta Contrato")
        {
            $mensaje = ''
                . '{'
                . '"messaging_product": "whatsapp", '
                . '"recipient_type": "individual",'
                . '"to": "' . $telefonoCliente . '", '
                . '"type": "text", '
                . '"text": '
                . '{'
                . '     "body":"Ingrese su Cuenta Contrato",'
                . '     "preview_url": true, '
                . '} '
                . '}';
        }
         $mensajebody="";
        if (ctype_digit($recibido)) 
        {
           
            $tipo = 'IDENTIFICACION';
            $urlt = 'https://api.cnelep.gob.ec/servicios-linea/v1/notificaciones/consultar/' . $recibido . '/'.$tipo;
                
            // Inicializar cURL
            $ch = curl_init($urlt);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // Ejecutar la solicitud
            $respuestaJson = curl_exec($ch);
            curl_close($ch);
            
            // Decodificar la respuesta JSON
            $respuesta = json_decode($respuestaJson, true);
            
            // Verificar si la respuesta es exitosa
            if ($respuesta['resp'] === 'OK') {
                // Obtener las notificaciones
                $notificaciones = $respuesta['notificaciones'];
            
                // Crear un arreglo para agrupar las notificaciones por cuenta de contrato
                $notificacionesAgrupadas = [];
            
                foreach ($notificaciones as $notificacion) {
                    $direccion = $notificacion['direccion'];
                    $cuentaContrato = $notificacion['cuentaContrato'];
                    $detallePlanificacion = $notificacion['detallePlanificacion'];
            
                    // Inicializar la estructura si no existe
                    if (!isset($notificacionesAgrupadas[$cuentaContrato])) {
                        $notificacionesAgrupadas[$cuentaContrato] = [
                            'direccion' => $direccion,
                            'fechas' => [],
                        ];
                    }
            
                    // Iterar sobre los detalles de planificaci��n
                    foreach ($detallePlanificacion as $detalle) {
                        $horaDesde = $detalle['horaDesde'];
                        $horaHasta = $detalle['horaHasta'];
                        $fechaCorte = $detalle['fechaCorte'];
            
                        // Agrupar por fecha
                        if (!isset($notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte])) {
                            $notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte] = [];
                        }
                        $notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte][] = "$horaDesde - $horaHasta";
                    }
                }
            
                // Construir el mensaje
                $mensajebody = '';
                foreach ($notificacionesAgrupadas as $cuentaContrato => $info) {
                    $mensajebody .= "*Contrato*: $cuentaContrato\n";
                    $mensajebody .= "*Dirección*: {$info['direccion']}\n\n";
                    $mensajebody .= "*Detalles de Planificación*:\n";
                    foreach ($info['fechas'] as $fecha => $horas) {
                        $mensajebody .= "*$fecha*\n";
                        $mensajebody .= implode("\n", $horas) . "\n\n";
                    }
                }
                
                //echo $mensajebody; // Mostrar el mensaje
            } else {
                echo "Error en la consulta: " . $respuesta['mensajeError'];
            }
            if ($mensajebody=="") {
            $tipo = 'CUEN';
            $urlt = 'https://api.cnelep.gob.ec/servicios-linea/v1/notificaciones/consultar/' . $recibido . '/'.$tipo;
                
            // Inicializar cURL
            $ch = curl_init($urlt);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // Ejecutar la solicitud
            $respuestaJson = curl_exec($ch);
            curl_close($ch);
            
            // Decodificar la respuesta JSON
            $respuesta = json_decode($respuestaJson, true);
            
            // Verificar si la respuesta es exitosa
            if ($respuesta['resp'] === 'OK') {
                // Obtener las notificaciones
                $notificaciones = $respuesta['notificaciones'];
            
                // Crear un arreglo para agrupar las notificaciones por cuenta de contrato
                $notificacionesAgrupadas = [];
            
                foreach ($notificaciones as $notificacion) {
                    $direccion = $notificacion['direccion'];
                    $cuentaContrato = $notificacion['cuentaContrato'];
                    $detallePlanificacion = $notificacion['detallePlanificacion'];
            
                    // Inicializar la estructura si no existe
                    if (!isset($notificacionesAgrupadas[$cuentaContrato])) {
                        $notificacionesAgrupadas[$cuentaContrato] = [
                            'direccion' => $direccion,
                            'fechas' => [],
                        ];
                    }
            
                    // Iterar sobre los detalles de planificaci��n
                    foreach ($detallePlanificacion as $detalle) {
                        $horaDesde = $detalle['horaDesde'];
                        $horaHasta = $detalle['horaHasta'];
                        $fechaCorte = $detalle['fechaCorte'];
            
                        // Agrupar por fecha
                        if (!isset($notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte])) {
                            $notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte] = [];
                        }
                        $notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte][] = "$horaDesde - $horaHasta";
                    }
                }
            
                // Construir el mensaje
                $mensajebody = '';
                foreach ($notificacionesAgrupadas as $cuentaContrato => $info) {
                    $mensajebody .= "*Contrato*: $cuentaContrato\n";
                    $mensajebody .= "*Dirección*: {$info['direccion']}\n\n";
                    $mensajebody .= "*Detalles de Planificación*:\n";
                    foreach ($info['fechas'] as $fecha => $horas) {
                        $mensajebody .= "*$fecha*\n";
                        $mensajebody .= implode("\n", $horas) . "\n\n";
                    }
                }
                
                //echo $mensajebody; // Mostrar el mensaje
            } else {
                echo "Error en la consulta: " . $respuesta['mensajeError'];
            }
                }
            if ($mensajebody=="") {
            $tipo = 'CUENTA_CONTRATO';
            $urlt = 'https://api.cnelep.gob.ec/servicios-linea/v1/notificaciones/consultar/' . $recibido . '/'.$tipo;
                
            // Inicializar cURL
            $ch = curl_init($urlt);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // Ejecutar la solicitud
            $respuestaJson = curl_exec($ch);
            curl_close($ch);
            
            // Decodificar la respuesta JSON
            $respuesta = json_decode($respuestaJson, true);
            
            // Verificar si la respuesta es exitosa
            if ($respuesta['resp'] === 'OK') {
                // Obtener las notificaciones
                $notificaciones = $respuesta['notificaciones'];
            
                // Crear un arreglo para agrupar las notificaciones por cuenta de contrato
                $notificacionesAgrupadas = [];
            
                foreach ($notificaciones as $notificacion) {
                    $direccion = $notificacion['direccion'];
                    $cuentaContrato = $notificacion['cuentaContrato'];
                    $detallePlanificacion = $notificacion['detallePlanificacion'];
            
                    // Inicializar la estructura si no existe
                    if (!isset($notificacionesAgrupadas[$cuentaContrato])) {
                        $notificacionesAgrupadas[$cuentaContrato] = [
                            'direccion' => $direccion,
                            'fechas' => [],
                        ];
                    }
            
                    // Iterar sobre los detalles de planificaci��n
                    foreach ($detallePlanificacion as $detalle) {
                        $horaDesde = $detalle['horaDesde'];
                        $horaHasta = $detalle['horaHasta'];
                        $fechaCorte = $detalle['fechaCorte'];
            
                        // Agrupar por fecha
                        if (!isset($notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte])) {
                            $notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte] = [];
                        }
                        $notificacionesAgrupadas[$cuentaContrato]['fechas'][$fechaCorte][] = "$horaDesde - $horaHasta";
                    }
                }
            
                // Construir el mensaje
                $mensajebody = '';
                foreach ($notificacionesAgrupadas as $cuentaContrato => $info) {
                    $mensajebody .= "*Contrato*: $cuentaContrato\n";
                    $mensajebody .= "*Dirección*: {$info['direccion']}\n\n";
                    $mensajebody .= "*Detalles de Planificación*:\n";
                    foreach ($info['fechas'] as $fecha => $horas) {
                        $mensajebody .= "*$fecha*\n";
                        $mensajebody .= implode("\n", $horas) . "\n\n";
                    }
                }
                
                    //echo $mensajebody; // Mostrar el mensaje
                } else {
                    echo "Error en la consulta: " . $respuesta['mensajeError'];
                }
                }
                
                if (empty($mensajebody)) {
    $mensajebody = "No se encontraron resultados para el criterio ingresado.";
                    
                }
                //$mensajebody = str_replace(',', '.', $mensajebody);
                $mensajebodyEscapado = str_replace("\n", '\\n', $mensajebody);
                $mensaje = ''
                . '{'
                . '"messaging_product": "whatsapp", '
                . '"recipient_type": "individual",'
                . '"to": "' . $telefonoCliente . '", '
                . '"type": "text", '
                . '"text": '
                . '{'
                 . '     "body":"' . $mensajebodyEscapado . '",'
                . '     "preview_url": true, '
                . '} '
                . '}';
        }
        //DECLARAMOS LAS CABECERAS
        $header = array("Authorization: Bearer " . $token, "Content-Type: application/json",);
        //INICIAMOS EL CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //OBTENEMOS LA RESPUESTA DEL ENVIO DE INFORMACION
        $response = json_decode(curl_exec($curl), true);
        //OBTENEMOS EL CODIGO DE LA RESPUESTA
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //CERRAMOS EL CURL
        curl_close($curl);
        


}
