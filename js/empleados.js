/**
 * empleados.js
 */
const Empleados = {
    async cargar() {
        const tbody = document.querySelector('#tablaEmpleados tbody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center">Cargando...</td></tr>';

        if (App.sucursales.length === 0) {
            const rs = await API.getSucursales();
            if (rs.success) App.sucursales = rs.data;
        }

        const filtro = document.getElementById('filtroSucursal');
        filtro.innerHTML = '<option value="">— Todas las sucursales —</option>';
        App.sucursales.forEach(s => {
            filtro.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
        });

        const response = await API.getEmpleados();
        if (response.success) {
            App.empleados = response.data;
            Empleados.renderizar(App.empleados);
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:red">Error al cargar empleados</td></tr>';
        }
    },

    renderizar(lista, termino = '') {
        const tbody = document.querySelector('#tablaEmpleados tbody');
        tbody.innerHTML = '';
        if (lista.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888">No se encontraron empleados.</td></tr>';
            document.getElementById('resultadosCount').textContent = '';
            return;
        }
        lista.forEach((e, index) => {
            const nombreMostrado = termino ? resaltarTexto(e.nombre, termino) : e.nombre;
            tbody.innerHTML += `
        <tr>
            <td>${index + 1}</td>
            <td>${nombreMostrado}</td>
                    <td>$${parseFloat(e.sueldo_base).toFixed(2)}</td>
                    <td>${e.sucursal_nombre || 'N/A'}</td>
                    <td>${e.fecha_ingreso || '-'}</td>
                    <td class="actions">
                        <button class="btn btn-warning" onclick="Empleados.editar(${e.id})">✏️ Editar</button>
                        <button class="btn btn-danger" onclick="Empleados.eliminar(${e.id})">🗑️ Eliminar</button>
                    </td>
                </tr>`;
        });
        const total = App.empleados.length;
        const mostrando = lista.length;
        document.getElementById('resultadosCount').textContent =
            mostrando < total ? `Mostrando ${mostrando} de ${total} empleados` : `${total} empleado${total !== 1 ? 's' : ''}`;
    },

    filtrar() {
        const termino = document.getElementById('buscarEmpleado').value.trim().toLowerCase();
        const sucursalId = document.getElementById('filtroSucursal').value;
        const resultado = App.empleados.filter(e => {
            const coincideNombre = !termino || e.nombre.toLowerCase().includes(termino);
            const coincideSucursal = !sucursalId || String(e.sucursal_id) === sucursalId;
            return coincideNombre && coincideSucursal;
        });
        Empleados.renderizar(resultado, termino);
    },

    async abrirModal(id = null) {
        document.getElementById('modalEmpleado').classList.add('active');
        document.getElementById('formEmpleado').reset();
        document.getElementById('empleadoId').value = '';
        document.getElementById('tituloModalEmpleado').textContent = 'Nuevo Empleado';

        const select = document.getElementById('empleadoSucursal');
        select.innerHTML = '<option value="">-- Cargando sucursales... --</option>';
        if (App.sucursales.length === 0) {
            const response = await API.getSucursales();
            if (response.success) App.sucursales = response.data;
        }
        select.innerHTML = '<option value="">-- Seleccione --</option>';
        App.sucursales.forEach(s => {
            select.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
        });

        if (id) {
            const e = App.empleados.find(x => x.id == id);
            if (e) {
                document.getElementById('empleadoId').value = e.id;
                document.getElementById('empleadoNombre').value = e.nombre;
                document.getElementById('empleadoSueldo').value = e.sueldo_base;
                document.getElementById('empleadoSucursal').value = e.sucursal_id;
                document.getElementById('empleadoFecha').value = e.fecha_ingreso || '';
                document.getElementById('tituloModalEmpleado').textContent = 'Editar Empleado';
            }
        }
    },

    cerrarModal() {
        document.getElementById('modalEmpleado').classList.remove('active');
    },

    async guardar(e) {
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
            data.id = parseInt(id);
            response = await API.updateEmpleado(data);
        } else {
            response = await API.createEmpleado(data);
        }
        if (response.success) {
            alert('Empleado guardado correctamente');
            Empleados.cerrarModal();
            Empleados.cargar();
        } else {
            alert('Error: ' + response.message);
        }
    },

    editar(id) { Empleados.abrirModal(id); },

    async eliminar(id) {
        if (confirm('¿Está seguro de eliminar este empleado?')) {
            const response = await API.deleteEmpleado(id);
            if (response.success) {
                alert('Empleado eliminado correctamente');
                Empleados.cargar();
            } else {
                alert('Error: ' + response.message);
            }
        }
    }
};

// Helpers globales
function resaltarTexto(texto, termino) {
    if (!termino) return texto;
    const regex = new RegExp(`(${termino.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return texto.replace(regex, '<span class="highlight">$1</span>');
}

// Puentes para onclick antiguos
function openEmpleadoModal(id) { Empleados.abrirModal(id); }
function closeEmpleadoModal() { Empleados.cerrarModal(); }
function guardarEmpleado(e) { Empleados.guardar(e); }
function editarEmpleado(id) { Empleados.editar(id); }
function eliminarEmpleado(id) { Empleados.eliminar(id); }
function filtrarEmpleados() { Empleados.filtrar(); }