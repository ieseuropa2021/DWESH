<?php

/*
 * Fichero que contiene las funciones auxiliares para resolver la tarea 2, DWES 2021/22
 */


/*
 * Función que nos permitirá leer un fichero de configuración
 * @param $file : es un string con la ruta al archivo .ini a leer
 * @param $defaultConfig : valor por defecto para ciertos parámetros (se usarán en 
 * caso de que no se indiquen en el archivo .ini).
 * @return : devuelve un array asociativo con la configuración
 *  del archivo .ini combinada con la configuración por defecto, 
 * teniendo preferencia la configuración indicada en el archivo .ini.
 * Si se produce un error en la lectura del fichero retornará un array vacío(false)
 */

function readConfig(string $file, array $defaultConfig) {
    $contenido = parse_ini_file($file, false); //Obtenemos un array asociativo con las claves y valores

    if (!$contenido) {//Si se produce un error al procesar el fichero .ini
        // Se devueve un array vacío
        $contenido = array();
    } else {//Si se ha leído el fichero de configuración
        /**
         * En un principio pensé realizarlo de esta forma
         * foreach ($defaultConfig as $clave => $valor) {
         *            if(!array_key_exists($clave,$contenido)) {
         *                $contenido[$clave]=$defaultConfig[$clave];
         *            }
         *        }
         */
        //Añadimos a la configuración por defecto el contenido del fichero .ini
        $contenido = array_merge($defaultConfig, $contenido);
    }
    return $contenido;
}

/**
 * Datos para el ejercicio 2 de la tarea 2
 * CREATE SCHEMA `convecinos`;
 * 
 * CREATE TABLE reservas ( 
 * inicio TIME NOT NULL,
 * fin TIME NOT NULL,
 * fecha DATE NOT NULL,
 * user_id INT NOT NULL,
 * zona_id INT NOT NULL,
 * creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
 * CONSTRAINT PK_RESERVAS PRIMARY KEY (inicio,fecha,zona_id),
 * CONSTRAINT CHK_INIFIN CHECK (fin>inicio)
 * );
 * 
 * Datos de prueba
 * insert into reservas (inicio, fin, fecha, user_id, zona_id) values ('10:00', '10:59', '2022-01-20', 10,2);
 * insert into reservas (inicio, fin, fecha, user_id, zona_id) values ('12:00', '12:59', '2022-01-20', 11,2);
 * insert into reservas (inicio, fin, fecha, user_id, zona_id) values ('16:30', '17:29', '2022-01-20', 12,2);
 * insert into reservas (inicio, fin, fecha, user_id, zona_id) values ('9:00', '9:59', '2022-01-21', 12,2);
 * insert into reservas (inicio, fin, fecha, user_id, zona_id) values ('11:00', '11:59', '2022-01-21', 11,2);
 * insert into reservas (inicio, fin, fecha, user_id, zona_id) values ('17:30', '18:29', '2022-01-21', 10,2);
 *
 *   
 */
/*
 * Funciones para resolver el ejercicio 3, 4, 5 y 6
 * 
 * 
 * Función connect (array $dbconf) retorna una conexión PDO para la base de datos.
 * Los datos a utilizar para conectar a la base de datos serán exclusivamente 
 * los pasados por parámetro en forma de array asociativo (que seguirán la 
 * línea de los datos configurados en el archivo dbconf.ini).
 * En caso de que la conexión no pueda realizarse, el método deberá retornar false.
 *  Ten en cuenta que la función deberá verificar que en array asociativo pasado
 *  por parámetro ($dbconf) se proporcionan todos los datos necesarios para 
 * realizar la conexión: driver,host, puerto, nombre del esquema, usuario y password.
 * Esta función, además deberá indicar el siguiente atributo a la hora de crear 
 * la conexión PDO con la base de datos:
 * array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION). El parámetro 
 * array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXC ) hará que cuando se produzca un 
 * error al usar PDO para realizar una consulta o al establecer la conexión con
 * la base de datos, se genere una excepción (que podrás capturar usando try-catch):
 * @param $dbconf   array con los datos necesarios para la conexión.
 * @return conexión PDO que será false si hay algún error en el fichero de configuración
 *  o un código del error producido en el intento de conexión. En otro caso, una conexión PDO
 */
