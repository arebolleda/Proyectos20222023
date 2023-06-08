<!DOCTYPE html>
<html>
<head>
    <title>Smart Watch Site</title>
    <meta charset="utf-8" />
    <meta name="author" content="Andrés González Calva" />
    <!-- imports: jspdf,jquery y canvasjs-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <link rel="stylesheet" href="styles/estilo1.css">
</head>
<body>
    <h1>Graficas</h1>
    <div class="contenedorFormulario">
        <form id="formulario1" method="POST" action="procesar.php" enctype="multipart/form-data">
            <div class="item1">
            <input type="file" name="archivos[]" multiple accept=".csv">
            </div>
            <div class="item2">
            <input type="submit" name="submit" value="Enviar archivos">
            </div>
        </form>
        <div class="item3">
        <button id="borrarGraficas">Borrar gráficas</button>
    </div>
    </div>
    <div id="contenedorGraficas">
    <div id="graficoCanvasJS0"></div>
    </div>
    <div class="item4">
    <button id="exportarPDF" class="exportar">Exportar a pdf todo</button>
    </div>

    <h1>Graficas Dobles</h1>
    <div class="contenedorDFormulario">
        <form id="formulario2" method="POST" action="procesar.php" enctype="multipart/form-data">
            <div class="itemD1">
                <input type="file" name="archivosDobles1[]" accept=".csv">
            </div>
            <div class="itemD2">
                <input type="file" name="archivosDobles2[]" accept=".csv">
            </div>
            <div class="itemD3b">
            <input type="submit" name="submit" value="Enviar archivos">
            </div>
        </form>
    <div class="itemD3">
    <button id="borrarGraficaDoble">Borrar gráficas</button>
    </div>
    </div>
    <div id="contenedorGraficasDobles">
    <div id="graficoCanvasDoblesJS0"></div>
    </div>
    <div class="itemD4">
    <button id="exportarPDFDobles" class="exportar">Exportar pdf doble</button>
    </div>
