<?php
// dashboard.php
session_start();

require_once 'inc/conexion.php';

// Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=' . urlencode('Debes iniciar sesión.'));
    exit;
}

$rol = $_SESSION['rol'] ?? 'cliente';
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';

// Cantidad de items en el carrito (solo para cliente)
$cant_items_carrito = 0;
if ($rol === 'cliente' && isset($_SESSION['carrito'])) {
    $cant_items_carrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal | Aura Cosmetics</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --cosmetic-rose: #d88a8a;
            --cosmetic-dark: #2a2024;
            --cosmetic-bg: #faf6f5;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--cosmetic-bg);
            color: var(--cosmetic-dark);
        }

        .brand-font { font-family: 'Playfair Display', serif; }

        .navbar-cosmetic {
            background-color: #ffffff;
            border-bottom: 1px solid #ebd9d8;
        }

        /* BANNER INSTITUCIONAL DE ENCABEZADO */
        .institutional-header {
            background: #ffffff;
            border: 1px solid rgba(235, 217, 216, 0.8);
            border-radius: 20px;
            padding: 20px 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
            margin-bottom: 2rem;
        }

        /* TEXTO CON DEGRADADO AZUL Y VERDE CLARO */
        .developer-name {
    font-weight: 700;
    background: linear-gradient(135deg, #0056b3 0%, #20c997 100%);
    background-clip: text; /* Regla estándar */
    -webkit-background-clip: text; /* Soporte para navegadores WebKit/Chrome/Safari */
    -webkit-text-fill-color: transparent;
    color: transparent; /* Respaldo para compatibilidad */
    display: inline-block;
}

        .card-custom {
            border: 1px solid rgba(235, 217, 216, 0.8);
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: transform 0.3s ease;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        .btn-cosmetic {
            background: linear-gradient(135deg, #df8d8d 0%, #b86b6b 100%);
            border: none;
            color: #ffffff;
            border-radius: 12px;
            font-weight: 500;
        }

        .btn-cosmetic:hover {
            background: linear-gradient(135deg, #cf7c7c 0%, #a45a5a 100%);
            color: #ffffff;
        }
    </style>
</head>
<body>

<!-- NAVBAR DINÁMICA -->
<nav class="navbar navbar-expand-lg navbar-cosmetic sticky-top py-3">
    <div class="container">
        <!-- LOGO CON INSIGNIA ADMIN BÁSICA DE BOOTSTRAP -->
        <a class="navbar-brand brand-font fw-bold fs-4 text-dark" href="dashboard.php">
            <i class="bi bi-flower1 me-2" style="color: #b86b6b;"></i>Aura Cosmetics
            <?php if ($rol === 'admin'): ?>
                <span class="badge bg-secondary fs-6 align-middle ms-1">Admin</span>
            <?php endif; ?>
        </a>
        
        <div class="d-flex align-items-center gap-2">
            <?php if ($rol === 'admin'): ?>
                <!-- OPCIONES ADMINISTRADOR (INICIO ACTIVO) -->
                <a href="dashboard.php" class="btn btn-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-house me-1"></i> Inicio
                </a>
                <a href="productos.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-box-seam me-1"></i> Productos
                </a>
                <a href="categorias.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-tags me-1"></i> Categorías
                </a>
                <a href="pedidos.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-receipt me-1"></i> Pedidos
                </a>

            <?php else: ?>
                <!-- OPCIONES CLIENTE -->
                <a href="dashboard.php" class="btn btn-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-house me-1"></i> Inicio
                </a>
                <a href="catalogo.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-grid me-1"></i> Productos
                </a>
                <a href="carrito.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 position-relative">
                    <i class="bi bi-bag-heart me-1"></i> Carrito
                    <?php if (isset($cant_items_carrito) && $cant_items_carrito > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $cant_items_carrito ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="mis_pedidos.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-clock-history me-1"></i> Mis Pedidos
                </a>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">Salir</a>
        </div>
    </div>
</nav>

<!-- CONTENIDO DEL PANEL PRINCIPAL -->
<div class="container py-4">

    <!-- ENCABEZADO INSTITUCIONAL AL PRINCIPIO -->
    <div class="institutional-header">
        <div class="row align-items-center text-center text-md-start g-3">
            <!-- LOGOS INSTITUCIONALES -->
            <div class="col-12 col-md-auto d-flex justify-content-center align-items-center gap-3">
                <img src="imagenes/logo SENA.png" 
                     alt="Logo SENA" 
                     style="height: 55px; width: auto;" 
                     loading="lazy">
                
                <div class="vr d-none d-md-block h-100 my-auto"></div>

                <img src="imagenes/logo del colegio.jpg" 
                     alt="Logo Colegio Técnico Superior Neiva" 
                     style="height: 55px; width: auto;" 
                     onerror="this.onerror=null; this.src='imagenes/logo del colegio.jpg'"
                     loading="lazy">
            </div>

            <!-- DETALLES DEL PROYECTO Y DESARROLLADOR -->
            <div class="col-12 col-md text-center text-md-end">
                <div class="small text-muted mb-1">
                    <span class="fw-semibold text-dark">Colegio Técnico Superior de Neiva Huila</span> | 
                    <span>Ficha SENA: <strong class="text-dark">3153943</strong></span>
                </div>
                <div class="small text-muted mb-1">
                    <i class="bi bi-envelope me-1"></i>Correo: <a href="mailto:BMRC35121734@soy.sena.edu.co" class="text-secondary text-decoration-none fw-medium">BMRC35121734@soy.sena.edu.co</a>
                </div>
                <div class="fs-6 mt-1">
                    Desarrollador: <span class="developer-name">Alejandro Cardozo Quintana</span>
                </div>
            </div>
        </div>
    </div>

    <!-- BIENVENIDA -->
    <div class="text-center mb-5">
        <?php if ($rol === 'admin'): ?>
            <span class="badge bg-dark-subtle text-dark border border-secondary-subtle px-3 py-2 rounded-pill small fw-semibold mb-3 d-inline-block">
                <i class="bi bi-person-badge me-1"></i> Administrador: <?= htmlspecialchars($nombre_usuario) ?>
            </span>
            <h2 class="brand-font display-5 fw-bold mb-2">Panel de Administración</h2>
            <p class="text-muted">Gestiona el inventario, categorías y pedidos del sistema</p>
        <?php else: ?>
            <h2 class="brand-font display-5 fw-bold mb-2">¡Hola, <?= htmlspecialchars($nombre_usuario) ?>!</h2>
            <p class="text-muted">Explora nuestras colecciones y revisa tus compras</p>
        <?php endif; ?>
    </div>

    <?php if ($rol === 'admin'): ?>
        <!-- TARJETAS PARA ADMINISTRADOR -->
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card card-custom p-4 text-center h-100">
                    <div class="display-5 text-danger mb-3"><i class="bi bi-box-seam"></i></div>
                    <h4 class="brand-font fw-bold mb-2">Gestión de Productos</h4>
                    <p class="text-muted small mb-4">Añade, edita, elimina productos y actualiza el stock disponible.</p>
                    <a href="productos.php" class="btn btn-cosmetic rounded-pill mt-auto">Administrar Productos</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom p-4 text-center h-100">
                    <div class="display-5 text-danger mb-3"><i class="bi bi-tags"></i></div>
                    <h4 class="brand-font fw-bold mb-2">Categorías</h4>
                    <p class="text-muted small mb-4">Crea y gestiona las categorías de cosméticos.</p>
                    <a href="categorias.php" class="btn btn-cosmetic rounded-pill mt-auto">Administrar Categorías</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom p-4 text-center h-100">
                    <div class="display-5 text-danger mb-3"><i class="bi bi-receipt"></i></div>
                    <h4 class="brand-font fw-bold mb-2">Pedidos de Clientes</h4>
                    <p class="text-muted small mb-4">Revisa todas las ventas realizadas y cambia los estados de entrega.</p>
                    <a href="pedidos.php" class="btn btn-cosmetic rounded-pill mt-auto">Ver Todos los Pedidos</a>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- TARJETAS PARA CLIENTES / USUARIOS NORMALES -->
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card card-custom p-4 text-center h-100">
                    <div class="display-5 text-danger mb-3"><i class="bi bi-grid"></i></div>
                    <h4 class="brand-font fw-bold mb-2">Catálogo</h4>
                    <p class="text-muted small mb-4">Explora todos los productos de maquillaje y cuidado personal.</p>
                    <a href="catalogo.php" class="btn btn-cosmetic rounded-pill mt-auto">Ver Productos</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom p-4 text-center h-100">
                    <div class="display-5 text-danger mb-3"><i class="bi bi-bag-heart"></i></div>
                    <h4 class="brand-font fw-bold mb-2">Mi Carrito</h4>
                    <p class="text-muted small mb-4">Revisa los cosméticos añadidos y finaliza tu pedido.</p>
                    <a href="carrito.php" class="btn btn-cosmetic rounded-pill mt-auto">Ir al Carrito</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom p-4 text-center h-100">
                    <div class="display-5 text-danger mb-3"><i class="bi bi-clock-history"></i></div>
                    <h4 class="brand-font fw-bold mb-2">Mis Pedidos</h4>
                    <p class="text-muted small mb-4">Consulta el estado de envío y el historial de tus compras.</p>
                    <a href="mis_pedidos.php" class="btn btn-cosmetic rounded-pill mt-auto">Ver Historial</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>