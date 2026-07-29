/**
 * api.js
 * Comunicación con el backend. Todas las peticiones pasan por aquí.
 */
const API = {
    async request(action, method = 'GET', data = null, params = {}) {
        try {
            const url = new URL('api.php', window.location.href);
            url.searchParams.set('action', action);
            Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
            const options = {
                method,
                headers: { 'Content-Type': 'application/json' }
            };
            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }
            const response = await fetch(url, options);
            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}`);
            }
            return await response.json();
        } catch (err) {
            console.error('[API]', err);
            alert('Error de conexión con el servidor');
            return { success: false, message: 'Error de conexión' };
        }
    },

    // ── Sucursales ────────────────────────────────────
    getSucursales:           ()   => API.request('getSucursales'),
    createSucursal:          (d)  => API.request('createSucursal', 'POST', d),
    updateSucursal:          (d)  => API.request('updateSucursal', 'POST', d),
    deleteSucursal:          (id) => API.request('deleteSucursal', 'POST', {}, { id }),

    // ── Empleados ─────────────────────────────────────
    getEmpleados:            ()   => API.request('getEmpleados'),
    createEmpleado:          (d)  => API.request('createEmpleado', 'POST', d),
    updateEmpleado:          (d)  => API.request('updateEmpleado', 'POST', d),
    deleteEmpleado:          (id) => API.request('deleteEmpleado', 'POST', {}, { id }),
    getEmpleadosBySucursal:  (id) => API.request('getEmpleadosBySucursal', 'GET', null, { sucursal_id: id })
};