<?php
// productos.php
session_start();

require_once 'inc/conexion.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=' . urlencode('Debes iniciar sesión como administrador.'));
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';

// Crear directorio de subidas si no existe
$directorio_subidas = 'uploads/';
if (!is_dir($directorio_subidas)) {
    mkdir($directorio_subidas, 0777, true);
}

// --- PROCESAR ACCIONES (CREAR / EDITAR / ELIMINAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Función auxiliar para subir imagen
    function procesarImagen($archivo, $directorio) {
        if (isset($archivo) && $archivo['error'] === UPLOAD_ERR_OK) {
            $nombre_original = basename($archivo['name']);
            $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            $extensiones_validas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($extension, $extensiones_validas)) {
                // Generar un nombre único para no sobreescribir archivos
                $nombre_nuevo = time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = $directorio . $nombre_nuevo;
                
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    return $ruta_destino;
                }
            }
        }
        return false;
    }

    // 1. CREAR PRODUCTO
    if ($_POST['action'] === 'crear') {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = (float)$_POST['precio'];
        $stock = (int)$_POST['stock'];
        $id_categoria = (int)$_POST['id_categoria'];
        
        // Procesar imagen
        $ruta_imagen = '';
        if (!empty($_FILES['imagen']['name'])) {
            $resultado_subida = procesarImagen($_FILES['imagen'], $directorio_subidas);
            if ($resultado_subida) {
                $ruta_imagen = $resultado_subida;
            }
        }

        if (!empty($nombre) && $precio >= 0 && $stock >= 0 && $id_categoria > 0) {
            $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria, imagen) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, 'ssdiis', $nombre, $descripcion, $precio, $stock, $id_categoria, $ruta_imagen);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje_exito = "¡Producto creado exitosamente!";
            } else {
                $mensaje_error = "Error al crear el producto: " . mysqli_error($conexion);
            }
        } else {
            $mensaje_error = "Por favor completa todos los campos obligatorios correctamente.";
        }
    }

    // 2. EDITAR PRODUCTO
    if ($_POST['action'] === 'editar') {
        $id_producto = (int)$_POST['id_producto'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = (float)$_POST['precio'];
        $stock = (int)$_POST['stock'];
        $id_categoria = (int)$_POST['id_categoria'];
        $imagen_actual = $_POST['imagen_actual']; // Imagen que ya tenía el producto

        $ruta_imagen = $imagen_actual; // Por defecto mantiene la misma

        // Si se subió una nueva imagen, reemplazarla
        if (!empty($_FILES['imagen']['name'])) {
            $resultado_subida = procesarImagen($_FILES['imagen'], $directorio_subidas);
            if ($resultado_subida) {
                $ruta_imagen = $resultado_subida;
                
                // Opcional: Eliminar la imagen anterior del servidor para ahorrar espacio
                if (!empty($imagen_actual) && file_exists($imagen_actual)) {
                    unlink($imagen_actual);
                }
            }
        }

        if (!empty($nombre) && $id_producto > 0) {
            $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, id_categoria = ?, imagen = ? WHERE id_producto = ?";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, 'ssdiisi', $nombre, $descripcion, $precio, $stock, $id_categoria, $ruta_imagen, $id_producto);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje_exito = "¡Producto actualizado correctamente!";
            } else {
                $mensaje_error = "Error al actualizar el producto: " . mysqli_error($conexion);
            }
        } else {
            $mensaje_error = "Datos inválidos para actualizar.";
        }
    }

    // 3. ELIMINAR PRODUCTO
    if ($_POST['action'] === 'eliminar') {
        $id_producto = (int)$_POST['id_producto'];
        
        // Primero obtener la imagen para borrarla del servidor
        $sql_img = "SELECT imagen FROM productos WHERE id_producto = ?";
        $stmt_img = mysqli_prepare($conexion, $sql_img);
        mysqli_stmt_bind_param($stmt_img, 'i', $id_producto);
        mysqli_stmt_execute($stmt_img);
        $res_img = mysqli_stmt_get_result($stmt_img);
        $prod_eliminar = mysqli_fetch_assoc($res_img);

        // Eliminar registro
        $sql = "DELETE FROM productos WHERE id_producto = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_producto);
        
        if (mysqli_stmt_execute($stmt)) {
            // Borrar archivo físico si existe
            if (!empty($prod_eliminar['imagen']) && file_exists($prod_eliminar['imagen'])) {
                unlink($prod_eliminar['imagen']);
            }
            $mensaje_exito = "Producto eliminado correctamente.";
        } else {
            $mensaje_error = "No se puede eliminar el producto, es posible que esté asociado a un pedido.";
        }
    }
}

// CONSULTAS PARA LA VISTA
$sql_categorias = "SELECT * FROM categorias ORDER BY nombre ASC";
$res_categorias_modal = mysqli_query($conexion, $sql_categorias);
// Guardar las categorías en un array para usarlas en los modales múltiples veces
$categorias = [];
while($cat = mysqli_fetch_assoc($res_categorias_modal)) {
    $categorias[] = $cat;
}

