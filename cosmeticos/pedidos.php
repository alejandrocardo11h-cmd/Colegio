<?php
// pedidos.php
session_start();

require_once 'inc/conexion.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=' . urlencode('Debes iniciar sesión como administrador.'));
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';

// --- PROCESAR ACCIÓN: ACTUALIZAR ESTADO DEL PEDIDO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_estado') {
    $id_pedido = (int)$_POST['id_pedido'];
    $nuevo_estado = trim($_POST['estado']);

    if ($id_pedido > 0 && !empty($nuevo_estado)) {
        $sql = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $nuevo_estado, $id_pedido);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje_exito = "¡El estado del pedido #$id_pedido se actualizó correctamente!";
        } else {
            $mensaje_error = "Error al actualizar el estado del pedido: " . mysqli_error($conexion);
        }
    }
}

// CONSULTAR TODOS LOS PEDIDOS (Uniendo con la tabla usuarios para ver quién compró)
$sql_pedidos = "SELECT p.*, u.nombre AS nombre_cliente, u.correo 
                FROM pedidos p 
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                ORDER BY p.fecha_pedido DESC";
$res_pedidos = mysqli_query($conexion, $sql_pedidos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos | Aura Cosmetics Admin</title>
    
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

        .card-custom {
            border: 1px solid rgba(235, 217, 216, 0.8);
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
    </style>
</head>
<body>

<!-- NAVBAR ADMIN (Con resaltado en "Pedidos") -->
<nav class="navbar navbar-expand-lg navbar-cosmetic sticky-top py-3">
    <div class="container">
        <a class="navbar-brand brand-font fw-bold fs-4 text-dark" href="dashboard.php">
            <i class="bi bi-flower1 me-2" style="color: #b86b6b;"></i>Aura Cosmetics <span class="badge bg-secondary fs-6 align-middle">Admin</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                <i class="bi bi-house me-1"></i> Inicio
            </a>
            <a href="productos.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                <i class="bi bi-box-seam me-1"></i> Productos
            </a>
            <a href="categorias.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                <i class="bi bi-tags me-1"></i> Categorías
            </a>
            <a href="pedidos.php" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="bi bi-receipt me-1"></i> Pedidos
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4 py-lg-5">

    <div class="mb-4">
        <h2 class="brand-font fw-bold mb-1">Gestión de Pedidos</h2>
        <p class="text-muted small mb-0">Revisa las compras de tus clientes y actualiza su estado de envío</p>
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

    <!-- TABLA DE PEDIDOS -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado Actual</th>
                        <th class="text-end">Actualizar Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_pedidos && mysqli_num_rows($res_pedidos) > 0): ?>
                        <?php while ($pedido = mysqli_fetch_assoc($res_pedidos)): 
                            
                            // Asignar color de badge según el estado
                            $badge_color = 'bg-secondary';
                            $estado = strtolower($pedido['estado']);
                            if ($estado == 'pendiente') $badge_color = 'bg-warning text-dark';
                            if ($estado == 'enviado' || $estado == 'procesando') $badge_color = 'bg-primary';
                            if ($estado == 'entregado' || $estado == 'completado') $badge_color = 'bg-success';
                            if ($estado == 'cancelado') $badge_color = 'bg-danger';
                        ?>
                            <tr>
                                <td class="fw-bold">#<?= $pedido['id_pedido'] ?></td>
                                <td>
                                    <span class="d-block fw-semibold text-dark"><?= htmlspecialchars($pedido['nombre_cliente'] ?? 'Usuario Desconocido') ?></span>
                                    <span class="small text-muted"><?= htmlspecialchars($pedido['correo'] ?? '') ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></td>
                                <td class="fw-bold">$<?= number_format($pedido['total'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge <?= $badge_color ?> rounded-pill px-3 py-2">
                                        <?= htmlspecialchars(ucfirst($pedido['estado'])) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="pedidos.php" method="POST" class="d-flex justify-content-end gap-2">
                                        <input type="hidden" name="action" value="actualizar_estado">
                                        <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                                        <select name="estado" class="form-select form-select-sm rounded-pill w-auto" required>
                                            <option value="Pendiente" <?= ($estado == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                            <option value="Enviado" <?= ($estado == 'enviado') ? 'selected' : '' ?>>Enviado</option>
                                            <option value="Entregado" <?= ($estado == 'entregado') ? 'selected' : '' ?>>Entregado</option>
                                            <option value="Cancelado" <?= ($estado == 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-cosmetic rounded-pill px-3">
                                            Guardar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No hay pedidos registrados en el sistema.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>