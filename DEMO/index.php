<?php include '../Seguridad.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario</title>
    <link rel="stylesheet" href="styles.css">
    <script src="contrarer.js" defer></script>
</head>
<body>
    <header class="navegacion">
        <div class="logo">
            <img src="../IMG/LOGODDE.png" width="25%">
            <h2>D.D.E.</h2>
            <label for="check" class="mostrar-menu">&#8801</label>
        </div>
        
        <nav class="menu">
            <div class="perfil" id="perfilUsuario" onclick="toggleMenu()">
                <img src="../IMG/usuario.png" width="9%">
                <span id="nombreUsuario"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                <div class="menu-usuario" id="menuUsuario">
                    <a href="../logout.php">Cerrar sesión</a>
                </div>
            </div>
        </nav>
   </header> 
   <aside>
        <input type="checkbox" id="check">
        <label for="check" class="esconder-menu">&#215</label>
    <div class="aside">
        <?php if (in_array('Administrador', $_SESSION['rol_asignado'])): ?>
        <details>
            <summary>ACCESO Y SEGURIDAD</summary>
            <ul>
                <li><a href="../PRESENTACION/ADM_Usuario.php">Administrar Usuario</a></li>
                <li><a href="../PRESENTACION/ADM_Rol.php">Administrar Rol</a></li>
                <li><a href="../PRESENTACION/ADM_RolUsuario.php">Asignar Rol-Usuario</a></li>
            </ul>
        </details>
        <?php endif; ?>
        <?php if (count(array_intersect(['Administrador', 'Operador'], $_SESSION['rol_asignado'])) > 0): ?>
        <details>
        <summary>ADMINISTRAR PARAMETRIZACION</summary>
            <ul>
                <?php if (in_array('Administrador', $_SESSION['rol_asignado'])): ?>
                    <li><a href="../PRESENTACION/ADM_Area.php">Administrar Area</a></li>
                    <li><a href="../PRESENTACION/ADM_Cargo.php">Administrar Cargo</a></li>
                <?php endif; ?>
                <li><a href="../PRESENTACION/ADM_Funcionario.php">Administrar Funcionario</a></li>
                <li><a href="../PRESENTACION/ADM_Categoria.php">Administrar Categoria</a></li>
                <li><a href="../PRESENTACION/ADM_U_Medida.php">Administrar Unidad de Medida</a></li>
                <li><a href="../PRESENTACION/ADM_Material.php">Administrar Material</a></li>
                <li><a href="../PRESENTACION/ADM_Proveedor.php">Administrar Proveedor</a></li>
            </ul>
    </details>
    <?php endif; ?>
    <?php if (count(array_intersect(['Administrador', 'Operador'], $_SESSION['rol_asignado'])) > 0): ?>
    <details>
        <summary>TRANSACCIONAL</summary>
            <ul>
                <li><a href="../TRANSACCIONAL/Ingreso.php">INGRESO</a></li>
                <li><a href="../TRANSACCIONAL/Egreso.php">EGRESO</a></li>
            </ul>
    </details>
    <?php endif; ?>
    <?php if (count(array_intersect(['Administrador', 'Operador','Funcionario', 'Supervisor'], $_SESSION['rol_asignado'])) > 0): ?>
    <details>
        <summary>SOLICITAR MATERIALES</summary>
            <ul>
                <li><a href="../TRANSACCIONAL/Solicitud.php">Catalogo de materiales</a></li>
                <?php if (count(array_intersect(['Administrador', 'Operador'], $_SESSION['rol_asignado'])) > 0): ?>
                    <li><a href="../STOCK_MATERIALES/Stock.php">Stock de materiales</a></li>
                <?php endif; ?>
            </ul>
    </details>
    <?php endif; ?>
    </div>
    </aside>
    <script>
        // Detecta si el usuario viene del historial (flecha atrás)
        window.addEventListener("pageshow", function (event) {
            if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
                // Si vuelve con la flecha atrás, recargar forzadamente
                window.location.reload();
            }
        });
        // Guardar el estado de los details en localStorage
        document.querySelectorAll('details').forEach((detail, idx) => {
            detail.addEventListener('toggle', function() {
                localStorage.setItem('menuDetail' + idx, detail.open);
            });
            // Al cargar la página, restaurar el estado
            const open = localStorage.getItem('menuDetail' + idx);
            if (open === 'true') detail.open = true;
            if (open === 'false') detail.open = false;
        });
    </script>
</body>
</html>