$sql_productos = "SELECT p.*, c.nombre AS nombre_categoria 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                  ORDER BY p.id_producto DESC";
$res_productos = mysqli_query($conexion, $sql_productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos | Aura Cosmetics Admin</title>
    
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

        .card-custom {
            border: 1px solid rgba(235, 217, 216, 0.8);
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
        
        .img-thumbnail-custom {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ebd9d8;
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
            <a href="productos.php" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="bi bi-box-seam me-1"></i> Productos
            </a>
            <a href="categorias.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
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
            <h2 class="brand-font fw-bold mb-1">Gestión de Productos</h2>
            <p class="text-muted small mb-0">Crea, edita o elimina productos del catálogo</p>
        </div>
        <button class="btn btn-cosmetic px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Producto
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

    <!-- TABLA DE PRODUCTOS -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>IMG</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_productos && mysqli_num_rows($res_productos) > 0): ?>
                        <?php while ($prod = mysqli_fetch_assoc($res_productos)): 
                            // Determinar imagen a mostrar
                            $img_src = !empty($prod['imagen']) && file_exists($prod['imagen']) 
                                       ? $prod['imagen'] 
                                       : 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?q=80&w=150&auto=format&fit=crop';
                        ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="Prod" class="img-thumbnail-custom">
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($prod['nombre']) ?></span>
                                    <small class="text-muted d-inline-block text-truncate" style="max-width: 150px;">
                                        <?= htmlspecialchars($prod['descripcion']) ?>
                                    </small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($prod['nombre_categoria'] ?? 'N/A') ?></span></td>
                                <td class="fw-bold">$<?= number_format($prod['precio'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if($prod['stock'] > 0): ?>
                                        <span class="text-success fw-semibold"><?= $prod['stock'] ?> ud.</span>
                                    <?php else: ?>
                                        <span class="text-danger fw-semibold">Agotado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <!-- BOTÓN EDITAR -->
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditar"
                                            data-id="<?= $prod['id_producto'] ?>"
                                            data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                                            data-descripcion="<?= htmlspecialchars($prod['descripcion']) ?>"
                                            data-precio="<?= $prod['precio'] ?>"
                                            data-stock="<?= $prod['stock'] ?>"
                                            data-categoria="<?= $prod['id_categoria'] ?>"
                                            data-imagen="<?= htmlspecialchars($prod['imagen'] ?? '') ?>">
                                        <i class="bi bi-pencil-square me-1"></i>
                                    </button>

                                    <!-- BOTÓN ELIMINAR -->
                                    <form action="productos.php" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id_producto" value="<?= $prod['id_producto'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-trash me-1"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay productos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL: CREAR PRODUCTO -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0">
            <!-- NOTA: enctype="multipart/form-data" es obligatorio para subir archivos -->
            <form action="productos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="crear">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title brand-font fw-bold fs-4">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre del Producto</label>
                            <input type="text" name="nombre" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select name="id_categoria" class="form-select rounded-3" required>
                                <option value="">Seleccione una categoría...</option>
                                <?php foreach($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Precio ($)</label>
                            <input type="number" step="0.01" name="precio" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stock Inicial</label>
                            <input type="number" name="stock" class="form-control rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Imagen del Producto (Local)</label>
                            <input type="file" name="imagen" class="form-control rounded-3" accept="image/png, image/jpeg, image/webp, image/gif">
                            <small class="text-muted">Formatos soportados: JPG, PNG, WEBP. Se guardará en la carpeta /uploads</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cosmetic px-4">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR PRODUCTO -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0">
            <form action="productos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id_producto" id="edit_id_producto">
                <input type="hidden" name="imagen_actual" id="edit_imagen_actual">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title brand-font fw-bold fs-4">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre del Producto</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select name="id_categoria" id="edit_id_categoria" class="form-select rounded-3" required>
                                <?php foreach($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Precio ($)</label>
                            <input type="number" step="0.01" name="precio" id="edit_precio" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stock</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Actualizar Imagen (Opcional)</label>
                            <input type="file" name="imagen" class="form-control rounded-3" accept="image/png, image/jpeg, image/webp, image/gif">
                            <small class="text-muted">Sube una nueva imagen sólo si deseas reemplazar la actual.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control rounded-3" rows="3"></textarea>
                        </div>
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
        
        // Obtener datos del botón
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const descripcion = button.getAttribute('data-descripcion');
        const precio = button.getAttribute('data-precio');
        const stock = button.getAttribute('data-stock');
        const categoria = button.getAttribute('data-categoria');
        const imagen = button.getAttribute('data-imagen');

        // Llenar los inputs del modal
        document.getElementById('edit_id_producto').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_descripcion').value = descripcion;
        document.getElementById('edit_precio').value = precio;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_id_categoria').value = categoria;
        document.getElementById('edit_imagen_actual').value = imagen;
    });
}
</script>

</body>
</html>