function connect(array $dbconf) {
    $devuelveconexion = false;  //En principio supongo que no es posible
//claves o parámetros que deben incluirse en el array $dbconf
    $parametros = ['DB_DRIVER', 'DB_HOST', 'DB_PORT', 'DB_SCHEMA', 'DB_USER', 'DB_PASSWORD'];

//Si $dbconf tiene 6 parámetros y estos coinciden con los esperados, se intenta realizar la conexión
    if (count(array_diff($parametros, array_keys($dbconf))) == 0 && count($dbconf) == 6) {
        try {
//Formamos la primera parte de la conexión
            $cadenaconexion = $dbconf['DB_DRIVER'] . ':host=' . $dbconf['DB_HOST'] . ';port=' . $dbconf['DB_PORT'] . ';dbname=' . $dbconf['DB_SCHEMA'];
//Intentamos realizar la conexión con los datos de la configuración
            $devuelveconexion = new PDO($cadenaconexion, $dbconf['DB_USER'], $dbconf['DB_PASSWORD'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) { //Si no es factible la conexión, se muestra el error correspondiente para probarla en la depuración
            //echo "<H3>Error en la conexión:</H3>";
            //echo "Datos de la conexión realizada: <PRE>";
            //foreach ($dbconf as $clave => $valor) {
            //  echo $clave . ' = "' . $valor . '"' . '<br />';
            //echo 'Nº código del error: ' . $e->getCode();
            //Se devuelve false, según el enunciado del ejercicio.
            $devuelveconexion = false; //Se devuelve false
        } //Fín de la captura de la excepción del esablecimiento de la conexión
    } //Fín de la comprobación de los parámetros recibidos
    //Se podrían haber especificado otros valores de retorno distintos para controlar aún más el error
    return $devuelveconexion;
}

/*
 * Funciones para resolver el ejercicio 4
 */
/*
 * Función consultarReservas. 
 * 
 * 
 * @param PDO pdo   conexión a la BBDD
 * @param array $datos  es un array que contiene "user_id" y "orden". "user_id" es el id de usuario en la BBDD que será introducido en el formulario
 * @return array $salida  contiene una array con los resultados de la consulta
 * 
 */

function consultarReservas(PDO $con, array $dato) {
    $salida = []; //En principio es un array vacío, no existen registros
    $datodefecto = ['user_id', 'orden'];//Valores de las claves que debemos recibir en $dato
    try {
        //Comprobamos que hemos recibido los datos correctos
        if (count(array_diff($datodefecto, array_keys($dato))) == 0 && count($dato) == 2) {
            //Establecemos la conexión si se han recibido los datos correctos
            $con->beginTransaction();

            if ($dato['orden'] === "0") {
                $seleccion = "SELECT fecha, inicio, fin FROM reservas WHERE user_id=:miid order by
timestamp(fecha,inicio) DESC;";
            } elseif ($dato['orden'] === "A") {
                $seleccion = "SELECT fecha, inicio, fin from reservas WHERE user_id=:miid order by
timestamp(fecha,inicio) ASC;";
            } elseif ($dato['orden'] === "X") {
                $seleccion = "SELECT fecha, inicio, fin from reservas WHERE user_id=:miid ;";
            }
            //Preparamos la consulta
            $seleccionados = $con->prepare($seleccion);
            //Asignamos al parámetro el valor de la variable
            $seleccionados->bindParam(":miid", $dato['user_id']);
            //Se ejecuta la consulta y si se obtiene algún registro (reserva). Modificada tras
            //las aclaraciones en el foro
            $seleccionados->execute();
            $salida=$seleccionados->fetchAll(PDO::FETCH_ASSOC);
            //if ($seleccionados->execute()) { //Mientras haya reservas, se almacenan en $resultado
              //  while ($resultado = $seleccionados->fetch(PDO::FETCH_ASSOC)) { //una reserva en cada iteración
                //    $salida[] = $resultado;  //Se añaden al array de salida
               //}
            //}
        } else {//No hemos recibido los datos correctos
            //No se especifica que debamos realizar una acción concreta
        }
    } catch (Exception $ex) {//Se produce un error al realizar la consulta
        //No se especifica que debamos realizar una acción concreta
    }
    //Devolvemos el resultado de la consulta
    return $salida;
}

/*
 * Esta función se ha pensado para validar campos cuyo valor deba ser un entero >=0
 * Función que sanea y valida el id de usuario y zona_id (ambos son enteros )
 * @param string $campo indicará el campo que será validado y saneado
 * @param array $error de errores pasado por referencia para modificar su contenido si
 *             no es un id válido
 * @param array $datos de datos pasado por referencia para modificar su valor con el dato correcto
 * 
 */

function procesarEntero(string $campo, array &$error, array &$dato) {
//Comprobamos que hemos recibido un campo válido 
    if (isset($_POST[$campo])) {
//$valorentero = trim($_POST['user_id']);  Eliminar los espacios blancos antes y despues. No sentido puesto que ya lo hace filter_input
//filtramos que sea un número entero >=0;  
        $valorentero = filter_input(INPUT_POST, $campo, FILTER_VALIDATE_INT, ["options" => ['min_range' => 0]]);
        if ($valorentero === false || $valorentero < 0) {
            $error[$campo] = " No es un número entero >=0,  " . $_POST[$campo];
        } elseif ($valorentero == 0) {
            $error[$campo] = " No puede ser 0 ";
            $dato[$campo] = 0; //Se almacena dicho valor para el ejercicio 5 aunque en el 4 debe mostrar el error.
        } else { //En otro caso devolverá el valor leído
            $dato[$campo] = $valorentero;
        }
    } else {//Si no se envía dicho campo
        $error[$campo] = "Debes especificar un valor para el campo '.$campo.' ,debe ser entero ";
    }
}

/*
 * Función que comprueba que se recibe un orden correcto (ascendente, descendente o sin orden)
 * @param array $error  un array pasado por referencia con los posibles errores
 * @param array $dato   un array pasado por referencia los posibles datos recibidos
 */

function procesarOrden(array &$error, array &$dato) {
//Comprobamos que hemos recibido un orden correcto
//ordenesvalidos = ["0", "A", "X"]; 
    if (isset($_POST['orden']) && in_array($_POST['orden'], VALORESORDENES)) {
        $dato['orden'] = $_POST['orden'];
    } else {
        $error['orden'] = "No se ha seleccionado un orden válido: " . $_POST['orden'] . '  posición de pruebas o errónea<br/>';
    }
}

/* 
 * Funciones para resolver las actividades 5 y 6
 * 
 */



/*
 * Función que comprueba que se recibe una hora de formato correcto HH:MM
 * @param array $error  un array pasado por referencia con los posibles errores
 * @param array $dato   un array pasado por referencia, los datos recibidos si son correctos. 
 * @param string $hora  contiene el campo que debe recibirse vía POST 
 */

function procesarHora(string $hora, array &$error, array &$dato) {
//Comprobamos que hemos recibido una hora correcta, en el string $hora 
    if (isset($_POST[$hora])) {
        $datorecibido = filter_input(INPUT_POST, $hora, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => EXP_REG_HORA]]);
        if ($datorecibido === false || $datorecibido === null) { //no verifica la expresión regular
            $error[$hora] = " No es una hora válida " . $_POST[$hora] . ', su formato es HH:MM';
        } else { //Aunque cumpla la expresión regular, debemos comprobar que es una hora correcta
            $datorecibido = trim($datorecibido);//eliminamos los espacios iniciales y finales
            $valorminutos = convertirHoraAMinutos($datorecibido); //Convertimos la expresión HH:MM, en minutos.
            if ($valorminutos === false) {//Si la conversión no ha tenido éxito, no es una hora válida
                $error[$hora] = ' No es una hora válida para el campo ' . $hora . " , $_POST[$hora]" . ', su formato estará entre 00:00 y 23:59';
            } else {//Si la conversión es correcta, devolvemos dicho valor recibido, sin espacios iniciales-finales
                $dato[$hora] = $datorecibido;
            }
        }//Fin del proceso del dato recibido en $_POST[$hora]
    } else {//Si no existe la hora recibida en el POST
        $error[$hora] = 'Debes especificar una hora para el campo ' . $hora . ' ,con el formato HH:MM';
    }
}

