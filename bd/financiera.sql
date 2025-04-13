-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-04-2025 a las 17:01:34
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
  `dni` varchar(20) NOT NULL,
  `domicilio` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `estado` enum('Activo','Deudor','Inactivo') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `dni`, `domicilio`, `telefono`, `estado`) VALUES
(1, 'Gabriela Deolinda', 'Montaña', '35938746', 'MZA D CASA 22 S/N, B° HUARPE, POCITO, SAN JUAN', '2646047760', 'Activo'),
(2, 'Maria Laura', 'Muñoz', '10808262', 'SEBASTIAN CABOT OESTE 315, B° HUAZIHUL, RIVADAVIA, SAN JUAN', '2645619594', 'Inactivo'),
(3, 'Lucia Alicia', 'Muñoz', '11839254', 'SEBASTIAN CABOT OESTE 315, B° HUAZIHUL, RIVADAVIA, SAN JUAN', '2645619594', 'Inactivo'),
(4, 'Eugenia Belen', 'Carrizo Mercado', '35510951', 'MZA E CASA 5, B° HUARPE, POCITO, SAN JUAN', '2645041015', 'Activo'),
(5, 'Brisa Daniela', 'Gomez', '42334403', 'LEMOS E/ CALLE 5 Y 6, LOTE N° 2, POCITO, SAN JUAN', '2644845920', 'Inactivo'),
(6, 'Angela Teodora ', 'Trigo', '6662571', 'URQUIZA SUR 38 ENTRE CARRIL Y GRANADEROS, VILLA NACUSI, POCITO, SAN JUAN', '2645710719', 'Inactivo'),
(7, 'Myriam del Carmen', 'Nuñez', '13672491', 'MZA U CASA 3, B° TERESA DE CALCUTA, POCITO, SAN JUAN', '0', 'Inactivo'),
(8, 'Rosa Paola ', 'Fernandez', '26557734', 'B° TERESA DE CALCUTA, MZA F CASA 17, VA. ABERASTAIN, POCITO, SAN JUAN', '2645251067', 'Activo'),
(9, 'Carmen Nelly ', 'Mercado', '17166756', 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', '2644840485', 'Activo'),
(10, 'Joana Beatriz ', 'Carrizo Mercado', '42645141', 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', '2645012637', 'Activo'),
(11, 'Micaela Alejandra', 'Carrizo Mercado', '51195824', 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', '2645407957', 'Activo'),
(12, 'Miguel Angel ', 'Sarmiento', '22935648', 'ABERASTAIN SUR 287, B° PÉREZ HERNANDEZ, RIVADAVIA, SAN JUAN', '2645407957', 'Inactivo'),
(13, 'Genesis Vanesa', 'Olmedo Saldaño', '39008091', 'MZA T CASA 2, SECTOR II OESTE, B° SIERRAS DEL MARQUESADO, RIVADAVIA, SAN JUAN', '2646123496', 'Inactivo'),
(14, 'Maria Florencia', 'Gil', '40962859', 'DAVID CHAVEZ E/ CALLE 10 Y 11, LOTE 37, Vª ABERASTAIN, POCITO, SAN JUAN', '0', 'Inactivo'),
(15, 'Yaquelina Veronica ', 'Mercado', '29098056', 'AMABLE JONES LOTE 80, VILLA HUARPE, POCITO, SAN JUAN', '2645580338', 'Inactivo'),
(16, 'Luisa Rosalia ', 'Ruarte', '38908663', 'MZA L CASA 10, B° SAN JOSE, RAWSON, SAN JUAN', '2644122308', 'Inactivo'),
(17, 'Tania Elena', 'Cortez', '33337790', 'MZA 23 CASA 1, B° LA ESTACION, RAWSON, SAN JUAN', '2645828964', 'Inactivo'),
(18, 'Valentina Joana ', 'Zabala ', '45885024', 'B° ABERASTAIN MZA B CASA 2, POCITO, SAN JUAN', '2645152507', 'Inactivo'),
(19, 'Nadia Alejandra ', 'Garcia Ramirez', '30634169', 'CALLEJON GALLASTELL CASA 4, MANO DERECHA, POCITO, SAN JUAN', '2646319623', 'Inactivo'),
(20, 'Natalia Lorena', 'Guzman Navarro', '33095325', 'B° CRUCE DE LOS ANDES III, MZA H CASA 31, POCITO, SAN JUAN', '2646628423', 'Inactivo'),
(21, 'Ivana Natalia ', 'Miranda', '32939196', 'GDOR. MANUEL QUIROGA OESTE 733, Vª KRAUSE, RAWSON, SAN JUAN', '2646318999', 'Inactivo'),
(22, 'Carina Ayelen', 'Cortez', '43376031', 'MZA 23 CASA 1, B° LA ESTACION, RAWSON, SAN JUAN', '2645828964', 'Inactivo'),
(23, 'Talia Noemi', 'Guajardo', '43690127', 'MZA 10 CASA 18 SECTOR 1 S/N, B° LA ESTACIÓN, RAWSON, SAN JUAN', '2645198984', 'Inactivo'),
(24, 'Liliana Rosario', 'Balmaceda', '18594620', 'MZA P CASA 8 S/N, B° CERRO GRANDE, POCITO, SAN JUAN', '2645674356', 'Inactivo'),
(25, 'Tamara Caren', 'Rios Olguin', '44249164', 'B° SAN JOSE MZA J CASA 6, RAWSON, SAN JUAN', '2645530411', 'Inactivo'),
(26, 'Emanuel Alejandro', 'Espina', '28967961', 'MZA C CASA 16, B° CONJUNTO 8, RAWSON, SAN JUAN', '2646110163', 'Inactivo'),
(27, 'Eliana Valeria', 'Olguin', '32007497', 'B° HUARPES, MZA G CASA 22, POCITO, SAN JUAN', '2643176337', 'Inactivo'),
(30, 'Jorge Cesar', 'Bacil', '11142487', 'DEVOTO OESTE 390, VILLA KRAUSE, RAWSON, SAN JUAN', '2644058489', 'Inactivo'),
(32, 'Claudia Beatriz', 'Guajardo', '22659770', 'VIRGEN DEL LORETO OESTE 24, VILLA KRAUSE, RAWSON, SAN JUAN', '2645239481', 'Activo'),
(33, 'Camila Gisel', 'Manrique Gonzalez', '38462443', 'B° Los Medanos, Mza N Casa 1, Rawson, San Juan', '2644725627', 'Activo'),
(34, 'Aylen Rocio', 'Montaña', '42006008', '', '2643182115', 'Activo'),
(35, 'Tamara Elizabeth', 'Videla', '36034686', '', '2646200958', 'Activo'),
(36, 'Olivares', 'Erika Romina', '38217766', '', '2646044093', 'Activo'),
(37, 'Noelia Yanel', 'Miranda', '34647540', 'QUIROGA OESTE 711, VILLA KRAUSE, RAWSON, SAN JUAN', '2645123179', 'Activo'),
(38, 'Elena Carolina', 'Godoy Narvaez', '40369033', '', '', 'Activo'),
(39, 'Florencia Damaris', 'Correa Alaniz', '40711457', '', '', 'Activo'),
(40, 'Andrea del Carmen', 'Carrizo Mercado', '30243616', 'B° HUARPE, MZA H CASA 17, RAWSON, SAN JUAN', '2644840485', 'Activo'),
(41, 'Erika Patricia', 'Vega Vera', '29911787', '', '2645136894', 'Activo'),
(42, 'Jaquelina Ana Victoria', 'De Castro Villalonga', '35509641', '', '92996238787', 'Activo'),
(43, 'Monica Beatriz', 'Gonzalez', '29883239', '', '2645022970', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditos`
--

CREATE TABLE `creditos` (
  `id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `monto_cuota` decimal(10,2) NOT NULL,
  `cuotas` int(10) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('Activo','Vencido','Pagado') NOT NULL,
  `frecuencia` enum('mensual','quincenal','semanal') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `creditos`
--

INSERT INTO `creditos` (`id`, `cliente_id`, `monto`, `monto_total`, `monto_cuota`, `cuotas`, `fecha_inicio`, `fecha_vencimiento`, `estado`, `frecuencia`) VALUES
(13, 1, 100000.00, 136000.00, 136000.00, 1, '2025-04-20', '2025-04-20', 'Activo', 'mensual'),
(14, 4, 100000.00, 136000.00, 136000.00, 1, '2025-04-12', '2025-04-12', 'Activo', 'mensual'),
(15, 11, 150000.00, 236000.00, 118000.00, 2, '2025-04-18', '2025-05-18', 'Activo', 'mensual'),
(16, 9, 100000.00, 136000.00, 136000.00, 1, '2025-04-10', '2025-04-10', 'Activo', 'mensual'),
(17, 10, 100000.00, 136000.00, 136000.00, 1, '2025-04-13', '2025-04-13', 'Activo', 'mensual'),
(18, 32, 150000.00, 311000.00, 77750.00, 4, '2025-04-26', '2025-07-26', 'Activo', 'mensual'),
(19, 32, 150000.00, 311000.00, 77750.00, 4, '2025-04-26', '2025-07-26', 'Activo', 'mensual'),
(20, 33, 250000.00, 448500.00, 149500.00, 3, '2025-02-12', '2025-04-12', 'Activo', 'mensual'),
(21, 34, 130000.00, 238500.00, 79500.00, 3, '2025-04-21', '2025-06-21', 'Activo', 'mensual'),
(22, 35, 200000.00, 361000.00, 120333.33, 3, '2025-03-20', '2025-05-20', 'Activo', 'mensual'),
(23, 36, 150000.00, 273500.00, 91166.67, 3, '2025-04-18', '2025-06-18', 'Activo', 'mensual'),
(24, 37, 300000.00, 686000.00, 137200.00, 5, '2025-04-12', '2025-08-12', 'Activo', 'mensual'),
(25, 38, 350000.00, 623500.00, 207833.33, 3, '2025-04-13', '2025-06-13', 'Activo', 'mensual'),
(26, 39, 150000.00, 273500.00, 91166.67, 3, '2025-03-20', '2025-05-20', 'Activo', 'mensual'),
(27, 40, 100000.00, 136000.00, 136000.00, 1, '2025-04-10', '2025-04-10', 'Activo', 'mensual'),
(28, 41, 100000.00, 186000.00, 62000.00, 3, '2025-03-20', '2025-05-20', 'Activo', 'mensual'),
(29, 42, 200000.00, 361000.00, 120333.33, 3, '2025-03-10', '2025-05-10', 'Activo', 'mensual'),
(30, 43, 150000.00, 273500.00, 91166.67, 3, '2025-03-21', '2025-05-21', 'Activo', 'mensual'),
(31, 8, 100000.00, 186000.00, 62000.00, 3, '2025-03-14', '2025-05-14', 'Activo', 'mensual');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(10) UNSIGNED NOT NULL,
  `id_credito` int(10) UNSIGNED NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `estado` enum('Pagado','Impago') DEFAULT 'Impago',
  `fecha_vencimiento` date DEFAULT NULL,
  `nro_cuota` int(11) NOT NULL,
  `monto_adeudado` decimal(10,2) NOT NULL,
  `dias_retraso` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_credito`, `fecha_pago`, `monto_pagado`, `estado`, `fecha_vencimiento`, `nro_cuota`, `monto_adeudado`, `dias_retraso`) VALUES
(1, 13, '2025-04-13', 136000.00, 'Pagado', '0000-00-00', 1, 2025.00, 0),
(2, 30, '2025-04-13', 101650.84, 'Pagado', '0000-00-00', 1, 2025.00, 0),
(3, 29, '2025-04-13', 1407390.00, 'Pagado', '0000-00-00', 1, 2025.00, 0),
(4, 29, '2025-04-13', 122738.33, 'Pagado', '0000-00-00', 2, 2025.00, 0),
(5, 26, '2025-04-13', 102106.67, 'Pagado', '0000-00-00', 1, 2025.00, 0),
(6, 16, '2025-04-13', 138040.00, 'Pagado', '0000-00-00', 1, 2025.00, 3);

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
  ADD KEY `id_prestamo` (`id_credito`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `creditos`
--
ALTER TABLE `creditos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `creditos`
--
ALTER TABLE `creditos`
  ADD CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `creditos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_creditos_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
