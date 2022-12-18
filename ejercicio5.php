<?php
/*
 * Fichero para resolver las tareas de la asignatura DWES 2021/22
 * 
 *  */
require_once './conf/defaultdbconf.php';
require_once './libs/funcionesHoras.php';//Se ha copiado de la tarea 1 y modificado una función: 
require_once './libs/utils.php';
//Leemos el fichero que contiene el formulario si no se ha leído antes
if (!isset($formulario)) {
    $formulario = file_get_contents(CARP_FORMULARIO . 'ejercicio5.forms.html', FALSE);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Ejercicio 5, Tarea 2 ( DWES 2021/22, JARL) </title>
        <style>
            body  {
                display: grid;
                justify-content:center;
            }
            label {
                display: block;
            }

        </style>
    </head>

    <body>
        <H2>Ejercicio 5, tarea 2, DWES 2021/22. Autor: José Antonio Romero López</H2>
        <?php
        //Valores por defecto que deben leerse
        $datosdefecto = ['zona_id' => '', 'user_id' => '', 'inicio' => '', 'fin' => '', 'fecha' => ''];

       //Comprobamos si hemos pulsado Enviar
        if (count($_POST) > 0) { //Si hemos pulsado Enviar en el formulario
            $errores = []; //No tenemos errores, en principio
            $datos = []; //no hay datos correctos, en principio

            //procesamos(saneamos y validamos) los datos recibidos de "user_id"        
            procesarEntero('user_id', $errores, $datos);
            //Comprobamos si es 0
            if (isset($datos['user_id']) && isset($errores['user_id'])) { //Eliminamos el error que mostraba, según el ejercicio 4: caso de tener valor 0
                unset($errores['user_id']); //Se elimina dicho error del array de errores
            }
            //Saneamos y validamos "zona_id", "inicio", "fin".
            procesarEntero('zona_id', $errores, $datos);
            procesarHora('inicio', $errores, $datos);
            procesarHora('fin', $errores, $datos);
            //Comprobamos que la hora final es >= hora inicial.  
            //Se podía haber incluido en una función distinta el código que sigue 
            if (empty($errores['inicio'])) {//Si la hora de inicio es correcta 
                if (empty($errores['fin'])) {//Si la hora final tambien es correcta, comprobamos que es >=inicial
                    //Se utiliza la función creada en la tarea 1, modificada para que devuelva false si no es un tramo de horas correcto
                    $validaHF = comprobarTramoHoras($datos['inicio'] . '-' . $datos['fin']);
                    if ($validaHF === false) {
                        //Si la hora final no es >= que la hora inicial
                        $errores['fin'] = 'La hora final ' . $datos['fin'] . ' debe ser mayor igual que la hora de inicio';
                        unset($datos['fin']); //Se eliminan dichos datos por no ser correctos
                    }
                } //Si hora final no es correcta ya ha quedado registrada
            } else {//Si la hora inicial no es válida, la hora final tampoco lo será
                $errores['fin'] = "Previamente debes introducir una hora de inicio válida";
                if (isset($datos['fin'])) 
                    unset($datos['fin']); //Puede haber sido dada como válida
            }//Fín de comprobación de las horas iniciales y finales

            //Saneamos y validamos la fecha
            procesarFechaCampo('fecha',$errores, $datos);

            //Finalmente comprobamos si hay errores
            if ($errores) { //Puesto que hay errores debemos modificar los datos del formulario, 
                // mostrando un mensaje con los errores correspondientes
                $formularioF = $formulario; //Inicalizamos el formulario final  
                //con el valor leído del fichero completándolo con los valores correctos

                foreach ($datosdefecto as $clave => $valor) {
                    if (isset($errores[$clave])) {//El reemplazo se puede realizar con la función str_replace o preg_replace
                        //Si existe un error para esa clave, se sustituye con el valor ''
                        $formularioF = str_replace('[[PREV:' . $clave . ']]', '', $formularioF);
                    } else {//Si no existe un error para esa clave, se sustituye con el valor correcto                         
                        $formularioF = str_replace('[[PREV:' . $clave . ']]', $datos[$clave], $formularioF);
                    }
                }
                //Mostramos de nuevo el formulario
                echo '<h3> Se procederá a la inserción de la siguiente reserva</h3>';
                echo $formularioF;
                //Mostramos los errores detectados
                echo '<h3> Errores detectados</h3>';
                foreach ($errores as $clave => $valor) {
                    echo "El valor del campo " . $clave . " no es válido.  " . $valor . "<br/>";
                }
            } else { //En caso de no existir errores en los datos, procedemos a insertarlos, si es posible
                //Leemos lo parámetros de configuración de la conexión               
                $configuracion = readConfig(FICHEROINI, $valoresdefecto);

                //Realizamos la conexión con dichos parámetros
                $conexion = connect($configuracion);

                if ($conexion) { //Si se ha establecido la conexión PDO
                        //Se llama a la función insertarReserva con la conexión PDO y los datos saneados y validados
                        $resultadoI = insertarReserva($conexion, $datos);
                        //Comprobamos el valor devuelto por la función insertarReserva
                        switch ($resultadoI) {
                            case -2:
                                echo "<h3>No se han recibido los datos correctos para realizar la reserva</h3>";
                                break;
                            case -1:
                                echo '<h3>El tramo indicado se pisa con otras reservas</h3>';
                                break;
                            case 0:
                                echo '<h3>Error en la inserción de la reserva</h3>';
                                break;
                            default:
                                echo "<h3>Reserva realizada con éxito:</h3>";
                                echo 'Número de reservas realizadas: '.$resultadoI;
                                break;
                        }                    
                } else {//No se ha obtenido una conexión PDO válida, mostrándose el error
                    echo '<h3> Errores detectados</h3>';
                    echo 'Conexion realizada SIN  éxito<br/>';
                    echo 'Revise los datos del fichero dbconf.ini o valores por defecto<br/>';
                }//Fin del establecimiento de la conexión
            }//Fín del procesamiento de los errores
        } else { //Hemos entrado por primera vez, mostramos el formulario inicial
            //En esta ocasión se ha empleado la función preg_replace puesto que 
            //deben reemplazarse todos los campos por ''. Si se desea emplear str_replace
            // el código sería:
            //  $formularioI=$formulario;
            //foreach ($datosdefecto as $clave => $valor) {
            //         $formularioI = str_replace('[[PREV:' . $clave . ']]', '', $formularioI);
            // }
            $formularioI = preg_replace('/\[\[PREV:.*\]]/', '', $formulario);
            //Se muestra el formulario
            echo '<h3> Se procederá a la inserción de la siguiente reserva</h3>';
            echo $formularioI;
        }//Fin del procesamiento del formulario
        ?>
    </body>
</html>