/*
 * Función que comprueba que se recibe una fecha correcta
 * @param $campo  es un string que contiene el nombre del campo recibido vía POST
 * @param array $error  un array pasado por referencia con los posibles errores
 * @param array $dato   un array pasado por referencia, los datos recibidos. 
 * 
 * Se emplea filter_input, no eliminaba los espacios en blanco iniciales y finales
 */



function procesarFechaCampo(string $campo, array &$error, array &$dato) {
//Comprobamos que hemos recibido ese campo vía POST 
    if (isset($_POST[$campo])) {
        //Comprobamos que es una fecha
        $datorecibido = filter_input(INPUT_POST, $campo, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => EXP_REG_FECHA]]);

        if ($datorecibido) { //Los datos recibidos verifican la expresión regular de una fecha
            $datorecibido = trim($datorecibido); //eliminamos los espacios en blanco iniciales y finales
            list($dd, $mm, $aa) = explode('/', $datorecibido); //separamos días, mes y año
            if (checkdate(intval($mm), intval($dd), intval($aa))) {//Validamos si es una fecha correcta
                $dato[$campo] = $datorecibido; //En este caso tenemos una fecha correcta
            } else {//En este caso, no es una fecha correcta aunque cumpla la expr. regular
                $error[$campo] = 'La expresión ' . $_POST[$campo] . ' no es una fecha válida. El formato es dd/mm/aaaa';
            }
        } else { //Los datos recibidos no verifican la expr. regular de una fecha
            $error[$campo] = 'La expresión: ' . $_POST[$campo] . ' no verifica la expresión regular de la fecha. El formato es dd/mm/aaaa';
        }
    } else {//No se ha recibido una fecha
        $error[$campo] = 'Debe introducir una fecha en el campo '.$campo. '. El formato es dd/mm/aaaa';
    }
}