</body>
<script>
    var contadorGraficos = 1; // Contador de graficas
    var nombresGraficas = []; // Graficas ya generadas

    // Funcion que hace la peticion ajax
    $(document).ready(function() {
        
        $('#formulario1').submit(function(event) {
            // Linea que previene que el formulario se envie de forma normal
            event.preventDefault();
            var formData = new FormData($(this)[0]);
            
            // Configuracion de la peticion
            $.ajax({
                url: 'procesar.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                // Si la peticion sale bien

                    success: function(response) {
                        // Cargar el gráfico de CanvasJS después de recibir la respuesta
                        cargarGraficoCanvasJS(response);
                    }
                });
            });
        });

    // Funcion que carga el grafico con canvasjs 
    function cargarGraficoCanvasJS(response) {
        var arr_from_json = JSON.parse(response); // Pasar la respuesta a array
        var divBase = document.getElementById("graficoCanvasJS0"); // Obtener el div base existente
        
        // Bucle que recorre el array con las graficas
        for (var i = 0; i < arr_from_json.length; i++) {
            // Obtener el nombre
            var nombreArchivo = arr_from_json[i].name; 
            nombreArchivo = nombreArchivo.slice(0, nombreArchivo.search("_"));

            // Verificar si el nombre de archivo ya existe en la lista de gráficas generadas
            if (nombresGraficas.includes(nombreArchivo)) {
                alert(nombreArchivo + " ya creada");
                continue; // Omitir la generación de la gráfica repetida
            }

            // Crear un nuevo div para cada elemento en arr_from_json
            var nuevoDiv = document.createElement("div");
            nuevoDiv.id = "graficoCanvasJS" + contadorGraficos; // Asignar un id único a cada div
            nuevoDiv.className = "grafico-canvasjs"; // Aplicar la clase "grafico-canvasjs"
            divBase.insertAdjacentElement("afterend", nuevoDiv); // Insertar el div después del div base

            nombresGraficas.push(nombreArchivo); // Agregar el nombre de archivo a la lista

            // Configuracion del grafico
            var chart = new CanvasJS.Chart(nuevoDiv.id, {
                width: 10940,
                height: 200,
                // Configuracion de la cuadricula para que quede como papel milimetrado
                axisX: {
                    interval: 0.2,
                    gridThickness: 1,
                    labelFormatter: function(e) {
                        if (e.value % 1 === 0) {
                            return e.value.toFixed(0); // Mostrar etiquetas enteras cada 1
                        } else {
                            return "";
                        }
                    }
                },
                axisY: {
                    interval: 500,
                    gridThickness: 1
                },
                // Titulo y funcion de obtencion de informacion de puntos
                title: {
                    text: nombreArchivo
                },
                data: [
                    {
                        click: function(e) {
                            onClick(e);
                        },
                        cursor: "pointer",
                        type: "line",
                        dataPoints: arr_from_json[i].data
                    }
                ]
            });
            // Creacion del grafico
            chart.render();
            nuevoDiv.scrollLeft = (nuevoDiv.scrollWidth - nuevoDiv.clientWidth) / 2; // el scroll comienza centrado
        
            // Crear un botón de exportar a PDF para el gráfico actual
            var exportButton = document.createElement("button");
            exportButton.textContent = "Exportar a PDF";
            exportButton.id = "ExpoPDF" + contadorGraficos;

            // Añadir funcion para que cree los pdfs
            exportButton.addEventListener("click", function() {
                var divId = this.previousElementSibling.id;
                console.log(divId);
                exportarAPDF(divId);
            });
            
            //Añadir el boton
            nuevoDiv.insertAdjacentElement("afterend",exportButton);
            contadorGraficos++; // Incrementar el contador de gráficos

        }
    }

    // Funcion que exporta a pdf
    function exportarAPDF(divId) {
        var canvas = document.querySelector("#" + divId + " canvas"); // Obtener el canvas del gráfico
        var dataURL = canvas.toDataURL("image/png"); // Obtener la imagen del canvas como base64

        var pdf = new jsPDF();

        // Obtener las dimensiones del canvas
        var canvasWidth = canvas.width;
        var canvasHeight = canvas.height;

        // Calcular el ancho y alto proporcionales para la imagen en el PDF
        var pdfWidth = 2000;
        var pdfHeight = 50;

        pdf.addImage(dataURL, "PNG", 10, 10, pdfWidth, pdfHeight); // Agregar la imagen al PDF
        pdf.save("grafico.pdf"); // Descargar el PDF
    }

    // Funcion que exportas todas las graficas a pdfs
    botonTodosPDF = document.getElementById("exportarPDF")
    botonTodosPDF.addEventListener("click", function() {
        var divGrafica = document.getElementById("graficoCanvasJS1");
        // if para evitar pdfs vacios
        if (divGrafica) {
        var pdf = new jsPDF();
        //for que recorre todos los divs con graficos
        for (var i = 1; i < contadorGraficos; i++) {
            var divId = "graficoCanvasJS" + i;
            var canvas = document.querySelector("#" + divId + " canvas");

            // Verificar si el canvas existe
            if (!canvas) {
            continue; // Omitir la exportación si el canvas no está presente
            }

            var dataURL = canvas.toDataURL("image/png"); // Obtener la imagen del canvas como base64

            // Agregar la imagen al PDF
            pdf.addImage(dataURL, "PNG", 10, 10, 2000, 50);

            // Agregar una nueva página si no es la última gráfica
            if (i < contadorGraficos - 1) {
            pdf.addPage();
            }
        }

        pdf.save("graficas.pdf"); // Descargar el PDF
    }else{
        alert("Error: no hay graficas")
    }
    })

    // Funcion que al darle click a la linea del grafico, muestra la informacion del punto
    function onClick(e) {
        var idPadre = e.chart._containerId;

        // Verificar si el div infoPuntosDiv ya existe
        var infoPuntosDiv = document.getElementById("infoPuntosDiv" + idPadre);
        if (!infoPuntosDiv) {
            // Si no existe, crear un nuevo div y agregarlo como hijo del div padre
            infoPuntosDiv = document.createElement("div");
            infoPuntosDiv.id = "infoPuntosDiv" + idPadre;
            infoPuntosDiv.className = "info-puntos";
            document.getElementById(idPadre).appendChild(infoPuntosDiv);
        }

        var info = "Informacion del punto: x = " + e.dataPoint.x + ", y = " + e.dataPoint.y;
        infoPuntosDiv.innerText = info;

        // Ajustar la posición del div infoPuntosDiv debajo del gráfico
    }

    document.getElementById("borrarGraficas").addEventListener("click", function() {
        // Eliminar todas las gráficas creadas
        for (var i = 1; i < contadorGraficos; i++) {
            var divGrafico = document.getElementById("graficoCanvasJS" + i);
            var botonpdf = document.getElementById("ExpoPDF" + i);

            divGrafico.nextElementSibling.remove(botonpdf);
            divGrafico.parentNode.removeChild(divGrafico);
        }

        // Reiniciar el contador y el array de nombres de gráficas
        contadorGraficos = 1;
        nombresGraficas = [];
    });
