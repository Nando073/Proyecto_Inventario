document.addEventListener('DOMContentLoaded', () => {
    const hamburguesa = document.querySelector('.mostrar-menu');
    const cerrar = document.querySelector('.esconder-menu');
    const sidebar = document.querySelector('aside');
    const main = document.querySelector('main');

    const esMovil = () => window.innerWidth < 768;

    // Restaurar estado del menú en móviles
    if (esMovil() && sidebar) {
    const estadoMenu = localStorage.getItem('asideMenu');
    if (estadoMenu === 'open') {
        sidebar.classList.remove('hidden');
    } else {
        sidebar.classList.add('hidden');
    }
}

    // Mostrar menú
    if (hamburguesa) {
        hamburguesa.addEventListener('click', () => {
            if (esMovil() && sidebar) {
                sidebar.classList.remove('hidden');
                localStorage.setItem('asideMenu', 'open');
            }
        });
    }

    // Ocultar menú
    if (cerrar) {
        cerrar.addEventListener('click', () => {
            if (esMovil() && sidebar) {
                sidebar.classList.add('hidden');
                localStorage.setItem('asideMenu', 'closed');
            }
        });
    }

    // Cerrar al hacer clic en enlaces
    document.querySelectorAll('aside a').forEach(enlace => {
        enlace.addEventListener('click', () => {
            if (esMovil() && sidebar) {
                sidebar.classList.add('hidden');
                localStorage.setItem('asideMenu', 'closed');
            }
        });
    });

    // Ajustar layout al cambiar tamaño
    window.addEventListener('resize', () => {
        if (!esMovil() && sidebar) {
            sidebar.classList.remove('hidden'); // siempre visible en escritorio
            localStorage.removeItem('asideMenu');
            if (main) main.classList.remove('expanded');
        } else if (esMovil() && sidebar) {
            const estadoMenu = localStorage.getItem('asideMenu');
            if (estadoMenu === 'open') {
                sidebar.classList.remove('hidden');
            } else {
                sidebar.classList.add('hidden');
            }
        }
    });
});