-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-03-2025 a las 07:13:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `financiera`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `nombre` text NOT NULL,
  `apellido` text NOT NULL,
  `dni` int(10) NOT NULL,
  `domicilio` varchar(100) NOT NULL,
  `telefono` int(20) UNSIGNED DEFAULT NULL,
  `estado` enum('Activo','Deudor','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `dni`, `domicilio`, `telefono`, `estado`) VALUES
(1, 'Gabriela Deolinda', 'Montaña', 35938746, 'MZA D CASA 22 S/N, B° HUARPE, POCITO, SAN JUAN', 2646047760, 'Activo'),
(2, 'Maria Laura', 'Muñoz', 10808262, 'SEBASTIAN CABOT OESTE 315, B° HUAZIHUL, RIVADAVIA, SAN JUAN', 2645619594, 'Activo'),
(3, 'Lucia Alicia', 'Muñoz', 11839254, 'SEBASTIAN CABOT OESTE 315, B° HUAZIHUL, RIVADAVIA, SAN JUAN', 2645619594, 'Activo'),
(4, 'Eugenia Belen', 'Carrizo Mercado', 35510951, 'MZA E CASA 5, B° HUARPE, POCITO, SAN JUAN', 2645041015, 'Activo'),
(5, 'Brisa Daniela', 'Gomez', 42334403, 'LEMOS E/ CALLE 5 Y 6, LOTE N° 2, POCITO, SAN JUAN', 2644845920, 'Activo'),
(6, 'Angela Teodora ', 'Trigo', 6662571, 'URQUIZA SUR 38 ENTRE CARRIL Y GRANADEROS, VILLA NACUSI, POCITO, SAN JUAN', 2645710719, 'Activo'),
(7, 'Myriam del Carmen', 'Nuñez', 13672491, 'MZA U CASA 3, B° TERESA DE CALCUTA, POCITO, SAN JUAN', 0, 'Activo'),
(8, 'Rosa Paola ', 'Fernandez', 26557734, 'B° TERESA DE CALCUTA, MZA F CASA 17, VA. ABERASTAIN, POCITO, SAN JUAN', 2645251067, 'Activo'),
(9, 'Carmen Nelly ', 'Mercado', 17166756, 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', 2644840485, 'Activo'),
(10, 'Joana Beatriz ', 'Carrizo Mercado', 42645141, 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', 2645012637, 'Activo'),
(11, 'Micaela Alejandra', 'Carrizo Mercado', 51195824, 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', 2645407957, 'Activo'),
(12, 'Miguel Angel ', 'Sarmiento', 22935648, 'ABERASTAIN SUR 287, B° PÉREZ HERNANDEZ, RIVADAVIA, SAN JUAN', 2645407957, 'Activo'),
(13, 'Genesis Vanesa', 'Olmedo Saldaño', 39008091, 'MZA T CASA 2, SECTOR II OESTE, B° SIERRAS DEL MARQUESADO, RIVADAVIA, SAN JUAN', 2646123496, 'Activo'),
(14, 'Maria Florencia', 'Gil', 40962859, 'DAVID CHAVEZ E/ CALLE 10 Y 11, LOTE 37, Vª ABERASTAIN, POCITO, SAN JUAN', 0, 'Activo'),
(15, 'Yaquelina Veronica ', 'Mercado', 29098056, 'AMABLE JONES LOTE 80, VILLA HUARPE, POCITO, SAN JUAN', 2645580338, 'Activo'),
(16, 'Luisa Rosalia ', 'Ruarte', 38908663, 'MZA L CASA 10, B° SAN JOSE, RAWSON, SAN JUAN', 2644122308, 'Activo'),
(17, 'Tania Elena', 'Cortez', 33337790, 'MZA 23 CASA 1, B° LA ESTACION, RAWSON, SAN JUAN', 2645828964, 'Activo'),
(18, 'Valentina Joana ', 'Zabala ', 45885024, 'B° ABERASTAIN MZA B CASA 2, POCITO, SAN JUAN', 2645152507, 'Activo'),
(19, 'Nadia Alejandra ', 'Garcia Ramirez', 30634169, 'CALLEJON GALLASTELL CASA 4, MANO DERECHA, POCITO, SAN JUAN', 2646319623, 'Activo'),
(20, 'Natalia Lorena', 'Guzman Navarro', 33095325, 'B° CRUCE DE LOS ANDES III, MZA H CASA 31, POCITO, SAN JUAN', 2646628423, 'Activo'),
(21, 'Ivana Natalia ', 'Miranda', 32939196, 'GDOR. MANUEL QUIROGA OESTE 733, Vª KRAUSE, RAWSON, SAN JUAN', 2646318999, 'Activo'),
(22, 'Carina Ayelen', 'Cortez', 43376031, 'MZA 23 CASA 1, B° LA ESTACION, RAWSON, SAN JUAN', 2645828964, 'Activo'),
(23, 'Talia Noemi', 'Guajardo', 43690127, 'MZA 10 CASA 18 SECTOR 1 S/N, B° LA ESTACIÓN, RAWSON, SAN JUAN', 2645198984, 'Activo'),
(24, 'Liliana Rosario', 'Balmaceda', 18594620, 'MZA P CASA 8 S/N, B° CERRO GRANDE, POCITO, SAN JUAN', 2645674356, 'Activo'),
(25, 'Tamara Caren', 'Rios Olguin', 44249164, 'B° SAN JOSE MZA J CASA 6, RAWSON, SAN JUAN', 2645530411, 'Activo'),
(26, 'Emanuel Alejandro', 'Espina', 28967961, 'MZA C CASA 16, B° CONJUNTO 8, RAWSON, SAN JUAN', 2646110163, 'Activo'),
(27, 'Eliana Valeria', 'Olguin', 32007497, 'B° HUARPES, MZA G CASA 22, POCITO, SAN JUAN', 2643176337, 'Activo'),
(28, 'Eliana Valeria', 'Olguin', 32007497, 'B° HUARPES, MZA G CASA 22, POCITO, SAN JUAN', 2643176337, 'Activo'),
(29, 'Eliana Valeria', 'Olguin', 32007497, 'B° HUARPES, MZA G CASA 22, POCITO, SAN JUAN', 2643176337, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditos`
--

CREATE TABLE `creditos` (
  `id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `cuotas` int(10) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('Activo','Vencido','Pagado') DEFAULT 'Activo',
  `frecuencia` enum('mensual','semanal','quincenal') NOT NULL DEFAULT 'mensual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `creditos`
--

INSERT INTO `creditos` (`id`, `cliente_id`, `monto`, `cuotas`, `fecha_inicio`, `fecha_vencimiento`, `estado`, `frecuencia`) VALUES
(1, 1, 100000.00, 1, '2025-03-21', '2025-04-21', 'Activo', 'mensual');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(10) UNSIGNED NOT NULL,
  `id_prestamo` int(11) UNSIGNED NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto_pagado` float DEFAULT NULL,
  `estado` enum('Pagado','Impago') DEFAULT 'Impago',
  `fecha_vencimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `creditos`
--
ALTER TABLE `creditos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_prestamo` (`id_prestamo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `creditos`
--
ALTER TABLE `creditos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `creditos`
--
ALTER TABLE `creditos`
  ADD CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_prestamos` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamos` (`id_prestamo`),
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamos` (`id_prestamo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
