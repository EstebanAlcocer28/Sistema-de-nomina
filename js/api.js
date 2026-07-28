/**
 * api.js
 * Responsabilidad única: comunicación con el servidor.
 */
const API = {

    async request(action, method = 'GET', data = null) {
        try {
            const options = {
                method,
                headers: { 'Content-Type': 'application/json' }
            };
            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }
            const response = await fetch(`api.php?action=${action}`, options);
            return await response.json();
        } catch (err) {
            console.error('[API] Error:', err);
            return { success: false, message: 'Error de conexión con el servidor' };
        }
    },

    // ── Sucursales ──────────────────────────────────────────────
    getSucursales:  ()     => API.request('getSucursales'),
    createSucursal: (data) => API.request('createSucursal', 'POST', data),
    updateSucursal: (data) => API.request('updateSucursal', 'POST', data),
    deleteSucursal: (id)   => API.request(`deleteSucursal&id=${id}`),

    // ── Empleados ───────────────────────────────────────────────
    getEmpleados:           ()     => API.request('getEmpleados'),
    createEmpleado:         (data) => API.request('createEmpleado', 'POST', data),
    updateEmpleado:         (data) => API.request('updateEmpleado', 'POST', data),
    deleteEmpleado:         (id)   => API.request(`deleteEmpleado&id=${id}`),
    getEmpleadosBySucursal: (id)   => API.request(`getEmpleadosBySucursal&sucursal_id=${id}`),
};