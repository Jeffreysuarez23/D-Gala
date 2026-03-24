-- ============================================================
-- Datos de Ejemplo para el Sistema de Variantes
-- ============================================================
-- Este archivo contiene datos de ejemplo para probar
-- el sistema completo de variantes de productos

-- 1. Limpiar datos anteriores (OPCIONAL, comentar si quieres mantener datos)
-- DELETE FROM productos_variantes;
-- DELETE FROM c_colores;
-- DELETE FROM c_tallas;

-- ============================================================
-- 2. Agregar Tallas Disponibles
-- ============================================================
INSERT INTO `c_tallas` (`id`, `nombre`) VALUES
(1, '28'),
(2, '30'),
(3, '32'),
(4, '34'),
(5, '36'),
(6, 'S'),
(7, 'M'),
(8, 'L'),
(9, 'XL'),
(10, 'XXL');

-- ============================================================
-- 3. Agregar Colores Disponibles
-- ============================================================
INSERT INTO `c_colores` (`id`, `nombre`, `codigo_hex`, `estado`) VALUES
(1, 'Negro', '#000000', 1),
(2, 'Blanco', '#FFFFFF', 1),
(3, 'Azul', '#0000FF', 1),
(4, 'Rojo', '#FF0000', 1),
(5, 'Verde', '#008000', 1),
(6, 'Amarillo', '#FFFF00', 1),
(7, 'Gris', '#808080', 1),
(8, 'Marron', '#A52A2A', 1),
(9, 'Rosa', '#FFC0CB', 1),
(10, 'Naranja', '#FFA500', 1);

-- ============================================================
-- 4. Agregar Variantes para el Producto 9 (Conjunto louis Button)
-- ============================================================
-- Talla S con diferentes colores
INSERT INTO `productos_variantes` (`id_producto`, `id_talla`, `id_color`, `precio`, `stock`) VALUES
(9, 6, 1, 24000.00, 10),   -- S, Negro, 24000, 10 unidades
(9, 6, 2, 24000.00, 8),    -- S, Blanco, 24000, 8 unidades
(9, 6, 3, 24000.00, 12),   -- S, Azul, 24000, 12 unidades
(9, 6, 4, 25000.00, 5),    -- S, Rojo, 25000 (más caro), 5 unidades

-- Talla M con diferentes colores
(9, 7, 1, 24000.00, 15),   -- M, Negro, 24000, 15 unidades
(9, 7, 2, 24000.00, 10),   -- M, Blanco, 24000, 10 unidades
(9, 7, 3, 24000.00, 20),   -- M, Azul, 24000, 20 unidades
(9, 7, 5, 23000.00, 7),    -- M, Verde, 23000 (más barato), 7 unidades

-- Talla L con diferentes colores
(9, 8, 1, 24000.00, 18),   -- L, Negro, 24000, 18 unidades
(9, 8, 2, 24000.00, 12),   -- L, Blanco, 24000, 12 unidades
(9, 8, 4, 25000.00, 6),    -- L, Rojo, 25000, 6 unidades
(9, 8, 6, 26000.00, 3),    -- L, Amarillo, 26000 (premium), 3 unidades

-- Talla XL con diferentes colores
(9, 9, 1, 24000.00, 9),    -- XL, Negro, 24000, 9 unidades
(9, 9, 3, 24000.00, 14),   -- XL, Azul, 24000, 14 unidades
(9, 9, 7, 23000.00, 8);    -- XL, Gris, 23000, 8 unidades

-- ============================================================
-- 5. Agregar Variantes para el Producto 10 (Conjunto louis Button)
-- ============================================================
INSERT INTO `productos_variantes` (`id_producto`, `id_talla`, `id_color`, `precio`, `stock`) VALUES
(10, 6, 1, 12000.00, 20),  -- S, Negro
(10, 6, 2, 12000.00, 15),  -- S, Blanco
(10, 6, 3, 12000.00, 25),  -- S, Azul

(10, 7, 1, 12000.00, 30),  -- M, Negro
(10, 7, 2, 12000.00, 22),  -- M, Blanco
(10, 7, 4, 13000.00, 10),  -- M, Rojo (más caro)

(10, 8, 1, 12000.00, 18),  -- L, Negro
(10, 8, 3, 12000.00, 25),  -- L, Azul
(10, 8, 9, 14000.00, 5);   -- L, Rosa (premium)

-- ============================================================
-- 6. Agregar Variantes para el Producto 11 (roblox)
-- ============================================================
INSERT INTO `productos_variantes` (`id_producto`, `id_talla`, `id_color`, `precio`, `stock`) VALUES
(11, 2, 1, 20000.00, 15),  -- 30, Negro
(11, 2, 2, 20000.00, 10),  -- 30, Blanco
(11, 2, 3, 20000.00, 8),   -- 30, Azul

(11, 3, 1, 20000.00, 12),  -- 32, Negro
(11, 3, 3, 20000.00, 20),  -- 32, Azul
(11, 3, 7, 19000.00, 9),   -- 32, Gris (descuento)

(11, 4, 1, 20000.00, 14),  -- 34, Negro
(11, 4, 4, 21000.00, 6);   -- 34, Rojo (más caro)

-- ============================================================
-- 7. Agregar Variantes para el Producto 12 (prueba 233)
-- ============================================================
INSERT INTO `productos_variantes` (`id_producto`, `id_talla`, `id_color`, `precio`, `stock`) VALUES
(12, 6, 1, 12000.00, 8),   -- S, Negro
(12, 6, 3, 12000.00, 5),   -- S, Azul

(12, 7, 1, 12000.00, 12),  -- M, Negro
(12, 7, 2, 12000.00, 10),  -- M, Blanco

(12, 8, 4, 13000.00, 4);   -- L, Rojo (más caro)

-- ============================================================
-- Verificar datos insertados
-- ============================================================
-- SELECT COUNT(*) as total_variantes FROM productos_variantes;
-- SELECT pv.*, t.nombre as talla, c.nombre as color, c.codigo_hex
-- FROM productos_variantes pv
-- LEFT JOIN c_tallas t ON pv.id_talla = t.id
-- LEFT JOIN c_colores c ON pv.id_color = c.id
-- WHERE pv.id_producto = 9
-- ORDER BY talla, color;
