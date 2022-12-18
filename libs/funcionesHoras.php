<?php
/**
 * Funciones copiadas de mi tarea 1 íntegramente
 */


/* * ****************************************************************************
 * function convertirHoraAMinutos ($hora). Esta función convierte una cadena 
 * que contiene una hora, por ejemplo '11:30', a minutos desde comienzo del día 
 * (ten en cuenta que el comienzo es a las 00:00). Esta función retornará un 
 * número entero o false si no se ha podido hacer la conversión.
 */

function convertirHoraAMinutos($hora) {
    //Obtenemos un array de dos trozos correspondientes a la hora y los minutos
    $trozosfecha = explode(':', $hora);

    $numerotrozos = count($trozosfecha);
    $valordevuelto = 0;
    if ($numerotrozos != 2) {   //Comprobamos que la cadena contiene 2 partes
        $valordevuelto = false; // Si no tiene 2 partes, devuelve false
    } else {                    // Si tiene dos partes, comprobamos que la primera es una hora correcta
        list($shora, $smin)= explode(':', $hora);
        if (is_numeric($shora) && (intval($shora) >= 0 && intval($shora <= 23)) && intval($shora)==floatval($shora)) {
            $valordevuelto = 60 * $shora;
        } else { //Si la primera parte no es un número natural o no se encuentra en el intervalo [0,23], devuelve false
            $valordevuelto = false;
        }

        //Si la segunda parte son minutos válidos, se lo sumamos a los anteriores
        if (is_numeric($smin) && (intval($smin) >= 0 && intval($smin) <= 59 && intval($smin)==floatval($smin)) && $valordevuelto !== false) {
            $valordevuelto += intval($smin);
        } else { //Si la segunda parte no es un número o no se encuentra en el intervalo [0,59] o no es una hora correcta, devuelve false
            $valordevuelto = false;
        }
    }

    return $valordevuelto;  //Se devuelve el valor de la función
}

 
/**
 * function convertirTramoHorasATramoMinutos ($tramo). Lo primero que tienes que
 * tener en cuenta es que esta función debe usar la función convertirHoraAMinutos.
 * El objetivo de esta función es convertir un tramo horario en el número de 
 * minutos desde comienzo del día, retornando un array de dos números, uno para
 * el comienzo del tramo y otro para el fin del tramo.
 */
function convertirTramoHorasATramoMinutos($tramo) {
    //Por el enunciado he supuesto que se recibe un tramo de horario correcto.
    $tramos = explode("-", $tramo);  //Separamos el tramo en las dos horas: inicial y final
    //Convertimos cada una de las horas en un número y lo devolvemos en un array
    $arraydevuelto = array(convertirHoraAMinutos($tramos[0]), convertirHoraAMinutos($tramos[1]));

    return $arraydevuelto;
}


/**
 * function comprobarTramoHoras ($tramo). Esta función recibirá como parámetro 
 * un tramo y deberá usar las funciones anteriores según sea necesario. 
 * La idea de esta función es:
 * • 1º) Comprobar que el tramo horario cumple con la expresión regular 
 * siguiente: '/^\d+:\d+-\d+:\d+$/'
 * • 2º) Comprobar que la hora de inicio del tramo es anterior a la hora de 
 * fin del tramo.
 * • 3º) Comprobar que la hora de inicio es mayor o igual que 0.
 * • 4º) Comprobar que la hora de fin es menor o igual a 23:59.
 * • Esta función deberá retornar true si el tramo es correcto y false en caso 
 * contrario. Ten en cuenta que deberá retornar false cuando una de las horas 
 * no es válida (por ejemplo: '10:61'), y que la hora '0:0' es válida.
 */
function comprobarTramoHoras($tramo) {
    //En principio, considero que es cierto 
    $valordevuelto = true;
    // Comprobamos si la entrada verifica la expresión regular definida
    $valordeltramo = preg_match(EXPRESIONREGULARTRAMO, $tramo);
    //Si no verifica la expresión regular, se devuelve false
    if ($valordeltramo == 0 || $valordeltramo == false) {
        $valordevuelto = false;
    } else { //Si verifica la expresión regular, comprobamos si las dos horas son válidas
        $intervalo = convertirTramoHorasATramoMinutos($tramo);
        if ($intervalo[0] === false || $intervalo[1] === false) { //Si una de ellas no lo es, devolvemos false
            $valordevuelto = false;
        } elseif ($intervalo[0] >= $intervalo[1]) { //Si la hora de inicio es mayor o igual que el final, se devuelve false
            $valordevuelto = false;
        }
    }

    return $valordevuelto;
}


/**
 * function esTramoHorasContenidoEnOtroTramoHoras ($tramoA, $tramoB). 
 * Nuevamente, esta función deberá usar las otras funciones convenientemente. 
 * El objetivo de esta función es retornar "true" si el $tramoA está contenido 
 * en $tramoB, y false en caso contrario.
 */