/*
 * La función insertarReserva debe recibir la conexión PDO a la base de datos 
 * por parámetro, así como el resto de datos a insertar en la base de datos. 
 * Nunca debe crear internamente una conexión a la base de datos, ni usar los 
 * datos del array $_POST directamente.Deberá realizar lo siguiente:
 * 1. Se inicia una transacción.
 * 2. Se rescatan las reservas del día (solo hora de inicio y hora de fin).
 * 3. Se verifica que la reserva a insertar no pisa ninguna de las reservas 
 * ya existentes ese día.
 * 4. Se inserta la nueva reserva solo si no pisa ninguna reserva anterior.
 * 5. Se realiza un rollback o un commit de la transacción según corresponda.
 * @param PDO $con  conexión PDO
 * @param array $datos  es un array con los datos necesarios para realizar una inserción
 * @return $resultadoinserción (1= inserción correcta, 0= problema en la inserción, -1= se pisan los datos con alguna reserva
 * 
 */

function insertarReserva(PDO $con, array $datos) {
    $resultadoinsercion = false;
    $clavesdatos = ['user_id', 'zona_id', 'inicio', 'fin', 'fecha'];
//Comprobamos que recibimos los datos correctos
    try {
        if (count(array_diff($clavesdatos, array_keys($datos))) == 0 && count($datos) == 5) {
//Creamos una variable tipo fecha con el formato aaaa-mm-dd a partir de la entrada
            $fechades = explode('/', $datos['fecha']);
//Creo que sería más eficiente $mifecha2=$fechades['2'] . '-' . $fechades[1] . '-' .$fechades[0]
            $mifecha = date_create($fechades['2'] . '/' . $fechades[1] . '/' . $fechades[0]);
            $mifecha2 = date_format($mifecha, 'Y-m-d'); //Obtenemos la fecha en el formato deseado
// Creamos el tramo nuevo que deseamos insertar
            $tramonuevo = $datos['inicio'] . '-' . $datos['fin'];
//Comenzamos con las transacciones
            $con->beginTransaction();

//Seleccionamos las reservas para esa fecha
            /*
             * Al seleccionar únicamente la fecha se puede dará el caso que se responda que no
             * se puede insertar una reserva para una zona distinta a la misma hora.
             * EN el enunciado no aparece que se debe controlar esta situación.
             * Lo único que habría que modificar sería que la condición sería 
             * WHERE fecha='$mifcha2' AND zona_id='$mizona'
             */

            $seleccion = <<<SQL
            SELECT inicio, fin FROM reservas WHERE fecha='$mifecha2'
SQL;

            $seleccionados = $con->query($seleccion);
//Comprobamos que existen reservas para la fecha indicada; en ese caso, 
//debemos comprobar que no pisa con el nuevo tramo a insertar

            $puedoinsertar = true; //En principio supongo que puedo insertar


            if ($seleccionados) {//Vamos comparando cada reserva almacenada para esa fecha
                $reserva = $seleccionados->fetch(PDO::FETCH_ASSOC);
//Comprobamos que dicha reserva no es pisada por los datos nuevos        
                while ($reserva != null && $puedoinsertar) {//Mientras haya reservas y no se pise 
//El tramo reservado es igual a la concatenación de las horas de inicio y fin (5 primeros caracteres
                    $tramoreservado = substr($reserva['inicio'], 0, 5) . '-' . substr($reserva['fin'], 0, 5);
//Comprobamos si se pisan el nuevo tramo y el reservado.
//La función comprobarSiSePisanTramosHoras se ha modificado para que devuelva false si no se pisan
                    $comprobante = comprobarSiSePisanTramosHoras($tramonuevo, $tramoreservado);

                    if ($comprobante !== false) {//Si se pisan, no debo seguir comprobando
                        $puedoinsertar = false;
                    } else {//Seguimos con el resto de tramos, si los hay
                        $reserva = $seleccionados->fetch(PDO::FETCH_ASSOC);
                    }
                }  //Final de la comprobación con las reservas existentes(while)
            } //Fin de la comprobación de si existen reservas para la fecha dada

            if ($puedoinsertar) { //Si no hay reservas o no se pisan, podremos insertar los valores dados
//Preparamos la inserción de los datos            
                $inserto = 'INSERT INTO reservas (user_id, zona_id, inicio, fin, fecha) values (:usuario, :zona, :miini, :mifin, :mifecha)';
                $pdoStmt1 = $con->prepare($inserto);

                $user = $datos['user_id'];
                $pdoStmt1->bindParam('usuario', $user);

                $mizona = $datos['zona_id'];
                $pdoStmt1->bindParam('zona', $mizona);

                $ini = $datos['inicio'];
                $pdoStmt1->bindParam('miini', $ini);

                $fin = $datos['fin'];
                $pdoStmt1->bindParam('mifin', $fin);

                $pdoStmt1->bindParam('mifecha', $mifecha2);

                try {
                    if ($pdoStmt1->execute()) {//Ejecutamos la inserción
                        $resultadoinsercion = $pdoStmt1->rowCount();
                    }
                    $con->commit(); //Confirmamos 
//$resultadoinsercion = $pdoStmt1->rowCount();
                } catch (PDOException $e) {//Si hay algún error en el intento de inserción
                    $resultadoinsercion = 0;
                    $con->rollBack(); //Deshacemos la transacción
                }
            } else {//Si se pisa con alguna(s) de las reservas
                $resultadoinsercion = -1;
                $con->rollBack(); //Deshacemos la transacción
            }
        }
    } catch (Exception $ex) {//No se han recibido los datos correctos
        $resultadoinsercion = -2;
        $con->rollBack();
    }

    return $resultadoinsercion;
}

