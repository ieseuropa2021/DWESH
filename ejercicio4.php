<?php
//incluimos los ficheros de configuración y utilidades
require_once './conf/defaultdbconf.php';
require_once './libs/utils.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Ejercicio 4, Tarea 2 ( DWES 2021/22, JARL) </title>
        <style>
            body  {
                display: grid;
                justify-content:center;
            }
            
            table {
                border: 1px solid black;
            }
            thead {
                background-color: yellow;
            }
            td {
                border: 1px solid grey;
                padding: 4px;
                text-align: center;
                background-color: aqua;
            }
        </style>
    </head>

    <body>
        <H2>Ejercicio 4, tarea 2, DWES 2021/22. Autor: José Antonio Romero López</H2>
        <?php
        //Arrays que cntendrán los datos correctos y errores si los hubiera en algún dato
        $datos = [];
        $errores = [];
        //incluimos el formulario
        readfile('./forms/ejercicio4.forms.html');
        //Comprobamos si hemos pulsado "Buscar"

        if (count($_POST) > 0) { //Si hemos pulsado Buscar
            // Validamos y saneamos los datos del formulario obteniendo los datos y, errores, si los hubiere
            //Los parámetros $datos y $errores se pasan por referencia para modificar su contenido en las funciones
            procesarEntero("user_id",$errores, $datos ); //función que comprueba que hemos leído un entero >=0
            procesarOrden($errores, $datos);//función que comprueba que hemos seleccionado un orden válido

            if (!$errores) {//Si no hay errores
                //Leemos lo parámetros de configuración de la conexión               
                $configuracion = readConfig(FICHEROINI, $valoresdefecto);

                //Realizamos la conexión con dichos parámetros
                $conexion = connect($configuracion);
                
                if ($conexion) { //Si se ha establecido la conexión  PDO
                    //Obtenemos el array con las reservas que cumplen la condición
                    $respuesta = consultarReservas($conexion, $datos);
                    $CabeceraTabla = true;
                    if (count($respuesta) > 0) {//Se devuelven registros para dichos valores, si los hay
                        echo '<br> Reservas realizadas por el usuario: '.$datos['user_id'].'<br/>';
                        foreach ($respuesta as $usuario) {
                            if ($CabeceraTabla) {//La primera vez mostramos la cabecera de la tabla
                                echo "<table>";
                                echo "<thead>";
                                echo '<TR>';
                                array_walk($usuario, function ($val, $key) {
                                    echo "<th>$key</th>";
                                });
                                echo '</TR>';
                                echo "</thead>";
                                echo "<tbody>";
                                $CabeceraTabla = false;
                            } //El reso de veces únicamente debemos mostrar los valores de cada reserva
                            echo '<TR>';
                            array_walk($usuario, function ($val, $key) {
                                echo "<td>$val</td>";
                            });
                            echo '</TR>';
                        }
                        echo "</tbody>";
                        echo '</TABLE>';
                    } else { //En el caso de que no haya reservas para dicho id, se muestra el mensaje correspondiente
                        echo "<h3>No existen registros con dicho Id de usuario: " . $datos['user_id']."<h3><br/>";
                    }
                } else {//No se ha podido establecer la conexión                                     
                    echo '<h3> Conexion realizada SIN  éxito </h2> ';
                }
            } else { //Existen errores en el validado y saneado
               
                echo "<h2> Errores detectados</h2>";
                foreach ($errores as $clave => $valor) { //Se muestran los errores detectados
                    echo "El valor del campo " . $clave . " no es válido:  " . $valor . "<br/>";
                }            
            } //Fin del mensaje con los errores   
        }//Fin de la validación de la información enviada por el formulario
        ?>
    </body>
</html>



