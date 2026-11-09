<?php
// catalogo.php
session_start();

require_once 'inc/conexion.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$mensaje_carrito = '';
$mensaje_alerta = '';

if (isset($_GET['requiere_login'])) {
    $mensaje_alerta = "Debes iniciar sesión o registrarte para realizar esta acción.";
}

// PROCESAR ACCIÓN: AGREGAR AL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'agregar') {
    
    // Si no está logueado, redirigir a login
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?error=' . urlencode('Debes iniciar sesión o registrarte para añadir productos al carrito.'));
        exit;
    }

    $id_producto = (int)$_POST['id_producto'];
    
    $sql_prod = "SELECT * FROM productos WHERE id_producto = ?";
    if ($stmt_p = mysqli_prepare($conexion, $sql_prod)) {
        mysqli_stmt_bind_param($stmt_p, 'i', $id_producto);
        mysqli_stmt_execute($stmt_p);
        $res_p = mysqli_stmt_get_result($stmt_p);
        
        if ($prod = mysqli_fetch_assoc($res_p)) {
            if ($prod['stock'] > 0) {
                if (isset($_SESSION['carrito'][$id_producto])) {
                    if ($_SESSION['carrito'][$id_producto]['cantidad'] < $prod['stock']) {
                        $_SESSION['carrito'][$id_producto]['cantidad']++;
                        $mensaje_carrito = "¡Cantidad actualizada en el carrito!";
                    } else {
                        $mensaje_carrito = "No hay suficiente stock disponible.";
                    }
                } else {
                    $_SESSION['carrito'][$id_producto] = [
                        'id_producto' => $prod['id_producto'],
                        'nombre'      => $prod['nombre'],
                        'precio'      => $prod['precio'],
                        'cantidad'    => 1
                    ];
                    $mensaje_carrito = "¡Producto añadido al carrito!";
                }
            }
        }
        mysqli_stmt_close($stmt_p);
    }
}

$categoria_filtro = $_GET['categoria'] ?? '';

// CONSULTA SQL SEGURA Y VALIDADA
if (!empty($categoria_filtro)) {
    $sql = "SELECT p.*, c.nombre AS nombre_categoria 
            FROM productos p 
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
            WHERE c.nombre = ? 
            ORDER BY p.id_producto DESC";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $categoria_filtro);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
    } else {
        $resultado = false;
    }
} else {
    $sql = "SELECT p.*, c.nombre AS nombre_categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
            ORDER BY p.id_producto DESC";
    $resultado = mysqli_query($conexion, $sql);
}

$res_categorias = mysqli_query($conexion, "SELECT * FROM categorias ORDER BY nombre ASC");

// FUNCIÓN DE IMÁGENES CON TIPADO NULLABLE PARA EVITAR ADVERTENCIAS
if (!function_exists('obtenerImagenProducto')) {
    function obtenerImagenProducto(?string $nombre_prod, ?string $nombre_cat = '', ?string $imagen_bd = ''): string {
        $nombre_prod = $nombre_prod ?? '';
        $nombre_cat  = $nombre_cat ?? '';
        $imagen_bd   = $imagen_bd ?? '';

        if ($imagen_bd !== '') {
            if (str_starts_with($imagen_bd, 'http') || file_exists($imagen_bd)) {
                return $imagen_bd;
            }
        }

        $prod = mb_strtolower($nombre_prod, 'UTF-8');

        if (str_contains($prod, 'serum') || str_contains($prod, 'sérum') || str_contains($prod, 'hialurónico')) {
            return 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'protector') || str_contains($prod, 'bloqueador') || str_contains($prod, 'spf')) {
            return 'https://images.unsplash.com/photo-1598440947619-2c35fc9aa908?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'limpiador') || str_contains($prod, 'gel')) {
            return 'https://images.unsplash.com/photo-1556228720-195a672e8a03?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'pestañina') || str_contains($prod, 'pestania') || str_contains($prod, 'mascara')) {
            return 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'delineador')) {
            return 'https://images.unsplash.com/photo-1631214500115-598fc2cb8d2d?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'paleta') || str_contains($prod, 'sombra')) {
            return 'https://images.unsplash.com/photo-1596704017254-9b121068fb31?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'tinta')) {
            return 'https://images.unsplash.com/photo-1625093742435-6fa192b6fb10?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'gloss')) {
            return 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'labial')) {
            return 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'base')) {
            return 'https://images.unsplash.com/photo-1631729371254-42c2892f0e6e?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'polvo')) {
            return 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?q=80&w=600&auto=format&fit=crop';
        } elseif (str_contains($prod, 'rubor') || str_contains($prod, 'blush')) {
            return 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=600&auto=format&fit=crop';
        }

        return 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?q=80&w=600&auto=format&fit=crop';
    }
}

