-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-03-2026 a las 22:45:35
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tienda_online`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `password` varchar(120) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `token_password` varchar(50) DEFAULT NULL,
  `password_request` tinyint(4) NOT NULL DEFAULT 0,
  `activo` tinyint(4) NOT NULL,
  `fecha_alta` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`id`, `usuario`, `password`, `nombre`, `email`, `token_password`, `password_request`, `activo`, `fecha_alta`) VALUES
(1, 'admin', '$2y$10$6gwH8Fc5xzzRV6A.XATdMuTkUwUDUE.7mUMptacwbTlomYm41DGwW', 'Admin super', 'alesfornarvaez@gmail.com', NULL, 0, 1, '2025-12-08 16:08:47'),
(2, 'pepe', '$2y$10$3Y/HHqDGBFDFoCnNVs2We.szc2o8J2xoR8xZiUg4zkIlqkKjWj4M2', 'pepa perez', 'a.narvaezolivera@gmail.com', NULL, 0, 1, '2026-03-10 16:00:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas`
--

CREATE TABLE `caracteristicas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `activo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caracteristicas`
--

INSERT INTO `caracteristicas` (`id`, `nombre`, `activo`) VALUES
(1, 'Color', 0),
(2, 'Tela', 0),
(3, 'Talla', 0),
(4, 'Genero', 0),
(5, 'pepee', 0),
(6, 'Hombre', 0),
(7, 'Mujer', 0),
(8, 'Pantalones', 0),
(9, 'hector 2', 0),
(10, 'Estudiantiljjjjjjjjj', 0),
(11, 'hector 2', 0),
(12, 'Ropa', 1),
(13, 'pantalones', 1),
(14, 'camisas', 1),
(15, 'corbatas', 1),
(16, 'medias', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracter_producto`
--

CREATE TABLE `caracter_producto` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_caracteristica` int(11) NOT NULL,
  `valor` varchar(30) NOT NULL,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caracter_producto`
--

