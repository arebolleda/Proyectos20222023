<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar los datos recibidos del formulario
    $yValues = array();

    //Función que recibe la direccion del archivo y devuelve los valores limpiados en un array
    function limpiarCSV($archivo){
        $yValues = array();

        //if que al mismo tiempo abre el archivo en modo lectura
        if (($gestor = fopen($archivo, 'r')) !== false) {

            //while que recorre los valores, los limpia y los mete en un array
            while (($fila = fgetcsv($gestor, 1000, ',')) !== false) {

                //Borrar los valores que no sean numeros de la grafica
                $filaFiltrada = array_filter($fila, function($valor) {
                    return preg_match('/^-?\d*\d+$/', $valor);
                });

                //Juntar los valores y sustituye ", " por un "."
                $filaFiltrada= implode(', ', $filaFiltrada);
                $filaFiltrada = str_replace(", ", ".",$filaFiltrada);

                // Borrar y guardar los valores que no se hayan vaciado
                if(!$filaFiltrada == ""){
                    $yValues[] = floatval($filaFiltrada);
                }

            }
            fclose($gestor);
        }
        return $yValues;
    }

    //foreach que recorre todos los ficheros, los limpia y los devuelve
    foreach ($_FILES as $archivo) {
        // For que saca los nombres de los archivos de $_FILES

        for($i = 0; $i < count($archivo["tmp_name"]); $i++){
            $extension = strtolower(pathinfo($archivo["name"][$i], PATHINFO_EXTENSION));
            //Si el archivo no es csv devuelve un error en la consola

            if ($extension !== 'csv') {
                // Mostrar un mensaje de error para archivos que no sean de tipo CSV
                echo "Error: El archivo " . $archivo["name"][$i] . " debe ser de tipo CSV.";
                continue;
            }

            //Poner los valores del csv en yvalues
            array_push($yValues, limpiarCSV($archivo["tmp_name"][$i]));

            // Obtener el nombre del archivo
            $nombreArchivo = $archivo["name"][$i];

            // Construir el array con los datos del csv
            $dataPoints[$i] = [
                'name' => $nombreArchivo,
                'data' => [],
            ];

            //bucle que pasa los valores de yvalues al array
            for ($j = 0; $j < count($yValues[$i]); $j++) {
                $dataPoints[$i]['data'][$j] = [
                    'x' => $j * 0.0019,
                    'y' => $yValues[$i][$j]
                ];
            }
        }
    }

    // Convertir los datos a formato JSON
    $jsonData = json_encode($dataPoints, JSON_NUMERIC_CHECK);

    // Devolver la respuesta como salida del script
    echo $jsonData;
    
} else {
    // Si no se recibió una solicitud POST, mostrar un mensaje de error
    echo "Error: No se ha enviado ningún formulario.";
}
?>
