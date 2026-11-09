<?php
// carrito.php
session_start();

require_once 'inc/conexion.php';

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$mensaje_exito = '';
$mensaje_error = '';

// --- PROCESAR ACCIONES DEL CARRITO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id_producto = (int)($_POST['id_producto'] ?? 0);

    // 1. ELIMINAR UN PRODUCTO DEL CARRITO
    if ($action === 'eliminar' && isset($_SESSION['carrito'][$id_producto])) {
        unset($_SESSION['carrito'][$id_producto]);
        $mensaje_exito = "Producto eliminado del carrito.";
    }

    // 2. ACTUALIZAR CANTIDAD
    if ($action === 'actualizar' && isset($_SESSION['carrito'][$id_producto])) {
        $nueva_cant = (int)$_POST['cantidad'];
        
        // Verificar stock disponible en BD
        $sql_stock = "SELECT stock FROM productos WHERE id_producto = ?";
        $stmt_s = mysqli_prepare($conexion, $sql_stock);
        mysqli_stmt_bind_param($stmt_s, 'i', $id_producto);
        mysqli_stmt_execute($stmt_s);
        $res_s = mysqli_stmt_get_result($stmt_s);
        $prod = mysqli_fetch_assoc($res_s);

        if ($prod && $nueva_cant > 0 && $nueva_cant <= $prod['stock']) {
            $_SESSION['carrito'][$id_producto]['cantidad'] = $nueva_cant;
            $mensaje_exito = "Cantidad actualizada correctamente.";
        } else {
            $mensaje_error = "La cantidad solicitada supera el stock disponible (" . ($prod['stock'] ?? 0) . " unidades).";
        }
    }

    // 3. VACIAR TODO EL CARRITO
    if ($action === 'vaciar') {
        $_SESSION['carrito'] = [];
        $mensaje_exito = "Se ha vaciado el carrito por completo.";
    }
}

// Calcular cantidad total de items
$cant_items_carrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));

// Calcular total a pagar
$total_pagar = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total_pagar += ($item['precio'] * $item['cantidad']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito | Aura Cosmetics</title>
    
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

        .card-custom {
            border: 1px solid rgba(235, 217, 216, 0.8);
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
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

<!-- NAVBAR DEL CLIENTE MEJORADA Y UNIFICADA -->
<!-- NAVBAR PARA CARRITO.PHP -->
<nav class="navbar navbar-expand-lg navbar-cosmetic sticky-top py-3">
    <div class="container">
        <a class="navbar-brand brand-font fw-bold fs-4 text-dark" href="<?= isset($_SESSION['usuario_id']) ? 'dashboard.php' : 'catalogo.php' ?>">
            <i class="bi bi-flower1 me-2" style="color: #b86b6b;"></i>Aura Cosmetics
        </a>
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-house me-1"></i> Inicio
                </a>
                <a href="catalogo.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-grid me-1"></i> Productos
                </a>

                <!-- ACTIVO: fw-bold + bi-bag-heart-fill -->
                <a href="carrito.php" class="btn btn-dark btn-sm rounded-pill px-3 position-relative fw-bold">
                    <i class="bi bi-bag-heart-fill me-1"></i> Carrito
                    <?php if (isset($cant_items_carrito) && $cant_items_carrito > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger fw-normal">
                            <?= $cant_items_carrito ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="mis_pedidos.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="bi bi-clock-history me-1"></i> Mis Pedidos
                </a>

                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">Salir</a>
            <?php else: ?>
                <a href="catalogo.php" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-grid-fill me-1"></i> Productos
                </a>
                <a href="login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Iniciar Sesión</a>
                <a href="registro.php" class="btn btn-cosmetic btn-sm rounded-pill px-3">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-4 py-lg-5">

    <div class="mb-4">
        <h2 class="brand-font fw-bold mb-1">Mi Carrito de Compras</h2>
        <p class="text-muted small mb-0">Revisa tus productos elegidos antes de finalizar el pedido</p>
    </div>

    <!-- ALERTAS -->
    <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensaje_exito) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($mensaje_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['carrito'])): ?>
        <div class="row g-4">
            
            <!-- LISTA DE PRODUCTOS -->
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th style="width: 140px;">Cantidad</th>
                                    <th>Subtotal</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['carrito'] as $id => $item): 
                                    $subtotal = $item['precio'] * $item['cantidad'];
                                ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold d-block text-dark"><?= htmlspecialchars($item['nombre']) ?></span>
                                        </td>
                                        <td>$<?= number_format($item['precio'], 0, ',', '.') ?></td>
                                        <td>
                                            <form action="carrito.php" method="POST" class="d-flex align-items-center gap-1">
                                                <input type="hidden" name="action" value="actualizar">
                                                <input type="hidden" name="id_producto" value="<?= $id ?>">
                                                <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="1" class="form-control form-control-sm text-center rounded-pill" style="width: 65px;" required>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle" title="Actualizar">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="fw-bold text-dark">$<?= number_format($subtotal, 0, ',', '.') ?></td>
                                        <td class="text-end">
                                            <form action="carrito.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="eliminar">
                                                <input type="hidden" name="id_producto" value="<?= $id ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar del carrito">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <form action="carrito.php" method="POST">
                            <input type="hidden" name="action" value="vaciar">
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="bi bi-trash me-1"></i> Vaciar Carrito
                            </button>
                        </form>
                        <a href="catalogo.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                            <i class="bi bi-arrow-left me-1"></i> Seguir Comprando
                        </a>
                    </div>
                </div>
            </div>

            <!-- RESUMEN DE COMPRA -->
            <div class="col-lg-4">
                <div class="card card-custom p-4">
                    <h4 class="brand-font fw-bold mb-3">Resumen del Pedido</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cantidad de items:</span>
                        <span class="fw-semibold"><?= $cant_items_carrito ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-3 fs-5 fw-bold text-dark pt-3 border-top">
                        <span>Total:</span>
                        <span>$<?= number_format($total_pagar, 0, ',', '.') ?></span>
                    </div>

                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="checkout.php" class="btn btn-cosmetic w-100 py-3 rounded-pill fw-bold text-center mt-2">
                            Procesar Pedido <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php?error=<?= urlencode('Debes iniciar sesión para procesar tu compra.') ?>" class="btn btn-cosmetic w-100 py-3 rounded-pill fw-bold text-center mt-2">
                            Iniciar Sesión para Comprar <i class="bi bi-box-arrow-in-right ms-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    <?php else: ?>
        <div class="card card-custom p-5 text-center">
            <i class="bi bi-bag-x fs-1 text-muted d-block mb-3"></i>
            <h4 class="fw-bold">Tu carrito está vacío</h4>
            <p class="text-muted small mb-4">Aún no has agregado ningún cosmético a tu lista de compra.</p>
            <div>
                <a href="catalogo.php" class="btn btn-cosmetic px-4 rounded-pill">Explorar Catálogo</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<footer class="bg-white border-top py-4 mt-5">
    <div class="container text-center text-muted small">
        <p class="mb-0">&copy; 2026 Aura Cosmetics — Sistema de Gestión y Ventas SENA.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>