/*
 * La función eliminarReserva debe recibir la conexión PDO a la base de datos 
 * por parámetro, así como el resto de datos a eliminar en la base de datos. 
 * Nunca debe crear internamente una conexión a la base de datos, ni usar los 
 * datos del array $_POST directamente.Deberá realizar lo siguiente:
 * Se elimina la reserva para la zona, fecha_actual y inicio_actual
 * indicados en el formulario sin utilizar transacciones.
 * 
 * @param PDO $con  conexión PDO
 * @param array $datos  es un array con los datos necesarios para realizar una inserción
 * @return $resultadoinserción (-3=error al borrar los datos seleccionados, 
 *         -2=Se produce un error en la consulta/selección sobre los datos
 *          -1=Los datos recibidos por la función no son correctos
 *          0= No existen registros para esa reserva
 *          1=Nº de registros insertados
 * 
 */

function eliminarReserva(PDO $con, array $datos) {
    $resultadoeliminacion = false;
    $clavesdatos = ['zona_id', 'fecha_actual', 'inicio_actual', 'nuevo_inicio', 'nuevo_fin'];

    try {    //Comprobamos que recibimos los datos correctos
        if (count(array_diff($clavesdatos, array_keys($datos))) == 0 && count($datos) == 5) {
//Creamos una variable tipo fecha con el formato aaaa-mm-dd a partir de la entrada             

            $fechades = explode('/', $datos['fecha_actual']);
//Creo que sería más eficiente $mifecha2=$fechades['2'] . '-' . $fechades[1] . '-' .$fechades[0]
            $mifecha = date_create($fechades['2'] . '/' . $fechades[1] . '/' . $fechades[0]);
            $mifecha2 = date_format($mifecha, 'Y-m-d'); //Obtenemos la fecha en el formato deseado
            $ini = $datos['inicio_actual'];
            $zon = $datos['zona_id'];
//Seleccionamos las reservas para esa fecha
            $con->beginTransaction();

            $seleccion = 'SELECT fecha FROM reservas WHERE fecha=:mifecha AND zona_id=:mizona AND  inicio=:miini';

            $seleccionados = $con->prepare($seleccion);

//Asignamos los valores para la selección
            $seleccionados->bindParam('mifecha', $mifecha2);
            $seleccionados->bindParam('mizona', $zon);
            $seleccionados->bindParam('miini', $ini);

            //Ejecutamos la selección
            if ($seleccionados->execute() && $seleccionados->fetchColumn() > 0) {//Eliminamos los seleccionados
                $borrar = 'DELETE FROM reservas WHERE fecha=:mifecha AND zona_id=:mizona AND  inicio=:miini';
                $pdoborrar = $con->prepare($borrar);
//Asignamos el valor del parámetro
                $pdoborrar->bindParam('mifecha', $mifecha2);
                $pdoborrar->bindParam('mizona', $zon);
                $pdoborrar->bindParam('miini', $ini);

                try {//Intentamos borrar los registros
                    if ($pdoborrar->execute()) {//retorna el número de registros borrados
                        $resultadoeliminacion = $pdoborrar->rowCount();
                    }
                    $con->commit();//Confirmamos la transacción
                } catch (PDOException $e) { //Error al intentar borrar los datos seleccionados
                    $resultadoeliminacion = -3;
                    $con->rollBack();//Deshacemos la transacción
                }
            } //Fin de la comprobación sobre de si existen reservas para la fecha dada
            else {//No existen registros para esa reserva
                $resultadoeliminacion = 0;
                $con->rollBack();//Deshacemos la transacción, 
            }
        } else {//Los datos de la función no son correctos
            $resultadoeliminacion = -1;
        }
    } catch (Exception $ex) {//Se produce un error en la consulta/selección sobre los datos
        $resultadoeliminacion = -2;
        //$con->rollBack();
    }
    return $resultadoeliminacion;
}

