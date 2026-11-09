<?php
// categorias.php
session_start();

require_once 'inc/conexion.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=' . urlencode('Debes iniciar sesión como administrador.'));
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';

// --- PROCESAR ACCIONES (CREAR / EDITAR / ELIMINAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // 1. EDITAR CATEGORÍA
    if ($_POST['action'] === 'editar') {
        $id_categoria = (int)$_POST['id_categoria'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!empty($nombre) && $id_categoria > 0) {
            $sql = "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id_categoria = ?";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, 'ssi', $nombre, $descripcion, $id_categoria);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje_exito = "¡Categoría actualizada correctamente!";
            } else {
                $mensaje_error = "Error al actualizar la categoría: " . mysqli_error($conexion);
            }
        } else {
            $mensaje_error = "El nombre de la categoría no puede estar vacío.";
        }
    }

    // 2. CREAR CATEGORÍA
    if ($_POST['action'] === 'creAR') {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!empty($nombre)) {
            $sql = "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, 'ss', $nombre, $descripcion);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje_exito = "¡Categoría creada exitosamente!";
            } else {
                $mensaje_error = "Error al crear la categoría: " . mysqli_error($conexion);
            }
        } else {
            $mensaje_error = "Por favor ingresa un nombre para la categoría.";
        }
    }

    // 3. ELIMINAR CATEGORÍA
    if ($_POST['action'] === 'eliminar') {
        $id_categoria = (int)$_POST['id_categoria'];
        
        $sql = "DELETE FROM categorias WHERE id_categoria = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_categoria);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje_exito = "Categoría eliminada correctamente.";
        } else {
            $mensaje_error = "No se puede eliminar la categoría porque tiene productos asociados.";
        }
    }
}

// CONSULTAR TODAS LAS CATEGORÍAS
$sql_cat = "SELECT * FROM categorias ORDER BY id_categoria DESC";
$res_categorias = mysqli_query($conexion, $sql_cat);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías | Aura Cosmetics Admin</title>
    
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

<!-- NAVBAR ADMIN -->
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
            <a href="categorias.php" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="bi bi-tags me-1"></i> Categorías
            </a>
            <a href="pedidos.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                <i class="bi bi-receipt me-1"></i> Pedidos
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4 py-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="brand-font fw-bold mb-1">Gestión de Categorías</h2>
            <p class="text-muted small mb-0">Crea, edita o elimina categorías de productos</p>
        </div>
        <button class="btn btn-cosmetic px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-circle me-1"></i> Nueva Categoría
        </button>
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

    <!-- TABLA DE CATEGORÍAS -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_categorias && mysqli_num_rows($res_categorias) > 0): ?>
                        <?php while ($cat = mysqli_fetch_assoc($res_categorias)): ?>
                            <tr>
                                <td class="fw-bold">#<?= $cat['id_categoria'] ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($cat['nombre']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($cat['descripcion'] ?? 'Sin descripción') ?></td>
                                <td class="text-end">
                                    <!-- BOTÓN EDITAR -->
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditar"
                                            data-id="<?= $cat['id_categoria'] ?>"
                                            data-nombre="<?= htmlspecialchars($cat['nombre']) ?>"
                                            data-descripcion="<?= htmlspecialchars($cat['descripcion'] ?? '') ?>">
                                        <i class="bi bi-pencil-square me-1"></i> Editar
                                    </button>

                                    <!-- BOTÓN ELIMINAR -->
                                    <form action="categorias.php" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id_categoria" value="<?= $cat['id_categoria'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-trash me-1"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay categorías registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL: CREAR CATEGORÍA -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <form action="categorias.php" method="POST">
                <input type="hidden" name="action" value="creAR">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title brand-font fw-bold fs-4">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre de la Categoría</label>
                        <input type="text" name="nombre" class="form-control rounded-3" placeholder="Ej: Maquillaje Facial" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción (Opcional)</label>
                        <textarea name="descripcion" class="form-control rounded-3" rows="3" placeholder="Breve descripción..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cosmetic px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR CATEGORÍA -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <form action="categorias.php" method="POST">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id_categoria" id="edit_id_categoria">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title brand-font fw-bold fs-4">Editar Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre de la Categoría</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea name="descripcion" id="edit_descripcion" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cosmetic px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Script para cargar los datos en el modal de edición
const modalEditar = document.getElementById('modalEditar');
if (modalEditar) {
    modalEditar.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const descripcion = button.getAttribute('data-descripcion');

        document.getElementById('edit_id_categoria').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_descripcion').value = descripcion;
    });
}
</script>

</body>
</html>