<?php
// login.php
session_start();

require_once 'inc/conexion.php';

// Si ya tiene sesión activa, redirigir al panel principal
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

// INICIALIZAR VARIABLES PARA EVITAR WARNINGS
$error = $_GET['error'] ?? '';
$correo = ''; // <-- AGREGAR ESTA LÍNEA

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($correo) && !empty($password)) {
        $sql = "SELECT id_usuario, nombre, correo, password, rol FROM usuarios WHERE correo = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $correo);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if ($usuario = mysqli_fetch_assoc($resultado)) {
                if (password_verify($password, $usuario['password'])) {
                    $_SESSION['usuario_id'] = $usuario['id_usuario'];
                    $_SESSION['nombre']     = $usuario['nombre'];
                    $_SESSION['rol']        = strtolower(trim($usuario['rol']));

                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'La contraseña ingresada es incorrecta.';
                }
            } else {
                $error = 'No existe una cuenta registrada con este correo.';
            }
        } else {
            $error = 'Error en la consulta a la base de datos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Cosmetics | Acceso Exclusivo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --cosmetic-rose: #d88a8a;
            --cosmetic-dark: #2a2024;
            --cosmetic-gold: #c59b6d;
            --cosmetic-bg: #faf6f5;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #fcefe9 0%, #f6e3e1 50%, #eddcd9 100%);
            color: var(--cosmetic-dark);
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            letter-spacing: 1px;
        }

        .beauty-card {
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(184, 134, 134, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
        }

        .beauty-banner {
            background: linear-gradient(180deg, rgba(42, 32, 36, 0.25) 0%, rgba(42, 32, 36, 0.75) 100%), 
                        url('https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?q=80&w=1000&auto=format&fit=crop') center/cover no-repeat;
            min-height: 250px;
        }

        .form-control-cosmetic {
            border: 1.5px solid #ebd9d8;
            background-color: #fffaf9;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.95rem;
            transition: all 0.25s ease;
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
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-cosmetic:hover {
            background: linear-gradient(135deg, #cf7c7c 0%, #a45a5a 100%);
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(184, 107, 107, 0.35);
            transform: translateY(-1px);
        }

        .tag-pill {
            display: inline-block;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 30px;
            padding: 4px 14px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #ffffff;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9">
            
            <div class="card beauty-card">
                <div class="row g-0">
                    
                    <!-- Lateral Izquierdo: Branding & Visual -->
                    <div class="col-md-6 beauty-banner d-flex flex-column justify-content-between p-4 p-lg-5 text-white">
                        <div>
                            <span class="tag-pill">Colección 2026</span>
                        </div>
                        <div class="my-4">
                            <h2 class="brand-title display-6 fw-normal mb-2">Aura & Glow</h2>
                            <p class="small text-white-50 mb-0">
                                <em>"La belleza comienza en el momento en que decides cuidar de ti misma."</em>
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-3 small text-white-50">
                            <span><i class="bi bi-flower1 me-1 text-white"></i> Skincare</span>
                            <span>•</span>
                            <span><i class="bi bi-stars me-1 text-white"></i> Maquillaje</span>
                            <span>•</span>
                            <span><i class="bi bi-droplet-half me-1 text-white"></i> Vegano</span>
                        </div>
                    </div>

                    <!-- Lateral Derecho: Formulario -->
                    <div class="col-md-6 p-4 p-lg-5 d-flex flex-column justify-content-center">
                        
                        <div class="text-center mb-4">
                            <div class="d-inline-flex p-3 rounded-circle mb-3" style="background-color: #f7e6e5; color: #b86b6b;">
                                <i class="bi bi-bag-heart-fill fs-4"></i>
                            </div>
                            <h3 class="brand-title fw-bold mb-1">Bienvenida de nuevo</h3>
                            <p class="text-muted small">Ingresa a tu cuenta para gestionar tus cosméticos y pedidos</p>
                        </div>

                        <?php if (!empty($mensaje_error)): ?>
                            <div class="alert alert-danger d-flex align-items-center border-0 rounded-3 py-2 px-3 small mb-4" role="alert" style="background-color: #feebe8; color: #9c2e2e;">
                                <i class="bi bi-exclamation-circle-fill me-2 fs-6"></i>
                                <div><?= htmlspecialchars($mensaje_error) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Corregido: action="" enviará los datos al mismo archivo -->
                        <form action="" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="correo" class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-envelope me-1"></i> Correo Electrónico
                                </label>
                                <input type="email" class="form-control form-control-cosmetic" id="correo" name="correo" 
                                       placeholder="tu.nombre@ejemplo.com" value="<?= htmlspecialchars($correo) ?>" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-lock me-1"></i> Contraseña
                                </label>
                                <input type="password" class="form-control form-control-cosmetic" id="password" name="password" 
                                       placeholder="••••••••" required>
                            </div>

                            <button type="submit" class="btn btn-cosmetic w-100 mt-3 d-flex align-items-center justify-content-center gap-2">
                                <span>Acceder al Portal</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>

                        <div class="mt-4 pt-3 border-top text-center text-muted small">
                            ¿No tienes una cuenta aún? <a href="registro.php" class="fw-semibold text-decoration-none" style="color: #b86b6b;">Regístrate aquí</a>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>1