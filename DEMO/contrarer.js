document.addEventListener('DOMContentLoaded', () => {
    const hamburguesa = document.querySelector('.mostrar-menu'); // Icono de menú
    const cerrar = document.querySelector('.esconder-menu'); // Icono de cerrar
    const sidebar = document.querySelector('aside'); // Menú lateral
    const main = document.querySelector('main'); // Contenido principal

    // Establecer la vista inicial con aside visible y main al 80%
    sidebar.classList.remove('hidden');
    main.classList.remove('expanded');

    // Mostrar el menú y reducir el main
    hamburguesa.addEventListener('click', () => {
        sidebar.classList.remove('hidden');
        main.classList.remove('expanded');
    });

    // Ocultar el menú y expandir el main
    cerrar.addEventListener('click', () => {
        sidebar.classList.add('hidden');
        main.classList.add('expanded');
    });
});

