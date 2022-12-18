<?php
//Valores por defecto para la actividad 3, 4, 5 y 6
$valoresdefecto['DB_DRIVER']= 'mysql';
$valoresdefecto['DB_HOST']= 'localhost';
$valoresdefecto['DB_PORT']='3306';
//Constantes empleadas en la resolución de las actividades 4,5,6 de la tarea 2, DWES 2021/22
define('FICHEROINI','./conf/dbconf.ini');
define('CARP_FORMULARIO', './forms/');
define('VALORESORDENES',["0","A","X"]);
define('EXP_REG_FECHA','/^( )*\d{1,2}\/\d{1,2}\/\d{4}( )*$/');
define('EXP_REG_HORA', '/^( )*\d{1,2}\:\d{1,2}( )*$/');
define('EXPRESIONREGULARTRAMO', '/^\d+:\d+-\d+:\d+$/');

