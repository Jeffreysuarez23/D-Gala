# CRUD de Pedidos - Guía Completa

## Descripción General

Se ha creado un sistema completo de gestión de pedidos para tu tienda e-commerce. El sistema permite:

- ✅ Listar todos los pedidos
- ✅ Ver detalles de cada pedido
- ✅ Cambiar el estado de los pedidos
- ✅ Filtrar por estado, cliente y fecha
- ✅ Eliminar pedidos
- ✅ Operaciones CRUD programáticas mediante la clase `PedidosCRUD`

## Archivos Creados

### 1. `/Admin/modulos/GestionarPedidos.php`
Módulo principal de gestión de pedidos con interfaz visual completa.

**Características:**
- Tabla interactiva de pedidos
- Sistema de filtros avanzado (estado, cliente, fecha)
- Vista detallada de cada pedido
- Visualización de productos dentro de cada pedido
- Cambio de estado de pedidos
- Eliminación de pedidos

**Estados disponibles:**
- `pendiente` - Pedido creado, en espera de procesamiento
- `procesando` - Pedido en proceso de preparación
- `enviado` - Pedido fue enviado
- `entregado` - Pedido fue entregado al cliente
- `cancelado` - Pedido fue cancelado

### 2. `/Admin/crud/pedidos.php`
Clase `PedidosCRUD` con métodos programáticos para operaciones de base de datos.

## Cómo Usar

### A. Interfaz Visual (Recomendado para Administradores)

1. **Acceder al módulo:**
   - Ve a la sección "Pedidos" en el menú lateral del admin
   - Haz clic en "Gestionar Pedidos"

2. **Listar pedidos:**
   - Se mostrará una tabla con todos los pedidos

3. **Filtrar pedidos:**
   - Por Estado: Selecciona el estado de la lista desplegable
   - Por Cliente: Escribe nombre, apellido o email
   - Por Fecha: Selecciona una fecha específica
   - Haz clic en "Filtrar"

4. **Ver detalles de un pedido:**
   - Haz clic en el botón ojo (👁️)
   - Verás información del cliente, detalles de compra y productos

5. **Cambiar estado:**
   - Desde la vista de detalles
   - Selecciona el nuevo estado en la lista desplegable
   - Haz clic en "Actualizar Estado"

6. **Eliminar un pedido:**
   - Haz clic en el botón de basura (🗑️)
   - Confirma la eliminación

### B. Uso Programático (Para Desarrolladores)

#### Importar la clase:
```php
require_once("../Admin/crud/pedidos.php");

$pedidos_crud = new PedidosCRUD($conexion);
```

#### Obtener todos los pedidos:
```php
$todos_los_pedidos = $pedidos_crud->obtenerTodos();
print_r($todos_los_pedidos);
```

#### Obtener con filtros:
```php
$filtros = [
    'estado' => 'pendiente',
    'cliente' => 'Juan',
    'fecha' => '2026-02-23'
];
$pedidos_filtrados = $pedidos_crud->obtenerTodos($filtros);
```

#### Obtener un pedido específico:
```php
$pedido = $pedidos_crud->obtenerPorId(1);
echo "Cliente: " . $pedido['nombre'] . " " . $pedido['apellido'];
echo "Total: $" . number_format($pedido['total'], 2);
```

#### Obtener detalles del pedido:
```php
$detalles = $pedidos_crud->obtenerDetalles(1);
foreach ($detalles as $detalle) {
    echo $detalle['titulo'] . " x" . $detalle['cantidad']; 
    echo " = $" . number_format($detalle['precio'] * $detalle['cantidad'], 2);
}
```

#### Cambiar estado:
```php
try {
    $pedidos_crud->cambiarEstado(1, 'enviado');
    echo "Estado actualizado correctamente";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### Crear un nuevo pedido:
```php
$datos_pedido = [
    'id_transaccion' => 'TRX-123456',
    'email' => 'cliente@example.com',
    'id_cliente' => 10,
    'total' => 150.00,
    'medio_pago' => 'PayPal',
    'status' => 'pendiente'
];

