<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eden - Sistema de Nómina</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h1>Eden</h1>
            <div class="nav-item active" onclick="showSection('sucursales', this)">
                Sucursales
            </div>
            <div class="nav-item" onclick="showSection('empleados', this)">
                Gestión de Empleados
            </div>
            <div class="nav-item" onclick="showSection('nomina', this)">
                Nómina Semanal
            </div>
        </div>

        <div class="main-content">
            <!-- Sección Sucursales -->
            <div id="sucursales" class="section active">
                <div class="header">
                    <h2>Gestión de Sucursales</h2>
                </div>
                <div class="card">
                    <button class="btn btn-primary" onclick="openSucursalModal()">+ Nueva Sucursal</button>
                    <table id="tablaSucursales">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Sección Empleados -->
            <div id="empleados" class="section">
                <div class="header">
                    <h2>Gestión de Empleados</h2>
                </div>
                <div class="card">
                    <!-- Toolbar: búsqueda + filtro + botón -->
                    <div class="empleados-toolbar">
                        <div class="toolbar-item search-item">
                            <label for="buscarEmpleado">Buscar por nombre:</label>
                            <div class="search-wrapper">
                                <span class="search-icon">🔍</span>
                                <input
                                    type="text"
                                    id="buscarEmpleado"
                                    placeholder="Escribe un nombre o apellido..."
                                    oninput="filtrarEmpleados()">
                            </div>
                        </div>
                        <div class="toolbar-item filter-item">
                            <label for="filtroSucursal">Filtrar por sucursal:</label>
                            <select id="filtroSucursal" onchange="filtrarEmpleados()">
                                <option value="">— Todas las sucursales —</option>
                            </select>
                        </div>
                        <div class="toolbar-item">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-add" onclick="openEmpleadoModal()">+ Nuevo Empleado</button>
                        </div>
                    </div>

                    <div id="resultadosCount" class="results-count"></div>

                    <table id="tablaEmpleados">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Sueldo Base</th>
                                <th>Sucursal</th>
                                <th>Fecha Ingreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Sección Nómina -->
            <div id="nomina" class="section">
                <div class="header">
                    <h2>Nómina Semanal</h2>
                </div>
                <div class="card">
                    <div class="payroll-controls">
                        <div class="select-wrapper">
                            <label>Seleccionar Sucursal:</label>
                            <select id="sucursalNomina" onchange="cargarNomina()">
                                <option value="">-- Seleccione una sucursal --</option>
                            </select>
                        </div>
                        <button class="btn btn-success" onclick="generarPDF()">📄 Generar PDF</button>
                    </div>
                    <div id="nominaContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sucursal -->
    <div id="modalSucursal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal" onclick="closeSucursalModal()">×</span>
                <span id="tituloModalSucursal">Nueva Sucursal</span>
            </div>
            <form id="formSucursal" onsubmit="guardarSucursal(event)">
                <input type="hidden" id="sucursalId">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" id="sucursalNombre" required>
                </div>
                <div class="form-group">
                    <label>Dirección:</label>
                    <textarea id="sucursalDireccion" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="text" id="sucursalTelefono">
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <!-- Modal Empleado -->
    <div id="modalEmpleado" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal" onclick="closeEmpleadoModal()">×</span>
                <span id="tituloModalEmpleado">Nuevo Empleado</span>
            </div>
            <form id="formEmpleado" onsubmit="guardarEmpleado(event)">
                <input type="hidden" id="empleadoId">
                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" id="empleadoNombre" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sueldo Base Semanal:</label>
                        <input type="number" id="empleadoSueldo" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Sucursal:</label>
                        <select id="empleadoSucursal" required>
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Fecha de Ingreso:</label>
                    <input type="date" id="empleadoFecha">
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <script src="/js/api.js"></script>
    <script src="/js/app.js"></script>
    <script src="/js/sucursales.js"></script>
    <script src="/js/empleados.js"></script>
    <script src="/js/nomina.js"></script>

</body>

</html>