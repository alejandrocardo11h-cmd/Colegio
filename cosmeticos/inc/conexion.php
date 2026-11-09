<?php
    // conexion.php
    $servidor = 'localhost';
    $usuario  = 'root';
    $clave    = ''; // En XAMPP o Laragon suele estar vacía por defecto
    $bd       = 'tienda_cosmeticos';

    // Conexión procedimental directa
    $conexion = mysqli_connect($servidor, $usuario, $clave, $bd);

    if (!$conexion) {
        die("Error al conectar con MySQL: " . mysqli_connect_error());
    }

    // Establecer codificación de caracteres a utf8mb4
    mysqli_set_charset($conexion, 'utf8mb4');
?>