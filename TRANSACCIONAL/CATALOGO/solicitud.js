// ========== BÚSQUEDA EN TIEMPO REAL DE MATERIALES ==========
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchMaterial');
    const suggestionsList = document.getElementById('suggestionsList');
    const btnLimpiar = document.getElementById('btnLimpiarBusqueda');
    
    // Verificar que los elementos existan antes de continuar
    if (!searchInput || !suggestionsList || !btnLimpiar) {
        console.log('Elementos de búsqueda no encontrados');
        return;
    }
    
    let materiales = [];

    // Recopilar todos los materiales con su información
    document.querySelectorAll('.material-item').forEach(item => {
        const nombre = item.dataset.materialNombre;
        const id = item.dataset.materialId;
        const nombreDisplay = item.querySelector('.material-nombre').textContent;
        const categoria = item.closest('.accordion-item').querySelector('.accordion-button').textContent
            .replace(/[0-9]/g, '').trim();
        materiales.push({
            id: id,
            nombre: nombre,
            nombreDisplay: nombreDisplay,
            categoria: categoria,
            element: item,
            accordionId: item.closest('.accordion-collapse').id,
            accordionItem: item.closest('.accordion-item')
        });
    });

    // Búsqueda en tiempo real con autocompletado
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length === 0) {
            limpiarBusqueda();
            return;
        }

        // Filtrar materiales que coincidan
        const coincidencias = materiales.filter(mat => mat.nombre.includes(query));

        // Mostrar sugerencias de autocompletado
        mostrarSugerencias(coincidencias, query);

        // Resaltar materiales coincidentes
        resaltarMateriales(coincidencias);
    });

    // Función para mostrar sugerencias
    function mostrarSugerencias(coincidencias, query) {
        if (coincidencias.length === 0) {
            suggestionsList.innerHTML = '<div class="p-2 text-muted"><i class="bi bi-search"></i> No se encontraron materiales</div>';
            suggestionsList.style.display = 'block';
            return;
        }

        suggestionsList.innerHTML = '';
        coincidencias.slice(0, 8).forEach(mat => {
            const div = document.createElement('div');
            div.className = 'suggestion-item p-2 border-bottom';
            div.style.cursor = 'pointer';
            
            // Resaltar la parte que coincide
            const regex = new RegExp('(' + query + ')', 'gi');
            const nombreResaltado = mat.nombreDisplay.replace(regex, '<strong class="text-primary">$1</strong>');
            
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div>${nombreResaltado}</div>
                        <small class="text-muted"><i class="bi bi-folder"></i> ${mat.categoria}</small>
                    </div>
                    <i class="bi bi-arrow-right-circle text-primary"></i>
                </div>
            `;
            
            // Click en sugerencia
            div.addEventListener('click', function() {
                searchInput.value = mat.nombreDisplay;
                suggestionsList.style.display = 'none';
                expandirYResaltar(mat);
            });
            
            // Hover efecto
            div.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            div.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'white';
            });
            
            suggestionsList.appendChild(div);
        });
        
        suggestionsList.style.display = 'block';
    }

    // Función para resaltar materiales
    function resaltarMateriales(coincidencias) {
        // Primero ocultar todos los materiales
        materiales.forEach(mat => {
            mat.element.style.display = 'none';
            mat.element.querySelector('.material-card').classList.remove('border-primary', 'border-3', 'highlight-material');
        });

        // Colapsar todos los acordeones
        document.querySelectorAll('.accordion-collapse').forEach(collapse => {
            collapse.classList.remove('show');
        });
        document.querySelectorAll('.accordion-button').forEach(btn => {
            btn.classList.add('collapsed');
            btn.setAttribute('aria-expanded', 'false');
        });

        // Mostrar y expandir acordeones con coincidencias
        const categoriasExpandidas = new Set();
        coincidencias.forEach(mat => {
            mat.element.style.display = 'block';
            mat.element.querySelector('.material-card').classList.add('border-primary', 'border-3', 'highlight-material');
            
            // Expandir acordeón
            if (!categoriasExpandidas.has(mat.accordionId)) {
                const collapse = document.getElementById(mat.accordionId);
                const button = mat.accordionItem.querySelector('.accordion-button');
                
                collapse.classList.add('show');
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
                categoriasExpandidas.add(mat.accordionId);
            }
        });

        // Scroll al primer resultado
        if (coincidencias.length > 0) {
            setTimeout(() => {
                coincidencias[0].element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
    }

    // Función para expandir y resaltar un material específico
    function expandirYResaltar(mat) {
        // Ocultar todos menos el seleccionado
        materiales.forEach(m => {
            if (m.id === mat.id) {
                m.element.style.display = 'block';
                m.element.querySelector('.material-card').classList.add('border-success', 'border-3', 'highlight-material');
            } else {
                m.element.style.display = 'none';
                m.element.querySelector('.material-card').classList.remove('border-primary', 'border-success', 'border-3', 'highlight-material');
            }
        });

        // Expandir su acordeón
        document.querySelectorAll('.accordion-collapse').forEach(collapse => {
            collapse.classList.remove('show');
        });
        document.querySelectorAll('.accordion-button').forEach(btn => {
            btn.classList.add('collapsed');
            btn.setAttribute('aria-expanded', 'false');
        });

        const collapse = document.getElementById(mat.accordionId);
        const button = mat.accordionItem.querySelector('.accordion-button');
        collapse.classList.add('show');
        button.classList.remove('collapsed');
        button.setAttribute('aria-expanded', 'true');

        // Scroll al material
        setTimeout(() => {
            mat.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }

    // Función para limpiar búsqueda
    function limpiarBusqueda() {
        suggestionsList.style.display = 'none';
        suggestionsList.innerHTML = '';
        
        // Mostrar todos los materiales
        materiales.forEach(mat => {
            mat.element.style.display = 'block';
            mat.element.querySelector('.material-card').classList.remove('border-primary', 'border-success', 'border-3', 'highlight-material');
        });

        // Colapsar todos los acordeones
        document.querySelectorAll('.accordion-collapse').forEach(collapse => {
            collapse.classList.remove('show');
        });
        document.querySelectorAll('.accordion-button').forEach(btn => {
            btn.classList.add('collapsed');
            btn.setAttribute('aria-expanded', 'false');
        });
    }

    // Botón limpiar
    btnLimpiar.addEventListener('click', function() {
        searchInput.value = '';
        limpiarBusqueda();
        searchInput.focus();
    });

    // Cerrar sugerencias al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // Soporte para teclas de navegación
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            limpiarBusqueda();
        }
    });
});

// ========== CARRITO DE SOLICITUDES ==========
document.addEventListener('DOMContentLoaded', function() {
    const carrito = [];
    const carritoMateriales = document.getElementById('carrito-materiales');
    const totalMateriales = document.getElementById('totalMateriales');
    const formSolicitud = document.getElementById('formSolicitud');
    
    // Verificar que los elementos existan
    if (!carritoMateriales || !totalMateriales) {
        console.log('Elementos del carrito no encontrados');
        return;
    }

    // Añadir material al carrito
    document.querySelectorAll('.agregar-egreso').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const cardBody = this.closest('.card-body');
            const nombre = cardBody.querySelector('.card-title').textContent;
            
            // Encuentra la categoría
            const accordionItem = this.closest('.accordion-item');
            const categoria = accordionItem.querySelector('.accordion-button').textContent
                .replace(/[0-9]/g, '')
                .trim();
                
            const stock = cardBody.querySelector('input[type="number"]').max;
            const cantidadInput = document.getElementById('cantidad_' + id);
            const cantidad = cantidadInput ? cantidadInput.value : '';

            // Validaciones
            if (!cantidad || cantidad <= 0) {
                alert('Ingrese una cantidad válida para "' + nombre + '"');
                return;
            }
            if (parseInt(cantidad) > parseInt(stock)) {
                alert('No puede egresar más que el stock disponible.');
                return;
            }
            
            // Evitar duplicados
            if (carrito.find(item => item.id === id)) {
                alert('Este material ya está en el carrito.');
                return;
            }
            
            // Agregar al carrito
            carrito.push({id, nombre, categoria, cantidad});
            
            // Efecto visual: botón rojo y desactivado
            this.classList.remove('btn-primary');
            this.classList.add('btn-danger');
            this.disabled = true;
            this.textContent = 'Agregado';
            
            renderCarrito();
            actualizarBadgeCarrito();
        });
    });

    // Renderizar carrito
    function renderCarrito() {
        carritoMateriales.innerHTML = '';
        let sumaTotal = 0;
        
        carrito.forEach((item, idx) => {
            sumaTotal += parseInt(item.cantidad) || 0;
            const row = document.createElement('div');
            row.className = 'row align-items-center mb-2';
            row.innerHTML = `
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="text" class="form-control" value="${item.categoria}" readonly>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="hidden" name="materiales[${idx}][id]" value="${item.id}">
                    <input type="text" class="form-control" value="${item.nombre}" readonly>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="number" class="form-control" name="materiales[${idx}][cantidad]" value="${item.cantidad}" readonly>
                </div>
                <div class="col-12 col-md-2 text-center">
                    <button type="button" class="btn btn-danger btn-sm quitar-material btn-responsive" data-idx="${idx}">X</button>
                </div>
            `;
            carritoMateriales.appendChild(row);
        });
        
        totalMateriales.value = sumaTotal;

        // Botón para quitar material
        document.querySelectorAll('.quitar-material').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.dataset.idx;
                const id = carrito[idx].id;
                carrito.splice(idx, 1);
                
                // Reactivar el botón en la card
                const btnCard = document.querySelector('.agregar-egreso[data-id="' + id + '"]');
                if (btnCard) {
                    btnCard.classList.remove('btn-danger');
                    btnCard.classList.add('btn-primary');
                    btnCard.disabled = false;
                    btnCard.textContent = 'Añadir al carrito';
                }
                
                renderCarrito();
                actualizarBadgeCarrito();
            });
        });
    }

    // Actualizar badge del carrito
    function actualizarBadgeCarrito() {
        const badge = document.getElementById('badgeCarrito');
        if (!badge) return;
        
        let sumaTotal = 0;
        carrito.forEach(item => sumaTotal += parseInt(item.cantidad) || 0);
        
        if (sumaTotal > 0) {
            badge.textContent = sumaTotal;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    // Validar formulario antes de enviar
    if (formSolicitud) {
        formSolicitud.addEventListener('submit', function(e) {
            if (carrito.length === 0) {
                alert('Debe añadir al menos un material al carrito.');
                e.preventDefault();
            }
        });
    }
});

// ========== PREVENIR ZOOM EN MÓVILES ==========
document.addEventListener('touchstart', function(e) {
    if (e.touches.length > 1) {
        e.preventDefault();
    }
}, { passive: false });

let lastTouchEnd = 0;
document.addEventListener('touchend', function(e) {
    const now = (new Date()).getTime();
    if (now - lastTouchEnd <= 300) {
        e.preventDefault();
    }
    lastTouchEnd = now;
}, false);

// ========== VALIDACIÓN DE CAMPOS DE CANTIDAD ==========
document.addEventListener('DOMContentLoaded', function() {
    // Validar todos los inputs de cantidad
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            // Solo permitir números enteros
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            
            // Evitar que empiece con 0
            if (e.target.value.length > 1 && e.target.value.startsWith('0')) {
                e.target.value = e.target.value.replace(/^0+/, '');
            }
            
            // Si solo hay un 0, limpiar el campo
            if (e.target.value === '0') {
                e.target.value = '';
            }
        }
    });
    
    // Prevenir entrada de caracteres no numéricos
    document.addEventListener('keypress', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            // Solo permitir teclas numéricas (0-9)
            const charCode = e.which ? e.which : e.keyCode;
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
            }
        }
    });
    
    // Prevenir pegar contenido no válido
    document.addEventListener('paste', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericValue = pastedText.replace(/[^0-9]/g, '').replace(/^0+/, '');
            if (numericValue) {
                e.target.value = numericValue;
            }
        }
    });
});