/*
 * La función modificarReserva debe recibir la conexión PDO a la base de datos 
 * por parámetro, así como el resto de datos a modificar en la base de datos. 
 * Nunca debe crear internamente una conexión a la base de datos, ni usar los 
 * datos del array $_POST directamente.Deberá realizar lo siguiente:
 * 1. Se inicia una transacción.
 * 2. Se comprueba si la reserva a modificar existe o no. Si la reserva a 
 * modificar no existiese, se realizaría un rollback y se finalizaría la 
 * ejecución de la función.
 * 3. Se rescatan las reservas restantes del día para esa zona (solo hora de 
 * inicio y hora de fin), excluyendo la reserva a modificar.
 * 4. Se verifica que la modificación de la reserva no pisa ninguna de las 
 * reservas restantes de ese día.
 *      1. Si la modificación de la reserva pisa otra reserva, se realizará un rollback.
 *      2. Si la modificación de la reserva no pisa otra reserva, se modifica 
 * la reserva existente y se realizará un commit.
 * 
 * 
 * @param PDO $con  conexión PDO
 * @param array $datos  es un array con los datos necesarios para realizar una inserción
 * @return $resultadoinserción (1= eliminación correcta, 0= problema en la eliminación, 
 * false=error en los datos de entrada o bien el número de registros elimnados
 * 
 */

function modificarReserva(PDO $con, array $datos) {
    $resultadomodificacion = false;
    $clavesdatos = ['zona_id', 'fecha_actual', 'inicio_actual', 'nuevo_inicio', 'nuevo_fin'];
   
    //Comprobamos que recibimos los datos correctos
    if (count(array_diff($clavesdatos, array_keys($datos))) == 0 && count($datos) == 5) {
        //Creamos una variable tipo fecha con el formato aaaa-mm-dd a partir de la entrada             

        $fechades = explode('/', $datos['fecha_actual']);
        //Creo que sería más eficiente $mifecha2=$fechades['2'] . '-' . $fechades[1] . '-' .$fechades[0]
        $mifecha = date_create($fechades['2'] . '/' . $fechades[1] . '/' . $fechades[0]);
        $mifecha2 = date_format($mifecha, 'Y-m-d'); //Obtenemos la fecha en el formato deseado
        $ini = $datos['inicio_actual'];
        $zon = $datos['zona_id'];
        //Seleccionamos las reservas para esa fecha, iniciando una transición
        $con->beginTransaction();

        $seleccion = 'SELECT fecha, inicio, fin  FROM reservas WHERE fecha=:mifecha AND zona_id=:mizona AND inicio=:miini';

        $seleccionados = $con->prepare($seleccion);

        //Asignamos los valores para la selección
        $seleccionados->bindParam('mifecha', $mifecha2);
        $seleccionados->bindParam('mizona', $zon);
        $seleccionados->bindParam('miini', $ini);

        //Comprobamos que existen al menos una reserva para esa fecha, zona e inicio actual
        //Otra posibilidad sería: $seleccionados->execute(); ($resultado=$seleccionados->fetchAll())>0;
        if ($seleccionados->execute() && $seleccionados->fetchColumn() > 0) {
            //Las variables  $mifecha, $mini, $mizon contienen los datos de la reserva a modificar
            //Preparamos la actualización, seleccionando únicamente las reservas distintas de la que 
            //se pretende modificar para comprobar que no se pisan con la nueva modificación
            //Seleccionamos todas las reservas para esa fecha y zona
            $con->rollBack();  //Termino la transacción anterior puesto que no he conseguido que me funcione de otra forma
            //Comienzo la transición para modificar
            $con->beginTransaction();
               
            //Seleccionamos todas las reservas para esa zona,fecha e inicio !=inicio
            $todasMenosElla = <<<SQL
            SELECT inicio, fin FROM reservas WHERE fecha='$mifecha2' AND zona_id='$zon' AND NOT inicio='$ini'
SQL;
            //retorna el número de reservas para esa fecha y zona, excepto la que se pretende modificar
            $selectotal = $con->query($todasMenosElla);
            //////////////////////////////////////////////////////////
            //No termino de entender porque de esta otra forma no funciona
            //
            //$todasMenosElla = 'SELECT inicio, fin FROM reservas WHERE fecha=:mifecha AND zona_id=:mizona AND NOT inicio=:miini';
            //$selectotal = $con->prepare($todasMenosElla);
            //$selectotal->bindParam('mifecha', $mifecha2);
            //$selectotal->bindParam('mizona', $zon);
            //$selectotal->bindParam('miini', $ini);
            //retorna el número de reservas para esa fecha y zona, excepto la que se pretende modificar
            //if ($selectotal->execute() && $selectotal->fetchColumn() > 0) {
            //Nuevo tramo 
            $miininuevo = $datos['nuevo_inicio'];
            $mifinnuevo = $datos['nuevo_fin'];
            $tramonuevo = $miininuevo . '-' . $mifinnuevo; //o todo de una vez $datos['nuevo_inicio'].'-'.$datos['nuevo_fin'];
            //Intentamos modificar las reservas para esa fecha y zona 
            $puedomodificar = true; //En principio supongo que puedo MODIFICAR            
            
            if ($selectotal) {//Si hay reservas
                $reserva = $selectotal->fetch(PDO::FETCH_ASSOC);

                //Comprobamos que dicha reserva no es pisada por los datos nuevos        
                while ($reserva != null && $puedomodificar) {//Mientras haya reservas y no se pise 
                    //El tramo reservado es igual a la concatenación de las horas de inicio y fin (5 primeros caracteres
                    $tramoreservado = substr($reserva['inicio'], 0, 5) . '-' . substr($reserva['fin'], 0, 5);
                    //Comprobamos si se pisan el nuevo tramo y el reservado.
                    //La función comprobarSiSePisanTramosHoras se ha modificado para que devuelva false si no se pisan                    

                    $comprobante = comprobarSiSePisanTramosHoras($tramonuevo, $tramoreservado);

                    if ($comprobante !== false) {//Si se pisan, no debo seguir comprobando, no se puede modificar
                        $puedomodificar = false;
                    } else {//Seguimos con el resto de tramos, si los hay
                        $reserva = $selectotal->fetch(PDO::FETCH_ASSOC);
                    }
                } //Fin de la comprobación de que el resto de reservas no se pisa con la nueva                       
            } 
            else {//Si no hay reservas distintas a la que se pretende modificar se puede modificar sin problemas
                //En realidad este else no sería necesario pero me queda más clara la lógica
            }

            if ($puedomodificar) {//Ejecutamos la actualización si se puede
                //Preparamos los datos para una posible actualización
                $actualiza = 'UPDATE reservas SET inicio=:miininuevo, fin=:mifinnuevo WHERE fecha=:mifecha AND zona_id=:mizona AND inicio=:miini';
                $pdoactualiza = $con->prepare($actualiza);

                $pdoactualiza->bindParam('miininuevo', $miininuevo);
                $pdoactualiza->bindParam('mifinnuevo', $mifinnuevo);
                $pdoactualiza->bindParam('mifecha', $mifecha2);
                $pdoactualiza->bindParam('mizona', $zon);
                $pdoactualiza->bindParam('miini', $ini);
                try {// Por si salta una excepción
                    if ($pdoactualiza->execute()) {//Ejecutamos la actualización
                        $resultadomodificacion = $pdoactualiza->rowCount();
                    }
                    $con->commit(); //Confirmamos 
                } catch (Exception $ex) {//Hay un error al intentar actualizar los datos
                    $resultadomodificacion = -3;
                    $con->rollBack();//Deshacemos la transacción
                }
            } 
            else {//Los datos se pisan con otras reservas
                $resultadomodificacion = -2;
                $con->rollBack();
            }
        } else {//No existen reservas para esa fecha, por tanto, no se puede modificar nada
            $resultadomodificacion = 0;
            $con->rollBack();
        }//Fin de la comprobación sobre de si existen reservas para la fecha dada
    } else {//Los datos recibidos por la función no son correctos
        $resultadomodificacion = -1;
    }

    return $resultadomodificacion;
}

