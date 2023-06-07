<!DOCTYPE html>
<html>
<head>
    <title>Formulario con Gráfico CanvasJS</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <link rel="stylesheet" href="styles/estilo.css">
</head>
<body>
    <h1>Graficas</h1>
    <div id="contenedorFormulario">
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
    <button id="exportarPDF">Exportar todo</button>

    <h1>Graficas Dobles</h1>
    <div id="contenedorDFormulario">
    <form id="formulario2" method="POST" action="procesar.php" enctype="multipart/form-data">
    <div>
    <input type="file" name="archivosDobles1[]" accept=".csv">
    </div>
    <div>
    <input type="file" name="archivosDobles2[]" accept=".csv">
    </div>
    <input type="submit" name="submit" value="Enviar archivos">
    </form>
    <div class="itemD3">
    <button id="borrarGraficaDoble">Borrar gráficas</button>
    </div>
    </div>
    <div id="contenedorGraficasDobles">
    <div id="graficoCanvasDoblesJS0"></div>
    </div>
    <button id="exportarPDFDobles">Exportar doble</button>
</body>
<script>
    var contadorGraficos = 1;
    var nombresGraficas = [];

    $(document).ready(function() {
        $('#formulario1').submit(function(event) {
            event.preventDefault();
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: 'procesar.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Cargar el gráfico de CanvasJS después de recibir la respuesta
                    cargarGraficoCanvasJS(response);
                }
                });
            });
        });

    function cargarGraficoCanvasJS(response) {
        var arr_from_json = JSON.parse(response);
        var divBase = document.getElementById("graficoCanvasJS0"); // Obtener el div base existente

        for (var i = 0; i < arr_from_json.length; i++) {
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

            var chart = new CanvasJS.Chart(nuevoDiv.id, {
                theme: "light2",
                width: 10940,
                height: 200,
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
                        dataPoints: arr_from_json[i].data
                    }
                ]
            });
            chart.render();
            nuevoDiv.scrollLeft = (nuevoDiv.scrollWidth - nuevoDiv.clientWidth) / 2; // el scroll comienza centrado
        // Crear un botón de exportar a PDF para el gráfico actual
        var exportButton = document.createElement("button");
        exportButton.textContent = "Exportar a PDF";
        exportButton.id = "ExpoPDF" + contadorGraficos;
        exportButton.addEventListener("click", function() {
            var divId = this.previousElementSibling.id;
            console.log(divId);
            exportarAPDF(divId);
        });
        
        nuevoDiv.insertAdjacentElement("afterend",exportButton);
        contadorGraficos++; // Incrementar el contador de gráficos

    }
}

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

botonTodosPDF = document.getElementById("exportarPDF")
botonTodosPDF.addEventListener("click", function() {
  var pdf = new jsPDF();

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
})

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

        var info = "x: " + e.dataPoint.x + ", y: " + e.dataPoint.y;
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
//------------------------------------------------------------------------------------------------------------------------------------



$(document).ready(function() {
        $('#formulario2').submit(function(event) {
            event.preventDefault();
            var formData = new FormData($(this)[0]);
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
        function cargarGraficoCanvasJSDoble(response) {
        var arr_from_json = JSON.parse(response);
        var divBase = document.getElementById("graficoCanvasDoblesJS0"); // Obtener el div base existente
        var nombreArchivo =""
        for (var i = 0; i < arr_from_json.length; i++) {
            limpiarNombreArchivo = arr_from_json[i].name.slice(0, arr_from_json[i].name.search("_"));
            nombreArchivo += limpiarNombreArchivo+ " ";
        }
            // Crear un nuevo div para cada elemento en arr_from_json
            var divExistente = document.getElementById("graficoCanvasDoblesJS1");
            if (divExistente) {
                divExistente.remove();
            }
            var nuevoDiv = document.createElement("div");
            nuevoDiv.id = "graficoCanvasDoblesJS1";
            nuevoDiv.className = "grafico-canvasjs"; // Aplicar la clase "grafico-canvasjs"
            divBase.insertAdjacentElement("afterend", nuevoDiv); // Insertar el div después del div base

            nombresGraficas.push(nombreArchivo); // Agregar el nombre de archivo a la lista

            var chart = new CanvasJS.Chart(nuevoDiv.id, {
                theme: "light2",
                width: 10940,
                height: 200,
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

function borrarGraficas() {
    var divGrafica = document.getElementById("graficoCanvasDoblesJS1");
    if (divGrafica) {
        divGrafica.remove();
    }
}

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
