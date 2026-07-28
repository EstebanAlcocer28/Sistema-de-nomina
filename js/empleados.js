/**
 * empleados.js
 * Responsabilidad: todo lo relacionado con empleados.
 * Depende de: API (api.js), Sucursales (sucursales.js)
 */
const Empleados = {

    /** Cache local */
    lista: [],

    // ── Carga y render ──────────────────────────────────────────

    async cargar() {
        const tbody = document.querySelector('#tablaEmpleados tbody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center">Cargando...</td></tr>';

        // Poblar filtro de sucursales
        await Sucursales.poblarSelect('filtroSucursal', '— Todas las sucursales —');

        const res = await API.getEmpleados();
        if (!res.success) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:red">Error al cargar empleados</td></tr>';
            return;
        }

        this.lista = res.data;
        this._render(this.lista);
    },

    /**
     * Renderiza una lista de empleados, resaltando opcionalmente un término.
     */
    _render(lista, termino = '') {
        const tbody = document.querySelector('#tablaEmpleados tbody');

        if (lista.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888">No se encontraron empleados.</td></tr>';
            document.getElementById('resultadosCount').textContent = '';
            return;
        }

        tbody.innerHTML = lista.map(e => {
            const nombreMostrado = termino
                ? this._resaltar(e.nombre, termino)
                : e.nombre;

            return `
                <tr>
                    <td>${e.id}</td>
                    <td>${nombreMostrado}</td>
                    <td>$${parseFloat(e.sueldo_base).toFixed(2)}</td>
                    <td>${e.sucursal_nombre || 'N/A'}</td>
                    <td>${e.fecha_ingreso || '-'}</td>
                    <td class="actions">
                        <button class="btn btn-warning" onclick="Empleados.abrirModal(${e.id})">✏️ Editar</button>
                        <button class="btn btn-danger"  onclick="Empleados.eliminar(${e.id})">🗑️ Eliminar</button>
                    </td>
                </tr>`;
        }).join('');

        const total     = this.lista.length;
        const mostrando = lista.length;
        document.getElementById('resultadosCount').textContent =
            mostrando < total
                ? `Mostrando ${mostrando} de ${total} empleados`
                : `${total} empleado${total !== 1 ? 's' : ''}`;
    },

    /**
     * Envuelve las coincidencias del término con <span class="highlight">.
     */
    _resaltar(texto, termino) {
        const regex = new RegExp(
            `(${termino.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`,
            'gi'
        );
        return texto.replace(regex, '<span class="highlight">$1</span>');
    },

    // ── Filtrado ────────────────────────────────────────────────

    filtrar() {
        const termino    = document.getElementById('buscarEmpleado').value.trim().toLowerCase();
        const sucursalId = document.getElementById('filtroSucursal').value;

        const resultado = this.lista.filter(e => {
            const coincideNombre   = !termino    || e.nombre.toLowerCase().includes(termino);
            const coincideSucursal = !sucursalId || String(e.sucursal_id) === sucursalId;
            return coincideNombre && coincideSucursal;
        });

        this._render(resultado, termino);
    },

    // ── Modal ───────────────────────────────────────────────────

    async abrirModal(id = null) {
        document.getElementById('formEmpleado').reset();
        document.getElementById('empleadoId').value = '';
        document.getElementById('tituloModalEmpleado').textContent = 'Nuevo Empleado';

        // Poblar select de sucursales dentro del modal
        await Sucursales.poblarSelect('empleadoSucursal', '-- Seleccione --');

        if (id !== null) {
            // FIX 1: si la lista aún no cargó, buscar directo en la caché de Sucursales
            // o forzar recarga para garantizar que el dato existe
            let emp = this.lista.find(x => x.id == id);
            if (!emp) {
                const res = await API.getEmpleados();
                if (res.success) {
                    this.lista = res.data;
                    emp = this.lista.find(x => x.id == id);
                }
            }
            if (emp) {
                document.getElementById('empleadoId').value       = emp.id;
                document.getElementById('empleadoNombre').value   = emp.nombre;
                document.getElementById('empleadoSueldo').value   = emp.sueldo_base;
                document.getElementById('empleadoSucursal').value = emp.sucursal_id;
                // FIX 3: fecha vacía → string vacío es aceptable para <input type="date">,
                // pero normalizamos a '' explícitamente para evitar "null" como texto
                document.getElementById('empleadoFecha').value    = emp.fecha_ingreso ?? '';
                document.getElementById('tituloModalEmpleado').textContent = 'Editar Empleado';
            }
        }

        document.getElementById('modalEmpleado').classList.add('active');
    },

    cerrarModal() {
        document.getElementById('modalEmpleado').classList.remove('active');
    },

    // ── CRUD ────────────────────────────────────────────────────

    async guardar(e) {
        e.preventDefault();

        const id         = document.getElementById('empleadoId').value;
        const nombre     = document.getElementById('empleadoNombre').value.trim();
        const sueldo     = parseFloat(document.getElementById('empleadoSueldo').value);
        const sucursalId = document.getElementById('empleadoSucursal').value;
        const fecha      = document.getElementById('empleadoFecha').value;

        // FIX 2: validar sucursal antes de parsear (parseInt('') → NaN)
        if (!sucursalId) {
            alert('Debe seleccionar una sucursal.');
            return;
        }

        // FIX 4: validar sueldo positivo
        if (isNaN(sueldo) || sueldo <= 0) {
            alert('El sueldo base debe ser mayor a 0.');
            return;
        }

        const data = {
            nombre,
            sueldo_base:   sueldo,
            sucursal_id:   parseInt(sucursalId),
            // FIX 3: enviar null al servidor si no hay fecha, no string vacío
            fecha_ingreso: fecha || null,
        };

        const res = id
            ? await API.updateEmpleado({ ...data, id: parseInt(id) })
            : await API.createEmpleado(data);

        if (res.success) {
            alert('Empleado guardado correctamente');
            this.cerrarModal();
            await this.cargar();
        } else {
            alert('Error: ' + res.message);
        }
    },

    async eliminar(id) {
        if (!confirm('¿Está seguro de eliminar este empleado?')) return;

        const res = await API.deleteEmpleado(id);
        if (res.success) {
            alert('Empleado eliminado correctamente');
            await this.cargar();
        } else {
            alert('Error: ' + res.message);
        }
    },
};