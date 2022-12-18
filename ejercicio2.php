<?php
//incluimos los ficheros de configuración y utilidades
//Para que termine si no encuentra el fichero de las utilidades se emplea requiere_once en lugar de include_once
require_once './libs/utils.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Ejercicio 2, Tarea 2 ( DWES 2021/22, JARL) </title>
        <style>
            body {
                display: grid;
                justify-content:center;
            }            
        </style>
    </head>

    <body>
        <H2>Ejercicio 2, tarea 2, DWES 2021/22. Autor: José Antonio Romero López</H2>
        <?php
        //Establecemos los valores por defecto. En la resolución de los ejercicios 3, 4,5 y 6 
        //se incluye en un fichero de configuración junto con otras constantes
        $valoresdefecto['DB_DRIVER'] = 'mysql';
        $valoresdefecto['DB_HOST'] = 'localhost';
        $valoresdefecto['DB_PORT'] = '3306';
        $configuracion = readConfig('./conf/dbconf.ini', $valoresdefecto);
        if (!$configuracion) {//Si se produce un error en el fichero de entrada
            echo 'Error en el fichero de entrada';
        } else {//En otro caso se muestra la información de la conexión
            echo '<pre>';
            var_dump($configuracion);
            echo '</pre>';

            /**
             * Otra forma sería. 
             * En realidad es la qque pensé antes de releer el enunciado
             * del ejercicio y ver el foro. Debía emplear var_dump.
             * foreach($configuracion as $clave => $valor) {
             *         echo $clave.' => "'.$valor.'"'.'<br />';
             *                }  
             * 
             */
        }
        ?>
    </body>
</html>
