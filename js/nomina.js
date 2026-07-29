/**
 * nomina.js
 */
const Nomina = {
    cargarSelectSucursales() {
        const select = document.getElementById('sucursalNomina');
        select.innerHTML = '<option value="">-- Seleccione una sucursal --</option>';
        App.sucursales.forEach(s => {
            select.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
        });
    },

    async cargar() {
        const sucursalId = parseInt(document.getElementById('sucursalNomina').value);
        const container = document.getElementById('nominaContainer');
        if (!sucursalId) { container.innerHTML = ''; return; }

        container.innerHTML = '<p>Cargando...</p>';
        const response = await API.getEmpleadosBySucursal(sucursalId);
        if (!response.success) {
            container.innerHTML = '<p style="color:red">Error al cargar empleados</p>';
            return;
        }

        const empleadosSucursal = response.data;
        App.empleadosNomina = empleadosSucursal;

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
                    <td><input type="number" class="input-small dias" min="0" max="12" value="7" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small sanciones" min="0" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small prestamo" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small extra" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small adelanto" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small faltante" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small tarjeta" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small total-pagar" step="0.01" value="${sueldoBase.toFixed(2)}"></td>
                </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    },

    calcularTotal(input) {
        const row = input.closest('tr');
        const sueldoBase = parseFloat(row.dataset.sueldo);
        const dias = parseFloat(row.querySelector('.dias').value) || 0;
        const sanciones = parseFloat(row.querySelector('.sanciones').value) || 0;
        const prestamo = parseFloat(row.querySelector('.prestamo').value) || 0;
        const extra = parseFloat(row.querySelector('.extra').value) || 0;
        const adelanto = parseFloat(row.querySelector('.adelanto').value) || 0;
        const faltante = parseFloat(row.querySelector('.faltante').value) || 0;
        const tarjeta = parseFloat(row.querySelector('.tarjeta').value) || 0;

        const pagoDiario = sueldoBase / 7;
        const pagoAsistencia = pagoDiario * dias;
        const totalSanciones = sanciones * 100;
        const total = (pagoAsistencia - totalSanciones - prestamo - adelanto - tarjeta - faltante) + extra;

        row.querySelector('.total-pagar').value = total.toFixed(2);
    },

    generarPDF() {
        const sucursalId = document.getElementById('sucursalNomina').value;
        if (!sucursalId) { alert('Debe seleccionar una sucursal primero'); return; }

        const tabla = document.getElementById('tablaNomina');
        if (!tabla) { alert('No hay datos de nómina para generar'); return; }

        const sucursal = App.sucursales.find(s => s.id == sucursalId);
        const rows = tabla.querySelectorAll('tbody tr');
        const datos = [];

        rows.forEach(row => {
            const empleadoId = row.dataset.empleadoId;
            const empleado = App.empleadosNomina.find(e => e.id == empleadoId);
            if (!empleado) return;

            datos.push({
                nombre: empleado.nombre,
                sueldo_base: empleado.sueldo_base,
                dias: row.querySelector('.dias').value,
                sanciones: row.querySelector('.sanciones').value,
                prestamo: row.querySelector('.prestamo').value,
                extra: row.querySelector('.extra').value,
                adelanto: row.querySelector('.adelanto').value,
                faltante: row.querySelector('.faltante').value,
                tarjeta: row.querySelector('.tarjeta').value,
                total: row.querySelector('.total-pagar').value
            });
        });

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'generar_pdf.php';
        form.target = '_blank';
        form.innerHTML = `
            <input type="hidden" name="sucursal" value="${sucursal.nombre}">
            <input type="hidden" name="datos" value='${JSON.stringify(datos)}'>
        `;
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
};

// Puentes globales para onclick antiguos
function cargarNomina() { Nomina.cargar(); }
function calcularTotal(input) { Nomina.calcularTotal(input); }
function generarPDF() { Nomina.generarPDF(); }