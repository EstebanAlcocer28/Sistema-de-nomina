/**
 * sucursales.js
 */
const Sucursales = {
    async cargar() {
        const tbody = document.querySelector('#tablaSucursales tbody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center">Cargando...</td></tr>';
        const response = await API.getSucursales();
        if (response.success) {
            App.sucursales = response.data;
            tbody.innerHTML = '';
            App.sucursales.forEach(s => {
                tbody.innerHTML += `
                    <tr>
                        <td>${s.id}</td>
                        <td>${s.nombre}</td>
                        <td>${s.direccion || '-'}</td>
                        <td>${s.telefono || '-'}</td>
                        <td class="actions">
                            <button class="btn btn-warning" onclick="Sucursales.editar(${s.id})">✏️ Editar</button>
                            <button class="btn btn-danger" onclick="Sucursales.eliminar(${s.id})">🗑️ Eliminar</button>
                        </td>
                    </tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:red">Error al cargar datos</td></tr>';
        }
    },

    abrirModal(id = null) {
        document.getElementById('modalSucursal').classList.add('active');
        document.getElementById('formSucursal').reset();
        document.getElementById('sucursalId').value = '';
        document.getElementById('tituloModalSucursal').textContent = 'Nueva Sucursal';
        if (id !== null) {
            const s = App.sucursales.find(x => x.id == id);
            if (s) {
                document.getElementById('sucursalId').value = s.id;
                document.getElementById('sucursalNombre').value = s.nombre;
                document.getElementById('sucursalDireccion').value = s.direccion || '';
                document.getElementById('sucursalTelefono').value = s.telefono || '';
                document.getElementById('tituloModalSucursal').textContent = 'Editar Sucursal';
            }
        }
    },

    cerrarModal() {
        document.getElementById('modalSucursal').classList.remove('active');
    },

    async guardar(e) {
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
            response = await API.updateSucursal(data);
        } else {
            response = await API.createSucursal(data);
        }
        if (response.success) {
            alert('Sucursal guardada correctamente');
            Sucursales.cerrarModal();
            Sucursales.cargar();
        } else {
            alert('Error: ' + response.message);
        }
    },

    editar(id) { Sucursales.abrirModal(id); },

    async eliminar(id) {
        if (confirm('¿Está seguro de eliminar esta sucursal?')) {
            const response = await API.deleteSucursal(id);
            if (response.success) {
                alert('Sucursal eliminada correctamente');
                Sucursales.cargar();
            } else {
                alert('Error: ' + response.message);
            }
        }
    }
};

// Mantener funciones globales para los onclick antiguos (si los hay)
function openSucursalModal(id) { Sucursales.abrirModal(id); }
function closeSucursalModal() { Sucursales.cerrarModal(); }
function guardarSucursal(e) { Sucursales.guardar(e); }
function editarSucursal(id) { Sucursales.editar(id); }
function eliminarSucursal(id) { Sucursales.eliminar(id); }