/*
 * **************************************************************************************************
 * Conjunto de funciones que pensé en utilizar como alternativa a las que finalmente he utilizado
 * **************************************************************************************************
 */
/*
 * Función que comprueba que se recibe una fecha correcta
 * @param array $error  un array pasado por referencia con los posibles errores
 * @param array $dato   un array pasado por referencia, los datos recibidos. 
 * Se emplea filter_input, no eliminaba los espacios en blanco iniciales y finales
 */

function procesarFecha(array &$error, array &$dato) {
//Comprobamos que hemos recibido una fecha 
    if (isset($_POST['fecha'])) {
//$sid_v = trim($_POST['fecha']);  //Eliminar los espacios blancos antes y después ya se incluye en la expr. regular
        $datorecibido = filter_input(INPUT_POST, 'fecha', FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => EXP_REG_FECHA]]);
//$datorecibido=filter_var($sid_v,FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => EXP_REG_FECHA]]);
        if ($datorecibido) { //Si los datos recibidos verifican la expresión regular de una fecha
            $datorecibido = trim($datorecibido); //eliminamos los espacios en blanco iniciales y finales
            list($dd, $mm, $aa) = explode('/', $datorecibido); //separamos días, mes y año
            if (checkdate(intval($mm), intval($dd), intval($aa))) {//Validamos si es una fecha correcta
                $dato['fecha'] = $datorecibido; //En este caso tenemos una fecha correcta
            } else {//En este caso, no es una fecha correcta aunque cumpla la expr. regular
                $error['fecha'] = 'La expresión ' . $_POST['fecha'] . ' no es una fecha válida. El formato es dd/mm/aaaa';
            }
        } else { //Los datos recibidos no verifican la expr. regular de una fecha
            $error['fecha'] = 'La expresión: ' . $_POST['fecha'] . ' no verifica la expresión regular de la fecha. El formato es dd/mm/aaaa';
        }
    } else {//No se ha recibido una fecha
        $error['fecha'] = 'Debe introducir una fecha. El formato es dd/mm/aaaa';
    }
}

