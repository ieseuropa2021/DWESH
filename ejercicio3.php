<?php
//incluimos los ficheros de configuración y utilidades
require_once './conf/defaultdbconf.php';
require_once './libs/utils.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Ejercicio 3, tarea 2,  DWES 2021/22. Autor: José Antonio Romero López </title>
        <style>
            body  {
                display: grid;
                justify-content:center;
            }

        </style>
    </head>

    <body>
        <H2>Ejercicio 3, tarea 2, DWES 2021/22. Autor: José Antonio Romero López</H2>
        <?php
        //Leemos lo parámetros de configuración de la conexión
        $configuracion = readConfig(FICHEROINI, $valoresdefecto);
        //Realizamos la conexión con dichos parámetros
        $conexion = connect($configuracion);
        
        if ($conexion) {//Si retorna una conexión PDO
            echo '<h3>Conexion realizada con éxito</b3>';
        } else {//Ha ociurrido un error en la conexión
            echo '<h3>Conexion realizada SIN  éxito.</b3>';                                
        }
        ?>
    </body>
</html>



