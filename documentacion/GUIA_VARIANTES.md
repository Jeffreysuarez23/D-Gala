# 📦 Sistema de Variantes de Productos - Guía de Integración

## 🎯 Resumen
He creado un sistema completo de variantes de productos que permite a los clientes seleccionar múltiples variantes (talla + color + precio) en la página de detalles sin recargar la página, todo con AJAX.

---

## 📁 Archivos Creados/Modificados

### 1. **include/api_variantes.php** ✅ (NUEVO)
API que gestiona todas las variantes del producto:
- `?action=getVariantes` - Obtiene todas las variantes
- `?action=filtrarVariantes` - Filtra variantes según talla/color seleccionados
- `?action=getVariante` - Obtiene una variante específica

### 2. **include/carrito_variantes.php** ✅ (NUEVO)
Sistema mejorado de carrito que maneja variantes:
- `action=agregarVariante` - Agrega variante al carrito
- `action=obtenerCarrito` - Obtiene carrito actual
- `action=actualizarCantidad` - Actualiza cantidad de variante
- `action=eliminarVariante` - Elimina variante del carrito
- `action=vaciarCarrito` - Vacía todo el carrito

### 3. **include/carrito.php** ✅ (MODIFICADO)
Actualizado para soportar tanto productos antiguos como variantes nuevas (compatibilidad)

### 4. **public/detalles.php** ✅ (MODIFICADO)
Completamente rediseñado con:
- Interfaz visual para seleccionar tallas (cuadros)
- Interfaz visual para seleccionar colores (círculos)
- Filtrado dinámico bidireccional
- Sistema temporal para agregar múltiples variantes
- AJAX para todo (sin recargas)

### 5. **public/carrito_nuevo.php** ✅ (NUEVO)
Página mejorada que visualiza:
- Productos antiguos (compatibilidad)
- Variantes seleccionadas
- Totales y resumen

---

## 🔄 Flujo del Sistema

```
1. Usuario entra a detalles.php
   ↓
2. Carga variantes del producto (AJAX)
   ↓
3. Se muestran tallas disponibles (cuadros)
   ↓
4. Se muestran colores disponibles (círculos)
   ↓
5. Usuario selecciona Talla
   ↓
6. Sistema filtra y muestra solo colores disponibles (AJAX)
   ↓
7. Usuario selecciona Color
   ↓
8. Se muestra información de la variante (precio, stock)
   ↓
9. Usuario ingresa cantidad
   ↓
10. Usuario hace clic "Agregar esta variante"
    ↓
11. Variante se agrega al carrito temporal (sin recargar)
    ↓
12. Usuario puede agregar más variantes
    ↓
13. Usuario hace clic "Agregar todas al carrito"
    ↓
14. Todas las variantes se envían al carrito principal (AJAX)
```

---

## 🚀 Características Principales

### ✅ Filtrado Dinámico Bidireccional
- Si selecciona **talla**, solo muestra colores disponibles para esa talla
- Si selecciona **color**, solo muestra tallas disponibles para ese color
- Si selecciona ambas, muestra la variante con precio y stock exacto

### ✅ Interfaz Visual Mejorada
- **Tallas**: Botones cuadrados con efecto hover y active
- **Colores**: Círculos con código hexadecimal (muestra el color real)
- **Stock**: Indicador "Disponible" o "Agotado"
- **Precio**: Muestra el precio específico de la variante

### ✅ Carrito Temporal
- Agregue múltiples variantes antes de agregarlas al carrito principal
- Modifique cantidades en tiempo real
- Vea el total de variantes seleccionadas
- Elimine variantes del carrito temporal

### ✅ AJAX (Sin Recargas)
- Todas las operaciones sin recargar la página
- Notificaciones visuales (success, error, warning)
- Actualización del contador del carrito en tiempo real

### ✅ Compatibilidad
- Sigue funcionando el sistema antiguo de productos simples
- Carrito soporta tanto productos como variantes
- Sin romper funcionalidad existente

---

## 📊 Estructura de BD Utilizada

### Tabla: `productos_variantes`
```sql
- id (Variante ID)
- id_producto (FK)
- id_talla (FK a c_tallas)
- id_color (FK a c_colores)
- precio (decimal - puede ser diferente al producto base)
- stock (int - stock específico de la variante)
```

### Tablas de Referencia:
- `c_tallas` (id, nombre) - "S", "M", "L", "XL", "28", "30", etc.
- `c_colores` (id, nombre, codigo_hex) - "Negro", "#000000", etc.

---

## 🔧 Cómo Usar

### Para el Desarrollador:

