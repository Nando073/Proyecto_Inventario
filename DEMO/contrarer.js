document.addEventListener('DOMContentLoaded', () => {
    const hamburguesa = document.querySelector('.mostrar-menu');
    const cerrar = document.querySelector('.esconder-menu');
    const sidebar = document.querySelector('aside');
    const main = document.querySelector('main');

    // Detecta si es móvil
    const esMovil = () => window.innerWidth <= 768;

    // Inicializa el aside según el estado guardado
    function inicializarAside() {
        if (!sidebar || !main) return;

        if (esMovil()) {
            const estadoGuardado = localStorage.getItem('asideMenuMovil');
            if (estadoGuardado === 'open') {
                sidebar.classList.remove('hidden');
                main.classList.remove('expanded');
            } else {
                sidebar.classList.add('hidden');
                main.classList.add('expanded');
            }
        } else {
            const estadoEscritorio = localStorage.getItem('asideMenuEscritorio');
            if (estadoEscritorio === 'closed') {
                sidebar.classList.add('hidden');
                main.classList.add('expanded');
            } else {
                sidebar.classList.remove('hidden');
                main.classList.remove('expanded');
            }
        }
    }

    // Abrir/cerrar menú con hamburguesa (todas las pantallas)
    if (hamburguesa) {
        hamburguesa.addEventListener('click', (e) => {
            e.preventDefault();
            if (sidebar && main) {
                const estaAbierto = !sidebar.classList.contains('hidden');
                if (estaAbierto) {
                    sidebar.classList.add('hidden');
                    main.classList.add('expanded');
                    if (esMovil()) {
                        localStorage.setItem('asideMenuMovil', 'closed');
                    } else {
                        localStorage.setItem('asideMenuEscritorio', 'closed');
                    }
                } else {
                    sidebar.classList.remove('hidden');
                    main.classList.remove('expanded');
                    if (esMovil()) {
                        localStorage.setItem('asideMenuMovil', 'open');
                    } else {
                        localStorage.setItem('asideMenuEscritorio', 'open');
                    }
                }
            }
        });
    }

    // Cerrar menú con X (todas las pantallas)
    if (cerrar) {
        cerrar.addEventListener('click', (e) => {
            e.preventDefault();
            if (sidebar && main) {
                sidebar.classList.add('hidden');
                main.classList.add('expanded');
                if (esMovil()) {
                    localStorage.setItem('asideMenuMovil', 'closed');
                } else {
                    localStorage.setItem('asideMenuEscritorio', 'closed');
                }
            }
        });
    }

    // Cerrar aside al hacer clic en enlaces (solo en móvil)
    document.querySelectorAll('aside a').forEach(enlace => {
        enlace.addEventListener('click', () => {
            if (esMovil() && sidebar && main) {
                sidebar.classList.add('hidden');
                main.classList.add('expanded');
                localStorage.setItem('asideMenuMovil', 'closed');
            }
        });
    });

    // Reinicializar al cambiar el tamaño de la ventana
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            inicializarAside();
        }, 200);
    });

    // Ejecutar inicialización al cargar
    inicializarAside();
});

// Detectar navegación con botón atrás del navegador
window.addEventListener('pageshow', (event) => {
    if (event.persisted || 
        (performance.getEntriesByType('navigation')[0] && 
         performance.getEntriesByType('navigation')[0].type === 'back_forward')) {
        window.location.reload();
    }
});

// Guardar estado de los menús desplegables (details)
// document.addEventListener('DOMContentLoaded', () => {
//     document.querySelectorAll('details').forEach((detail, index) => {
//         // Restaurar estado guardado
//         const estadoGuardado = localStorage.getItem('menuDetail' + index);
//         if (estadoGuardado === 'true') {
//             detail.open = true;
//         } else if (estadoGuardado === 'false') {
//             detail.open = false;
//         }
        
//         // Guardar estado al cambiar
//         detail.addEventListener('toggle', function() {
//             localStorage.setItem('menuDetail' + index, detail.open);
//         });
//     });
// });

// Toggle del menú de usuario (perfil)
function toggleMenu() {
    const menuUsuario = document.getElementById('menuUsuario');
    if (menuUsuario) {
        if (menuUsuario.style.display === 'block') {
            menuUsuario.style.display = 'none';
        } else {
            menuUsuario.style.display = 'block';
        }
    }
}

// Agregar evento click al perfil en móvil
document.addEventListener('DOMContentLoaded', () => {
    const perfil = document.getElementById('perfilUsuario');
    const menuUsuario = document.getElementById('menuUsuario');
    
    if (perfil && menuUsuario) {
        perfil.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });
        
        // Cerrar menú al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!perfil.contains(e.target)) {
                menuUsuario.style.display = 'none';
            }
        });
    }
});