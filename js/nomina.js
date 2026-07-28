/**
 * main.js
 * Responsabilidad: inicialización y navegación.
 */
const App = {

    _secciones: {
        sucursales: () => Sucursales.cargar(),
        empleados:  () => Empleados.cargar(),
        nomina:     () => Nomina.inicializar(),
    },

    init() {
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                App.showSection(item.dataset.section, item);
            });
        });

        Sucursales.cargar();
    },

    showSection(seccion, navEl) {
        document.querySelectorAll('.section').forEach(s  => s.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(seccion).classList.add('active');
        navEl.classList.add('active');
        this._secciones[seccion]?.();
    },
};

document.addEventListener('DOMContentLoaded', () => App.init());