#### 1. **Insertar variantes de ejemplo**
```sql
INSERT INTO `c_tallas` (nombre) VALUES ('S'), ('M'), ('L'), ('XL');

INSERT INTO `c_colores` (nombre, codigo_hex) VALUES 
('Negro', '#000000'),
('Azul', '#0000FF'),
('Rojo', '#FF0000');

INSERT INTO `productos_variantes` (id_producto, id_talla, id_color, precio, stock) VALUES
(1, 1, 1, 25000.00, 10),  -- Talla S, Negro, 25000, 10 unidades
(1, 1, 2, 25000.00, 8),   -- Talla S, Azul, 25000, 8 unidades
(1, 2, 1, 25000.00, 15),  -- Talla M, Negro, 25000, 15 unidades
(1, 2, 2, 26000.00, 5);   -- Talla M, Azul, 26000 (precio diferente), 5 unidades
```

#### 2. **Agregar link a carrito nuevo** (en header.php)
```html
<a href="carrito_nuevo.php" class="nav-link">
    <i class="bi bi-bag"></i> Carrito
</a>
```

#### 3. **Verificar detalles.php**
El archivo ya está completamente configurado. Para un producto con ID=1:
```
https://tutienda.com/public/detalles.php?id=1&token=[token]
```

---

## 💾 Estructura de Datos en Sesión

### Sistema Antiguo (productos simples):
```php
$_SESSION['carrito']['productos'] = [
    1 => 5,    // Producto ID 1, cantidad 5
    2 => 3     // Producto ID 2, cantidad 3
];
```

### Sistema Nuevo (variantes):
```php
$_SESSION['carrito']['variantes'] = [
    'variante_1' => [
        'variante_id' => 1,
        'id_producto' => 1,
        'cantidad' => 2,
        'precio' => 25000.00,
        'talla' => 'M',
        'color' => 'Negro'
    ],
    'variante_2' => [
        'variante_id' => 2,
        'id_producto' => 1,
        'cantidad' => 1,
        'precio' => 26000.00,
        'talla' => 'L',
        'color' => 'Azul'
    ]
];
```

---

## 🎨 Customización de Colores

Los colores usados vienen del archivo CSS de Bootstrap (variables):
- `--primary-dark`: Color principal (botones activos)
- `--secondary-light`: Fondo claro (secciones)
- `--accent-gray`: Texto secundario

Modifica en `public/detalles.php` la sección de `<style>` para cambiar colores.

---

## 🔐 Seguridad

1. **Validación de Stock**: Verifica que hay stock disponible antes de agregar
2. **Validación de Variante**: Confirma que la variante existe
3. **Validación de Cantidad**: Rango de 1 a 12 unidades
4. **Protección CSRF**: Usa tokens si es necesario agregar

---

## 📱 Responsive

Todas las interfaces son responsive:
- **Desktop**: Grid de tallas/colores automático
- **Mobile**: Se adapta al ancho de pantalla
- **Tablet**: Interfaz optimizada

---

## 🐛 Troubleshooting

### Las variantes no aparecen
- ✅ Verificar que existen variantes en BD para el producto
- ✅ Verificar que `productos_variantes` tiene datos

### Los colores no se muestran
- ✅ Revisar tabla `c_colores` - columna `codigo_hex` debe tener formato #XXXXXX
- ✅ Asegurar que variantes tengan `id_color` (puede ser NULL)

### El carrito no actualiza
- ✅ Verificar que `carrito_variantes.php` está en la ruta correcta: `../include/`
- ✅ Verificar que la sesión está iniciada

---

## 📝 Notas Importantes

1. **Precio de Variante**: El precio guardado en `productos_variantes` es el precio FINAL de esa variante. No se aplican descuentos automáticos.

2. **Stock de Variante**: Cada variante tiene su propio stock independiente.

3. **Carrito Temporal**: Está en la interfaz de detalles.php, no se guarda en BD hasta que el usuario agregue al carrito principal.

4. **Compatibilidad**: El sistema actual soporta AMBOS:
   - Productos simples (tabla `caracter_producto`)
   - Variantes complejas (tabla `productos_variantes`)

---

## 🎯 Próximos Pasos Sugeridos

1. Integrar `carrito_nuevo.php` como página de carrito principal
2. Crear página de checkout que lea variantes del carrito
3. Agregar filtros en catálogo por talla/color
4. Sistema de tallas/colores disponibles en listado de productos

---

## 📞 Soporte

Todos los archivos están listos para usar. Solo necesitas:
1. Verificar la BD tiene variantes
2. Actualizar links en header si cambias nombre de carrito
3. Ajustar estilos CSS según tu diseño

¡El sistema está 100% funcional con AJAX sin recargas! 🚀