//Grafica doble------------------------------------------------------------------------------------------------------------------------------------


    // Funcion que hace la peticion ajax
    $(document).ready(function() {

        $('#formulario2').submit(function(event) {
            // Linea que previene que el formulario se envie de forma normal
            event.preventDefault();
            var formData = new FormData($(this)[0]);

            // Configuracion de la peticion
            $.ajax({
                url: 'procesarDoble.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Cargar el gráfico de CanvasJS después de recibir la respuesta
                    console.log(response)
                    cargarGraficoCanvasJSDoble(response);

                }
                });
            });
        });
        
    // Funcion que carga el grafico con canvasjs 
    function cargarGraficoCanvasJSDoble(response) {
    var arr_from_json = JSON.parse(response); // Pasar la respuesta a array
    var divBase = document.getElementById("graficoCanvasDoblesJS0"); // Obtener el div base existente
    //Variable nueva para escribir el nombre del archivo
    var nombreArchivo =""
    for (var i = 0; i < arr_from_json.length; i++) {
        limpiarNombreArchivo = arr_from_json[i].name.slice(0, arr_from_json[i].name.search("_"));
        nombreArchivo += limpiarNombreArchivo+ " ";
    }
        // Crear un nuevo div el elemento en arr_from_json
        var divExistente = document.getElementById("graficoCanvasDoblesJS1");
        if (divExistente) {
            divExistente.remove();
        }

        // Crear un nuevo div para cada elemento en arr_from_json
        var nuevoDiv = document.createElement("div");
        nuevoDiv.id = "graficoCanvasDoblesJS1";
        nuevoDiv.className = "grafico-canvasjs"; // Aplicar la clase "grafico-canvasjs"
        divBase.insertAdjacentElement("afterend", nuevoDiv); // Insertar el div después del div base

        nombresGraficas.push(nombreArchivo); // Agregar el nombre de archivo a la lista

        // Configuracion del grafico
        var chart = new CanvasJS.Chart(nuevoDiv.id, {
            theme: "light2",
            width: 10940,
            height: 200,
            // Configuracion de la cuadricula para que quede como papel milimetrado
            axisX: {
                interval: 0.2,
                gridThickness: 1,
                labelFormatter: function(e) {
                    if (e.value % 1 === 0) {
                        return e.value.toFixed(0); // Mostrar etiquetas enteras cada 1
                    } else {
                        return "";
                    }
                }
            },
            axisY: {
                interval: 500,
                gridThickness: 1
            },
            title: {
                text: nombreArchivo
            },
            data: [
                {
                    click: function(e) {
                        onClick(e);
                    },
                    cursor: "pointer",
                    type: "line",
                    dataPoints: arr_from_json[0].data
                },
                {
                    click: function(e) {
                        onClick(e);
                    },
            cursor: "pointer",
            type: "line",
            dataPoints: arr_from_json[1].data
        }]
        });
        chart.render();
        nuevoDiv.scrollLeft = (nuevoDiv.scrollWidth - nuevoDiv.clientWidth) / 2; // el scroll comienza centrado
    }
    var botonBorrar = document.getElementById("borrarGraficaDoble");
    botonBorrar.addEventListener("click", borrarGraficas);

    // Funcion para borrar las graficas
    function borrarGraficas() {
        var divGrafica = document.getElementById("graficoCanvasDoblesJS1");
        if (divGrafica) {
            divGrafica.remove();
        }
    }

    // Funcion para exportar la grafica a pdf
    var botonExportar = document.getElementById("exportarPDFDobles");
    botonExportar.addEventListener("click", dobleExportarAPDF);

    
    function dobleExportarAPDF() {
        var divGrafica = document.getElementById("graficoCanvasDoblesJS1");
        if (divGrafica) {
        var canvas = document.querySelector("#" + "graficoCanvasDoblesJS1" + " canvas"); // Obtener el canvas del gráfico
        var dataURL = canvas.toDataURL("image/png"); // Obtener la imagen del canvas como base64

        var pdf = new jsPDF();

        // Obtener las dimensiones del canvas
        var canvasWidth = canvas.width;
        var canvasHeight = canvas.height;

        // Calcular el ancho y alto proporcionales para la imagen en el PDF
        var pdfWidth = 2000;
        var pdfHeight = 50;

        pdf.addImage(dataURL, "PNG", 10, 10, pdfWidth, pdfHeight); // Agregar la imagen al PDF
        pdf.save("grafico.pdf"); // Descargar el PDF

        }else{
            alert("Error: no hay grafica doble")
        }
    }
</script>
</html>
