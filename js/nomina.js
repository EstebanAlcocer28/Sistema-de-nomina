/**
 * nomina.js
 * Nómina Semanal con columnas visibles de días (L, Ma, Mi, J, V, S, D)
 */
const Nomina = {

    // Días: clave interna y etiqueta visible
    DIAS: [
        { key: 'lunes',     label: 'L',  titulo: 'Lunes' },
        { key: 'martes',    label: 'Ma', titulo: 'Martes' },
        { key: 'miercoles', label: 'Mi', titulo: 'Miércoles' },
        { key: 'jueves',    label: 'J',  titulo: 'Jueves' },
        { key: 'viernes',   label: 'V',  titulo: 'Viernes' },
        { key: 'sabado',    label: 'S',  titulo: 'Sábado' },
        { key: 'domingo',   label: 'D',  titulo: 'Domingo' }
    ],

    // =====================================================
    // SELECT DE SUCURSALES
    // =====================================================
    cargarSelectSucursales() {
        const select = document.getElementById('sucursalNomina');
        select.innerHTML = '<option value="">-- Seleccione una sucursal --</option>';
        App.sucursales.forEach(s => {
            select.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
        });
    },

    // =====================================================
    // CARGA LA NÓMINA DE LA SUCURSAL
    // =====================================================
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

        // Encabezados de los 7 días (visibles)
        const headersDias = this.DIAS
            .map(d => `<th class="th-dia" title="${d.titulo}">${d.label}</th>`)
            .join('');

        let html = `
            <table id="tablaNomina">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Empleado</th>
                        <th>Sueldo Base</th>
                        <th>Pago Diario</th>
                        ${headersDias}
                        <th>Asist.</th>
                        <th>Sanciones</th>
                        <th>Préstamo</th>
                        <th>Extra</th>
                        <th>Adelanto</th>
                        <th>Faltante</th>
                        <th>Tarjeta</th>
                        <th>Total a Pagar</th>
                    </tr>
                </thead>
                <tbody>`;

        empleadosSucursal.forEach((e, index) => {
            const sueldoBase = Number(e.sueldo_base) || 0;
            const pagoDiario = (sueldoBase / 7).toFixed(2);

            // Una celda por cada día, con su input correspondiente
            const celdasDias = this.DIAS.map(d => `
                <td class="td-dia">
                    <input type="text"
                           class="dia-input"
                           data-dia="${d.key}"
                           maxlength="1"
                           value=""
                           placeholder="-"
                           oninput="Nomina.onDiaInput(event, this)"
                           onkeydown="Nomina.onDiaKeydown(event, this)">
                </td>`).join('');

            html += `
                <tr data-empleado-id="${e.id}" data-sueldo="${sueldoBase}">
                    <td>${index + 1}</td>
                    <td class="td-empleado">${e.nombre}</td>
                    <td>$${sueldoBase.toFixed(2)}</td>
                    <td>$${pagoDiario}</td>
                    ${celdasDias}
                    <td class="celda-asistencia">0</td>
                    <td><input type="number" class="input-small sanciones" min="0" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small prestamo" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small extra" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small adelanto" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small faltante" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small tarjeta" min="0" step="0.01" value="0" onchange="Nomina.calcularTotal(this)"></td>
                    <td><input type="number" class="input-small total-pagar" step="0.01" value="${sueldoBase.toFixed(2)}" readonly></td>
                </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    },

    // =====================================================
    // ENTRADA DE DÍAS (A / D / vacío)
    // =====================================================
    onDiaInput(event, input) {
        let valor = input.value.toUpperCase();
        valor = valor.replace(/[^AD]/g, '');
        valor = valor.slice(-1);
        input.value = valor;

        input.classList.remove('estado-A', 'estado-D');
        if (valor === 'A') input.classList.add('estado-A');
        if (valor === 'D') input.classList.add('estado-D');

        this.calcularTotal(input);
    },

    onDiaKeydown(event, input) {
        if (event.key === 'Backspace' || event.key === 'Delete') {
            event.preventDefault();
            input.value = '';
            input.classList.remove('estado-A', 'estado-D');
            this.calcularTotal(input);
        }
    },

    // =====================================================
    // CÁLCULOS
    // =====================================================
    contarAsistencia(row) {
        let asistencia = 0;
        row.querySelectorAll('.dia-input').forEach(inp => {
            if (inp.value.toUpperCase() === 'A') asistencia++;
        });
        return asistencia;
    },

    calcularTotal(input) {
        const row = input.closest('tr');
        const sueldoBase = parseFloat(row.dataset.sueldo);
        const pagoDiario = sueldoBase / 7;

        const asistencia = this.contarAsistencia(row);
        const pagoAsistencia = pagoDiario * asistencia;

        const sanciones = parseFloat(row.querySelector('.sanciones').value) || 0;
        const prestamo  = parseFloat(row.querySelector('.prestamo').value)  || 0;
        const extra     = parseFloat(row.querySelector('.extra').value)     || 0;
        const adelanto  = parseFloat(row.querySelector('.adelanto').value)  || 0;
        const faltante  = parseFloat(row.querySelector('.faltante').value)  || 0;
        const tarjeta   = parseFloat(row.querySelector('.tarjeta').value)   || 0;

        const totalSanciones = sanciones * 100;
        const total = (pagoAsistencia - totalSanciones - prestamo - adelanto - tarjeta - faltante) + extra;

        row.querySelector('.celda-asistencia').textContent = asistencia;
        row.querySelector('.total-pagar').value = total.toFixed(2);
    },

    // =====================================================
    // GENERAR PDF
    // =====================================================
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

            // Días tal cual (A / D / '')
            const dias = {};
            this.DIAS.forEach(d => {
                const inp = row.querySelector(`.dia-input[data-dia="${d.key}"]`);
                dias[d.key] = inp ? inp.value.toUpperCase() : '';
            });

            datos.push({
                nombre: empleado.nombre,
                sueldo_base: empleado.sueldo_base,
                pago_diario: (Number(empleado.sueldo_base) / 7).toFixed(2),
                ...dias,
                asistencia: this.contarAsistencia(row),
                sanciones: row.querySelector('.sanciones').value,
                prestamo:  row.querySelector('.prestamo').value,
                extra:     row.querySelector('.extra').value,
                adelanto:  row.querySelector('.adelanto').value,
                faltante:  row.querySelector('.faltante').value,
                tarjeta:   row.querySelector('.tarjeta').value,
                total:     row.querySelector('.total-pagar').value
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

// Puentes globales
function cargarNomina() { Nomina.cargar(); }
function calcularTotal(input) { Nomina.calcularTotal(input); }
function generarPDF() { Nomina.generarPDF(); }