try {
    $id_nuevo_pedido = $pedidos_crud->crear($datos_pedido);
    echo "Pedido creado con ID: " . $id_nuevo_pedido;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### Agregar productos al pedido:
```php
$detalle = [
    'id_producto' => 9,
    'titulo' => 'Conjunto louis Button',
    'precio' => 24000.00,
    'cantidad' => 2
];

try {
    $pedidos_crud->agregarDetalle($id_nuevo_pedido, $detalle);
    echo "Producto agregado al pedido";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### Obtener estadísticas:
```php
$stats = $pedidos_crud->obtenerEstadisticas();
echo "Total de pedidos: " . $stats['total'];
echo "Total en ventas: $" . number_format($stats['total_ventas'], 2);
echo "Promedio de venta: $" . number_format($stats['promedio_venta'], 2);
```

#### Obtener pedidos por rango de fechas:
```php
$pedidos = $pedidos_crud->obtenerPorFechas('2026-02-01', '2026-02-28');
```

#### Obtener pedidos de un cliente:
```php
$pedidos_cliente = $pedidos_crud->obtenerPorCliente(10);
```

#### Buscar por número de transacción:
```php
$pedido = $pedidos_crud->buscarPorTransaccion('TRX-123456');
```

#### Eliminar un pedido:
```php
try {
    $pedidos_crud->eliminar(1);
    echo "Pedido eliminado correctamente";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Estructura de la Base de Datos

### Tabla `compra`
```sql
id                INT (Clave primaria)
id_transaccion    VARCHAR(20) - Identificador único de transacción
fecha             DATETIME - Fecha y hora del pedido
status            VARCHAR(20) - Estado del pedido
email             VARCHAR(200) - Email del cliente
id_cliente        INT - Referencia a tabla clientes
total             DECIMAL(10,2) - Total del pedido
medio_pago        VARCHAR(50) - Método de pago utilizado
```

### Tabla `detalle_compra`
```sql
id                INT (Clave primaria)
id_compra         INT - Referencia a tabla compra
id_producto       INT - Referencia a tabla productos
titulo            VARCHAR(200) - Nombre del producto
precio            DECIMAL(10,2) - Precio unitario
cantidad          INT - Cantidad de unidades
```

### Tabla `clientes`
```sql
id                INT (Clave primaria)
nombre            VARCHAR(80)
apellido          VARCHAR(80)
email             VARCHAR(200)
telefono          VARCHAR(20)
documento         VARCHAR(30)
estatus           TINYINT
fecha_alta        DATETIME
```

## Gestos de Validación

El sistema incluye validaciones para:
- ✅ Estados válidos (solo acepta: pendiente, procesando, enviado, entregado, cancelado)
- ✅ Campos requeridos en la creación de pedidos
- ✅ Transacciones de base de datos (rollback en caso de error)
- ✅ Protección contra SQL Injection usando prepared statements

## Formato de Respuesta

Todos los métodos devuelven arrays asociativos:
```php
[
    'id' => 1,
    'id_transaccion' => 'TRX-123456',
    'fecha' => '2026-02-23 14:30:00',
    'status' => 'pendiente',
    'email' => 'cliente@example.com',
    'id_cliente' => 10,
    'total' => 150.50,
    'medio_pago' => 'PayPal',
    'nombre' => 'Juan',
    'apellido' => 'Pérez'
]
```

## Mensajes de Error

En caso de error, la clase lanza excepciones:
```php
try {
    $pedidos_crud->cambiarEstado(1, 'estado_invalido');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    // Output: "Error: Estado no válido"
}
```

## Notas Importantes

1. **Cascada de eliminación**: Al eliminar un pedido, se eliminan automáticamente sus detalles
2. **Transacciones**: Se usan transacciones para garantizar integridad de datos
3. **Seguridad**: Se usan prepared statements para prevenir inyecciones SQL
4. **Formato**: Los precios se procesan con 2 decimales (DECIMAL 10,2)
5. **Fechas**: El sistema usa formato DATETIME (Y-m-d H:i:s)

## Próximas Funcionalidades Sugeridas

- 📧 Envío automático de emails al cambiar estado
- 🗒️ Sistema de notas/comentarios en pedidos
- 📊 Generación de reportes
- 📄 Descarga de comprobantes en PDF
- 🔍 Búsqueda avanzada por rango de precios
- 📈 Gráficas de ventas

---

¿Necesitas ayuda con alguna funcionalidad específica?
