/**
 * app.js
 * Estado global compartido y navegación.
 */
const App = {
    sucursales: [],
    empleados: [],
    empleadosNomina: []
};

/**
 * Cambia la sección visible y carga los datos correspondientes.
 * @param {string} section - ID de la sección ('sucursales','empleados','nomina')
 * @param {HTMLElement} el - Elemento de navegación clicado
 */
function showSection(section, el) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById(section).classList.add('active');
    el.classList.add('active');

    if (section === 'sucursales') Sucursales.cargar();
    else if (section === 'empleados') Empleados.cargar();
    else if (section === 'nomina') Nomina.cargarSelectSucursales();
}

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    Sucursales.cargar();
});