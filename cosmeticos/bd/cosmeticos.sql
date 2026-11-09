-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-09-2026 a las 15:40:39
-- Versión del servidor: 8.4.3
-- Versión de PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tienda_cosmeticos`
--
CREATE DATABASE IF NOT EXISTS `tienda_cosmeticos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tienda_cosmeticos`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Cuidado Facial (Skincare)', 'Sérums, tónicos, cremas hidratantes y protectores solares'),
(2, 'Maquillaje de Ojos', 'Pestañinas, delineadores, sombras y fijadores de cejas'),
(3, 'Labios', 'Labiales líquidos mate, brillos labiales, bálsamos y tintas'),
(4, 'Rostro y Base', 'Bases de maquillaje, polvos compactos, correctores y rubores'),
(5, 'Cuidado Corporal', 'Exfoliantes, cremas humectantes y lociones con brillo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id_detalle` int NOT NULL,
  `id_pedido` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 1, 1, 48000.00),
(2, 1, 2, 1, 62000.00),
(3, 1, 4, 1, 26000.00),
(4, 2, 7, 1, 32000.00),
(5, 2, 8, 1, 21000.00),
(6, 2, 5, 1, 19000.00),
(7, 3, 2, 1, 62000.00),
(8, 3, 10, 1, 45000.00),
(9, 4, 9, 1, 55000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int NOT NULL,
  `id_usuario` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagado','enviado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_pedido` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `total`, `estado`, `fecha_pedido`) VALUES
(1, 2, 136000.00, 'enviado', '2026-08-25 10:15:00'),
(2, 3, 72000.00, 'pagado', '2026-09-01 14:30:20'),
(3, 4, 107000.00, 'pendiente', '2026-09-03 18:45:10'),
(4, 2, 55000.00, 'cancelado', '2026-09-02 09:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int NOT NULL,
  `id_categoria` int NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.jpg',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `nombre`, `descripcion`, `precio`, `stock`, `imagen`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'Sérum Ácido Hialurónico 30ml', 'Hidratación profunda antiedad con textura ligera', 48000.00, 25, 'imagenes/serum_ha.jpg', 'activo', '2026-09-04 10:25:35'),
(2, 1, 'Protector Solar Toque Seco SPF 50+', 'Filtro solar facial mate sin sensación grasosa', 62000.00, 18, 'imagenes/bloqueador.jpg', 'activo', '2026-09-04 10:25:35'),
(3, 1, 'Gel Limpiador con Ácido Salicílico', 'Limpieza profunda para piel grasa con tendencia a acné', 38000.00, 12, 'limpiador.jpg', 'activo', '2026-09-04 10:25:35'),
(4, 3, 'Labial Líquido Mate Tono Carmín', 'Fórmula indeleble de larga duración 16 horas', 26000.00, 30, 'labial_carmin.jpg', 'activo', '2026-09-04 10:25:35'),
(5, 3, 'Tinta Multiusos Rosa Suave', 'Apta para labios y mejillas, acabado natural', 19000.00, 40, 'tinta_rosa.jpg', 'activo', '2026-09-04 10:25:35'),
(6, 3, 'Gloss Hidratante con Ácido Hialurónico', 'Brillo intenso no pegajoso con efecto voluminizador', 24000.00, 15, 'gloss.jpg', 'activo', '2026-09-04 10:25:35'),
(7, 2, 'Pestañina Volumen Extremo Lavable', 'Cepillo curvo para máximo volumen sin grumos', 32000.00, 20, 'pestania.jpg', 'activo', '2026-09-04 10:25:35'),
(8, 2, 'Delineador Líquido Waterproof Negro', 'Punta plumón ultra precisa color negro intenso', 21000.00, 22, 'delineador.jpg', 'activo', '2026-09-04 10:25:35'),
(9, 2, 'Paleta de Sombras Nude 12 Tonos', 'Acabados mate y satinados de alta pigmentación', 55000.00, 10, 'sombras_nude.jpg', 'activo', '2026-09-04 10:25:35'),
(10, 4, 'Base de Maquillaje Cobertura Media Tono 02', 'Acabado semi-mate de larga duración', 45000.00, 14, 'base_02.jpg', 'activo', '2026-09-04 10:25:35'),
(11, 4, 'Polvo Traslúcido Matificante', 'Fija el maquillaje y controla el brillo durante el día', 28000.00, 16, 'polvo_traslucido.jpg', 'activo', '2026-09-04 10:25:35'),
(12, 4, 'Rubor en Crema Tono Melocotón', 'Fórmula fundente fácil de difuminar', 23000.00, 0, 'rubor.jpg', 'inactivo', '2026-09-04 10:25:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('cliente','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'cliente',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `password`, `telefono`, `direccion`, `rol`, `fecha_registro`) VALUES
(1, 'Administrador Tienda', 'admin@cosmeticos.com', '$2y$10$wT8vFq1R2F7NkW7QpYgHDe99Z0gC6qVvQo9nC9qU8xYgO7V7t2f3S', '3157890123', 'Cra 5 # 10-20, Centro', 'admin', '2026-09-04 10:25:35'),
(2, 'Valentina Torres', 'valentina.torres@gmail.com', '$2y$10$wT8vFq1R2F7NkW7QpYgHDe99Z0gC6qVvQo9nC9qU8xYgO7V7t2f3S', '3104567890', 'Calle 24 # 7-15, Apto 302', 'cliente', '2026-09-04 10:25:35'),
(3, 'Camila Rodríguez', 'camila.rodriguez@outlook.com', '$2y$10$wT8vFq1R2F7NkW7QpYgHDe99Z0gC6qVvQo9nC9qU8xYgO7V7t2f3S', '3209876543', 'Cra 15 # 48-30, Casa 12', 'cliente', '2026-09-04 10:25:35'),
(4, 'Andrea Morales', 'andrea.morales@yahoo.es', '$2y$10$wT8vFq1R2F7NkW7QpYgHDe99Z0gC6qVvQo9nC9qU8xYgO7V7t2f3S', '3183344556', 'Torre 2 Apto 501', 'cliente', '2026-09-04 10:25:35');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_pedido` (`id_pedido`),
  ADD KEY `fk_detalle_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedido_usuario` (`id_usuario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `fk_producto_categoria` (`id_categoria`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id_detalle` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
