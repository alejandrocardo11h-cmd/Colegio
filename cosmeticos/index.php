<?php
// index.php
header('Location: catalogo.php');
exit;
?>

$nombre = $_SESSION["nombre"];
$rol = $_SESSION["rol"];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tienda de Cosméticos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">

        <div class="container">

            <a class="navbar-brand" href="index.php">
                Tienda de Cosméticos
            </a>

            <div>

                <span class="text-white me-3">
                    Bienvenida, <?php echo htmlspecialchars($nombre); ?>
                </span>

                <a href="cerrar_sesion.php" class="btn btn-danger">
                    Cerrar sesión
                </a>

            </div>

        </div>

    </nav>


    <div class="container mt-5">

        <div class="text-center">

            <h1>Bienvenida a nuestra tienda de cosméticos</h1>

            <p class="lead">
                Encuentra productos de belleza, maquillaje y cuidado personal.
            </p>

        </div>


        <div class="row mt-5">

            <div class="col-md-4">

                <div class="card text-center">

                    <div class="card-body">

                        <h3>💄 Maquillaje</h3>

                        <p>
                            Encuentra labiales, bases, rubores y más.
                        </p>

                        <a href="productos.php" class="btn btn-primary">
                            Ver productos
                        </a>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card text-center">

                    <div class="card-body">

                        <h3>✨ Cuidado facial</h3>

                        <p>
                            Productos para el cuidado de tu piel.
                        </p>

                        <a href="productos.php" class="btn btn-primary">
                            Ver productos
                        </a>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card text-center">

                    <div class="card-body">

                        <h3>🛍️ Productos</h3>

                        <p>
                            Consulta nuestro catálogo de productos.
                        </p>

                        <a href="productos.php" class="btn btn-primary">
                            Ver catálogo
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>