/*
 * Función que comprueba que se recibe una fecha correcta para un campo
 * @param array $error  un array pasado por referencia con los posibles errores
 * @param array $dato   un array pasado por referencia, los datos recibidos. 
 * @param string $campo es el nombre del campo que se recibirá vía POST
 * Se emplea filter_input, no eliminaba los espacios en blanco iniciales y finales
 */

/*
 * Función que comprueba que se recibe una zona_id
 * @param array $error  un array pasado por referencia con los posibles errores
 * @param array $dato   un array pasado por referencia los posibles datos recibidos
 */

function procesarZonaId(array &$error, array &$dato) {
//Comprobamos que hemos recibido un id de usuario 
    if (isset($_POST['zona_id'])) {
//$sid_v = trim($_POST['zona_id']);  Eliminar los espacios blancos antes y despues. No sentido puesto que ya lo hace filter_input
//filtramos que sea un número entero >=0; 
        $sid_v = filter_input(INPUT_POST, 'zona_id', FILTER_VALIDATE_INT, ["options" => ['min_range' => 0]]);
        if ($sid_v === false || $sid_v < 0) {
            $error['zona_id'] = " No es un número entero >=0,  " . $_POST['zona_id'];
        } else {
            $dato['zona_id'] = $sid_v;
        }
    } else {
        $error['zona_id'] = "Debes especificar una zona de usuario válido (entero >=0)";
    }
}

function procesarInicio(array &$error, array &$dato) {
//Comprobamos que hemos recibido una hora de inicio 
    if (isset($_POST['inicio'])) {
//$sid_v = trim($_POST['inicio']);  //Eliminamos los espacios blancos antes y despues
//filtramos que sea una hora válida; 
        $datorecibido = filter_input(INPUT_POST, 'inicio', FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => EXP_REG_HORA]]);
        if ($datorecibido === false || $datorecibido === null) { //no verifica la expresión regular
            $error['inicio'] = " No es una hora válida " . $_POST['inicio'] . ', su formato es HH:MM';
        } else { //Aunque cumpla la expresión regular, debemos comprobar que es una hora correcta
            $datorecibido = trim($datorecibido);
            $sid_h = convertirHoraAMinutos($datorecibido); //Convertimos la expresión HH:MM, en minutos.
            if ($sid_h === false) {
                $error['inicio'] = " No es una hora de inicio válida " . $_POST['inicio'] . ', su formato estará entre 00:00 y 23:59';
            } else {
                $dato['inicio'] = $datorecibido;
            }
        }
    } else {
        $error['inicio'] = "Debes especificar una hora de inicio en el formato HH:MM";
    }
}

/*
 * Función que comprueba que se recibe una hora de fin correcta
 * @param array $error  un array pasado por referencia con los posibles errores
 * @param array $dato   un array pasado por referencia, los datos recibidos. 
 * En este caso, la hora de inicio se almacena en formato: número de minutos en 
 * el array $dato para ser utilizada en la siguiente función
 */

function procesarFin(array &$error, array &$dato) {
//Comprobamos que hemos recibido una hora de inicio 
    if (isset($_POST['fin'])) {
//$sid_v = trim($_POST['fin']);  //Eliminamos los espacios blancos antes y despues
//filtramos que sea una hora válida. Para ello empleo filter_var en lugar de filter_input; 
//$sid_v = filter_var($sid_v,FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => EXP_REG_HORA]]);
        $datorecibido = filter_input(INPUT_POST, 'fin', FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => EXP_REG_HORA]]);
        if ($datorecibido === false || $datorecibido == null) { //no verifica la expresión regular
            $error['fin'] = " No es una hora de fin válida " . $_POST['fin'] . ', su formato es HH:MM';
        } else { //Aunque cumpla la expresión regular, debemos comprobar que es una hora correcta
            $datorecibido = trim($datorecibido); //Eliminamos los espacios iniciales y finales
            $sid_vf = convertirHoraAMinutos($datorecibido); //Convertimos la expresión HH:MM, en minutos.
            if ($sid_vf === false) {
                $error['fin'] = " No es una hora de fín válida " . $_POST['fin'] . ', su formato estará entre 00:00 y 23:59';
            } else { //Comprobamos que junto con el inicio es un tramo horario válido
                if (isset($error['inicio'])) {//Comprobamos que hay una hora de inicio válida
                    $error['fin'] = "Previamente debes introducir una hora de inicio válida";
                } else { //La hora final es válida
//Comprobamos que junto con la hora final es un tramo horario válido
                    $validaHF = comprobarTramoHoras(trim($_POST['inicio']) . '-' . $datorecibido);
                    if ($validaHF) {
                        $dato['fin'] = $datorecibido;
                    } else { //Si la hora final no es >= que la hora inicial
                        $error['fin'] = "La hora final debe ser mayor igual que la hora de inicio";
                    }
                }
            }
        }
    } else {
        $error['fin'] = "Debes especificar una hora de fin en el formato HH:MM";
    }
}
