<?php
/*
 * Fichero para resolver las tareas de la asignatura DWES 2021/22
 * Seguiré la misma estrategia que en el ejercicio 5, modificando el formulario 
 * inicial, añadiendole los caracteres comodines 
 */
require_once './conf/defaultdbconf.php';
require_once './libs/funcionesHoras.php';
require_once './libs/utils.php';
//Leemos el formulario inicial
if (!isset($formulario)) {
    $formulario = file_get_contents(CARP_FORMULARIO . 'ejercicio6.forms.html', FALSE);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Ejercicio 6, Tarea 2 ( DWES 2021/22, JARL) </title>
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
        <H2>Ejercicio 6, tarea 2, DWES 2021/22. Autor: José Antonio Romero López</H2>
        <?php
        $datosdefecto = ['zona_id' => '', 'fecha_actual' => '', 'inicio_actual' => '', 'nuevo_inicio' => '', 'nuevo_fin' => ''];

        if (count($_POST) > 0) { //Si hemos pulsado Enviar en el formulario
            $errores = [];
            $datos = [];

            //procesamos(saneamos y validamos) los datos recibidos
            //
            //Procesado y validado de la zona
            procesarEntero('zona_id', $errores, $datos);
            if (isset($datos['zona_id']) && isset($errores['zona_id'])) {//Si se ha introducido un cero, es válido
                unset($errores['zona_id']);
            }

            //Procesado y validado de la fecha_actual
            procesarFechaCampo('fecha_actual', $errores, $datos);

            //Procesado y validado de la hora inicio_actual
            procesarHora('inicio_actual', $errores, $datos);

            //Procesado y validado de la hora nuevo_inicio
            if (trim($_POST['nuevo_inicio']) === '') {
                $datos['nuevo_inicio'] = '';
            } else {
                procesarHora('nuevo_inicio', $errores, $datos);
            }

            //Procesado y validado de la hora nuevo_fin; faltaría comprobar que nuevo fin >= nuevo inicio
            if (trim($_POST['nuevo_fin']) === '') {
                $datos['nuevo_fin'] = '';
            } else {
                procesarHora('nuevo_fin', $errores, $datos);
            }

            //Comprobamos que si ambas (nuevo_inicio y nuevo_fin) son  vacías
            //o la nueva hora final es >= nueva hora inicio

            if (isset($datos['nuevo_inicio']) && $datos['nuevo_inicio'] === '' && isset($datos['nuevo_fin']) && $datos['nuevo_fin'] === '') {
                //todo es correcto, se supone que se va a eliminar una reserva
            } elseif (!empty($datos['nuevo_inicio']) && !empty($datos['nuevo_fin'])) {
                //Se supone que vamos a modificar datos,  si los datos son correctos, es decir, nueva hora final>=nueva hora inicio                 
                //Se podía haber incluido en una función distinta el código que sigue
                if (empty($errores['nuevo_inicio'])) {//Si la hora de inicio es correcta 
                    if (empty($errores['nuevo_fin'])) {//Si la hora final tambien es correcta, comprobamos que es >=inicial
                        //Se utiliza la función creada en la tarea 1, modificada para que devuelva false si no es un tramo de horas correcto
                        $validaHF = comprobarTramoHoras($datos['nuevo_inicio'] . '-' . $datos['nuevo_fin']);
                        if ($validaHF === false) {
                            //Si la hora final no es >= que la hora inicial
                            $errores['nuevo_fin'] = 'La hora final ' . $datos['nuevo_fin'] . ' debe ser mayor igual que la hora de inicio';
                            unset($datos['nuevo_fin']); //Se eliminan dichos datos por no ser correctos
                        }
                    } //Si hora final no es correcta, ya ha quedado registrada
                } else {//Si la hora inicial no es válida, la hora final tampoco lo será
                    $errores['nuevo_fin'] = "Previamente debes introducir una hora de inicio válida";
                    if (isset($datos['nuevo_fin']))
                        unset($datos['nuevo_fin']); //Puede haber sido dada como válida
                }//Fin de la comprobación de que la nueva hora inicial y final son válidas
            } else {//En este caso puede que existan errores; solo quedan dos posibilidades: uno vacío y otro no
                if (isset($datos['nuevo_inicio']) && $datos['nuevo_inicio'] === '') {//Si la hora nuevo inicio está vacía
                    if (!empty($datos['nuevo_fin'])) {//La hora nuevo fin  es no vacía----> error
                        $errores['nuevo_fin'] = 'Previamente debes introducir una Nueva hora inicio válida(no vacía) o dejar en blanco Nueva hora fin para una eliminación';
                        unset($datos['nuevo_fin']);
                    }
                } elseif (isset($datos['nuevo_fin']) && $datos['nuevo_fin'] === '') {//Si la hora nuevo fin está vacía
                    if (!empty($datos['nuevo_inicio'])) {//La hora nuevo inicio es no vacía ----> error
                        $errores['nuevo_fin'] = 'Debes introducir una Nueva hora fín no vacía';
                        unset($datos['nuevo_fin']);
                    }
                }// Fin de la comprobación de los posibles errores si uno es vacío
            }//Fin del if sobre comprobación de las horas de nuevo inicio y nuevo fín
            //Final de validación y saneado de los datos recibidos
            
            //Comenzamos con el procesado del formulario: sus errores o acción correspondiente

            if ($errores) { //Puesto que hay errores debemos modificar el formulario, mostrando el mensaje correspondiente 
                $formularioF = $formulario; //Inicalizamos el formulario final con el valor leído del fichero
                //Lo modificamos para que tenga una estructura semejante al ejercicio 5, conservando los datos correctos            

                foreach ($datosdefecto as $clave => $valor) {
                    $cadenabusqueda = 'id="' . $clave . '"';
                    if (isset($errores[$clave])) {//SI había un error, lo dejamos en blanco
                        $formularioF = str_replace($cadenabusqueda, $cadenabusqueda . ' value=""', $formularioF);
                    } else {//Si no era erróneo el dato, se conserva
                        $formularioF = str_replace($cadenabusqueda, $cadenabusqueda . ' value="' . $datos[$clave] . '"', $formularioF);
                    }
                }

                //Mostramos de nuevo el formulario
                echo '<h3>Formulario para eliminar o modificar una reserva</h3>';
                echo $formularioF;
                //Mostramos los errores detectados
                echo '<h3>Errores detectados </h3>';
                foreach ($errores as $clave => $valor) {
                    echo "El valor del campo " . $clave . " no es válido.  " . $valor . "<br/>";
                }
            } else { //En caso de no existir errores en los datos, procedemos a eliminarlos/modificarlos, si es posible
                //Leemos lo parámetros de configuración de la conexión               
                $configuracion = readConfig(FICHEROINI, $valoresdefecto);

                //Realizamos la conexión con dichos parámetros
                $conexion = connect($configuracion);

                if ($conexion) { //Si se ha establecido la conexión PDO
                    //Comprobamos que deseamos eliminar
                    if ($datos['nuevo_inicio'] === '' && $datos['nuevo_fin'] === '') {
                        //Se supone que se va a eliminar la reserva indicada

                        $resultadoE = eliminarReserva($conexion, $datos);
                        //Mostramos el mensaje correspondiente a la operación realizada
                        switch ($resultadoE) {
                            case -3:
                                echo '<h3>Error al intentar borrar los datos seleccionados</h3>';
                                break;
                            case -2:
                                echo '<h3>Se produce un error en la consulta/selección sobre los datos</h3>';
                                break;
                            case -1:
                                echo '<h3>Los datos de la función no son correctos</h3>';
                                break;
                            case 0:
                                echo '<h3>No existen registros para esa reserva</h3>';
                                break;
                            case 1:
                                echo '<h3>Reserva eliminada con éxito, nº registros eliminados: ' . $resultadoE . '</h3>';
                                break;
                        }
                    }//Fin de la eliminación de una reserva 
                    else {//Se supone que vamos a modificar una reserva
                        //Comprobamos el resultado del intento de modificar 
                        $resultadoM = modificarReserva($conexion, $datos);
                        switch ($resultadoM) {                            
                            case -3:
                                echo '<h3>Error al intentar modificar los datos seleccionados</h3>';
                                break;
                            case -2:
                                echo '<h3>Los datos se pisan con otras reservas</h3>';
                                break;
                            case -1:
                                echo '<h3>Los datos recibidos por la función no son correctos</h3>';
                                break;
                            case 0:
                                echo '<h3>No existen reservas para esa fecha, por tanto, no se puede modificar nada</h3>';
                                break;
                            case 1:
                                echo '<h3>Reserva modificada con éxito, nº registros modificados: ' . $resultadoM . '</h3>';
                                break;                            
                        }
                    }
                } else {//Si hay errores en la conexión PDO
                    echo "<h3> Errores detectados en la eliminación/modificación de reservas</h3>";
                    echo 'Conexion realizada SIN  éxito';
                }//Fin del if de la conexión
            }//Fin del else de la eliminación/modificación
        } else { //Hemos entrado por primera vez, mostramos el formulario inicial
            //Lo modificamos para que tenga una estructura semejante al ejercicio 5  
            $formularioI = $formulario; //Inicializamos el formulario final con el valor leído del fichero
            //Lo modificamos para que tenga una estructura semejante al ejercicio 5, 
            foreach ($datosdefecto as $clave => $valor) {
                $cadenabusqueda = 'id="' . $clave . '"';
                $formularioI = str_replace($cadenabusqueda, $cadenabusqueda . ' value=""', $formularioI);
            }

            //Mostramos el formulario
            echo '<h3>Formulario para eliminar o modificar una reserva</h3>';
            echo $formularioI;
        }
        ?>
    </body>
</html>