INSERT INTO `caracter_producto` (`id`, `id_producto`, `id_caracteristica`, `valor`, `stock`) VALUES
(6, 11, 2, 'algodon', 23),
(7, 11, 1, 'azul', 23),
(8, 9, 3, 'L', 10),
(9, 10, 3, 'M', 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `email` varchar(200) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `documento` varchar(30) NOT NULL,
  `estatus` tinyint(4) NOT NULL,
  `fecha_alta` datetime NOT NULL,
  `fecha_modifica` datetime DEFAULT NULL,
  `fecha_baja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `apellido`, `email`, `telefono`, `documento`, `estatus`, `fecha_alta`, `fecha_modifica`, `fecha_baja`) VALUES
(10, 'alejandro', 'Prueba', 'alesfornarvaez@gmail.com', '302222222221', '12121', 1, '2025-12-21 14:51:52', NULL, NULL),
(11, 'jeffrey', 'suarez', 'jeffrey232008suarez@gmail.com', '1231231231', '2434534534', 1, '2026-02-23 13:36:31', NULL, NULL),
(12, 'jhon', 'perna', 'jaderperna@gmail.com', '234324', '231231', 1, '2026-03-04 13:35:29', NULL, NULL),
(13, 'sara', 'xxx', 'sarahiguita21@gmail.com', '34567', '123456', 1, '2026-03-10 15:02:44', NULL, NULL),
(14, 'pepe', 'qwdq', 'a.narvaezolivera@gmail.com', '1233', '123213', 1, '2026-03-10 15:59:04', NULL, NULL),
(15, 'fds', 'sfsf', 'juan@gmail.com', '312313123312', '3242342', 1, '2026-03-11 16:35:41', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra`
--

CREATE TABLE `compra` (
  `id` int(11) NOT NULL,
  `id_transaccion` varchar(20) NOT NULL,
  `fecha` datetime NOT NULL,
  `status` varchar(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `medio_pago` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compra`
--

INSERT INTO `compra` (`id`, `id_transaccion`, `fecha`, `status`, `email`, `id_cliente`, `total`, `medio_pago`) VALUES
(3, '7XE11479J14624947', '2026-02-17 19:56:54', 'pagado', 'alesfornarvaez@gmail.com', 10, 21600.00, 'PayPal'),
(4, '3FU845957J3286129', '2026-02-17 19:59:01', 'pagado', 'alesfornarvaez@gmail.com', 10, 2400.00, 'PayPal'),
(5, '4EF81938A7411750P', '2026-02-17 21:25:41', 'pagado', 'alesfornarvaez@gmail.com', 10, 134850.00, 'PayPal'),
(6, '7LB60679X1670921F', '2026-02-17 21:26:50', 'pagado', 'alesfornarvaez@gmail.com', 10, 247380.00, 'PayPal'),
(7, '8RH19212BY482693S', '2026-02-17 21:49:04', 'procesando', 'alesfornarvaez@gmail.com', 10, 134850.00, 'PayPal'),
(8, '7MD0401112561634A', '2026-02-23 19:42:06', 'entregado', 'jeffrey232008suarez@gmail.com', 11, 24000.00, 'PayPal'),
(9, '69224047V87027838', '2026-02-23 19:55:47', 'enviado', 'jeffrey232008suarez@gmail.com', 11, 12960.00, 'PayPal'),
(10, '1BF4258293028640A', '2026-03-04 21:14:32', 'pagado', 'alesfornarvaez@gmail.com', 10, 21600.00, 'PayPal'),
(11, '9M519245E9693572N', '2026-03-04 21:28:17', 'pagado', 'jaderperna@gmail.com', 12, 21120.00, 'PayPal'),
(12, '8R557450ML285115L', '2026-03-10 19:57:08', 'enviado', 'alesfornarvaez@gmail.com', 10, 16000.00, 'PayPal'),
(13, '4KG01534TJ266542S', '2026-03-10 20:06:18', 'cancelado', 'alesfornarvaez@gmail.com', 10, 10560.00, 'PayPal'),
(14, '7GC695822S340464X', '2026-03-10 21:04:42', 'pagado', 'sarahiguita21@gmail.com', 13, 10560.00, 'PayPal'),
(15, '441688461A3769945', '2026-03-10 21:07:09', 'COMPLETED', 'alesfornarvaez@gmail.com', 10, 10560.00, 'PayPal'),
(16, '03707742DV972980K', '2026-03-10 21:12:41', 'COMPLETED', 'sarahiguita21@gmail.com', 13, 21120.00, 'PayPal'),
(17, '5U494208890302121', '2026-03-10 22:15:13', 'entregado', 'alesfornarvaez@gmail.com', 10, 259140.00, 'PayPal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `valor` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `nombre`, `valor`) VALUES
(1, 'tienda_nombre', 'D\'gala'),
(2, 'correo_email', 'alesfornarvaez@gmail.com'),
(3, 'correo_smtp', 'smtp.gmail.com'),
(4, 'correo_password', 'wphRdZpMQXX4loVwxA5FbQ==...'),
(5, 'correo_puerto', '587');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c_colores`
--

CREATE TABLE `c_colores` (
  `id` smallint(6) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `codigo_hex` varchar(7) NOT NULL COMMENT '#FFFFFF',
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `c_colores`
--

INSERT INTO `c_colores` (`id`, `nombre`, `codigo_hex`, `estado`) VALUES
(1, 'Negro', '#000000', 1),
(2, 'Azul', '#0000FF', 1),
(3, 'Verde', '#008000', 1),
(4, 'Rojo', '#FF0000', 1),
(5, 'Blanco', '#FFFFFF', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c_tallas`
--

CREATE TABLE `c_tallas` (
  `id` smallint(6) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `c_tallas`
--

INSERT INTO `c_tallas` (`id`, `nombre`) VALUES
(1, '28'),
(2, '30'),
(3, '32'),
(4, '34'),
(5, '36'),
(6, 'S'),
(7, 'M'),
(8, 'XL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra`
--

CREATE TABLE `detalle_compra` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_compra`
--

INSERT INTO `detalle_compra` (`id`, `id_compra`, `id_producto`, `titulo`, `precio`, `cantidad`) VALUES
(3, 3, 9, 'Conjunto louis Button', 21600.00, 1),
(4, 5, 10, 'Conjunto louis Button', 10560.00, 1),
(5, 5, 11, 'roblox (Talla: 28, Color: Azul)', 1200.00, 1),
(6, 5, 11, 'roblox (Talla: M, Color: Negro)', 123090.00, 1),
(7, 6, 11, 'roblox (Talla: 28, Color: Azul)', 1200.00, 1),
(8, 6, 11, 'roblox (Talla: M, Color: Negro)', 123090.00, 2),
(9, 7, 10, 'Conjunto louis Button', 10560.00, 1),
(10, 7, 11, 'roblox (Talla: 28, Color: Azul)', 1200.00, 1),
(11, 7, 11, 'roblox (Talla: M, Color: Negro)', 123090.00, 1),
(12, 8, 9, 'Conjunto louis Button', 21600.00, 1),
(13, 8, 11, 'roblox (Talla: 28, Color: Azul)', 1200.00, 2),
(14, 9, 10, 'Conjunto louis Button', 10560.00, 1),
(15, 9, 11, 'roblox (Talla: 28, Color: Azul)', 1200.00, 2),
(16, 10, 9, 'Conjunto louis Button', 21600.00, 1),
(17, 11, 10, 'Conjunto louis Button', 10560.00, 2),
(18, 12, 11, 'roblox', 16000.00, 1),
(19, 13, 10, 'Conjunto louis Button', 10560.00, 1),
(20, 14, 10, 'Conjunto louis Button', 10560.00, 1),
(21, 15, 10, 'Conjunto louis Button', 10560.00, 1),
(22, 16, 10, 'Conjunto louis Button', 10560.00, 2),
(23, 17, 10, 'Conjunto louis Button', 10560.00, 1),
(24, 17, 11, 'roblox (Talla: 28, Color: Azul)', 1200.00, 2),
(25, 17, 11, 'roblox (Talla: M, Color: Negro)', 123090.00, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `descuento` tinyint(3) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `activo` int(11) DEFAULT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `titulo`, `descripcion`, `precio`, `descuento`, `stock`, `imagen`, `id_categoria`, `activo`, `fecha_publicacion`) VALUES
(9, 'Conjunto louis Button', 'Un saco es un recipiente flexible...', 24000.00, 10, 123, 'main_68b9d89582fb4-hombre.webp', 1, 1, '2025-09-04 18:21:09'),
(10, 'Conjunto louis Button', 'hola funciona', 12000.00, 12, 1221, 'main_68b9f00a43078-mujer.webp', 1, 1, '2025-09-04 20:01:14'),
(11, 'roblox', 'traje formal...', 20000.00, 20, 0, 'main_68c31474c89ff-hombre.webp', 0, 1, '2025-09-11 18:27:00'),
(13, 'jeffrey', 'is handsome', 1000000.00, 0, 120, 'main_699ca0bca1dd6-S.drawio (1).png', 6, 1, '2026-02-23 18:47:24'),
(14, 'n', 'n', 100.00, 100, 0, 'main_69b08ba4ee34d-images.jpg', 5, 1, '2026-03-10 21:22:44'),
(15, 'fasa', 'wefwefewffwef', 12.00, 18, -3224, 'main_69b1bbf350c50-evidencia.jpg', 12, 1, '2026-03-11 19:01:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_variantes`
--

CREATE TABLE `productos_variantes` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_talla` smallint(6) DEFAULT NULL,
  `id_color` smallint(6) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos_variantes`
--

INSERT INTO `productos_variantes` (`id`, `id_producto`, `id_talla`, `id_color`, `precio`, `stock`) VALUES
(1, 11, 1, 2, 1200.00, 123),
(2, 11, 7, 1, 123090.00, 3443),
(3, 13, 3, 5, 2000000.00, 300),
(4, 13, 2, 4, 2000000.00, 300),
(5, 14, 2, 2, 1220.00, 10),
(6, 14, 2, 5, 1222.00, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_imagenes`
--

CREATE TABLE `producto_imagenes` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_imagenes`
--

INSERT INTO `producto_imagenes` (`id`, `producto_id`, `imagen`) VALUES
(1, 13, 'ropa_699ca0bcaab2e-mapa mental andres felipe jaramillo.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(80) NOT NULL,
  `password` varchar(120) NOT NULL,
  `activacion` int(11) NOT NULL DEFAULT 0,
  `token` varchar(40) NOT NULL,
  `token_password` varchar(40) DEFAULT NULL,
  `password_request` int(11) NOT NULL DEFAULT 0,
  `id_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `password`, `activacion`, `token`, `token_password`, `password_request`, `id_cliente`) VALUES
(1, 'Prueba_XL', '$2y$10$KV3/oauD57ozQnNIbsVgueVnI4F7DJWyOpj4pDXX/GrNrm1/YEHe2', 1, '', '', 0, 10),
(2, 'jeffrey', '$2y$10$vs8Y3Ql.VDuvBsB6ekqGVO5ZYnP15nW6ZmMpKw.4zwVP6VKIt9k9S', 1, '4c3e92a33a2dc37496e1d841bf6bed67', '', 0, 11),
(3, 'negro', '$2y$10$vzACEvI1U87nAIwqBjBmluqRl1m83cZB9Iim/Ypj/xxrFPAsBtgau', 1, '7599c00424fbf0c699b72e6bf5002daf', NULL, 0, 12),
(4, 'sara', '$2y$10$on3S1QVodFSPwxvbgxTJE.Adkm1ucst74X.R3Czx69Heu5evS5W02', 1, '8c43bac440087d9265b832918c4e9135', NULL, 0, 13),
(5, 'pepe', '$2y$10$ntccrLD.83Jk.w5aAe69pePcJIgmsHW8vyl63X1UxpanMk4G4r6yW', 1, '2cb718b1ec4aff42bb0b4edafe9b0e4b', NULL, 0, 14),
(6, 'pp', '$2y$10$Ri05Gm411o/FP9T/j3ISA.9NS2IY8wmWwzZtg8PPqj1iFcJuSUq22', 1, '92dd49045b0451b5b8f2a911b9206fd0', NULL, 0, 15);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caracteristicas`
--
ALTER TABLE `caracteristicas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caracter_producto`
--
ALTER TABLE `caracter_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_det_prod` (`id_producto`),
  ADD KEY `fk_det_caracter` (`id_caracteristica`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_compra_cliente` (`id_cliente`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `c_colores`
--
ALTER TABLE `c_colores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_color_nombre` (`nombre`);

--
-- Indices de la tabla `c_tallas`
--
ALTER TABLE `c_tallas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_compra_compra` (`id_compra`),
  ADD KEY `fk_detalle_compra_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos_variantes`
--
ALTER TABLE `productos_variantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_var_prod` (`id_producto`);

--
-- Indices de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario` (`nombre_usuario`),
  ADD KEY `fk_usuario_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `caracteristicas`
--
ALTER TABLE `caracteristicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `caracter_producto`
--
ALTER TABLE `caracter_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `c_colores`
--
ALTER TABLE `c_colores`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `c_tallas`
--
ALTER TABLE `c_tallas`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `productos_variantes`
--
ALTER TABLE `productos_variantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `caracter_producto`
--
ALTER TABLE `caracter_producto`
  ADD CONSTRAINT `fk_det_caracter` FOREIGN KEY (`id_caracteristica`) REFERENCES `caracteristicas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_det_prod` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `fk_compra_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `fk_detalle_compra_compra` FOREIGN KEY (`id_compra`) REFERENCES `compra` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_compra_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos_variantes`
--
ALTER TABLE `productos_variantes`
  ADD CONSTRAINT `fk_var_prod` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD CONSTRAINT `producto_imagenes_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