$cant_items_carrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Cosmetics | Catálogo Exclusivo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

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

        .product-card {
            border: 1px solid rgba(235, 217, 216, 0.8);
            border-radius: 20px;
            overflow: hidden;
            background-color: #ffffff;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 35px rgba(184, 134, 134, 0.18);
        }

        .product-img-container {
            height: 220px;
            overflow: hidden;
            position: relative;
            background-color: #f5f5f5;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-img {
            transform: scale(1.06);
        }

        .badge-category {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(6px);
            color: var(--cosmetic-dark);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 30px;
            z-index: 2;
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

        .filter-btn {
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* CLASE REUTILIZABLE PARA RECORTAR TEXTO A 2 LÍNEAS (EVITA ADVERTENCIAS DE CSS INLINE) */
.text-truncate-2 {
    display: block; /* Respaldo estándar para el linter */
    display: -webkit-box;
    max-height: 3em; /* Altura de seguridad para 2 líneas */
    line-height: 1.5;
    overflow: hidden;
    text-overflow: ellipsis;
    -webkit-line-clamp: 2;
    line-clamp: 2; /* Propiedad estándar */
    -webkit-box-orient: vertical;
}
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-cosmetic sticky-top py-3">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand brand-font fw-bold fs-4 text-dark" href="<?= isset($_SESSION['usuario_id']) ? 'dashboard.php' : 'catalogo.php' ?>">
            <i class="bi bi-flower1 me-2" style="color: #b86b6b;"></i>Aura Cosmetics
        </a>
        
        <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <!-- OPCIONES PARA USUARIO AUTENTICADO -->
                <a href="dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-house me-1"></i> Inicio
                </a>
                <a href="catalogo.php" class="btn btn-dark btn-sm rounded-pill px-3">
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
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">Salir</a>

            <?php else: ?>
                <!-- OPCIONES PARA VISITANTE (SIN SESIÓN) -->
                <a href="catalogo.php" class="btn btn-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-grid me-1"></i> Productos
                </a>
                <a href="login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Iniciar Sesión</a>
                <a href="registro.php" class="btn btn-cosmetic btn-sm rounded-pill px-3">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-4 py-lg-5">

    <?php if (!empty($mensaje_alerta)): ?>
        <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-lock-fill me-2"></i> <?= htmlspecialchars($mensaje_alerta) ?>
            <a href="login.php" class="fw-bold text-dark text-decoration-underline ms-2">Iniciar Sesión</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_carrito)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensaje_carrito) ?>
            <a href="carrito.php" class="fw-bold text-success text-decoration-underline ms-2">Ver Carrito</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="text-center mb-5">
        <span class="text-uppercase small fw-semibold tracking-wide text-muted">Colección Exclusiva</span>
        <h2 class="brand-font display-5 fw-bold mb-2">Nuestros Cosméticos</h2>
        <p class="text-muted col-lg-6 mx-auto">
            Descubre productos formulados para resaltar tu belleza natural.
        </p>
    </div>

    <!-- FILTROS POR CATEGORÍA -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
        <a href="catalogo.php" class="btn <?= empty($categoria_filtro) ? 'btn-dark' : 'btn-outline-dark' ?> filter-btn">
            Todos
        </a>
        <?php if ($res_categorias && mysqli_num_rows($res_categorias) > 0): ?>
            <?php while ($cat = mysqli_fetch_assoc($res_categorias)): ?>
                <a href="catalogo.php?categoria=<?= urlencode($cat['nombre']) ?>" 
                   class="btn <?= ($categoria_filtro === $cat['nombre']) ? 'btn-dark' : 'btn-outline-dark' ?> filter-btn">
                    <?= htmlspecialchars($cat['nombre']) ?>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- TARJETAS DE PRODUCTOS -->
    <div class="row g-4">
        <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
            <?php while ($producto = mysqli_fetch_assoc($resultado)): 
                $nombre_cat = $producto['nombre_categoria'] ?? 'General';
                $imagen_bd = $producto['imagen'] ?? '';
                $src_imagen = obtenerImagenProducto($producto['nombre'], $nombre_cat, $imagen_bd);
            ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="card product-card h-100">
                        
                        <div class="product-img-container">
                            <img src="<?= htmlspecialchars($src_imagen) ?>" 
                                 class="product-img" 
                                 alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?q=80&w=600&auto=format&fit=crop';"
                                 loading="lazy">
                            <span class="badge-category shadow-sm">
                                <?= htmlspecialchars($nombre_cat) ?>
                            </span>
                        </div>

                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="fw-bold fs-6 mb-1 text-truncate" title="<?= htmlspecialchars($producto['nombre']) ?>">
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </h5>
                            
                            <!-- DESCRIPCIÓN CON LA CLASE DE TRUNCAMIENTO CORREGIDA -->
                            <p class="text-muted small mb-3 flex-grow-1 text-truncate-2">
                                <?= htmlspecialchars((string)($producto['descripcion'] ?? 'Producto de alta calidad para tu rutina de belleza.')) ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fs-5 fw-bold text-dark">
                                    $<?= number_format($producto['precio'], 0, ',', '.') ?>
                                </span>
                                
                                <?php if ($producto['stock'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                        Disponible (<?= $producto['stock'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                        Agotado
                                    </span>
                                <?php endif; ?>
                            </div>

                            <form action="catalogo.php" method="POST">
                                <input type="hidden" name="action" value="agregar">
                                <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                                <button type="submit" class="btn btn-cosmetic w-100 py-2 d-flex align-items-center justify-content-center gap-2" 
                                        <?= ($producto['stock'] <= 0) ? 'disabled' : '' ?>>
                                    <i class="bi bi-cart-plus fs-5"></i>
                                    <span>Añadir al Carrito</span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-bag-x fs-1 text-muted d-block mb-3"></i>
                <h4 class="fw-bold">No se encontraron productos</h4>
                <p class="text-muted">No hay cosméticos registrados bajo esta categoría actualmente.</p>
                <a href="catalogo.php" class="btn btn-outline-dark rounded-pill px-4 mt-2">Ver todos los productos</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<footer class="bg-white border-top py-4 mt-5">
    <div class="container text-center text-muted small">
        <p class="mb-0">&copy; 2026 Aura Cosmetics — Sistema de Gestión y Ventas SENA.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>