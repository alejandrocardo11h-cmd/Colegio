<?php
// logout.php
session_start();

// 1. Vaciar todas las variables de sesión
$_SESSION = array();

// 2. Destruir la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir directamente al catálogo de productos
header('Location: catalogo.php');
exit;
?>