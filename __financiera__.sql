-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-08-2024 a las 02:56:30
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
  `telefono` int(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `dni`, `domicilio`, `telefono`) VALUES
(1, 'Gabriela Deolinda ', 'Montaña', 35938746, 'MZA D CASA 22 S/N, B° HUARPE, POCITO, SAN JUAN', 2646047760),
(2, 'Maria Laura', 'Muñoz', 10808262, 'SEBASTIAN CABOT OESTE 315, B° HUAZIHUL, RIVADAVIA, SAN JUAN', 2645619594),
(3, 'Lucia Alicia', 'Muñoz', 11839254, 'SEBASTIAN CABOT OESTE 315, B° HUAZIHUL, RIVADAVIA, SAN JUAN', 2645619594),
(4, 'Eugenia Belen', 'Carrizo Mercado', 35510951, 'MZA E CASA 5, B° HUARPE, POCITO, SAN JUAN', 2645041015),
(5, 'Brisa Daniela', 'Gomez', 42334403, 'LEMOS E/ CALLE 5 Y 6, LOTE N° 2, POCITO, SAN JUAN', 2644845920),
(6, 'Angela Teodora ', 'Trigo', 6662571, 'URQUIZA SUR 38 ENTRE CARRIL Y GRANADEROS, VILLA NACUSI, POCITO, SAN JUAN', 2645710719),
(7, 'Myriam del Carmen', 'Nuñez', 13672491, 'MZA U CASA 3, B° TERESA DE CALCUTA, POCITO, SAN JUAN', 0),
(8, 'Rosa Paola ', 'Fernandez', 26557734, 'B° TERESA DE CALCUTA, MZA F CASA 17, VA. ABERASTAIN, POCITO, SAN JUAN', 2645251067),
(9, 'Carmen Nelly ', 'Mercado', 17166756, 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', 2644840485),
(10, 'Joana Beatriz ', 'Carrizo Mercado', 42645141, 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', 2645012637),
(11, 'Micaela Alejandra', 'Carrizo Mercado', 51195824, 'B° HUARPES MZA H CASA 17, POCITO, SAN JUAN', 2645407957),
(12, 'Miguel Angel ', 'Sarmiento', 22935648, 'ABERASTAIN SUR 287, B° PÉREZ HERNANDEZ, RIVADAVIA, SAN JUAN', 2645407957),
(13, 'Genesis Vanesa', 'Olmedo Saldaño', 39008091, 'MZA T CASA 2, SECTOR II OESTE, B° SIERRAS DEL MARQUESADO, RIVADAVIA, SAN JUAN', 2646123496),
(14, 'Maria Florencia', 'Gil', 40962859, 'DAVID CHAVEZ E/ CALLE 10 Y 11, LOTE 37, Vª ABERASTAIN, POCITO, SAN JUAN', 0),
(15, 'Yaquelina Veronica ', 'Mercado', 29098056, 'AMABLE JONES LOTE 80, VILLA HUARPE, POCITO, SAN JUAN', 2645580338),
(16, 'Luisa Rosalia ', 'Ruarte', 38908663, 'MZA L CASA 10, B° SAN JOSE, RAWSON, SAN JUAN', 2644122308),
(17, 'Tania Elena', 'Cortez', 33337790, 'MZA 23 CASA 1, B° LA ESTACION, RAWSON, SAN JUAN', 2645828964),
(18, 'Valentina Joana ', 'Zabala ', 45885024, 'B° ABERASTAIN MZA B CASA 2, POCITO, SAN JUAN', 2645152507),
(19, 'Nadia Alejandra ', 'Garcia Ramirez', 30634169, 'CALLEJON GALLASTELL CASA 4, MANO DERECHA, POCITO, SAN JUAN', 2646319623),
(20, 'Natalia Lorena', 'Guzman Navarro', 33095325, 'B° CRUCE DE LOS ANDES III, MZA H CASA 31, POCITO, SAN JUAN', 2646628423),
(21, 'Ivana Natalia ', 'Miranda', 32939196, 'GDOR. MANUEL QUIROGA OESTE 733, Vª KRAUSE, RAWSON, SAN JUAN', 2646318999);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(10) UNSIGNED NOT NULL,
  `id_prestamo` int(11) UNSIGNED NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto_pagado` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) UNSIGNED NOT NULL,
  `id_cliente` int(11) UNSIGNED NOT NULL,
  `fecha_entrega` date NOT NULL,
  `monto_prestado` float NOT NULL,
  `cantidad_cuotas` int(11) NOT NULL,
  `monto_cuota` int(11) NOT NULL
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
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_prestamo` (`id_prestamo`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamos` (`id_prestamo`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
