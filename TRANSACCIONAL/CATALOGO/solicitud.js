class EgresoManager {
    constructor() {
        this.carrito = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.actualizarBadgeCarrito();
    }

    bindEvents() {
        // Agregar materiales al carrito
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-agregar')) {
                this.agregarAlCarrito(e.target);
            }
            
            if (e.target.classList.contains('quitar-material')) {
                this.quitarDelCarrito(e.target.dataset.idx);
            }
        });

        // Validar formulario de egreso
        document.getElementById('formEgreso')?.addEventListener('submit', (e) => {
            if (this.carrito.length === 0) {
                e.preventDefault();
                this.mostrarAlerta('Debe añadir al menos un material al carrito.', 'warning');
            }
        });

        // Limpiar carrito cuando se cierra el modal
        document.getElementById('modalCarrito')?.addEventListener('hidden.bs.modal', () => {
            // Opcional: mantener el carrito o limpiarlo
        });
    }

    agregarAlCarrito(boton) {
        const id = boton.dataset.id;
        const nombre = boton.dataset.nombre;
        const stock = boton.dataset.stock;
        const card = boton.closest('.card-body');
        const cantidadInput = card.querySelector('.cantidad-input');
        const cantidad = cantidadInput.value;

        // Validaciones
        if (!this.validarCantidad(cantidad, stock, nombre)) {
            return;
        }

        // Verificar duplicados
        if (this.carrito.find(item => item.id === id)) {
            this.mostrarAlerta('Este material ya está en el carrito.', 'info');
            return;
        }

        // Agregar al carrito
        this.carrito.push({
            id,
            nombre,
            cantidad: parseInt(cantidad),
            stock: parseInt(stock)
        });

        // Actualizar UI
        this.actualizarEstadoBoton(boton, true);
        this.renderCarrito();
        this.actualizarBadgeCarrito();
        
        this.mostrarAlerta(`"${nombre}" agregado al carrito.`, 'success');
    }

    validarCantidad(cantidad, stock, nombre) {
        if (!cantidad || cantidad <= 0) {
            this.mostrarAlerta(`Ingrese una cantidad válida para "${nombre}"`, 'error');
            return false;
        }

        if (parseInt(cantidad) > parseInt(stock)) {
            this.mostrarAlerta('No puede egresar más que el stock disponible.', 'error');
            return false;
        }

        return true;
    }

    actualizarEstadoBoton(boton, agregado) {
        if (agregado) {
            boton.classList.add('added');
            boton.disabled = true;
            boton.innerHTML = '<i class="bi bi-check-circle"></i> Agregado';
        } else {
            boton.classList.remove('added');
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-cart-plus"></i> Añadir al carrito';
        }
    }

    quitarDelCarrito(indice) {
        if (this.carrito[indice]) {
            const material = this.carrito[indice];
            this.carrito.splice(indice, 1);
            
            // Reactivar botón en la card
            const botonCard = document.querySelector(`.btn-agregar[data-id="${material.id}"]`);
            if (botonCard) {
                this.actualizarEstadoBoton(botonCard, false);
            }
            
            this.renderCarrito();
            this.actualizarBadgeCarrito();
        }
    }

    renderCarrito() {
        const contenedor = document.getElementById('carrito-materiales');
        const totalInput = document.getElementById('totalMateriales');
        
        if (!contenedor) return;

        let sumaTotal = 0;
        let html = '';

        this.carrito.forEach((item, idx) => {
            sumaTotal += item.cantidad;
            
            html += `
                <div class="carrito-item">
                    <div class="row align-items-center g-2">
                        <div class="col-12 col-md-5">
                            <input type="hidden" name="materiales[${idx}][id]" value="${item.id}">
                            <input type="text" class="form-control form-control-sm" value="${item.nombre}" readonly>
                        </div>
                        <div class="col-8 col-md-4">
                            <input type="number" class="form-control form-control-sm" 
                                   name="materiales[${idx}][cantidad]" value="${item.cantidad}" 
                                   min="1" max="${item.stock}" readonly>
                        </div>
                        <div class="col-4 col-md-3 text-end">
                            <button type="button" class="btn btn-danger btn-sm quitar-material" 
                                    data-idx="${idx}" title="Quitar del carrito">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        contenedor.innerHTML = html || '<p class="text-muted text-center py-3">No hay materiales en el carrito</p>';
        totalInput.value = sumaTotal;
    }

    actualizarBadgeCarrito() {
        const badge = document.getElementById('badgeCarrito');
        if (!badge) return;

        const total = this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
        
        if (total > 0) {
            badge.textContent = total;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    mostrarAlerta(mensaje, tipo = 'info') {
        // Usar Toast de Bootstrap o alerta simple
        const alertClass = tipo === 'error' ? 'danger' : tipo;
        alert(mensaje); // Puedes reemplazar con un sistema de notificaciones más elegante
    }

    limpiarCarrito() {
        this.carrito = [];
        this.renderCarrito();
        this.actualizarBadgeCarrito();
        
        // Reactivar todos los botones
        document.querySelectorAll('.btn-agregar').forEach(boton => {
            this.actualizarEstadoBoton(boton, false);
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new EgresoManager();
});

// Prevenir zoom en dispositivos móviles
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