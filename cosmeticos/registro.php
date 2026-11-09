<?php
// registro.php

session_start();

// Si ya inició sesión, enviarlo al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'inc/conexion.php';

$mensaje_error = '';
$mensaje_exito = '';

// Variables para conservar lo escrito en caso de error
$nombre    = '';
$correo    = '';
$telefono  = '';
$direccion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombre) || empty($correo) || empty($password)) {
        $mensaje_error = 'Por favor completa los campos obligatorios (*).';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = 'Ingresa un correo electrónico válido.';
    } elseif (strlen($password) < 6) {
        $mensaje_error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // 1. Validar que el correo no esté registrado previamente
        $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? LIMIT 1";
        $stmt_check = mysqli_prepare($conexion, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 's', $correo);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $mensaje_error = 'El correo electrónico ya se encuentra registrado.';
            mysqli_stmt_close($stmt_check);
        } else {
            mysqli_stmt_close($stmt_check);

            // 2. Encriptar contraseña y registrar usuario con rol 'cliente'
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $rol = 'cliente';

            $sql_insert = "INSERT INTO usuarios (nombre, correo, password, telefono, direccion, rol) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conexion, $sql_insert);

            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, 'ssssss', $nombre, $correo, $password_hash, $telefono, $direccion, $rol);

                if (mysqli_stmt_execute($stmt_insert)) {
                    $mensaje_exito = '¡Registro exitoso! Ya puedes iniciar sesión con tu cuenta.';
                    // Limpiar campos
                    $nombre = $correo = $telefono = $direccion = '';
                } else {
                    $mensaje_error = 'Error al registrar el usuario en la base de datos.';
                }

                mysqli_stmt_close($stmt_insert);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Cosmetics | Crear Cuenta</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --cosmetic-rose: #d88a8a;
            --cosmetic-dark: #2a2024;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #fcefe9 0%, #f6e3e1 50%, #eddcd9 100%);
            color: var(--cosmetic-dark);
        }

        .brand-title { font-family: 'Playfair Display', serif; }

        .beauty-card {
            border-radius: 28px;
            box-shadow: 0 20px 45px rgba(184, 134, 134, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.95);
        }

        .form-control-cosmetic {
            border: 1.5px solid #ebd9d8;
            background-color: #fffaf9;
            border-radius: 12px;
            padding: 10px 14px;
        }

        .form-control-cosmetic:focus {
            background-color: #ffffff;
            border-color: var(--cosmetic-rose);
            box-shadow: 0 0 0 4px rgba(216, 138, 138, 0.2);
        }

        .btn-cosmetic {
            background: linear-gradient(135deg, #df8d8d 0%, #b86b6b 100%);
            border: none;
            color: #ffffff;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
        }

        .btn-cosmetic:hover {
            background: linear-gradient(135deg, #cf7c7c 0%, #a45a5a 100%);
            color: #ffffff;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            
            <div class="card beauty-card p-4 p-md-5">
                
                <div class="text-center mb-4">
                    <h3 class="brand-title fw-bold mb-1">Crea tu Cuenta</h3>
                    <p class="text-muted small">Únete a Aura Cosmetics y realiza tus compras fácilmente</p>
                </div>

                <!-- Mensaje de Error -->
                <?php if (!empty($mensaje_error)): ?>
                    <div class="alert alert-danger d-flex align-items-center border-0 rounded-3 py-2 px-3 small mb-3" style="background-color: #feebe8; color: #9c2e2e;">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <div><?= htmlspecialchars($mensaje_error) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Mensaje de Éxito -->
                <?php if (!empty($mensaje_exito)): ?>
                    <div class="alert alert-success d-flex align-items-center border-0 rounded-3 py-2 px-3 small mb-3" style="background-color: #e8f5e9; color: #1b5e20;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div><?= htmlspecialchars($mensaje_exito) ?></div>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nombre Completo *</label>
                        <input type="text" name="nombre" class="form-control form-control-cosmetic" value="<?= htmlspecialchars($nombre) ?>" placeholder="Ej. Maria Lopez" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Correo Electrónico *</label>
                        <input type="email" name="correo" class="form-control form-control-cosmetic" value="<?= htmlspecialchars($correo) ?>" placeholder="tu@ejemplo.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Contraseña *</label>
                        <input type="password" name="password" class="form-control form-control-cosmetic" placeholder="Mínimo 6 caracteres" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold text-secondary">Teléfono</label>
                            <input type="text" name="telefono" class="form-control form-control-cosmetic" value="<?= htmlspecialchars($telefono) ?>" placeholder="3001234567">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold text-secondary">Dirección</label>
                            <input type="text" name="direccion" class="form-control form-control-cosmetic" value="<?= htmlspecialchars($direccion) ?>" placeholder="Calle 10 # 5-20">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cosmetic w-100 mt-2">
                        Registrarme
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center text-muted small">
                    ¿Ya tienes una cuenta? <a href="login.php" class="fw-semibold text-decoration-none" style="color: #b86b6b;">Inicia sesión aquí</a>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>