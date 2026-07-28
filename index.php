<!DOCTYPE html>
<html lang="es">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eden - Sistema de Nómina</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F8F8 100%);
            min-height: 100vh;
        }
 
        .container {
            display: flex;
            min-height: 100vh;
        }
 
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #B30000 0%, #CC0000 100%);
            color: #FFFFFF;
            padding: 20px 0;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
 
        .sidebar h1 {
            padding: 0 20px 20px;
            font-size: 24px;
            border-bottom: 2px solid #FFFFFF;
            margin-bottom: 20px;
        }
 
        .nav-item {
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }
 
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: #FFCCCC;
        }
 
        .nav-item.active {
            background: rgba(255, 255, 255, 0.25);
            border-left-color: #FFFFFF;
            font-weight: bold;
        }
 
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
        }
 
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(204, 0, 0, 0.15);
            margin-bottom: 30px;
        }
 
        .header h2 {
            color: #B30000;
            font-size: 28px;
        }
 
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(204, 0, 0, 0.1);
            margin-bottom: 20px;
        }
 
        form .form-group {
            margin-bottom: 15px;
        }
 
        label {
            display: block;
            margin-bottom: 5px;
            color: #B30000;
            font-weight: 500;
        }
 
        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #FFCCCC;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
 
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #B30000;
        }
 
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            font-weight: 500;
        }
 
        .btn-primary {
            background: #B30000;
            color: #FFFFFF;
        }
 
        .btn-primary:hover {
            background: #CC0000;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(179, 0, 0, 0.4);
        }
 
        .btn-success {
            background: #009933;
            color: white;
        }
 
        .btn-success:hover {
            background: #00B347;
        }
 
        .btn-danger {
            background: #CC0000;
            color: white;
        }
 
        .btn-danger:hover {
            background: #990000;
        }
 
        .btn-warning {
            background: #FF6600;
            color: white;
        }
 
        .btn-warning:hover {
            background: #CC5200;
        }
 
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
 
        th {
            background: #B30000;
            color: #FFFFFF;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
 
        td {
            padding: 10px;
            border-bottom: 1px solid #FFCCCC;
        }
 
        tr:hover {
            background: #FFF0F0;
        }
 
        .actions {
            display: flex;
            gap: 10px;
        }
 
        .section {
            display: none;
        }
 
        .section.active {
            display: block;
        }
 
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
 
        .input-small {
            width: 80px;
            text-align: right;
        }
 
        .total-cell {
            background: #FFCCCC;
            font-weight: bold;
            color: #B30000;
        }
 
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
 
        .alert-success {
            background: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }
 
        .alert-error {
            background: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }
 
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
 
        .modal.active {
            display: flex;
        }
 
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }
 
        .modal-header {
            color: #B30000;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #FFCCCC;
        }
 
        .close-modal {
            float: right;
            cursor: pointer;
            font-size: 28px;
            color: #B30000;
            line-height: 20px;
        }
 
        .payroll-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }
 
        .select-wrapper {
            flex: 1;
        }
 
        /* ── Filtros de empleados ── */
        .empleados-toolbar {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
 
        .empleados-toolbar .toolbar-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
 
        .empleados-toolbar .toolbar-item.search-item {
            flex: 1;
            min-width: 200px;
        }
 
        .empleados-toolbar .toolbar-item.filter-item {
            min-width: 200px;
        }
 
        .search-wrapper {
            position: relative;
        }
 
        .search-wrapper input {
            padding-left: 36px;
        }
 
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #B30000;
            font-size: 15px;
            pointer-events: none;
        }
 
        .empleados-toolbar .btn-add {
            margin-bottom: 1px; /* align with inputs */
        }
 
        .results-count {
            font-size: 13px;
            color: #888;
            margin-top: 10px;
            margin-bottom: -10px;
        }
 
        .highlight {
            background: #FFE0E0;
            border-radius: 2px;
            padding: 0 2px;
            font-weight: 600;
            color: #B30000;
        }
 
        tr.hidden-row {
            display: none;
        }
    </style>
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
                                    oninput="filtrarEmpleados()"
                                >
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
 
    <script>
        let sucursales = [];
        let empleados = [];
        let empleadosNomina = [];
 
        async function apiRequest(action, method = 'GET', data = null) {
            try {
                const options = {
                    method: method,
                    headers: { 'Content-Type': 'application/json' }
                };
                if (data && method !== 'GET') {
                    options.body = JSON.stringify(data);
                }
                const response = await fetch(`api.php?action=${action}`, options);
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error en la petición:', error);
                alert('Error de conexión con el servidor');
                return { success: false, message: 'Error de conexión' };
            }
        }
 
        // Navegación — recibe el elemento clicado como parámetro (evita `event` global)
        function showSection(section, el) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            document.getElementById(section).classList.add('active');
            el.classList.add('active');
 
            if (section === 'sucursales') cargarSucursales();
            if (section === 'empleados') cargarEmpleados();
            if (section === 'nomina') cargarSelectSucursales();
        }
 
        // ==================== SUCURSALES ====================
        async function cargarSucursales() {
            const tbody = document.querySelector('#tablaSucursales tbody');
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center">Cargando...</td></tr>';
            const response = await apiRequest('getSucursales');
            if (response.success) {
                sucursales = response.data;
                tbody.innerHTML = '';
                sucursales.forEach(s => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${s.id}</td>
                            <td>${s.nombre}</td>
                            <td>${s.direccion || '-'}</td>
                            <td>${s.telefono || '-'}</td>
                            <td class="actions">
                                <button class="btn btn-warning" onclick="editarSucursal(${s.id})">✏️ Editar</button>
                                <button class="btn btn-danger" onclick="eliminarSucursal(${s.id})">🗑️ Eliminar</button>
                            </td>
                        </tr>`;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:red">Error al cargar datos</td></tr>';
            }
        }
 
        function openSucursalModal(id = null) {
            document.getElementById('modalSucursal').classList.add('active');
            document.getElementById('formSucursal').reset();
            document.getElementById('sucursalId').value = '';
            document.getElementById('tituloModalSucursal').textContent = 'Nueva Sucursal';
            if (id !== null) {
                const s = sucursales.find(x => x.id == id);
                if (s) {
                    document.getElementById('sucursalId').value = s.id;
                    document.getElementById('sucursalNombre').value = s.nombre;
                    document.getElementById('sucursalDireccion').value = s.direccion || '';
                    document.getElementById('sucursalTelefono').value = s.telefono || '';
                    document.getElementById('tituloModalSucursal').textContent = 'Editar Sucursal';
                }
            }
        }
 
        function closeSucursalModal() {
            document.getElementById('modalSucursal').classList.remove('active');
        }
 
        async function guardarSucursal(e) {
            e.preventDefault();
            const id = document.getElementById('sucursalId').value;
            const data = {
                nombre: document.getElementById('sucursalNombre').value,
                direccion: document.getElementById('sucursalDireccion').value,
                telefono: document.getElementById('sucursalTelefono').value
            };
            let response;
            if (id) {
                data.id = parseInt(id);
                response = await apiRequest('updateSucursal', 'POST', data);
            } else {
                response = await apiRequest('createSucursal', 'POST', data);
            }
            if (response.success) {
                alert('Sucursal guardada correctamente');
                closeSucursalModal();
                cargarSucursales();
            } else {
                alert('Error: ' + response.message);
            }
        }
 
        function editarSucursal(id) { openSucursalModal(id); }
 
        async function eliminarSucursal(id) {
            if (confirm('¿Está seguro de eliminar esta sucursal?')) {
                const response = await apiRequest(`deleteSucursal&id=${id}`, 'GET');
                if (response.success) {
                    alert('Sucursal eliminada correctamente');
                    cargarSucursales();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        }
 
        // ==================== EMPLEADOS ====================
        async function cargarEmpleados() {
            const tbody = document.querySelector('#tablaEmpleados tbody');
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center">Cargando...</td></tr>';
 
            // Asegurar que las sucursales estén cargadas para el filtro
            if (sucursales.length === 0) {
                const rs = await apiRequest('getSucursales');
                if (rs.success) sucursales = rs.data;
            }
 
            // Poblar el select de filtro por sucursal
            const filtro = document.getElementById('filtroSucursal');
            filtro.innerHTML = '<option value="">— Todas las sucursales —</option>';
            sucursales.forEach(s => {
                filtro.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
            });
 
            const response = await apiRequest('getEmpleados');
            if (response.success) {
                empleados = response.data;
                renderEmpleados(empleados);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:red">Error al cargar empleados</td></tr>';
            }
        }
 
        /**
         * Renderiza la lista de empleados en la tabla, resaltando el término buscado.
         */
        function renderEmpleados(lista, termino = '') {
            const tbody = document.querySelector('#tablaEmpleados tbody');
            tbody.innerHTML = '';
 
            if (lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888">No se encontraron empleados.</td></tr>';
                document.getElementById('resultadosCount').textContent = '';
                return;
            }
 
            lista.forEach(e => {
                const nombreMostrado = termino
                    ? resaltarTexto(e.nombre, termino)
                    : e.nombre;
 
                tbody.innerHTML += `
                    <tr>
                        <td>${e.id}</td>
                        <td>${nombreMostrado}</td>
                        <td>$${parseFloat(e.sueldo_base).toFixed(2)}</td>
                        <td>${e.sucursal_nombre || 'N/A'}</td>
                        <td>${e.fecha_ingreso || '-'}</td>
                        <td class="actions">
                            <button class="btn btn-warning" onclick="editarEmpleado(${e.id})">✏️ Editar</button>
                            <button class="btn btn-danger" onclick="eliminarEmpleado(${e.id})">🗑️ Eliminar</button>
                        </td>
                    </tr>`;
            });
 
            const total = empleados.length;
            const mostrando = lista.length;
            document.getElementById('resultadosCount').textContent =
                mostrando < total
                    ? `Mostrando ${mostrando} de ${total} empleados`
                    : `${total} empleado${total !== 1 ? 's' : ''}`;
        }
 
        /**
         * Envuelve las coincidencias del término buscado con <span class="highlight">.
         */
        function resaltarTexto(texto, termino) {
            if (!termino) return texto;
            const regex = new RegExp(`(${termino.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return texto.replace(regex, '<span class="highlight">$1</span>');
        }
 
        /**
         * Aplica ambos filtros (búsqueda por nombre + sucursal) sobre el array en memoria.
         */
        function filtrarEmpleados() {
            const termino = document.getElementById('buscarEmpleado').value.trim().toLowerCase();
            const sucursalId = document.getElementById('filtroSucursal').value;
 
            const resultado = empleados.filter(e => {
                const coincideNombre = !termino || e.nombre.toLowerCase().includes(termino);
                const coincideSucursal = !sucursalId || String(e.sucursal_id) === sucursalId;
                return coincideNombre && coincideSucursal;
            });
 
            renderEmpleados(resultado, termino);
        }
 
        async function openEmpleadoModal(id = null) {
            document.getElementById('modalEmpleado').classList.add('active');
            document.getElementById('formEmpleado').reset();
            document.getElementById('empleadoId').value = '';
            document.getElementById('tituloModalEmpleado').textContent = 'Nuevo Empleado';
 
            const select = document.getElementById('empleadoSucursal');
            select.innerHTML = '<option value="">-- Cargando sucursales... --</option>';
 
            if (sucursales.length === 0) {
                const response = await apiRequest('getSucursales');
                if (response.success) sucursales = response.data;
            }
 
            select.innerHTML = '<option value="">-- Seleccione --</option>';
            sucursales.forEach(s => {
                select.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
            });
 
            if (id) {
                const e = empleados.find(x => x.id == id);
                if (e) {
                    document.getElementById('empleadoId').value = e.id;
                    document.getElementById('empleadoNombre').value = e.nombre;
                    document.getElementById('empleadoSueldo').value = e.sueldo_base;
                    document.getElementById('empleadoSucursal').value = e.sucursal_id;
                    document.getElementById('empleadoFecha').value = e.fecha_ingreso || '';
                    document.getElementById('tituloModalEmpleado').textContent = 'Editar Empleado';
                }
            }
        }
 
        function closeEmpleadoModal() {
            document.getElementById('modalEmpleado').classList.remove('active');
        }
 
        async function guardarEmpleado(e) {
            e.preventDefault();
            const id = document.getElementById('empleadoId').value;
            const data = {
                nombre: document.getElementById('empleadoNombre').value,
                sueldo_base: parseFloat(document.getElementById('empleadoSueldo').value),
                sucursal_id: parseInt(document.getElementById('empleadoSucursal').value),
                fecha_ingreso: document.getElementById('empleadoFecha').value
            };
            let response;
            if (id) {
                data.id = parseInt(id); // corregido: era string
                response = await apiRequest('updateEmpleado', 'POST', data);
            } else {
                response = await apiRequest('createEmpleado', 'POST', data);
            }
            if (response.success) {
                alert('Empleado guardado correctamente');
                closeEmpleadoModal();
                cargarEmpleados();
            } else {
                alert('Error: ' + response.message);
            }
        }
 
        function editarEmpleado(id) { openEmpleadoModal(id); }
 
        async function eliminarEmpleado(id) {
            if (confirm('¿Está seguro de eliminar este empleado?')) {
                const response = await apiRequest(`deleteEmpleado&id=${id}`, 'GET');
                if (response.success) {
                    alert('Empleado eliminado correctamente');
                    cargarEmpleados();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        }
 
        // ==================== NÓMINA ====================
        function cargarSelectSucursales() {
            const select = document.getElementById('sucursalNomina');
            select.innerHTML = '<option value="">-- Seleccione una sucursal --</option>';
            sucursales.forEach(s => {
                select.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
            });
        }
 
        async function cargarNomina() {
    const sucursalId = parseInt(document.getElementById('sucursalNomina').value);
    const container = document.getElementById('nominaContainer');
    if (!sucursalId) { container.innerHTML = ''; return; }

    container.innerHTML = '<p>Cargando...</p>';
    const response = await apiRequest(`getEmpleadosBySucursal&sucursal_id=${sucursalId}`);

    if (!response.success) {
        container.innerHTML = '<p style="color:red">Error al cargar empleados</p>';
        return;
    }

    const empleadosSucursal = response.data;
    empleadosNomina = empleadosSucursal;

    if (empleadosSucursal.length === 0) {
        container.innerHTML = '<p>No hay empleados en esta sucursal.</p>';
        return;
    }

    let html = `
        <table id="tablaNomina">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Sueldo Base</th>
                    <th>Días (Asist.)</th>
                    <th>Sanciones</th>
                    <th>Préstamo</th>
                    <th>Extra</th>
                    <th>Adelanto</th>
                    <th>Faltante</th>
                    <th>Pago con Tarjeta</th>
                    <th>Total a Pagar En Efectivo</th>
                </tr>
            </thead>
            <tbody>`;

    empleadosSucursal.forEach(e => {
        const sueldoBase = Number(e.sueldo_base) || 0;
        html += `
            <tr data-empleado-id="${e.id}" data-sueldo="${sueldoBase}">
                <td>${e.nombre}</td>
                <td>$${sueldoBase.toFixed(2)}</td>
                <td><input type="number" class="input-small dias" min="0" max="12" value="7" onchange="calcularTotal(this)"></td>
                <td><input type="number" class="input-small sanciones" min="0" value="0" onchange="calcularTotal(this)"></td>
                <td><input type="number" class="input-small prestamo" min="0" step="0.01" value="0" onchange="calcularTotal(this)"></td>
                <td><input type="number" class="input-small extra" min="0" step="0.01" value="0" onchange="calcularTotal(this)"></td>
                <td><input type="number" class="input-small adelanto" min="0" step="0.01" value="0" onchange="calcularTotal(this)"></td>
                <td><input type="number" class="input-small faltante" min="0" step="0.01" value="0" onchange="calcularTotal(this)"></td>
                <td><input type="number" class="input-small tarjeta" min="0" step="0.01" value="0" onchange="calcularTotal(this)"></td>
                <td><input type="number" class="input-small total-pagar" step="0.01" value="${sueldoBase.toFixed(2)}"></td>
            </tr>`;
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}
 
        function calcularTotal(input) {
    const row = input.closest('tr');
    const sueldoBase = parseFloat(row.dataset.sueldo);
    const dias      = parseFloat(row.querySelector('.dias').value)      || 0;
    const sanciones = parseFloat(row.querySelector('.sanciones').value) || 0;
    const prestamo  = parseFloat(row.querySelector('.prestamo').value)  || 0;
    const extra     = parseFloat(row.querySelector('.extra').value)     || 0;
    const adelanto  = parseFloat(row.querySelector('.adelanto').value)  || 0;
    const faltante  = parseFloat(row.querySelector('.faltante').value)  || 0; 
    const tarjeta   = parseFloat(row.querySelector('.tarjeta').value)   || 0;

    const pagoDiario     = sueldoBase / 7;
    const pagoAsistencia = pagoDiario * dias;
    const totalSanciones = sanciones * 100;
    
    // Se agregó "- faltante" a la ecuación
    const total = (pagoAsistencia - totalSanciones - prestamo - adelanto - tarjeta - faltante) + extra;

    row.querySelector('.total-pagar').value = total.toFixed(2);
}
 
        function generarPDF() {
    const sucursalId = document.getElementById('sucursalNomina').value;
    if (!sucursalId) { alert('Debe seleccionar una sucursal primero'); return; }

    const tabla = document.getElementById('tablaNomina');
    if (!tabla) { alert('No hay datos de nómina para generar'); return; }

    const sucursal = sucursales.find(s => s.id == sucursalId);
    const rows = tabla.querySelectorAll('tbody tr');
    const datos = [];

    rows.forEach(row => {
        const empleadoId = row.dataset.empleadoId;
        const empleado = empleadosNomina.find(e => e.id == empleadoId);
        if (!empleado) return;
        
        datos.push({
            nombre:      empleado.nombre,
            sueldo_base: empleado.sueldo_base,
            dias:        row.querySelector('.dias').value,
            sanciones:   row.querySelector('.sanciones').value,
            prestamo:    row.querySelector('.prestamo').value,
            extra:       row.querySelector('.extra').value,
            adelanto:    row.querySelector('.adelanto').value,
            faltante:    row.querySelector('.faltante').value, // <-- Se agregó esta línea
            tarjeta:     row.querySelector('.tarjeta').value,
            total:       row.querySelector('.total-pagar').value
        });
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'generar_pdf.php';
    form.target = '_blank';

    const inputSucursal = document.createElement('input');
    inputSucursal.type = 'hidden';
    inputSucursal.name = 'sucursal';
    inputSucursal.value = sucursal.nombre;
    form.appendChild(inputSucursal);

    const inputDatos = document.createElement('input');
    inputDatos.type = 'hidden';
    inputDatos.name = 'datos';
    inputDatos.value = JSON.stringify(datos);
    form.appendChild(inputDatos);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
 
        // Inicializar
        cargarSucursales();
    </script>
</body>
</html>