<?php
// mis_pedidos.php
session_start();

require_once 'inc/conexion.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=' . urlencode('Debes iniciar sesión para ver tu historial de pedidos.'));
    exit;
}

$id_usuario = (int)$_SESSION['usuario_id'];

// Obtener cantidad de items en el carrito para la barra de navegación
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$cant_items_carrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));

// CONSULTAR PEDIDOS DEL USUARIO LOGUEADO
$sql_pedidos = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY id_pedido DESC";
$stmt = mysqli_prepare($conexion, $sql_pedidos);
mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
mysqli_stmt_execute($stmt);
$res_pedidos = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos | Aura Cosmetics</title>
    
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
<!-- NAVBAR PARA MIS_PEDIDOS.PHP -->
<nav class="navbar navbar-expand-lg navbar-cosmetic sticky-top py-3">
    <div class="container">
        <a class="navbar-brand brand-font fw-bold fs-4 text-dark" href="dashboard.php">
            <i class="bi bi-flower1 me-2" style="color: #b86b6b;"></i>Aura Cosmetics
        </a>
        
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <a href="dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
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

            <!-- ACTIVO: fw-bold + bi-clock-fill -->
            <a href="mis_pedidos.php" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold">
                <i class="bi bi-clock-fill me-1"></i> Mis Pedidos
            </a>

            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4 py-lg-5">

    <div class="mb-4">
        <h2 class="brand-font fw-bold mb-1">Historial de Mis Pedidos</h2>
        <p class="text-muted small mb-0">Consulta el estado y detalle de todas tus compras realizadas</p>
    </div>

    <!-- TABLA DE HISTORIAL DE PEDIDOS -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Pedido</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado de Envío</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_pedidos && mysqli_num_rows($res_pedidos) > 0): ?>
                        <?php while ($pedido = mysqli_fetch_assoc($res_pedidos)): 
                            // Configurar color según estado del pedido
                            $estado_raw = strtolower($pedido['estado'] ?? 'pendiente');
                            $badge_color = 'bg-secondary';
                            if ($estado_raw === 'pendiente') $badge_color = 'bg-warning text-dark';
                            if ($estado_raw === 'enviado' || $estado_raw === 'procesando') $badge_color = 'bg-primary';
                            if ($estado_raw === 'entregado' || $estado_raw === 'completado' || $estado_raw === 'pagado') $badge_color = 'bg-success';
                            if ($estado_raw === 'cancelado') $badge_color = 'bg-danger';

                            $fecha_mostrar = isset($pedido['fecha_pedido']) ? date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) : (isset($pedido['fecha']) ? date('d/m/Y H:i', strtotime($pedido['fecha'])) : 'N/D');
                        ?>
                            <tr>
                                <td class="fw-bold">#<?= $pedido['id_pedido'] ?></td>
                                <td class="text-muted"><?= $fecha_mostrar ?></td>
                                <td class="fw-bold text-dark">$<?= number_format($pedido['total'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge <?= $badge_color ?> rounded-pill px-3 py-2">
                                        <?= htmlspecialchars(ucfirst($pedido['estado'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-bag-x fs-1 d-block mb-3 text-muted"></i>
                                <h5 class="fw-bold">Aún no has hecho ninguna compra</h5>
                                <p class="small text-muted mb-3">Explora nuestro catálogo y realiza tu primer pedido.</p>
                                <a href="catalogo.php" class="btn btn-cosmetic px-4 rounded-pill">Ir a Productos</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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