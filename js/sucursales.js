
/**
 * sucursales.js
 * Responsabilidad: todo lo relacionado con sucursales.
 * Depende de: API (api.js)
 */
const Sucursales = {
 
    /** Cache local de sucursales cargadas desde el servidor */
    lista: [],
 
    // ── Carga y render ──────────────────────────────────────────
 
    async cargar() {
        const tbody = document.querySelector('#tablaSucursales tbody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center">Cargando...</td></tr>';
 
        const res = await API.getSucursales();
        if (!res.success) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:red">Error al cargar datos</td></tr>';
            return;
        }
 
        this.lista = res.data;
        this._render();
    },
 
    _render() {
        const tbody = document.querySelector('#tablaSucursales tbody');
        if (this.lista.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888">No hay sucursales registradas.</td></tr>';
            return;
        }
 
        tbody.innerHTML = this.lista.map(s => `
            <tr>
                <td>${s.id}</td>
                <td>${s.nombre}</td>
                <td>${s.direccion || '-'}</td>
                <td>${s.telefono  || '-'}</td>
                <td class="actions">
                    <button class="btn btn-warning" onclick="Sucursales.abrirModal(${s.id})">✏️ Editar</button>
                    <button class="btn btn-danger"  onclick="Sucursales.eliminar(${s.id})">🗑️ Eliminar</button>
                </td>
            </tr>`).join('');
    },
 
    // ── Modal ───────────────────────────────────────────────────
 
    abrirModal(id = null) {
        const modal = document.getElementById('modalSucursal');
        document.getElementById('formSucursal').reset();
        document.getElementById('sucursalId').value = '';
        document.getElementById('tituloModalSucursal').textContent = 'Nueva Sucursal';
 
        if (id !== null) {
            const s = this.lista.find(x => x.id == id);
            if (s) {
                document.getElementById('sucursalId').value       = s.id;
                document.getElementById('sucursalNombre').value   = s.nombre;
                document.getElementById('sucursalDireccion').value = s.direccion || '';
                document.getElementById('sucursalTelefono').value = s.telefono   || '';
                document.getElementById('tituloModalSucursal').textContent = 'Editar Sucursal';
            }
        }
 
        modal.classList.add('active');
    },
 
    cerrarModal() {
        document.getElementById('modalSucursal').classList.remove('active');
    },
 
    // ── CRUD ────────────────────────────────────────────────────
 
    async guardar(e) {
        e.preventDefault();
        const id = document.getElementById('sucursalId').value;
        const data = {
            nombre:    document.getElementById('sucursalNombre').value,
            direccion: document.getElementById('sucursalDireccion').value,
            telefono:  document.getElementById('sucursalTelefono').value,
        };
 
        const res = id
            ? await API.updateSucursal({ ...data, id: parseInt(id) })
            : await API.createSucursal(data);
 
        if (res.success) {
            alert('Sucursal guardada correctamente');
            this.cerrarModal();
            await this.cargar();
        } else {
            alert('Error: ' + res.message);
        }
    },
 
    async eliminar(id) {
        if (!confirm('¿Está seguro de eliminar esta sucursal?')) return;
 
        const res = await API.deleteSucursal(id);
        if (res.success) {
            alert('Sucursal eliminada correctamente');
            await this.cargar();
        } else {
            alert('Error: ' + res.message);
        }
    },
 
    // ── Helpers públicos usados por otros módulos ───────────────
 
    /**
     * Devuelve la lista cargada. Si está vacía, la carga primero.
     */
    async obtener() {
        if (this.lista.length === 0) {
            const res = await API.getSucursales();
            if (res.success) this.lista = res.data;
        }
        return this.lista;
    },
 
    /**
     * Puebla cualquier <select> con las sucursales disponibles.
     * @param {string} selectId  - id del elemento <select>
     * @param {string} placeholder - texto de la opción vacía
     */
    async poblarSelect(selectId, placeholder = '-- Seleccione --') {
        const select = document.getElementById(selectId);
        select.innerHTML = `<option value="">${placeholder}</option>`;
        const lista = await this.obtener();
        lista.forEach(s => {
            select.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
        });
    },
};