function esTramoHorasContenidoEnOtroTramoHoras($tramoA, $tramoB) {
    //En principio supongo es false (no está contenido)
    $valordevuelto = false;

    //Comprobamos que los tramos son válidos; si lo son seguimos comprobando
    if (comprobarTramoHoras($tramoA) && comprobarTramoHoras($tramoB)) {
        //Transformamos cada tramo en su array de dos dimensiones equivalente 
        $tramo1 = convertirTramoHorasATramoMinutos($tramoA);
        $tramo2 = convertirTramoHorasATramoMinutos($tramoB);
        //El tramoA estará contenido, si su hora de inicio es mayor o igual que la hora de incio del tramoB
        // y su hora final es menor o igual que la del tramoB
        if ($tramo1[0] >= $tramo2[0] && $tramo1[1] <= $tramo2[1]) {
            $valordevuelto = true;
        }
    }

    return $valordevuelto;
}


/**
 * function comprobarSiSePisanTramosHoras ($tramo1,$tramo2).
 * Nuevamente, esta función deberá usar las otras funciones convenientemente. 
 * El objetivo de esta función es verificar si el $tramo1 se solapa con el 
 * $tramo2, retornando una cadena de texto indicando el problema.
 */
function comprobarSiSePisanTramosHoras($tramo1, $tramo2) {
    //En principio supongo que no se solapan y, por tanto, no hay problemas
    //Además he supuesto que son tramos válidos
    $valordevuelto = false;

    //Comprobamos si el tramo1 está contenido en el tramo2, o al revés.
    if (esTramoHorasContenidoEnOtroTramoHoras($tramo1, $tramo2)) {
        $valordevuelto = " El tramo " . $tramo1 . " está contenido en el tramo " . $tramo2;
    } elseif (esTramoHorasContenidoEnOtroTramoHoras($tramo2, $tramo1)) {
        $valordevuelto = " El tramo " . $tramo2 . " está contenido en el tramo " . $tramo1;
    } else {    //Si ninguno está contenido en el otro, comprobamos que no se solapan en parte
        $tramo11 = convertirTramoHorasATramoMinutos($tramo1);
        $tramo22 = convertirTramoHorasATramoMinutos($tramo2);
        if ($tramo11[1] === $tramo22[0]) {//Comprobamos que el tramo final de tramo1 coincide con el tramo inicial del tramo2
            $valordevuelto = "Dichos tramos " . $tramo1 . " y " . $tramo2 . " coinciden en el instante final del primero con el comienzo del segundo:  \n";
        } elseif ($tramo11[0] < $tramo22[0] && $tramo11[1] > $tramo22[0] && $tramo11[1] < $tramo22[1]) {
            $valordevuelto = "El tramo: " . $tramo1 . "  comienza antes del tramo " . $tramo2 . "; termina después del comienzo de dicho tramo, finalizando antes de que haya terminado dicho tramo \n";
        } elseif ($tramo22[0] < $tramo11[0] && $tramo11[0] < $tramo22[1] && $tramo11[1] > $tramo22[1]) {
            $valordevuelto = "El tramo: " . $tramo1 . " comienza después de que haya comenzado el tramo " . $tramo2 . " y termina después de él \n ";
        } elseif ($tramo11[0] === $tramo22[1]) {
            $valordevuelto = "Dichos tramos " . $tramo1 . " y " . $tramo2 . " coinciden en el instante final de segundo con el comienzo del primero:  \n";
        }
    }

    return $valordevuelto;
}



/**
 * function comprobarSiPisaTramosOcupados ($tramo, $tramosOcupados). 
 * Usando los métodos anteriores convenientemente, debes crear un método que 
 * dado un tramo ($tramo) compruebe si se solapa con alguno de los tramos 
 * contenidos en el array $tramosOcupados.
 */
function comprobarSiPisaTramosOcupados($tramo, $tramosOcupados) {
    //Array que devolverá los tramos que se solapan
    $valordevuelto = array();
    //Se comprobará para cada uno de los tramos ocupados, si se solapa con el tramo
    foreach ($tramosOcupados as $tramocandidato) {
        $coincidencia= comprobarSiSePisanTramosHoras($tramo, $tramocandidato);
        if ($coincidencia !== "") { //Si se pisan, en dicha variable tendremos el problema, por tanto, se añade al array de problemas
            $valordevuelto[] = $coincidencia."<br>";
        }
    }

    return $valordevuelto;
}



/**
 * function comprobarSiEntraEnHorario ($tramo, $tramosHorario).
 * Usando los métodos anteriores convenientemente, debes crear un método que 
 * dado un tramo ($tramo) retorne true si el tramo está dentro del horario 
 * ($tramosHorario) o false en caso contrario.
 */
function comprobarSiEntraEnHorario($tramo, $tramosHorario) {
    //En principio supongo que no, false
    $valordevuelto = false;
    //Comprobamos si el tramo está contenido en alguno de los tramos candidatos de $tramosHorarios
    foreach ($tramosHorario as $tramocandidato) { //Comprobamos si el tramo está contenido en el candidato
        $coincidencia = esTramoHorasContenidoEnOtroTramoHoras($tramo, $tramocandidato);
        if ($coincidencia === true) {
            $valordevuelto = true;
            break;   //Una vez hemos comprobado que está contenido en un tramo candidato, no es necesario seguir comprobando
        }
    }

    return $valordevuelto;
}

