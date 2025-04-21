<?php
// Iniciar sesión para recuperar mensajes
session_start();

// Incluir la configuración de la base de datos
require_once '../../config/database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pagos</title>
    <!-- <link rel="stylesheet" href="../../../style/style.css"> -->
    <style>
        .mensaje-notificacion {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            animation: fadeOut 5s forwards;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .mensaje-exito {
            background-color: #c8e6c9;
            color: #1b5e20;
            border: 1px solid #a5d6a7;
        }

        .mensaje-error {
            background-color: #ffcdd2;
            color: #b71c1c;
            border: 1px solid #ef9a9a;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
        
        .resultados-busqueda {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            display: none;
        }
        
        .cliente-credito {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .cliente-credito:hover {
            background-color: #f5f5f5;
        }
        
        .formulario-pago {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            display: none;
        }
        
        .btn-anular-intereses {
            background-color: #ff9800;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .estado-cancelado {
            background-color: #c8e6c9;
            color: #1b5e20;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .estado-mora {
            background-color: #ffcdd2;
            color: #b71c1c;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .estado-activo {
            background-color: #e3f2fd;
            color: #0d47a1;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .detalle-credito {
            margin-top: 20px;
        }
        
        #tabla-cuotas {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        
        #tabla-cuotas th, #tabla-cuotas td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        #tabla-cuotas th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Gestión de Pagos</h1>
    
    <?php
    // Mostrar mensaje si existe (de sesión)
    if (isset($_SESSION['mensaje'])) {
        $tipo_clase = (isset($_SESSION['tipo_mensaje']) && $_SESSION['tipo_mensaje'] == 'exito') ? 'mensaje-exito' : 'mensaje-error';
        echo '<div class="mensaje-notificacion ' . $tipo_clase . '">' . $_SESSION['mensaje'] . '</div>';

        // Script para ocultar el mensaje después de 5 segundos
        echo '<script>
            setTimeout(function() {
                var mensaje = document.querySelector(".mensaje-notificacion");
                if (mensaje) {
                    mensaje.style.display = "none";
                }
            }, 5000);
        </script>';

        // Limpiar las variables de sesión
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    }
    ?>
    
    <!-- Buscador de clientes -->
    <div class="buscador">
        <h2>Buscar Cliente</h2>
        <input type="text" id="buscar-cliente" placeholder="Ingrese nombre, apellido o DNI..." autocomplete="off">
        <div id="resultados-busqueda" class="resultados-busqueda"></div>
    </div>
    
    <!-- Detalles del crédito seleccionado -->
    <div id="detalle-credito" class="detalle-credito" style="display: none;">
        <h2>Detalles del Crédito</h2>
        <div id="info-credito"></div>
        
        <!-- Formulario para registrar pago -->
        <div id="formulario-pago" class="formulario-pago">
            <h3>Registrar Pago</h3>
            <form id="form-pago" action="procesar_pago.php" method="POST">
                <input type="hidden" id="credito_id" name="credito_id">
                
                <div class="form-group">
                    <label for="nro_cuota">Cuota:</label>
                    <select id="nro_cuota" name="nro_cuota" required>
                        <!-- Las opciones se llenarán dinámicamente -->
                    </select>
                    <button type="button" id="btn-anular-intereses" class="btn-anular-intereses">Anular Intereses</button>
                </div>
                
                <div class="form-group">
                    <label for="monto">Monto a pagar:</label>
                    <input type="number" id="monto" name="monto" step="0.01" required>
                </div>
                
                <button type="submit" class="btn-submit">Registrar Pago</button>
            </form>
        </div>
        
        <!-- Tabla de cuotas -->
        <div id="tabla-cuotas-container">
            <h3>Detalle de Cuotas</h3>
            <table id="tabla-cuotas">
                <thead>
                    <tr>
                        <th>Nro de Cuota</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Monto Cuota</th>
                        <th>Intereses</th>
                        <th>Total a Pagar</th>
                        <th>Pagado</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="cuotas-body">
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="index_creditos.php">Volver al listado de créditos</a>
    </div>
    
    <script>
        // Función para buscar clientes con AJAX
        document.getElementById('buscar-cliente').addEventListener('input', function() {
            const busqueda = this.value.trim();
            const resultadosDiv = document.getElementById('resultados-busqueda');
            
            if (busqueda.length < 2) {
                resultadosDiv.style.display = 'none';
                return;
            }
            
            fetch('buscar_clientes_ajax.php?buscar=' + encodeURIComponent(busqueda))
                .then(response => response.json())
                .then(data => {
                    resultadosDiv.innerHTML = '';
                    
                    if (data.length === 0) {
                        resultadosDiv.innerHTML = '<p>No se encontraron resultados</p>';
                    } else {
                        data.forEach(cliente => {
                            const div = document.createElement('div');
                            div.classList.add('cliente-credito');
                            div.innerHTML = `
                                <strong>${cliente.nombre} ${cliente.apellido}</strong> - DNI: ${cliente.dni}<br>
                                <span>Crédito #${cliente.credito_id} - Monto: $${parseFloat(cliente.monto).toLocaleString('es-AR')}</span>
                            `;
                            div.addEventListener('click', function() {
                                cargarDetalleCredito(cliente.credito_id);
                                resultadosDiv.style.display = 'none';
                                document.getElementById('buscar-cliente').value = `${cliente.nombre} ${cliente.apellido} - DNI: ${cliente.dni}`;
                            });
                            resultadosDiv.appendChild(div);
                        });
                    }
                    
                    resultadosDiv.style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
        });
        
        // Función para cargar detalles del crédito
        function cargarDetalleCredito(creditoId) {
            fetch('obtener_detalle_credito.php?id=' + creditoId)
                .then(response => response.json())
                .then(data => {
                    // Mostrar información del crédito
                    document.getElementById('info-credito').innerHTML = `
                        <p><strong>Cliente:</strong> ${data.cliente}</p>
                        <p><strong>Nro de Crédito:</strong> ${data.id}</p>
                        <p><strong>Monto Prestado:</strong> $${parseFloat(data.monto).toLocaleString('es-AR')}</p>
                        <p><strong>Monto Total:</strong> $${parseFloat(data.monto_total).toLocaleString('es-AR')}</p>
                        <p><strong>Cuotas:</strong> ${data.cuotas}</p>
                        <p><strong>Valor por cuota:</strong> $${parseFloat(data.monto_cuota).toLocaleString('es-AR')}</p>
                        <p><strong>Estado:</strong> <span class="estado-${data.estado.toLowerCase()}">${data.estado}</span></p>
                    `;
                    
                    // Actualizar ID del crédito en el formulario
                    document.getElementById('credito_id').value = data.id;
                    
                    // Llenar selector de cuotas
                    const selectCuotas = document.getElementById('nro_cuota');
                    selectCuotas.innerHTML = '';
                    
                    data.cuotas_detalle.forEach(cuota => {
                        if (cuota.adeudado > 0) {
                            const option = document.createElement('option');
                            option.value = cuota.nro;
                            option.textContent = `Cuota ${cuota.nro} - Adeuda: $${parseFloat(cuota.adeudado).toLocaleString('es-AR')}`;
                            option.dataset.adeudado = cuota.adeudado;
                            selectCuotas.appendChild(option);
                        }
                    });
                    
                    // Actualizar monto a pagar según la cuota seleccionada
                    if (selectCuotas.options.length > 0) {
                        document.getElementById('monto').value = selectCuotas.options[0].dataset.adeudado;
                    }
                    
                    // Llenar tabla de cuotas
                    const tablaBody = document.getElementById('cuotas-body');
                    tablaBody.innerHTML = '';
                    
                    data.cuotas_detalle.forEach(cuota => {
                        const tr = document.createElement('tr');
                        const intereses = cuota.dias_retraso > 0 ? parseFloat(data.monto_cuota) * 0.005 * cuota.dias_retraso : 0;
                        const totalPagar = parseFloat(data.monto_cuota) + intereses;
                        
                        let estadoTexto, estadoClase;
                        if (cuota.pagado >= totalPagar) {
                            estadoTexto = 'Pagado';
                            estadoClase = 'estado-cancelado';
                        } else {
                            estadoTexto = 'Pendiente';
                            estadoClase = cuota.dias_retraso > 0 ? 'estado-mora' : 'estado-activo';
                        }
                        
                        tr.innerHTML = `
                            <td>${cuota.nro}</td>
                            <td>${cuota.fecha_vencimiento}</td>
                            <td>$${parseFloat(data.monto_cuota).toLocaleString('es-AR')}</td>
                            <td>$${intereses.toLocaleString('es-AR')}</td>
                            <td>$${totalPagar.toLocaleString('es-AR')}</td>
                            <td>$${parseFloat(cuota.pagado).toLocaleString('es-AR')}</td>
                            <td><span class="${estadoClase}">${estadoTexto}</span></td>
                        `;
                        
                        tablaBody.appendChild(tr);
                    });
                    
                    // Mostrar el detalle y el formulario
                    document.getElementById('detalle-credito').style.display = 'block';
                    document.getElementById('formulario-pago').style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Actualizar monto al cambiar la cuota
        document.getElementById('nro_cuota').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('monto').value = selectedOption.dataset.adeudado;
        });
        
        // Botón para anular intereses
       
document.getElementById('btn-anular-intereses').addEventListener('click', function() {
    const selectCuotas = document.getElementById('nro_cuota');
    const selectedOption = selectCuotas.options[selectCuotas.selectedIndex];
    const cuotaNum = selectedOption.value;
    const creditoId = document.getElementById('credito_id').value;
    
    fetch('anular_intereses.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `credito_id=${creditoId}&nro_cuota=${cuotaNum}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar notificación
            mostrarNotificacion('Intereses anulados correctamente. Cuota marcada como pagada.', 'exito');
            
            // Recargar detalles del crédito
            cargarDetalleCredito(creditoId);
        } else {
            mostrarNotificacion('Error al anular intereses: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al procesar la solicitud', 'error');
    });
});
        
        // Función para mostrar notificaciones
        function mostrarNotificacion(mensaje, tipo) {
            // Crear el elemento de notificación
            const notificacion = document.createElement('div');
            notificacion.className = 'mensaje-notificacion ' + (tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error');
            notificacion.textContent = mensaje;
            
            // Agregar al DOM
            document.body.appendChild(notificacion);
            
            // Eliminar después de 5 segundos
            setTimeout(() => {
                notificacion.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notificacion);
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>